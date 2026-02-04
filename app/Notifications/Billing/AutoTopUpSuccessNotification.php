<?php

namespace App\Notifications\Billing;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AutoTopUpSuccessNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Organization $organization,
        public float $credits,
        public float $amount
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Auto Top-Up Successful - '.number_format($this->credits, 0).' Credits Added')
            ->greeting('Credits Added!')
            ->line("An automatic top-up was processed for **{$this->organization->org_name}**.")
            ->line("**Credits Added:** ".number_format($this->credits, 0))
            ->line("**Amount Charged:** $".number_format($this->amount, 2))
            ->action('View Balance', url('/settings/billing'))
            ->line('Your services will continue without interruption.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auto_topup_success',
            'org_id' => $this->organization->id,
            'org_name' => $this->organization->org_name,
            'credits' => $this->credits,
            'amount' => $this->amount,
        ];
    }
}
