<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Program;
use App\Models\Provider;
use App\Models\Learner;
use App\Services\Domain\AIResponseParserService;
use App\Services\Domain\ResourceMatcherService;
use App\Services\Domain\LearnerNeedsInferenceService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProviderMatchingService
{
    public function __construct(
        protected ClaudeService $claudeService,
        protected LearnerNeedsInferenceService $needsInference,
        protected ResourceMatcherService $resourceMatcher,
        protected AIResponseParserService $aiResponseParser
    ) {}

    /**
     * Find matching providers for a learner's needs.
     */
    public function findMatchingProviders(Learner $learner, array $needs = [], int $maxResults = 5): Collection
    {
        $providers = Provider::where('org_id', $learner->org_id)
            ->active()
            ->verified()
            ->get();

        if ($providers->isEmpty()) {
            return collect();
        }

        // If no specific needs provided, infer from learner data
        if (empty($needs)) {
            $needs = $this->inferLearnerNeeds($learner);
        }

        // Score and rank providers
        $scoredProviders = $providers->map(function ($provider) use ($needs, $learner) {
            $score = $this->calculateMatchScore($provider, $needs, $learner);

            return [
                'provider' => $provider,
                'score' => $score['total'],
                'matching_factors' => $score['factors'],
                'recommendation_reason' => $this->generateRecommendationReason($provider, $score['factors']),
            ];
        });

        // Sort by score and take top results
        return $scoredProviders
            ->sortByDesc('score')
            ->take($maxResults)
            ->filter(fn ($item) => $item['score'] >= 30) // Minimum threshold
            ->values();
    }

    /**
     * Find matching programs for a learner's needs.
     */
    public function findMatchingPrograms(Learner $learner, array $needs = [], int $maxResults = 5): Collection
    {
        $programs = Program::where('org_id', $learner->org_id)
            ->active()
            ->get();

        if ($programs->isEmpty()) {
            return collect();
        }

        if (empty($needs)) {
            $needs = $this->inferLearnerNeeds($learner);
        }

        $scoredPrograms = $programs->map(function ($program) use ($needs, $learner) {
            $score = $this->calculateProgramMatchScore($program, $needs, $learner);

            return [
                'program' => $program,
                'score' => $score['total'],
                'matching_factors' => $score['factors'],
                'recommendation_reason' => $this->generateProgramRecommendationReason($program, $score['factors']),
            ];
        });

        return $scoredPrograms
            ->sortByDesc('score')
            ->take($maxResults)
            ->filter(fn ($item) => $item['score'] >= 30)
            ->values();
    }

    /**
     * Get AI-powered provider recommendations with detailed rationale.
     */
    public function getAiProviderRecommendations(Learner $learner, array $context = []): array
    {
        $providers = Provider::where('org_id', $learner->org_id)
            ->active()
            ->verified()
            ->get();

        if ($providers->isEmpty()) {
            return [
                'recommendations' => [],
                'rationale' => 'No providers available in the system.',
            ];
        }

        $providerList = $providers->map(fn ($p) => [
            'id' => $p->id,
            'name' => $p->name,
            'type' => $p->provider_type,
            'specialties' => $p->specialty_areas,
            'credentials' => $p->credentials,
            'serves_remote' => $p->serves_remote,
            'serves_in_person' => $p->serves_in_person,
            'accepts_insurance' => $p->accepts_insurance,
            'hourly_rate' => $p->hourly_rate,
            'ratings_average' => $p->ratings_average,
        ])->toArray();

        $learnerContext = [
            'grade_level' => $learner->grade_level,
            'risk_level' => $learner->risk_level,
            'iep_status' => $learner->iep_status,
            'needs' => $context['needs'] ?? $this->inferLearnerNeeds($learner),
            'preferences' => $context['preferences'] ?? [],
        ];

        $systemPrompt = <<<'PROMPT'
You are a learner support specialist matching learners with appropriate service providers.
Analyze the learner's needs and available providers to recommend the best matches.

Return a JSON object with:
- recommendations: Array of up to 3 provider recommendations, each with:
  - provider_id: ID of the recommended provider
  - match_score: Score 0-100 indicating match quality
  - primary_reason: Main reason for this recommendation
  - how_they_can_help: Specific ways this provider can help the learner
  - considerations: Any important considerations (cost, availability, etc.)
- overall_rationale: Brief explanation of the matching approach
- alternative_suggestions: Any other suggestions if providers aren't quite right
PROMPT;

        $response = $this->claudeService->sendMessage(
            "Match providers for this learner:\n\nLearner:\n".json_encode($learnerContext, JSON_PRETTY_PRINT).
            "\n\nAvailable Providers:\n".json_encode($providerList, JSON_PRETTY_PRINT),
            $systemPrompt
        );

        if (! $response['success']) {
            return $this->generateFallbackRecommendations($providers, $learner);
        }

        // Parse AI response using domain service
        try {
            $result = $this->aiResponseParser->parseRecommendationResponse($response['content']);

            // Attach provider models to recommendations
            if (isset($result['recommendations'])) {
                $result['recommendations'] = collect($result['recommendations'])->map(function ($rec) use ($providers) {
                    $provider = $providers->firstWhere('id', $rec['provider_id']);
                    $rec['provider'] = $provider;

                    return $rec;
                })->filter(fn ($r) => $r['provider'] !== null)->values()->toArray();
            }

            return $result;
        } catch (\Exception $e) {
            Log::warning('Failed to parse AI provider recommendations', ['error' => $e->getMessage()]);
        }

        return $this->generateFallbackRecommendations($providers, $learner);
    }

    /**
     * Infer learner needs from their data using domain service.
     */
    protected function inferLearnerNeeds(Learner $learner): array
    {
        $inferredNeeds = $this->needsInference->inferNeeds($learner);

        // Extract need keys from inferred data and convert to string array
        $needs = [];
        foreach ($inferredNeeds as $key => $needData) {
            // Convert snake_case keys to human-readable format
            $needs[] = str_replace('_', ' ', $key);
        }

        // Add severity-based categorical needs
        $concerns = $this->needsInference->extractConcerns(
            array_filter([$learner->metrics?->first() ?? []])
        );

        foreach ($concerns as $concernKey => $concernData) {
            if ($concernData['severity'] !== 'low') {
                $needs[] = str_replace('_', ' ', $concernKey);
            }
        }

        return array_unique($needs);
    }

    /**
     * Calculate match score for a provider using domain service.
     */
    protected function calculateMatchScore(Provider $provider, array $needs, Learner $learner): array
    {
        return $this->resourceMatcher->calculateProviderScore($provider, $needs, $learner);
    }

    /**
     * Calculate match score for a program using domain service.
     */
    protected function calculateProgramMatchScore(Program $program, array $needs, Learner $learner): array
    {
        return $this->resourceMatcher->calculateProgramScore($program, $needs, $learner);
    }

    /**
     * Fuzzy string matching using domain service.
     */
    protected function fuzzyMatch(string $needle, string $haystack): bool
    {
        return $this->resourceMatcher->fuzzyMatch($needle, $haystack);
    }

    /**
     * Generate recommendation reason text.
     */
    protected function generateRecommendationReason(Provider $provider, array $factors): string
    {
        if (empty($factors)) {
            return 'Available provider in your area.';
        }

        $primary = $factors[0];
        $additional = count($factors) > 1 ? ' Also: '.implode(', ', array_slice($factors, 1)) : '';

        return "{$provider->name} is recommended because: {$primary}.{$additional}";
    }

    /**
     * Generate program recommendation reason text.
     */
    protected function generateProgramRecommendationReason(Program $program, array $factors): string
    {
        if (empty($factors)) {
            return 'Available program that may help.';
        }

        return "{$program->name} is recommended because it ".strtolower(implode(', ', array_slice($factors, 0, 2))).'.';
    }

    /**
     * Generate fallback recommendations when AI fails.
     */
    protected function generateFallbackRecommendations(Collection $providers, Learner $learner): array
    {
        $needs = $this->inferLearnerNeeds($learner);

        $recommendations = $this->findMatchingProviders($learner, $needs, 3);

        return [
            'recommendations' => $recommendations->map(fn ($r) => [
                'provider_id' => $r['provider']->id,
                'provider' => $r['provider'],
                'match_score' => $r['score'],
                'primary_reason' => $r['recommendation_reason'],
                'how_they_can_help' => 'Based on their specialties and your needs.',
                'considerations' => [],
            ])->toArray(),
            'overall_rationale' => 'Matched based on specialty areas and availability.',
            'alternative_suggestions' => [],
        ];
    }
}
