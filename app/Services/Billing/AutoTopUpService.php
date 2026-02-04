<?php

namespace App\Services\Billing;

use App\Exceptions\Billing\PaymentFailedException;
use App\Models\CreditTransaction;
use App\Models\CreditWallet;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\Billing\AutoTopUpSuccessNotification;
use App\Notifications\Billing\HardCapReachedNotification;
use App\Notifications\Billing\PaymentFailedNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class AutoTopUpService
{
    public function __construct(
        protected StripeService $stripeService
    ) {}

    /**
     * Process auto top-up if needed for an organization.
     */
    public function processIfNeeded(int $orgId): ?CreditTransaction
    {
        $wallet = CreditWallet::forOrg($orgId);

        // Check if auto top-up should trigger
        if (! $wallet->shouldAutoTopUp()) {
            return null;
        }

        // Check monthly limit
        if (! $wallet->canAutoTopUp()) {
            $this->sendHardCapReachedAlert($orgId);
            Log::info('Auto top-up blocked: monthly limit reached', [
                'org_id' => $orgId,
                'count_this_month' => $wallet->auto_topup_count_this_month,
                'monthly_limit' => $wallet->auto_topup_monthly_limit,
            ]);

            return null;
        }

        // Check if org has payment method
        $org = Organization::find($orgId);
        if (! $org?->default_payment_method_id) {
            Log::warning('Auto top-up skipped: no payment method', ['org_id' => $orgId]);

            return null;
        }

        return $this->executeTopUp($wallet);
    }

    /**
     * Execute the auto top-up payment.
     */
    protected function executeTopUp(CreditWallet $wallet): ?CreditTransaction
    {
        $dollarAmount = $wallet->auto_topup_amount;

        try {
            // Process payment via Stripe
            $paymentIntent = $this->stripeService->chargeCustomer($wallet->org_id, $dollarAmount);

            // Calculate credits based on tier
            $creditInfo = CreditWallet::getCreditsForAmount($dollarAmount);

            // Add credits to wallet
            $transaction = $wallet->addCredits(
                $creditInfo['credits'],
                'Auto top-up',
                [
                    'tier' => $creditInfo['tier'],
                    'amount_charged' => $dollarAmount,
                    'payment_intent_id' => $paymentIntent->id,
                    'trigger_balance' => $wallet->balance - $creditInfo['credits'], // Balance before top-up
                    'threshold' => $wallet->auto_topup_threshold,
                ]
            );

            // Record the top-up
            $wallet->recordAutoTopUp();

            // Clear grace period if they were in one
            $wallet->clearGracePeriod();

            // Update tier based on lifetime purchases
            $wallet->updateTier();

            // Send success notification
            $this->sendSuccessNotification($wallet->org_id, $creditInfo['credits'], $dollarAmount);

            Log::info('Auto top-up successful', [
                'org_id' => $wallet->org_id,
                'credits' => $creditInfo['credits'],
                'amount' => $dollarAmount,
                'tier' => $creditInfo['tier'],
                'new_balance' => $wallet->balance,
            ]);

            return $transaction;

        } catch (PaymentFailedException $e) {
            // Enable grace period
            $wallet->enableGracePeriod(24);

            // Send failure notification
            $this->sendPaymentFailedAlert($wallet->org_id, $e->getMessage());

            Log::error('Auto top-up payment failed', [
                'org_id' => $wallet->org_id,
                'amount' => $dollarAmount,
                'error' => $e->getMessage(),
                'stripe_error_code' => $e->stripeErrorCode,
                'decline_code' => $e->declineCode,
            ]);

            return null;
        }
    }

    /**
     * Process auto top-ups for all eligible organizations.
     */
    public function processAllPending(): array
    {
        $results = [
            'processed' => 0,
            'successful' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        // Find wallets that need top-up
        $wallets = CreditWallet::where('auto_topup_enabled', true)
            ->whereNotNull('auto_topup_threshold')
            ->whereNotNull('auto_topup_amount')
            ->whereRaw('balance <= auto_topup_threshold')
            ->whereRaw('auto_topup_count_this_month < auto_topup_monthly_limit')
            ->get();

        foreach ($wallets as $wallet) {
            $results['processed']++;

            $transaction = $this->processIfNeeded($wallet->org_id);

            if ($transaction) {
                $results['successful']++;
            } elseif ($wallet->balance > $wallet->auto_topup_threshold) {
                $results['skipped']++; // Balance was topped up elsewhere
            } else {
                $results['failed']++;
            }
        }

        Log::info('Auto top-up batch completed', $results);

        return $results;
    }

    /**
     * Configure auto top-up for an organization.
     */
    public function configure(
        int $orgId,
        bool $enabled,
        ?float $threshold = null,
        ?float $amount = null,
        ?int $monthlyLimit = null
    ): CreditWallet {
        $wallet = CreditWallet::forOrg($orgId);

        $wallet->update([
            'auto_topup_enabled' => $enabled,
            'auto_topup_threshold' => $threshold,
            'auto_topup_amount' => $amount,
            'auto_topup_monthly_limit' => $monthlyLimit ?? 3,
        ]);

        Log::info('Auto top-up configured', [
            'org_id' => $orgId,
            'enabled' => $enabled,
            'threshold' => $threshold,
            'amount' => $amount,
            'monthly_limit' => $monthlyLimit,
        ]);

        return $wallet->fresh();
    }

    /**
     * Send success notification.
     */
    protected function sendSuccessNotification(int $orgId, float $credits, float $amount): void
    {
        $org = Organization::find($orgId);
        $admins = $this->getOrgAdmins($orgId);

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new AutoTopUpSuccessNotification($org, $credits, $amount));
        }
    }

    /**
     * Send hard cap reached alert.
     */
    protected function sendHardCapReachedAlert(int $orgId): void
    {
        $org = Organization::find($orgId);
        $wallet = CreditWallet::forOrg($orgId);
        $admins = $this->getOrgAdmins($orgId);

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new HardCapReachedNotification($org, $wallet));
        }
    }

    /**
     * Send payment failed alert.
     */
    protected function sendPaymentFailedAlert(int $orgId, string $errorMessage): void
    {
        $org = Organization::find($orgId);
        $admins = $this->getOrgAdmins($orgId);

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new PaymentFailedNotification($org, $errorMessage));
        }
    }

    /**
     * Get admin users for an organization.
     */
    protected function getOrgAdmins(int $orgId): \Illuminate\Support\Collection
    {
        return User::where('org_id', $orgId)
            ->whereIn('role', ['admin', 'billing'])
            ->get();
    }

    /**
     * Reset monthly top-up counts for all wallets.
     */
    public function resetAllMonthlyCounters(): int
    {
        return CreditWallet::where('auto_topup_count_this_month', '>', 0)
            ->update(['auto_topup_count_this_month' => 0]);
    }
}
