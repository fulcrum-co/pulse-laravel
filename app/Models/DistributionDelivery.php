<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DistributionDelivery extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';

    const STATUS_SENDING = 'sending';

    const STATUS_COMPLETED = 'completed';

    const STATUS_FAILED = 'failed';

    const STATUS_PARTIAL = 'partial';

    protected $fillable = [
        'distribution_id',
        'schedule_id',
        'status',
        'total_recipients',
        'sent_count',
        'failed_count',
        'opened_count',
        'clicked_count',
        'started_at',
        'completed_at',
        'error_log',
    ];

    protected $casts = [
        'total_recipients' => 'integer',
        'sent_count' => 'integer',
        'failed_count' => 'integer',
        'opened_count' => 'integer',
        'clicked_count' => 'integer',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'error_log' => 'array',
    ];

    public function distribution(): BelongsTo
    {
        return $this->belongsTo(Distribution::class);
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(DistributionSchedule::class);
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(DistributionRecipient::class, 'delivery_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSending($query)
    {
        return $query->where('status', self::STATUS_SENDING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSending(): bool
    {
        return $this->status === self::STATUS_SENDING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isPartial(): bool
    {
        return $this->status === self::STATUS_PARTIAL;
    }

    public function getDeliveryRate(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }

        return round(($this->sent_count / $this->total_recipients) * 100, 1);
    }

    public function getOpenRate(): float
    {
        if ($this->sent_count === 0) {
            return 0;
        }

        return round(($this->opened_count / $this->sent_count) * 100, 1);
    }

    public function getClickRate(): float
    {
        if ($this->sent_count === 0) {
            return 0;
        }

        return round(($this->clicked_count / $this->sent_count) * 100, 1);
    }

    public function getFailureRate(): float
    {
        if ($this->total_recipients === 0) {
            return 0;
        }

        return round(($this->failed_count / $this->total_recipients) * 100, 1);
    }

    public function markAsStarted(): void
    {
        $this->update([
            'status' => self::STATUS_SENDING,
            'started_at' => now(),
        ]);
    }

    public function markAsCompleted(): void
    {
        $status = $this->failed_count > 0 && $this->sent_count > 0
            ? self::STATUS_PARTIAL
            : ($this->failed_count === $this->total_recipients ? self::STATUS_FAILED : self::STATUS_COMPLETED);

        $this->update([
            'status' => $status,
            'completed_at' => now(),
        ]);
    }

    public function incrementSentCount(): void
    {
        $this->increment('sent_count');
    }

    public function incrementFailedCount(): void
    {
        $this->increment('failed_count');
    }

    public function incrementOpenedCount(): void
    {
        $this->increment('opened_count');
    }

    public function incrementClickedCount(): void
    {
        $this->increment('clicked_count');
    }

    public function addError(string $error, array $context = []): void
    {
        $errors = $this->error_log ?? [];
        $errors[] = [
            'message' => $error,
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
        ];
        $this->update(['error_log' => $errors]);
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SENDING => 'Sending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_PARTIAL => 'Partial',
        ];
    }
}
