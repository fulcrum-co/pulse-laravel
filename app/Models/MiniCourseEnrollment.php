<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MiniCourseEnrollment extends Model
{
    // Enrollment sources
    public const SOURCE_MANUAL = 'manual';

    public const SOURCE_AI_SUGGESTED = 'ai_suggested';

    public const SOURCE_RULE_TRIGGERED = 'rule_triggered';

    public const SOURCE_SELF_ENROLLED = 'self_enrolled';

    // Statuses
    public const STATUS_ENROLLED = 'enrolled';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_PAUSED = 'paused';

    public const STATUS_WITHDRAWN = 'withdrawn';

    protected $fillable = [
        'mini_course_id',
        'mini_course_version_id',
        'participant_id',
        'enrolled_by',
        'enrollment_source',
        'suggestion_id',
        'status',
        'progress_percent',
        'current_step_id',
        'started_at',
        'completed_at',
        'expected_completion_date',
        'notes',
        'feedback',
        'analytics_data',
    ];

    protected $casts = [
        'feedback' => 'array',
        'analytics_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'expected_completion_date' => 'date',
    ];

    protected $attributes = [
        'status' => self::STATUS_ENROLLED,
        'enrollment_source' => self::SOURCE_MANUAL,
        'progress_percent' => 0,
    ];

    /**
     * Get available enrollment sources.
     */
    public static function getEnrollmentSources(): array
    {
        return [
            self::SOURCE_MANUAL => 'Manual',
            self::SOURCE_AI_SUGGESTED => 'AI Suggested',
            self::SOURCE_RULE_TRIGGERED => 'Rule Triggered',
            self::SOURCE_SELF_ENROLLED => 'Self Enrolled',
        ];
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_ENROLLED => 'Enrolled',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_WITHDRAWN => 'Withdrawn',
        ];
    }

    /**
     * The mini-course for this enrollment.
     */
    public function miniCourse(): BelongsTo
    {
        return $this->belongsTo(MiniCourse::class);
    }

    /**
     * The specific version enrolled in.
     */
    public function version(): BelongsTo
    {
        return $this->belongsTo(MiniCourseVersion::class, 'mini_course_version_id');
    }

    /**
     * The participant enrolled.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    /**
     * Who enrolled the participant.
     */
    public function enrolledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'enrolled_by');
    }

    /**
     * The suggestion that led to this enrollment (if any).
     */
    public function suggestion(): BelongsTo
    {
        return $this->belongsTo(MiniCourseSuggestion::class);
    }

    /**
     * Current step.
     */
    public function currentStep(): BelongsTo
    {
        return $this->belongsTo(MiniCourseStep::class, 'current_step_id');
    }

    /**
     * Progress records for each step.
     */
    public function stepProgress(): HasMany
    {
        return $this->hasMany(MiniCourseStepProgress::class, 'enrollment_id');
    }

    /**
     * Scope to active enrollments.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_ENROLLED, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Scope to completed enrollments.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope by status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope by enrollment source.
     */
    public function scopeFromSource(Builder $query, string $source): Builder
    {
        return $query->where('enrollment_source', $source);
    }

    /**
     * Start the course.
     */
    public function start(): void
    {
        if ($this->status === self::STATUS_ENROLLED) {
            $firstStep = $this->miniCourse->steps()->orderBy('sort_order')->first();

            $this->update([
                'status' => self::STATUS_IN_PROGRESS,
                'started_at' => now(),
                'current_step_id' => $firstStep?->id,
            ]);

            // Create progress record for first step
            if ($firstStep) {
                $this->stepProgress()->create([
                    'step_id' => $firstStep->id,
                    'status' => 'in_progress',
                    'started_at' => now(),
                ]);
            }
        }
    }

    /**
     * Complete a step and move to next.
     */
    public function completeStep(MiniCourseStep $step, ?array $responseData = null, ?string $feedback = null): void
    {
        // Update step progress
        $progress = $this->stepProgress()->where('step_id', $step->id)->first();
        if ($progress) {
            $progress->update([
                'status' => 'completed',
                'completed_at' => now(),
                'response_data' => $responseData,
                'feedback_response' => $feedback,
            ]);
        }

        // Move to next step
        $nextStep = $step->next_step;
        if ($nextStep) {
            $this->update(['current_step_id' => $nextStep->id]);

            // Create progress record for next step
            $this->stepProgress()->create([
                'step_id' => $nextStep->id,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);
        }

        // Recalculate progress
        $this->recalculateProgress();

        // Check if course is complete
        if (! $nextStep || $this->progress_percent >= 100) {
            $this->markCompleted();
        }
    }

    /**
     * Recalculate progress percentage.
     */
    public function recalculateProgress(): void
    {
        $totalSteps = $this->miniCourse->steps()->required()->count();
        $completedSteps = $this->stepProgress()
            ->whereIn('step_id', $this->miniCourse->steps()->required()->pluck('id'))
            ->where('status', 'completed')
            ->count();

        $progress = $totalSteps > 0 ? round(($completedSteps / $totalSteps) * 100) : 0;

        $this->update(['progress_percent' => $progress]);
    }

    /**
     * Mark enrollment as completed.
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'progress_percent' => 100,
        ]);
    }

    /**
     * Pause enrollment.
     */
    public function pause(): void
    {
        $this->update(['status' => self::STATUS_PAUSED]);
    }

    /**
     * Resume enrollment.
     */
    public function resume(): void
    {
        if ($this->status === self::STATUS_PAUSED) {
            $this->update(['status' => self::STATUS_IN_PROGRESS]);
        }
    }

    /**
     * Withdraw from enrollment.
     */
    public function withdraw(): void
    {
        $this->update(['status' => self::STATUS_WITHDRAWN]);
    }

    /**
     * Check if enrollment is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_ENROLLED, self::STATUS_IN_PROGRESS]);
    }

    /**
     * Check if enrollment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get time spent on course (in seconds).
     */
    public function getTotalTimeSpentAttribute(): int
    {
        return $this->stepProgress()->sum('time_spent_seconds') ?? 0;
    }

    /**
     * Get formatted time spent.
     */
    public function getFormattedTimeSpentAttribute(): string
    {
        $seconds = $this->total_time_spent;
        $minutes = floor($seconds / 60);
        $hours = floor($minutes / 60);

        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes % 60);
        }

        return sprintf('%dm', $minutes);
    }

    /**
     * Add feedback to enrollment.
     */
    public function addFeedback(string $key, $value): void
    {
        $feedback = $this->feedback ?? [];
        $feedback[$key] = $value;
        $this->update(['feedback' => $feedback]);
    }

    /**
     * Track analytics event.
     */
    public function trackAnalytics(string $event, array $data = []): void
    {
        $analytics = $this->analytics_data ?? [];
        $analytics['events'] = $analytics['events'] ?? [];
        $analytics['events'][] = [
            'event' => $event,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ];
        $this->update(['analytics_data' => $analytics]);
    }
}
