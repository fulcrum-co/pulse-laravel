<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ProviderAccount extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'provider_id',
        'user_id',
        'email',
        'password',
        'account_type',
        'stripe_account_id',
        'stripe_account_status',
        'stream_user_id',
        'stream_user_token',
        'notification_preferences',
        'last_login_at',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'stream_user_token',
    ];

    protected $casts = [
        'notification_preferences' => 'array',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    const TYPE_FULL = 'full';

    const TYPE_EMAIL_ONLY = 'email_only';

    /**
     * Get the provider this account belongs to.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Get the associated user (if any).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this is a full account (can log in).
     */
    public function isFullAccount(): bool
    {
        return $this->account_type === self::TYPE_FULL && $this->password !== null;
    }

    /**
     * Check if email notifications are enabled.
     */
    public function wantsEmailNotifications(): bool
    {
        return $this->notification_preferences['email'] ?? true;
    }

    /**
     * Check if SMS notifications are enabled.
     */
    public function wantsSmsNotifications(): bool
    {
        return $this->notification_preferences['sms'] ?? true;
    }

    /**
     * Check if Stripe account is active and can receive payments.
     */
    public function canReceivePayments(): bool
    {
        return $this->stripe_account_id !== null
            && $this->stripe_account_status === 'active';
    }

    /**
     * Get the Stream user ID for this provider.
     */
    public function getStreamUserId(): string
    {
        return $this->stream_user_id ?? 'provider_'.$this->provider_id;
    }

    /**
     * Update notification preferences.
     */
    public function updateNotificationPreferences(array $preferences): void
    {
        $this->update([
            'notification_preferences' => array_merge(
                $this->notification_preferences ?? [],
                $preferences
            ),
        ]);
    }
}
