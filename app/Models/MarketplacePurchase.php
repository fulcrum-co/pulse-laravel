<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketplacePurchase extends Model
{
    protected $fillable = [
        'transaction_id',
        'marketplace_item_id',
        'user_id',
        'org_id',
        'has_access',
        'access_granted_at',
        'access_expires_at',
        'access_revoked_at',
        'downloads_remaining',
        'last_accessed_at',
    ];

    protected $casts = [
        'has_access' => 'boolean',
        'access_granted_at' => 'datetime',
        'access_expires_at' => 'datetime',
        'access_revoked_at' => 'datetime',
        'last_accessed_at' => 'datetime',
    ];

    protected $attributes = [
        'has_access' => true,
    ];

    /**
     * Transaction relationship.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(MarketplaceTransaction::class, 'transaction_id');
    }

    /**
     * Marketplace item relationship.
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(MarketplaceItem::class, 'marketplace_item_id');
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
     * Scope to active access.
     */
    public function scopeWithAccess(Builder $query): Builder
    {
        return $query->where('has_access', true)
            ->where(function ($q) {
                $q->whereNull('access_expires_at')
                    ->orWhere('access_expires_at', '>', now());
            });
    }

    /**
     * Scope to expired access.
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('access_expires_at', '<=', now());
    }

    /**
     * Scope by user.
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope by organization.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Check if user currently has access.
     */
    public function hasActiveAccess(): bool
    {
        if (! $this->has_access) {
            return false;
        }

        if ($this->access_revoked_at) {
            return false;
        }

        if ($this->access_expires_at && $this->access_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if access is expiring soon (within 7 days).
     */
    public function isExpiringSoon(): bool
    {
        if (! $this->access_expires_at) {
            return false;
        }

        return $this->access_expires_at->isBetween(now(), now()->addDays(7));
    }

    /**
     * Grant access.
     */
    public function grantAccess(?int $downloadsLimit = null, ?\DateTime $expiresAt = null): void
    {
        $this->update([
            'has_access' => true,
            'access_granted_at' => now(),
            'access_expires_at' => $expiresAt,
            'access_revoked_at' => null,
            'downloads_remaining' => $downloadsLimit,
        ]);
    }

    /**
     * Revoke access.
     */
    public function revokeAccess(): void
    {
        $this->update([
            'has_access' => false,
            'access_revoked_at' => now(),
        ]);
    }

    /**
     * Extend access.
     */
    public function extendAccess(\DateTime $newExpiresAt): void
    {
        $this->update(['access_expires_at' => $newExpiresAt]);
    }

    /**
     * Record content access.
     */
    public function recordAccess(): void
    {
        $this->update(['last_accessed_at' => now()]);
        $this->transaction?->recordAccess();
    }

    /**
     * Use a download.
     */
    public function useDownload(): bool
    {
        if ($this->downloads_remaining === null) {
            return true; // Unlimited downloads
        }

        if ($this->downloads_remaining <= 0) {
            return false;
        }

        $this->decrement('downloads_remaining');

        return true;
    }

    /**
     * Get days until expiration.
     */
    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (! $this->access_expires_at) {
            return null;
        }

        return max(0, now()->diffInDays($this->access_expires_at, false));
    }
}
