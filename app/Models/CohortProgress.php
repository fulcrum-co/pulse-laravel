<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CohortProgress extends Model
{
    protected $table = 'cohort_progress';

    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_SKIPPED = 'skipped';

    protected $fillable = [
        'cohort_member_id',
        'mini_course_step_id',
        'status',
        'time_spent_seconds',
        'response_data',
        'score',
        'feedback_response',
        'started_at',
        'completed_at',
        'attempts',
    ];

    protected $casts = [
        'time_spent_seconds' => 'integer',
        'response_data' => 'array',
        'score' => 'decimal:2',
        'feedback_response' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'attempts' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_NOT_STARTED,
        'time_spent_seconds' => 0,
        'attempts' => 0,
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(CohortMember::class, 'cohort_member_id');
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(MiniCourseStep::class, 'mini_course_step_id');
    }

    // Scopes
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeNotStarted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_NOT_STARTED);
    }

    public function scopeForMember(Builder $query, int $memberId): Builder
    {
        return $query->where('cohort_member_id', $memberId);
    }

    // Helper methods
    public static function getStatusOptions(): array
    {
        return [
            self::STATUS_NOT_STARTED => 'Not Started',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_SKIPPED => 'Skipped',
        ];
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function start(): void
    {
        if ($this->status === self::STATUS_NOT_STARTED) {
            $this->update([
                'status' => self::STATUS_IN_PROGRESS,
                'started_at' => now(),
                'attempts' => $this->attempts + 1,
            ]);
        }
    }

    public function complete(array $responseData = null, float $score = null): void
    {
        $data = [
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ];

        if ($responseData !== null) {
            $data['response_data'] = $responseData;
        }

        if ($score !== null) {
            $data['score'] = $score;
        }

        $this->update($data);

        // Update parent member's progress
        $this->member->updateProgress();
    }

    public function skip(): void
    {
        $this->update(['status' => self::STATUS_SKIPPED]);
        $this->member->updateProgress();
    }

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
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        }

        return "{$minutes}m";
    }
}
