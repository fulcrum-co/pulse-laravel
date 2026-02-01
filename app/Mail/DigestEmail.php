<?php

namespace App\Mail;

use App\Models\User;
use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\URL;

class DigestEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param  User  $user  The recipient user
     * @param  Collection  $groupedNotifications  Notifications grouped by category
     * @param  string  $digestType  'daily' or 'weekly'
     */
    public function __construct(
        public User $user,
        public Collection $groupedNotifications,
        public string $digestType = 'daily'
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $count = $this->groupedNotifications->flatten(1)->count();
        $typeLabel = ucfirst($this->digestType);

        return new Envelope(
            subject: "Your {$typeLabel} Digest: {$count} notification".($count !== 1 ? 's' : ''),
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Build summary stats
        $totalCount = 0;
        $highPriorityCount = 0;

        foreach ($this->groupedNotifications as $category => $notifications) {
            foreach ($notifications as $notification) {
                $totalCount++;
                if (in_array($notification->priority, [UserNotification::PRIORITY_HIGH, UserNotification::PRIORITY_URGENT])) {
                    $highPriorityCount++;
                }
            }
        }

        // Generate signed unsubscribe URL (valid for 30 days)
        $unsubscribeUrl = URL::signedRoute('notifications.unsubscribe', [
            'user' => $this->user->id,
        ], now()->addDays(30));

        return new Content(
            view: 'emails.digest',
            with: [
                'user' => $this->user,
                'digestType' => $this->digestType,
                'groupedNotifications' => $this->groupedNotifications,
                'totalCount' => $totalCount,
                'highPriorityCount' => $highPriorityCount,
                'categoryLabels' => $this->getCategoryLabels(),
                'notificationCenterUrl' => url('/alerts'),
                'unsubscribeUrl' => $unsubscribeUrl,
                'preferencesUrl' => url('/settings/notifications'),
            ],
        );
    }

    /**
     * Get category labels.
     */
    protected function getCategoryLabels(): array
    {
        return [
            UserNotification::CATEGORY_WORKFLOW_ALERT => 'Alerts',
            UserNotification::CATEGORY_SURVEY => 'Surveys',
            UserNotification::CATEGORY_REPORT => 'Reports',
            UserNotification::CATEGORY_STRATEGY => 'Plans',
            UserNotification::CATEGORY_COURSE => 'Courses',
            UserNotification::CATEGORY_COLLECTION => 'Data Collections',
            UserNotification::CATEGORY_SYSTEM => 'System',
        ];
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
