<?php

namespace App\Services;

use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\Student;
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
    public function matchAndAssign(Student $student, array $surveyData, ?SurveyAttempt $attempt = null): array
    {
        $concerns = $this->extractConcerns($surveyData);
        $assignments = [];

        foreach ($concerns as $concern) {
            // Query matching resources
            $resources = $this->findMatchingResources($student, $concern);

            if ($resources->isEmpty()) {
                Log::info('No matching resources found', [
                    'student_id' => $student->_id,
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
                    'assigned_to_user_id' => $student->user_id,
                    'assigned_by_user_id' => auth()->id(),
                    'org_id' => $student->org_id,
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
            $this->notifyAssignments($student, $assignments);
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
                'trigger' => 'teacher_recommendation',
                'description' => $intervention,
                'severity' => 'medium',
            ];
        }

        return $concerns;
    }

    /**
     * Find resources matching a concern.
     */
    protected function findMatchingResources(Student $student, array $concern): Collection
    {
        $query = Resource::query()
            ->active()
            ->approved()
            ->accessibleTo($student->org_id);

        // Filter by subject domain
        if (! empty($concern['subject'])) {
            $query->where(function ($q) use ($concern) {
                $q->where('tags.subject_domain', $concern['subject'])
                    ->orWhere('tags.subject_domain', 'general');
            });
        }

        // Filter by grade level
        $gradeLevel = $this->getGradeLevelBucket($student->grade_level);
        if ($gradeLevel) {
            $query->where(function ($q) use ($gradeLevel) {
                $q->where('tags.grade_level', $gradeLevel)
                    ->orWhereNull('tags.grade_level');
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

        // Filter to student-appropriate resources
        $query->where('tags.target_audience', 'Student');

        return $query->orderBy('avg_rating', 'desc')
            ->orderBy('completion_count', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Convert grade level to bucket.
     */
    protected function getGradeLevelBucket(int $grade): ?string
    {
        return match (true) {
            $grade <= 2 => 'K-2',
            $grade <= 5 => '3-5',
            $grade <= 8 => '6-8',
            $grade <= 12 => '9-12',
            default => null,
        };
    }

    /**
     * Send notifications about new assignments.
     */
    protected function notifyAssignments(Student $student, array $assignments): void
    {
        // This would integrate with Laravel's notification system
        // For now, just log
        Log::info('Resources assigned to student', [
            'student_id' => $student->_id,
            'assignment_count' => count($assignments),
        ]);

        // TODO: Implement notifications
        // $student->user->notify(new ResourcesAssignedNotification($assignments));
        //
        // foreach ($student->parents as $parent) {
        //     $parent->notify(new ChildResourcesAssignedNotification($student, $assignments));
        // }
    }

    /**
     * Get recommended resources for a student without auto-assigning.
     */
    public function getRecommendations(Student $student, ?array $surveyData = null): Collection
    {
        // If no survey data provided, use latest survey attempt
        if (! $surveyData) {
            $latestAttempt = $student->latest_survey_attempt;
            $surveyData = $latestAttempt?->llm_extracted_data ?? [];
        }

        if (empty($surveyData)) {
            return collect();
        }

        $concerns = $this->extractConcerns($surveyData);
        $allResources = collect();

        foreach ($concerns as $concern) {
            $resources = $this->findMatchingResources($student, $concern);
            $allResources = $allResources->merge($resources);
        }

        return $allResources->unique('_id')->take(10);
    }
}
