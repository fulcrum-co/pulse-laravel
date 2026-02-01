<?php

namespace App\Console\Commands;

use App\Models\UserNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupOldNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:cleanup {--days=90 : Days to retain resolved/dismissed notifications}';

    /**
     * The console command description.
     */
    protected $description = 'Delete old resolved and dismissed notifications';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $cutoff = now()->subDays($days);

        $this->info("Cleaning up notifications older than {$days} days...");

        $count = UserNotification::query()
            ->whereIn('status', [
                UserNotification::STATUS_RESOLVED,
                UserNotification::STATUS_DISMISSED,
            ])
            ->where('updated_at', '<', $cutoff)
            ->delete();

        $this->info("Deleted {$count} old notifications.");

        if ($count > 0) {
            Log::info('CleanupOldNotifications: Deleted old notifications', [
                'count' => $count,
                'days' => $days,
            ]);
        }

        return Command::SUCCESS;
    }
}
