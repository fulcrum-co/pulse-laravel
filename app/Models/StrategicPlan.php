<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'category',
        'target_type',
        'target_id',
        'status',
        'start_date',
        'end_date',
        'consultant_visible',
        'settings',
        'metadata',
        'created_by',
        'manager_id',
        'trigger_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'consultant_visible' => 'boolean',
        'settings' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Plan types.
     */
    public const TYPE_ORGANIZATIONAL = 'organizational';

    public const TYPE_TEACHER = 'teacher';

    public const TYPE_STUDENT = 'learner';

    public const TYPE_DEPARTMENT = 'department';

    public const TYPE_GRADE = 'grade';

    // New OKR-style plan types
    public const TYPE_IMPROVEMENT = 'improvement';  // Performance Improvement Plans (PIPs)

    public const TYPE_GROWTH = 'growth';            // Individual Development Plans (IDPs)

    public const TYPE_STRATEGIC = 'strategic';      // OKR-style strategic plans

    public const TYPE_ACTION = 'action';            // Alert-triggered action plans

    /**
     * Plan categories.
     */
    public const CATEGORY_PIP = 'pip';

    public const CATEGORY_IDP = 'idp';

    public const CATEGORY_OKR = 'okr';

    public const CATEGORY_ACTION_PLAN = 'action_plan';

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
     * Get top-level goals for this plan (OKR-style).
     */
    public function goals(): HasMany
    {
        return $this->hasMany(Goal::class)->whereNull('parent_goal_id')->orderBy('sort_order');
    }

    /**
     * Get all goals including nested ones.
     */
    public function allGoals(): HasMany
    {
        return $this->hasMany(Goal::class)->orderBy('sort_order');
    }

    /**
     * Get milestones for this plan.
     */
    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('due_date');
    }

    /**
     * Get progress updates for this plan.
     */
    public function progressUpdates(): HasMany
    {
        return $this->hasMany(ProgressUpdate::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get AI-generated progress summaries.
     */
    public function progressSummaries(): HasMany
    {
        return $this->hasMany(ProgressSummary::class)->orderBy('period_end', 'desc');
    }

    /**
     * Get the manager (for PIPs).
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Check if this is an improvement plan (not organizational).
     */
    public function isImprovementPlan(): bool
    {
        return $this->plan_type !== self::TYPE_ORGANIZATIONAL;
    }

    /**
     * Check if this plan uses OKR-style goals (vs FocusArea structure).
     */
    public function isOkrStyle(): bool
    {
        return in_array($this->plan_type, [
            self::TYPE_STRATEGIC,
            self::TYPE_GROWTH,
            self::TYPE_IMPROVEMENT,
            self::TYPE_ACTION,
        ]);
    }

    /**
     * Check if this is a Performance Improvement Plan.
     */
    public function isPip(): bool
    {
        return $this->plan_type === self::TYPE_IMPROVEMENT;
    }

    /**
     * Calculate goal-based progress percentage.
     */
    public function calculateGoalProgress(): float
    {
        $goals = $this->goals;

        if ($goals->isEmpty()) {
            return 0;
        }

        return $goals->avg(fn ($goal) => $goal->calculateProgress());
    }

    /**
     * Get overall progress (from goals or focus areas).
     */
    public function getProgressAttribute(): float
    {
        if ($this->isOkrStyle()) {
            return $this->calculateGoalProgress();
        }

        // Traditional focus area calculation
        $focusAreas = $this->focusAreas;
        if ($focusAreas->isEmpty()) {
            return 0;
        }

        $totalActivities = 0;
        $completedActivities = 0;

        foreach ($focusAreas as $fa) {
            foreach ($fa->objectives as $obj) {
                foreach ($obj->activities as $act) {
                    $totalActivities++;
                    if (in_array($act->status, ['on_track', 'completed'])) {
                        $completedActivities++;
                    }
                }
            }
        }

        return $totalActivities > 0 ? ($completedActivities / $totalActivities) * 100 : 0;
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
        $newPlan->title = $this->title.' (Copy)';
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
