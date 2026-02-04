<?php

namespace App\Console\Commands;

use App\Services\Billing\UsageWatchdog;
use Illuminate\Console\Command;

class CheckWalletHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:check-wallet-health';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check wallet health and send alerts for low balances or usage spikes';

    /**
     * Execute the console command.
     */
    public function handle(UsageWatchdog $watchdog): int
    {
        $results = $watchdog->checkAllWallets();

        $totalAlerts = 0;
        foreach ($results as $orgId => $result) {
            $totalAlerts += count($result['alerts'] ?? []);
        }

        $this->info("Checked ".count($results)." wallets, sent {$totalAlerts} alerts.");

        return Command::SUCCESS;
    }
}
