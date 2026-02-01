<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class KeyResult extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'goal_id',
        'title',
        'description',
        'metric_type',
        'target_value',
        'current_value',
        'starting_value',
        'unit',
        'due_date',
        'status',
        'sort_order',
        'history',
    ];

    protected $casts = [
        'target_value' => 'decimal:2',
        'current_value' => 'decimal:2',
        'starting_value' => 'decimal:2',
        'due_date' => 'date',
        'sort_order' => 'integer',
        'history' => 'array',
    ];

    /**
     * Metric types.
     */
    public const METRIC_PERCENTAGE = 'percentage';

    public const METRIC_NUMBER = 'number';

    public const METRIC_CURRENCY = 'currency';

    public const METRIC_BOOLEAN = 'boolean';

    public const METRIC_MILESTONE = 'milestone';

    /**
     * Statuses.
     */
    public const STATUS_NOT_STARTED = 'not_started';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_ON_TRACK = 'on_track';

    public const STATUS_AT_RISK = 'at_risk';

    public const STATUS_COMPLETED = 'completed';

    /**
     * Get the goal this key result belongs to.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get progress updates for this key result.
     */
    public function progressUpdates(): HasMany
    {
        return $this->hasMany(ProgressUpdate::class)->orderBy('created_at', 'desc');
    }

    /**
     * Calculate progress percentage.
     */
    public function calculateProgress(): float
    {
        if ($this->metric_type === self::METRIC_BOOLEAN) {
            return $this->current_value >= 1 ? 100 : 0;
        }

        if (! $this->target_value || $this->target_value == 0) {
            return 0;
        }

        // Calculate progress accounting for starting value
        $totalRange = $this->target_value - $this->starting_value;
        if ($totalRange == 0) {
            return $this->current_value >= $this->target_value ? 100 : 0;
        }

        $progress = (($this->current_value - $this->starting_value) / $totalRange) * 100;

        return max(0, min(100, $progress));
    }

    /**
     * Update the current value and track history.
     */
    public function updateValue(float $newValue, ?int $userId = null): void
    {
        $history = $this->history ?? [];
        $history[] = [
            'value' => $this->current_value,
            'changed_to' => $newValue,
            'changed_at' => now()->toDateTimeString(),
            'changed_by' => $userId,
        ];

        $this->current_value = $newValue;
        $this->history = $history;
        $this->updateStatusFromProgress();
        $this->save();

        // Update parent goal status
        $this->goal->updateStatusFromKeyResults();
    }

    /**
     * Update status based on current progress.
     */
    protected function updateStatusFromProgress(): void
    {
        $progress = $this->calculateProgress();

        if ($progress >= 100) {
            $this->status = self::STATUS_COMPLETED;
        } elseif ($progress >= 70) {
            $this->status = self::STATUS_ON_TRACK;
        } elseif ($progress >= 30) {
            $this->status = self::STATUS_IN_PROGRESS;
        } elseif ($progress > 0) {
            $this->status = self::STATUS_AT_RISK;
        } else {
            $this->status = self::STATUS_NOT_STARTED;
        }
    }

    /**
     * Get status color class for display.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_COMPLETED => 'green',
            self::STATUS_ON_TRACK => 'green',
            self::STATUS_IN_PROGRESS => 'blue',
            self::STATUS_AT_RISK => 'yellow',
            self::STATUS_NOT_STARTED => 'gray',
            default => 'gray',
        };
    }

    /**
     * Get formatted current value with unit.
     */
    public function getFormattedValueAttribute(): string
    {
        $value = number_format($this->current_value, $this->metric_type === self::METRIC_PERCENTAGE ? 1 : 0);

        return match ($this->metric_type) {
            self::METRIC_PERCENTAGE => $value.'%',
            self::METRIC_CURRENCY => '$'.$value,
            self::METRIC_BOOLEAN => $this->current_value >= 1 ? 'Yes' : 'No',
            default => $value.($this->unit ? ' '.$this->unit : ''),
        };
    }

    /**
     * Get formatted target value with unit.
     */
    public function getFormattedTargetAttribute(): string
    {
        $value = number_format($this->target_value, $this->metric_type === self::METRIC_PERCENTAGE ? 1 : 0);

        return match ($this->metric_type) {
            self::METRIC_PERCENTAGE => $value.'%',
            self::METRIC_CURRENCY => '$'.$value,
            self::METRIC_BOOLEAN => 'Yes',
            default => $value.($this->unit ? ' '.$this->unit : ''),
        };
    }
}
