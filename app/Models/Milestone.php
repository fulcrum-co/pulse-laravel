<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'strategic_plan_id',
        'goal_id',
        'title',
        'description',
        'due_date',
        'status',
        'completed_at',
        'completed_by',
        'sort_order',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completed_at' => 'datetime',
        'sort_order' => 'integer',
    ];

    /**
     * Milestone statuses.
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_MISSED = 'missed';

    /**
     * Get the strategic plan this milestone belongs to.
     */
    public function strategicPlan(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class);
    }

    /**
     * Get the goal this milestone is associated with.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the user who completed this milestone.
     */
    public function completedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    /**
     * Get progress updates for this milestone.
     */
    public function progressUpdates(): HasMany
    {
        return $this->hasMany(ProgressUpdate::class);
    }

    /**
     * Mark milestone as complete.
     */
    public function markComplete(int $userId): void
    {
        $this->status = self::STATUS_COMPLETED;
        $this->completed_at = now();
        $this->completed_by = $userId;
        $this->save();
    }

    /**
     * Mark milestone as missed.
     */
    public function markMissed(): void
    {
        $this->status = self::STATUS_MISSED;
        $this->save();
    }

    /**
     * Check if milestone is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date->isPast() &&
               ! in_array($this->status, [self::STATUS_COMPLETED]);
    }

    /**
     * Check if milestone is due soon (within days).
     */
    public function isDueSoon(int $days = 7): bool
    {
        return ! $this->due_date->isPast() &&
               $this->due_date->isBefore(now()->addDays($days)) &&
               $this->status !== self::STATUS_COMPLETED;
    }

    /**
     * Get status color class for display.
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->isOverdue()) {
            return 'red';
        }

        return match ($this->status) {
            self::STATUS_COMPLETED => 'green',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_MISSED => 'red',
            self::STATUS_PENDING => 'gray',
            default => 'gray',
        };
    }

    /**
     * Scope to get upcoming milestones.
     */
    public function scopeUpcoming($query, int $days = 14)
    {
        return $query->where('status', '!=', self::STATUS_COMPLETED)
            ->whereBetween('due_date', [now(), now()->addDays($days)])
            ->orderBy('due_date');
    }

    /**
     * Scope to get overdue milestones.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', self::STATUS_COMPLETED)
            ->where('due_date', '<', now())
            ->orderBy('due_date');
    }

    /**
     * Scope to get completed milestones.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED)
            ->orderBy('completed_at', 'desc');
    }
}
