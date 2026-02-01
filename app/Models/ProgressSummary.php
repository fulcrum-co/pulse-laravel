<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressSummary extends Model
{
    protected $fillable = [
        'strategic_plan_id',
        'period_type',
        'period_start',
        'period_end',
        'summary',
        'highlights',
        'concerns',
        'recommendations',
        'metrics_snapshot',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'highlights' => 'array',
        'concerns' => 'array',
        'recommendations' => 'array',
        'metrics_snapshot' => 'array',
    ];

    /**
     * Period types.
     */
    public const PERIOD_WEEKLY = 'weekly';

    public const PERIOD_MONTHLY = 'monthly';

    public const PERIOD_QUARTERLY = 'quarterly';

    /**
     * Get the strategic plan this summary belongs to.
     */
    public function strategicPlan(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class);
    }

    /**
     * Get period label for display.
     */
    public function getPeriodLabelAttribute(): string
    {
        return match ($this->period_type) {
            self::PERIOD_WEEKLY => 'Week of '.$this->period_start->format('M j, Y'),
            self::PERIOD_MONTHLY => $this->period_start->format('F Y'),
            self::PERIOD_QUARTERLY => 'Q'.ceil($this->period_start->month / 3).' '.$this->period_start->year,
            default => $this->period_start->format('M j').' - '.$this->period_end->format('M j, Y'),
        };
    }

    /**
     * Check if this is the latest summary for the plan.
     */
    public function isLatest(): bool
    {
        return ! static::where('strategic_plan_id', $this->strategic_plan_id)
            ->where('period_type', $this->period_type)
            ->where('period_end', '>', $this->period_end)
            ->exists();
    }

    /**
     * Scope to get summaries by period type.
     */
    public function scopeOfPeriod($query, string $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    /**
     * Scope to get the latest summary.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('period_end', 'desc');
    }
}
