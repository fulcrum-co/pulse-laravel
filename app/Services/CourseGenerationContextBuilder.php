<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\ContactList;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Collection;

class CourseGenerationContextBuilder
{
    /**
     * Build context for generating a student-focused course.
     */
    public function buildStudentContext(Student $student): array
    {
        $context = [
            'entity_type' => 'student',
            'entity_id' => $student->id,
            'basic_info' => $this->getStudentBasicInfo($student),
            'academic_profile' => $this->getStudentAcademicProfile($student),
            'behavioral_profile' => $this->getStudentBehavioralProfile($student),
            'support_needs' => $this->getStudentSupportNeeds($student),
            'recent_data' => $this->getStudentRecentData($student),
            'improvement_areas' => $this->identifyStudentImprovementAreas($student),
            'strengths' => $this->identifyStudentStrengths($student),
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
            'student_outcomes' => $this->getTeacherStudentOutcomes($teacher),
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

    protected function getStudentBasicInfo(Student $student): array
    {
        return [
            'grade_level' => $student->grade_level,
            'enrollment_status' => $student->enrollment_status,
            'has_iep' => $student->has_iep ?? false,
            'is_ell' => $student->is_ell ?? false,
            'is_504' => $student->is_504 ?? false,
            'tags' => $student->tags ?? [],
        ];
    }

    protected function getStudentAcademicProfile(Student $student): array
    {
        // Get recent academic metrics
        $academicMetrics = $student->metrics()
            ->whereIn('metric_type', ['academic', 'grade', 'assessment'])
            ->where('recorded_at', '>=', now()->subMonths(3))
            ->orderByDesc('recorded_at')
            ->limit(20)
            ->get();

        return [
            'recent_metrics' => $academicMetrics->map(fn ($m) => [
                'type' => $m->metric_type,
                'name' => $m->metric_name,
                'value' => $m->metric_value,
                'date' => $m->recorded_at->format('Y-m-d'),
            ])->toArray(),
            'average_performance' => $academicMetrics->avg('metric_value'),
            'trend' => $this->calculateTrend($academicMetrics),
        ];
    }

    protected function getStudentBehavioralProfile(Student $student): array
    {
        $behavioralMetrics = $student->metrics()
            ->whereIn('metric_type', ['behavioral', 'social_emotional', 'attendance'])
            ->where('recorded_at', '>=', now()->subMonths(3))
            ->orderByDesc('recorded_at')
            ->limit(20)
            ->get();

        return [
            'recent_metrics' => $behavioralMetrics->map(fn ($m) => [
                'type' => $m->metric_type,
                'name' => $m->metric_name,
                'value' => $m->metric_value,
                'date' => $m->recorded_at->format('Y-m-d'),
            ])->toArray(),
            'risk_level' => $student->risk_level ?? 'unknown',
        ];
    }

    protected function getStudentSupportNeeds(Student $student): array
    {
        $needs = [];

        if ($student->has_iep) {
            $needs[] = 'IEP accommodations';
        }
        if ($student->is_ell) {
            $needs[] = 'English language support';
        }
        if ($student->is_504) {
            $needs[] = '504 accommodations';
        }

        // Get active interventions
        $enrollments = $student->enrollments()
            ->with('course')
            ->whereIn('status', ['active', 'in_progress'])
            ->get();

        foreach ($enrollments as $enrollment) {
            if ($enrollment->course) {
                $needs[] = 'Enrolled in: '.$enrollment->course->title;
            }
        }

        return $needs;
    }

    protected function getStudentRecentData(Student $student): array
    {
        // Get recent notes
        $recentNotes = $student->notes()
            ->where('created_at', '>=', now()->subMonths(1))
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Get recent survey responses
        $recentSurveys = $student->surveyAttempts()
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

    protected function identifyStudentImprovementAreas(Student $student): array
    {
        $areas = [];

        // Based on risk level
        if (in_array($student->risk_level, ['high', 'critical'])) {
            $areas[] = 'Overall academic support needed';
        }

        // Based on metrics trends
        $recentMetrics = $student->metrics()
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
        $tags = $student->tags ?? [];
        foreach ($tags as $tag) {
            if (str_contains(strtolower($tag), 'struggling') ||
                str_contains(strtolower($tag), 'intervention') ||
                str_contains(strtolower($tag), 'support')) {
                $areas[] = $tag;
            }
        }

        return array_unique($areas);
    }

    protected function identifyStudentStrengths(Student $student): array
    {
        $strengths = [];

        // Based on metrics trends
        $recentMetrics = $student->metrics()
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
        $tags = $student->tags ?? [];
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
        $classrooms = Classroom::where('teacher_id', $teacher->id)->get();

        return $classrooms->map(function ($classroom) {
            $students = $classroom->students ?? collect();

            return [
                'name' => $classroom->name,
                'grade_level' => $classroom->grade_level,
                'student_count' => $students->count(),
                'high_risk_count' => $students->where('risk_level', 'high')->count(),
            ];
        })->toArray();
    }

    protected function getTeacherStudentOutcomes(User $teacher): array
    {
        // Get aggregate metrics for students in teacher's classrooms
        $classroomIds = Classroom::where('teacher_id', $teacher->id)->pluck('id');

        $studentIds = \DB::table('classroom_student')
            ->whereIn('classroom_id', $classroomIds)
            ->pluck('student_id');

        if ($studentIds->isEmpty()) {
            return [];
        }

        // Get aggregate metrics
        $metrics = \App\Models\StudentMetric::whereIn('student_id', $studentIds)
            ->where('recorded_at', '>=', now()->subMonths(3))
            ->get()
            ->groupBy('metric_type');

        $outcomes = [];
        foreach ($metrics as $type => $typeMetrics) {
            $outcomes[$type] = [
                'average' => $typeMetrics->avg('metric_value'),
                'trend' => $this->calculateTrend($typeMetrics),
                'count' => $typeMetrics->count(),
            ];
        }

        return $outcomes;
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

        // Based on student outcomes
        $outcomes = $this->getTeacherStudentOutcomes($teacher);
        foreach ($outcomes as $type => $data) {
            if (($data['trend'] ?? '') === 'declining') {
                $areas[] = 'Student '.str_replace('_', ' ', $type).' declining';
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

        $teacherIds = $teacherQuery->pluck('id');

        // Get student metrics for those teachers' students
        $classroomIds = Classroom::whereIn('teacher_id', $teacherIds)->pluck('id');
        $studentIds = \DB::table('classroom_student')
            ->whereIn('classroom_id', $classroomIds)
            ->pluck('student_id');

        if ($studentIds->isEmpty()) {
            return [];
        }

        $metrics = \App\Models\StudentMetric::whereIn('student_id', $studentIds)
            ->where('recorded_at', '>=', now()->subMonths(3))
            ->get()
            ->groupBy('metric_type');

        $aggregates = [];
        foreach ($metrics as $type => $typeMetrics) {
            $aggregates[$type] = [
                'average' => round($typeMetrics->avg('metric_value'), 2),
                'min' => $typeMetrics->min('metric_value'),
                'max' => $typeMetrics->max('metric_value'),
                'count' => $typeMetrics->count(),
            ];
        }

        return $aggregates;
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
            $students = $list->students;

            $iepCount = $students->where('has_iep', true)->count();
            $ellCount = $students->where('is_ell', true)->count();
            $highRiskCount = $students->whereIn('risk_level', ['high', 'critical'])->count();

            if ($iepCount > 0) {
                $needs[] = "$iepCount students with IEP";
            }
            if ($ellCount > 0) {
                $needs[] = "$ellCount ELL students";
            }
            if ($highRiskCount > 0) {
                $needs[] = "$highRiskCount high-risk students";
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

        if ($entityType === 'student') {
            $basic = $context['basic_info'] ?? [];
            $lines[] = '- Grade Level: '.($basic['grade_level'] ?? 'Unknown');
            $lines[] = '- Risk Level: '.($context['behavioral_profile']['risk_level'] ?? 'Unknown');

            if (! empty($basic['has_iep'])) {
                $lines[] = '- Has IEP: Yes';
            }
            if (! empty($basic['is_ell'])) {
                $lines[] = '- ELL Student: Yes';
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

            if (! empty($context['student_outcomes'])) {
                $lines[] = "\n### Student Outcome Trends:";
                foreach ($context['student_outcomes'] as $type => $data) {
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
