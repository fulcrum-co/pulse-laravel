<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditRateCard extends Model
{
    protected $fillable = [
        'action_type',
        'display_name',
        'category',
        'vendor_cost',
        'vendor_unit',
        'credit_cost',
        'active',
        'metadata',
    ];

    protected $casts = [
        'vendor_cost' => 'decimal:6',
        'credit_cost' => 'decimal:2',
        'active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Categories.
     */
    public const CATEGORY_AI = 'ai';

    public const CATEGORY_VOICE = 'voice';

    public const CATEGORY_TELECOM = 'telecom';

    public const CATEGORY_STORAGE = 'storage';

    /**
     * Vendor units.
     */
    public const UNIT_PER_1K_TOKENS = 'per_1k_tokens';

    public const UNIT_PER_MINUTE = 'per_minute';

    public const UNIT_PER_MESSAGE = 'per_message';

    public const UNIT_PER_MB = 'per_mb';

    /**
     * Get cost for a specific action type.
     */
    public static function getCostForAction(string $actionType): float
    {
        $rateCard = static::where('action_type', $actionType)
            ->where('active', true)
            ->first();

        return $rateCard ? $rateCard->credit_cost : 0;
    }

    /**
     * Calculate cost for given units.
     */
    public static function calculateCost(string $actionType, float $units = 1): float
    {
        $costPerUnit = static::getCostForAction($actionType);

        return ceil($costPerUnit * $units);
    }

    /**
     * Get display rate for an action type.
     */
    public static function getDisplayRate(string $actionType): array
    {
        $rateCard = static::where('action_type', $actionType)
            ->where('active', true)
            ->first();

        if (! $rateCard) {
            return [
                'credits' => 0,
                'unit' => 'unknown',
                'display' => 'N/A',
            ];
        }

        return [
            'credits' => $rateCard->credit_cost,
            'unit' => $rateCard->vendor_unit,
            'display' => $rateCard->credit_cost.' credits/'.$rateCard->getUnitDisplayName(),
        ];
    }

    /**
     * Get human-readable unit name.
     */
    public function getUnitDisplayName(): string
    {
        return match ($this->vendor_unit) {
            self::UNIT_PER_1K_TOKENS => '1K tokens',
            self::UNIT_PER_MINUTE => 'minute',
            self::UNIT_PER_MESSAGE => 'message',
            self::UNIT_PER_MB => 'MB',
            default => $this->vendor_unit,
        };
    }

    /**
     * Scope to filter active rates only.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Get all rates grouped by category.
     */
    public static function getGroupedRates(): array
    {
        return static::active()
            ->get()
            ->groupBy('category')
            ->toArray();
    }

    /**
     * Recalculate credit cost based on vendor cost and default multiplier.
     */
    public function recalculateCreditCost(float $multiplier = 3.0): void
    {
        // Credit cost = vendor_cost * multiplier * 1000 (to get credits per unit)
        $this->credit_cost = ceil($this->vendor_cost * $multiplier * 1000);
        $this->save();
    }
}
