<?php

namespace App\Observers;

use App\Models\Objective;
use App\Models\UserNotification;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class ObjectiveObserver
{
    public function __construct(
        protected NotificationService $notificationService,
        protected NotificationDeliveryService $deliveryService
    ) {}

    /**
     * Handle the Objective "updated" event.
     */
    public function updated(Objective $objective): void
    {
        // Check if status changed
        if (!$objective->isDirty('status')) {
            return;
        }

        $oldStatus = $objective->getOriginal('status');
        $newStatus = $objective->status;

        Log::info('ObjectiveObserver: Status changed', [
            'objective_id' => $objective->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        // Notify if objective is now at risk or off track
        if (in_array($newStatus, [Objective::STATUS_AT_RISK, Objective::STATUS_OFF_TRACK])) {
            $this->notifyStatusChange($objective, $oldStatus, $newStatus);
        }
    }

    /**
     * Notify plan owner about status change.
     */
    protected function notifyStatusChange(Objective $objective, string $oldStatus, string $newStatus): void
    {
        $focusArea = $objective->focusArea;
        $plan = $focusArea?->strategicPlan;

        if (!$plan || !$plan->created_by) {
            Log::info('ObjectiveObserver: No plan creator to notify', [
                'objective_id' => $objective->id,
            ]);
            return;
        }

        $isOffTrack = $newStatus === Objective::STATUS_OFF_TRACK;
        $statusLabel = $isOffTrack ? 'Off Track' : 'At Risk';

        $notification = $this->notificationService->notify(
            $plan->created_by,
            UserNotification::CATEGORY_STRATEGY,
            $isOffTrack ? 'objective_off_track' : 'objective_at_risk',
            [
                'title' => "Objective {$statusLabel}: {$objective->title}",
                'body' => $this->buildNotificationBody($objective, $focusArea, $plan),
                'action_url' => route('strategies.show', $plan->id) . '#objective-' . $objective->id,
                'action_label' => 'View Objective',
                'icon' => $isOffTrack ? 'exclamation-triangle' : 'exclamation-circle',
                'priority' => $isOffTrack
                    ? UserNotification::PRIORITY_HIGH
                    : UserNotification::PRIORITY_NORMAL,
                'notifiable_type' => Objective::class,
                'notifiable_id' => $objective->id,
                'metadata' => [
                    'plan_id' => $plan->id,
                    'plan_title' => $plan->title,
                    'focus_area_id' => $focusArea->id,
                    'focus_area_title' => $focusArea->title,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'activity_count' => $objective->activities()->count(),
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
    protected function buildNotificationBody(Objective $objective, $focusArea, $plan): string
    {
        $parts = [];

        // Count activities at risk
        $atRiskCount = $objective->activities()
            ->whereIn('status', [Objective::STATUS_AT_RISK, Objective::STATUS_OFF_TRACK])
            ->count();

        if ($atRiskCount > 0) {
            $parts[] = "{$atRiskCount} activities require attention.";
        }

        if ($objective->end_date) {
            $daysRemaining = now()->diffInDays($objective->end_date, false);
            if ($daysRemaining < 0) {
                $parts[] = 'This objective is ' . abs($daysRemaining) . ' days overdue.';
            } elseif ($daysRemaining <= 14) {
                $parts[] = 'Due in ' . $daysRemaining . ' days.';
            }
        }

        $parts[] = "Focus Area: {$focusArea->title}";

        return implode(' ', $parts);
    }
}
