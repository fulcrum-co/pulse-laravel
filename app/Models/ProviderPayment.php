<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class ProviderPayment extends Model
{
    protected $fillable = [
        'uuid',
        'booking_id',
        'provider_id',
        'payer_type',
        'payer_id',
        'amount',
        'platform_fee',
        'provider_payout',
        'currency',
        'payment_type',
        'status',
        'stripe_payment_intent_id',
        'stripe_charge_id',
        'stripe_transfer_id',
        'stripe_refund_id',
        'paid_at',
        'transferred_at',
        'refunded_at',
        'refund_amount',
        'refund_reason',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'integer',
        'platform_fee' => 'integer',
        'provider_payout' => 'integer',
        'refund_amount' => 'integer',
        'paid_at' => 'datetime',
        'transferred_at' => 'datetime',
        'refunded_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Payment types
    const TYPE_SESSION = 'session';
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_PACKAGE = 'package';
    const TYPE_CANCELLATION_FEE = 'cancellation_fee';

    // Statuses
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_PARTIALLY_REFUNDED = 'partially_refunded';
    const STATUS_FAILED = 'failed';

    // Platform fee percentage (10%)
    const PLATFORM_FEE_PERCENTAGE = 10;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($payment) {
            if (empty($payment->uuid)) {
                $payment->uuid = (string) Str::uuid();
            }

            // Calculate platform fee and provider payout if not set
            if ($payment->amount && !$payment->platform_fee) {
                $payment->platform_fee = (int) round($payment->amount * (self::PLATFORM_FEE_PERCENTAGE / 100));
                $payment->provider_payout = $payment->amount - $payment->platform_fee;
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
     * Get the booking.
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(ProviderBooking::class, 'booking_id');
    }

    /**
     * Get the provider.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Get who paid.
     */
    public function payer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Check if payment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if payment is pending.
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if payment failed.
     */
    public function hasFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    /**
     * Check if payment was refunded (fully or partially).
     */
    public function isRefunded(): bool
    {
        return in_array($this->status, [self::STATUS_REFUNDED, self::STATUS_PARTIALLY_REFUNDED]);
    }

    /**
     * Mark payment as completed.
     */
    public function markCompleted(string $chargeId = null): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'paid_at' => now(),
            'stripe_charge_id' => $chargeId ?? $this->stripe_charge_id,
        ]);
    }

    /**
     * Mark payment as failed.
     */
    public function markFailed(): void
    {
        $this->update(['status' => self::STATUS_FAILED]);
    }

    /**
     * Process refund.
     */
    public function processRefund(int $amount, string $reason, string $refundId): void
    {
        $isFullRefund = $amount >= $this->amount;

        $this->update([
            'status' => $isFullRefund ? self::STATUS_REFUNDED : self::STATUS_PARTIALLY_REFUNDED,
            'refunded_at' => now(),
            'refund_amount' => $amount,
            'refund_reason' => $reason,
            'stripe_refund_id' => $refundId,
        ]);
    }

    /**
     * Mark as transferred to provider.
     */
    public function markTransferred(string $transferId): void
    {
        $this->update([
            'transferred_at' => now(),
            'stripe_transfer_id' => $transferId,
        ]);
    }

    /**
     * Get amount in dollars.
     */
    public function getAmountInDollarsAttribute(): float
    {
        return $this->amount / 100;
    }

    /**
     * Get platform fee in dollars.
     */
    public function getPlatformFeeInDollarsAttribute(): float
    {
        return $this->platform_fee / 100;
    }

    /**
     * Get provider payout in dollars.
     */
    public function getProviderPayoutInDollarsAttribute(): float
    {
        return $this->provider_payout / 100;
    }

    /**
     * Get formatted amount.
     */
    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount_in_dollars, 2);
    }

    /**
     * Get formatted provider payout.
     */
    public function getFormattedPayoutAttribute(): string
    {
        return '$' . number_format($this->provider_payout_in_dollars, 2);
    }

    /**
     * Scope: completed payments.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope: for a specific provider.
     */
    public function scopeForProvider($query, int $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Scope: pending transfer to provider.
     */
    public function scopePendingTransfer($query)
    {
        return $query->where('status', self::STATUS_COMPLETED)
            ->whereNull('transferred_at');
    }

    /**
     * Calculate total earnings for a provider.
     */
    public static function totalEarningsForProvider(int $providerId): int
    {
        return static::forProvider($providerId)
            ->completed()
            ->sum('provider_payout');
    }

    /**
     * Calculate pending payout for a provider.
     */
    public static function pendingPayoutForProvider(int $providerId): int
    {
        return static::forProvider($providerId)
            ->pendingTransfer()
            ->sum('provider_payout');
    }
}
