<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class ProviderConversation extends Model
{
    protected $fillable = [
        'uuid',
        'provider_id',
        'initiator_type',
        'initiator_id',
        'student_id',
        'stream_channel_id',
        'stream_channel_type',
        'status',
        'last_message_at',
        'last_message_preview',
        'last_message_sender_type',
        'last_message_sender_id',
        'unread_count_provider',
        'unread_count_initiator',
        'last_notification_sent_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'last_notification_sent_at' => 'datetime',
        'unread_count_provider' => 'integer',
        'unread_count_initiator' => 'integer',
    ];

    const STATUS_ACTIVE = 'active';

    const STATUS_ARCHIVED = 'archived';

    const STATUS_BLOCKED = 'blocked';

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($conversation) {
            if (empty($conversation->uuid)) {
                $conversation->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Get the provider.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Get the initiator (User or Student).
     */
    public function initiator(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the student being discussed (if any).
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * Get all bookings related to this conversation.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(ProviderBooking::class, 'conversation_id');
    }

    /**
     * Check if conversation is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Archive the conversation.
     */
    public function archive(): void
    {
        $this->update(['status' => self::STATUS_ARCHIVED]);
    }

    /**
     * Block the conversation.
     */
    public function block(): void
    {
        $this->update(['status' => self::STATUS_BLOCKED]);
    }

    /**
     * Reactivate the conversation.
     */
    public function reactivate(): void
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
    }

    /**
     * Update last message info.
     */
    public function updateLastMessage(string $preview, string $senderType, int $senderId): void
    {
        $this->update([
            'last_message_at' => now(),
            'last_message_preview' => Str::limit($preview, 100),
            'last_message_sender_type' => $senderType,
            'last_message_sender_id' => $senderId,
        ]);
    }

    /**
     * Check if notification can be sent (rate limiting).
     */
    public function canSendNotification(): bool
    {
        if (! $this->last_notification_sent_at) {
            return true;
        }

        // Rate limit: 5 minutes between notifications
        return $this->last_notification_sent_at->diffInMinutes(now()) >= 5;
    }

    /**
     * Mark notification as sent.
     */
    public function markNotificationSent(): void
    {
        $this->update(['last_notification_sent_at' => now()]);
    }

    /**
     * Generate the GetStream channel ID.
     */
    public function generateStreamChannelId(): string
    {
        $initiatorPrefix = $this->initiator_type === 'App\\Models\\User' ? 'user' : 'student';

        return "provider_{$this->provider_id}_{$initiatorPrefix}_{$this->initiator_id}";
    }

    /**
     * Reset unread count for provider.
     */
    public function markReadByProvider(): void
    {
        $this->update(['unread_count_provider' => 0]);
    }

    /**
     * Reset unread count for initiator.
     */
    public function markReadByInitiator(): void
    {
        $this->update(['unread_count_initiator' => 0]);
    }

    /**
     * Increment unread count for provider.
     */
    public function incrementProviderUnread(): void
    {
        $this->increment('unread_count_provider');
    }

    /**
     * Increment unread count for initiator.
     */
    public function incrementInitiatorUnread(): void
    {
        $this->increment('unread_count_initiator');
    }

    /**
     * Scope: active conversations only.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope: for a specific provider.
     */
    public function scopeForProvider($query, int $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Scope: for a specific initiator.
     */
    public function scopeForInitiator($query, $initiator)
    {
        return $query->where('initiator_type', get_class($initiator))
            ->where('initiator_id', $initiator->id);
    }

    /**
     * Scope: with unread messages for provider.
     */
    public function scopeWithUnreadForProvider($query)
    {
        return $query->where('unread_count_provider', '>', 0);
    }

    /**
     * Scope: with unread messages for initiator.
     */
    public function scopeWithUnreadForInitiator($query)
    {
        return $query->where('unread_count_initiator', '>', 0);
    }
}
