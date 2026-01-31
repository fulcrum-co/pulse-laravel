<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class SellerProfile extends Model
{
    use SoftDeletes;

    // Seller types
    public const TYPE_INDIVIDUAL = 'individual';
    public const TYPE_ORGANIZATION = 'organization';
    public const TYPE_VERIFIED_EDUCATOR = 'verified_educator';

    // Verification badges
    public const BADGE_EDUCATOR = 'educator';
    public const BADGE_EXPERT = 'expert';
    public const BADGE_PARTNER = 'partner';
    public const BADGE_TOP_SELLER = 'top_seller';

    // Stripe account statuses
    public const STRIPE_PENDING = 'pending';
    public const STRIPE_ACTIVE = 'active';
    public const STRIPE_RESTRICTED = 'restricted';
    public const STRIPE_DISABLED = 'disabled';

    protected $fillable = [
        'user_id',
        'org_id',
        'display_name',
        'slug',
        'bio',
        'avatar_url',
        'banner_url',
        'expertise_areas',
        'credentials',
        'seller_type',
        'is_verified',
        'verified_at',
        'verification_badge',
        'stripe_account_id',
        'stripe_account_status',
        'payouts_enabled',
        'total_sales',
        'total_items',
        'lifetime_revenue',
        'ratings_average',
        'ratings_count',
        'followers_count',
        'active',
    ];

    protected $casts = [
        'expertise_areas' => 'array',
        'credentials' => 'array',
        'is_verified' => 'boolean',
        'verified_at' => 'datetime',
        'payouts_enabled' => 'boolean',
        'lifetime_revenue' => 'decimal:2',
        'ratings_average' => 'decimal:2',
        'active' => 'boolean',
    ];

    protected $attributes = [
        'seller_type' => self::TYPE_INDIVIDUAL,
        'is_verified' => false,
        'payouts_enabled' => false,
        'total_sales' => 0,
        'total_items' => 0,
        'lifetime_revenue' => 0,
        'ratings_count' => 0,
        'followers_count' => 0,
        'active' => true,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($profile) {
            if (empty($profile->slug)) {
                $profile->slug = static::generateUniqueSlug($profile->display_name);
            }
        });
    }

    /**
     * Generate a unique slug from display name.
     */
    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get available seller types.
     */
    public static function getSellerTypes(): array
    {
        return [
            self::TYPE_INDIVIDUAL => 'Individual',
            self::TYPE_ORGANIZATION => 'Organization',
            self::TYPE_VERIFIED_EDUCATOR => 'Verified Educator',
        ];
    }

    /**
     * Get available verification badges.
     */
    public static function getVerificationBadges(): array
    {
        return [
            self::BADGE_EDUCATOR => 'Verified Educator',
            self::BADGE_EXPERT => 'Expert',
            self::BADGE_PARTNER => 'Pulse Partner',
            self::BADGE_TOP_SELLER => 'Top Seller',
        ];
    }

    /**
     * User relationship.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Organization relationship.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Marketplace items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MarketplaceItem::class);
    }

    /**
     * Transactions as seller.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(MarketplaceTransaction::class);
    }

    /**
     * Scope to active profiles.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Scope to verified profiles.
     */
    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to profiles with payouts enabled.
     */
    public function scopePayoutsEnabled(Builder $query): Builder
    {
        return $query->where('payouts_enabled', true);
    }

    /**
     * Scope by seller type.
     */
    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('seller_type', $type);
    }

    /**
     * Check if seller can receive payouts.
     */
    public function canReceivePayouts(): bool
    {
        return $this->payouts_enabled && $this->stripe_account_status === self::STRIPE_ACTIVE;
    }

    /**
     * Check if seller has any badge.
     */
    public function hasBadge(): bool
    {
        return $this->verification_badge !== null;
    }

    /**
     * Get published items count.
     */
    public function getPublishedItemsCountAttribute(): int
    {
        return $this->items()->where('status', MarketplaceItem::STATUS_APPROVED)->count();
    }

    /**
     * Increment sales count and revenue.
     */
    public function recordSale(float $amount): void
    {
        $this->increment('total_sales');
        $this->increment('lifetime_revenue', $amount);
    }

    /**
     * Update ratings aggregate.
     */
    public function updateRatingsAggregate(): void
    {
        $stats = MarketplaceReview::whereHas('item', function ($q) {
            $q->where('seller_profile_id', $this->id);
        })
            ->where('status', MarketplaceReview::STATUS_PUBLISHED)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
            ->first();

        $this->update([
            'ratings_average' => $stats->avg_rating,
            'ratings_count' => $stats->count,
        ]);
    }
}
