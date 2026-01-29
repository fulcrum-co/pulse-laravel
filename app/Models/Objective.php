<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Objective extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'focus_area_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'sort_order' => 'integer',
    ];

    /**
     * Status constants.
     */
    public const STATUS_ON_TRACK = 'on_track';
    public const STATUS_AT_RISK = 'at_risk';
    public const STATUS_OFF_TRACK = 'off_track';
    public const STATUS_NOT_STARTED = 'not_started';

    /**
     * Get the focus area this objective belongs to.
     */
    public function focusArea(): BelongsTo
    {
        return $this->belongsTo(FocusArea::class);
    }

    /**
     * Get the strategic plan through focus area.
     */
    public function strategicPlan()
    {
        return $this->focusArea->strategicPlan;
    }

    /**
     * Get all activities for this objective.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class)->orderBy('sort_order');
    }

    /**
     * Get all survey assignments for this objective.
     */
    public function surveyAssignments(): MorphMany
    {
        return $this->morphMany(StrategySurveyAssignment::class, 'assignable');
    }

    /**
     * Get the assigned surveys.
     */
    public function surveys()
    {
        return $this->surveyAssignments()->with('survey')->get()->pluck('survey');
    }

    /**
     * Calculate status based on child activities.
     */
    public function calculateStatus(): string
    {
        $activities = $this->activities;

        if ($activities->isEmpty()) {
            return self::STATUS_NOT_STARTED;
        }

        $statuses = $activities->pluck('status')->toArray();

        if (in_array(self::STATUS_OFF_TRACK, $statuses)) {
            return self::STATUS_OFF_TRACK;
        }

        if (in_array(self::STATUS_AT_RISK, $statuses)) {
            return self::STATUS_AT_RISK;
        }

        if (in_array(self::STATUS_NOT_STARTED, $statuses)) {
            return self::STATUS_NOT_STARTED;
        }

        return self::STATUS_ON_TRACK;
    }

    /**
     * Update status based on children.
     */
    public function updateStatusFromChildren(): void
    {
        $this->status = $this->calculateStatus();
        $this->save();

        // Also update parent focus area
        $this->focusArea->updateStatusFromChildren();
    }

    /**
     * Get the date range as a formatted string.
     */
    public function getDateRangeAttribute(): ?string
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        return $this->start_date->format('n/j/Y') . ' - ' . $this->end_date->format('n/j/Y');
    }

    /**
     * Scope to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
