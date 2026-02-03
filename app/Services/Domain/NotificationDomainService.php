<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\UserNotification;

/**
 * NotificationDomainService
 *
 * Encapsulates notification business logic including duplicate detection,
 * notification data validation, and notification state management.
 * This domain service is responsible for core notification rules.
 */
class NotificationDomainService
{
    /**
     * Check if a duplicate active notification exists.
     *
     * Identifies duplicate notifications by user, type, and source reference.
     * Only checks for duplicates if we have a notifiable reference (type and ID).
     *
     * @param  int  $userId  The user to check for
     * @param  string  $type  Notification type
     * @param  string|null  $notifiableType  Source model class
     * @param  int|null  $notifiableId  Source model ID
     * @return bool True if a duplicate active notification exists
     */
    public function isDuplicate(int $userId, string $type, ?string $notifiableType, ?int $notifiableId): bool
    {
        // Only check duplicates if we have a notifiable reference
        if (! $notifiableType || ! $notifiableId) {
            return false;
        }

        return UserNotification::forUser($userId)
            ->where('type', $type)
            ->where('notifiable_type', $notifiableType)
            ->where('notifiable_id', $notifiableId)
            ->active()
            ->exists();
    }

    /**
     * Validate notification data array.
     *
     * Ensures required fields are present and have valid values.
     *
     * @param  array  $data  Notification data array
     * @return array{valid: bool, errors: array<string>}
     */
    public function validateNotificationData(array $data): array
    {
        $errors = [];

        // Required: title
        if (empty($data['title'])) {
            $errors[] = 'Notification title is required';
        }

        // Optional but if present, must be valid
        if (isset($data['priority'])) {
            $validPriorities = [
                UserNotification::PRIORITY_LOW,
                UserNotification::PRIORITY_NORMAL,
                UserNotification::PRIORITY_HIGH,
                UserNotification::PRIORITY_URGENT,
            ];

            if (! in_array($data['priority'], $validPriorities)) {
                $errors[] = 'Invalid notification priority';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Get safe notification data for database storage.
     *
     * Ensures all notification data is properly formatted and safe.
     * Returns data ready for model creation.
     *
     * @param  int  $userId  User ID for the notification
     * @param  string  $category  Notification category
     * @param  string  $type  Notification type
     * @param  array  $data  Raw notification data
     * @param  int|null  $orgId  Organization override
     * @return array Safe notification data ready for storage
     */
    public function buildNotificationPayload(
        int $userId,
        string $category,
        string $type,
        array $data,
        ?int $orgId = null
    ): array {
        return [
            'user_id' => $userId,
            'category' => $category,
            'type' => $type,
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'icon' => $data['icon'] ?? null,
            'priority' => $data['priority'] ?? UserNotification::PRIORITY_NORMAL,
            'status' => UserNotification::STATUS_UNREAD,
            'action_url' => $data['action_url'] ?? null,
            'action_label' => $data['action_label'] ?? null,
            'notifiable_type' => $data['notifiable_type'] ?? null,
            'notifiable_id' => $data['notifiable_id'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'created_by' => $data['created_by'] ?? null,
            'org_id' => $orgId,
        ];
    }

    /**
     * Filter user IDs for batch notification.
     *
     * Removes duplicates and invalid IDs.
     *
     * @param  array  $userIds  Raw user ID array
     * @return array<int> Filtered and unique user IDs
     */
    public function filterUserIds(array $userIds): array
    {
        return array_unique(array_filter($userIds, fn ($id) => is_int($id) || (is_numeric($id) && (int) $id > 0)));
    }
}
