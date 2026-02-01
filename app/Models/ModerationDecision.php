<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModerationDecision extends Model
{
    use HasFactory;

    protected $fillable = [
        'queue_item_id',
        'user_id',
        'decision',
        'notes',
        'field_changes',
        'time_spent_seconds',
        'previous_status',
        'new_status',
    ];

    protected $casts = [
        'field_changes' => 'array',
        'time_spent_seconds' => 'integer',
    ];

    // Decision constants
    public const DECISION_APPROVE = 'approve';
    public const DECISION_REJECT = 'reject';
    public const DECISION_REQUEST_CHANGES = 'request_changes';
    public const DECISION_ESCALATE = 'escalate';
    public const DECISION_SKIP = 'skip';

    public static array $decisions = [
        self::DECISION_APPROVE,
        self::DECISION_REJECT,
        self::DECISION_REQUEST_CHANGES,
        self::DECISION_ESCALATE,
        self::DECISION_SKIP,
    ];

    // Relationships

    public function queueItem(): BelongsTo
    {
        return $this->belongsTo(ModerationQueueItem::class, 'queue_item_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDecision($query, string $decision)
    {
        return $query->where('decision', $decision);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    // Accessors

    public function getFormattedTimeSpentAttribute(): string
    {
        if (!$this->time_spent_seconds) {
            return '0s';
        }

        $minutes = floor($this->time_spent_seconds / 60);
        $seconds = $this->time_spent_seconds % 60;

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }

    public function getDecisionLabelAttribute(): string
    {
        return match ($this->decision) {
            self::DECISION_APPROVE => 'Approved',
            self::DECISION_REJECT => 'Rejected',
            self::DECISION_REQUEST_CHANGES => 'Changes Requested',
            self::DECISION_ESCALATE => 'Escalated',
            self::DECISION_SKIP => 'Skipped',
            default => ucfirst($this->decision),
        };
    }

    public function getDecisionColorAttribute(): string
    {
        return match ($this->decision) {
            self::DECISION_APPROVE => 'green',
            self::DECISION_REJECT => 'red',
            self::DECISION_REQUEST_CHANGES => 'yellow',
            self::DECISION_ESCALATE => 'orange',
            self::DECISION_SKIP => 'gray',
            default => 'gray',
        };
    }

    // Methods

    public function hasFieldChanges(): bool
    {
        return !empty($this->field_changes);
    }

    public function getChangedFields(): array
    {
        if (!$this->hasFieldChanges()) {
            return [];
        }

        return array_keys($this->field_changes);
    }
}
