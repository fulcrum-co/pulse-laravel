<?php

namespace App\Notifications\Billing;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowBalanceAlert extends Notification
{
    use Queueable;

    public function __construct(
        public Organization $organization,
        public float $balance,
        public float $percent
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Low Credit Balance Alert - '.number_format($this->percent, 0).'% Remaining')
            ->greeting('Hello!')
            ->line("Your organization **{$this->organization->org_name}** has a low credit balance.")
            ->line("**Current Balance:** ".number_format($this->balance, 0).' credits')
            ->line("**Remaining:** ".number_format($this->percent, 0).'% of typical capacity')
            ->action('Purchase Credits', url('/settings/billing/purchase'))
            ->line('Consider enabling auto top-up to avoid service interruptions.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_balance',
            'org_id' => $this->organization->id,
            'org_name' => $this->organization->org_name,
            'balance' => $this->balance,
            'percent' => $this->percent,
        ];
    }
}
