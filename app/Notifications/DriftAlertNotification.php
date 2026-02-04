<?php

namespace App\Notifications;

use App\Models\ContactNote;
use App\Models\StrategyDriftScore;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DriftAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public ContactNote $note,
        public StrategyDriftScore $score
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $alignmentPercent = round($this->score->alignment_score * 100);
        $authorName = $this->note->author?->name ?? 'Unknown';
        $contactName = $this->note->contact?->name ?? 'Unknown';

        return (new MailMessage)
            ->subject('Strategy Drift Alert: Low Alignment Detected')
            ->greeting("Hello {$notifiable->name},")
            ->line("A field activity was recorded with low alignment to your strategic plans.")
            ->line("**Alignment Score:** {$alignmentPercent}%")
            ->line("**Recorded by:** {$authorName}")
            ->line("**Contact:** {$contactName}")
            ->line("**Preview:** ".substr($this->note->content, 0, 200).'...')
            ->action('View Watchdog Dashboard', route('strategy.watchdog'))
            ->line('This may indicate that field activities are drifting from strategic objectives.');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'strategy_drift',
            'title' => 'Strategy Drift Alert',
            'message' => sprintf(
                'Low alignment (%d%%) detected in a field activity',
                round($this->score->alignment_score * 100)
            ),
            'note_id' => $this->note->id,
            'score_id' => $this->score->id,
            'alignment_score' => $this->score->alignment_score,
            'alignment_level' => $this->score->alignment_level,
            'author_id' => $this->note->created_by,
            'action_url' => route('strategy.watchdog'),
        ];
    }
}
