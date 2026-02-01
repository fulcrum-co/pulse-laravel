<?php

namespace App\Services;

use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Create a single notification for a user.
     *
     * @param int $userId The user to notify
     * @param string $category Category (survey, report, strategy, workflow_alert, course, system)
     * @param string $type Specific notification type (survey_assigned, workflow_triggered, etc.)
     * @param array $data Notification data:
     *   - title (required): Notification title
     *   - body (optional): Longer description
     *   - icon (optional): Heroicon name
     *   - priority (optional): low, normal, high, urgent (default: normal)
     *   - action_url (optional): Deep link URL
     *   - action_label (optional): CTA button text
     *   - notifiable_type (optional): Source model class
     *   - notifiable_id (optional): Source model ID
     *   - metadata (optional): Additional JSON data
     *   - expires_at (optional): Auto-expiration timestamp
     *   - created_by (optional): User ID who created this
     *   - org_id (optional): Override organization ID
     */
    public function notify(int $userId, string $category, string $type, array $data): ?UserNotification
    {
        // Get user to determine org_id
        $user = \App\Models\User::find($userId);
        if (!$user) {
            Log::warning("NotificationService: User not found", ['user_id' => $userId]);
            return null;
        }

        $orgId = $data['org_id'] ?? $user->org_id;
        if (!$orgId) {
            Log::warning("NotificationService: No org_id for user", ['user_id' => $userId]);
            return null;
        }

        // Check for duplicates if notifiable is provided
        if ($this->isDuplicate($userId, $type, $data['notifiable_type'] ?? null, $data['notifiable_id'] ?? null)) {
            Log::info("NotificationService: Duplicate notification skipped", [
                'user_id' => $userId,
                'type' => $type,
                'notifiable_type' => $data['notifiable_type'] ?? null,
                'notifiable_id' => $data['notifiable_id'] ?? null,
            ]);
            return null;
        }

        $notification = UserNotification::create([
            'user_id' => $userId,
            'org_id' => $orgId,
            'category' => $category,
            'type' => $type,
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'icon' => $data['icon'] ?? null,
            'priority' => $data['priority'] ?? UserNotification::PRIORITY_NORMAL,
            'action_url' => $data['action_url'] ?? null,
            'action_label' => $data['action_label'] ?? null,
            'notifiable_type' => $data['notifiable_type'] ?? null,
            'notifiable_id' => $data['notifiable_id'] ?? null,
            'metadata' => $data['metadata'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'created_by' => $data['created_by'] ?? null,
        ]);

        // Invalidate cache
        UserNotification::invalidateUnreadCountForUser($userId);

        Log::info("NotificationService: Notification created", [
            'notification_id' => $notification->id,
            'user_id' => $userId,
            'category' => $category,
            'type' => $type,
        ]);

        return $notification;
    }

    /**
     * Create notifications for multiple users (same content).
     *
     * @param array $userIds Array of user IDs to notify
     * @param string $category Category
     * @param string $type Notification type
     * @param array $data Notification data (same as notify())
     * @return int Number of notifications created
     */
    public function notifyMany(array $userIds, string $category, string $type, array $data): int
    {
        if (empty($userIds)) {
            return 0;
        }

        $userIds = array_unique(array_filter($userIds));
        if (empty($userIds)) {
            return 0;
        }

        // Get users with their org_ids
        $users = \App\Models\User::whereIn('id', $userIds)->get(['id', 'org_id']);
        if ($users->isEmpty()) {
            return 0;
        }

        $now = now();
        $inserts = [];
        $affectedUserIds = [];

        foreach ($users as $user) {
            if (!$user->org_id) {
                continue;
            }

            // Check for duplicates
            if ($this->isDuplicate($user->id, $type, $data['notifiable_type'] ?? null, $data['notifiable_id'] ?? null)) {
                continue;
            }

            $inserts[] = [
                'user_id' => $user->id,
                'org_id' => $data['org_id'] ?? $user->org_id,
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
                'metadata' => isset($data['metadata']) ? json_encode($data['metadata']) : null,
                'expires_at' => $data['expires_at'] ?? null,
                'created_by' => $data['created_by'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $affectedUserIds[] = $user->id;
        }

        if (empty($inserts)) {
            return 0;
        }

        // Batch insert
        $count = 0;
        foreach (array_chunk($inserts, 100) as $chunk) {
            DB::table('user_notifications')->insert($chunk);
            $count += count($chunk);
        }

        // Invalidate cache for all affected users
        foreach ($affectedUserIds as $userId) {
            UserNotification::invalidateUnreadCountForUser($userId);
        }

        Log::info("NotificationService: Bulk notifications created", [
            'count' => $count,
            'category' => $category,
            'type' => $type,
            'user_ids' => $affectedUserIds,
        ]);

        return $count;
    }

    /**
     * Check if a duplicate active notification exists.
     */
    protected function isDuplicate(int $userId, string $type, ?string $notifiableType, ?int $notifiableId): bool
    {
        // Only check duplicates if we have a notifiable reference
        if (!$notifiableType || !$notifiableId) {
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
     * Get users by role for an organization.
     *
     * @param int $orgId Organization ID
     * @param array $roles Array of role names
     * @return array User IDs
     */
    public function getUserIdsByRoles(int $orgId, array $roles): array
    {
        return \App\Models\User::where('org_id', $orgId)
            ->whereIn('primary_role', $roles)
            ->pluck('id')
            ->toArray();
    }

    /**
     * Create an admin announcement for the organization.
     *
     * @param int $orgId Organization ID
     * @param string $title Announcement title
     * @param string|null $body Announcement body
     * @param array $targetUserIds Specific user IDs (empty = all org users)
     * @param array $targetRoles Specific roles (empty = all roles)
     * @param int|null $createdBy Admin user who created this
     * @return int Number of notifications created
     */
    public function createAnnouncement(
        int $orgId,
        string $title,
        ?string $body = null,
        array $targetUserIds = [],
        array $targetRoles = [],
        ?int $createdBy = null
    ): int {
        // Determine target users
        if (!empty($targetUserIds)) {
            $userIds = $targetUserIds;
        } elseif (!empty($targetRoles)) {
            $userIds = $this->getUserIdsByRoles($orgId, $targetRoles);
        } else {
            // All users in org
            $userIds = \App\Models\User::where('org_id', $orgId)->pluck('id')->toArray();
        }

        return $this->notifyMany($userIds, UserNotification::CATEGORY_SYSTEM, 'admin_announcement', [
            'title' => $title,
            'body' => $body,
            'icon' => 'megaphone',
            'priority' => UserNotification::PRIORITY_NORMAL,
            'org_id' => $orgId,
            'created_by' => $createdBy,
        ]);
    }
}
