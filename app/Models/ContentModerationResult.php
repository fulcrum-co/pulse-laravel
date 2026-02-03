<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContentModerationResult extends Model
{
    // Moderation statuses
    public const STATUS_PENDING = 'pending';

    public const STATUS_PASSED = 'passed';

    public const STATUS_FLAGGED = 'flagged';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_APPROVED_OVERRIDE = 'approved_override';

    // Assignment priorities
    public const PRIORITY_LOW = 'low';

    public const PRIORITY_NORMAL = 'normal';

    public const PRIORITY_HIGH = 'high';

    public const PRIORITY_URGENT = 'urgent';

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
        // Assignment fields
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'collaborator_ids',
        'assignment_priority',
        'due_at',
        'assignment_notes',
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
        // Assignment casts
        'assigned_at' => 'datetime',
        'due_at' => 'datetime',
        'collaborator_ids' => 'array',
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
     * User this moderation is assigned to.
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * User who made the assignment.
     */
    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get collaborator users.
     */
    public function getCollaboratorsAttribute(): \Illuminate\Support\Collection
    {
        if (empty($this->collaborator_ids)) {
            return collect();
        }

        return User::whereIn('id', $this->collaborator_ids)->get();
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
     * Scope to items assigned to a specific user.
     */
    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope to items where user is assignee or collaborator.
     */
    public function scopeAssignedToOrCollaborator(Builder $query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('assigned_to', $userId)
                ->orWhereJsonContains('collaborator_ids', $userId);
        });
    }

    /**
     * Scope to unassigned items.
     */
    public function scopeUnassigned(Builder $query): Builder
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Scope by assignment priority.
     */
    public function scopeByAssignmentPriority(Builder $query, string $priority): Builder
    {
        return $query->where('assignment_priority', $priority);
    }

    /**
     * Order by assignment priority then due date.
     */
    public function scopeOrderByPriorityAndDue(Builder $query): Builder
    {
        return $query->orderByRaw("CASE assignment_priority
                WHEN 'urgent' THEN 1
                WHEN 'high' THEN 2
                WHEN 'normal' THEN 3
                WHEN 'low' THEN 4
                ELSE 5 END")
            ->orderBy('due_at', 'asc')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Calculate weighted overall score from dimension scores.
     */
    public function calculateOverallScore(): float
    {
        $totalWeight = 0;
        $weightedSum = 0;

        foreach (self::DIMENSION_WEIGHTS as $dimension => $weight) {
            $scoreField = $dimension.'_score';
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
            && ! $this->human_reviewed;
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

        $dimensions = array_filter($dimensions, fn ($score) => $score !== null);

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
            return "{$flagCount} concern".($flagCount > 1 ? 's' : '').' identified';
        }

        return 'Review required';
    }

    // ============================================
    // ASSIGNMENT METHODS
    // ============================================

    /**
     * Get available priorities.
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }

    /**
     * Get priority color for UI.
     */
    public static function getPriorityColor(string $priority): string
    {
        return match ($priority) {
            self::PRIORITY_URGENT => 'red',
            self::PRIORITY_HIGH => 'orange',
            self::PRIORITY_NORMAL => 'blue',
            self::PRIORITY_LOW => 'gray',
            default => 'gray',
        };
    }

    /**
     * Assign this moderation result to a user.
     */
    public function assignTo(int $userId, int $assignedBy, array $options = []): void
    {
        $this->update([
            'assigned_to' => $userId,
            'assigned_by' => $assignedBy,
            'assigned_at' => now(),
            'assignment_priority' => $options['priority'] ?? self::PRIORITY_NORMAL,
            'due_at' => $options['due_at'] ?? null,
            'assignment_notes' => $options['notes'] ?? null,
        ]);
    }

    /**
     * Add a collaborator.
     */
    public function addCollaborator(int $userId): void
    {
        $collaborators = $this->collaborator_ids ?? [];
        if (! in_array($userId, $collaborators)) {
            $collaborators[] = $userId;
            $this->update(['collaborator_ids' => $collaborators]);
        }
    }

    /**
     * Remove a collaborator.
     */
    public function removeCollaborator(int $userId): void
    {
        $collaborators = $this->collaborator_ids ?? [];
        $collaborators = array_filter($collaborators, fn ($id) => $id !== $userId);
        $this->update(['collaborator_ids' => array_values($collaborators)]);
    }

    /**
     * Unassign this moderation result.
     */
    public function unassign(): void
    {
        $this->update([
            'assigned_to' => null,
            'assigned_by' => null,
            'assigned_at' => null,
            'assignment_notes' => null,
        ]);
    }

    /**
     * Check if a user can review this item.
     */
    public function canBeReviewedBy(User $user): bool
    {
        // Assignee can always review
        if ($this->assigned_to === $user->id) {
            return true;
        }

        // Collaborators can review
        if (in_array($user->id, $this->collaborator_ids ?? [])) {
            return true;
        }

        // Admins can review any item in their org
        if ($user->isAdmin() && $user->canAccessOrganization($this->org_id)) {
            return true;
        }

        // Unassigned items can be reviewed by any authorized user
        if ($this->assigned_to === null && $user->canAccessOrganization($this->org_id)) {
            return $this->canUserModerate($user);
        }

        return false;
    }

    /**
     * Check if user has moderation permissions based on role.
     */
    protected function canUserModerate(User $user): bool
    {
        return in_array($user->effective_role, [
            'admin', 'consultant', 'administrative_role', 'organization_admin',
        ]);
    }

    /**
     * Check if this item is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_at !== null && $this->due_at->isPast() && ! $this->human_reviewed;
    }

    /**
     * Check if this item is due soon (within 24 hours).
     */
    public function isDueSoon(): bool
    {
        return $this->due_at !== null
            && $this->due_at->isFuture()
            && $this->due_at->diffInHours(now()) <= 24
            && ! $this->human_reviewed;
    }
}
