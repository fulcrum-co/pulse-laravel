<?php

namespace App\Console\Commands;

use App\Models\UserNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ProcessExpiredNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:process-expired';

    /**
     * The console command description.
     */
    protected $description = 'Dismiss notifications that have passed their expiration date';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing expired notifications...');

        $count = UserNotification::query()
            ->whereIn('status', [
                UserNotification::STATUS_UNREAD,
                UserNotification::STATUS_READ,
            ])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update([
                'status' => UserNotification::STATUS_DISMISSED,
                'dismissed_at' => now(),
            ]);

        $this->info("Dismissed {$count} expired notifications.");

        if ($count > 0) {
            Log::info('ProcessExpiredNotifications: Dismissed expired notifications', ['count' => $count]);
        }

        return Command::SUCCESS;
    }
}
