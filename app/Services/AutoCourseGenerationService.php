<?php

namespace App\Services;

use App\Models\MiniCourse;
use App\Models\MiniCourseStep;
use App\Models\CourseApprovalWorkflow;
use App\Models\Student;
use App\Models\User;
use App\Models\ContactList;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

class AutoCourseGenerationService
{
    protected CourseGenerationContextBuilder $contextBuilder;
    protected CourseContentAIService $aiService;

    public function __construct(
        CourseGenerationContextBuilder $contextBuilder,
        CourseContentAIService $aiService
    ) {
        $this->contextBuilder = $contextBuilder;
        $this->aiService = $aiService;
    }

    /**
     * Generate a personalized course for a student.
     */
    public function generateForStudent(
        Student $student,
        string $trigger = MiniCourse::TRIGGER_MANUAL,
        array $signals = [],
        ?int $requestedBy = null
    ): ?MiniCourse {
        try {
            // Build context
            $context = $this->contextBuilder->buildStudentContext($student);
            $promptContext = $this->contextBuilder->buildPromptContext($context);

            // Determine course focus based on context
            $focus = $this->determineStudentCourseFocus($context);

            // Generate course content
            $result = $this->aiService->generateCompleteCourse([
                'topic' => $focus['topic'],
                'audience' => 'students',
                'grade_level' => $this->gradeToRange($student->grade_level),
                'course_type' => $focus['course_type'],
                'duration_minutes' => $focus['duration'],
                'objectives' => $focus['objectives'],
                'additional_context' => $promptContext,
            ]);

            if (!$result['success']) {
                Log::error('Failed to generate course for student', [
                    'student_id' => $student->id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);

                return null;
            }

            // Create the course
            $course = $this->createCourseFromResult(
                $result['course'],
                $student->org_id,
                $requestedBy ?? auth()->id(),
                [
                    'trigger' => $trigger,
                    'target_type' => MiniCourse::TARGET_STUDENT,
                    'target_id' => $student->id,
                    'signals' => $signals,
                ]
            );

            // Handle approval workflow
            $this->handleApprovalWorkflow($course);

            Log::info('Auto-generated course for student', [
                'course_id' => $course->id,
                'student_id' => $student->id,
                'trigger' => $trigger,
            ]);

            return $course;
        } catch (\Exception $e) {
            Log::error('Exception generating course for student', [
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate a personalized course for a teacher.
     */
    public function generateForTeacher(
        User $teacher,
        string $trigger = MiniCourse::TRIGGER_MANUAL,
        array $signals = [],
        ?int $requestedBy = null
    ): ?MiniCourse {
        try {
            // Build context
            $context = $this->contextBuilder->buildTeacherContext($teacher);
            $promptContext = $this->contextBuilder->buildPromptContext($context);

            // Determine course focus
            $focus = $this->determineTeacherCourseFocus($context);

            // Generate course content
            $result = $this->aiService->generateCompleteCourse([
                'topic' => $focus['topic'],
                'audience' => 'teachers',
                'course_type' => $focus['course_type'],
                'duration_minutes' => $focus['duration'],
                'objectives' => $focus['objectives'],
                'additional_context' => $promptContext,
            ]);

            if (!$result['success']) {
                Log::error('Failed to generate course for teacher', [
                    'teacher_id' => $teacher->id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);

                return null;
            }

            // Create the course
            $course = $this->createCourseFromResult(
                $result['course'],
                $teacher->org_id,
                $requestedBy ?? auth()->id(),
                [
                    'trigger' => $trigger,
                    'target_type' => MiniCourse::TARGET_TEACHER,
                    'target_id' => $teacher->id,
                    'signals' => $signals,
                ]
            );

            // Handle approval workflow
            $this->handleApprovalWorkflow($course);

            Log::info('Auto-generated course for teacher', [
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
                'trigger' => $trigger,
            ]);

            return $course;
        } catch (\Exception $e) {
            Log::error('Exception generating course for teacher', [
                'teacher_id' => $teacher->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate a course for a department.
     */
    public function generateForDepartment(
        int $orgId,
        array $criteria,
        string $trigger = MiniCourse::TRIGGER_MANUAL,
        ?int $requestedBy = null
    ): ?MiniCourse {
        try {
            // Build context
            $context = $this->contextBuilder->buildDepartmentContext($orgId, $criteria);
            $promptContext = $this->contextBuilder->buildPromptContext($context);

            // Determine course focus
            $focus = $this->determineDepartmentCourseFocus($context, $criteria);

            // Generate course content
            $result = $this->aiService->generateCompleteCourse([
                'topic' => $focus['topic'],
                'audience' => 'teachers',
                'course_type' => $focus['course_type'],
                'duration_minutes' => $focus['duration'],
                'objectives' => $focus['objectives'],
                'additional_context' => $promptContext,
            ]);

            if (!$result['success']) {
                Log::error('Failed to generate course for department', [
                    'org_id' => $orgId,
                    'criteria' => $criteria,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);

                return null;
            }

            // Create the course
            $course = $this->createCourseFromResult(
                $result['course'],
                $orgId,
                $requestedBy ?? auth()->id(),
                [
                    'trigger' => $trigger,
                    'target_type' => MiniCourse::TARGET_DEPARTMENT,
                    'target_id' => null,
                    'signals' => ['criteria' => $criteria],
                ]
            );

            // Handle approval workflow
            $this->handleApprovalWorkflow($course);

            Log::info('Auto-generated course for department', [
                'course_id' => $course->id,
                'org_id' => $orgId,
                'criteria' => $criteria,
            ]);

            return $course;
        } catch (\Exception $e) {
            Log::error('Exception generating course for department', [
                'org_id' => $orgId,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Generate courses for all members of a contact list.
     */
    public function generateForContactList(
        ContactList $list,
        string $trigger = MiniCourse::TRIGGER_MANUAL,
        ?int $requestedBy = null
    ): array {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        $members = $list->getAllMembers();

        foreach ($members as $member) {
            $course = null;

            if ($member instanceof Student) {
                $course = $this->generateForStudent(
                    $member,
                    $trigger,
                    ['contact_list_id' => $list->id],
                    $requestedBy
                );
            } elseif ($member instanceof User) {
                $course = $this->generateForTeacher(
                    $member,
                    $trigger,
                    ['contact_list_id' => $list->id],
                    $requestedBy
                );
            }

            if ($course) {
                $results['success'][] = [
                    'member_type' => get_class($member),
                    'member_id' => $member->id,
                    'course_id' => $course->id,
                ];
            } else {
                $results['failed'][] = [
                    'member_type' => get_class($member),
                    'member_id' => $member->id,
                ];
            }
        }

        Log::info('Batch course generation for contact list completed', [
            'list_id' => $list->id,
            'success_count' => count($results['success']),
            'failed_count' => count($results['failed']),
        ]);

        return $results;
    }

    /**
     * Run batch generation for an organization.
     */
    public function runBatchGeneration(int $orgId, array $options = []): array
    {
        $results = [
            'students' => ['success' => 0, 'failed' => 0],
            'teachers' => ['success' => 0, 'failed' => 0],
        ];

        $settings = $this->getOrgSettings($orgId);
        $maxCourses = $options['max_courses'] ?? $settings['max_auto_courses_per_day'] ?? 10;
        $coursesCreated = 0;

        // Generate for high-risk students
        if ($options['include_students'] ?? true) {
            $students = Student::where('org_id', $orgId)
                ->whereIn('risk_level', ['high', 'critical'])
                ->whereDoesntHave('enrollments', function ($q) {
                    $q->whereIn('status', ['active', 'in_progress'])
                      ->where('created_at', '>=', now()->subDays(30));
                })
                ->limit($maxCourses - $coursesCreated)
                ->get();

            foreach ($students as $student) {
                if ($coursesCreated >= $maxCourses) {
                    break;
                }

                $course = $this->generateForStudent(
                    $student,
                    MiniCourse::TRIGGER_SCHEDULED,
                    ['batch_run' => true]
                );

                if ($course) {
                    $results['students']['success']++;
                    $coursesCreated++;
                } else {
                    $results['students']['failed']++;
                }
            }
        }

        // Generate for teachers with declining student outcomes
        if (($options['include_teachers'] ?? true) && $coursesCreated < $maxCourses) {
            $teachers = User::where('org_id', $orgId)
                ->where('role', 'teacher')
                ->whereDoesntHave('enrollments', function ($q) {
                    $q->whereIn('status', ['active', 'in_progress'])
                      ->where('created_at', '>=', now()->subDays(30));
                })
                ->limit($maxCourses - $coursesCreated)
                ->get();

            foreach ($teachers as $teacher) {
                if ($coursesCreated >= $maxCourses) {
                    break;
                }

                // Check if teacher has declining student outcomes
                $context = $this->contextBuilder->buildTeacherContext($teacher);
                $hasDecline = collect($context['student_outcomes'] ?? [])
                    ->contains(fn ($o) => ($o['trend'] ?? '') === 'declining');

                if (!$hasDecline) {
                    continue;
                }

                $course = $this->generateForTeacher(
                    $teacher,
                    MiniCourse::TRIGGER_SCHEDULED,
                    ['batch_run' => true]
                );

                if ($course) {
                    $results['teachers']['success']++;
                    $coursesCreated++;
                } else {
                    $results['teachers']['failed']++;
                }
            }
        }

        Log::info('Batch course generation completed', [
            'org_id' => $orgId,
            'results' => $results,
        ]);

        return $results;
    }

    /**
     * Handle a real-time signal trigger.
     */
    public function handleSignalTrigger(string $signalType, $entity, array $data): void
    {
        $settings = $this->getOrgSettings($entity->org_id ?? $entity->org_id);

        if (!($settings['auto_generate_enabled'] ?? false)) {
            return;
        }

        $triggers = $settings['generation_triggers'] ?? [];
        if (!in_array($signalType, $triggers)) {
            return;
        }

        switch ($signalType) {
            case 'risk_level_change':
                if ($entity instanceof Student && in_array($entity->risk_level, ['high', 'critical'])) {
                    $this->generateForStudent($entity, MiniCourse::TRIGGER_SIGNAL, [
                        'signal_type' => $signalType,
                        'signal_data' => $data,
                    ]);
                }
                break;

            case 'survey_completed':
                // Generate course based on survey results
                if (isset($data['student_id'])) {
                    $student = Student::find($data['student_id']);
                    if ($student) {
                        $this->generateForStudent($student, MiniCourse::TRIGGER_SIGNAL, [
                            'signal_type' => $signalType,
                            'survey_id' => $data['survey_id'] ?? null,
                        ]);
                    }
                }
                break;

            case 'metric_threshold':
                // Generate course when metric drops below threshold
                if ($entity instanceof Student) {
                    $this->generateForStudent($entity, MiniCourse::TRIGGER_SIGNAL, [
                        'signal_type' => $signalType,
                        'signal_data' => $data,
                    ]);
                }
                break;

            default:
                Log::info('Unhandled signal type for course generation', [
                    'signal_type' => $signalType,
                ]);
        }
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Create a MiniCourse from AI generation result.
     */
    protected function createCourseFromResult(
        array $courseData,
        int $orgId,
        ?int $createdBy,
        array $autoGenData
    ): MiniCourse {
        $course = MiniCourse::create([
            'org_id' => $orgId,
            'title' => $courseData['title'] ?? 'AI-Generated Course',
            'description' => $courseData['description'] ?? '',
            'objectives' => $courseData['objectives'] ?? [],
            'rationale' => $courseData['rationale'] ?? null,
            'expected_experience' => $courseData['expected_experience'] ?? null,
            'course_type' => $courseData['course_type'] ?? MiniCourse::TYPE_INTERVENTION,
            'creation_source' => MiniCourse::SOURCE_AI_GENERATED,
            'ai_generation_context' => $courseData,
            'target_grades' => $courseData['target_grades'] ?? [],
            'target_needs' => $courseData['target_needs'] ?? [],
            'estimated_duration_minutes' => $courseData['estimated_duration_minutes'] ?? 30,
            'status' => MiniCourse::STATUS_DRAFT,
            'created_by' => $createdBy,
            // Auto-generation fields
            'generation_trigger' => $autoGenData['trigger'],
            'target_entity_type' => $autoGenData['target_type'],
            'target_entity_id' => $autoGenData['target_id'],
            'generation_signals' => $autoGenData['signals'] ?? [],
            'auto_generated_at' => now(),
            'approval_status' => MiniCourse::APPROVAL_PENDING,
        ]);

        // Create steps
        if (!empty($courseData['steps'])) {
            foreach ($courseData['steps'] as $index => $stepData) {
                $course->steps()->create([
                    'sort_order' => $index + 1,
                    'step_type' => $stepData['step_type'] ?? MiniCourseStep::TYPE_CONTENT,
                    'title' => $stepData['title'] ?? 'Step ' . ($index + 1),
                    'description' => $stepData['description'] ?? null,
                    'instructions' => $stepData['instructions'] ?? null,
                    'content_type' => $stepData['content_type'] ?? MiniCourseStep::CONTENT_TEXT,
                    'content_data' => $stepData['content_data'] ?? [],
                    'estimated_duration_minutes' => $stepData['duration'] ?? 5,
                    'is_required' => $stepData['is_required'] ?? true,
                    'feedback_prompt' => $stepData['feedback_prompt'] ?? null,
                ]);
            }
        }

        return $course;
    }

    /**
     * Handle the approval workflow based on org settings.
     */
    protected function handleApprovalWorkflow(MiniCourse $course): void
    {
        $settings = $this->getOrgSettings($course->org_id);
        $mode = $settings['approval_mode'] ?? CourseApprovalWorkflow::MODE_CREATE_APPROVE;

        if ($mode === CourseApprovalWorkflow::MODE_AUTO_ACTIVATE) {
            // Auto-activate: publish immediately
            $course->update([
                'status' => MiniCourse::STATUS_ACTIVE,
                'approval_status' => MiniCourse::APPROVAL_APPROVED,
                'approved_at' => now(),
                'published_at' => now(),
            ]);
        } else {
            // Create approval workflow record
            CourseApprovalWorkflow::create([
                'mini_course_id' => $course->id,
                'status' => CourseApprovalWorkflow::STATUS_PENDING,
                'workflow_mode' => $mode,
                'submitted_at' => now(),
            ]);

            // Send notification to reviewers
            $this->notifyReviewers($course, $settings);
        }
    }

    /**
     * Notify reviewers about pending course.
     */
    protected function notifyReviewers(MiniCourse $course, array $settings): void
    {
        $recipients = $settings['notification_recipients'] ?? ['admin'];

        // In production, dispatch notification job
        Log::info('Course pending review notification', [
            'course_id' => $course->id,
            'recipients' => $recipients,
        ]);
    }

    /**
     * Get organization settings for AI courses.
     */
    protected function getOrgSettings(int $orgId): array
    {
        $org = Organization::find($orgId);

        if (!$org) {
            return $this->getDefaultSettings();
        }

        $settings = $org->settings ?? [];

        return $settings['ai_course_settings'] ?? $this->getDefaultSettings();
    }

    /**
     * Get default AI course settings.
     */
    protected function getDefaultSettings(): array
    {
        return [
            'approval_mode' => CourseApprovalWorkflow::MODE_CREATE_APPROVE,
            'auto_generate_enabled' => false,
            'generation_triggers' => ['manual'],
            'notification_recipients' => ['admin'],
            'max_auto_courses_per_day' => 10,
            'require_review_for_ai_generated' => true,
        ];
    }

    /**
     * Determine course focus for a student based on context.
     */
    protected function determineStudentCourseFocus(array $context): array
    {
        $improvementAreas = $context['improvement_areas'] ?? [];
        $riskLevel = $context['behavioral_profile']['risk_level'] ?? 'unknown';
        $basicInfo = $context['basic_info'] ?? [];

        // Default focus
        $topic = 'Building Academic Success Skills';
        $courseType = MiniCourse::TYPE_SKILL_BUILDING;
        $objectives = ['Develop effective study habits', 'Build self-confidence'];
        $duration = 30;

        // Customize based on needs
        if (in_array($riskLevel, ['high', 'critical'])) {
            $topic = 'Getting Back on Track: A Personal Success Plan';
            $courseType = MiniCourse::TYPE_INTERVENTION;
            $objectives = [
                'Identify personal barriers to success',
                'Create actionable improvement goals',
                'Build resilience and coping strategies',
            ];
            $duration = 45;
        } elseif ($basicInfo['has_iep'] ?? false) {
            $topic = 'Learning Strategies for Your Success';
            $courseType = MiniCourse::TYPE_SKILL_BUILDING;
            $objectives = [
                'Understand your learning style',
                'Apply personalized study techniques',
                'Advocate for your learning needs',
            ];
        } elseif ($basicInfo['is_ell'] ?? false) {
            $topic = 'Building Language and Academic Confidence';
            $courseType = MiniCourse::TYPE_ACADEMIC;
            $objectives = [
                'Strengthen academic vocabulary',
                'Improve reading comprehension strategies',
                'Build confidence in classroom participation',
            ];
        }

        // Check for specific improvement areas
        foreach ($improvementAreas as $area) {
            if (str_contains(strtolower($area), 'behavioral')) {
                $topic = 'Managing Emotions and Building Positive Habits';
                $courseType = MiniCourse::TYPE_BEHAVIORAL;
                $objectives = [
                    'Recognize emotional triggers',
                    'Practice self-regulation techniques',
                    'Build positive relationships',
                ];
                break;
            }
            if (str_contains(strtolower($area), 'attendance')) {
                $topic = 'Making School Matter: Building Your Path to Success';
                $courseType = MiniCourse::TYPE_INTERVENTION;
                $objectives = [
                    'Understand the value of consistent attendance',
                    'Identify and address barriers to attendance',
                    'Create a personal attendance improvement plan',
                ];
                break;
            }
        }

        return [
            'topic' => $topic,
            'course_type' => $courseType,
            'objectives' => $objectives,
            'duration' => $duration,
        ];
    }

    /**
     * Determine course focus for a teacher based on context.
     */
    protected function determineTeacherCourseFocus(array $context): array
    {
        $improvementAreas = $context['improvement_areas'] ?? [];
        $studentOutcomes = $context['student_outcomes'] ?? [];

        // Default focus
        $topic = 'Enhancing Student Engagement and Achievement';
        $courseType = MiniCourse::TYPE_SKILL_BUILDING;
        $objectives = [
            'Implement evidence-based engagement strategies',
            'Differentiate instruction for diverse learners',
            'Use data to drive instructional decisions',
        ];
        $duration = 45;

        // Customize based on student outcomes
        foreach ($studentOutcomes as $type => $data) {
            if (($data['trend'] ?? '') === 'declining') {
                if (str_contains($type, 'behavioral') || str_contains($type, 'social')) {
                    $topic = 'Building a Positive Classroom Environment';
                    $courseType = MiniCourse::TYPE_BEHAVIORAL;
                    $objectives = [
                        'Implement proactive classroom management strategies',
                        'Build positive student-teacher relationships',
                        'Address challenging behaviors constructively',
                    ];
                    break;
                }
                if (str_contains($type, 'academic') || str_contains($type, 'grade')) {
                    $topic = 'Strategies for Improving Student Academic Performance';
                    $courseType = MiniCourse::TYPE_ACADEMIC;
                    $objectives = [
                        'Identify root causes of academic struggles',
                        'Implement targeted intervention strategies',
                        'Monitor and adjust instruction based on data',
                    ];
                    break;
                }
            }
        }

        return [
            'topic' => $topic,
            'course_type' => $courseType,
            'objectives' => $objectives,
            'duration' => $duration,
        ];
    }

    /**
     * Determine course focus for a department.
     */
    protected function determineDepartmentCourseFocus(array $context, array $criteria): array
    {
        $challenges = $context['common_challenges'] ?? [];
        $priorities = $context['improvement_priorities'] ?? [];

        $department = $criteria['department'] ?? 'General';

        // Default focus
        $topic = "$department Department: Building Excellence Together";
        $courseType = MiniCourse::TYPE_SKILL_BUILDING;
        $objectives = [
            'Align on departmental goals and priorities',
            'Share best practices across the team',
            'Develop collaborative improvement strategies',
        ];
        $duration = 60;

        // Customize based on challenges
        if (!empty($challenges)) {
            $primaryChallenge = $challenges[0] ?? '';

            if (str_contains(strtolower($primaryChallenge), 'behavioral')) {
                $topic = "$department Department: Collaborative Behavior Management";
                $courseType = MiniCourse::TYPE_BEHAVIORAL;
                $objectives = [
                    'Establish consistent behavior expectations',
                    'Implement department-wide PBIS strategies',
                    'Support each other with challenging students',
                ];
            } elseif (str_contains(strtolower($primaryChallenge), 'academic')) {
                $topic = "$department Department: Raising Academic Standards Together";
                $courseType = MiniCourse::TYPE_ACADEMIC;
                $objectives = [
                    'Analyze student performance data as a team',
                    'Develop targeted intervention strategies',
                    'Monitor progress and adjust approaches',
                ];
            }
        }

        return [
            'topic' => $topic,
            'course_type' => $courseType,
            'objectives' => $objectives,
            'duration' => $duration,
        ];
    }

    /**
     * Convert grade level to grade range string.
     */
    protected function gradeToRange(?int $grade): ?string
    {
        if ($grade === null) {
            return null;
        }

        if ($grade <= 2) {
            return 'K-2';
        }
        if ($grade <= 5) {
            return '3-5';
        }
        if ($grade <= 8) {
            return '6-8';
        }

        return '9-12';
    }
}
