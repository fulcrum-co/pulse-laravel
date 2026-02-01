<?php

namespace App\Services;

use App\Jobs\SendNotificationEmail;
use App\Jobs\SendNotificationSms;
use App\Models\User;
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
     * @return array Summary of channels dispatched/skipped
     */
    public function deliver(UserNotification $notification): array
    {
        $user = $notification->user;
        $summary = [
            'email' => ['dispatched' => false, 'reason' => null],
            'sms' => ['dispatched' => false, 'reason' => null],
        ];

        if (! $user) {
            Log::warning('NotificationDeliveryService: User not found', [
                'notification_id' => $notification->id,
            ]);
            $summary['email']['reason'] = 'user_not_found';
            $summary['sms']['reason'] = 'user_not_found';

            return $summary;
        }

        // Check email preference
        $emailResult = $this->shouldSendEmail($notification);
        if ($emailResult['should_send']) {
            SendNotificationEmail::dispatch($notification)
                ->onQueue('notifications-email');

            $summary['email']['dispatched'] = true;

            Log::info('NotificationDeliveryService: Email job dispatched', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
            ]);
        } else {
            $summary['email']['reason'] = $emailResult['reason'];
        }

        // Check SMS preference
        $smsResult = $this->shouldSendSms($notification);
        if ($smsResult['should_send']) {
            SendNotificationSms::dispatch($notification)
                ->onQueue('notifications-sms');

            $summary['sms']['dispatched'] = true;

            Log::info('NotificationDeliveryService: SMS job dispatched', [
                'notification_id' => $notification->id,
                'user_id' => $user->id,
            ]);
        } else {
            $summary['sms']['reason'] = $smsResult['reason'];
        }

        return $summary;
    }

    /**
     * Deliver notifications to multiple users.
     * Use this when notifyMany() is called and you need multi-channel delivery.
     *
     * @param  Collection  $notifications  Collection of UserNotification models
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
     */
    public function deliverByIds(array $notificationIds): void
    {
        $notifications = UserNotification::whereIn('id', $notificationIds)
            ->with('user')
            ->get();

        $this->deliverMany($notifications);
    }

    /**
     * Check if email should be sent based on user preferences.
     *
     * Order of checks:
     * 1. User has email address
     * 2. Critical/urgent admin notifications bypass all preferences
     * 3. Type-level override (if explicitly disabled)
     * 4. Digest mode suppresses individual emails
     * 5. Priority-based channel config
     * 6. Category preference
     * 7. Quiet hours
     *
     * @return array ['should_send' => bool, 'reason' => string|null]
     */
    protected function shouldSendEmail(UserNotification $notification): array
    {
        $user = $notification->user;

        // 1. Check if user has email
        if (empty($user->email)) {
            return ['should_send' => false, 'reason' => 'no_email'];
        }

        // 2. Critical admin notifications bypass all preferences
        if ($this->isCriticalAdminNotification($notification)) {
            return ['should_send' => true, 'reason' => null];
        }

        // 3. Check if type is explicitly disabled
        if ($user->isTypeDisabled($notification->type)) {
            Log::debug('NotificationDeliveryService: Email suppressed - type disabled', [
                'user_id' => $user->id,
                'type' => $notification->type,
            ]);

            return ['should_send' => false, 'reason' => 'type_disabled'];
        }

        // 4. Check if digest mode suppresses individual emails
        if ($user->shouldSuppressIndividualEmails()) {
            Log::info('NotificationDeliveryService: Email suppressed - digest mode active', [
                'user_id' => $user->id,
                'notification_id' => $notification->id,
            ]);

            return ['should_send' => false, 'reason' => 'digest_suppress'];
        }

        // 5. Check priority-based channel config
        $priority = $notification->priority ?? 'normal';
        if (! $user->wantsChannelForPriority($priority, 'email')) {
            Log::debug('NotificationDeliveryService: Email suppressed - priority channel disabled', [
                'user_id' => $user->id,
                'priority' => $priority,
            ]);

            return ['should_send' => false, 'reason' => 'priority_channel_disabled'];
        }

        // 6. Check category preference
        if (! $user->wantsNotificationVia($notification->category, 'email')) {
            return ['should_send' => false, 'reason' => 'category_preference'];
        }

        // 7. Check quiet hours
        if ($user->isInQuietHours()) {
            Log::info('NotificationDeliveryService: Email suppressed due to quiet hours', [
                'user_id' => $user->id,
                'category' => $notification->category,
            ]);

            return ['should_send' => false, 'reason' => 'quiet_hours'];
        }

        return ['should_send' => true, 'reason' => null];
    }

    /**
     * Check if SMS should be sent based on user preferences.
     *
     * Order of checks:
     * 1. User has phone number
     * 2. Critical/urgent admin notifications bypass all preferences
     * 3. Type-level override (if explicitly disabled)
     * 4. Priority-based channel config
     * 5. Category preference
     * 6. Quiet hours
     *
     * @return array ['should_send' => bool, 'reason' => string|null]
     */
    protected function shouldSendSms(UserNotification $notification): array
    {
        $user = $notification->user;

        // 1. Check if user has phone
        if (empty($user->phone)) {
            return ['should_send' => false, 'reason' => 'no_phone'];
        }

        // 2. Critical admin notifications bypass all preferences
        if ($this->isCriticalAdminNotification($notification)) {
            return ['should_send' => true, 'reason' => null];
        }

        // 3. Check if type is explicitly disabled
        if ($user->isTypeDisabled($notification->type)) {
            Log::debug('NotificationDeliveryService: SMS suppressed - type disabled', [
                'user_id' => $user->id,
                'type' => $notification->type,
            ]);

            return ['should_send' => false, 'reason' => 'type_disabled'];
        }

        // 4. Check priority-based channel config
        $priority = $notification->priority ?? 'normal';
        if (! $user->wantsChannelForPriority($priority, 'sms')) {
            Log::debug('NotificationDeliveryService: SMS suppressed - priority channel disabled', [
                'user_id' => $user->id,
                'priority' => $priority,
            ]);

            return ['should_send' => false, 'reason' => 'priority_channel_disabled'];
        }

        // 5. Check category preference
        if (! $user->wantsNotificationVia($notification->category, 'sms')) {
            return ['should_send' => false, 'reason' => 'category_preference'];
        }

        // 6. Check quiet hours
        if ($user->isInQuietHours()) {
            Log::info('NotificationDeliveryService: SMS suppressed due to quiet hours', [
                'user_id' => $user->id,
                'category' => $notification->category,
            ]);

            return ['should_send' => false, 'reason' => 'quiet_hours'];
        }

        return ['should_send' => true, 'reason' => null];
    }

    /**
     * Check if this is a critical admin notification that bypasses all preferences.
     */
    protected function isCriticalAdminNotification(UserNotification $notification): bool
    {
        // Critical/urgent system notifications from admins cannot be suppressed
        return $notification->priority === UserNotification::PRIORITY_URGENT
            && $notification->category === UserNotification::CATEGORY_SYSTEM;
    }

    /**
     * Force deliver a notification via specific channels (bypasses preferences).
     * Use for urgent/critical notifications.
     *
     * @param  array  $channels  Array of channels: ['email', 'sms']
     */
    public function forceDeliver(UserNotification $notification, array $channels, bool $bypassQuietHours = false): void
    {
        $user = $notification->user;

        if (! $user) {
            return;
        }

        if (in_array('email', $channels) && ! empty($user->email)) {
            if ($bypassQuietHours || ! $user->isInQuietHours()) {
                SendNotificationEmail::dispatch($notification)
                    ->onQueue('notifications-email');
            }
        }

        if (in_array('sms', $channels) && ! empty($user->phone)) {
            if ($bypassQuietHours || ! $user->isInQuietHours()) {
                SendNotificationSms::dispatch($notification)
                    ->onQueue('notifications-sms');
            }
        }
    }

    /**
     * Check channel availability for a user.
     */
    public function getAvailableChannels(User $user): array
    {
        return [
            'in_app' => true, // Always available
            'email' => ! empty($user->email),
            'sms' => ! empty($user->phone),
        ];
    }

    /**
     * Get a preview of which channels would be used for a notification.
     * Useful for debugging or UI display.
     */
    public function previewDeliveryChannels(UserNotification $notification): array
    {
        return [
            'in_app' => true, // Always delivered
            'email' => $this->shouldSendEmail($notification),
            'sms' => $this->shouldSendSms($notification),
        ];
    }
}
