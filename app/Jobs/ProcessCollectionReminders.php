<?php

namespace App\Jobs;

use App\Models\CollectionReminder;
use App\Models\UserNotification;
use App\Services\NotificationService;
use App\Services\SinchService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessCollectionReminders implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService, SinchService $sinchService): void
    {
        // Get all due reminders (pending and scheduled time has passed)
        $reminders = CollectionReminder::due()
            ->with(['user', 'collection', 'session'])
            ->limit(100)
            ->get();

        if ($reminders->isEmpty()) {
            return;
        }

        Log::info('ProcessCollectionReminders: Processing due reminders', [
            'count' => $reminders->count(),
        ]);

        foreach ($reminders as $reminder) {
            $this->processReminder($reminder, $notificationService, $sinchService);
        }
    }

    /**
     * Process a single reminder.
     */
    protected function processReminder(
        CollectionReminder $reminder,
        NotificationService $notificationService,
        SinchService $sinchService
    ): void {
        try {
            $user = $reminder->user;
            $collection = $reminder->collection;
            $session = $reminder->session;

            if (!$user || !$collection) {
                $reminder->markFailed('Missing user or collection');
                return;
            }

            $message = $reminder->getMessage();

            // Send via the configured channel
            $sendResult = match ($reminder->channel) {
                CollectionReminder::CHANNEL_SMS => $this->sendSms($reminder, $sinchService, $message),
                CollectionReminder::CHANNEL_EMAIL => $this->sendEmail($reminder, $message),
                CollectionReminder::CHANNEL_WHATSAPP => $this->sendWhatsApp($reminder, $sinchService, $message),
                default => ['success' => false, 'error' => 'Unknown channel'],
            };

            if ($sendResult['success']) {
                $reminder->markSent($sendResult['metadata'] ?? []);

                // Create in-app notification as well
                $this->createInAppNotification($reminder, $notificationService);

                Log::info('ProcessCollectionReminders: Reminder sent successfully', [
                    'reminder_id' => $reminder->id,
                    'channel' => $reminder->channel,
                    'user_id' => $user->id,
                ]);
            } else {
                $reminder->markFailed($sendResult['error'] ?? 'Send failed');

                Log::warning('ProcessCollectionReminders: Failed to send reminder', [
                    'reminder_id' => $reminder->id,
                    'channel' => $reminder->channel,
                    'error' => $sendResult['error'] ?? 'Unknown error',
                ]);
            }
        } catch (\Exception $e) {
            $reminder->markFailed($e->getMessage());

            Log::error('ProcessCollectionReminders: Exception processing reminder', [
                'reminder_id' => $reminder->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send SMS reminder via Sinch.
     */
    protected function sendSms(CollectionReminder $reminder, SinchService $sinchService, string $message): array
    {
        $user = $reminder->user;

        if (!$user->phone) {
            return ['success' => false, 'error' => 'User has no phone number'];
        }

        // Add link to the message if session exists
        if ($reminder->session_id) {
            $link = route('collections.session', $reminder->session_id);
            $message .= " {$link}";
        }

        $result = $sinchService->sendSms($user->phone, $message);

        return [
            'success' => $result['success'] ?? false,
            'error' => $result['error'] ?? null,
            'metadata' => [
                'channel' => 'sms',
                'phone' => $user->phone,
                'message_id' => $result['data']['id'] ?? null,
            ],
        ];
    }

    /**
     * Send email reminder.
     */
    protected function sendEmail(CollectionReminder $reminder, string $message): array
    {
        $user = $reminder->user;
        $collection = $reminder->collection;

        if (!$user->email) {
            return ['success' => false, 'error' => 'User has no email'];
        }

        try {
            Mail::send('emails.collection-reminder', [
                'user' => $user,
                'collection' => $collection,
                'session' => $reminder->session,
                'message' => $message,
                'actionUrl' => $reminder->session_id
                    ? route('collections.session', $reminder->session_id)
                    : route('collections.show', $collection->id),
            ], function ($mail) use ($user, $collection) {
                $mail->to($user->email)
                    ->subject("Reminder: {$collection->title}");
            });

            return [
                'success' => true,
                'metadata' => [
                    'channel' => 'email',
                    'email' => $user->email,
                ],
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Send WhatsApp reminder via Sinch.
     */
    protected function sendWhatsApp(CollectionReminder $reminder, SinchService $sinchService, string $message): array
    {
        $user = $reminder->user;

        if (!$user->phone) {
            return ['success' => false, 'error' => 'User has no phone number'];
        }

        // Add link to the message if session exists
        if ($reminder->session_id) {
            $link = route('collections.session', $reminder->session_id);
            $message .= " {$link}";
        }

        $result = $sinchService->sendWhatsApp($user->phone, $message);

        return [
            'success' => $result['success'] ?? false,
            'error' => $result['error'] ?? null,
            'metadata' => [
                'channel' => 'whatsapp',
                'phone' => $user->phone,
                'message_id' => $result['data']['id'] ?? null,
            ],
        ];
    }

    /**
     * Create an in-app notification for the reminder.
     */
    protected function createInAppNotification(CollectionReminder $reminder, NotificationService $notificationService): void
    {
        $collection = $reminder->collection;
        $session = $reminder->session;

        $actionUrl = $session
            ? route('collections.session', $session->id)
            : route('collections.show', $collection->id);

        $notificationService->notify(
            $reminder->user_id,
            UserNotification::CATEGORY_COLLECTION,
            'collection_reminder',
            [
                'title' => "Data Collection: {$collection->title}",
                'body' => $reminder->getMessage(),
                'action_url' => $actionUrl,
                'action_label' => 'Start Collection',
                'icon' => 'clipboard-document-check',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'notifiable_type' => CollectionReminder::class,
                'notifiable_id' => $reminder->id,
                'metadata' => [
                    'collection_id' => $collection->id,
                    'session_id' => $session?->id,
                    'session_date' => $session?->session_date?->toIso8601String(),
                ],
            ]
        );
    }
}
