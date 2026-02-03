<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LearningGroup;
use App\Models\ContactList;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Support\Collection;

class CourseGenerationContextBuilder
{
    /**
     * Build context for generating a participant-focused course.
     */
    public function buildLearnerContext(Participant $participant): array
    {
        $context = [
            'entity_type' => 'participant',
            'entity_id' => $participant->id,
            'basic_info' => $this->getLearnerBasicInfo($participant),
            'academic_profile' => $this->getLearnerAcademicProfile($participant),
            'behavioral_profile' => $this->getLearnerBehavioralProfile($participant),
            'support_needs' => $this->getLearnerSupportNeeds($participant),
            'recent_data' => $this->getLearnerRecentData($participant),
            'improvement_areas' => $this->identifyLearnerImprovementAreas($participant),
            'strengths' => $this->identifyLearnerStrengths($participant),
        ];

        return $context;
    }

    /**
     * Build context for generating an instructor-focused course.
     */
    public function buildInstructorContext(User $instructor): array
    {
        $context = [
            'entity_type' => 'instructor',
            'entity_id' => $instructor->id,
            'basic_info' => $this->getInstructorBasicInfo($instructor),
            'learning_group_data' => $this->getInstructorLearningGroupData($instructor),
            'learner_outcomes' => $this->getInstructorLearnerOutcomes($instructor),
            'survey_feedback' => $this->getInstructorSurveyFeedback($instructor),
            'observation_data' => $this->getInstructorObservationData($instructor),
            'improvement_areas' => $this->identifyInstructorImprovementAreas($instructor),
            'professional_goals' => $this->getInstructorProfessionalGoals($instructor),
        ];

        return $context;
    }

    /**
     * Build context for generating a department-focused course.
     */
    public function buildDepartmentContext(int $orgId, array $departmentCriteria): array
    {
        $context = [
            'entity_type' => 'department',
            'org_id' => $orgId,
            'criteria' => $departmentCriteria,
            'aggregate_metrics' => $this->getDepartmentAggregateMetrics($orgId, $departmentCriteria),
            'common_challenges' => $this->getDepartmentCommonChallenges($orgId, $departmentCriteria),
            'improvement_priorities' => $this->getDepartmentImprovementPriorities($orgId, $departmentCriteria),
            'instructor_data' => $this->getDepartmentInstructorData($orgId, $departmentCriteria),
        ];

        return $context;
    }

    /**
     * Build context for a contact list (batch generation).
     */
    public function buildContactListContext(ContactList $list): array
    {
        $context = [
            'entity_type' => 'contact_list',
            'entity_id' => $list->id,
            'list_name' => $list->name,
            'list_type' => $list->list_type,
            'member_count' => $list->getAllMembers()->count(),
            'common_characteristics' => $this->getListCommonCharacteristics($list),
            'aggregate_needs' => $this->getListAggregateNeeds($list),
        ];

        return $context;
    }

    // ========================================
    // PARTICIPANT CONTEXT METHODS
    // ========================================

    protected function getLearnerBasicInfo(Participant $participant): array
    {
        return [
            'level' => $participant->level,
            'enrollment_status' => $participant->enrollment_status,
            'has_iep' => $participant->has_iep ?? false,
            'is_ell' => $participant->is_ell ?? false,
            'is_504' => $participant->is_504 ?? false,
            'tags' => $participant->tags ?? [],
        ];
    }

    protected function getLearnerAcademicProfile(Participant $participant): array
    {
        // Use aggregation pipeline for efficient metric retrieval
        $threeMonthsAgo = now()->subMonths(3)->timestamp;

        $academicMetrics = $participant->metrics()
            ->whereIn('metric_type', ['academic', 'level', 'assessment'])
            ->where('recorded_at', '>=', now()->subMonths(3))
            ->orderByDesc('recorded_at')
            ->limit(20)
            ->select(['metric_type', 'metric_name', 'metric_value', 'recorded_at'])
            ->get();

        $avgPerformance = $participant->metrics()
            ->whereIn('metric_type', ['academic', 'level', 'assessment'])
            ->where('recorded_at', '>=', now()->subMonths(3))
            ->raw(function ($collection) {
                return $collection->aggregate([
                    ['$match' => [
                        'metric_type' => ['$in' => ['academic', 'level', 'assessment']],
                        'recorded_at' => ['$gte' => new \MongoDB\BSON\UTCDateTime(now()->subMonths(3)->timestamp * 1000)]
                    ]],
                    ['$group' => [
                        '_id' => null,
                        'avg_value' => ['$avg' => '$metric_value']
                    ]]
                ]);
            });

        $avgValue = 0;
        if (!empty($avgPerformance)) {
            $avgValue = $avgPerformance[0]['avg_value'] ?? 0;
        }

        return [
            'recent_metrics' => $academicMetrics->map(fn ($m) => [
                'type' => $m->metric_type,
                'name' => $m->metric_name,
                'value' => $m->metric_value,
                'date' => $m->recorded_at->format('Y-m-d'),
            ])->toArray(),
            'average_performance' => $avgValue,
            'trend' => $this->calculateTrend($academicMetrics),
        ];
    }

    protected function getLearnerBehavioralProfile(Participant $participant): array
    {
        // Optimized with column selection and pagination
        $behavioralMetrics = $participant->metrics()
            ->whereIn('metric_type', ['behavioral', 'social_emotional', 'attendance'])
            ->where('recorded_at', '>=', now()->subMonths(3))
            ->orderByDesc('recorded_at')
            ->select(['metric_type', 'metric_name', 'metric_value', 'recorded_at'])
            ->limit(20)
            ->get();

        return [
            'recent_metrics' => $behavioralMetrics->map(fn ($m) => [
                'type' => $m->metric_type,
                'name' => $m->metric_name,
                'value' => $m->metric_value,
                'date' => $m->recorded_at->format('Y-m-d'),
            ])->toArray(),
            'risk_level' => $participant->risk_level ?? 'unknown',
        ];
    }

    protected function getLearnerSupportNeeds(Participant $participant): array
    {
        $needs = [];

        if ($participant->has_iep) {
            $needs[] = 'IEP accommodations';
        }
        if ($participant->is_ell) {
            $needs[] = 'English language support';
        }
        if ($participant->is_504) {
            $needs[] = '504 accommodations';
        }

        // Get active interventions with column selection optimization
        $enrollments = $participant->enrollments()
            ->with(['course:id,title'])
            ->whereIn('status', ['active', 'in_progress'])
            ->select(['id', 'participant_id', 'course_id', 'status'])
            ->get();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->course) {
                $needs[] = 'Enrolled in: '.$enrollment->course->title;
            }
        }

        return $needs;
    }

    protected function getLearnerRecentData(Participant $participant): array
    {
        // Get recent notes
        $recentNotes = $participant->notes()
            ->where('created_at', '>=', now()->subMonths(1))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Get recent survey responses
        $recentSurveys = $participant->surveyAttempts()
            ->with('survey')
            ->where('completed_at', '>=', now()->subMonths(3))
            ->where('status', 'completed')
            ->limit(5)
            ->get();

        return [
            'recent_notes' => $recentNotes->map(fn ($n) => [
                'content' => substr($n->content, 0, 200),
                'type' => $n->note_type,
                'date' => $n->created_at->format('Y-m-d'),
            ])->toArray(),
            'recent_surveys' => $recentSurveys->map(fn ($a) => [
                'survey_title' => $a->survey->title ?? 'Survey',
                'completed_at' => $a->completed_at?->format('Y-m-d'),
            ])->toArray(),
        ];
    }

    protected function identifyLearnerImprovementAreas(Participant $participant): array
    {
        $areas = [];

        // Based on risk level
        if (in_array($participant->risk_level, ['high', 'critical'])) {
            $areas[] = 'Overall academic support needed';
        }

        // Based on metrics trends
        $recentMetrics = $participant->metrics()
            ->where('recorded_at', '>=', now()->subMonths(3))
            ->get()
            ->groupBy('metric_type');

        foreach ($recentMetrics as $type => $metrics) {
            $trend = $this->calculateTrend($metrics);
            if ($trend === 'declining') {
                $areas[] = ucfirst(str_replace('_', ' ', $type)).' - declining trend';
            }
        }

        // Based on tags/flags
        $tags = $participant->tags ?? [];
        foreach ($tags as $tag) {
            if (str_contains(strtolower($tag), 'struggling') ||
                str_contains(strtolower($tag), 'intervention') ||
                str_contains(strtolower($tag), 'support')) {
                $areas[] = $tag;
            }
        }

        return array_unique($areas);
    }

    protected function identifyLearnerStrengths(Participant $participant): array
    {
        $strengths = [];

        // Based on metrics trends
        $recentMetrics = $participant->metrics()
            ->where('recorded_at', '>=', now()->subMonths(3))
            ->get()
            ->groupBy('metric_type');

        foreach ($recentMetrics as $type => $metrics) {
            $trend = $this->calculateTrend($metrics);
            if ($trend === 'improving') {
                $strengths[] = ucfirst(str_replace('_', ' ', $type)).' - improving';
            }
        }

        // Based on tags
        $tags = $participant->tags ?? [];
        foreach ($tags as $tag) {
            if (str_contains(strtolower($tag), 'gifted') ||
                str_contains(strtolower($tag), 'honors') ||
                str_contains(strtolower($tag), 'advanced')) {
                $strengths[] = $tag;
            }
        }

        return array_unique($strengths);
    }

    // ========================================
    // INSTRUCTOR CONTEXT METHODS
    // ========================================

    protected function getInstructorBasicInfo(User $instructor): array
    {
        return [
            'name' => $instructor->first_name.' '.$instructor->last_name,
            'role' => $instructor->role,
            'department' => $instructor->department ?? null,
        ];
    }

    protected function getInstructorLearningGroupData(User $instructor): array
    {
        // Use aggregation pipeline for efficient learning_group data retrieval
        $learning_groups = LearningGroup::where('instructor_user_id', $instructor->id)
            ->select(['id', 'name', 'level'])
            ->with(['participants:id,learning_group_id,risk_level'])
            ->get();

        return $learning_groups->map(function ($learning_group) {
            $participants = $learning_group->participants ?? collect();
            $highRiskCount = $participants->where('risk_level', 'high')->count();

            return [
                'name' => $learning_group->name,
                'level' => $learning_group->level,
                'learner_count' => $participants->count(),
                'high_risk_count' => $highRiskCount,
            ];
        })->toArray();
    }

    protected function getInstructorLearnerOutcomes(User $instructor): array
    {
        // Optimized with aggregation pipeline for instructor's learning_groups
        $learningGroupIds = LearningGroup::where('instructor_user_id', $instructor->id)
            ->select(['id'])
            ->pluck('id')
            ->toArray();

        if (empty($learningGroupIds)) {
            return [];
        }

        // Use aggregation pipeline with $lookup for JOIN
        $outcomes = \App\Models\LearnerMetric::raw(function ($collection) use ($learningGroupIds) {
            return $collection->aggregate([
                ['$lookup' => [
                    'from' => 'learning_group_participant',
                    'localField' => 'participant_id',
                    'foreignField' => 'participant_id',
                    'as' => 'enrollment'
                ]],
                ['$match' => [
                    'enrollment.learning_group_id' => ['$in' => $learningGroupIds],
                    'recorded_at' => ['$gte' => new \MongoDB\BSON\UTCDateTime(now()->subMonths(3)->timestamp * 1000)]
                ]],
                ['$group' => [
                    '_id' => '$metric_type',
                    'average' => ['$avg' => '$metric_value'],
                    'count' => ['$sum' => 1]
                ]],
                ['$project' => [
                    'metric_type' => '$_id',
                    'average' => 1,
                    'count' => 1,
                    '_id' => 0
                ]]
            ]);
        });

        $result = [];
        foreach ($outcomes as $metric) {
            $metricType = $metric['metric_type'] ?? 'unknown';
            $result[$metricType] = [
                'average' => round($metric['average'] ?? 0, 2),
                'trend' => 'stable', // Calculated separately if needed
                'count' => $metric['count'] ?? 0,
            ];
        }

        return $result;
    }

    protected function getInstructorSurveyFeedback(User $instructor): array
    {
        // Get survey responses where instructor was the target or respondent
        $attempts = \App\Models\SurveyAttempt::where(function ($q) use ($instructor) {
            $q->where('respondent_id', $instructor->id)
                ->orWhere('respondent_type', 'instructor');
        })
            ->where('status', 'completed')
            ->where('completed_at', '>=', now()->subMonths(6))
            ->with('survey')
            ->limit(10)
            ->get();

        return $attempts->map(fn ($a) => [
            'survey_title' => $a->survey->title ?? 'Survey',
            'completed_at' => $a->completed_at?->format('Y-m-d'),
            'response_summary' => $this->summarizeResponses($a->responses ?? []),
        ])->toArray();
    }

    protected function getInstructorObservationData(User $instructor): array
    {
        // Get notes/observations about the instructor
        $notes = \App\Models\ContactNote::where('contact_type', 'user')
            ->where('contact_id', $instructor->id)
            ->whereIn('note_type', ['observation', 'feedback', 'evaluation'])
            ->where('created_at', '>=', now()->subMonths(6))
            ->limit(5)
            ->get();

        return $notes->map(fn ($n) => [
            'type' => $n->note_type,
            'summary' => substr($n->content, 0, 200),
            'date' => $n->created_at->format('Y-m-d'),
        ])->toArray();
    }

    protected function identifyInstructorImprovementAreas(User $instructor): array
    {
        $areas = [];
        $terminology = app(\App\Services\TerminologyService::class);

        // Based on participant outcomes
        $outcomes = $this->getInstructorLearnerOutcomes($instructor);
        foreach ($outcomes as $type => $data) {
            if (($data['trend'] ?? '') === 'declining') {
                $areas[] = $terminology->get('learner_singular').' '.str_replace('_', ' ', $type).' '.$terminology->get('declining_label');
            }
        }

        // Based on survey feedback themes (simplified)
        $feedback = $this->getInstructorSurveyFeedback($instructor);
        // In production, you'd use NLP to extract themes

        return $areas;
    }

    protected function getInstructorProfessionalGoals(User $instructor): array
    {
        // Would typically come from a professional development tracking system
        // For now, return empty array
        return [];
    }

    // ========================================
    // DEPARTMENT CONTEXT METHODS
    // ========================================

    protected function getDepartmentAggregateMetrics(int $orgId, array $criteria): array
    {
        // Get instructors matching criteria
        $instructorQuery = User::where('org_id', $orgId)
            ->where('role', 'instructor');

        if (! empty($criteria['department'])) {
            $instructorQuery->where('department', $criteria['department']);
        }

        if (! empty($criteria['levels'])) {
            // Filter by learning_groups with matching level levels
            $instructorQuery->whereHas('learning_groups', function ($q) use ($criteria) {
                $q->whereIn('level', $criteria['levels']);
            });
        }

        $instructorIds = $instructorQuery->select(['id'])->pluck('id')->toArray();

        if (empty($instructorIds)) {
            return [];
        }

        // Optimized aggregation pipeline for all metrics in one query
        $aggregates = \App\Models\LearnerMetric::raw(function ($collection) use ($instructorIds, $criteria) {
            return $collection->aggregate([
                ['$lookup' => [
                    'from' => 'learning_group_participant',
                    'localField' => 'participant_id',
                    'foreignField' => 'participant_id',
                    'as' => 'enrollment'
                ]],
                ['$lookup' => [
                    'from' => 'learning_groups',
                    'localField' => 'enrollment.learning_group_id',
                    'foreignField' => 'id',
                    'as' => 'learning_group'
                ]],
                ['$match' => [
                    'learning_group.instructor_user_id' => ['$in' => $instructorIds],
                    'recorded_at' => ['$gte' => new \MongoDB\BSON\UTCDateTime(now()->subMonths(3)->timestamp * 1000)]
                ]],
                ['$unwind' => '$learning_group'],
                ['$group' => [
                    '_id' => '$metric_type',
                    'average' => ['$avg' => '$metric_value'],
                    'min' => ['$min' => '$metric_value'],
                    'max' => ['$max' => '$metric_value'],
                    'count' => ['$sum' => 1]
                ]],
                ['$project' => [
                    'metric_type' => '$_id',
                    'average' => ['$round' => ['$average', 2]],
                    'min' => 1,
                    'max' => 1,
                    'count' => 1,
                    '_id' => 0
                ]]
            ]);
        });

        $result = [];
        foreach ($aggregates as $metric) {
            $metricType = $metric['metric_type'] ?? 'unknown';
            $result[$metricType] = [
                'average' => $metric['average'] ?? 0,
                'min' => $metric['min'] ?? 0,
                'max' => $metric['max'] ?? 0,
                'count' => $metric['count'] ?? 0,
            ];
        }

        return $result;
    }

    protected function getDepartmentCommonChallenges(int $orgId, array $criteria): array
    {
        // Identify common challenges based on metrics and data
        $metrics = $this->getDepartmentAggregateMetrics($orgId, $criteria);
        $challenges = [];

        foreach ($metrics as $type => $data) {
            if (($data['average'] ?? 0) < 60) {
                $challenges[] = 'Low average in '.str_replace('_', ' ', $type);
            }
        }

        return $challenges;
    }

    protected function getDepartmentImprovementPriorities(int $orgId, array $criteria): array
    {
        $challenges = $this->getDepartmentCommonChallenges($orgId, $criteria);

        // Prioritize based on severity (simplified)
        return array_slice($challenges, 0, 3);
    }

    protected function getDepartmentInstructorData(int $orgId, array $criteria): array
    {
        $instructorQuery = User::where('org_id', $orgId)
            ->where('role', 'instructor');

        if (! empty($criteria['department'])) {
            $instructorQuery->where('department', $criteria['department']);
        }

        return [
            'instructor_count' => $instructorQuery->count(),
        ];
    }

    // ========================================
    // CONTACT LIST CONTEXT METHODS
    // ========================================

    protected function getListCommonCharacteristics(ContactList $list): array
    {
        $characteristics = [];

        // From filter criteria
        if ($list->filter_criteria) {
            if (! empty($list->filter_criteria['levels'])) {
                $characteristics['levels'] = $list->filter_criteria['levels'];
            }
            if (! empty($list->filter_criteria['risk_levels'])) {
                $characteristics['risk_levels'] = $list->filter_criteria['risk_levels'];
            }
            if (! empty($list->filter_criteria['tags'])) {
                $characteristics['tags'] = $list->filter_criteria['tags'];
            }
        }

        return $characteristics;
    }

    protected function getListAggregateNeeds(ContactList $list): array
    {
        $needs = [];

        if ($list->list_type === ContactList::TYPE_PARTICIPANT) {
            $participants = $list->participants;

            $iepCount = $participants->where('has_iep', true)->count();
            $ellCount = $participants->where('is_ell', true)->count();
            $highRiskCount = $participants->whereIn('risk_level', ['high', 'critical'])->count();

            if ($iepCount > 0) {
                $needs[] = "$iepCount participants with support plans";
            }
            if ($ellCount > 0) {
                $needs[] = "$ellCount participants with language support needs";
            }
            if ($highRiskCount > 0) {
                $needs[] = "$highRiskCount high-risk participants";
            }
        }

        return $needs;
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    protected function calculateTrend(Collection $metrics): string
    {
        if ($metrics->count() < 3) {
            return 'insufficient_data';
        }

        $sorted = $metrics->sortBy('recorded_at')->values();
        $firstHalf = $sorted->take((int) floor($sorted->count() / 2));
        $secondHalf = $sorted->skip((int) floor($sorted->count() / 2));

        $firstAvg = $firstHalf->avg('metric_value') ?? 0;
        $secondAvg = $secondHalf->avg('metric_value') ?? 0;

        $diff = $secondAvg - $firstAvg;

        if ($diff > 5) {
            return 'improving';
        }
        if ($diff < -5) {
            return 'declining';
        }

        return 'stable';
    }

    protected function summarizeResponses(array $responses): string
    {
        // Simple summary - in production you'd use NLP
        $count = count($responses);

        return "$count responses recorded";
    }

    /**
     * Build a prompt-ready context string from the context array.
     */
    public function buildPromptContext(array $context): string
    {
        $terminology = app(\App\Services\TerminologyService::class);
        $lines = [];

        $entityType = $context['entity_type'] ?? 'unknown';
        $lines[] = $terminology->get('prompt_target_label').': '.ucfirst($entityType);

        if ($entityType === 'participant') {
            $basic = $context['basic_info'] ?? [];
            $lines[] = '- '.$terminology->get('level_label').': '.($basic['level'] ?? $terminology->get('unknown_label'));
            $lines[] = '- '.$terminology->get('risk_level_label').': '.($context['behavioral_profile']['risk_level'] ?? $terminology->get('unknown_label'));

            if (! empty($basic['has_iep'])) {
                $lines[] = '- '.$terminology->get('has_iep_label').': '.$terminology->get('yes_label');
            }
            if (! empty($basic['is_ell'])) {
                $lines[] = '- '.$terminology->get('english_language_participant_label').': '.$terminology->get('yes_label');
            }

            if (! empty($context['improvement_areas'])) {
                $lines[] = "\n### ".$terminology->get('improvement_areas_label').':';
                foreach ($context['improvement_areas'] as $area) {
                    $lines[] = "- $area";
                }
            }

            if (! empty($context['strengths'])) {
                $lines[] = "\n### ".$terminology->get('strengths_label').':';
                foreach ($context['strengths'] as $strength) {
                    $lines[] = "- $strength";
                }
            }
        } elseif ($entityType === 'instructor') {
            $basic = $context['basic_info'] ?? [];
            $lines[] = '- '.$terminology->get('role_label').': '.($basic['role'] ?? $terminology->get('role_instructor_label'));

            if (! empty($context['improvement_areas'])) {
                $lines[] = "\n### ".$terminology->get('professional_development_areas_label').':';
                foreach ($context['improvement_areas'] as $area) {
                    $lines[] = "- $area";
                }
            }

            if (! empty($context['learner_outcomes'])) {
                $lines[] = "\n### ".$terminology->get('participant_outcome_trends_label').':';
                foreach ($context['learner_outcomes'] as $type => $data) {
                    $trend = $data['trend'] ?? $terminology->get('unknown_label');
                    $lines[] = '- '.ucfirst(str_replace('_', ' ', $type)).": $trend";
                }
            }
        } elseif ($entityType === 'department') {
            if (! empty($context['criteria'])) {
                $lines[] = '- '.$terminology->get('department_label').': '.($context['criteria']['department'] ?? $terminology->get('all_label'));
            }

            if (! empty($context['common_challenges'])) {
                $lines[] = "\n### Common Challenges:";
                foreach ($context['common_challenges'] as $challenge) {
                    $lines[] = "- $challenge";
                }
            }

            if (! empty($context['improvement_priorities'])) {
                $lines[] = "\n### Improvement Priorities:";
                foreach ($context['improvement_priorities'] as $priority) {
                    $lines[] = "- $priority";
                }
            }
        }

        return implode("\n", $lines);
    }
}
