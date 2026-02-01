<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CollectionQueueItem extends Model
{
    protected $fillable = [
        'session_id',
        'entry_id',
        'contact_type',
        'contact_id',
        'position',
        'status',
        'priority',
        'priority_reason',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_CURRENT = 'current';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_SKIPPED = 'skipped';

    /**
     * Priority constants
     */
    public const PRIORITY_LOW = 1;

    public const PRIORITY_NORMAL = 3;

    public const PRIORITY_HIGH = 4;

    public const PRIORITY_CRITICAL = 5;

    /**
     * Get the session that owns this queue item.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(CollectionSession::class, 'session_id');
    }

    /**
     * Get the entry for this queue item.
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(CollectionEntry::class, 'entry_id');
    }

    /**
     * Get the contact (polymorphic).
     */
    public function contact(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter by session.
     */
    public function scopeForSession(Builder $query, int $sessionId): Builder
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope to filter pending items.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to order by priority then position.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('priority', 'desc')->orderBy('position', 'asc');
    }

    /**
     * Mark as current.
     */
    public function markCurrent(): void
    {
        $this->update(['status' => self::STATUS_CURRENT]);
    }

    /**
     * Mark as completed.
     */
    public function markCompleted(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Mark as skipped.
     */
    public function markSkipped(): void
    {
        $this->update(['status' => self::STATUS_SKIPPED]);
    }

    /**
     * Check if this is a high priority item.
     */
    public function isHighPriority(): bool
    {
        return $this->priority >= self::PRIORITY_HIGH;
    }

    /**
     * Get the contact name.
     */
    public function getContactName(): string
    {
        $contact = $this->contact;

        if (! $contact) {
            return 'Unknown';
        }

        if (method_exists($contact, 'getFullNameAttribute')) {
            return $contact->full_name;
        }

        if (isset($contact->first_name, $contact->last_name)) {
            return trim($contact->first_name.' '.$contact->last_name);
        }

        return $contact->name ?? 'Unknown';
    }

    /**
     * Get statuses for dropdown.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_CURRENT => 'Current',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_SKIPPED => 'Skipped',
        ];
    }

    /**
     * Get priorities for dropdown.
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_CRITICAL => 'Critical',
        ];
    }
}
