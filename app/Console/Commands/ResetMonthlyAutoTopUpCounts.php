<?php

namespace App\Console\Commands;

use App\Services\Billing\AutoTopUpService;
use Illuminate\Console\Command;

class ResetMonthlyAutoTopUpCounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:reset-monthly-topups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset monthly auto top-up counters on wallets';

    /**
     * Execute the console command.
     */
    public function handle(AutoTopUpService $autoTopUpService): int
    {
        $count = $autoTopUpService->resetAllMonthlyCounters();

        $this->info("Reset monthly top-up counters for {$count} wallets.");

        return Command::SUCCESS;
    }
}
