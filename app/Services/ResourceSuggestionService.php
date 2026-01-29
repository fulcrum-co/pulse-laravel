<?php

namespace App\Services;

use App\Models\ContactMetric;
use App\Models\ContactResourceSuggestion;
use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Student;
use Illuminate\Support\Collection;

class ResourceSuggestionService
{
    public function __construct(
        protected ClaudeService $claudeService
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
     * Generate AI-based resource suggestions for a student.
     */
    public function generateAiSuggestions(Student $student, int $maxSuggestions = 5): Collection
    {
        // Get recent metrics to understand needs
        $metrics = $student->metrics()
            ->where('period_start', '>=', now()->subMonths(3))
            ->get();

        // Build need description
        $needDescription = $this->buildNeedDescription($student, $metrics);

        // Get available resources
        $resources = Resource::where('org_id', $student->org_id)
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
            if (!$resource) {
                continue;
            }

            $suggestion = ContactResourceSuggestion::updateOrCreate(
                [
                    'contact_type' => Student::class,
                    'contact_id' => $student->id,
                    'resource_id' => $resource->id,
                ],
                [
                    'org_id' => $student->org_id,
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
     */
    public function getPendingSuggestions(string $contactType, int $contactId): Collection
    {
        return ContactResourceSuggestion::forContact($contactType, $contactId)
            ->pending()
            ->with('resource')
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
     * Build a description of student needs based on metrics.
     */
    private function buildNeedDescription(Student $student, Collection $metrics): string
    {
        $needs = [];

        // Check academic metrics
        $gpa = $metrics->where('metric_key', 'gpa')->first();
        if ($gpa && $gpa->status === ContactMetric::STATUS_OFF_TRACK) {
            $needs[] = "Academic support needed - GPA: {$gpa->numeric_value}";
        } elseif ($gpa && $gpa->status === ContactMetric::STATUS_AT_RISK) {
            $needs[] = "Academic monitoring - GPA: {$gpa->numeric_value}";
        }

        // Check wellness
        $wellness = $metrics->where('metric_category', ContactMetric::CATEGORY_WELLNESS)->first();
        if ($wellness && $wellness->status !== ContactMetric::STATUS_ON_TRACK) {
            $needs[] = "Social-emotional support needed - wellness score: {$wellness->numeric_value}";
        }

        // Check attendance
        $attendance = $metrics->where('metric_key', 'attendance_rate')->first();
        if ($attendance && $attendance->numeric_value < 90) {
            $needs[] = "Attendance intervention needed - rate: {$attendance->numeric_value}%";
        }

        // Check behavior
        $behavior = $metrics->where('metric_category', ContactMetric::CATEGORY_BEHAVIOR)->first();
        if ($behavior && $behavior->status !== ContactMetric::STATUS_ON_TRACK) {
            $needs[] = "Behavioral support needed";
        }

        // Include risk level
        $needs[] = "Current risk level: {$student->risk_level}";

        // Include grade level for age-appropriate suggestions
        $needs[] = "Grade level: {$student->grade_level}";

        // Include any tags
        if (!empty($student->tags)) {
            $needs[] = "Tags: " . implode(', ', $student->tags);
        }

        return implode('. ', $needs);
    }

    /**
     * Rank resources using Claude AI.
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

        $systemPrompt = <<<PROMPT
You are helping match educational resources to student needs. Given a student's needs and a list of available resources, rank the top 5 most relevant resources.

Return a JSON array of objects with:
- resource_id: The ID of the resource
- score: Relevance score from 0-100
- reason: Brief explanation of why this resource matches the student's needs

Only return valid JSON array, no other text.
PROMPT;

        $userMessage = "Student needs:\n{$needDescription}\n\nAvailable resources:\n" . json_encode($resourceList);

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
            $ranking = json_decode($response['content'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Try to extract JSON array from response
                preg_match('/\[.*\]/s', $response['content'], $matches);
                if (!empty($matches[0])) {
                    $ranking = json_decode($matches[0], true);
                }
            }

            return $ranking ?? [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Generate rule-based suggestions (fallback when AI is unavailable).
     */
    public function generateRuleBasedSuggestions(Student $student, int $maxSuggestions = 5): Collection
    {
        $resources = Resource::where('org_id', $student->org_id)
            ->where('active', true)
            ->get();

        $suggestions = collect();
        $metrics = $student->metrics()
            ->where('period_start', '>=', now()->subMonths(3))
            ->get();

        // Match resources based on tags and metrics
        foreach ($resources as $resource) {
            $score = 0;
            $reasons = [];

            // Check if resource tags match student needs
            $resourceTags = $resource->tags ?? [];

            // Academic resources for low GPA
            $gpa = $metrics->where('metric_key', 'gpa')->first();
            if ($gpa && $gpa->status !== ContactMetric::STATUS_ON_TRACK) {
                if (in_array('academic', $resourceTags) || in_array('tutoring', $resourceTags)) {
                    $score += 30;
                    $reasons[] = 'Academic support needed';
                }
            }

            // Wellness resources for low wellness scores
            $wellness = $metrics->where('metric_category', ContactMetric::CATEGORY_WELLNESS)->first();
            if ($wellness && $wellness->status !== ContactMetric::STATUS_ON_TRACK) {
                if (in_array('wellness', $resourceTags) || in_array('mental-health', $resourceTags)) {
                    $score += 30;
                    $reasons[] = 'Wellness support needed';
                }
            }

            // Grade level match
            if (in_array("grade-{$student->grade_level}", $resourceTags)) {
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

        // Sort by score and take top suggestions
        return $suggestions->sortByDesc('score')
            ->take($maxSuggestions)
            ->map(function ($item) use ($student) {
                return ContactResourceSuggestion::updateOrCreate(
                    [
                        'contact_type' => Student::class,
                        'contact_id' => $student->id,
                        'resource_id' => $item['resource']->id,
                    ],
                    [
                        'org_id' => $student->org_id,
                        'suggestion_source' => ContactResourceSuggestion::SOURCE_RULE_BASED,
                        'relevance_score' => $item['score'],
                        'matching_criteria' => ['reasons' => $item['reasons']],
                        'status' => ContactResourceSuggestion::STATUS_PENDING,
                    ]
                );
            });
    }
}
