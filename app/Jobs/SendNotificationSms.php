<?php

namespace App\Jobs;

use App\Models\UserNotification;
use App\Services\SinchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendNotificationSms implements ShouldQueue
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
    public function handle(SinchService $sinch): void
    {
        $user = $this->notification->user;

        if (!$user) {
            Log::warning('SendNotificationSms: User not found', [
                'notification_id' => $this->notification->id,
            ]);
            return;
        }

        if (empty($user->phone)) {
            Log::warning('SendNotificationSms: User has no phone', [
                'notification_id' => $this->notification->id,
                'user_id' => $user->id,
            ]);
            return;
        }

        // Skip if notification is no longer active (resolved/dismissed)
        if (!$this->notification->isActive()) {
            Log::info('SendNotificationSms: Notification no longer active, skipping', [
                'notification_id' => $this->notification->id,
                'status' => $this->notification->status,
            ]);
            return;
        }

        // Format SMS message (keep it concise for SMS)
        $message = $this->formatSmsMessage();

        try {
            $result = $sinch->sendSms($user->phone, $message);

            if ($result['success']) {
                $this->notification->update([
                    'metadata' => array_merge(
                        $this->notification->metadata ?? [],
                        ['sms_sent_at' => now()->toIso8601String()]
                    ),
                ]);

                Log::info('SendNotificationSms: SMS sent successfully', [
                    'notification_id' => $this->notification->id,
                    'user_id' => $user->id,
                    'phone' => $user->phone,
                ]);
            } else {
                Log::error('SendNotificationSms: Sinch API returned error', [
                    'notification_id' => $this->notification->id,
                    'user_id' => $user->id,
                    'error' => $result['error'] ?? 'Unknown error',
                ]);

                throw new \Exception($result['error'] ?? 'SMS send failed');
            }
        } catch (\Exception $e) {
            Log::error('SendNotificationSms: Failed to send SMS', [
                'notification_id' => $this->notification->id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // Re-throw to trigger retry
        }
    }

    /**
     * Format the notification as an SMS message.
     * Keep it under 160 characters when possible.
     */
    protected function formatSmsMessage(): string
    {
        $title = $this->notification->title;
        $body = $this->notification->body;
        $actionUrl = $this->notification->action_url;

        // Start with title
        $message = $title;

        // Add body if there's room (aim for ~140 chars to leave room for URL)
        if ($body && strlen($message) < 100) {
            $maxBodyLength = 100 - strlen($message);
            if (strlen($body) > $maxBodyLength) {
                $body = substr($body, 0, $maxBodyLength - 3) . '...';
            }
            $message .= ': ' . $body;
        }

        // Add shortened URL hint if available (user can find link in app)
        if ($actionUrl && strlen($message) < 140) {
            $message .= ' Open Pulse for details.';
        }

        return $message;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('SendNotificationSms: Job failed permanently', [
            'notification_id' => $this->notification->id,
            'error' => $exception->getMessage(),
        ]);

        // Update metadata to record the failure
        $this->notification->update([
            'metadata' => array_merge(
                $this->notification->metadata ?? [],
                [
                    'sms_failed_at' => now()->toIso8601String(),
                    'sms_error' => $exception->getMessage(),
                ]
            ),
        ]);
    }
}
