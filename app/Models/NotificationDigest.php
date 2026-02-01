<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDigest extends Model
{
    public const TYPE_DAILY = 'daily';

    public const TYPE_WEEKLY = 'weekly';

    protected $fillable = [
        'user_id',
        'digest_type',
        'notification_ids',
        'notification_count',
        'sent_at',
    ];

    protected $casts = [
        'notification_ids' => 'array',
        'sent_at' => 'datetime',
    ];

    /**
     * Get the user this digest was sent to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the last digest of a specific type for a user.
     */
    public static function getLastDigestForUser(int $userId, string $type): ?self
    {
        return static::where('user_id', $userId)
            ->where('digest_type', $type)
            ->orderByDesc('sent_at')
            ->first();
    }

    /**
     * Check if a digest was sent recently (within the last hour).
     * Used to prevent duplicate sends.
     */
    public static function wasSentRecently(int $userId, string $type, int $minutesAgo = 60): bool
    {
        return static::where('user_id', $userId)
            ->where('digest_type', $type)
            ->where('sent_at', '>=', now()->subMinutes($minutesAgo))
            ->exists();
    }

    /**
     * Get digest types.
     */
    public static function getTypes(): array
    {
        return [
            self::TYPE_DAILY => 'Daily',
            self::TYPE_WEEKLY => 'Weekly',
        ];
    }
}
