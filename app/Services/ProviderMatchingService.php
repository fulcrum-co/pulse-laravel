<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Program;
use App\Models\Provider;
use App\Models\Participant;
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
     * Find matching providers for a participant's needs.
     */
    public function findMatchingProviders(Participant $participant, array $needs = [], int $maxResults = 5): Collection
    {
        $providers = Provider::where('org_id', $participant->org_id)
            ->active()
            ->verified()
            ->get();

        if ($providers->isEmpty()) {
            return collect();
        }

        // If no specific needs provided, infer from participant data
        if (empty($needs)) {
            $needs = $this->inferLearnerNeeds($participant);
        }

        // Score and rank providers
        $scoredProviders = $providers->map(function ($provider) use ($needs, $participant) {
            $score = $this->calculateMatchScore($provider, $needs, $participant);

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
     * Find matching programs for a participant's needs.
     */
    public function findMatchingPrograms(Participant $participant, array $needs = [], int $maxResults = 5): Collection
    {
        $programs = Program::where('org_id', $participant->org_id)
            ->active()
            ->get();

        if ($programs->isEmpty()) {
            return collect();
        }

        if (empty($needs)) {
            $needs = $this->inferLearnerNeeds($participant);
        }

        $scoredPrograms = $programs->map(function ($program) use ($needs, $participant) {
            $score = $this->calculateProgramMatchScore($program, $needs, $participant);

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
    public function getAiProviderRecommendations(Participant $participant, array $context = []): array
    {
        $providers = Provider::where('org_id', $participant->org_id)
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
            'level' => $participant->level,
            'risk_level' => $participant->risk_level,
            'iep_status' => $participant->iep_status,
            'needs' => $context['needs'] ?? $this->inferLearnerNeeds($participant),
            'preferences' => $context['preferences'] ?? [],
        ];

        $systemPrompt = <<<'PROMPT'
You are a participant support specialist matching participants with appropriate service providers.
Analyze the participant's needs and available providers to recommend the best matches.

Return a JSON object with:
- recommendations: Array of up to 3 provider recommendations, each with:
  - provider_id: ID of the recommended provider
  - match_score: Score 0-100 indicating match quality
  - primary_reason: Main reason for this recommendation
  - how_they_can_help: Specific ways this provider can help the participant
  - considerations: Any important considerations (cost, availability, etc.)
- overall_rationale: Brief explanation of the matching approach
- alternative_suggestions: Any other suggestions if providers aren't quite right
PROMPT;

        $response = $this->claudeService->sendMessage(
            "Match providers for this participant:\n\nLearner:\n".json_encode($learnerContext, JSON_PRETTY_PRINT).
            "\n\nAvailable Providers:\n".json_encode($providerList, JSON_PRETTY_PRINT),
            $systemPrompt
        );

        if (! $response['success']) {
            return $this->generateFallbackRecommendations($providers, $participant);
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

        return $this->generateFallbackRecommendations($providers, $participant);
    }

    /**
     * Infer participant needs from their data using domain service.
     */
    protected function inferLearnerNeeds(Participant $participant): array
    {
        $inferredNeeds = $this->needsInference->inferNeeds($participant);

        // Extract need keys from inferred data and convert to string array
        $needs = [];
        foreach ($inferredNeeds as $key => $needData) {
            // Convert snake_case keys to human-readable format
            $needs[] = str_replace('_', ' ', $key);
        }

        // Add severity-based categorical needs
        $concerns = $this->needsInference->extractConcerns(
            array_filter([$participant->metrics?->first() ?? []])
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
    protected function calculateMatchScore(Provider $provider, array $needs, Participant $participant): array
    {
        return $this->resourceMatcher->calculateProviderScore($provider, $needs, $participant);
    }

    /**
     * Calculate match score for a program using domain service.
     */
    protected function calculateProgramMatchScore(Program $program, array $needs, Participant $participant): array
    {
        return $this->resourceMatcher->calculateProgramScore($program, $needs, $participant);
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
    protected function generateFallbackRecommendations(Collection $providers, Participant $participant): array
    {
        $needs = $this->inferLearnerNeeds($participant);

        $recommendations = $this->findMatchingProviders($participant, $needs, 3);

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
