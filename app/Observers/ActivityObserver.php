<?php

namespace App\Observers;

use App\Models\Activity;
use App\Models\UserNotification;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class ActivityObserver
{
    public function __construct(
        protected NotificationService $notificationService,
        protected NotificationDeliveryService $deliveryService
    ) {}

    /**
     * Handle the Activity "updated" event.
     */
    public function updated(Activity $activity): void
    {
        // Check if status changed
        if (! $activity->isDirty('status')) {
            return;
        }

        $oldStatus = $activity->getOriginal('status');
        $newStatus = $activity->status;

        Log::info('ActivityObserver: Status changed', [
            'activity_id' => $activity->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        // Notify if activity is now at risk or off track
        if (in_array($newStatus, [Activity::STATUS_AT_RISK, Activity::STATUS_OFF_TRACK])) {
            $this->notifyStatusChange($activity, $oldStatus, $newStatus);
        }
    }

    /**
     * Notify plan owner about status change.
     */
    protected function notifyStatusChange(Activity $activity, string $oldStatus, string $newStatus): void
    {
        $objective = $activity->objective;
        $focusArea = $objective?->focusArea;
        $plan = $focusArea?->strategicPlan;

        if (! $plan || ! $plan->created_by) {
            Log::info('ActivityObserver: No plan creator to notify', [
                'activity_id' => $activity->id,
            ]);

            return;
        }

        $isOffTrack = $newStatus === Activity::STATUS_OFF_TRACK;
        $statusLabel = $isOffTrack ? 'Off Track' : 'At Risk';

        $notification = $this->notificationService->notify(
            $plan->created_by,
            UserNotification::CATEGORY_STRATEGY,
            $isOffTrack ? 'activity_off_track' : 'activity_at_risk',
            [
                'title' => "Activity {$statusLabel}: {$activity->title}",
                'body' => $this->buildNotificationBody($activity, $plan),
                'action_url' => route('strategies.show', $plan->id).'#activity-'.$activity->id,
                'action_label' => 'View Activity',
                'icon' => $isOffTrack ? 'exclamation-triangle' : 'exclamation-circle',
                'priority' => $isOffTrack
                    ? UserNotification::PRIORITY_HIGH
                    : UserNotification::PRIORITY_NORMAL,
                'notifiable_type' => Activity::class,
                'notifiable_id' => $activity->id,
                'metadata' => [
                    'plan_id' => $plan->id,
                    'plan_title' => $plan->title,
                    'objective_id' => $objective->id,
                    'objective_title' => $objective->title,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ],
            ]
        );

        if ($notification) {
            $this->deliveryService->deliver($notification);
        }
    }

    /**
     * Build notification body with context.
     */
    protected function buildNotificationBody(Activity $activity, $plan): string
    {
        $parts = [];

        if ($activity->description) {
            $parts[] = \Illuminate\Support\Str::limit($activity->description, 80);
        }

        if ($activity->end_date) {
            $daysRemaining = now()->diffInDays($activity->end_date, false);
            if ($daysRemaining < 0) {
                $parts[] = 'This activity is '.abs($daysRemaining).' days overdue.';
            } elseif ($daysRemaining <= 7) {
                $parts[] = 'Due in '.$daysRemaining.' days.';
            }
        }

        $parts[] = "Plan: {$plan->title}";

        return implode(' ', $parts);
    }
}
