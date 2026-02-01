<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgressUpdate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'strategic_plan_id',
        'goal_id',
        'key_result_id',
        'milestone_id',
        'content',
        'update_type',
        'value_change',
        'status_change',
        'attachments',
        'created_by',
    ];

    protected $casts = [
        'value_change' => 'decimal:2',
        'attachments' => 'array',
    ];

    /**
     * Update types.
     */
    public const TYPE_MANUAL = 'manual';

    public const TYPE_AI_GENERATED = 'ai_generated';

    public const TYPE_SYSTEM = 'system';

    /**
     * Get the strategic plan this update belongs to.
     */
    public function strategicPlan(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class);
    }

    /**
     * Get the goal this update is associated with.
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the key result this update is associated with.
     */
    public function keyResult(): BelongsTo
    {
        return $this->belongsTo(KeyResult::class);
    }

    /**
     * Get the milestone this update is associated with.
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    /**
     * Get the user who created this update.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Check if this is an AI-generated update.
     */
    public function isAiGenerated(): bool
    {
        return $this->update_type === self::TYPE_AI_GENERATED;
    }

    /**
     * Check if this is a system update.
     */
    public function isSystem(): bool
    {
        return $this->update_type === self::TYPE_SYSTEM;
    }

    /**
     * Get the context entity (goal, key result, or milestone).
     */
    public function getContextAttribute(): ?Model
    {
        if ($this->milestone_id) {
            return $this->milestone;
        }
        if ($this->key_result_id) {
            return $this->keyResult;
        }
        if ($this->goal_id) {
            return $this->goal;
        }

        return null;
    }

    /**
     * Get context label for display.
     */
    public function getContextLabelAttribute(): ?string
    {
        if ($this->milestone_id && $this->milestone) {
            return 'Milestone: '.$this->milestone->title;
        }
        if ($this->key_result_id && $this->keyResult) {
            return 'Key Result: '.$this->keyResult->title;
        }
        if ($this->goal_id && $this->goal) {
            return 'Goal: '.$this->goal->title;
        }

        return null;
    }

    /**
     * Scope to get recent updates.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to filter by update type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('update_type', $type);
    }

    /**
     * Scope to get manual updates only.
     */
    public function scopeManual($query)
    {
        return $query->where('update_type', self::TYPE_MANUAL);
    }
}
