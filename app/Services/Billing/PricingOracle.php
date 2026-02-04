<?php

namespace App\Services\Billing;

use App\Models\CreditRateCard;
use App\Models\CreditWallet;

class PricingOracle
{
    /**
     * Get the credit cost for an action type.
     */
    public function getCost(string $actionType, ?float $units = 1): float
    {
        $rateCard = CreditRateCard::where('action_type', $actionType)
            ->where('active', true)
            ->first();

        if (! $rateCard) {
            return 0;
        }

        return ceil($rateCard->credit_cost * ($units ?? 1));
    }

    /**
     * Get display rate information for an action type.
     */
    public function getDisplayRate(string $actionType): array
    {
        return CreditRateCard::getDisplayRate($actionType);
    }

    /**
     * Get all active rates grouped by category.
     */
    public function getAllRates(): array
    {
        return CreditRateCard::getGroupedRates();
    }

    /**
     * Get tier configuration.
     */
    public function getTierConfig(): array
    {
        return CreditWallet::TIER_CONFIG;
    }

    /**
     * Calculate credits for a dollar amount based on tier.
     */
    public function calculateCreditsForPurchase(float $dollarAmount): array
    {
        return CreditWallet::getCreditsForAmount($dollarAmount);
    }

    /**
     * Estimate cost for AI operation based on token count.
     */
    public function estimateAiCost(int $inputTokens, int $outputTokens, string $model = 'default'): float
    {
        // Input tokens (typically cheaper)
        $inputCost = $this->getCost('ai_analysis', $inputTokens / 1000);

        // Output tokens (typically more expensive)
        $outputCost = $this->getCost('ai_summary', $outputTokens / 1000);

        return $inputCost + $outputCost;
    }

    /**
     * Estimate cost for transcription based on duration.
     */
    public function estimateTranscriptionCost(float $durationSeconds, string $provider = 'whisper'): float
    {
        $minutes = ceil($durationSeconds / 60);
        $actionType = $provider === 'assemblyai' ? 'transcription_assemblyai' : 'transcription_minute';

        return $this->getCost($actionType, $minutes);
    }

    /**
     * Get vendor cost (for margin calculations/reporting).
     */
    public function getVendorCost(string $actionType): ?float
    {
        $rateCard = CreditRateCard::where('action_type', $actionType)
            ->where('active', true)
            ->first();

        return $rateCard?->vendor_cost;
    }

    /**
     * Get all rates with pricing breakdown.
     */
    public function getRatesWithBreakdown(): array
    {
        $rates = CreditRateCard::active()->get();
        $breakdown = [];

        foreach ($rates as $rate) {
            $breakdown[] = [
                'action_type' => $rate->action_type,
                'display_name' => $rate->display_name,
                'category' => $rate->category,
                'credit_cost' => $rate->credit_cost,
                'vendor_unit' => $rate->vendor_unit,
                'unit_display' => $rate->getUnitDisplayName(),
                'vendor_cost' => $rate->vendor_cost,
                'margin_multiplier' => $rate->credit_cost / ($rate->vendor_cost * 1000),
            ];
        }

        return $breakdown;
    }
}
