<?php

namespace App\Notifications\Billing;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalBalanceAlert extends Notification
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
            ->subject('URGENT: Credit Balance Critical - '.number_format($this->percent, 0).'% Remaining')
            ->error()
            ->greeting('Urgent Attention Required!')
            ->line("Your organization **{$this->organization->org_name}** has a critically low credit balance.")
            ->line("**Current Balance:** ".number_format($this->balance, 0).' credits')
            ->line("**Remaining:** ".number_format($this->percent, 0).'% of typical capacity')
            ->line('Some features may be disabled soon if credits are not added.')
            ->action('Purchase Credits Now', url('/settings/billing/purchase'))
            ->line('This is an automated alert from Pulse.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'critical_balance',
            'org_id' => $this->organization->id,
            'org_name' => $this->organization->org_name,
            'balance' => $this->balance,
            'percent' => $this->percent,
            'urgent' => true,
        ];
    }
}
