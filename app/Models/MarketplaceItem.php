<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class MarketplaceItem extends Model
{
    use SoftDeletes;

    // Categories
    public const CATEGORY_SURVEY = 'survey';

    public const CATEGORY_STRATEGY = 'strategy';

    public const CATEGORY_CONTENT = 'content';

    public const CATEGORY_PROVIDER = 'provider';

    // Pricing types
    public const PRICING_FREE = 'free';

    public const PRICING_ONE_TIME = 'one_time';

    public const PRICING_RECURRING = 'recurring';

    // Statuses
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING_REVIEW = 'pending_review';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'uuid',
        'listable_type',
        'listable_id',
        'seller_profile_id',
        'org_id',
        'title',
        'description',
        'short_description',
        'category',
        'subcategories',
        'tags',
        'thumbnail_url',
        'preview_images',
        'preview_content',
        'target_grades',
        'target_subjects',
        'target_needs',
        'pricing_type',
        'status',
        'is_featured',
        'is_verified',
        'ratings_average',
        'ratings_count',
        'download_count',
        'purchase_count',
        'view_count',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'subcategories' => 'array',
        'tags' => 'array',
        'preview_images' => 'array',
        'preview_content' => 'array',
        'target_grades' => 'array',
        'target_subjects' => 'array',
        'target_needs' => 'array',
        'is_featured' => 'boolean',
        'is_verified' => 'boolean',
        'ratings_average' => 'decimal:2',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'pricing_type' => self::PRICING_FREE,
        'is_featured' => false,
        'is_verified' => false,
        'ratings_count' => 0,
        'download_count' => 0,
        'purchase_count' => 0,
        'view_count' => 0,
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->uuid)) {
                $item->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * Get available categories.
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_SURVEY => 'Surveys',
            self::CATEGORY_STRATEGY => 'Strategies',
            self::CATEGORY_CONTENT => 'Content',
            self::CATEGORY_PROVIDER => 'Providers',
        ];
    }

    /**
     * Get available pricing types.
     */
    public static function getPricingTypes(): array
    {
        return [
            self::PRICING_FREE => 'Free',
            self::PRICING_ONE_TIME => 'One-time Purchase',
            self::PRICING_RECURRING => 'Subscription',
        ];
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_REVIEW => 'Pending Review',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_SUSPENDED => 'Suspended',
        ];
    }

    /**
     * Listable content (polymorphic).
     */
    public function listable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Seller profile.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(SellerProfile::class, 'seller_profile_id');
    }

    /**
     * Organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Creator.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Reviewer.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Pricing options.
     */
    public function pricing(): HasMany
    {
        return $this->hasMany(MarketplacePricing::class);
    }

    /**
     * Primary pricing (default license).
     */
    public function primaryPricing(): HasOne
    {
        return $this->hasOne(MarketplacePricing::class)
            ->where('license_type', 'single')
            ->where('is_active', true);
    }

    /**
     * Reviews.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(MarketplaceReview::class);
    }

    /**
     * Transactions.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(MarketplaceTransaction::class);
    }

    /**
     * Purchases (access records).
     */
    public function purchases(): HasMany
    {
        return $this->hasMany(MarketplacePurchase::class);
    }

    /**
     * Scope to published items.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED)
            ->whereNotNull('published_at');
    }

    /**
     * Scope to pending review.
     */
    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING_REVIEW);
    }

    /**
     * Scope by category.
     */
    public function scopeInCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by pricing type.
     */
    public function scopeWithPricing(Builder $query, string $type): Builder
    {
        return $query->where('pricing_type', $type);
    }

    /**
     * Scope to free items.
     */
    public function scopeFree(Builder $query): Builder
    {
        return $query->where('pricing_type', self::PRICING_FREE);
    }

    /**
     * Scope to featured items.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for search.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'ilike', "%{$term}%")
                ->orWhere('description', 'ilike', "%{$term}%")
                ->orWhere('short_description', 'ilike', "%{$term}%")
                ->orWhereJsonContains('tags', $term);
        });
    }

    /**
     * Scope for grade level.
     */
    public function scopeForGrade(Builder $query, string $grade): Builder
    {
        return $query->whereJsonContains('target_grades', $grade);
    }

    /**
     * Scope with minimum rating.
     */
    public function scopeMinRating(Builder $query, float $rating): Builder
    {
        return $query->where('ratings_average', '>=', $rating);
    }

    /**
     * Check if item is published.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_APPROVED && $this->published_at !== null;
    }

    /**
     * Check if item is free.
     */
    public function isFree(): bool
    {
        return $this->pricing_type === self::PRICING_FREE;
    }

    /**
     * Submit for review.
     */
    public function submitForReview(): void
    {
        $this->update([
            'status' => self::STATUS_PENDING_REVIEW,
            'submitted_at' => now(),
        ]);
    }

    /**
     * Approve and publish.
     */
    public function approve(int $reviewerId, ?string $notes = null): void
    {
        $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'review_notes' => $notes,
            'published_at' => now(),
        ]);

        $this->seller->increment('total_items');
    }

    /**
     * Reject.
     */
    public function reject(int $reviewerId, string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewerId,
            'review_notes' => $reason,
        ]);
    }

    /**
     * Increment view count.
     */
    public function recordView(): void
    {
        $this->increment('view_count');
    }

    /**
     * Update ratings aggregate.
     */
    public function updateRatingsAggregate(): void
    {
        $stats = $this->reviews()
            ->where('status', MarketplaceReview::STATUS_PUBLISHED)
            ->selectRaw('AVG(rating) as avg_rating, COUNT(*) as count')
            ->first();

        $this->update([
            'ratings_average' => $stats->avg_rating,
            'ratings_count' => $stats->count,
        ]);
    }

    /**
     * Get the primary price for display.
     */
    public function getPriceAttribute(): ?float
    {
        $primary = $this->primaryPricing;
        if (! $primary) {
            return null;
        }

        return $primary->pricing_type === self::PRICING_RECURRING
            ? $primary->recurring_price
            : $primary->price;
    }
}
