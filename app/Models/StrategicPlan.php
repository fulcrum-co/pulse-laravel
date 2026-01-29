<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StrategicPlan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'org_id',
        'source_plan_id',
        'source_org_id',
        'title',
        'description',
        'plan_type',
        'target_type',
        'target_id',
        'status',
        'start_date',
        'end_date',
        'consultant_visible',
        'settings',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'consultant_visible' => 'boolean',
        'settings' => 'array',
    ];

    /**
     * Plan types.
     */
    public const TYPE_ORGANIZATIONAL = 'organizational';
    public const TYPE_TEACHER = 'teacher';
    public const TYPE_STUDENT = 'student';
    public const TYPE_DEPARTMENT = 'department';
    public const TYPE_GRADE = 'grade';

    /**
     * Plan statuses.
     */
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ARCHIVED = 'archived';

    /**
     * Get the organization this plan belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the source plan (if copied from another plan).
     */
    public function sourcePlan(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class, 'source_plan_id');
    }

    /**
     * Get the source organization (if pushed from upstream).
     */
    public function sourceOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'source_org_id');
    }

    /**
     * Get the user who created this plan.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the target entity for improvement plans.
     */
    public function target(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get all focus areas for this plan.
     */
    public function focusAreas(): HasMany
    {
        return $this->hasMany(FocusArea::class)->orderBy('sort_order');
    }

    /**
     * Get all collaborators for this plan.
     */
    public function collaborators(): HasMany
    {
        return $this->hasMany(StrategyCollaborator::class);
    }

    /**
     * Get all assignments for this plan.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(StrategyAssignment::class);
    }

    /**
     * Get plans copied from this one.
     */
    public function copiedPlans(): HasMany
    {
        return $this->hasMany(StrategicPlan::class, 'source_plan_id');
    }

    /**
     * Check if this is an improvement plan (not organizational).
     */
    public function isImprovementPlan(): bool
    {
        return $this->plan_type !== self::TYPE_ORGANIZATIONAL;
    }

    /**
     * Calculate the overall status based on focus areas.
     */
    public function calculateOverallStatus(): string
    {
        $focusAreas = $this->focusAreas;

        if ($focusAreas->isEmpty()) {
            return 'not_started';
        }

        $statuses = $focusAreas->pluck('status')->toArray();

        if (in_array('off_track', $statuses)) {
            return 'off_track';
        }

        if (in_array('at_risk', $statuses)) {
            return 'at_risk';
        }

        if (in_array('not_started', $statuses)) {
            return 'not_started';
        }

        return 'on_track';
    }

    /**
     * Duplicate this plan within the same organization.
     */
    public function duplicate(): self
    {
        $newPlan = $this->replicate(['id', 'created_at', 'updated_at']);
        $newPlan->title = $this->title . ' (Copy)';
        $newPlan->status = self::STATUS_DRAFT;
        $newPlan->source_plan_id = $this->id;
        $newPlan->save();

        // Duplicate focus areas, objectives, and activities
        foreach ($this->focusAreas as $focusArea) {
            $newFocusArea = $focusArea->replicate(['id', 'created_at', 'updated_at']);
            $newFocusArea->strategic_plan_id = $newPlan->id;
            $newFocusArea->save();

            foreach ($focusArea->objectives as $objective) {
                $newObjective = $objective->replicate(['id', 'created_at', 'updated_at']);
                $newObjective->focus_area_id = $newFocusArea->id;
                $newObjective->save();

                foreach ($objective->activities as $activity) {
                    $newActivity = $activity->replicate(['id', 'created_at', 'updated_at']);
                    $newActivity->objective_id = $newObjective->id;
                    $newActivity->save();
                }
            }
        }

        return $newPlan;
    }

    /**
     * Push this plan to a downstream organization.
     */
    public function pushToOrganization(Organization $targetOrg): self
    {
        $newPlan = $this->replicate(['id', 'created_at', 'updated_at']);
        $newPlan->org_id = $targetOrg->id;
        $newPlan->source_plan_id = $this->id;
        $newPlan->source_org_id = $this->org_id;
        $newPlan->status = self::STATUS_DRAFT;
        $newPlan->save();

        // Duplicate focus areas, objectives, and activities
        foreach ($this->focusAreas as $focusArea) {
            $newFocusArea = $focusArea->replicate(['id', 'created_at', 'updated_at']);
            $newFocusArea->strategic_plan_id = $newPlan->id;
            $newFocusArea->save();

            foreach ($focusArea->objectives as $objective) {
                $newObjective = $objective->replicate(['id', 'created_at', 'updated_at']);
                $newObjective->focus_area_id = $newFocusArea->id;
                $newObjective->save();

                foreach ($objective->activities as $activity) {
                    $newActivity = $activity->replicate(['id', 'created_at', 'updated_at']);
                    $newActivity->objective_id = $newObjective->id;
                    $newActivity->save();
                }
            }
        }

        return $newPlan;
    }

    /**
     * Scope to filter by plan type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('plan_type', $type);
    }

    /**
     * Scope to filter organizational plans only.
     */
    public function scopeOrganizational($query)
    {
        return $query->where('plan_type', self::TYPE_ORGANIZATIONAL);
    }

    /**
     * Scope to filter improvement plans only.
     */
    public function scopeImprovementPlans($query)
    {
        return $query->where('plan_type', '!=', self::TYPE_ORGANIZATIONAL);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter active plans.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
