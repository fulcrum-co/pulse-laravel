<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\NotificationCreated;
use App\Models\UserNotification;
use App\Services\Domain\NotificationDomainService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function __construct(
        protected NotificationDomainService $domainService
    ) {}
    /**
     * Create a single notification for a user.
     *
     * @param  int  $userId  The user to notify
     * @param  string  $category  Category (survey, report, strategy, workflow_alert, course, system)
     * @param  string  $type  Specific notification type (survey_assigned, workflow_triggered, etc.)
     * @param  array  $data  Notification data:
     *                       - title (required): Notification title
     *                       - body (optional): Longer description
     *                       - icon (optional): Heroicon name
     *                       - priority (optional): low, normal, high, urgent (default: normal)
     *                       - action_url (optional): Deep link URL
     *                       - action_label (optional): CTA button text
     *                       - notifiable_type (optional): Source model class
     *                       - notifiable_id (optional): Source model ID
     *                       - metadata (optional): Additional JSON data
     *                       - expires_at (optional): Auto-expiration timestamp
     *                       - created_by (optional): User ID who created this
     *                       - org_id (optional): Override organization ID
     */
    public function notify(int $userId, string $category, string $type, array $data): ?UserNotification
    {
        // Get user to determine org_id
        $user = \App\Models\User::find($userId);
        if (! $user) {
            Log::warning('NotificationService: User not found', ['user_id' => $userId]);

            return null;
        }

        $orgId = $data['org_id'] ?? $user->org_id;
        if (! $orgId) {
            Log::warning('NotificationService: No org_id for user', ['user_id' => $userId]);

            return null;
        }

        // Check for duplicates using domain service
        if ($this->domainService->isDuplicate($userId, $type, $data['notifiable_type'] ?? null, $data['notifiable_id'] ?? null)) {
            Log::info('NotificationService: Duplicate notification skipped', [
                'user_id' => $userId,
                'type' => $type,
                'notifiable_type' => $data['notifiable_type'] ?? null,
                'notifiable_id' => $data['notifiable_id'] ?? null,
            ]);

            return null;
        }

        // Build payload using domain service
        $payload = $this->domainService->buildNotificationPayload($userId, $category, $type, $data, $orgId);

        $notification = UserNotification::create($payload);

        // Invalidate cache
        UserNotification::invalidateUnreadCountForUser($userId);

        // Broadcast for real-time delivery
        event(new NotificationCreated($notification));

        Log::info('NotificationService: Notification created', [
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
     * @param  array  $userIds  Array of user IDs to notify
     * @param  string  $category  Category
     * @param  string  $type  Notification type
     * @param  array  $data  Notification data (same as notify())
     * @return int Number of notifications created
     *
     * PERFORMANCE OPTIMIZATION:
     * - Uses select() to fetch only required columns
     * - Single batch query instead of N+1 pattern
     * - Efficient bulk insert with proper chunking
     * - Minimizes duplicate checks to only necessary lookups
     */
    public function notifyMany(array $userIds, string $category, string $type, array $data): int
    {
        if (empty($userIds)) {
            return 0;
        }

        // Filter and validate user IDs using domain service
        $userIds = $this->domainService->filterUserIds($userIds);
        if (empty($userIds)) {
            return 0;
        }

        // OPTIMIZATION: Select only required columns to reduce data transfer and memory
        $users = \App\Models\User::whereIn('id', $userIds)->select(['id', 'org_id'])->get();
        if ($users->isEmpty()) {
            return 0;
        }

        $now = now();
        $inserts = [];
        $affectedUserIds = [];

        foreach ($users as $user) {
            if (! $user->org_id) {
                continue;
            }

            // Check for duplicates using domain service
            if ($this->domainService->isDuplicate($user->id, $type, $data['notifiable_type'] ?? null, $data['notifiable_id'] ?? null)) {
                continue;
            }

            // Build payload using domain service
            $payload = $this->domainService->buildNotificationPayload($user->id, $category, $type, $data, $data['org_id'] ?? $user->org_id);
            $payload['status'] = UserNotification::STATUS_UNREAD;
            $payload['created_at'] = $now;
            $payload['updated_at'] = $now;

            // Handle JSON encoding for metadata if needed
            if (isset($payload['metadata']) && is_array($payload['metadata'])) {
                $payload['metadata'] = json_encode($payload['metadata']);
            }

            $inserts[] = $payload;
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

        Log::info('NotificationService: Bulk notifications created', [
            'count' => $count,
            'category' => $category,
            'type' => $type,
            'user_ids' => $affectedUserIds,
        ]);

        return $count;
    }


    /**
     * Get users by role for an organization.
     *
     * @param  int  $orgId  Organization ID
     * @param  array  $roles  Array of role names
     * @return array User IDs
     *
     * PERFORMANCE OPTIMIZATION:
     * - Uses select() before pluck() to specify MongoDB projection
     * - Efficient single query with proper filtering
     */
    public function getUserIdsByRoles(int $orgId, array $roles): array
    {
        return \App\Models\User::where('org_id', $orgId)
            ->whereIn('primary_role', $roles)
            ->select(['id']) // OPTIMIZATION: MongoDB projection for pluck()
            ->pluck('id')
            ->toArray();
    }

    /**
     * Create an admin announcement for the organization.
     *
     * @param  int  $orgId  Organization ID
     * @param  string  $title  Announcement title
     * @param  string|null  $body  Announcement body
     * @param  array  $targetUserIds  Specific user IDs (empty = all org users)
     * @param  array  $targetRoles  Specific roles (empty = all roles)
     * @param  int|null  $createdBy  Admin user who created this
     * @return int Number of notifications created
     *
     * PERFORMANCE OPTIMIZATION:
     * - Uses efficient column selection for all user queries
     * - Reuses getUserIdsByRoles for consistent optimization
     */
    public function createAnnouncement(
        int $orgId,
        string $title,
        ?string $body = null,
        array $targetUserIds = [],
        array $targetRoles = [],
        ?int $createdBy = null
    ): int {
        // Determine target users with optimized queries
        if (! empty($targetUserIds)) {
            $userIds = $targetUserIds;
        } elseif (! empty($targetRoles)) {
            $userIds = $this->getUserIdsByRoles($orgId, $targetRoles);
        } else {
            // All users in org - OPTIMIZATION: select only needed column
            $userIds = \App\Models\User::where('org_id', $orgId)
                ->select(['id'])
                ->pluck('id')
                ->toArray();
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

    /**
     * Create a notification and dispatch multi-channel delivery in one call.
     *
     * @param  int  $userId  The user to notify
     * @param  string  $category  Notification category
     * @param  string  $type  Notification type
     * @param  array  $data  Notification data (same as notify())
     */
    public function notifyAndDeliver(int $userId, string $category, string $type, array $data): ?UserNotification
    {
        $notification = $this->notify($userId, $category, $type, $data);

        if ($notification) {
            app(NotificationDeliveryService::class)->deliver($notification);
        }

        return $notification;
    }

    /**
     * Create notifications for multiple users and dispatch delivery for all.
     *
     * @param  array  $userIds  Array of user IDs
     * @param  string  $category  Notification category
     * @param  string  $type  Notification type
     * @param  array  $data  Notification data
     * @return Collection Created notifications
     *
     * PERFORMANCE OPTIMIZATION:
     * - Uses efficient column selection to reduce query payload
     * - Combines multiple filters into single query
     * - Uses index-friendly query patterns for date range
     */
    public function notifyManyAndDeliver(array $userIds, string $category, string $type, array $data): Collection
    {
        $count = $this->notifyMany($userIds, $category, $type, $data);

        if ($count === 0) {
            return collect();
        }

        // OPTIMIZATION: Select only required columns and use efficient filtering
        $notifications = UserNotification::select([
            'id', 'user_id', 'org_id', 'category', 'type', 'title', 'body', 'icon',
            'priority', 'status', 'action_url', 'action_label', 'created_at', 'updated_at'
        ])
            ->where('category', $category)
            ->where('type', $type)
            ->whereIn('user_id', $userIds)
            ->where('created_at', '>=', now()->subMinute())
            ->orderBy('created_at', 'desc')
            ->limit($count)
            ->get();

        // Dispatch delivery for all
        app(NotificationDeliveryService::class)->deliverMany($notifications);

        return $notifications;
    }
}
