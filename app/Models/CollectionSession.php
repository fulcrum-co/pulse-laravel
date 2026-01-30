<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class CollectionSession extends Model
{
    protected $fillable = [
        'collection_id',
        'schedule_id',
        'session_date',
        'status',
        'total_contacts',
        'completed_count',
        'skipped_count',
        'completion_rate',
        'started_at',
        'completed_at',
        'collected_by_user_id',
        'notes',
    ];

    protected $casts = [
        'session_date' => 'date',
        'completion_rate' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the collection that owns this session.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the schedule that triggered this session.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(CollectionSchedule::class, 'schedule_id');
    }

    /**
     * Get the user who collected data in this session.
     */
    public function collector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by_user_id');
    }

    /**
     * Get all entries for this session.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(CollectionEntry::class, 'session_id');
    }

    /**
     * Get queue items for this session.
     */
    public function queueItems(): HasMany
    {
        return $this->hasMany(CollectionQueueItem::class, 'session_id');
    }

    /**
     * Get reminders for this session.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(CollectionReminder::class, 'session_id');
    }

    /**
     * Scope to filter by collection.
     */
    public function scopeForCollection(Builder $query, int $collectionId): Builder
    {
        return $query->where('collection_id', $collectionId);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter active (in_progress) sessions.
     */
    public function scopeInProgress(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Start the session.
     */
    public function start(int $userId): void
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
            'started_at' => now(),
            'collected_by_user_id' => $userId,
        ]);
    }

    /**
     * Complete the session.
     */
    public function complete(): void
    {
        $this->updateStats();
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);
    }

    /**
     * Cancel the session.
     */
    public function cancel(string $reason = null): void
    {
        $this->updateStats();
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'completed_at' => now(),
            'notes' => $reason,
        ]);
    }

    /**
     * Update statistics for this session.
     */
    public function updateStats(): void
    {
        $completed = $this->entries()->where('status', CollectionEntry::STATUS_COMPLETED)->count();
        $skipped = $this->entries()->where('status', CollectionEntry::STATUS_SKIPPED)->count();
        $total = $this->total_contacts;

        $rate = $total > 0 ? ($completed / $total) * 100 : 0;

        $this->update([
            'completed_count' => $completed,
            'skipped_count' => $skipped,
            'completion_rate' => round($rate, 2),
        ]);
    }

    /**
     * Get the next item in the queue.
     */
    public function getNextQueueItem(): ?CollectionQueueItem
    {
        return $this->queueItems()
            ->where('status', CollectionQueueItem::STATUS_PENDING)
            ->orderBy('priority', 'desc')
            ->orderBy('position', 'asc')
            ->first();
    }

    /**
     * Get remaining contacts count.
     */
    public function getRemainingCount(): int
    {
        return $this->total_contacts - $this->completed_count - $this->skipped_count;
    }

    /**
     * Check if session can be resumed.
     */
    public function canResume(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS && $this->getRemainingCount() > 0;
    }

    /**
     * Check if session is complete.
     */
    public function isComplete(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Get statuses for dropdown.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_CANCELLED => 'Cancelled',
        ];
    }
}
