<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StrategyDriftScore extends Model
{
    // Alignment levels
    public const LEVEL_STRONG = 'strong';

    public const LEVEL_MODERATE = 'moderate';

    public const LEVEL_WEAK = 'weak';

    // Drift directions
    public const DIRECTION_IMPROVING = 'improving';

    public const DIRECTION_STABLE = 'stable';

    public const DIRECTION_DECLINING = 'declining';

    // Thresholds for alignment levels
    public const THRESHOLD_STRONG = 0.85;

    public const THRESHOLD_MODERATE = 0.65;

    protected $fillable = [
        'org_id',
        'contact_note_id',
        'strategic_plan_id',
        'goal_id',
        'key_result_id',
        'alignment_score',
        'alignment_level',
        'matched_context',
        'drift_direction',
        'insight',
        'scored_by',
        'scored_at',
    ];

    protected $casts = [
        'alignment_score' => 'decimal:4',
        'matched_context' => 'array',
        'scored_at' => 'datetime',
    ];

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the contact note (narrative) being scored.
     */
    public function contactNote(): BelongsTo
    {
        return $this->belongsTo(ContactNote::class);
    }

    /**
     * Get the related strategic plan (if linked to one).
     */
    public function strategicPlan(): BelongsTo
    {
        return $this->belongsTo(StrategicPlan::class);
    }

    /**
     * Get the related goal (if linked to one).
     */
    public function goal(): BelongsTo
    {
        return $this->belongsTo(Goal::class);
    }

    /**
     * Get the related key result (if linked to one).
     */
    public function keyResult(): BelongsTo
    {
        return $this->belongsTo(KeyResult::class);
    }

    /**
     * Scope to weak alignment scores.
     */
    public function scopeWeak(Builder $query): Builder
    {
        return $query->where('alignment_level', self::LEVEL_WEAK);
    }

    /**
     * Scope to moderate alignment scores.
     */
    public function scopeModerate(Builder $query): Builder
    {
        return $query->where('alignment_level', self::LEVEL_MODERATE);
    }

    /**
     * Scope to strong alignment scores.
     */
    public function scopeStrong(Builder $query): Builder
    {
        return $query->where('alignment_level', self::LEVEL_STRONG);
    }

    /**
     * Scope to scores within a date range.
     */
    public function scopeRecent(Builder $query, int $days = 7): Builder
    {
        return $query->where('scored_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to scores for an organization.
     */
    public function scopeForOrg(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope to scores with declining direction.
     */
    public function scopeDeclining(Builder $query): Builder
    {
        return $query->where('drift_direction', self::DIRECTION_DECLINING);
    }

    /**
     * Determine alignment level from a score.
     */
    public static function levelFromScore(float $score): string
    {
        return match (true) {
            $score >= self::THRESHOLD_STRONG => self::LEVEL_STRONG,
            $score >= self::THRESHOLD_MODERATE => self::LEVEL_MODERATE,
            default => self::LEVEL_WEAK,
        };
    }

    /**
     * Get a human-readable label for the alignment level.
     */
    public function getAlignmentLabelAttribute(): string
    {
        return match ($this->alignment_level) {
            self::LEVEL_STRONG => 'On Track',
            self::LEVEL_MODERATE => 'Drifting',
            self::LEVEL_WEAK => 'Off Track',
            default => 'Unknown',
        };
    }

    /**
     * Get a CSS color class for the alignment level.
     */
    public function getAlignmentColorAttribute(): string
    {
        return match ($this->alignment_level) {
            self::LEVEL_STRONG => 'green',
            self::LEVEL_MODERATE => 'yellow',
            self::LEVEL_WEAK => 'red',
            default => 'gray',
        };
    }

    /**
     * Get the alignment score as a percentage.
     */
    public function getAlignmentPercentageAttribute(): int
    {
        return (int) round($this->alignment_score * 100);
    }
}
