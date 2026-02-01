<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModerationQueueItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'org_id',
        'moderation_result_id',
        'workflow_id',
        'current_step_id',
        'status',
        'assigned_to',
        'assigned_by',
        'assigned_at',
        'due_at',
        'priority',
        'started_at',
        'completed_at',
        'metadata',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'due_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Status constants
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_ESCALATED = 'escalated';
    public const STATUS_EXPIRED = 'expired';

    // Priority constants
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public static array $priorities = [
        self::PRIORITY_URGENT => 4,
        self::PRIORITY_HIGH => 3,
        self::PRIORITY_NORMAL => 2,
        self::PRIORITY_LOW => 1,
    ];

    // Relationships

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    public function moderationResult(): BelongsTo
    {
        return $this->belongsTo(ContentModerationResult::class, 'moderation_result_id');
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ModerationWorkflow::class, 'workflow_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assigner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function decisions(): HasMany
    {
        return $this->hasMany(ModerationDecision::class, 'queue_item_id');
    }

    // Scopes

    public function scopeForOrganization($query, int $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeAssignedTo($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_at', '<', now())
                     ->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    public function scopeDueSoon($query, int $hoursAhead = 24)
    {
        return $query->where('due_at', '<=', now()->addHours($hoursAhead))
                     ->where('due_at', '>', now())
                     ->whereIn('status', [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    public function scopeByPriority($query)
    {
        return $query->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'normal' THEN 3 WHEN 'low' THEN 4 ELSE 5 END");
    }

    // Accessors

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_at && $this->due_at->isPast() &&
               in_array($this->status, [self::STATUS_PENDING, self::STATUS_IN_PROGRESS]);
    }

    public function getTimeUntilDueAttribute(): ?int
    {
        if (!$this->due_at) {
            return null;
        }

        return now()->diffInMinutes($this->due_at, false);
    }

    public function getSlaStatusAttribute(): string
    {
        if (!$this->due_at) {
            return 'no_sla';
        }

        $hoursUntilDue = now()->diffInHours($this->due_at, false);

        if ($hoursUntilDue < 0) {
            return 'breached';
        }

        // Warning threshold based on priority
        $warningHours = match ($this->priority) {
            self::PRIORITY_URGENT => 2,
            self::PRIORITY_HIGH => 12,
            self::PRIORITY_NORMAL => 24,
            self::PRIORITY_LOW => 48,
            default => 24,
        };

        if ($hoursUntilDue <= $warningHours) {
            return 'warning';
        }

        return 'ok';
    }

    public function getPriorityWeightAttribute(): int
    {
        return self::$priorities[$this->priority] ?? 2;
    }

    // Methods

    public function assign(User $user, ?User $assigner = null): void
    {
        $this->update([
            'assigned_to' => $user->id,
            'assigned_by' => $assigner?->id,
            'assigned_at' => now(),
        ]);
    }

    public function unassign(): void
    {
        $this->update([
            'assigned_to' => null,
            'assigned_by' => null,
            'assigned_at' => null,
        ]);
    }

    public function startReview(): void
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);
    }

    public function complete(): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    public function escalate(): void
    {
        $this->update([
            'status' => self::STATUS_ESCALATED,
        ]);
    }

    public function markExpired(): void
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
        ]);
    }

    public function getTimeSpentSeconds(): int
    {
        if (!$this->started_at) {
            return 0;
        }

        $endTime = $this->completed_at ?? now();
        return $this->started_at->diffInSeconds($endTime);
    }

    public function recordDecision(User $user, string $decision, ?string $notes = null, ?array $fieldChanges = null): ModerationDecision
    {
        $previousStatus = $this->status;

        // Determine new status based on decision
        $newStatus = match ($decision) {
            'approve', 'reject' => self::STATUS_COMPLETED,
            'escalate' => self::STATUS_ESCALATED,
            'skip' => self::STATUS_PENDING,
            default => $this->status,
        };

        return $this->decisions()->create([
            'user_id' => $user->id,
            'decision' => $decision,
            'notes' => $notes,
            'field_changes' => $fieldChanges,
            'time_spent_seconds' => $this->getTimeSpentSeconds(),
            'previous_status' => $previousStatus,
            'new_status' => $newStatus,
        ]);
    }
}
