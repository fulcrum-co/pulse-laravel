<?php

namespace App\Notifications\Billing;

use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UsageSpikeAlert extends Notification
{
    use Queueable;

    public function __construct(
        public Organization $organization,
        public float $currentBurn,
        public float $averageBurn
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $multiplier = round($this->currentBurn / $this->averageBurn, 1);

        return (new MailMessage)
            ->subject('Usage Spike Detected - '.$multiplier.'x Normal')
            ->greeting('Unusual Activity Detected')
            ->line("Your organization **{$this->organization->org_name}** is experiencing higher than normal credit usage.")
            ->line("**Today's Usage:** ".number_format($this->currentBurn, 0).' credits')
            ->line("**30-Day Average:** ".number_format($this->averageBurn, 0).' credits/day')
            ->line("**Current Rate:** {$multiplier}x normal")
            ->action('Review Usage', url('/settings/billing?tab=usage'))
            ->line('This may be normal if you have increased activity, but please verify this usage is expected.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'usage_spike',
            'org_id' => $this->organization->id,
            'org_name' => $this->organization->org_name,
            'current_burn' => $this->currentBurn,
            'average_burn' => $this->averageBurn,
            'multiplier' => $this->currentBurn / $this->averageBurn,
        ];
    }
}
