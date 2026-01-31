<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class MarketplaceReview extends Model
{
    use SoftDeletes;

    // Statuses
    public const STATUS_PENDING = 'pending';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_HIDDEN = 'hidden';
    public const STATUS_FLAGGED = 'flagged';

    protected $fillable = [
        'marketplace_item_id',
        'user_id',
        'org_id',
        'transaction_id',
        'rating',
        'review_text',
        'rating_breakdown',
        'status',
        'is_verified_purchase',
        'helpful_count',
        'seller_response',
        'seller_responded_at',
    ];

    protected $casts = [
        'rating_breakdown' => 'array',
        'is_verified_purchase' => 'boolean',
        'seller_responded_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'is_verified_purchase' => false,
        'helpful_count' => 0,
    ];

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_PUBLISHED => 'Published',
            self::STATUS_HIDDEN => 'Hidden',
            self::STATUS_FLAGGED => 'Flagged',
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
     * User (reviewer) relationship.
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
     * Transaction relationship.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(MarketplaceTransaction::class, 'transaction_id');
    }

    /**
     * Scope to published reviews.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    /**
     * Scope to verified purchases.
     */
    public function scopeVerifiedPurchases(Builder $query): Builder
    {
        return $query->where('is_verified_purchase', true);
    }

    /**
     * Scope by minimum rating.
     */
    public function scopeMinRating(Builder $query, int $rating): Builder
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Scope most helpful first.
     */
    public function scopeMostHelpful(Builder $query): Builder
    {
        return $query->orderByDesc('helpful_count');
    }

    /**
     * Check if review is published.
     */
    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    /**
     * Publish the review.
     */
    public function publish(): void
    {
        $this->update(['status' => self::STATUS_PUBLISHED]);
        $this->item->updateRatingsAggregate();
        $this->item->seller->updateRatingsAggregate();
    }

    /**
     * Hide the review.
     */
    public function hide(): void
    {
        $this->update(['status' => self::STATUS_HIDDEN]);
        $this->item->updateRatingsAggregate();
        $this->item->seller->updateRatingsAggregate();
    }

    /**
     * Flag the review.
     */
    public function flag(): void
    {
        $this->update(['status' => self::STATUS_FLAGGED]);
    }

    /**
     * Add seller response.
     */
    public function addSellerResponse(string $response): void
    {
        $this->update([
            'seller_response' => $response,
            'seller_responded_at' => now(),
        ]);
    }

    /**
     * Mark as helpful.
     */
    public function markHelpful(): void
    {
        $this->increment('helpful_count');
    }

    /**
     * Get star display (e.g., "★★★★☆").
     */
    public function getStarDisplayAttribute(): string
    {
        $filled = str_repeat('★', $this->rating);
        $empty = str_repeat('☆', 5 - $this->rating);
        return $filled . $empty;
    }
}
