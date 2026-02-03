<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Classroom;
use App\Models\ContactList;
use App\Models\Learner;
use App\Models\User;
use Illuminate\Support\Collection;

class CourseGenerationContextBuilder
{
    /**
     * Build context for generating a learner-focused course.
     */
    public function buildLearnerContext(Learner $learner): array
    {
        $context = [
            'entity_type' => 'learner',
            'entity_id' => $learner->id,
            'basic_info' => $this->getLearnerBasicInfo($learner),
            'academic_profile' => $this->getLearnerAcademicProfile($learner),
            'behavioral_profile' => $this->getLearnerBehavioralProfile($learner),
            'support_needs' => $this->getLearnerSupportNeeds($learner),
            'recent_data' => $this->getLearnerRecentData($learner),
            'improvement_areas' => $this->identifyLearnerImprovementAreas($learner),
            'strengths' => $this->identifyLearnerStrengths($learner),
        ];

        return $context;
    }

    /**
     * Build context for generating a teacher-focused course.
     */
    public function buildTeacherContext(User $teacher): array
    {
        $context = [
            'entity_type' => 'teacher',
            'entity_id' => $teacher->id,
            'basic_info' => $this->getTeacherBasicInfo($teacher),
            'classroom_data' => $this->getTeacherClassroomData($teacher),
            'learner_outcomes' => $this->getTeacherLearnerOutcomes($teacher),
            'survey_feedback' => $this->getTeacherSurveyFeedback($teacher),
            'observation_data' => $this->getTeacherObservationData($teacher),
            'improvement_areas' => $this->identifyTeacherImprovementAreas($teacher),
            'professional_goals' => $this->getTeacherProfessionalGoals($teacher),
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
            'teacher_data' => $this->getDepartmentTeacherData($orgId, $departmentCriteria),
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
    // STUDENT CONTEXT METHODS
    // ========================================

    protected function getLearnerBasicInfo(Learner $learner): array
    {
        return [
            'grade_level' => $learner->grade_level,
            'enrollment_status' => $learner->enrollment_status,
            'has_iep' => $learner->has_iep ?? false,
            'is_ell' => $learner->is_ell ?? false,
            'is_504' => $learner->is_504 ?? false,
            'tags' => $learner->tags ?? [],
        ];
    }

    protected function getLearnerAcademicProfile(Learner $learner): array
    {
        // Use aggregation pipeline for efficient metric retrieval
        $threeMonthsAgo = now()->subMonths(3)->timestamp;

        $academicMetrics = $learner->metrics()
            ->whereIn('metric_type', ['academic', 'grade', 'assessment'])
            ->where('recorded_at', '>=', now()->subMonths(3))
            ->orderByDesc('recorded_at')
            ->limit(20)
            ->select(['metric_type', 'metric_name', 'metric_value', 'recorded_at'])
            ->get();

        $avgPerformance = $learner->metrics()
            ->whereIn('metric_type', ['academic', 'grade', 'assessment'])
            ->where('recorded_at', '>=', now()->subMonths(3))
            ->raw(function ($collection) {
                return $collection->aggregate([
                    ['$match' => [
                        'metric_type' => ['$in' => ['academic', 'grade', 'assessment']],
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

    protected function getLearnerBehavioralProfile(Learner $learner): array
    {
        // Optimized with column selection and pagination
        $behavioralMetrics = $learner->metrics()
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
            'risk_level' => $learner->risk_level ?? 'unknown',
        ];
    }

    protected function getLearnerSupportNeeds(Learner $learner): array
    {
        $needs = [];

        if ($learner->has_iep) {
            $needs[] = 'IEP accommodations';
        }
        if ($learner->is_ell) {
            $needs[] = 'English language support';
        }
        if ($learner->is_504) {
            $needs[] = '504 accommodations';
        }

        // Get active interventions with column selection optimization
        $enrollments = $learner->enrollments()
            ->with(['course:id,title'])
            ->whereIn('status', ['active', 'in_progress'])
            ->select(['id', 'learner_id', 'course_id', 'status'])
            ->get();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->course) {
                $needs[] = 'Enrolled in: '.$enrollment->course->title;
            }
        }

        return $needs;
    }

    protected function getLearnerRecentData(Learner $learner): array
    {
        // Get recent notes
        $recentNotes = $learner->notes()
            ->where('created_at', '>=', now()->subMonths(1))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Get recent survey responses
        $recentSurveys = $learner->surveyAttempts()
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

    protected function identifyLearnerImprovementAreas(Learner $learner): array
    {
        $areas = [];

        // Based on risk level
        if (in_array($learner->risk_level, ['high', 'critical'])) {
            $areas[] = 'Overall academic support needed';
        }

        // Based on metrics trends
        $recentMetrics = $learner->metrics()
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
        $tags = $learner->tags ?? [];
        foreach ($tags as $tag) {
            if (str_contains(strtolower($tag), 'struggling') ||
                str_contains(strtolower($tag), 'intervention') ||
                str_contains(strtolower($tag), 'support')) {
                $areas[] = $tag;
            }
        }

        return array_unique($areas);
    }

    protected function identifyLearnerStrengths(Learner $learner): array
    {
        $strengths = [];

        // Based on metrics trends
        $recentMetrics = $learner->metrics()
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
        $tags = $learner->tags ?? [];
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
    // TEACHER CONTEXT METHODS
    // ========================================

    protected function getTeacherBasicInfo(User $teacher): array
    {
        return [
            'name' => $teacher->first_name.' '.$teacher->last_name,
            'role' => $teacher->role,
            'department' => $teacher->department ?? null,
        ];
    }

    protected function getTeacherClassroomData(User $teacher): array
    {
        // Use aggregation pipeline for efficient classroom data retrieval
        $classrooms = Classroom::where('teacher_id', $teacher->id)
            ->select(['id', 'name', 'grade_level'])
            ->with(['learners:id,classroom_id,risk_level'])
            ->get();

        return $classrooms->map(function ($classroom) {
            $learners = $classroom->learners ?? collect();
            $highRiskCount = $learners->where('risk_level', 'high')->count();

            return [
                'name' => $classroom->name,
                'grade_level' => $classroom->grade_level,
                'learner_count' => $learners->count(),
                'high_risk_count' => $highRiskCount,
            ];
        })->toArray();
    }

    protected function getTeacherLearnerOutcomes(User $teacher): array
    {
        // Optimized with aggregation pipeline for teacher's classrooms
        $classroomIds = Classroom::where('teacher_id', $teacher->id)
            ->select(['id'])
            ->pluck('id')
            ->toArray();

        if (empty($classroomIds)) {
            return [];
        }

        // Use aggregation pipeline with $lookup for JOIN
        $outcomes = \App\Models\LearnerMetric::raw(function ($collection) use ($classroomIds) {
            return $collection->aggregate([
                ['$lookup' => [
                    'from' => 'classroom_learner',
                    'localField' => 'learner_id',
                    'foreignField' => 'learner_id',
                    'as' => 'enrollment'
                ]],
                ['$match' => [
                    'enrollment.classroom_id' => ['$in' => $classroomIds],
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

    protected function getTeacherSurveyFeedback(User $teacher): array
    {
        // Get survey responses where teacher was the target or respondent
        $attempts = \App\Models\SurveyAttempt::where(function ($q) use ($teacher) {
            $q->where('respondent_id', $teacher->id)
                ->orWhere('respondent_type', 'teacher');
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

    protected function getTeacherObservationData(User $teacher): array
    {
        // Get notes/observations about the teacher
        $notes = \App\Models\ContactNote::where('contact_type', 'user')
            ->where('contact_id', $teacher->id)
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

    protected function identifyTeacherImprovementAreas(User $teacher): array
    {
        $areas = [];

        // Based on learner outcomes
        $outcomes = $this->getTeacherLearnerOutcomes($teacher);
        foreach ($outcomes as $type => $data) {
            if (($data['trend'] ?? '') === 'declining') {
                $areas[] = 'Learner '.str_replace('_', ' ', $type).' declining';
            }
        }

        // Based on survey feedback themes (simplified)
        $feedback = $this->getTeacherSurveyFeedback($teacher);
        // In production, you'd use NLP to extract themes

        return $areas;
    }

    protected function getTeacherProfessionalGoals(User $teacher): array
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
        // Get teachers matching criteria
        $teacherQuery = User::where('org_id', $orgId)
            ->where('role', 'teacher');

        if (! empty($criteria['department'])) {
            $teacherQuery->where('department', $criteria['department']);
        }

        if (! empty($criteria['grade_levels'])) {
            // Filter by classrooms with matching grade levels
            $teacherQuery->whereHas('classrooms', function ($q) use ($criteria) {
                $q->whereIn('grade_level', $criteria['grade_levels']);
            });
        }

        $teacherIds = $teacherQuery->select(['id'])->pluck('id')->toArray();

        if (empty($teacherIds)) {
            return [];
        }

        // Optimized aggregation pipeline for all metrics in one query
        $aggregates = \App\Models\LearnerMetric::raw(function ($collection) use ($teacherIds, $criteria) {
            return $collection->aggregate([
                ['$lookup' => [
                    'from' => 'classroom_learner',
                    'localField' => 'learner_id',
                    'foreignField' => 'learner_id',
                    'as' => 'enrollment'
                ]],
                ['$lookup' => [
                    'from' => 'classrooms',
                    'localField' => 'enrollment.classroom_id',
                    'foreignField' => 'id',
                    'as' => 'classroom'
                ]],
                ['$match' => [
                    'classroom.teacher_id' => ['$in' => $teacherIds],
                    'recorded_at' => ['$gte' => new \MongoDB\BSON\UTCDateTime(now()->subMonths(3)->timestamp * 1000)]
                ]],
                ['$unwind' => '$classroom'],
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

    protected function getDepartmentTeacherData(int $orgId, array $criteria): array
    {
        $teacherQuery = User::where('org_id', $orgId)
            ->where('role', 'teacher');

        if (! empty($criteria['department'])) {
            $teacherQuery->where('department', $criteria['department']);
        }

        return [
            'teacher_count' => $teacherQuery->count(),
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
            if (! empty($list->filter_criteria['grade_levels'])) {
                $characteristics['grade_levels'] = $list->filter_criteria['grade_levels'];
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

        if ($list->list_type === ContactList::TYPE_STUDENT) {
            $learners = $list->learners;

            $iepCount = $learners->where('has_iep', true)->count();
            $ellCount = $learners->where('is_ell', true)->count();
            $highRiskCount = $learners->whereIn('risk_level', ['high', 'critical'])->count();

            if ($iepCount > 0) {
                $needs[] = "$iepCount learners with IEP";
            }
            if ($ellCount > 0) {
                $needs[] = "$ellCount ELL learners";
            }
            if ($highRiskCount > 0) {
                $needs[] = "$highRiskCount high-risk learners";
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
        $lines = [];

        $entityType = $context['entity_type'] ?? 'unknown';
        $lines[] = '## Target: '.ucfirst($entityType);

        if ($entityType === 'learner') {
            $basic = $context['basic_info'] ?? [];
            $lines[] = '- Grade Level: '.($basic['grade_level'] ?? 'Unknown');
            $lines[] = '- Risk Level: '.($context['behavioral_profile']['risk_level'] ?? 'Unknown');

            if (! empty($basic['has_iep'])) {
                $lines[] = '- Has IEP: Yes';
            }
            if (! empty($basic['is_ell'])) {
                $lines[] = '- ELL Learner: Yes';
            }

            if (! empty($context['improvement_areas'])) {
                $lines[] = "\n### Improvement Areas:";
                foreach ($context['improvement_areas'] as $area) {
                    $lines[] = "- $area";
                }
            }

            if (! empty($context['strengths'])) {
                $lines[] = "\n### Strengths:";
                foreach ($context['strengths'] as $strength) {
                    $lines[] = "- $strength";
                }
            }
        } elseif ($entityType === 'teacher') {
            $basic = $context['basic_info'] ?? [];
            $lines[] = '- Role: '.($basic['role'] ?? 'Teacher');

            if (! empty($context['improvement_areas'])) {
                $lines[] = "\n### Areas for Professional Development:";
                foreach ($context['improvement_areas'] as $area) {
                    $lines[] = "- $area";
                }
            }

            if (! empty($context['learner_outcomes'])) {
                $lines[] = "\n### Learner Outcome Trends:";
                foreach ($context['learner_outcomes'] as $type => $data) {
                    $trend = $data['trend'] ?? 'unknown';
                    $lines[] = '- '.ucfirst(str_replace('_', ' ', $type)).": $trend";
                }
            }
        } elseif ($entityType === 'department') {
            if (! empty($context['criteria'])) {
                $lines[] = '- Department: '.($context['criteria']['department'] ?? 'All');
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
