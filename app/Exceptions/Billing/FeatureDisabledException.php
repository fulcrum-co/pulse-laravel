<?php

namespace App\Exceptions\Billing;

use App\Models\FeatureValve;
use Exception;

class FeatureDisabledException extends Exception
{
    public string $featureKey;

    public ?string $reversionMessage;

    public ?string $reason;

    public function __construct(string $featureKey, ?string $reversionMessage = null, ?string $reason = null)
    {
        $this->featureKey = $featureKey;
        $this->reversionMessage = $reversionMessage;
        $this->reason = $reason;

        $featureName = FeatureValve::FEATURES[$featureKey]['name'] ?? $featureKey;
        $message = "Feature '{$featureName}' is currently disabled for this organization.";

        parent::__construct($message);
    }

    /**
     * Get user-friendly message.
     */
    public function getUserMessage(): string
    {
        if ($this->reversionMessage) {
            return $this->reversionMessage;
        }

        $featureName = FeatureValve::FEATURES[$this->featureKey]['name'] ?? $this->featureKey;

        return "{$featureName} is temporarily unavailable. Please contact your administrator.";
    }

    /**
     * Get feature display name.
     */
    public function getFeatureName(): string
    {
        return FeatureValve::FEATURES[$this->featureKey]['name'] ?? $this->featureKey;
    }
}
