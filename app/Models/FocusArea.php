<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class FocusArea extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'strategic_plan_id',
        'title',
        'description',
        'sort_order',
        'status',
    ];

    protected $casts = [
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
     * Get the strategic plan this focus area belongs to.
     */
    public function strategicPlan(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class);
    }

    /**
     * Get all objectives for this focus area.
     */
    public function objectives(): HasMany
    {
        return $this->hasMany(Objective::class)->orderBy('sort_order');
    }

    /**
     * Get all survey assignments for this focus area.
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
     * Calculate status based on child objectives.
     */
    public function calculateStatus(): string
    {
        $objectives = $this->objectives;

        if ($objectives->isEmpty()) {
            return self::STATUS_NOT_STARTED;
        }

        $statuses = $objectives->pluck('status')->toArray();

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
    }

    /**
     * Scope to order by sort_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }
}
