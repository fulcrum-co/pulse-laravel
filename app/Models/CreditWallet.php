<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditWallet extends Model
{
    protected $fillable = [
        'org_id',
        'parent_wallet_id',
        'wallet_mode',
        'balance',
        'lifetime_purchased',
        'lifetime_used',
        'pricing_tier',
        'auto_topup_enabled',
        'auto_topup_threshold',
        'auto_topup_amount',
        'auto_topup_monthly_limit',
        'auto_topup_count_this_month',
        'grace_period_until',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'lifetime_purchased' => 'decimal:2',
        'lifetime_used' => 'decimal:2',
        'auto_topup_threshold' => 'decimal:2',
        'auto_topup_amount' => 'decimal:2',
        'auto_topup_enabled' => 'boolean',
        'auto_topup_monthly_limit' => 'integer',
        'auto_topup_count_this_month' => 'integer',
        'grace_period_until' => 'datetime',
    ];

    /**
     * Wallet modes.
     */
    public const MODE_SEPARATE = 'separate';

    public const MODE_POOLED = 'pooled';

    /**
     * Pricing tiers.
     */
    public const TIER_STARTER = 'starter';

    public const TIER_GROWTH = 'growth';

    public const TIER_ENTERPRISE = 'enterprise';

    public const TIER_STRATEGIC = 'strategic';

    /**
     * Tier multipliers for credit yield.
     */
    public const TIER_CONFIG = [
        self::TIER_STARTER => ['min_deposit' => 0, 'max_deposit' => 4999, 'multiplier' => 3.0, 'yield' => 1000],
        self::TIER_GROWTH => ['min_deposit' => 5000, 'max_deposit' => 14999, 'multiplier' => 2.5, 'yield' => 1200],
        self::TIER_ENTERPRISE => ['min_deposit' => 15000, 'max_deposit' => 49999, 'multiplier' => 2.2, 'yield' => 1350],
        self::TIER_STRATEGIC => ['min_deposit' => 50000, 'max_deposit' => PHP_INT_MAX, 'multiplier' => 2.0, 'yield' => 1500],
    ];

    /**
     * Get the organization this wallet belongs to.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the parent wallet (for pooled mode).
     */
    public function parentWallet(): BelongsTo
    {
        return $this->belongsTo(CreditWallet::class, 'parent_wallet_id');
    }

    /**
     * Get child wallets (for pooled mode).
     */
    public function childWallets(): HasMany
    {
        return $this->hasMany(CreditWallet::class, 'parent_wallet_id');
    }

    /**
     * Get all transactions for this wallet.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class, 'wallet_id')->orderBy('created_at', 'desc');
    }

    /**
     * Get or create wallet for an organization.
     */
    public static function forOrg(int $orgId): self
    {
        return static::firstOrCreate(
            ['org_id' => $orgId],
            [
                'wallet_mode' => self::MODE_SEPARATE,
                'balance' => 0,
                'pricing_tier' => self::TIER_STARTER,
            ]
        );
    }

    /**
     * Check if wallet has sufficient balance.
     */
    public function hasBalance(float $amount): bool
    {
        // For pooled mode, check parent wallet
        if ($this->wallet_mode === self::MODE_POOLED && $this->parentWallet) {
            return $this->parentWallet->hasBalance($amount);
        }

        return $this->balance >= $amount;
    }

    /**
     * Get effective balance (own or parent's for pooled).
     */
    public function getEffectiveBalance(): float
    {
        if ($this->wallet_mode === self::MODE_POOLED && $this->parentWallet) {
            return $this->parentWallet->balance;
        }

        return $this->balance;
    }

    /**
     * Deduct credits from wallet.
     */
    public function deduct(float $amount, string $description, array $metadata = [], ?int $userId = null): CreditTransaction
    {
        // For pooled mode, deduct from parent wallet
        $targetWallet = ($this->wallet_mode === self::MODE_POOLED && $this->parentWallet)
            ? $this->parentWallet
            : $this;

        $targetWallet->balance -= $amount;
        $targetWallet->lifetime_used += $amount;
        $targetWallet->save();

        return CreditTransaction::create([
            'org_id' => $this->org_id,
            'wallet_id' => $targetWallet->id,
            'type' => CreditTransaction::TYPE_USAGE,
            'amount' => -$amount,
            'balance_after' => $targetWallet->balance,
            'action_type' => $metadata['action_type'] ?? null,
            'description' => $description,
            'metadata' => $metadata,
            'user_id' => $userId,
        ]);
    }

    /**
     * Add credits to wallet.
     */
    public function addCredits(float $amount, string $description, array $metadata = [], ?int $userId = null, string $type = CreditTransaction::TYPE_PURCHASE): CreditTransaction
    {
        $this->balance += $amount;
        $this->lifetime_purchased += $amount;
        $this->save();

        return CreditTransaction::create([
            'org_id' => $this->org_id,
            'wallet_id' => $this->id,
            'type' => $type,
            'amount' => $amount,
            'balance_after' => $this->balance,
            'description' => $description,
            'metadata' => $metadata,
            'user_id' => $userId,
        ]);
    }

    /**
     * Check if currently in grace period.
     */
    public function isInGracePeriod(): bool
    {
        return $this->grace_period_until && $this->grace_period_until->isFuture();
    }

    /**
     * Enable grace period for failed payments.
     */
    public function enableGracePeriod(int $hours = 24): void
    {
        $this->grace_period_until = now()->addHours($hours);
        $this->save();
    }

    /**
     * Clear grace period.
     */
    public function clearGracePeriod(): void
    {
        $this->grace_period_until = null;
        $this->save();
    }

    /**
     * Check if auto top-up should be triggered.
     */
    public function shouldAutoTopUp(): bool
    {
        if (! $this->auto_topup_enabled) {
            return false;
        }

        if (! $this->auto_topup_threshold || ! $this->auto_topup_amount) {
            return false;
        }

        return $this->balance <= $this->auto_topup_threshold;
    }

    /**
     * Check if auto top-up is allowed (monthly limit not reached).
     */
    public function canAutoTopUp(): bool
    {
        return $this->auto_topup_count_this_month < $this->auto_topup_monthly_limit;
    }

    /**
     * Record an auto top-up (increment monthly counter).
     */
    public function recordAutoTopUp(): void
    {
        $this->auto_topup_count_this_month++;
        $this->save();
    }

    /**
     * Reset monthly auto top-up counter.
     */
    public function resetMonthlyTopUpCount(): void
    {
        $this->auto_topup_count_this_month = 0;
        $this->save();
    }

    /**
     * Get balance as percentage of typical capacity.
     */
    public function getBalancePercentage(): float
    {
        $typicalCapacity = $this->lifetime_purchased > 0 ? $this->lifetime_purchased : 10000;

        return ($this->balance / $typicalCapacity) * 100;
    }

    /**
     * Calculate average daily burn rate over given days.
     */
    public function getAverageDailyBurn(int $days = 7): float
    {
        $usageTotal = $this->transactions()
            ->where('type', CreditTransaction::TYPE_USAGE)
            ->where('created_at', '>=', now()->subDays($days))
            ->sum('amount');

        return abs($usageTotal) / $days;
    }

    /**
     * Calculate credits per dollar based on tier.
     */
    public static function getCreditsForAmount(float $dollarAmount): array
    {
        $tier = self::getTierForAmount($dollarAmount);
        $config = self::TIER_CONFIG[$tier];
        $credits = $dollarAmount * $config['yield'];

        return [
            'tier' => $tier,
            'credits' => $credits,
            'multiplier' => $config['multiplier'],
            'yield_per_dollar' => $config['yield'],
        ];
    }

    /**
     * Determine pricing tier based on purchase amount.
     */
    public static function getTierForAmount(float $dollarAmount): string
    {
        foreach (self::TIER_CONFIG as $tier => $config) {
            if ($dollarAmount >= $config['min_deposit'] && $dollarAmount <= $config['max_deposit']) {
                return $tier;
            }
        }

        return self::TIER_STARTER;
    }

    /**
     * Update pricing tier based on lifetime purchases.
     */
    public function updateTier(): void
    {
        $this->pricing_tier = self::getTierForAmount($this->lifetime_purchased / 1000); // Convert back to dollars
        $this->save();
    }
}
