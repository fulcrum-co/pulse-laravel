<?php

namespace App\Console\Commands;

use App\Models\UserNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class UnsnoozeNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:unsnooze';

    /**
     * The console command description.
     */
    protected $description = 'Unsnooze notifications whose snooze period has expired';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Processing snoozed notifications...');

        $count = UserNotification::where('status', UserNotification::STATUS_SNOOZED)
            ->whereNotNull('snoozed_until')
            ->where('snoozed_until', '<=', now())
            ->update([
                'status' => UserNotification::STATUS_UNREAD,
                'snoozed_until' => null,
            ]);

        $this->info("Unsnoozed {$count} notifications.");

        if ($count > 0) {
            Log::info('UnsnoozeNotifications: Unsnoozed notifications', ['count' => $count]);
        }

        return Command::SUCCESS;
    }
}
