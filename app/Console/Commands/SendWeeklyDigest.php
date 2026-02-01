<?php

namespace App\Console\Commands;

use App\Mail\DigestEmail;
use App\Models\NotificationDigest;
use App\Models\User;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendWeeklyDigest extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:send-weekly-digest
        {--user= : Send to a specific user ID}
        {--dry-run : Preview without sending}';

    /**
     * The console command description.
     */
    protected $description = 'Send weekly digest emails to users on their chosen day in current 15-minute window';

    /**
     * Day name map for comparison.
     */
    protected array $dayNames = [
        'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $currentDay = strtolower($now->format('l')); // e.g., "monday"
        $windowStart = $now->copy()->second(0)->minute(floor($now->minute / 15) * 15);
        $windowEnd = $windowStart->copy()->addMinutes(15);

        $this->info("Processing weekly digests for {$currentDay} {$windowStart->format('H:i')} - {$windowEnd->format('H:i')}");

        // If specific user requested, just process that user
        if ($userId = $this->option('user')) {
            $user = User::find($userId);
            if (! $user) {
                $this->error("User {$userId} not found.");

                return Command::FAILURE;
            }

            return $this->processUser($user, 'weekly') ? Command::SUCCESS : Command::FAILURE;
        }

        // Find users whose weekly digest day and time match current window
        $users = User::whereNotNull('notification_preferences')
            ->whereNotNull('email')
            ->get()
            ->filter(function ($user) use ($currentDay, $windowStart, $windowEnd) {
                $prefs = $user->notification_preferences;
                $digest = $prefs['digest'] ?? [];

                // Check if digest is enabled and frequency includes weekly
                if (! ($digest['enabled'] ?? false)) {
                    return false;
                }

                $frequency = $digest['frequency'] ?? 'daily';
                if (! in_array($frequency, ['weekly', 'both'])) {
                    return false;
                }

                // Check if today is the user's chosen day
                $userDay = strtolower($digest['day'] ?? 'monday');
                if ($userDay !== $currentDay) {
                    return false;
                }

                // Check if user's preferred time falls in current window
                $digestTime = $digest['time'] ?? '07:00';
                $userTime = Carbon::createFromFormat('H:i', $digestTime);

                return $userTime->format('H:i') >= $windowStart->format('H:i')
                    && $userTime->format('H:i') < $windowEnd->format('H:i');
            });

        $this->info("Found {$users->count()} users for weekly digest.");

        $sent = 0;
        $skipped = 0;

        foreach ($users as $user) {
            if ($this->processUser($user, 'weekly')) {
                $sent++;
            } else {
                $skipped++;
            }
        }

        $this->info("Weekly digest complete. Sent: {$sent}, Skipped: {$skipped}");

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

        // Get the last weekly digest (look back 7 days by default)
        $lastDigest = NotificationDigest::getLastDigestForUser($user->id, $type);
        $since = $lastDigest?->sent_at ?? now()->subWeek();

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
