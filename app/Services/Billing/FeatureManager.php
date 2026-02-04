<?php

namespace App\Services\Billing;

use App\Models\CreditTransaction;
use App\Models\CreditWallet;
use App\Models\FeatureValve;

class FeatureManager
{
    /**
     * Map action types to feature keys.
     */
    protected const ACTION_TO_FEATURE = [
        'ai_analysis' => FeatureValve::FEATURE_AI_ANALYSIS,
        'ai_summary' => FeatureValve::FEATURE_AI_ANALYSIS,
        'ai_course_generation' => FeatureValve::FEATURE_AI_COURSES,
        'transcription_minute' => FeatureValve::FEATURE_VOICE_TRANSCRIPTION,
        'transcription_assemblyai' => FeatureValve::FEATURE_VOICE_TRANSCRIPTION,
        'sms_outbound' => FeatureValve::FEATURE_SMS_OUTREACH,
        'sms_inbound' => FeatureValve::FEATURE_SMS_OUTREACH,
        'whatsapp_outbound' => FeatureValve::FEATURE_WHATSAPP,
        'whatsapp_inbound' => FeatureValve::FEATURE_WHATSAPP,
    ];

    /**
     * Check if a feature is enabled for an organization.
     */
    public function isEnabled(int $orgId, string $actionTypeOrFeatureKey): bool
    {
        // First check if organization has credits
        $wallet = CreditWallet::forOrg($orgId);
        if ($wallet->balance <= 0 && ! $wallet->isInGracePeriod()) {
            return false;
        }

        // Map action type to feature key if needed
        $featureKey = self::ACTION_TO_FEATURE[$actionTypeOrFeatureKey] ?? $actionTypeOrFeatureKey;

        // Get the valve
        $valve = FeatureValve::where('org_id', $orgId)
            ->where('feature_key', $featureKey)
            ->first();

        // If no valve exists, default to enabled
        if (! $valve) {
            return true;
        }

        return $valve->isEnabled();
    }

    /**
     * Get valve for an organization and feature.
     */
    public function getValve(int $orgId, string $actionTypeOrFeatureKey): ?FeatureValve
    {
        $featureKey = self::ACTION_TO_FEATURE[$actionTypeOrFeatureKey] ?? $actionTypeOrFeatureKey;

        return FeatureValve::where('org_id', $orgId)
            ->where('feature_key', $featureKey)
            ->first();
    }

    /**
     * Toggle a feature valve.
     */
    public function toggleValve(
        int $orgId,
        string $featureKey,
        bool $active,
        int $userId,
        ?string $reason = null,
        ?string $reversionMessage = null
    ): FeatureValve {
        $valve = FeatureValve::firstOrCreate(
            ['org_id' => $orgId, 'feature_key' => $featureKey],
            ['is_active' => true]
        );

        $valve->update([
            'is_active' => $active,
            'changed_by' => $userId,
            'changed_at' => now(),
            'change_reason' => $reason,
            'reversion_message' => $reversionMessage,
        ]);

        // Log the change as a transaction for audit trail
        $wallet = CreditWallet::forOrg($orgId);
        CreditTransaction::create([
            'org_id' => $orgId,
            'wallet_id' => $wallet->id,
            'type' => CreditTransaction::TYPE_ADJUSTMENT,
            'amount' => 0,
            'balance_after' => $wallet->balance,
            'action_type' => CreditTransaction::ACTION_FEATURE_VALVE_CHANGE,
            'description' => $active ? "Enabled {$featureKey}" : "Disabled {$featureKey}",
            'metadata' => [
                'feature' => $featureKey,
                'action' => $active ? 'enable' : 'disable',
                'reason' => $reason,
            ],
            'user_id' => $userId,
        ]);

        return $valve;
    }

    /**
     * Set daily limit for a feature.
     */
    public function setDailyLimit(int $orgId, string $featureKey, ?int $limit, int $userId): FeatureValve
    {
        $valve = FeatureValve::firstOrCreate(
            ['org_id' => $orgId, 'feature_key' => $featureKey],
            ['is_active' => true]
        );

        $valve->update([
            'daily_limit' => $limit,
            'changed_by' => $userId,
            'changed_at' => now(),
        ]);

        return $valve;
    }

    /**
     * Increment usage counter for a feature.
     */
    public function incrementUsage(int $orgId, string $actionTypeOrFeatureKey): void
    {
        $featureKey = self::ACTION_TO_FEATURE[$actionTypeOrFeatureKey] ?? $actionTypeOrFeatureKey;

        $valve = FeatureValve::where('org_id', $orgId)
            ->where('feature_key', $featureKey)
            ->first();

        if ($valve && $valve->daily_limit !== null) {
            $valve->incrementUsage();
        }
    }

    /**
     * Get all valves for an organization.
     */
    public function getAllValves(int $orgId): array
    {
        return FeatureValve::getAllForOrg($orgId);
    }

    /**
     * Get features status summary for an organization.
     */
    public function getFeaturesSummary(int $orgId): array
    {
        $summary = [];
        $wallet = CreditWallet::forOrg($orgId);
        $hasCredits = $wallet->balance > 0 || $wallet->isInGracePeriod();

        foreach (FeatureValve::FEATURES as $featureKey => $config) {
            $valve = FeatureValve::where('org_id', $orgId)
                ->where('feature_key', $featureKey)
                ->first();

            $isEnabled = $hasCredits && (! $valve || $valve->isEnabled());

            $summary[$featureKey] = [
                'name' => $config['name'],
                'description' => $config['description'],
                'category' => $config['category'],
                'is_enabled' => $isEnabled,
                'is_valve_active' => $valve?->is_active ?? true,
                'daily_limit' => $valve?->daily_limit,
                'daily_usage' => $valve?->daily_usage ?? 0,
                'remaining_quota' => $valve?->getRemainingQuota(),
                'disabled_reason' => $this->getDisabledReason($hasCredits, $valve),
                'reversion_message' => $valve?->reversion_message,
            ];
        }

        return $summary;
    }

    /**
     * Get reason why feature is disabled.
     */
    protected function getDisabledReason(bool $hasCredits, ?FeatureValve $valve): ?string
    {
        if (! $hasCredits) {
            return 'insufficient_credits';
        }

        if ($valve && ! $valve->is_active) {
            return 'valve_disabled';
        }

        if ($valve && $valve->daily_limit !== null && $valve->daily_usage >= $valve->daily_limit) {
            return 'daily_limit_reached';
        }

        return null;
    }

    /**
     * Reset daily usage for all valves (called by scheduler).
     */
    public function resetAllDailyUsage(): int
    {
        return FeatureValve::whereNotNull('daily_limit')
            ->update(['daily_usage' => 0]);
    }

    /**
     * Bulk enable features for an organization.
     */
    public function enableAllFeatures(int $orgId, int $userId, ?string $reason = null): void
    {
        foreach (array_keys(FeatureValve::FEATURES) as $featureKey) {
            $this->toggleValve($orgId, $featureKey, true, $userId, $reason);
        }
    }

    /**
     * Bulk disable features for an organization.
     */
    public function disableAllFeatures(int $orgId, int $userId, ?string $reason = null, ?string $reversionMessage = null): void
    {
        foreach (array_keys(FeatureValve::FEATURES) as $featureKey) {
            $this->toggleValve($orgId, $featureKey, false, $userId, $reason, $reversionMessage);
        }
    }
}
