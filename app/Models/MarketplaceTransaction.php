<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MarketplaceTransaction extends Model
{
    use SoftDeletes;

    // Transaction types
    public const TYPE_PURCHASE = 'purchase';

    public const TYPE_SUBSCRIPTION = 'subscription';

    public const TYPE_DOWNLOAD = 'download'; // Free items

    // Statuses
    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REFUNDED = 'refunded';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_FAILED = 'failed';

    // Platform fee rates
    public const DIRECT_FEE_RATE = 0.10; // 10% for direct purchases

    public const DISCOVERY_FEE_RATE = 0.30; // 30% for marketplace discovery

    protected $fillable = [
        'uuid',
        'marketplace_item_id',
        'buyer_user_id',
        'buyer_org_id',
        'seller_profile_id',
        'transaction_type',
        'status',
        'amount',
        'platform_fee',
        'seller_payout',
        'currency',
        'license_type',
        'seat_count',
        'license_expires_at',
        'stripe_payment_intent_id',
        'stripe_subscription_id',
        'stripe_invoice_id',
        'current_period_start',
        'current_period_end',
        'cancel_at_period_end',
        'first_accessed_at',
        'access_count',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'seller_payout' => 'decimal:2',
        'license_expires_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'first_accessed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'currency' => 'USD',
        'cancel_at_period_end' => false,
        'access_count' => 0,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->uuid)) {
                $transaction->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get available transaction types.
     */
    public static function getTransactionTypes(): array
    {
        return [
            self::TYPE_PURCHASE => 'One-time Purchase',
            self::TYPE_SUBSCRIPTION => 'Subscription',
            self::TYPE_DOWNLOAD => 'Free Download',
        ];
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_COMPLETED => 'Completed',
            self::STATUS_REFUNDED => 'Refunded',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_FAILED => 'Failed',
        ];
    }

    /**
     * Calculate platform fee.
     */
    public static function calculatePlatformFee(float $amount, bool $isDiscovery = true): float
    {
        $rate = $isDiscovery ? self::DISCOVERY_FEE_RATE : self::DIRECT_FEE_RATE;

        return round($amount * $rate, 2);
    }

    /**
     * Marketplace item relationship.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(MarketplaceItem::class, 'marketplace_item_id');
    }

    /**
     * Buyer user relationship.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_user_id');
    }

    /**
     * Buyer organization relationship.
     */
    public function buyerOrganization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'buyer_org_id');
    }

    /**
     * Seller profile relationship.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(SellerProfile::class, 'seller_profile_id');
    }

    /**
     * Purchase records.
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(MarketplacePurchase::class, 'transaction_id');
    }

    /**
     * Review for this transaction.
     */
    public function review(): HasOne
    {
        return $this->hasOne(MarketplaceReview::class, 'transaction_id');
    }

    /**
     * Scope to completed transactions.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to subscriptions.
     */
    public function scopeSubscriptions(Builder $query): Builder
    {
        return $query->where('transaction_type', self::TYPE_SUBSCRIPTION);
    }

    /**
     * Scope to active subscriptions.
     */
    public function scopeActiveSubscriptions(Builder $query): Builder
    {
        return $query->subscriptions()
            ->completed()
            ->where(function ($q) {
                $q->whereNull('current_period_end')
                    ->orWhere('current_period_end', '>', now());
            })
            ->where('cancel_at_period_end', false);
    }

    /**
     * Scope by buyer.
     */
    public function scopeForBuyer(Builder $query, int $userId): Builder
    {
        return $query->where('buyer_user_id', $userId);
    }

    /**
     * Scope by seller.
     */
    public function scopeForSeller(Builder $query, int $sellerProfileId): Builder
    {
        return $query->where('seller_profile_id', $sellerProfileId);
    }

    /**
     * Check if transaction is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Check if this is a subscription.
     */
    public function isSubscription(): bool
    {
        return $this->transaction_type === self::TYPE_SUBSCRIPTION;
    }

    /**
     * Check if subscription is active.
     */
    public function isSubscriptionActive(): bool
    {
        if (! $this->isSubscription()) {
            return false;
        }

        if ($this->status !== self::STATUS_COMPLETED) {
            return false;
        }

        if ($this->cancel_at_period_end && $this->current_period_end && $this->current_period_end->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Complete the transaction.
     */
    public function complete(): void
    {
        $this->update(['status' => self::STATUS_COMPLETED]);

        // Update item stats
        if ($this->transaction_type === self::TYPE_DOWNLOAD) {
            $this->item->increment('download_count');
        } else {
            $this->item->increment('purchase_count');
        }

        // Update seller stats
        if ($this->amount > 0) {
            $this->seller->recordSale($this->seller_payout);
        }
    }

    /**
     * Record content access.
     */
    public function recordAccess(): void
    {
        $this->increment('access_count');

        if (! $this->first_accessed_at) {
            $this->update(['first_accessed_at' => now()]);
        }
    }
}
