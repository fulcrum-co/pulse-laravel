<?php

namespace App\Notifications\Billing;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentFailedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Organization $organization,
        public string $errorMessage
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Auto Top-Up Payment Failed')
            ->error()
            ->greeting('Payment Failed')
            ->line("An automatic top-up payment for **{$this->organization->org_name}** could not be processed.")
            ->line("**Reason:** {$this->errorMessage}")
            ->line('A 24-hour grace period has been activated to allow you to update your payment method.')
            ->action('Update Payment Method', url('/settings/billing'))
            ->line('Please update your payment method to avoid service interruptions.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_failed',
            'org_id' => $this->organization->id,
            'org_name' => $this->organization->org_name,
            'error_message' => $this->errorMessage,
        ];
    }
}
