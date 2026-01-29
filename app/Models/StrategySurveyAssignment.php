<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StrategySurveyAssignment extends Model
{
    protected $fillable = [
        'survey_id',
        'assignable_type',
        'assignable_id',
    ];

    /**
     * Get the survey.
     */
    public function survey(): BelongsTo
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Get the assignable entity (FocusArea, Objective, or Activity).
     */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the strategic plan this assignment belongs to.
     */
    public function getStrategicPlanAttribute()
    {
        $assignable = $this->assignable;

        if ($assignable instanceof FocusArea) {
            return $assignable->strategicPlan;
        }

        if ($assignable instanceof Objective) {
            return $assignable->focusArea->strategicPlan;
        }

        if ($assignable instanceof Activity) {
            return $assignable->objective->focusArea->strategicPlan;
        }

        return null;
    }
}
