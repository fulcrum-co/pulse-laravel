<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseApprovalWorkflow extends Model
{
    // Workflow modes (admin-configurable)
    public const MODE_AUTO_ACTIVATE = 'auto_activate';

    public const MODE_CREATE_APPROVE = 'create_approve';

    public const MODE_APPROVE_FIRST = 'approve_first';

    // Statuses
    public const STATUS_PENDING = 'pending_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_REVISION = 'revision_requested';

    protected $fillable = [
        'mini_course_id',
        'status',
        'workflow_mode',
        'submitted_by',
        'reviewed_by',
        'submitted_at',
        'reviewed_at',
        'review_notes',
        'revision_feedback',
        'revision_count',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'revision_count' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'revision_count' => 0,
    ];

    /**
     * Get available workflow modes.
     */
    public static function getWorkflowModes(): array
    {
        return [
            self::MODE_AUTO_ACTIVATE => 'Auto Activate',
            self::MODE_CREATE_APPROVE => 'Create Then Approve',
            self::MODE_APPROVE_FIRST => 'Approve Before Creation',
        ];
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_REVISION => 'Revision Requested',
        ];
    }

    /**
     * Course relationship.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(MiniCourse::class, 'mini_course_id');
    }

    /**
     * Submitter relationship.
     */
    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Reviewer relationship.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope to pending workflows.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to approved workflows.
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Scope to rejected workflows.
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    /**
     * Scope to workflows needing revision.
     */
    public function scopeNeedsRevision(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REVISION);
    }

    /**
     * Scope by workflow mode.
     */
    public function scopeByMode(Builder $query, string $mode): Builder
    {
        return $query->where('workflow_mode', $mode);
    }

    /**
     * Check if pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if approved.
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Check if needs revision.
     */
    public function needsRevision(): bool
    {
        return $this->status === self::STATUS_REVISION;
    }

    /**
     * Approve the workflow.
     */
    public function approve(int $reviewerId, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);

        // Also update the course
        $this->course->update([
            'approval_status' => MiniCourse::APPROVAL_APPROVED,
            'approved_by' => $reviewerId,
            'approved_at' => now(),
            'approval_notes' => $notes,
        ]);
    }

    /**
     * Reject the workflow.
     */
    public function reject(int $reviewerId, string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'review_notes' => $reason,
        ]);

        // Also update the course
        $this->course->update([
            'approval_status' => MiniCourse::APPROVAL_REJECTED,
            'approval_notes' => $reason,
        ]);
    }

    /**
     * Request revision.
     */
    public function requestRevision(int $reviewerId, string $feedback): void
    {
        $this->update([
            'status' => self::STATUS_REVISION,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
            'revision_feedback' => $feedback,
            'revision_count' => $this->revision_count + 1,
        ]);

        // Also update the course
        $this->course->update([
            'approval_status' => MiniCourse::APPROVAL_REVISION,
            'approval_notes' => $feedback,
        ]);
    }

    /**
     * Resubmit after revision.
     */
    public function resubmit(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING,
            'submitted_at' => now(),
        ]);

        // Also update the course
        $this->course->update([
            'approval_status' => MiniCourse::APPROVAL_PENDING,
        ]);
    }
}
