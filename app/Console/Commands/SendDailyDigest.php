<?php

namespace App\Console\Commands;

use App\Mail\DigestEmail;
use App\Models\NotificationDigest;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailyDigest extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:send-daily-digest
        {--user= : Send to a specific user ID}
        {--dry-run : Preview without sending}';

    /**
     * The console command description.
     */
    protected $description = 'Send daily digest emails to users in current 15-minute window';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $windowStart = $now->copy()->second(0)->minute(floor($now->minute / 15) * 15);
        $windowEnd = $windowStart->copy()->addMinutes(15);

        $this->info("Processing daily digests for time window: {$windowStart->format('H:i')} - {$windowEnd->format('H:i')}");

        // If specific user requested, just process that user
        if ($userId = $this->option('user')) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User {$userId} not found.");
                return Command::FAILURE;
            }

            return $this->processUser($user, 'daily') ? Command::SUCCESS : Command::FAILURE;
        }

        // Find users whose digest time falls in current window
        $users = User::whereNotNull('notification_preferences')
            ->whereNotNull('email')
            ->get()
            ->filter(function ($user) use ($windowStart, $windowEnd) {
                $prefs = $user->notification_preferences;
                $digest = $prefs['digest'] ?? [];

                // Check if digest is enabled and frequency includes daily
                if (!($digest['enabled'] ?? false)) {
                    return false;
                }

                $frequency = $digest['frequency'] ?? 'daily';
                if (!in_array($frequency, ['daily', 'both'])) {
                    return false;
                }

                // Check if user's preferred time falls in current window
                $digestTime = $digest['time'] ?? '07:00';
                $userTime = Carbon::createFromFormat('H:i', $digestTime);

                return $userTime->format('H:i') >= $windowStart->format('H:i')
                    && $userTime->format('H:i') < $windowEnd->format('H:i');
            });

        $this->info("Found {$users->count()} users for daily digest.");

        $sent = 0;
        $skipped = 0;

        foreach ($users as $user) {
            if ($this->processUser($user, 'daily')) {
                $sent++;
            } else {
                $skipped++;
            }
        }

        $this->info("Daily digest complete. Sent: {$sent}, Skipped: {$skipped}");

        return Command::SUCCESS;
    }

    /**
     * Process digest for a single user.
     */
    protected function processUser(User $user, string $type): bool
    {
        // Check if already sent recently (prevent duplicates)
        if (NotificationDigest::wasSentRecently($user->id, $type, 120)) {
            $this->line("  Skipping {$user->email} - digest sent recently");
            return false;
        }

        // Get the last digest
        $lastDigest = NotificationDigest::getLastDigestForUser($user->id, $type);
        $since = $lastDigest?->sent_at ?? now()->subDay();

        // Get unread notifications since last digest
        $notifications = UserNotification::forUser($user->id)
            ->where('status', UserNotification::STATUS_UNREAD)
            ->where('created_at', '>', $since)
            ->orderByPriorityAndDate()
            ->limit(100)
            ->get();

        if ($notifications->isEmpty()) {
            $this->line("  Skipping {$user->email} - no new notifications");
            return false;
        }

        // Group by category
        $grouped = $notifications->groupBy('category');

        if ($this->option('dry-run')) {
            $this->info("  Would send to {$user->email}: {$notifications->count()} notifications in {$grouped->count()} categories");
            return true;
        }

        try {
            // Send email
            Mail::to($user)->send(new DigestEmail($user, $grouped, $type));

            // Record digest
            NotificationDigest::create([
                'user_id' => $user->id,
                'digest_type' => $type,
                'notification_ids' => $notifications->pluck('id')->toArray(),
                'notification_count' => $notifications->count(),
                'sent_at' => now(),
            ]);

            $this->info("  Sent digest to {$user->email}: {$notifications->count()} notifications");
            return true;
        } catch (\Exception $e) {
            $this->error("  Failed to send to {$user->email}: {$e->getMessage()}");
            return false;
        }
    }
}
