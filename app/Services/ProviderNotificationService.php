<?php

namespace App\Services;

use App\Models\Provider;
use App\Models\ProviderConversation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ProviderNotificationService
{
    protected SinchService $sinchService;

    public function __construct(SinchService $sinchService)
    {
        $this->sinchService = $sinchService;
    }

    /**
     * Notify provider of a new message.
     */
    public function notifyNewMessage(ProviderConversation $conversation, array $message): void
    {
        $provider = $conversation->provider;
        $account = $provider->account;

        // Check if we should send notification
        if (! $conversation->canSendNotification()) {
            Log::debug('ProviderNotificationService: Rate limited, skipping notification', [
                'conversation_id' => $conversation->id,
            ]);

            return;
        }

        // Check if provider has a full account and is online
        // (This would check Stream presence in a real implementation)
        $isOnline = false; // TODO: Check Stream presence

        if ($account && $account->isFullAccount() && $isOnline) {
            Log::debug('ProviderNotificationService: Provider online, skipping notification');

            return;
        }

        // Generate reply link
        $replyLink = $this->generateReplyLink($conversation);

        // Get sender name
        $initiator = $conversation->initiator;
        $senderName = $initiator ? ($initiator->full_name ?? $initiator->first_name ?? 'Someone') : 'Someone';

        // Get message preview
        $messagePreview = Str::limit($message['text'] ?? 'New message', 100);

        // Send via preferred channels
        $notificationPrefs = $account?->notification_preferences ?? ['email' => true, 'sms' => true];

        if ($notificationPrefs['email'] ?? true) {
            $this->sendEmailNotification($provider, $senderName, $messagePreview, $replyLink);
        }

        if ($notificationPrefs['sms'] ?? false) {
            $this->sendSmsNotification($provider, $senderName, $messagePreview, $replyLink);
        }

        // Mark notification as sent
        $conversation->markNotificationSent();
    }

    /**
     * Generate a secure reply link for the provider.
     */
    public function generateReplyLink(ProviderConversation $conversation): string
    {
        // Generate a signed URL that expires in 7 days
        return URL::temporarySignedRoute(
            'provider.reply',
            now()->addDays(7),
            [
                'conversation' => $conversation->uuid,
            ]
        );
    }

    /**
     * Validate a reply link token.
     */
    public function validateReplyLink(string $signature, ProviderConversation $conversation): bool
    {
        // The signed URL validation is handled by Laravel's ValidateSignature middleware
        return true;
    }

    /**
     * Send email notification to provider.
     */
    protected function sendEmailNotification(
        Provider $provider,
        string $senderName,
        string $messagePreview,
        string $replyLink
    ): void {
        $email = $provider->contact_email;

        if (! $email) {
            Log::warning('ProviderNotificationService: No email for provider', [
                'provider_id' => $provider->id,
            ]);

            return;
        }

        try {
            Mail::send('emails.provider-message-notification', [
                'providerName' => $provider->name,
                'senderName' => $senderName,
                'messagePreview' => $messagePreview,
                'replyLink' => $replyLink,
            ], function ($mail) use ($email, $provider, $senderName) {
                $mail->to($email, $provider->name)
                    ->subject("New message from {$senderName} - Pulse");
            });

            Log::info('ProviderNotificationService: Email sent', [
                'provider_id' => $provider->id,
                'email' => $email,
            ]);
        } catch (\Exception $e) {
            Log::error('ProviderNotificationService: Failed to send email', [
                'provider_id' => $provider->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send SMS notification to provider.
     */
    protected function sendSmsNotification(
        Provider $provider,
        string $senderName,
        string $messagePreview,
        string $replyLink
    ): void {
        $phone = $provider->contact_phone;

        if (! $phone) {
            Log::warning('ProviderNotificationService: No phone for provider', [
                'provider_id' => $provider->id,
            ]);

            return;
        }

        // Create short message for SMS
        $message = "New message from {$senderName}: \"{$messagePreview}\"\n\nReply at: {$replyLink}";

        // Truncate if too long
        if (strlen($message) > 160) {
            $message = Str::limit($message, 157).'...';
        }

        try {
            $this->sinchService->sendSms($phone, $message);

            Log::info('ProviderNotificationService: SMS sent', [
                'provider_id' => $provider->id,
                'phone' => substr($phone, -4), // Log only last 4 digits
            ]);
        } catch (\Exception $e) {
            Log::error('ProviderNotificationService: Failed to send SMS', [
                'provider_id' => $provider->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send booking confirmation notification.
     */
    public function notifyBookingConfirmation(
        Provider $provider,
        array $bookingDetails
    ): void {
        $email = $provider->contact_email;

        if (! $email) {
            return;
        }

        try {
            Mail::send('emails.provider-booking-confirmation', $bookingDetails, function ($mail) use ($email, $provider) {
                $mail->to($email, $provider->name)
                    ->subject('New Booking Confirmed - Pulse');
            });
        } catch (\Exception $e) {
            Log::error('ProviderNotificationService: Failed to send booking confirmation', [
                'provider_id' => $provider->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send booking cancellation notification.
     */
    public function notifyBookingCancellation(
        Provider $provider,
        array $bookingDetails
    ): void {
        $email = $provider->contact_email;

        if (! $email) {
            return;
        }

        try {
            Mail::send('emails.provider-booking-cancellation', $bookingDetails, function ($mail) use ($email, $provider) {
                $mail->to($email, $provider->name)
                    ->subject('Booking Cancelled - Pulse');
            });
        } catch (\Exception $e) {
            Log::error('ProviderNotificationService: Failed to send cancellation notice', [
                'provider_id' => $provider->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send booking reminder notification (24 hours before).
     */
    public function sendBookingReminder(
        Provider $provider,
        array $bookingDetails
    ): void {
        $notificationPrefs = $provider->account?->notification_preferences ?? ['email' => true, 'sms' => true];

        if ($notificationPrefs['email'] ?? true) {
            $this->sendBookingReminderEmail($provider, $bookingDetails);
        }

        if ($notificationPrefs['sms'] ?? false) {
            $this->sendBookingReminderSms($provider, $bookingDetails);
        }
    }

    /**
     * Send booking reminder via email.
     */
    protected function sendBookingReminderEmail(Provider $provider, array $bookingDetails): void
    {
        $email = $provider->contact_email;

        if (! $email) {
            return;
        }

        try {
            Mail::send('emails.provider-booking-reminder', $bookingDetails, function ($mail) use ($email, $provider) {
                $mail->to($email, $provider->name)
                    ->subject('Appointment Reminder - Tomorrow');
            });
        } catch (\Exception $e) {
            Log::error('ProviderNotificationService: Failed to send reminder email', [
                'provider_id' => $provider->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send booking reminder via SMS.
     */
    protected function sendBookingReminderSms(Provider $provider, array $bookingDetails): void
    {
        $phone = $provider->contact_phone;

        if (! $phone) {
            return;
        }

        $studentName = $bookingDetails['student_name'] ?? 'a student';
        $time = $bookingDetails['scheduled_time'] ?? 'tomorrow';

        $message = "Reminder: You have an appointment with {$studentName} {$time}. Log in to Pulse for details.";

        try {
            $this->sinchService->sendSms($phone, $message);
        } catch (\Exception $e) {
            Log::error('ProviderNotificationService: Failed to send reminder SMS', [
                'provider_id' => $provider->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
