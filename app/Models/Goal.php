<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Goal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'strategic_plan_id',
        'parent_goal_id',
        'title',
        'description',
        'goal_type',
        'target_value',
        'current_value',
        'unit',
        'due_date',
        'status',
        'sort_order',
        'owner_id',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'due_date' => 'date',
        'sort_order' => 'integer',
    ];

    /**
     * Goal types.
     */
    public const TYPE_OBJECTIVE = 'objective';

    public const TYPE_KEY_RESULT = 'key_result';

    public const TYPE_OUTCOME = 'outcome';

    /**
     * Goal statuses.
     */
    public const STATUS_NOT_STARTED = 'not_started';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_AT_RISK = 'at_risk';

    public const STATUS_COMPLETED = 'completed';

    /**
     * Get the strategic plan this goal belongs to.
     */
    public function strategicPlan(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class);
    }

    /**
     * Get the parent goal.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Goal::class, 'parent_goal_id');
    }

    /**
     * Get child goals.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Goal::class, 'parent_goal_id')->orderBy('sort_order');
    }

    /**
     * Get key results for this goal.
     */
    public function keyResults(): HasMany
    {
        return $this->hasMany(KeyResult::class)->orderBy('sort_order');
    }

    /**
     * Get milestones for this goal.
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('due_date');
    }

    /**
     * Get progress updates for this goal.
     */
    public function progressUpdates(): HasMany
    {
        return $this->hasMany(ProgressUpdate::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get the owner of this goal.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Calculate progress percentage for this goal.
     */
    public function calculateProgress(): float
    {
        // If goal has key results, calculate from those
        if ($this->keyResults->isNotEmpty()) {
            return $this->keyResults->avg(fn ($kr) => $kr->calculateProgress());
        }

        // If goal has target/current values, calculate percentage
        if ($this->target_value && $this->target_value > 0) {
            return min(100, ($this->current_value / $this->target_value) * 100);
        }

        // Fall back to status-based progress
        return match ($this->status) {
            self::STATUS_COMPLETED => 100,
            self::STATUS_IN_PROGRESS => 50,
            self::STATUS_AT_RISK => 25,
            default => 0,
        };
    }

    /**
     * Update status based on key results.
     */
    public function updateStatusFromKeyResults(): void
    {
        $krs = $this->keyResults;
        if ($krs->isEmpty()) {
            return;
        }

        $avgProgress = $krs->avg(fn ($kr) => $kr->calculateProgress());
        $statuses = $krs->pluck('status')->toArray();

        if ($avgProgress >= 100) {
            $this->status = self::STATUS_COMPLETED;
        } elseif (in_array('at_risk', $statuses) || in_array('off_track', $statuses)) {
            $this->status = self::STATUS_AT_RISK;
        } elseif ($avgProgress > 0) {
            $this->status = self::STATUS_IN_PROGRESS;
        } else {
            $this->status = self::STATUS_NOT_STARTED;
        }

        $this->save();
    }

    /**
     * Get status color class for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'green',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_AT_RISK => 'yellow',
            self::STATUS_NOT_STARTED => 'gray',
            default => 'gray',
        };
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get top-level goals only (no parent).
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_goal_id');
    }
}
