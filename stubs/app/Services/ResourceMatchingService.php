<?php

namespace App\Services;

use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Participant;
use App\Models\SurveyAttempt;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ResourceMatchingService
{
    protected ClaudeService $claudeService;

    public function __construct(ClaudeService $claudeService)
    {
        $this->claudeService = $claudeService;
    }

    /**
     * Match and assign resources based on survey data.
     */
    public function matchAndAssign(Participant $participant, array $surveyData, ?SurveyAttempt $attempt = null): array
    {
        $concerns = $this->extractConcerns($surveyData);
        $assignments = [];

        foreach ($concerns as $concern) {
            // Query matching resources
            $resources = $this->findMatchingResources($participant, $concern);

            if ($resources->isEmpty()) {
                Log::info('No matching resources found', [
                    'participant_id' => $participant->_id,
                    'concern' => $concern,
                ]);

                continue;
            }

            // Rank resources using LLM if more than 3
            if ($resources->count() > 3) {
                $ranking = $this->claudeService->rankResources(
                    $resources->toArray(),
                    $concern['description']
                );

                $resources = $resources->sortBy(function ($resource, $key) use ($ranking) {
                    return array_search($key, $ranking) ?? PHP_INT_MAX;
                });
            }

            // Assign top 3 resources
            foreach ($resources->take(3) as $resource) {
                $assignment = ResourceAssignment::create([
                    'resource_id' => $resource->_id,
                    'assigned_to_user_id' => $participant->user_id,
                    'assigned_by_user_id' => auth()->id(),
                    'org_id' => $participant->org_id,
                    'assignment_reason' => $concern['description'],
                    'related_survey_attempt_id' => $attempt?->_id,
                    'auto_assigned' => true,
                    'status' => 'assigned',
                ]);

                // Update resource stats
                $resource->increment('assignment_count');

                $assignments[] = $assignment;
            }
        }

        // Send notifications
        if (! empty($assignments)) {
            $this->notifyAssignments($participant, $assignments);
        }

        return $assignments;
    }

    /**
     * Extract concerns from survey data.
     */
    protected function extractConcerns(array $surveyData): array
    {
        $concerns = [];

        // Check academics
        $academics = $surveyData['academics'] ?? [];
        if (($academics['overall_rating'] ?? 5) <= 2) {
            $concerns[] = [
                'type' => 'academic',
                'subject' => 'general',
                'trigger' => 'score_below_70',
                'description' => 'Overall academic performance needs improvement',
                'severity' => 'high',
            ];
        }

        // Check specific subjects
        foreach ($academics['subjects'] ?? [] as $subject => $data) {
            if (($data['rating'] ?? 5) <= 2) {
                $concerns[] = [
                    'type' => 'academic',
                    'subject' => $subject,
                    'trigger' => 'score_below_70',
                    'description' => "Struggling in {$subject}",
                    'severity' => 'medium',
                ];
            }
        }

        // Check behavior
        $behavior = $surveyData['behavior'] ?? [];
        if (($behavior['overall_rating'] ?? 5) <= 2) {
            $concerns[] = [
                'type' => 'behavior',
                'subject' => 'SEL',
                'trigger' => 'behavior_concern',
                'description' => 'Behavioral support needed',
                'severity' => 'medium',
            ];
        }

        // Check social-emotional
        $sel = $surveyData['social_emotional'] ?? [];
        if (($sel['overall_rating'] ?? 5) <= 2) {
            $concerns[] = [
                'type' => 'sel',
                'subject' => 'SEL',
                'trigger' => 'sel_concern',
                'description' => 'Social-emotional support needed',
                'severity' => 'high',
            ];
        }

        // Check recommended interventions
        foreach ($surveyData['recommended_interventions'] ?? [] as $intervention) {
            $concerns[] = [
                'type' => 'intervention',
                'subject' => 'general',
                'trigger' => 'instructor_recommendation',
                'description' => $intervention,
                'severity' => 'medium',
            ];
        }

        return $concerns;
    }

    /**
     * Find resources matching a concern.
     */
    protected function findMatchingResources(Participant $participant, array $concern): Collection
    {
        $query = Resource::query()
            ->active()
            ->approved()
            ->accessibleTo($participant->org_id);

        // Filter by subject domain
        if (! empty($concern['subject'])) {
            $query->where(function ($q) use ($concern) {
                $q->where('tags.subject_domain', $concern['subject'])
                    ->orWhere('tags.subject_domain', 'general');
            });
        }

        // Filter by level level
        $gradeLevel = $this->getGradeLevelBucket($participant->level);
        if ($gradeLevel) {
            $query->where(function ($q) use ($gradeLevel) {
                $q->where('tags.level', $gradeLevel)
                    ->orWhereNull('tags.level');
            });
        }

        // Filter by trigger type
        if (! empty($concern['trigger'])) {
            $query->where(function ($q) use ($concern) {
                $q->where('tags.performance_trigger', $concern['trigger'])
                    ->orWhereNull('tags.performance_trigger');
            });
        }

        // Filter by intervention type based on severity
        if ($concern['severity'] === 'high') {
            $query->where(function ($q) {
                $q->where('tags.intervention_type', 'Remedial')
                    ->orWhere('tags.intervention_type', 'Support');
            });
        }

        // Filter to participant-appropriate resources
        $query->where('tags.target_audience', 'Participant');

        return $query->orderBy('avg_rating', 'desc')
            ->orderBy('completion_count', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Convert level level to bucket.
     */
    protected function getGradeLevelBucket(int $level): ?string
    {
        return match (true) {
            $level <= 2 => 'K-2',
            $level <= 5 => '3-5',
            $level <= 8 => '6-8',
            $level <= 12 => '9-12',
            default => null,
        };
    }

    /**
     * Send notifications about new assignments.
     */
    protected function notifyAssignments(Participant $participant, array $assignments): void
    {
        // This would integrate with Laravel's notification system
        // For now, just log
        Log::info('Resources assigned to participant', [
            'participant_id' => $participant->_id,
            'assignment_count' => count($assignments),
        ]);

        // TODO: Implement notifications
        // $participant->user->notify(new ResourcesAssignedNotification($assignments));
        //
        // foreach ($participant->direct_supervisors as $direct_supervisor) {
        //     $direct_supervisor->notify(new ChildResourcesAssignedNotification($participant, $assignments));
        // }
    }

    /**
     * Get recommended resources for a participant without auto-assigning.
     */
    public function getRecommendations(Participant $participant, ?array $surveyData = null): Collection
    {
        // If no survey data provided, use latest survey attempt
        if (! $surveyData) {
            $latestAttempt = $participant->latest_survey_attempt;
            $surveyData = $latestAttempt?->llm_extracted_data ?? [];
        }

        if (empty($surveyData)) {
            return collect();
        }

        $concerns = $this->extractConcerns($surveyData);
        $allResources = collect();

        foreach ($concerns as $concern) {
            $resources = $this->findMatchingResources($participant, $concern);
            $allResources = $allResources->merge($resources);
        }

        return $allResources->unique('_id')->take(10);
    }
}
