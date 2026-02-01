<?php

namespace App\Services;

use App\Jobs\SendNotificationEmail;
use App\Jobs\SendNotificationSms;
use App\Models\UserNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NotificationDeliveryService
{
    /**
     * Deliver a notification via all user-enabled channels.
     * In-app is already created by NotificationService.
     * This method dispatches email/SMS jobs based on user preferences.
     *
     * @param UserNotification $notification
     * @return void
     */
    public function deliver(UserNotification $notification): void
    {
        $user = $notification->user;

        if (!$user) {
            Log::warning('NotificationDeliveryService: User not found', [
                'notification_id' => $notification->id,
            ]);
            return;
        }

        $category = $notification->category;

        // Check email preference
        if ($this->shouldSendEmail($user, $category)) {
            SendNotificationEmail::dispatch($notification)
                ->onQueue('notifications-email');

            Log::info('NotificationDeliveryService: Email job dispatched', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
            ]);
        }

        // Check SMS preference
        if ($this->shouldSendSms($user, $category)) {
            SendNotificationSms::dispatch($notification)
                ->onQueue('notifications-sms');

            Log::info('NotificationDeliveryService: SMS job dispatched', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
            ]);
        }
    }

    /**
     * Deliver notifications to multiple users.
     * Use this when notifyMany() is called and you need multi-channel delivery.
     *
     * @param Collection $notifications Collection of UserNotification models
     * @return void
     */
    public function deliverMany(Collection $notifications): void
    {
        foreach ($notifications as $notification) {
            $this->deliver($notification);
        }
    }

    /**
     * Deliver notifications by IDs.
     * Useful when you have IDs from a bulk insert.
     *
     * @param array $notificationIds
     * @return void
     */
    public function deliverByIds(array $notificationIds): void
    {
        $notifications = UserNotification::whereIn('id', $notificationIds)
            ->with('user')
            ->get();

        $this->deliverMany($notifications);
    }

    /**
     * Check if email should be sent based on user preferences and quiet hours.
     *
     * @param \App\Models\User $user
     * @param string $category
     * @return bool
     */
    protected function shouldSendEmail($user, string $category): bool
    {
        // Check if user has email
        if (empty($user->email)) {
            return false;
        }

        // Check preference
        if (!$user->wantsNotificationVia($category, 'email')) {
            return false;
        }

        // Check quiet hours
        if ($user->isInQuietHours()) {
            Log::info('NotificationDeliveryService: Email suppressed due to quiet hours', [
                'user_id' => $user->id,
                'category' => $category,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Check if SMS should be sent based on user preferences and quiet hours.
     *
     * @param \App\Models\User $user
     * @param string $category
     * @return bool
     */
    protected function shouldSendSms($user, string $category): bool
    {
        // Check if user has phone
        if (empty($user->phone)) {
            return false;
        }

        // Check preference
        if (!$user->wantsNotificationVia($category, 'sms')) {
            return false;
        }

        // Check quiet hours
        if ($user->isInQuietHours()) {
            Log::info('NotificationDeliveryService: SMS suppressed due to quiet hours', [
                'user_id' => $user->id,
                'category' => $category,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Force deliver a notification via specific channels (bypasses preferences).
     * Use for urgent/critical notifications.
     *
     * @param UserNotification $notification
     * @param array $channels Array of channels: ['email', 'sms']
     * @param bool $bypassQuietHours
     * @return void
     */
    public function forceDeliver(UserNotification $notification, array $channels, bool $bypassQuietHours = false): void
    {
        $user = $notification->user;

        if (!$user) {
            return;
        }

        if (in_array('email', $channels) && !empty($user->email)) {
            if ($bypassQuietHours || !$user->isInQuietHours()) {
                SendNotificationEmail::dispatch($notification)
                    ->onQueue('notifications-email');
            }
        }

        if (in_array('sms', $channels) && !empty($user->phone)) {
            if ($bypassQuietHours || !$user->isInQuietHours()) {
                SendNotificationSms::dispatch($notification)
                    ->onQueue('notifications-sms');
            }
        }
    }

    /**
     * Check channel availability for a user.
     *
     * @param \App\Models\User $user
     * @return array
     */
    public function getAvailableChannels($user): array
    {
        return [
            'in_app' => true, // Always available
            'email' => !empty($user->email),
            'sms' => !empty($user->phone),
        ];
    }
}
