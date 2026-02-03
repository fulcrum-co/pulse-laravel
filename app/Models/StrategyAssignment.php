<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StrategyAssignment extends Model
{
    protected $fillable = [
        'strategic_plan_id',
        'assignable_type',
        'assignable_id',
        'assigned_by',
    ];

    /**
     * Get the strategic plan.
     */
    public function strategicPlan(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class);
    }

    /**
     * Get the assignable entity (User, Department, Classroom, Learner).
     */
    public function assignable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who made this assignment.
     */
    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get a display name for the assignable.
     */
    public function getDisplayNameAttribute(): string
    {
        $assignable = $this->assignable;

        if (! $assignable) {
            return 'Unknown';
        }

        if ($assignable instanceof User) {
            return $assignable->first_name.' '.$assignable->last_name;
        }

        if ($assignable instanceof Learner) {
            return $assignable->user->first_name.' '.$assignable->user->last_name;
        }

        if ($assignable instanceof Department) {
            return $assignable->name;
        }

        if ($assignable instanceof Classroom) {
            return $assignable->name;
        }

        return 'Unknown';
    }
}
