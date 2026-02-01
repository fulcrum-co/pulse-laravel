<?php

namespace App\Services;

use App\Models\AdaptiveTrigger;
use App\Models\ContactMetric;
use App\Models\MiniCourse;
use App\Models\MiniCourseEnrollment;
use App\Models\MiniCourseSuggestion;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AdaptiveTriggerService
{
    public function __construct(
        protected ClaudeService $claudeService,
        protected MiniCourseGenerationService $courseGenerationService
    ) {}

    /**
     * Evaluate all active triggers for a student.
     */
    public function evaluateTriggersForStudent(Student $student): array
    {
        $triggers = AdaptiveTrigger::where('org_id', $student->org_id)
            ->where('active', true)
            ->get();

        $signals = $this->gatherInputSignals($student);
        $results = [];

        foreach ($triggers as $trigger) {
            // Check cooldown
            if ($trigger->last_triggered_at && $trigger->last_triggered_at->addHours($trigger->cooldown_hours)->isFuture()) {
                continue;
            }

            // Evaluate conditions
            $conditionsMet = $this->evaluateConditions($trigger->conditions, $signals);

            if ($conditionsMet) {
                // If AI interpretation is enabled, get AI analysis
                $aiAnalysis = null;
                if ($trigger->ai_interpretation_enabled) {
                    $aiAnalysis = $this->getAiInterpretation($trigger, $student, $signals);

                    // AI can veto the trigger if conditions are met but context suggests otherwise
                    if ($aiAnalysis && isset($aiAnalysis['should_proceed']) && ! $aiAnalysis['should_proceed']) {
                        continue;
                    }
                }

                // Execute the trigger action
                $result = $this->executeTrigger($trigger, $student, $signals, $aiAnalysis);
                $results[] = $result;

                // Update trigger stats
                $trigger->update([
                    'last_triggered_at' => now(),
                    'triggered_count' => $trigger->triggered_count + 1,
                ]);
            }
        }

        return $results;
    }

    /**
     * Gather all input signals for a student.
     */
    public function gatherInputSignals(Student $student): array
    {
        $signals = [
            'timestamp' => now()->toISOString(),
            'student_id' => $student->id,
        ];

        // Quantitative signals
        $signals['quantitative'] = $this->gatherQuantitativeSignals($student);

        // Qualitative signals
        $signals['qualitative'] = $this->gatherQualitativeSignals($student);

        // Behavioral signals
        $signals['behavioral'] = $this->gatherBehavioralSignals($student);

        // Explicit signals
        $signals['explicit'] = $this->gatherExplicitSignals($student);

        return $signals;
    }

    /**
     * Gather quantitative signals (metrics, grades, attendance).
     */
    protected function gatherQuantitativeSignals(Student $student): array
    {
        $metrics = $student->metrics()
            ->where('period_start', '>=', now()->subMonths(3))
            ->get();

        $signals = [
            'risk_level' => $student->risk_level,
            'risk_score' => $student->risk_score,
            'grade_level' => $student->grade_level,
        ];

        // Extract specific metrics
        foreach ($metrics as $metric) {
            $key = "{$metric->metric_category}_{$metric->metric_key}";
            $signals[$key] = $metric->numeric_value;
            $signals["{$key}_status"] = $metric->status;
        }

        // Calculate derived metrics
        $gpa = $metrics->where('metric_key', 'gpa')->first();
        $previousGpa = $metrics->where('metric_key', 'gpa')
            ->where('period_start', '<=', now()->subMonths(1))
            ->first();

        if ($gpa && $previousGpa) {
            $signals['gpa_change'] = $gpa->numeric_value - $previousGpa->numeric_value;
        }

        $attendanceMetric = $metrics->where('metric_key', 'attendance_rate')->first();
        if ($attendanceMetric) {
            $signals['attendance_rate_30d'] = $attendanceMetric->numeric_value;
        }

        return $signals;
    }

    /**
     * Gather qualitative signals (notes, survey responses).
     */
    protected function gatherQualitativeSignals(Student $student): array
    {
        $signals = [];

        // Recent notes
        $recentNotes = $student->notes()
            ->where('created_at', '>=', now()->subWeeks(2))
            ->get();

        $signals['recent_notes_count'] = $recentNotes->count();
        $signals['concern_notes_count'] = $recentNotes->where('note_type', 'concern')->count();
        $signals['follow_up_notes_count'] = $recentNotes->where('note_type', 'follow_up')->count();

        // Extract themes from notes (simplified)
        $noteContents = $recentNotes->pluck('content')->filter()->join(' ');
        $signals['note_themes'] = $this->extractThemes($noteContents);

        // Recent survey responses
        $recentSurvey = $student->surveyAttempts()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(1))
            ->with('survey')
            ->latest()
            ->first();

        if ($recentSurvey) {
            $signals['latest_survey_score'] = $recentSurvey->overall_score;
            $signals['latest_survey_date'] = $recentSurvey->completed_at?->toDateString();

            // Parse specific responses
            if ($recentSurvey->responses) {
                foreach ($recentSurvey->responses as $questionId => $response) {
                    $signals["survey_{$questionId}"] = $response;
                }
            }
        }

        return $signals;
    }

    /**
     * Gather behavioral signals (login patterns, resource completion).
     */
    protected function gatherBehavioralSignals(Student $student): array
    {
        $signals = [];

        // Resource assignment completion
        $assignments = $student->resourceAssignments()
            ->where('created_at', '>=', now()->subMonths(1))
            ->get();

        $signals['resource_assignments_count'] = $assignments->count();
        $signals['resources_completed_count'] = $assignments->where('status', 'completed')->count();
        $signals['resources_in_progress_count'] = $assignments->where('status', 'in_progress')->count();

        if ($assignments->count() > 0) {
            $signals['resource_completion_rate'] = ($assignments->where('status', 'completed')->count() / $assignments->count()) * 100;
        }

        // Course enrollments
        $enrollments = $student->miniCourseEnrollments()
            ->where('created_at', '>=', now()->subMonths(1))
            ->get();

        $signals['course_enrollments_count'] = $enrollments->count();
        $signals['courses_completed_count'] = $enrollments->where('status', 'completed')->count();
        $signals['courses_in_progress_count'] = $enrollments->where('status', 'in_progress')->count();

        // Behavioral incidents (if tracked in metrics)
        $behaviorMetrics = $student->metrics()
            ->where('metric_category', ContactMetric::CATEGORY_BEHAVIOR)
            ->where('period_start', '>=', now()->subDays(30))
            ->get();

        $signals['behavior_incidents_30d'] = $behaviorMetrics->sum('numeric_value');

        return $signals;
    }

    /**
     * Gather explicit signals (flags, IEP status, counselor notes).
     */
    protected function gatherExplicitSignals(Student $student): array
    {
        return [
            'iep_status' => $student->iep_status,
            'ell_status' => $student->ell_status,
            'free_reduced_lunch' => $student->free_reduced_lunch,
            'enrollment_status' => $student->enrollment_status,
            'days_since_enrollment' => $student->enrollment_date
                ? now()->diffInDays($student->enrollment_date)
                : null,
            'tags' => $student->tags ?? [],
            'has_counselor' => $student->counselor_user_id !== null,
            'custom_fields' => $student->custom_fields ?? [],
        ];
    }

    /**
     * Evaluate trigger conditions against signals.
     */
    protected function evaluateConditions(?array $conditions, array $signals): bool
    {
        if (empty($conditions)) {
            return false;
        }

        // Flatten signals for easier access
        $flatSignals = $this->flattenSignals($signals);

        // Handle 'all' conditions (AND)
        if (isset($conditions['all'])) {
            foreach ($conditions['all'] as $condition) {
                if (! $this->evaluateSingleCondition($condition, $flatSignals)) {
                    return false;
                }
            }

            return true;
        }

        // Handle 'any' conditions (OR)
        if (isset($conditions['any'])) {
            foreach ($conditions['any'] as $condition) {
                if ($this->evaluateSingleCondition($condition, $flatSignals)) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    /**
     * Evaluate a single condition.
     */
    protected function evaluateSingleCondition(array $condition, array $signals): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? null;
        $value = $condition['value'] ?? null;

        if (! $field || ! $operator) {
            return false;
        }

        // Get the signal value (supports dot notation)
        $signalValue = data_get($signals, $field);

        return match ($operator) {
            'equals' => $signalValue == $value,
            'not_equals' => $signalValue != $value,
            'greater_than' => is_numeric($signalValue) && $signalValue > $value,
            'less_than' => is_numeric($signalValue) && $signalValue < $value,
            'greater_than_or_equals' => is_numeric($signalValue) && $signalValue >= $value,
            'less_than_or_equals' => is_numeric($signalValue) && $signalValue <= $value,
            'contains' => is_string($signalValue) && str_contains($signalValue, $value),
            'contains_any' => is_array($signalValue) && ! empty(array_intersect($signalValue, (array) $value)),
            'is_empty' => empty($signalValue),
            'is_not_empty' => ! empty($signalValue),
            'in' => in_array($signalValue, (array) $value),
            'not_in' => ! in_array($signalValue, (array) $value),
            default => false,
        };
    }

    /**
     * Flatten nested signals array for easier condition evaluation.
     */
    protected function flattenSignals(array $signals, string $prefix = ''): array
    {
        $result = [];

        foreach ($signals as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value) && ! $this->isIndexedArray($value)) {
                $result = array_merge($result, $this->flattenSignals($value, $fullKey));
            } else {
                $result[$fullKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Check if array is indexed (not associative).
     */
    protected function isIndexedArray(array $arr): bool
    {
        return array_keys($arr) === range(0, count($arr) - 1);
    }

    /**
     * Get AI interpretation of the situation.
     */
    protected function getAiInterpretation(AdaptiveTrigger $trigger, Student $student, array $signals): ?array
    {
        $systemPrompt = <<<'PROMPT'
You are analyzing a student's situation to determine if an intervention trigger should proceed.
Consider the full context, not just the individual metrics that triggered the alert.

Return a JSON object with:
- should_proceed: boolean - whether the trigger action should proceed
- confidence: number 0-100 - how confident you are in this decision
- reasoning: string - brief explanation of your analysis
- additional_factors: array - other factors you noticed
- recommended_adjustments: array - any adjustments to the suggested action
PROMPT;

        if ($trigger->ai_prompt_context) {
            $systemPrompt .= "\n\nAdditional context from the trigger configuration:\n".$trigger->ai_prompt_context;
        }

        $response = $this->claudeService->sendMessage(
            "Analyze this situation:\n\nTrigger: {$trigger->name}\nTrigger Type: {$trigger->trigger_type}\n\nSignals:\n".
            json_encode($signals, JSON_PRETTY_PRINT),
            $systemPrompt
        );

        if (! $response['success']) {
            return null;
        }

        // Parse JSON response
        if (preg_match('/\{[\s\S]*\}/', $response['content'], $matches)) {
            try {
                return json_decode($matches[0], true);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Execute a trigger action.
     */
    public function executeTrigger(AdaptiveTrigger $trigger, Student $student, array $signals, ?array $aiAnalysis = null): array
    {
        $result = [
            'trigger_id' => $trigger->id,
            'trigger_name' => $trigger->name,
            'student_id' => $student->id,
            'action' => $trigger->output_action,
            'success' => false,
            'details' => [],
        ];

        try {
            switch ($trigger->output_action) {
                case AdaptiveTrigger::ACTION_SUGGEST_FOR_REVIEW:
                    $result['details'] = $this->createSuggestion($trigger, $student, $signals, $aiAnalysis);
                    $result['success'] = true;
                    break;

                case AdaptiveTrigger::ACTION_AUTO_CREATE:
                    $result['details'] = $this->autoCreateCourse($trigger, $student, $signals);
                    $result['success'] = isset($result['details']['course_id']);
                    break;

                case AdaptiveTrigger::ACTION_AUTO_ENROLL:
                    $result['details'] = $this->autoEnrollStudent($trigger, $student, $signals);
                    $result['success'] = isset($result['details']['enrollment_id']);
                    break;

                case AdaptiveTrigger::ACTION_NOTIFY:
                    $result['details'] = $this->sendNotification($trigger, $student, $signals, $aiAnalysis);
                    $result['success'] = true;
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Trigger execution failed', [
                'trigger_id' => $trigger->id,
                'student_id' => $student->id,
                'error' => $e->getMessage(),
            ]);
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Create a course/provider suggestion for review.
     */
    protected function createSuggestion(AdaptiveTrigger $trigger, Student $student, array $signals, ?array $aiAnalysis): array
    {
        $config = $trigger->output_config ?? [];

        // Find or generate a suitable course
        $suggestion = $this->courseGenerationService->generateCourseSuggestion($student, $signals);

        if (! $suggestion) {
            // No matching course found, create a placeholder suggestion
            $courseTypes = $config['course_types'] ?? [MiniCourse::TYPE_INTERVENTION];

            $matchingCourse = MiniCourse::where('org_id', $student->org_id)
                ->where('status', MiniCourse::STATUS_ACTIVE)
                ->whereIn('course_type', $courseTypes)
                ->first();

            if ($matchingCourse) {
                $suggestion = MiniCourseSuggestion::create([
                    'org_id' => $student->org_id,
                    'contact_type' => Student::class,
                    'contact_id' => $student->id,
                    'mini_course_id' => $matchingCourse->id,
                    'suggestion_source' => MiniCourseSuggestion::SOURCE_RULE_BASED,
                    'relevance_score' => 0.7,
                    'trigger_signals' => $signals,
                    'ai_rationale' => $aiAnalysis['reasoning'] ?? "Triggered by: {$trigger->name}",
                    'status' => MiniCourseSuggestion::STATUS_PENDING,
                ]);
            }
        }

        return [
            'suggestion_id' => $suggestion?->id,
            'course_id' => $suggestion?->mini_course_id,
        ];
    }

    /**
     * Auto-create a new course for the student.
     */
    protected function autoCreateCourse(AdaptiveTrigger $trigger, Student $student, array $signals): array
    {
        $course = $this->courseGenerationService->generateFromContext($student, $signals);

        return [
            'course_id' => $course?->id,
            'course_title' => $course?->title,
        ];
    }

    /**
     * Auto-enroll student in a suitable course.
     */
    protected function autoEnrollStudent(AdaptiveTrigger $trigger, Student $student, array $signals): array
    {
        $config = $trigger->output_config ?? [];
        $courseTags = $config['course_tags'] ?? [];

        // Find a suitable course
        $query = MiniCourse::where('org_id', $student->org_id)
            ->where('status', MiniCourse::STATUS_ACTIVE);

        if ($config['auto_enroll_template_courses'] ?? false) {
            $query->where('is_template', true);
        }

        if (! empty($courseTags)) {
            $query->where(function ($q) use ($courseTags) {
                foreach ($courseTags as $tag) {
                    $q->orWhereJsonContains('target_needs', $tag);
                }
            });
        }

        $course = $query->first();

        if (! $course) {
            return ['error' => 'No suitable course found'];
        }

        // Check if already enrolled
        $existingEnrollment = MiniCourseEnrollment::where('mini_course_id', $course->id)
            ->where('student_id', $student->id)
            ->whereIn('status', [
                MiniCourseEnrollment::STATUS_ENROLLED,
                MiniCourseEnrollment::STATUS_IN_PROGRESS,
            ])
            ->first();

        if ($existingEnrollment) {
            return [
                'enrollment_id' => $existingEnrollment->id,
                'already_enrolled' => true,
            ];
        }

        // Create enrollment
        $enrollment = MiniCourseEnrollment::create([
            'mini_course_id' => $course->id,
            'mini_course_version_id' => $course->current_version_id,
            'student_id' => $student->id,
            'enrollment_source' => MiniCourseEnrollment::SOURCE_RULE_TRIGGERED,
            'status' => MiniCourseEnrollment::STATUS_ENROLLED,
        ]);

        return [
            'enrollment_id' => $enrollment->id,
            'course_id' => $course->id,
            'course_title' => $course->title,
        ];
    }

    /**
     * Send notification about the trigger.
     */
    protected function sendNotification(AdaptiveTrigger $trigger, Student $student, array $signals, ?array $aiAnalysis): array
    {
        $config = $trigger->output_config ?? [];
        $recipients = $config['notification_recipients'] ?? ['counselor'];

        $notifiedUsers = [];

        foreach ($recipients as $recipientType) {
            $user = match ($recipientType) {
                'counselor' => $student->counselor,
                'admin' => User::where('org_id', $student->org_id)
                    ->where('primary_role', 'admin')
                    ->first(),
                'assigned_teacher' => $student->homeroomClassroom?->teacher,
                default => null,
            };

            if ($user) {
                // In a real implementation, this would send an actual notification
                // For now, we'll log it
                Log::info('Trigger notification', [
                    'trigger' => $trigger->name,
                    'student' => $student->full_name,
                    'recipient' => $user->email,
                    'ai_analysis' => $aiAnalysis,
                ]);

                $notifiedUsers[] = $user->id;
            }
        }

        return [
            'notified_users' => $notifiedUsers,
            'notification_sent' => count($notifiedUsers) > 0,
        ];
    }

    /**
     * Extract themes from text (simplified).
     */
    protected function extractThemes(string $text): array
    {
        $themes = [];
        $keywords = [
            'anxiety' => ['anxiety', 'anxious', 'worried', 'nervous', 'stressed'],
            'depression' => ['depression', 'depressed', 'sad', 'hopeless', 'withdrawn'],
            'academic' => ['grades', 'homework', 'studying', 'test', 'failing', 'academic'],
            'behavior' => ['behavior', 'conduct', 'discipline', 'outburst', 'disruptive'],
            'attendance' => ['absence', 'absent', 'tardy', 'late', 'attendance', 'missing'],
            'social' => ['friends', 'bullying', 'isolated', 'social', 'peer'],
            'family' => ['family', 'home', 'parents', 'divorce', 'custody'],
        ];

        $lowerText = strtolower($text);

        foreach ($keywords as $theme => $words) {
            foreach ($words as $word) {
                if (str_contains($lowerText, $word)) {
                    $themes[] = $theme;
                    break;
                }
            }
        }

        return array_unique($themes);
    }

    /**
     * Run triggers for all students in an organization (batch processing).
     */
    public function runBatchTriggerEvaluation(int $orgId, ?array $triggerIds = null): array
    {
        $students = Student::where('org_id', $orgId)
            ->where('enrollment_status', 'active')
            ->get();

        $results = [
            'students_evaluated' => 0,
            'triggers_fired' => 0,
            'errors' => [],
        ];

        foreach ($students as $student) {
            try {
                $studentResults = $this->evaluateTriggersForStudent($student);
                $results['students_evaluated']++;
                $results['triggers_fired'] += count($studentResults);
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'student_id' => $student->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
