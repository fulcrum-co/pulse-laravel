<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CollectionReminder extends Model
{
    protected $fillable = [
        'collection_id',
        'session_id',
        'user_id',
        'channel',
        'status',
        'scheduled_for',
        'sent_at',
        'delivery_metadata',
        'message_template',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'sent_at' => 'datetime',
        'delivery_metadata' => 'array',
    ];

    /**
     * Channel constants
     */
    public const CHANNEL_SMS = 'sms';

    public const CHANNEL_EMAIL = 'email';

    public const CHANNEL_WHATSAPP = 'whatsapp';

    /**
     * Status constants
     */
    public const STATUS_PENDING = 'pending';

    public const STATUS_SENT = 'sent';

    public const STATUS_DELIVERED = 'delivered';

    public const STATUS_FAILED = 'failed';

    /**
     * Get the collection that owns this reminder.
     */
    public function collection(): BelongsTo
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Get the session this reminder is for.
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(CollectionSession::class, 'session_id');
    }

    /**
     * Get the user to be reminded.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter pending reminders.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to filter due reminders.
     */
    public function scopeDue(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('scheduled_for', '<=', now());
    }

    /**
     * Scope to filter by channel.
     */
    public function scopeForChannel(Builder $query, string $channel): Builder
    {
        return $query->where('channel', $channel);
    }

    /**
     * Mark as sent.
     */
    public function markSent(?array $metadata = null): void
    {
        $data = [
            'status' => self::STATUS_SENT,
            'sent_at' => now(),
        ];

        if ($metadata !== null) {
            $data['delivery_metadata'] = array_merge(
                $this->delivery_metadata ?? [],
                $metadata
            );
        }

        $this->update($data);
    }

    /**
     * Mark as delivered.
     */
    public function markDelivered(?array $metadata = null): void
    {
        $data = ['status' => self::STATUS_DELIVERED];

        if ($metadata !== null) {
            $data['delivery_metadata'] = array_merge(
                $this->delivery_metadata ?? [],
                $metadata
            );
        }

        $this->update($data);
    }

    /**
     * Mark as failed.
     */
    public function markFailed(?string $error = null): void
    {
        $metadata = $this->delivery_metadata ?? [];
        $metadata['error'] = $error;
        $metadata['failed_at'] = now()->toIso8601String();

        $this->update([
            'status' => self::STATUS_FAILED,
            'delivery_metadata' => $metadata,
        ]);
    }

    /**
     * Get the message to send (with variable substitution).
     */
    public function getMessage(): string
    {
        $template = $this->message_template ?? $this->getDefaultTemplate();

        // Variable substitution
        $variables = [
            '{{user_name}}' => $this->user?->first_name ?? 'there',
            '{{collection_title}}' => $this->collection?->title ?? 'data collection',
            '{{session_date}}' => $this->session?->session_date?->format('M j') ?? 'today',
        ];

        return str_replace(array_keys($variables), array_values($variables), $template);
    }

    /**
     * Get the default message template.
     */
    protected function getDefaultTemplate(): string
    {
        return "Hi {{user_name}}! Don't forget to complete your {{collection_title}} for {{session_date}}. Click here to start: ";
    }

    /**
     * Get channels for dropdown.
     */
    public static function getChannels(): array
    {
        return [
            self::CHANNEL_SMS => 'SMS',
            self::CHANNEL_EMAIL => 'Email',
            self::CHANNEL_WHATSAPP => 'WhatsApp',
        ];
    }

    /**
     * Get statuses for dropdown.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_SENT => 'Sent',
            self::STATUS_DELIVERED => 'Delivered',
            self::STATUS_FAILED => 'Failed',
        ];
    }
}
