<?php

namespace App\Console\Commands;

use App\Services\Billing\FeatureManager;
use Illuminate\Console\Command;

class ResetDailyFeatureUsage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:reset-daily-usage';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily usage counters on feature valves';

    /**
     * Execute the console command.
     */
    public function handle(FeatureManager $featureManager): int
    {
        $count = $featureManager->resetAllDailyUsage();

        $this->info("Reset daily usage for {$count} feature valves.");

        return Command::SUCCESS;
    }
}
