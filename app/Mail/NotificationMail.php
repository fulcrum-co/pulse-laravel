<?php

namespace App\Mail;

use App\Models\UserNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public UserNotification $notification
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->notification->title;

        // Add priority indicator for high/urgent notifications
        if (in_array($this->notification->priority, [UserNotification::PRIORITY_HIGH, UserNotification::PRIORITY_URGENT])) {
            $subject = '[' . ucfirst($this->notification->priority) . '] ' . $subject;
        }

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'notification' => $this->notification,
                'user' => $this->notification->user,
                'title' => $this->notification->title,
                'body' => $this->notification->body,
                'actionUrl' => $this->notification->action_url,
                'actionLabel' => $this->notification->action_label ?? 'View Details',
                'priority' => $this->notification->priority,
                'category' => $this->notification->category,
                'categoryLabel' => $this->getCategoryLabel(),
            ],
        );
    }

    /**
     * Get a human-readable category label.
     */
    protected function getCategoryLabel(): string
    {
        return match ($this->notification->category) {
            UserNotification::CATEGORY_SURVEY => 'Survey',
            UserNotification::CATEGORY_REPORT => 'Report',
            UserNotification::CATEGORY_STRATEGY => 'Strategic Plan',
            UserNotification::CATEGORY_WORKFLOW_ALERT => 'Alert',
            UserNotification::CATEGORY_COURSE => 'Course',
            UserNotification::CATEGORY_SYSTEM => 'System',
            default => 'Notification',
        };
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
