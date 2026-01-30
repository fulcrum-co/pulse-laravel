<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class MiniCourseStepProgress extends Model
{
    // Statuses
    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_SKIPPED = 'skipped';

    protected $table = 'mini_course_step_progress';

    protected $fillable = [
        'enrollment_id',
        'step_id',
        'status',
        'started_at',
        'completed_at',
        'time_spent_seconds',
        'response_data',
        'feedback_response',
    ];

    protected $casts = [
        'response_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_NOT_STARTED,
        'time_spent_seconds' => 0,
    ];

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_NOT_STARTED => 'Not Started',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_SKIPPED => 'Skipped',
        ];
    }

    /**
     * The enrollment this progress belongs to.
     */
    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(MiniCourseEnrollment::class, 'enrollment_id');
    }

    /**
     * The step this progress is for.
     */
    public function step(): BelongsTo
    {
        return $this->belongsTo(MiniCourseStep::class, 'step_id');
    }

    /**
     * Scope to completed steps.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to in-progress steps.
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope by status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Start the step.
     */
    public function start(): void
    {
        if ($this->status === self::STATUS_NOT_STARTED) {
            $this->update([
                'status' => self::STATUS_IN_PROGRESS,
                'started_at' => now(),
            ]);
        }
    }

    /**
     * Complete the step.
     */
    public function complete(array $responseData = null, string $feedback = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
            'response_data' => $responseData,
            'feedback_response' => $feedback,
        ]);
    }

    /**
     * Skip the step.
     */
    public function skip(): void
    {
        $this->update(['status' => self::STATUS_SKIPPED]);
    }

    /**
     * Check if step is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if step is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if step was skipped.
     */
    public function isSkipped(): bool
    {
        return $this->status === self::STATUS_SKIPPED;
    }

    /**
     * Add time spent (in seconds).
     */
    public function addTimeSpent(int $seconds): void
    {
        $this->increment('time_spent_seconds', $seconds);
    }

    /**
     * Get formatted time spent.
     */
    public function getFormattedTimeSpentAttribute(): string
    {
        $seconds = $this->time_spent_seconds;
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $remainingSeconds);
        }
        return sprintf('%ds', $seconds);
    }

    /**
     * Get duration (time between start and completion).
     */
    public function getDurationAttribute(): ?int
    {
        if ($this->started_at && $this->completed_at) {
            return $this->completed_at->diffInSeconds($this->started_at);
        }
        return null;
    }

    /**
     * Get response value from response data.
     */
    public function getResponse(string $key, $default = null)
    {
        return $this->response_data[$key] ?? $default;
    }

    /**
     * Set response value in response data.
     */
    public function setResponse(string $key, $value): void
    {
        $data = $this->response_data ?? [];
        $data[$key] = $value;
        $this->update(['response_data' => $data]);
    }
}
