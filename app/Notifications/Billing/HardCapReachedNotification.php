<?php

namespace App\Notifications\Billing;

use App\Models\CreditWallet;
use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class HardCapReachedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Organization $organization,
        public CreditWallet $wallet
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Auto Top-Up Monthly Limit Reached')
            ->greeting('Monthly Limit Reached')
            ->line("Auto top-up for **{$this->organization->org_name}** has been blocked.")
            ->line("**Reason:** You've reached your monthly auto top-up limit of {$this->wallet->auto_topup_monthly_limit} charges.")
            ->line("**Current Balance:** ".number_format($this->wallet->balance, 0).' credits')
            ->action('Purchase Manually', url('/settings/billing/purchase'))
            ->line('You can manually purchase credits or wait until next month when your limit resets.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'hard_cap_reached',
            'org_id' => $this->organization->id,
            'org_name' => $this->organization->org_name,
            'monthly_limit' => $this->wallet->auto_topup_monthly_limit,
            'count_this_month' => $this->wallet->auto_topup_count_this_month,
        ];
    }
}
