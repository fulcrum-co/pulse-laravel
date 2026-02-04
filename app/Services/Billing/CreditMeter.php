<?php

namespace App\Services\Billing;

use App\Exceptions\Billing\FeatureDisabledException;
use App\Exceptions\Billing\InsufficientCreditsException;
use App\Models\CreditTransaction;
use App\Models\CreditWallet;
use Closure;
use Illuminate\Support\Facades\Auth;

class CreditMeter
{
    public function __construct(
        protected PricingOracle $pricingOracle,
        protected FeatureManager $featureManager
    ) {}

    /**
     * Execute an operation with credit metering.
     *
     * @param  int  $orgId  Organization ID
     * @param  string  $actionType  The type of action (ai_analysis, transcription_minute, etc.)
     * @param  Closure  $callback  The operation to execute
     * @param  float|null  $estimatedUnits  Estimated units for pre-check (optional)
     * @param  array  $metadata  Additional metadata to store with the transaction
     * @return mixed The result of the callback
     *
     * @throws FeatureDisabledException
     * @throws InsufficientCreditsException
     */
    public function executeWithCredits(
        int $orgId,
        string $actionType,
        Closure $callback,
        ?float $estimatedUnits = null,
        array $metadata = []
    ): mixed {
        // 1. Check feature valve
        if (! $this->featureManager->isEnabled($orgId, $actionType)) {
            $valve = $this->featureManager->getValve($orgId, $actionType);
            throw new FeatureDisabledException(
                $actionType,
                $valve?->reversion_message,
                $valve?->change_reason
            );
        }

        // 2. Get estimated cost from rate card
        $estimatedCost = $this->pricingOracle->getCost($actionType, $estimatedUnits ?? 1);

        // 3. Check wallet balance
        $wallet = CreditWallet::forOrg($orgId);
        if (! $wallet->hasBalance($estimatedCost) && ! $wallet->isInGracePeriod()) {
            throw new InsufficientCreditsException($estimatedCost, $wallet->getEffectiveBalance());
        }

        // 4. Execute the expensive operation
        $result = $callback();

        // 5. Calculate actual cost based on result (if available)
        $actualCost = $this->calculateActualCost($actionType, $result, $estimatedUnits);

        // 6. Deduct credits
        $this->deductCredits($wallet, $actualCost, $actionType, $metadata);

        // 7. Increment feature usage counter
        $this->featureManager->incrementUsage($orgId, $actionType);

        return $result;
    }

    /**
     * Calculate actual cost based on operation result.
     */
    protected function calculateActualCost(string $actionType, mixed $result, ?float $estimatedUnits): float
    {
        // If result contains usage data, use that
        if (is_array($result)) {
            // AI operations often return token usage
            if (isset($result['usage'])) {
                $usage = $result['usage'];
                $totalTokens = ($usage['input_tokens'] ?? 0) + ($usage['output_tokens'] ?? 0);
                if ($totalTokens > 0) {
                    return $this->pricingOracle->getCost($actionType, $totalTokens / 1000);
                }
            }

            // Transcription may return duration
            if (isset($result['duration_seconds'])) {
                $minutes = ceil($result['duration_seconds'] / 60);

                return $this->pricingOracle->getCost($actionType, $minutes);
            }
        }

        // Fall back to estimated units
        return $this->pricingOracle->getCost($actionType, $estimatedUnits ?? 1);
    }

    /**
     * Deduct credits from wallet and log transaction.
     */
    protected function deductCredits(CreditWallet $wallet, float $amount, string $actionType, array $metadata = []): CreditTransaction
    {
        return $wallet->deduct(
            $amount,
            "Usage: {$actionType}",
            array_merge($metadata, ['action_type' => $actionType]),
            Auth::id()
        );
    }

    /**
     * Check if organization has sufficient credits for an action (without executing).
     */
    public function canAfford(int $orgId, string $actionType, ?float $units = null): bool
    {
        $cost = $this->pricingOracle->getCost($actionType, $units ?? 1);
        $wallet = CreditWallet::forOrg($orgId);

        return $wallet->hasBalance($cost) || $wallet->isInGracePeriod();
    }

    /**
     * Get estimated cost for an action.
     */
    public function getEstimatedCost(string $actionType, ?float $units = null): float
    {
        return $this->pricingOracle->getCost($actionType, $units ?? 1);
    }

    /**
     * Check if feature is available for organization.
     */
    public function isFeatureAvailable(int $orgId, string $actionType): bool
    {
        return $this->featureManager->isEnabled($orgId, $actionType);
    }

    /**
     * Get current balance for organization.
     */
    public function getBalance(int $orgId): float
    {
        return CreditWallet::forOrg($orgId)->getEffectiveBalance();
    }

    /**
     * Manually add credits (for purchases, bonuses, etc.).
     */
    public function addCredits(
        int $orgId,
        float $amount,
        string $description,
        string $type = CreditTransaction::TYPE_PURCHASE,
        array $metadata = []
    ): CreditTransaction {
        $wallet = CreditWallet::forOrg($orgId);

        return $wallet->addCredits($amount, $description, $metadata, Auth::id(), $type);
    }

    /**
     * Issue refund.
     */
    public function refund(int $orgId, float $amount, string $reason, array $metadata = []): CreditTransaction
    {
        $wallet = CreditWallet::forOrg($orgId);

        return $wallet->addCredits(
            $amount,
            "Refund: {$reason}",
            $metadata,
            Auth::id(),
            CreditTransaction::TYPE_REFUND
        );
    }

    /**
     * Make an adjustment (positive or negative).
     */
    public function adjust(int $orgId, float $amount, string $reason, array $metadata = []): CreditTransaction
    {
        $wallet = CreditWallet::forOrg($orgId);

        if ($amount > 0) {
            return $wallet->addCredits(
                $amount,
                "Adjustment: {$reason}",
                $metadata,
                Auth::id(),
                CreditTransaction::TYPE_ADJUSTMENT
            );
        }

        return $wallet->deduct(
            abs($amount),
            "Adjustment: {$reason}",
            $metadata,
            Auth::id()
        );
    }
}
