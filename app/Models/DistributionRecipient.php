<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DistributionRecipient extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_OPENED = 'opened';
    const STATUS_CLICKED = 'clicked';
    const STATUS_BOUNCED = 'bounced';
    const STATUS_FAILED = 'failed';

    protected $fillable = [
        'delivery_id',
        'contact_type',
        'contact_id',
        'email',
        'phone',
        'status',
        'sent_at',
        'delivered_at',
        'opened_at',
        'clicked_at',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(DistributionDelivery::class, 'delivery_id');
    }

    public function contact(): MorphTo
    {
        return $this->morphTo();
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeOpened($query)
    {
        return $query->where('status', self::STATUS_OPENED);
    }

    public function scopeClicked($query)
    {
        return $query->where('status', self::STATUS_CLICKED);
    }

    public function scopeBounced($query)
    {
        return $query->where('status', self::STATUS_BOUNCED);
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    public function scopeSuccessful($query)
    {
        return $query->whereIn('status', [
            self::STATUS_SENT,
            self::STATUS_DELIVERED,
            self::STATUS_OPENED,
            self::STATUS_CLICKED,
        ]);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isSent(): bool
    {
        return $this->status === self::STATUS_SENT;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isOpened(): bool
    {
        return $this->status === self::STATUS_OPENED;
    }

    public function isClicked(): bool
    {
        return $this->status === self::STATUS_CLICKED;
    }

    public function isBounced(): bool
    {
        return $this->status === self::STATUS_BOUNCED;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isSuccessful(): bool
    {
        return in_array($this->status, [
            self::STATUS_SENT,
            self::STATUS_DELIVERED,
            self::STATUS_OPENED,
            self::STATUS_CLICKED,
        ]);
    }

    public function markAsSent(array $metadata = []): void
    {
        $this->update([
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
            'metadata' => array_merge($this->metadata ?? [], $metadata),
        ]);
    }

    public function markAsDelivered(): void
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'delivered_at' => now(),
        ]);
    }

    public function markAsOpened(): void
    {
        // Only update if not already opened
        if (!$this->opened_at) {
            $this->update([
                'status' => self::STATUS_OPENED,
                'opened_at' => now(),
            ]);

            $this->delivery?->incrementOpenedCount();
        }
    }

    public function markAsClicked(): void
    {
        // Only update if not already clicked
        if (!$this->clicked_at) {
            $this->update([
                'status' => self::STATUS_CLICKED,
                'clicked_at' => now(),
            ]);

            $this->delivery?->incrementClickedCount();
        }
    }

    public function markAsBounced(string $error = null): void
    {
        $this->update([
            'status' => self::STATUS_BOUNCED,
            'error_message' => $error,
        ]);
    }

    public function markAsFailed(string $error = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $error,
        ]);
    }

    public function getRecipientName(): string
    {
        if ($this->contact) {
            if ($this->contact_type === 'App\\Models\\Student') {
                return $this->contact->user->first_name . ' ' . $this->contact->user->last_name;
            }
            return $this->contact->first_name . ' ' . $this->contact->last_name;
        }

        return $this->email ?? $this->phone ?? 'Unknown';
    }

    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SENT => 'Sent',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_OPENED => 'Opened',
            self::STATUS_CLICKED => 'Clicked',
            self::STATUS_BOUNCED => 'Bounced',
            self::STATUS_FAILED => 'Failed',
        ];
    }
}
