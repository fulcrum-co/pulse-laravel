<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AdaptiveTrigger;
use App\Models\ContactMetric;
use App\Models\MiniCourse;
use App\Models\MiniCourseEnrollment;
use App\Models\MiniCourseSuggestion;
use App\Models\Learner;
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
     * Evaluate all active triggers for a learner.
     */
    public function evaluateTriggersForLearner(Learner $learner): array
    {
        $triggers = AdaptiveTrigger::query()
            ->where('org_id', $learner->org_id)
            ->where('active', true)
            ->select(['id', 'org_id', 'name', 'active', 'cooldown_hours', 'last_triggered_at', 'triggered_count', 'conditions', 'ai_interpretation_enabled', 'ai_prompt_context', 'output_action', 'output_config'])
            ->get();

        $signals = $this->gatherInputSignals($learner);
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
                    $aiAnalysis = $this->getAiInterpretation($trigger, $learner, $signals);

                    // AI can veto the trigger if conditions are met but context suggests otherwise
                    if ($aiAnalysis && isset($aiAnalysis['should_proceed']) && ! $aiAnalysis['should_proceed']) {
                        continue;
                    }
                }

                // Execute the trigger action
                $result = $this->executeTrigger($trigger, $learner, $signals, $aiAnalysis);
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
     * Gather all input signals for a learner.
     */
    public function gatherInputSignals(Learner $learner): array
    {
        $signals = [
            'timestamp' => now()->toISOString(),
            'learner_id' => $learner->id,
        ];

        // Quantitative signals
        $signals['quantitative'] = $this->gatherQuantitativeSignals($learner);

        // Qualitative signals
        $signals['qualitative'] = $this->gatherQualitativeSignals($learner);

        // Behavioral signals
        $signals['behavioral'] = $this->gatherBehavioralSignals($learner);

        // Explicit signals
        $signals['explicit'] = $this->gatherExplicitSignals($learner);

        return $signals;
    }

    /**
     * Gather quantitative signals (metrics, grades, attendance).
     */
    protected function gatherQuantitativeSignals(Learner $learner): array
    {
        $threeMonthsAgo = now()->subMonths(3);
        $oneMonthAgo = now()->subMonths(1);

        // Fetch all required metrics in a single query with optimized filtering
        $metrics = $learner->metrics()
            ->where('period_start', '>=', $threeMonthsAgo)
            ->select(['id', 'learner_id', 'metric_category', 'metric_key', 'numeric_value', 'status', 'period_start', 'period_end'])
            ->orderByDesc('period_start')
            ->get();

        $signals = [
            'risk_level' => $learner->risk_level,
            'risk_score' => $learner->risk_score,
            'grade_level' => $learner->grade_level,
        ];

        // Index metrics by key for O(1) lookups
        $metricsByKey = [];
        $metricsByKeyAndDate = [];

        // Extract specific metrics with indexing for better performance
        foreach ($metrics as $metric) {
            $key = "{$metric->metric_category}_{$metric->metric_key}";
            // Store the first (most recent) occurrence due to orderByDesc
            if (!isset($metricsByKey[$key])) {
                $signals[$key] = $metric->numeric_value;
                $signals["{$key}_status"] = $metric->status;
                $metricsByKey[$key] = $metric;
            }

            // Index for derived metrics
            $metricsByKeyAndDate[$metric->metric_key][] = $metric;
        }

        // Calculate derived metrics - now O(1) instead of O(n)
        if (isset($metricsByKeyAndDate['gpa']) && count($metricsByKeyAndDate['gpa']) > 0) {
            $gpa = $metricsByKeyAndDate['gpa'][0]; // Most recent

            // Find previous GPA - already filtered in query
            $previousGpa = null;
            foreach ($metricsByKeyAndDate['gpa'] as $metric) {
                if ($metric->period_start <= $oneMonthAgo) {
                    $previousGpa = $metric;
                    break;
                }
            }

            if ($previousGpa) {
                $signals['gpa_change'] = $gpa->numeric_value - $previousGpa->numeric_value;
            }
        }

        if (isset($metricsByKeyAndDate['attendance_rate'])) {
            $attendanceMetric = $metricsByKeyAndDate['attendance_rate'][0];
            $signals['attendance_rate_30d'] = $attendanceMetric->numeric_value;
        }

        return $signals;
    }

    /**
     * Gather qualitative signals (notes, survey responses).
     */
    protected function gatherQualitativeSignals(Learner $learner): array
    {
        $signals = [];
        $twoWeeksAgo = now()->subWeeks(2);
        $oneMonthAgo = now()->subMonths(1);

        // Fetch recent notes with optimized query
        $recentNotes = $learner->notes()
            ->where('created_at', '>=', $twoWeeksAgo)
            ->select(['id', 'learner_id', 'note_type', 'content', 'created_at'])
            ->get();

        // Use collection methods efficiently with pre-fetched data
        $signals['recent_notes_count'] = $recentNotes->count();
        $signals['concern_notes_count'] = $recentNotes->where('note_type', 'concern')->count();
        $signals['follow_up_notes_count'] = $recentNotes->where('note_type', 'follow_up')->count();

        // Extract themes from notes (simplified)
        $noteContents = $recentNotes->pluck('content')->filter()->join(' ');
        $signals['note_themes'] = $this->extractThemes($noteContents);

        // Recent survey responses - eager load survey relationship
        $recentSurvey = $learner->surveyAttempts()
            ->where('status', 'completed')
            ->where('created_at', '>=', $oneMonthAgo)
            ->with('survey')
            ->select(['id', 'learner_id', 'survey_id', 'overall_score', 'responses', 'completed_at', 'created_at', 'status'])
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
    protected function gatherBehavioralSignals(Learner $learner): array
    {
        $signals = [];
        $oneMonthAgo = now()->subMonths(1);
        $thirtyDaysAgo = now()->subDays(30);

        // Optimize: Use select to get only needed columns and defer processing
        $assignments = $learner->resourceAssignments()
            ->where('created_at', '>=', $oneMonthAgo)
            ->select(['id', 'learner_id', 'status', 'created_at'])
            ->get();

        $assignmentCount = $assignments->count();
        $signals['resource_assignments_count'] = $assignmentCount;
        $signals['resources_completed_count'] = $assignments->where('status', 'completed')->count();
        $signals['resources_in_progress_count'] = $assignments->where('status', 'in_progress')->count();

        if ($assignmentCount > 0) {
            $signals['resource_completion_rate'] = ($assignments->where('status', 'completed')->count() / $assignmentCount) * 100;
        }

        // Course enrollments - eager load to prevent additional queries
        $enrollments = $learner->miniCourseEnrollments()
            ->where('created_at', '>=', $oneMonthAgo)
            ->select(['id', 'learner_id', 'mini_course_id', 'status', 'created_at'])
            ->get();

        $enrollmentCount = $enrollments->count();
        $signals['course_enrollments_count'] = $enrollmentCount;
        $signals['courses_completed_count'] = $enrollments->where('status', 'completed')->count();
        $signals['courses_in_progress_count'] = $enrollments->where('status', 'in_progress')->count();

        // Behavioral incidents - fetch behavior metrics separately without N+1
        $behaviorMetrics = $learner->metrics()
            ->where('metric_category', ContactMetric::CATEGORY_BEHAVIOR)
            ->where('period_start', '>=', $thirtyDaysAgo)
            ->select(['id', 'learner_id', 'numeric_value', 'metric_category', 'period_start'])
            ->get();

        $signals['behavior_incidents_30d'] = $behaviorMetrics->sum('numeric_value');

        return $signals;
    }

    /**
     * Gather explicit signals (flags, IEP status, counselor notes).
     */
    protected function gatherExplicitSignals(Learner $learner): array
    {
        return [
            'iep_status' => $learner->iep_status,
            'ell_status' => $learner->ell_status,
            'free_reduced_lunch' => $learner->free_reduced_lunch,
            'enrollment_status' => $learner->enrollment_status,
            'days_since_enrollment' => $learner->enrollment_date
                ? now()->diffInDays($learner->enrollment_date)
                : null,
            'tags' => $learner->tags ?? [],
            'has_counselor' => $learner->counselor_user_id !== null,
            'custom_fields' => $learner->custom_fields ?? [],
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
    protected function getAiInterpretation(AdaptiveTrigger $trigger, Learner $learner, array $signals): ?array
    {
        $systemPrompt = <<<'PROMPT'
You are analyzing a learner's situation to determine if an intervention trigger should proceed.
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
    public function executeTrigger(AdaptiveTrigger $trigger, Learner $learner, array $signals, ?array $aiAnalysis = null): array
    {
        $result = [
            'trigger_id' => $trigger->id,
            'trigger_name' => $trigger->name,
            'learner_id' => $learner->id,
            'action' => $trigger->output_action,
            'success' => false,
            'details' => [],
        ];

        try {
            switch ($trigger->output_action) {
                case AdaptiveTrigger::ACTION_SUGGEST_FOR_REVIEW:
                    $result['details'] = $this->createSuggestion($trigger, $learner, $signals, $aiAnalysis);
                    $result['success'] = true;
                    break;

                case AdaptiveTrigger::ACTION_AUTO_CREATE:
                    $result['details'] = $this->autoCreateCourse($trigger, $learner, $signals);
                    $result['success'] = isset($result['details']['course_id']);
                    break;

                case AdaptiveTrigger::ACTION_AUTO_ENROLL:
                    $result['details'] = $this->autoEnrollLearner($trigger, $learner, $signals);
                    $result['success'] = isset($result['details']['enrollment_id']);
                    break;

                case AdaptiveTrigger::ACTION_NOTIFY:
                    $result['details'] = $this->sendNotification($trigger, $learner, $signals, $aiAnalysis);
                    $result['success'] = true;
                    break;
            }
        } catch (\Exception $e) {
            Log::error('Trigger execution failed', [
                'trigger_id' => $trigger->id,
                'learner_id' => $learner->id,
                'error' => $e->getMessage(),
            ]);
            $result['error'] = $e->getMessage();
        }

        return $result;
    }

    /**
     * Create a course/provider suggestion for review.
     */
    protected function createSuggestion(AdaptiveTrigger $trigger, Learner $learner, array $signals, ?array $aiAnalysis): array
    {
        $config = $trigger->output_config ?? [];

        // Find or generate a suitable course
        $suggestion = $this->courseGenerationService->generateCourseSuggestion($learner, $signals);

        if (! $suggestion) {
            // No matching course found, create a placeholder suggestion
            $courseTypes = $config['course_types'] ?? [MiniCourse::TYPE_INTERVENTION];

            $matchingCourse = MiniCourse::where('org_id', $learner->org_id)
                ->where('status', MiniCourse::STATUS_ACTIVE)
                ->whereIn('course_type', $courseTypes)
                ->first();

            if ($matchingCourse) {
                $suggestion = MiniCourseSuggestion::create([
                    'org_id' => $learner->org_id,
                    'contact_type' => Learner::class,
                    'contact_id' => $learner->id,
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
     * Auto-create a new course for the learner.
     */
    protected function autoCreateCourse(AdaptiveTrigger $trigger, Learner $learner, array $signals): array
    {
        $course = $this->courseGenerationService->generateFromContext($learner, $signals);

        return [
            'course_id' => $course?->id,
            'course_title' => $course?->title,
        ];
    }

    /**
     * Auto-enroll learner in a suitable course.
     */
    protected function autoEnrollLearner(AdaptiveTrigger $trigger, Learner $learner, array $signals): array
    {
        $config = $trigger->output_config ?? [];
        $courseTags = $config['course_tags'] ?? [];

        // Find a suitable course
        $query = MiniCourse::where('org_id', $learner->org_id)
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
            ->where('learner_id', $learner->id)
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
            'learner_id' => $learner->id,
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
    protected function sendNotification(AdaptiveTrigger $trigger, Learner $learner, array $signals, ?array $aiAnalysis): array
    {
        $config = $trigger->output_config ?? [];
        $recipients = $config['notification_recipients'] ?? ['counselor'];

        $notifiedUsers = [];

        foreach ($recipients as $recipientType) {
            $user = match ($recipientType) {
                'counselor' => $learner->counselor,
                'admin' => User::where('org_id', $learner->org_id)
                    ->where('primary_role', 'admin')
                    ->first(),
                'assigned_teacher' => $learner->homeroomClassroom?->teacher,
                default => null,
            };

            if ($user) {
                // In a real implementation, this would send an actual notification
                // For now, we'll log it
                Log::info('Trigger notification', [
                    'trigger' => $trigger->name,
                    'learner' => $learner->full_name,
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
     * Run triggers for all learners in an organization (batch processing).
     */
    public function runBatchTriggerEvaluation(int $orgId, ?array $triggerIds = null): array
    {
        // Optimize: Use select to fetch only required columns for batch processing
        $learners = Learner::where('org_id', $orgId)
            ->where('enrollment_status', 'active')
            ->select([
                'id', 'org_id', 'risk_level', 'risk_score', 'grade_level',
                'iep_status', 'ell_status', 'free_reduced_lunch', 'enrollment_status',
                'enrollment_date', 'tags', 'counselor_user_id', 'custom_fields',
            ])
            ->get();

        $results = [
            'learners_evaluated' => 0,
            'triggers_fired' => 0,
            'errors' => [],
        ];

        foreach ($learners as $learner) {
            try {
                $learnerResults = $this->evaluateTriggersForLearner($learner);
                $results['learners_evaluated']++;
                $results['triggers_fired'] += count($learnerResults);
            } catch (\Exception $e) {
                $results['errors'][] = [
                    'learner_id' => $learner->id,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }
}
