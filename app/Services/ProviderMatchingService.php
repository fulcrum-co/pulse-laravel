<?php

namespace App\Services;

use App\Models\Program;
use App\Models\Provider;
use App\Models\Student;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProviderMatchingService
{
    public function __construct(
        protected ClaudeService $claudeService
    ) {}

    /**
     * Find matching providers for a student's needs.
     */
    public function findMatchingProviders(Student $student, array $needs = [], int $maxResults = 5): Collection
    {
        $providers = Provider::where('org_id', $student->org_id)
            ->active()
            ->verified()
            ->get();

        if ($providers->isEmpty()) {
            return collect();
        }

        // If no specific needs provided, infer from student data
        if (empty($needs)) {
            $needs = $this->inferStudentNeeds($student);
        }

        // Score and rank providers
        $scoredProviders = $providers->map(function ($provider) use ($needs, $student) {
            $score = $this->calculateMatchScore($provider, $needs, $student);

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
     * Find matching programs for a student's needs.
     */
    public function findMatchingPrograms(Student $student, array $needs = [], int $maxResults = 5): Collection
    {
        $programs = Program::where('org_id', $student->org_id)
            ->active()
            ->get();

        if ($programs->isEmpty()) {
            return collect();
        }

        if (empty($needs)) {
            $needs = $this->inferStudentNeeds($student);
        }

        $scoredPrograms = $programs->map(function ($program) use ($needs, $student) {
            $score = $this->calculateProgramMatchScore($program, $needs, $student);

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
    public function getAiProviderRecommendations(Student $student, array $context = []): array
    {
        $providers = Provider::where('org_id', $student->org_id)
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

        $studentContext = [
            'grade_level' => $student->grade_level,
            'risk_level' => $student->risk_level,
            'iep_status' => $student->iep_status,
            'needs' => $context['needs'] ?? $this->inferStudentNeeds($student),
            'preferences' => $context['preferences'] ?? [],
        ];

        $systemPrompt = <<<'PROMPT'
You are a student support specialist matching students with appropriate service providers.
Analyze the student's needs and available providers to recommend the best matches.

Return a JSON object with:
- recommendations: Array of up to 3 provider recommendations, each with:
  - provider_id: ID of the recommended provider
  - match_score: Score 0-100 indicating match quality
  - primary_reason: Main reason for this recommendation
  - how_they_can_help: Specific ways this provider can help the student
  - considerations: Any important considerations (cost, availability, etc.)
- overall_rationale: Brief explanation of the matching approach
- alternative_suggestions: Any other suggestions if providers aren't quite right
PROMPT;

        $response = $this->claudeService->sendMessage(
            "Match providers for this student:\n\nStudent:\n".json_encode($studentContext, JSON_PRETTY_PRINT).
            "\n\nAvailable Providers:\n".json_encode($providerList, JSON_PRETTY_PRINT),
            $systemPrompt
        );

        if (! $response['success']) {
            return $this->generateFallbackRecommendations($providers, $student);
        }

        // Parse AI response
        if (preg_match('/\{[\s\S]*\}/', $response['content'], $matches)) {
            try {
                $result = json_decode($matches[0], true);

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
        }

        return $this->generateFallbackRecommendations($providers, $student);
    }

    /**
     * Infer student needs from their data.
     */
    protected function inferStudentNeeds(Student $student): array
    {
        $needs = [];

        // Based on risk level
        if ($student->risk_level === 'high') {
            $needs[] = 'crisis support';
            $needs[] = 'intensive intervention';
        } elseif ($student->risk_level === 'low') {
            $needs[] = 'preventive support';
        }

        // Based on IEP status
        if ($student->iep_status) {
            $needs[] = 'learning support';
            $needs[] = 'special education';
            $needs[] = 'accommodations';
        }

        // Based on ELL status
        if ($student->ell_status) {
            $needs[] = 'language support';
            $needs[] = 'cultural adaptation';
        }

        // Based on recent metrics
        $metrics = $student->metrics()
            ->where('period_start', '>=', now()->subMonths(3))
            ->get();

        $wellness = $metrics->where('metric_category', 'wellness')->first();
        if ($wellness && $wellness->numeric_value < 5) {
            $needs[] = 'mental health';
            $needs[] = 'emotional support';
        }

        $academic = $metrics->where('metric_category', 'academics')->first();
        if ($academic && $academic->status !== 'on_track') {
            $needs[] = 'academic support';
            $needs[] = 'tutoring';
        }

        $behavior = $metrics->where('metric_category', 'behavior')->first();
        if ($behavior && $behavior->status !== 'on_track') {
            $needs[] = 'behavioral support';
            $needs[] = 'counseling';
        }

        // Based on tags
        if (! empty($student->tags)) {
            $tagToNeeds = [
                'anxiety' => ['anxiety management', 'therapy'],
                'depression' => ['mental health', 'therapy', 'counseling'],
                'adhd' => ['adhd coaching', 'executive function'],
                'trauma' => ['trauma-informed care', 'therapy'],
                'social-skills' => ['social skills', 'group therapy'],
                'college-prep' => ['college preparation', 'mentorship'],
            ];

            foreach ($student->tags as $tag) {
                $lowerTag = strtolower($tag);
                if (isset($tagToNeeds[$lowerTag])) {
                    $needs = array_merge($needs, $tagToNeeds[$lowerTag]);
                }
            }
        }

        return array_unique($needs);
    }

    /**
     * Calculate match score for a provider.
     */
    protected function calculateMatchScore(Provider $provider, array $needs, Student $student): array
    {
        $score = 0;
        $factors = [];

        // Specialty match (up to 50 points)
        $specialtyMatch = 0;
        if ($provider->specialty_areas) {
            foreach ($needs as $need) {
                foreach ($provider->specialty_areas as $specialty) {
                    if ($this->fuzzyMatch($need, $specialty)) {
                        $specialtyMatch += 10;
                    }
                }
            }
        }
        $specialtyMatch = min(50, $specialtyMatch);
        $score += $specialtyMatch;
        if ($specialtyMatch > 0) {
            $factors[] = 'Specializes in relevant areas';
        }

        // Rating bonus (up to 15 points)
        if ($provider->ratings_average) {
            $ratingBonus = ($provider->ratings_average / 5) * 15;
            $score += $ratingBonus;
            if ($provider->ratings_average >= 4.5) {
                $factors[] = 'Highly rated by other students';
            }
        }

        // Verified bonus (10 points)
        if ($provider->verified_at) {
            $score += 10;
            $factors[] = 'Verified credentials';
        }

        // Availability bonus (up to 15 points)
        if ($provider->serves_remote && $provider->serves_in_person) {
            $score += 15;
            $factors[] = 'Flexible availability (remote and in-person)';
        } elseif ($provider->serves_remote) {
            $score += 10;
            $factors[] = 'Remote sessions available';
        } elseif ($provider->serves_in_person) {
            $score += 5;
            $factors[] = 'In-person sessions available';
        }

        // Insurance bonus (10 points)
        if ($provider->accepts_insurance && $student->free_reduced_lunch) {
            $score += 10;
            $factors[] = 'Accepts insurance (cost-effective option)';
        }

        return [
            'total' => min(100, $score),
            'factors' => $factors,
        ];
    }

    /**
     * Calculate match score for a program.
     */
    protected function calculateProgramMatchScore(Program $program, array $needs, Student $student): array
    {
        $score = 0;
        $factors = [];

        // Target needs match (up to 50 points)
        $needMatch = 0;
        if ($program->target_needs) {
            foreach ($needs as $need) {
                foreach ($program->target_needs as $targetNeed) {
                    if ($this->fuzzyMatch($need, $targetNeed)) {
                        $needMatch += 10;
                    }
                }
            }
        }
        $needMatch = min(50, $needMatch);
        $score += $needMatch;
        if ($needMatch > 0) {
            $factors[] = 'Addresses relevant needs';
        }

        // Cost structure bonus (up to 20 points)
        if ($program->cost_structure === 'free') {
            $score += 20;
            $factors[] = 'Free program';
        } elseif ($program->cost_structure === 'sliding_scale') {
            $score += 15;
            $factors[] = 'Sliding scale pricing available';
        } elseif ($program->cost_structure === 'insurance' && $student->free_reduced_lunch) {
            $score += 15;
            $factors[] = 'Insurance accepted';
        }

        // Location type bonus (up to 15 points)
        if ($program->location_type === 'hybrid') {
            $score += 15;
            $factors[] = 'Flexible format (in-person and virtual)';
        } elseif ($program->location_type === 'virtual') {
            $score += 10;
            $factors[] = 'Virtual program (accessible from anywhere)';
        } else {
            $score += 5;
            $factors[] = 'In-person program';
        }

        // Duration fit (up to 15 points)
        if ($program->duration_weeks) {
            if ($program->duration_weeks <= 8) {
                $score += 15;
                $factors[] = 'Short-term commitment';
            } elseif ($program->duration_weeks <= 16) {
                $score += 10;
                $factors[] = 'Medium-term program';
            } else {
                $score += 5;
                $factors[] = 'Long-term support';
            }
        }

        return [
            'total' => min(100, $score),
            'factors' => $factors,
        ];
    }

    /**
     * Fuzzy string matching for needs/specialties.
     */
    protected function fuzzyMatch(string $needle, string $haystack): bool
    {
        $needle = strtolower(trim($needle));
        $haystack = strtolower(trim($haystack));

        // Direct match
        if ($needle === $haystack) {
            return true;
        }

        // Contains match
        if (str_contains($haystack, $needle) || str_contains($needle, $haystack)) {
            return true;
        }

        // Similar words
        $synonyms = [
            'therapy' => ['therapist', 'therapeutic', 'counseling'],
            'counseling' => ['counselor', 'therapy', 'support'],
            'tutoring' => ['tutor', 'academic support', 'homework help'],
            'mental health' => ['therapy', 'counseling', 'emotional', 'psychological'],
            'anxiety' => ['stress', 'worry', 'nervous'],
            'depression' => ['sad', 'mood', 'emotional'],
            'adhd' => ['attention', 'focus', 'executive function'],
            'learning' => ['academic', 'educational', 'study'],
        ];

        foreach ($synonyms as $key => $related) {
            if (str_contains($needle, $key) || str_contains($key, $needle)) {
                foreach ($related as $synonym) {
                    if (str_contains($haystack, $synonym)) {
                        return true;
                    }
                }
            }
        }

        return false;
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
    protected function generateFallbackRecommendations(Collection $providers, Student $student): array
    {
        $needs = $this->inferStudentNeeds($student);

        $recommendations = $this->findMatchingProviders($student, $needs, 3);

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
