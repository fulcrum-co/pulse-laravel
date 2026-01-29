<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Activity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'objective_id',
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
     * Get the objective this activity belongs to.
     */
    public function objective(): BelongsTo
    {
        return $this->belongsTo(Objective::class);
    }

    /**
     * Get the focus area through objective.
     */
    public function focusArea()
    {
        return $this->objective->focusArea;
    }

    /**
     * Get the strategic plan through objective and focus area.
     */
    public function strategicPlan()
    {
        return $this->objective->focusArea->strategicPlan;
    }

    /**
     * Get all survey assignments for this activity.
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
     * Update status and propagate up the hierarchy.
     */
    public function updateStatus(string $status): void
    {
        $this->status = $status;
        $this->save();

        // Update parent objective status
        $this->objective->updateStatusFromChildren();
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
