<?php

namespace App\Exceptions\Billing;

use Exception;

class PaymentFailedException extends Exception
{
    public ?string $stripeErrorCode;

    public ?string $declineCode;

    public function __construct(string $message, ?string $stripeErrorCode = null, ?string $declineCode = null)
    {
        $this->stripeErrorCode = $stripeErrorCode;
        $this->declineCode = $declineCode;

        parent::__construct($message);
    }

    /**
     * Get user-friendly message.
     */
    public function getUserMessage(): string
    {
        if ($this->declineCode) {
            return match ($this->declineCode) {
                'insufficient_funds' => 'Your card has insufficient funds. Please try another payment method.',
                'card_declined' => 'Your card was declined. Please try another payment method.',
                'expired_card' => 'Your card has expired. Please update your payment method.',
                default => 'Payment failed. Please try again or use a different payment method.',
            };
        }

        return 'Payment could not be processed. Please try again or contact support.';
    }
}
