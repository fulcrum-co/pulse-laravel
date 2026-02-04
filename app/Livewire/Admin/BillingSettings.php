<?php

namespace App\Livewire\Admin;

use App\Models\CreditRateCard;
use App\Models\CreditTransaction;
use App\Models\CreditWallet;
use App\Models\FeatureValve;
use App\Models\Organization;
use App\Services\Billing\AutoTopUpService;
use App\Services\Billing\FeatureManager;
use App\Services\Billing\PricingOracle;
use App\Services\Billing\StripeService;
use App\Services\Billing\UsageWatchdog;
use Livewire\Component;
use Livewire\WithPagination;

class BillingSettings extends Component
{
    use WithPagination;

    // Active tab
    public string $activeTab = 'overview';

    // Wallet & Balance
    public float $balance = 0;

    public float $lifetimePurchased = 0;

    public float $lifetimeUsed = 0;

    public string $pricingTier = 'starter';

    public bool $inGracePeriod = false;

    // Auto Top-Up Settings
    public bool $autoTopUpEnabled = false;

    public ?float $autoTopUpThreshold = null;

    public ?float $autoTopUpAmount = null;

    public int $autoTopUpMonthlyLimit = 3;

    public int $autoTopUpCountThisMonth = 0;

    // Purchase Form
    public float $purchaseAmount = 100;

    public int $purchaseCredits = 100000;

    public string $purchaseTier = 'starter';

    // Feature Valves
    public array $featureValves = [];

    // Payment Method
    public ?array $paymentMethod = null;

    // Usage Data
    public array $usageBreakdown = [];

    public array $dailyTrend = [];

    public ?string $depletionForecast = null;

    public float $dailyBurnRate = 0;

    // Transaction History
    public string $transactionFilter = 'all';

    protected StripeService $stripeService;

    protected PricingOracle $pricingOracle;

    protected FeatureManager $featureManager;

    protected UsageWatchdog $usageWatchdog;

    protected AutoTopUpService $autoTopUpService;

    public function boot(
        StripeService $stripeService,
        PricingOracle $pricingOracle,
        FeatureManager $featureManager,
        UsageWatchdog $usageWatchdog,
        AutoTopUpService $autoTopUpService
    ): void {
        $this->stripeService = $stripeService;
        $this->pricingOracle = $pricingOracle;
        $this->featureManager = $featureManager;
        $this->usageWatchdog = $usageWatchdog;
        $this->autoTopUpService = $autoTopUpService;
    }

    public function mount(?string $tab = null): void
    {
        if ($tab) {
            $this->activeTab = $tab;
        }

        $this->loadWalletData();
        $this->loadFeatureValves();
        $this->loadUsageData();
        $this->loadPaymentMethod();
        $this->calculatePurchaseCredits();
    }

    protected function loadWalletData(): void
    {
        $orgId = auth()->user()->org_id;
        $wallet = CreditWallet::forOrg($orgId);

        $this->balance = $wallet->balance ?? 0;
        $this->lifetimePurchased = $wallet->lifetime_purchased ?? 0;
        $this->lifetimeUsed = $wallet->lifetime_used ?? 0;
        $this->pricingTier = $wallet->pricing_tier ?? 'starter';
        $this->inGracePeriod = $wallet->isInGracePeriod();

        // Auto Top-Up
        $this->autoTopUpEnabled = $wallet->auto_topup_enabled ?? false;
        $this->autoTopUpThreshold = $wallet->auto_topup_threshold;
        $this->autoTopUpAmount = $wallet->auto_topup_amount;
        $this->autoTopUpMonthlyLimit = $wallet->auto_topup_monthly_limit ?? 3;
        $this->autoTopUpCountThisMonth = $wallet->auto_topup_count_this_month ?? 0;
    }

    protected function loadFeatureValves(): void
    {
        $orgId = auth()->user()->org_id;
        $this->featureValves = $this->featureManager->getFeaturesSummary($orgId);
    }

    protected function loadUsageData(): void
    {
        $orgId = auth()->user()->org_id;

        $this->usageBreakdown = $this->usageWatchdog->getUsageBreakdown($orgId);
        $this->dailyTrend = $this->usageWatchdog->getDailyTrend($orgId, 30);
        $this->dailyBurnRate = $this->usageWatchdog->calculateDailyBurn($orgId);

        $depletion = $this->usageWatchdog->forecastDepletion($orgId);
        $this->depletionForecast = $depletion?->format('M j, Y');
    }

    protected function loadPaymentMethod(): void
    {
        $orgId = auth()->user()->org_id;
        $this->paymentMethod = $this->stripeService->getDefaultPaymentMethod($orgId);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function updatedPurchaseAmount(): void
    {
        $this->calculatePurchaseCredits();
    }

    protected function calculatePurchaseCredits(): void
    {
        $creditInfo = CreditWallet::getCreditsForAmount($this->purchaseAmount);
        $this->purchaseCredits = (int) $creditInfo['credits'];
        $this->purchaseTier = $creditInfo['tier'];
    }

    public function startPurchase(): mixed
    {
        $orgId = auth()->user()->org_id;

        try {
            $checkoutUrl = $this->stripeService->createCheckoutSession(
                $orgId,
                $this->purchaseAmount,
                route('settings.billing.success'),
                route('settings.billing')
            );

            return redirect()->away($checkoutUrl);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to start checkout: '.$e->getMessage(),
            ]);

            return null;
        }
    }

    public function saveAutoTopUp(): void
    {
        $this->validate([
            'autoTopUpEnabled' => 'boolean',
            'autoTopUpThreshold' => 'nullable|numeric|min:100',
            'autoTopUpAmount' => 'nullable|numeric|min:10',
            'autoTopUpMonthlyLimit' => 'integer|min:1|max:10',
        ]);

        if ($this->autoTopUpEnabled && ! $this->paymentMethod) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please add a payment method before enabling auto top-up.',
            ]);

            return;
        }

        $orgId = auth()->user()->org_id;

        $this->autoTopUpService->configure(
            $orgId,
            $this->autoTopUpEnabled,
            $this->autoTopUpEnabled ? $this->autoTopUpThreshold : null,
            $this->autoTopUpEnabled ? $this->autoTopUpAmount : null,
            $this->autoTopUpMonthlyLimit
        );

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Auto top-up settings saved.',
        ]);
    }

    public function toggleFeatureValve(string $featureKey): void
    {
        $orgId = auth()->user()->org_id;
        $userId = auth()->id();
        $currentStatus = $this->featureValves[$featureKey]['is_valve_active'] ?? true;

        $this->featureManager->toggleValve(
            $orgId,
            $featureKey,
            ! $currentStatus,
            $userId,
            'Toggled via billing settings'
        );

        $this->loadFeatureValves();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $currentStatus ? 'Feature disabled.' : 'Feature enabled.',
        ]);
    }

    public function managePaymentMethods(): mixed
    {
        $orgId = auth()->user()->org_id;

        try {
            $portalUrl = $this->stripeService->createPortalSession(
                $orgId,
                route('settings.billing')
            );

            return redirect()->away($portalUrl);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Failed to open payment portal: '.$e->getMessage(),
            ]);

            return null;
        }
    }

    public function getTransactionsProperty()
    {
        $orgId = auth()->user()->org_id;
        $query = CreditTransaction::where('org_id', $orgId)
            ->orderBy('created_at', 'desc');

        if ($this->transactionFilter !== 'all') {
            $query->where('type', $this->transactionFilter);
        }

        return $query->paginate(15);
    }

    public function getRateCardsProperty()
    {
        return CreditRateCard::active()->get()->groupBy('category');
    }

    public function getTierConfigProperty(): array
    {
        return CreditWallet::TIER_CONFIG;
    }

    public function getOrganizationProperty()
    {
        return Organization::find(auth()->user()->org_id);
    }

    public function render()
    {
        return view('livewire.admin.billing-settings', [
            'transactions' => $this->transactions,
            'rateCards' => $this->rateCards,
            'tierConfig' => $this->tierConfig,
            'organization' => $this->organization,
        ])->layout('layouts.dashboard', ['title' => 'Billing Settings']);
    }
}
