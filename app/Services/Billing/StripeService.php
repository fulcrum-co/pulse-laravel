<?php

namespace App\Services\Billing;

use App\Exceptions\Billing\PaymentFailedException;
use App\Models\CreditWallet;
use App\Models\Organization;
use Illuminate\Support\Facades\Log;

class StripeService
{
    protected bool $initialized = false;

    public function __construct()
    {
        // Lazy initialization - only set up Stripe when actually used
    }

    /**
     * Check if Stripe SDK is available.
     */
    public function isAvailable(): bool
    {
        return class_exists(\Stripe\Stripe::class);
    }

    /**
     * Initialize Stripe SDK (lazy).
     */
    protected function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        if (!$this->isAvailable()) {
            throw new \RuntimeException('Stripe SDK is not installed. Run: composer require stripe/stripe-php');
        }

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));
        $this->initialized = true;
    }

    /**
     * Get or create a Stripe customer for an organization.
     */
    public function getOrCreateCustomer(Organization $org): string
    {
        $this->initialize();

        if ($org->stripe_customer_id) {
            return $org->stripe_customer_id;
        }

        $customer = \Stripe\Customer::create([
            'email' => $org->billing_contact_email ?? $org->email,
            'name' => $org->org_name,
            'metadata' => [
                'org_id' => $org->id,
                'org_type' => $org->org_type,
            ],
        ]);

        $org->update(['stripe_customer_id' => $customer->id]);

        return $customer->id;
    }

    /**
     * Create a checkout session for purchasing credits.
     */
    public function createCheckoutSession(
        int $orgId,
        float $dollarAmount,
        string $successUrl,
        string $cancelUrl
    ): string {
        $this->initialize();

        $org = Organization::findOrFail($orgId);
        $customerId = $this->getOrCreateCustomer($org);

        // Calculate credits they'll receive
        $creditInfo = CreditWallet::getCreditsForAmount($dollarAmount);

        $session = \Stripe\Checkout\Session::create([
            'customer' => $customerId,
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => (int) ($dollarAmount * 100), // Convert to cents
                    'product_data' => [
                        'name' => 'Pulse Credits',
                        'description' => sprintf(
                            '%s credits at %s tier (%s credits/$1)',
                            number_format($creditInfo['credits']),
                            ucfirst($creditInfo['tier']),
                            number_format($creditInfo['yield_per_dollar'])
                        ),
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl.'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'org_id' => $orgId,
                'dollar_amount' => $dollarAmount,
                'credits' => $creditInfo['credits'],
                'tier' => $creditInfo['tier'],
            ],
        ]);

        return $session->url;
    }

    /**
     * Retrieve a checkout session by ID.
     */
    public function getCheckoutSession(string $sessionId): \Stripe\Checkout\Session
    {
        $this->initialize();

        return \Stripe\Checkout\Session::retrieve($sessionId);
    }

    /**
     * Process a successful checkout session.
     */
    public function processCheckoutComplete(\Stripe\Checkout\Session $session): array
    {
        $metadata = $session->metadata->toArray();
        $orgId = (int) $metadata['org_id'];
        $credits = (float) $metadata['credits'];
        $dollarAmount = (float) $metadata['dollar_amount'];
        $tier = $metadata['tier'];

        // Add credits to wallet
        $wallet = CreditWallet::forOrg($orgId);
        $transaction = $wallet->addCredits(
            $credits,
            'Credit purchase via Stripe',
            [
                'stripe_session_id' => $session->id,
                'payment_intent_id' => $session->payment_intent,
                'amount_paid' => $dollarAmount,
                'tier' => $tier,
            ]
        );

        // Update tier based on lifetime purchases
        $wallet->updateTier();

        // Clear grace period if they were in one
        $wallet->clearGracePeriod();

        Log::info('Stripe checkout completed', [
            'org_id' => $orgId,
            'credits' => $credits,
            'amount' => $dollarAmount,
            'tier' => $tier,
        ]);

        return [
            'org_id' => $orgId,
            'credits' => $credits,
            'transaction_id' => $transaction->id,
            'new_balance' => $wallet->balance,
        ];
    }

    /**
     * Create a setup intent for saving payment method.
     */
    public function createSetupIntent(int $orgId): \Stripe\SetupIntent
    {
        $this->initialize();

        $org = Organization::findOrFail($orgId);
        $customerId = $this->getOrCreateCustomer($org);

        return \Stripe\SetupIntent::create([
            'customer' => $customerId,
            'payment_method_types' => ['card'],
            'metadata' => [
                'org_id' => $orgId,
            ],
        ]);
    }

    /**
     * Save a payment method as the default for an organization.
     */
    public function saveDefaultPaymentMethod(int $orgId, string $paymentMethodId): void
    {
        $this->initialize();

        $org = Organization::findOrFail($orgId);
        $customerId = $this->getOrCreateCustomer($org);

        // Attach the payment method to the customer
        $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->attach(['customer' => $customerId]);

        // Set as default
        \Stripe\Customer::update($customerId, [
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodId,
            ],
        ]);

        $org->update(['default_payment_method_id' => $paymentMethodId]);
    }

    /**
     * Get the default payment method for an organization.
     */
    public function getDefaultPaymentMethod(int $orgId): ?array
    {
        $org = Organization::findOrFail($orgId);

        if (!$org->default_payment_method_id) {
            return null;
        }

        if (!$this->isAvailable()) {
            return null;
        }

        $this->initialize();

        try {
            $pm = \Stripe\PaymentMethod::retrieve($org->default_payment_method_id);

            return [
                'id' => $pm->id,
                'brand' => $pm->card->brand,
                'last4' => $pm->card->last4,
                'exp_month' => $pm->card->exp_month,
                'exp_year' => $pm->card->exp_year,
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to retrieve payment method', [
                'org_id' => $orgId,
                'payment_method_id' => $org->default_payment_method_id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Charge the saved payment method (for auto top-up).
     */
    public function chargeCustomer(int $orgId, float $dollarAmount): \Stripe\PaymentIntent
    {
        $this->initialize();

        $org = Organization::findOrFail($orgId);

        if (!$org->stripe_customer_id || !$org->default_payment_method_id) {
            throw new PaymentFailedException('No payment method on file');
        }

        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => (int) ($dollarAmount * 100),
                'currency' => 'usd',
                'customer' => $org->stripe_customer_id,
                'payment_method' => $org->default_payment_method_id,
                'off_session' => true,
                'confirm' => true,
                'description' => 'Pulse Credits - Auto Top-Up',
                'metadata' => [
                    'org_id' => $orgId,
                    'type' => 'auto_topup',
                ],
            ]);

            return $paymentIntent;

        } catch (\Stripe\Exception\CardException $e) {
            $error = $e->getError();
            throw new PaymentFailedException(
                $e->getMessage(),
                $error->code ?? null,
                $error->decline_code ?? null
            );
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            throw new PaymentFailedException($e->getMessage());
        }
    }

    /**
     * List all payment methods for an organization.
     */
    public function listPaymentMethods(int $orgId): array
    {
        if (!$this->isAvailable()) {
            return [];
        }

        $this->initialize();

        $org = Organization::findOrFail($orgId);

        if (!$org->stripe_customer_id) {
            return [];
        }

        $methods = \Stripe\PaymentMethod::all([
            'customer' => $org->stripe_customer_id,
            'type' => 'card',
        ]);

        return collect($methods->data)->map(function ($pm) use ($org) {
            return [
                'id' => $pm->id,
                'brand' => $pm->card->brand,
                'last4' => $pm->card->last4,
                'exp_month' => $pm->card->exp_month,
                'exp_year' => $pm->card->exp_year,
                'is_default' => $pm->id === $org->default_payment_method_id,
            ];
        })->toArray();
    }

    /**
     * Remove a payment method.
     */
    public function removePaymentMethod(int $orgId, string $paymentMethodId): void
    {
        $this->initialize();

        $org = Organization::findOrFail($orgId);

        $paymentMethod = \Stripe\PaymentMethod::retrieve($paymentMethodId);
        $paymentMethod->detach();

        // If this was the default, clear it
        if ($org->default_payment_method_id === $paymentMethodId) {
            $org->update(['default_payment_method_id' => null]);

            // Disable auto top-up if no payment method
            $wallet = CreditWallet::forOrg($orgId);
            if ($wallet->auto_topup_enabled) {
                $wallet->update(['auto_topup_enabled' => false]);
            }
        }
    }

    /**
     * Create a customer portal session.
     */
    public function createPortalSession(int $orgId, string $returnUrl): string
    {
        $this->initialize();

        $org = Organization::findOrFail($orgId);
        $customerId = $this->getOrCreateCustomer($org);

        $session = \Stripe\BillingPortal\Session::create([
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);

        return $session->url;
    }
}
