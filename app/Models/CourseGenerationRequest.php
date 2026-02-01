<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CourseGenerationRequest extends Model
{
    // Trigger types
    public const TRIGGER_RISK_THRESHOLD = 'risk_threshold';
    public const TRIGGER_WORKFLOW = 'workflow';
    public const TRIGGER_MANUAL = 'manual';

    // Assignment types
    public const ASSIGNMENT_INDIVIDUAL = 'individual';
    public const ASSIGNMENT_GROUP = 'group';

    // Generation strategies
    public const STRATEGY_TEMPLATE_FILL = 'template_fill';
    public const STRATEGY_AI_FULL = 'ai_full';
    public const STRATEGY_HYBRID = 'hybrid';

    // Statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_GENERATING = 'generating';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'org_id',
        'trigger_type',
        'triggered_by_user_id',
        'workflow_execution_id',
        'assignment_type',
        'target_student_ids',
        'target_group_id',
        'student_context',
        'template_id',
        'generation_strategy',
        'generation_params',
        'generated_course_id',
        'generation_log',
        'status',
        'requires_approval',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'target_student_ids' => 'array',
        'student_context' => 'array',
        'generation_params' => 'array',
        'generation_log' => 'array',
        'requires_approval' => 'boolean',
        'approved_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'requires_approval' => true,
    ];

    /**
     * Get available trigger types.
     */
    public static function getTriggerTypes(): array
    {
        return [
            self::TRIGGER_RISK_THRESHOLD => 'Risk Threshold',
            self::TRIGGER_WORKFLOW => 'Alert Workflow',
            self::TRIGGER_MANUAL => 'Manual Request',
        ];
    }

    /**
     * Get available assignment types.
     */
    public static function getAssignmentTypes(): array
    {
        return [
            self::ASSIGNMENT_INDIVIDUAL => 'Individual Student(s)',
            self::ASSIGNMENT_GROUP => 'Student Group',
        ];
    }

    /**
     * Get available generation strategies.
     */
    public static function getStrategies(): array
    {
        return [
            self::STRATEGY_TEMPLATE_FILL => 'Template-Based',
            self::STRATEGY_AI_FULL => 'Full AI Generation',
            self::STRATEGY_HYBRID => 'Hybrid (Template + AI)',
        ];
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_GENERATING => 'Generating',
            self::STATUS_PENDING_APPROVAL => 'Pending Approval',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_FAILED => 'Failed',
        ];
    }

    /**
     * Organization relationship.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * User who triggered the request.
     */
    public function triggeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'triggered_by_user_id');
    }

    /**
     * Workflow execution that triggered this request.
     */
    public function workflowExecution(): BelongsTo
    {
        return $this->belongsTo(WorkflowExecution::class, 'workflow_execution_id');
    }

    /**
     * Template used for generation.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(CourseTemplate::class, 'template_id');
    }

    /**
     * Generated course.
     */
    public function generatedCourse(): BelongsTo
    {
        return $this->belongsTo(MiniCourse::class, 'generated_course_id');
    }

    /**
     * User who approved the request.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Scope by organization.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope by status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to pending requests.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to requests pending approval.
     */
    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_APPROVAL);
    }

    /**
     * Scope to failed requests.
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Scope by trigger type.
     */
    public function scopeByTrigger(Builder $query, string $trigger): Builder
    {
        return $query->where('trigger_type', $trigger);
    }

    /**
     * Check if request is complete.
     */
    public function isComplete(): bool
    {
        return in_array($this->status, [
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_FAILED,
        ]);
    }

    /**
     * Check if request is pending approval.
     */
    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    /**
     * Check if request can be approved.
     */
    public function canBeApproved(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL
            && $this->generated_course_id !== null;
    }

    /**
     * Mark as generating.
     */
    public function markGenerating(): void
    {
        $this->update([
            'status' => self::STATUS_GENERATING,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark as pending approval.
     */
    public function markPendingApproval(int $courseId): void
    {
        $this->update([
            'status' => self::STATUS_PENDING_APPROVAL,
            'generated_course_id' => $courseId,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark as approved.
     */
    public function approve(int $userId): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        // Also update the generated course
        if ($this->generatedCourse) {
            $this->generatedCourse->update([
                'approval_status' => MiniCourse::APPROVAL_APPROVED,
                'approved_by' => $userId,
                'approved_at' => now(),
                'status' => MiniCourse::STATUS_ACTIVE,
            ]);
        }
    }

    /**
     * Mark as rejected.
     */
    public function reject(int $userId, string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'approved_by' => $userId,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Also update the generated course
        if ($this->generatedCourse) {
            $this->generatedCourse->update([
                'approval_status' => MiniCourse::APPROVAL_REJECTED,
                'approved_by' => $userId,
                'approved_at' => now(),
                'approval_notes' => $reason,
            ]);
        }
    }

    /**
     * Mark as failed.
     */
    public function markFailed(string $error): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
            'generation_log' => array_merge(
                $this->generation_log ?? [],
                ['error' => $error, 'failed_at' => now()->toIso8601String()]
            ),
        ]);
    }

    /**
     * Add entry to generation log.
     */
    public function log(string $message, array $data = []): void
    {
        $log = $this->generation_log ?? [];
        $log[] = [
            'timestamp' => now()->toIso8601String(),
            'message' => $message,
            'data' => $data,
        ];

        $this->update(['generation_log' => $log]);
    }

    /**
     * Get risk signals from student context.
     */
    public function getRiskSignalsAttribute(): array
    {
        return $this->student_context['risk_signals'] ?? [];
    }

    /**
     * Get demographics from student context.
     */
    public function getDemographicsAttribute(): array
    {
        return $this->student_context['demographics'] ?? [];
    }

    /**
     * Get target student count.
     */
    public function getStudentCountAttribute(): int
    {
        return count($this->target_student_ids ?? []);
    }
}
