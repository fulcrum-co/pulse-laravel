<?php

namespace App\Jobs;

use App\Mail\NotificationMail;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendNotificationEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [30, 60, 120];

    /**
     * Create a new job instance.
     */
    public function __construct(
        public UserNotification $notification
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->notification->user;

        if (! $user) {
            Log::warning('SendNotificationEmail: User not found', [
                'notification_id' => $this->notification->id,
            ]);

            return;
        }

        if (empty($user->email)) {
            Log::warning('SendNotificationEmail: User has no email', [
                'notification_id' => $this->notification->id,
                'user_id' => $user->id,
            ]);

            return;
        }

        // Skip if notification is no longer active (resolved/dismissed)
        if (! $this->notification->isActive()) {
            Log::info('SendNotificationEmail: Notification no longer active, skipping', [
                'notification_id' => $this->notification->id,
                'status' => $this->notification->status,
            ]);

            return;
        }

        try {
            Mail::to($user)->send(new NotificationMail($this->notification));

            $this->notification->update([
                'metadata' => array_merge(
                    $this->notification->metadata ?? [],
                    ['email_sent_at' => now()->toIso8601String()]
                ),
            ]);

            Log::info('SendNotificationEmail: Email sent successfully', [
                'notification_id' => $this->notification->id,
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (\Exception $e) {
            Log::error('SendNotificationEmail: Failed to send email', [
                'notification_id' => $this->notification->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendNotificationEmail: Job failed permanently', [
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
        ]);

        // Update metadata to record the failure
        $this->notification->update([
            'metadata' => array_merge(
                $this->notification->metadata ?? [],
                [
                    'email_failed_at' => now()->toIso8601String(),
                    'email_error' => $exception->getMessage(),
                ]
            ),
        ]);
    }
}
