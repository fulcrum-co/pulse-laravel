<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplacePricing extends Model
{
    // Pricing types
    public const TYPE_FREE = 'free';

    public const TYPE_ONE_TIME = 'one_time';

    public const TYPE_RECURRING = 'recurring';

    // Billing intervals
    public const INTERVAL_MONTH = 'month';

    public const INTERVAL_YEAR = 'year';

    // License types
    public const LICENSE_SINGLE = 'single';

    public const LICENSE_TEAM = 'team';

    public const LICENSE_SITE = 'site';

    public const LICENSE_DISTRICT = 'section';

    protected $table = 'marketplace_pricing';

    protected $fillable = [
        'marketplace_item_id',
        'pricing_type',
        'price',
        'original_price',
        'billing_interval',
        'billing_interval_count',
        'recurring_price',
        'license_type',
        'seat_limit',
        'license_terms',
        'stripe_price_id',
        'stripe_product_id',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'recurring_price' => 'decimal:2',
        'license_terms' => 'array',
        'is_active' => 'boolean',
    ];

    protected $attributes = [
        'pricing_type' => self::TYPE_FREE,
        'license_type' => self::LICENSE_SINGLE,
        'billing_interval_count' => 1,
        'is_active' => true,
    ];

    /**
     * Get available pricing types.
     */
    public static function getPricingTypes(): array
    {
        return [
            self::TYPE_FREE => 'Free',
            self::TYPE_ONE_TIME => 'One-time Purchase',
            self::TYPE_RECURRING => 'Subscription',
        ];
    }

    /**
     * Get available billing intervals.
     */
    public static function getBillingIntervals(): array
    {
        return [
            self::INTERVAL_MONTH => 'Monthly',
            self::INTERVAL_YEAR => 'Yearly',
        ];
    }

    /**
     * Get available license types.
     */
    public static function getLicenseTypes(): array
    {
        return [
            self::LICENSE_SINGLE => 'Single User',
            self::LICENSE_TEAM => 'Team',
            self::LICENSE_SITE => 'Site-wide',
            self::LICENSE_DISTRICT => 'Section-wide',
        ];
    }

    /**
     * Marketplace item relationship.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(MarketplaceItem::class, 'marketplace_item_id');
    }

    /**
     * Scope to active pricing.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by license type.
     */
    public function scopeForLicense(Builder $query, string $type): Builder
    {
        return $query->where('license_type', $type);
    }

    /**
     * Check if this is free.
     */
    public function isFree(): bool
    {
        return $this->pricing_type === self::TYPE_FREE;
    }

    /**
     * Check if this is a subscription.
     */
    public function isRecurring(): bool
    {
        return $this->pricing_type === self::TYPE_RECURRING;
    }

    /**
     * Get display price.
     */
    public function getDisplayPrice(): string
    {
        if ($this->isFree()) {
            return 'Free';
        }

        $price = $this->isRecurring() ? $this->recurring_price : $this->price;
        $formatted = '$'.number_format($price, 2);

        if ($this->isRecurring()) {
            $interval = $this->billing_interval === self::INTERVAL_MONTH ? '/mo' : '/yr';
            $formatted .= $interval;
        }

        return $formatted;
    }

    /**
     * Check if there's a discount.
     */
    public function hasDiscount(): bool
    {
        return $this->original_price !== null && $this->original_price > $this->price;
    }

    /**
     * Get discount percentage.
     */
    public function getDiscountPercentage(): ?int
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        return (int) round((($this->original_price - $this->price) / $this->original_price) * 100);
    }
}
