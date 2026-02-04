<?php

namespace App\Exceptions\Billing;

use Exception;

class InsufficientCreditsException extends Exception
{
    public float $requiredCredits;

    public float $availableCredits;

    public function __construct(float $requiredCredits, float $availableCredits)
    {
        $this->requiredCredits = $requiredCredits;
        $this->availableCredits = $availableCredits;

        $message = sprintf(
            'Insufficient credits. Required: %s, Available: %s',
            number_format($requiredCredits, 0),
            number_format($availableCredits, 0)
        );

        parent::__construct($message);
    }

    /**
     * Get the shortage amount.
     */
    public function getShortage(): float
    {
        return $this->requiredCredits - $this->availableCredits;
    }

    /**
     * Get user-friendly message.
     */
    public function getUserMessage(): string
    {
        return sprintf(
            'You need %s more credits to complete this action. Please purchase additional credits.',
            number_format($this->getShortage(), 0)
        );
    }
}
