<?php

namespace App\Services\Billing;

use App\Models\CreditTransaction;
use App\Models\CreditWallet;
use App\Models\Organization;
use App\Models\User;
use App\Notifications\Billing\CriticalBalanceAlert;
use App\Notifications\Billing\LowBalanceAlert;
use App\Notifications\Billing\UsageSpikeAlert;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class UsageWatchdog
{
    /**
     * Alert thresholds.
     */
    protected const LOW_BALANCE_THRESHOLD = 25;    // 25% of typical capacity

    protected const CRITICAL_BALANCE_THRESHOLD = 10; // 10% of typical capacity

    protected const SPIKE_MULTIPLIER = 3;           // 3x average is a spike

    /**
     * Check health of a specific organization's wallet.
     */
    public function checkHealth(int $orgId): array
    {
        $wallet = CreditWallet::forOrg($orgId);
        $alerts = [];

        // Calculate burn rate
        $dailyBurn = $this->calculateDailyBurn($orgId);
        $weeklyAvgBurn = $wallet->getAverageDailyBurn(7);
        $monthlyAvgBurn = $wallet->getAverageDailyBurn(30);

        // Balance alerts
        $balancePercent = $wallet->getBalancePercentage();

        if ($balancePercent <= self::CRITICAL_BALANCE_THRESHOLD) {
            $alerts[] = $this->sendCriticalBalanceAlert($orgId, $wallet->balance, $balancePercent);
        } elseif ($balancePercent <= self::LOW_BALANCE_THRESHOLD) {
            $alerts[] = $this->sendLowBalanceAlert($orgId, $wallet->balance, $balancePercent);
        }

        // Velocity spike detection
        if ($monthlyAvgBurn > 0 && $dailyBurn > $monthlyAvgBurn * self::SPIKE_MULTIPLIER) {
            $alerts[] = $this->sendUsageSpikeAlert($orgId, $dailyBurn, $monthlyAvgBurn);
        }

        Log::info('Wallet health check completed', [
            'org_id' => $orgId,
            'balance' => $wallet->balance,
            'balance_percent' => $balancePercent,
            'daily_burn' => $dailyBurn,
            'weekly_avg_burn' => $weeklyAvgBurn,
            'monthly_avg_burn' => $monthlyAvgBurn,
            'alerts_sent' => count($alerts),
        ]);

        return [
            'balance' => $wallet->balance,
            'balance_percent' => $balancePercent,
            'daily_burn' => $dailyBurn,
            'weekly_avg_burn' => $weeklyAvgBurn,
            'monthly_avg_burn' => $monthlyAvgBurn,
            'forecast_depletion' => $this->forecastDepletion($orgId),
            'alerts' => $alerts,
        ];
    }

    /**
     * Check health of all active organizations.
     */
    public function checkAllWallets(): array
    {
        $results = [];

        $wallets = CreditWallet::where('balance', '>', 0)
            ->orWhere('auto_topup_enabled', true)
            ->get();

        foreach ($wallets as $wallet) {
            $results[$wallet->org_id] = $this->checkHealth($wallet->org_id);
        }

        return $results;
    }

    /**
     * Calculate today's burn rate for an organization.
     */
    public function calculateDailyBurn(int $orgId): float
    {
        $usage = CreditTransaction::where('org_id', $orgId)
            ->where('type', CreditTransaction::TYPE_USAGE)
            ->whereDate('created_at', today())
            ->sum('amount');

        return abs($usage);
    }

    /**
     * Forecast when credits will be depleted.
     */
    public function forecastDepletion(int $orgId): ?Carbon
    {
        $wallet = CreditWallet::forOrg($orgId);
        $avgBurn = $wallet->getAverageDailyBurn(7);

        if ($avgBurn <= 0 || $wallet->balance <= 0) {
            return null;
        }

        $daysRemaining = $wallet->balance / $avgBurn;

        return now()->addDays((int) $daysRemaining);
    }

    /**
     * Get usage breakdown by category for a period.
     */
    public function getUsageBreakdown(int $orgId, int $days = 30): array
    {
        $usage = CreditTransaction::where('org_id', $orgId)
            ->where('type', CreditTransaction::TYPE_USAGE)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('action_type, SUM(ABS(amount)) as total')
            ->groupBy('action_type')
            ->pluck('total', 'action_type')
            ->toArray();

        return $usage;
    }

    /**
     * Get daily usage trend for charting.
     */
    public function getDailyTrend(int $orgId, int $days = 30): array
    {
        $trend = [];

        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $usage = CreditTransaction::where('org_id', $orgId)
                ->where('type', CreditTransaction::TYPE_USAGE)
                ->whereDate('created_at', $date)
                ->sum('amount');

            $trend[] = [
                'date' => $date,
                'usage' => abs($usage),
            ];
        }

        return $trend;
    }

    /**
     * Get top consuming child organizations (for parent orgs).
     */
    public function getTopConsumers(int $parentOrgId, int $days = 30, int $limit = 10): array
    {
        $childOrgIds = Organization::where('parent_org_id', $parentOrgId)
            ->pluck('id')
            ->toArray();

        if (empty($childOrgIds)) {
            return [];
        }

        return CreditTransaction::whereIn('org_id', $childOrgIds)
            ->where('type', CreditTransaction::TYPE_USAGE)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('org_id, SUM(ABS(amount)) as total')
            ->groupBy('org_id')
            ->orderByDesc('total')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $org = Organization::find($item->org_id);

                return [
                    'org_id' => $item->org_id,
                    'org_name' => $org?->org_name ?? 'Unknown',
                    'total_usage' => $item->total,
                ];
            })
            ->toArray();
    }

    /**
     * Send low balance alert.
     */
    protected function sendLowBalanceAlert(int $orgId, float $balance, float $percent): array
    {
        $org = Organization::find($orgId);
        $admins = $this->getOrgAdmins($orgId);

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new LowBalanceAlert($org, $balance, $percent));
        }

        return [
            'type' => 'low_balance',
            'balance' => $balance,
            'percent' => $percent,
            'notified' => $admins->count(),
        ];
    }

    /**
     * Send critical balance alert.
     */
    protected function sendCriticalBalanceAlert(int $orgId, float $balance, float $percent): array
    {
        $org = Organization::find($orgId);
        $admins = $this->getOrgAdmins($orgId);

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new CriticalBalanceAlert($org, $balance, $percent));
        }

        return [
            'type' => 'critical_balance',
            'balance' => $balance,
            'percent' => $percent,
            'notified' => $admins->count(),
        ];
    }

    /**
     * Send usage spike alert.
     */
    protected function sendUsageSpikeAlert(int $orgId, float $currentBurn, float $averageBurn): array
    {
        $org = Organization::find($orgId);
        $admins = $this->getOrgAdmins($orgId);

        if ($admins->isNotEmpty()) {
            Notification::send($admins, new UsageSpikeAlert($org, $currentBurn, $averageBurn));
        }

        return [
            'type' => 'usage_spike',
            'current_burn' => $currentBurn,
            'average_burn' => $averageBurn,
            'multiplier' => $currentBurn / $averageBurn,
            'notified' => $admins->count(),
        ];
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
}
