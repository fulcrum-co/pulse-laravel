<?php

namespace App\Console\Commands;

use App\Services\Billing\AutoTopUpService;
use Illuminate\Console\Command;

class ProcessAutoTopUps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'billing:process-auto-topups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending auto top-ups for eligible wallets';

    /**
     * Execute the console command.
     */
    public function handle(AutoTopUpService $autoTopUpService): int
    {
        $results = $autoTopUpService->processAllPending();

        $this->info("Processed: {$results['processed']}, Successful: {$results['successful']}, Failed: {$results['failed']}, Skipped: {$results['skipped']}");

        return Command::SUCCESS;
    }
}
