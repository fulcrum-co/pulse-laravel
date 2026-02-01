<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowExecution extends Model
{
    protected $table = 'workflow_executions';

    // Status constants
    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_WAITING = 'waiting';  // Waiting for delay/external

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'workflow_id',
        'org_id',
        'triggered_by',
        'trigger_data',
        'context',
        'status',
        'current_node_id',
        'started_at',
        'completed_at',
        'node_results',
        'error_message',
        'resume_at',
        'resume_data',
    ];

    protected $casts = [
        'trigger_data' => 'array',
        'context' => 'array',
        'node_results' => 'array',
        'resume_data' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'resume_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (is_null($model->node_results)) {
                $model->node_results = [];
            }
            if (is_null($model->context)) {
                $model->context = [];
            }
        });
    }

    /**
     * Get the workflow.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class, 'workflow_id');
    }

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Check if execution is running.
     */
    public function isRunning(): bool
    {
        return $this->status === self::STATUS_RUNNING;
    }

    /**
     * Check if execution is waiting (for delay/external).
     */
    public function isWaiting(): bool
    {
        return $this->status === self::STATUS_WAITING;
    }

    /**
     * Check if execution is complete.
     */
    public function isComplete(): bool
    {
        return in_array($this->status, [
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_CANCELLED,
        ]);
    }

    /**
     * Check if execution succeeded.
     */
    public function isSuccess(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if execution failed.
     */
    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Mark execution as started.
     */
    public function markStarted(): void
    {
        $this->update([
            'status' => self::STATUS_RUNNING,
            'started_at' => now(),
        ]);
    }

    /**
     * Mark execution as waiting (for delay node).
     */
    public function markWaiting(string $nodeId, \DateTime $resumeAt, array $resumeData = []): void
    {
        $this->update([
            'status' => self::STATUS_WAITING,
            'current_node_id' => $nodeId,
            'resume_at' => $resumeAt,
            'resume_data' => $resumeData,
        ]);
    }

    /**
     * Mark execution as completed.
     */
    public function markCompleted(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Mark execution as failed.
     */
    public function markFailed(string $errorMessage): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'completed_at' => now(),
            'error_message' => $errorMessage,
        ]);
    }

    /**
     * Mark execution as cancelled.
     */
    public function markCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Record a node execution result.
     */
    public function recordNodeResult(string $nodeId, string $status, array $output = [], ?string $error = null): void
    {
        $results = $this->node_results ?? [];
        $results[$nodeId] = [
            'status' => $status,
            'output' => $output,
            'executed_at' => now()->toISOString(),
            'error' => $error,
        ];

        $this->update([
            'node_results' => $results,
            'current_node_id' => $nodeId,
        ]);
    }

    /**
     * Get result for a specific node.
     */
    public function getNodeResult(string $nodeId): ?array
    {
        return ($this->node_results ?? [])[$nodeId] ?? null;
    }

    /**
     * Check if a node has been executed.
     */
    public function hasNodeBeenExecuted(string $nodeId): bool
    {
        return isset(($this->node_results ?? [])[$nodeId]);
    }

    /**
     * Get all successful node results.
     */
    public function getSuccessfulNodes(): array
    {
        return array_filter($this->node_results ?? [], function ($result) {
            return ($result['status'] ?? '') === 'success';
        });
    }

    /**
     * Get all failed node results.
     */
    public function getFailedNodes(): array
    {
        return array_filter($this->node_results ?? [], function ($result) {
            return ($result['status'] ?? '') === 'failed';
        });
    }

    /**
     * Update the execution context.
     */
    public function updateContext(array $data): void
    {
        $context = $this->context ?? [];
        $this->update([
            'context' => array_merge($context, $data),
        ]);
    }

    /**
     * Get value from context.
     */
    public function getContextValue(string $key, $default = null)
    {
        return ($this->context ?? [])[$key] ?? $default;
    }

    /**
     * Get execution duration in seconds.
     */
    public function getDurationAttribute(): ?int
    {
        if (! $this->started_at) {
            return null;
        }

        $endTime = $this->completed_at ?? now();

        return $this->started_at->diffInSeconds($endTime);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter running executions.
     */
    public function scopeRunning($query)
    {
        return $query->where('status', self::STATUS_RUNNING);
    }

    /**
     * Scope to filter waiting executions ready to resume.
     */
    public function scopeReadyToResume($query)
    {
        return $query->where('status', self::STATUS_WAITING)
            ->where('resume_at', '<=', now());
    }

    /**
     * Scope to filter by workflow.
     */
    public function scopeForWorkflow($query, $workflowId)
    {
        return $query->where('workflow_id', $workflowId);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrg($query, $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_RUNNING => 'Running',
            self::STATUS_WAITING => 'Waiting',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }
}
