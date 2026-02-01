<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Builder;

class ContentModerationResult extends Model
{
    // Moderation statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_PASSED = 'passed';
    public const STATUS_FLAGGED = 'flagged';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_APPROVED_OVERRIDE = 'approved_override';

    // Thresholds for auto-decisions
    public const THRESHOLD_AUTO_PASS = 0.85;
    public const THRESHOLD_FLAG_FOR_REVIEW = 0.70;
    public const THRESHOLD_AUTO_REJECT = 0.40;

    // Dimension weights for K-12 education context
    public const DIMENSION_WEIGHTS = [
        'age_appropriateness' => 0.30,
        'clinical_safety' => 0.35,
        'cultural_sensitivity' => 0.20,
        'accuracy' => 0.15,
    ];

    protected $fillable = [
        'org_id',
        'moderatable_type',
        'moderatable_id',
        'status',
        'overall_score',
        'age_appropriateness_score',
        'clinical_safety_score',
        'cultural_sensitivity_score',
        'accuracy_score',
        'flags',
        'recommendations',
        'dimension_details',
        'human_reviewed',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'model_version',
        'processing_time_ms',
        'token_count',
    ];

    protected $casts = [
        'overall_score' => 'decimal:4',
        'age_appropriateness_score' => 'decimal:4',
        'clinical_safety_score' => 'decimal:4',
        'cultural_sensitivity_score' => 'decimal:4',
        'accuracy_score' => 'decimal:4',
        'flags' => 'array',
        'recommendations' => 'array',
        'dimension_details' => 'array',
        'human_reviewed' => 'boolean',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_PASSED => 'Passed',
            self::STATUS_FLAGGED => 'Flagged for Review',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_APPROVED_OVERRIDE => 'Approved (Override)',
        ];
    }

    /**
     * Get status color for UI.
     */
    public static function getStatusColor(string $status): string
    {
        return match ($status) {
            self::STATUS_PASSED => 'green',
            self::STATUS_FLAGGED => 'yellow',
            self::STATUS_REJECTED => 'red',
            self::STATUS_APPROVED_OVERRIDE => 'blue',
            default => 'gray',
        };
    }

    /**
     * The moderated content (polymorphic).
     */
    public function moderatable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Organization relationship.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Reviewer relationship.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope to pending reviews.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to flagged content.
     */
    public function scopeFlagged(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FLAGGED);
    }

    /**
     * Scope to rejected content.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope to passed content.
     */
    public function scopePassed(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_PASSED, self::STATUS_APPROVED_OVERRIDE]);
    }

    /**
     * Scope to items needing human review.
     */
    public function scopeNeedsReview(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_FLAGGED, self::STATUS_REJECTED])
            ->where('human_reviewed', false);
    }

    /**
     * Scope by organization.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope by content type.
     */
    public function scopeForContentType(Builder $query, string $type): Builder
    {
        return $query->where('moderatable_type', $type);
    }

    /**
     * Calculate weighted overall score from dimension scores.
     */
    public function calculateOverallScore(): float
    {
        $totalWeight = 0;
        $weightedSum = 0;

        foreach (self::DIMENSION_WEIGHTS as $dimension => $weight) {
            $scoreField = $dimension . '_score';
            if ($this->$scoreField !== null) {
                $weightedSum += $this->$scoreField * $weight;
                $totalWeight += $weight;
            }
        }

        return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
    }

    /**
     * Determine status based on overall score.
     */
    public static function determineStatus(float $overallScore): string
    {
        if ($overallScore >= self::THRESHOLD_AUTO_PASS) {
            return self::STATUS_PASSED;
        }

        if ($overallScore >= self::THRESHOLD_FLAG_FOR_REVIEW) {
            return self::STATUS_FLAGGED;
        }

        if ($overallScore < self::THRESHOLD_AUTO_REJECT) {
            return self::STATUS_REJECTED;
        }

        return self::STATUS_FLAGGED;
    }

    /**
     * Approve this moderation result (human override).
     */
    public function approve(int $reviewerId, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED_OVERRIDE,
            'human_reviewed' => true,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        // Update the moderated content's status
        if ($this->moderatable) {
            $this->moderatable->update([
                'moderation_status' => self::STATUS_APPROVED_OVERRIDE,
                'latest_moderation_id' => $this->id,
            ]);
        }
    }

    /**
     * Confirm rejection (human review).
     */
    public function confirmRejection(int $reviewerId, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'human_reviewed' => true,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        // Update the moderated content's status
        if ($this->moderatable) {
            $this->moderatable->update([
                'moderation_status' => self::STATUS_REJECTED,
                'latest_moderation_id' => $this->id,
            ]);
        }
    }

    /**
     * Check if this result requires human review.
     */
    public function requiresReview(): bool
    {
        return in_array($this->status, [self::STATUS_FLAGGED, self::STATUS_REJECTED])
            && !$this->human_reviewed;
    }

    /**
     * Get the lowest scoring dimension.
     */
    public function getLowestScoringDimension(): ?array
    {
        $dimensions = [
            'age_appropriateness' => $this->age_appropriateness_score,
            'clinical_safety' => $this->clinical_safety_score,
            'cultural_sensitivity' => $this->cultural_sensitivity_score,
            'accuracy' => $this->accuracy_score,
        ];

        $dimensions = array_filter($dimensions, fn($score) => $score !== null);

        if (empty($dimensions)) {
            return null;
        }

        $lowestKey = array_keys($dimensions, min($dimensions))[0];

        return [
            'dimension' => $lowestKey,
            'score' => $dimensions[$lowestKey],
            'label' => ucwords(str_replace('_', ' ', $lowestKey)),
        ];
    }

    /**
     * Get summary for display.
     */
    public function getSummaryAttribute(): string
    {
        $flagCount = is_array($this->flags) ? count($this->flags) : 0;

        if ($this->status === self::STATUS_PASSED) {
            return 'Content passed moderation checks';
        }

        if ($this->status === self::STATUS_APPROVED_OVERRIDE) {
            return 'Content approved by reviewer';
        }

        if ($flagCount > 0) {
            return "{$flagCount} concern" . ($flagCount > 1 ? 's' : '') . ' identified';
        }

        return 'Review required';
    }
}
