<?php

declare(strict_types=1);

namespace App\Services\Domain;

use Illuminate\Support\Str;

/**
 * NotificationFormatterService
 *
 * Encapsulates formatting logic for notifications across multiple channels:
 * SMS, email, and voice. Handles message truncation, phone number formatting,
 * and content optimization for each channel.
 */
class NotificationFormatterService
{
    /**
     * Max SMS length in characters.
     */
    private const SMS_MAX_LENGTH = 160;

    /**
     * Character buffer for truncation indicator.
     */
    private const TRUNCATION_BUFFER = 3; // for "..."

    /**
     * Format a message for SMS delivery.
     *
     * Truncates content to SMS limits and adds ellipsis if needed.
     *
     * @param  string  $senderName  Name of the message sender
     * @param  string  $messagePreview  Preview text of the message
     * @param  string  $replyLink  URL for replying to the message
     * @return string Formatted SMS message
     */
    public function formatSmsNotification(
        string $senderName,
        string $messagePreview,
        string $replyLink
    ): string {
        $message = "New message from {$senderName}: \"{$messagePreview}\"\n\nReply at: {$replyLink}";

        return $this->truncateMessage($message, self::SMS_MAX_LENGTH);
    }

    /**
     * Format a booking reminder for SMS delivery.
     *
     * @param  string  $learnerName  Name of the participant
     * @param  string  $scheduledTime  When the appointment is scheduled
     * @return string Formatted SMS reminder message
     */
    public function formatBookingReminderSms(string $learnerName, string $scheduledTime): string
    {
        return "Reminder: You have an appointment with {$learnerName} {$scheduledTime}. Log in to Pulse for details.";
    }

    /**
     * Truncate a message to a maximum length.
     *
     * @param  string  $message  The message to truncate
     * @param  int  $maxLength  Maximum character length
     * @return string Truncated message with ellipsis if needed
     */
    public function truncateMessage(string $message, int $maxLength): string
    {
        if (strlen($message) <= $maxLength) {
            return $message;
        }

        return Str::limit($message, $maxLength - self::TRUNCATION_BUFFER) . '...';
    }

    /**
     * Format phone number to E.164 standard.
     *
     * Removes non-numeric characters and adds country code if needed.
     *
     * @param  string  $number  Raw phone number
     * @return string E.164 formatted phone number
     */
    public function formatPhoneNumber(string $number): string
    {
        // Remove all non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $number);

        // Add country code if not present (assuming US)
        if (strlen($number) === 10) {
            $number = '1' . $number;
        }

        return '+' . $number;
    }

    /**
     * Limit message preview text.
     *
     * @param  string  $text  Original text
     * @param  int  $limit  Character limit (default 100)
     * @return string Limited preview text
     */
    public function limitMessagePreview(string $text, int $limit = 100): string
    {
        return Str::limit($text, $limit);
    }

    /**
     * Get sender name from initiator model or fallback.
     *
     * @param  object|null  $initiator  The initiator model
     * @return string Display name for the sender
     */
    public function getSenderName(?object $initiator): string
    {
        if (!$initiator) {
            return 'Someone';
        }

        return $initiator->full_name ?? $initiator->first_name ?? 'Someone';
    }
}
