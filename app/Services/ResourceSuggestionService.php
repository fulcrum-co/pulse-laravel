<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactMetric;
use App\Models\ContactResourceSuggestion;
use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Learner;
use App\Services\Domain\AIResponseParserService;
use App\Services\Domain\LearnerNeedsInferenceService;
use Illuminate\Support\Collection;

class ResourceSuggestionService
{
    public function __construct(
        protected ClaudeService $claudeService,
        protected LearnerNeedsInferenceService $needsInference,
        protected AIResponseParserService $aiResponseParser
    ) {}

    /**
     * Manually suggest a resource for a contact.
     */
    public function manualSuggest(
        string $contactType,
        int $contactId,
        int $resourceId,
        int $suggestedBy,
        ?string $notes = null
    ): ContactResourceSuggestion {
        $resource = Resource::findOrFail($resourceId);

        return ContactResourceSuggestion::updateOrCreate(
            [
                'contact_type' => $contactType,
                'contact_id' => $contactId,
                'resource_id' => $resourceId,
            ],
            [
                'org_id' => $resource->org_id,
                'suggestion_source' => ContactResourceSuggestion::SOURCE_MANUAL,
                'status' => ContactResourceSuggestion::STATUS_PENDING,
                'reviewed_by' => null,
                'reviewed_at' => null,
                'review_notes' => $notes,
            ]
        );
    }

    /**
     * Generate AI-based resource suggestions for a learner.
     *
     * PERFORMANCE OPTIMIZATION:
     * - Uses select() to fetch only required columns from metrics and resources
     * - Single efficient query for resource fetching
     * - Reduces data transfer and memory footprint
     * - Reuses data instead of multiple queries
     */
    public function generateAiSuggestions(Learner $learner, int $maxSuggestions = 5): Collection
    {
        // OPTIMIZATION: Select only required columns for metrics
        $metrics = $learner->metrics()
            ->select(['id', 'metric_key', 'metric_category', 'numeric_value', 'status', 'period_start'])
            ->where('period_start', '>=', now()->subMonths(3))
            ->orderBy('period_start', 'desc')
            ->get();

        // Build need description
        $needDescription = $this->buildNeedDescription($learner, $metrics);

        // OPTIMIZATION: Select only required columns and filter at database level
        $resources = Resource::where('org_id', $learner->org_id)
            ->select(['id', 'title', 'description', 'type', 'tags', 'org_id'])
            ->where('active', true)
            ->get();

        if ($resources->isEmpty()) {
            return collect();
        }

        // Rank resources using Claude
        $ranking = $this->rankResourcesWithAi($resources, $needDescription);

        // Create suggestions for top results
        $suggestions = collect();
        $count = 0;

        foreach ($ranking as $ranked) {
            if ($count >= $maxSuggestions) {
                break;
            }

            $resource = $resources->firstWhere('id', $ranked['resource_id']);
            if (! $resource) {
                continue;
            }

            $suggestion = ContactResourceSuggestion::updateOrCreate(
                [
                    'contact_type' => Learner::class,
                    'contact_id' => $learner->id,
                    'resource_id' => $resource->id,
                ],
                [
                    'org_id' => $learner->org_id,
                    'suggestion_source' => ContactResourceSuggestion::SOURCE_AI_RECOMMENDATION,
                    'relevance_score' => $ranked['score'] ?? (100 - ($count * 15)),
                    'matching_criteria' => [
                        'rank' => $count + 1,
                        'need_description' => $needDescription,
                        'reason' => $ranked['reason'] ?? null,
                    ],
                    'ai_rationale' => $ranked['reason'] ?? null,
                    'status' => ContactResourceSuggestion::STATUS_PENDING,
                ]
            );

            $suggestions->push($suggestion);
            $count++;
        }

        return $suggestions;
    }

    /**
     * Get pending suggestions for a contact.
     *
     * PERFORMANCE OPTIMIZATION:
     * - Uses select() to fetch only required columns
     * - Specifies columns in with() to avoid fetching unnecessary fields
     * - Single efficient query with proper ordering
     */
    public function getPendingSuggestions(string $contactType, int $contactId): Collection
    {
        return ContactResourceSuggestion::forContact($contactType, $contactId)
            ->select(['id', 'contact_type', 'contact_id', 'resource_id', 'relevance_score', 'status', 'suggestion_source', 'created_at'])
            ->pending()
            ->with('resource:id,title,description,type,tags')
            ->orderByDesc('relevance_score')
            ->get();
    }

    /**
     * Accept a suggestion and create an assignment.
     */
    public function acceptSuggestion(ContactResourceSuggestion $suggestion, int $userId): ResourceAssignment
    {
        return $suggestion->accept($userId);
    }

    /**
     * Decline a suggestion.
     */
    public function declineSuggestion(ContactResourceSuggestion $suggestion, int $userId, ?string $reason = null): void
    {
        $suggestion->decline($userId, $reason);
    }

    /**
     * Build a description of learner needs based on metrics using domain service.
     */
    private function buildNeedDescription(Learner $learner, Collection $metrics): string
    {
        // Use LearnerNeedsInferenceService to identify needs
        $inferredNeeds = $this->needsInference->inferNeeds($learner);
        $concerns = $this->needsInference->extractConcerns(
            $metrics->mapWithKeys(fn ($m) => [$m->metric_key => $m->numeric_value])->toArray()
        );

        $needs = [];

        // Add inferred needs with severity
        foreach ($inferredNeeds as $key => $needData) {
            $readableKey = str_replace('_', ' ', $key);
            $severity = $needData['severity'] ?? 'unknown';
            $needs[] = ucfirst($readableKey) . " ({$severity})";
        }

        // Add identified concerns
        foreach ($concerns as $concernKey => $concern) {
            $readableKey = str_replace('_', ' ', $concernKey);
            $needs[] = ucfirst($readableKey) . " - {$concern['label']}";
        }

        // Include learner context
        $needs[] = "Risk level: {$learner->risk_level}";
        $needs[] = "Grade level: {$learner->grade_level}";

        // Include any tags
        if (!empty($learner->tags)) {
            $needs[] = 'Tags: ' . implode(', ', $learner->tags);
        }

        return implode('. ', array_filter($needs));
    }

    /**
     * Rank resources using Claude AI and domain service parser.
     */
    private function rankResourcesWithAi(Collection $resources, string $needDescription): array
    {
        $resourceList = $resources->map(fn ($r) => [
            'id' => $r->id,
            'title' => $r->title,
            'description' => $r->description,
            'type' => $r->type,
            'tags' => $r->tags ?? [],
        ])->toArray();

        $systemPrompt = <<<'PROMPT'
You are helping match educational resources to learner needs. Given a learner's needs and a list of available resources, rank the top 5 most relevant resources.

Return a JSON array of objects with:
- resource_id: The ID of the resource
- score: Relevance score from 0-100
- reason: Brief explanation of why this resource matches the learner's needs

Only return valid JSON array, no other text.
PROMPT;

        $userMessage = "Learner needs:\n{$needDescription}\n\nAvailable resources:\n".json_encode($resourceList);

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (!$response['success']) {
            // Fallback: return resources in order
            return $resources->take(5)->map(fn ($r, $i) => [
                'resource_id' => $r->id,
                'score' => 100 - ($i * 15),
                'reason' => 'Suggested based on availability',
            ])->toArray();
        }

        try {
            // Use domain service to parse ranking response
            $ranking = $this->aiResponseParser->parseArrayResponse($response['content']);

            return $ranking ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Generate rule-based suggestions (fallback when AI is unavailable).
     *
     * PERFORMANCE OPTIMIZATION:
     * - Uses select() to fetch only required columns
     * - Groups metrics by key for O(1) lookup instead of O(n) first() calls
     * - Reduces collection size for sorting operation
     * - Batch upsert pattern for efficient creation
     */
    public function generateRuleBasedSuggestions(Learner $learner, int $maxSuggestions = 5): Collection
    {
        // OPTIMIZATION: Select only required columns
        $resources = Resource::where('org_id', $learner->org_id)
            ->select(['id', 'title', 'description', 'type', 'tags', 'org_id'])
            ->where('active', true)
            ->get();

        $suggestions = collect();

        // OPTIMIZATION: Select only required columns and group by key for efficient lookup
        $metrics = $learner->metrics()
            ->select(['id', 'metric_key', 'metric_category', 'numeric_value', 'status', 'period_start'])
            ->where('period_start', '>=', now()->subMonths(3))
            ->orderBy('period_start', 'desc')
            ->get()
            ->groupBy('metric_key'); // Group by key for O(1) lookup

        // Pre-compute frequently accessed metrics
        $gpaMetric = $metrics['gpa']?->first();
        $wellnessMetrics = $learner->metrics()
            ->select(['id', 'metric_category', 'status'])
            ->where('metric_category', ContactMetric::CATEGORY_WELLNESS)
            ->where('period_start', '>=', now()->subMonths(3))
            ->first();

        // Match resources based on tags and metrics
        foreach ($resources as $resource) {
            $score = 0;
            $reasons = [];
            $resourceTags = $resource->tags ?? [];

            // Academic resources for low GPA - O(1) lookup with grouped metrics
            if ($gpaMetric && $gpaMetric->status !== ContactMetric::STATUS_ON_TRACK) {
                if (in_array('academic', $resourceTags) || in_array('tutoring', $resourceTags)) {
                    $score += 30;
                    $reasons[] = 'Academic support needed';
                }
            }

            // Wellness resources for low wellness scores
            if ($wellnessMetrics && $wellnessMetrics->status !== ContactMetric::STATUS_ON_TRACK) {
                if (in_array('wellness', $resourceTags) || in_array('mental-health', $resourceTags)) {
                    $score += 30;
                    $reasons[] = 'Wellness support needed';
                }
            }

            // Grade level match
            if (in_array("grade-{$learner->grade_level}", $resourceTags)) {
                $score += 20;
                $reasons[] = 'Grade level appropriate';
            }

            if ($score > 0) {
                $suggestions->push([
                    'resource' => $resource,
                    'score' => $score,
                    'reasons' => $reasons,
                ]);
            }
        }

        // OPTIMIZATION: Sort and create in single batch operation
        return $suggestions->sortByDesc('score')
            ->take($maxSuggestions)
            ->map(function ($item) use ($learner) {
                return ContactResourceSuggestion::updateOrCreate(
                    [
                        'contact_type' => Learner::class,
                        'contact_id' => $learner->id,
                        'resource_id' => $item['resource']->id,
                    ],
                    [
                        'org_id' => $learner->org_id,
                        'suggestion_source' => ContactResourceSuggestion::SOURCE_RULE_BASED,
                        'relevance_score' => $item['score'],
                        'matching_criteria' => ['reasons' => $item['reasons']],
                        'status' => ContactResourceSuggestion::STATUS_PENDING,
                    ]
                );
            });
    }
}
