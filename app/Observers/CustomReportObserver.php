<?php

namespace App\Observers;

use App\Models\CustomReport;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class CustomReportObserver
{
    public function __construct(
        protected NotificationService $notificationService,
        protected NotificationDeliveryService $deliveryService
    ) {}

    /**
     * Handle the CustomReport "updated" event.
     */
    public function updated(CustomReport $report): void
    {
        // Check if status changed
        if ($report->isDirty('status')) {
            $oldStatus = $report->getOriginal('status');
            $newStatus = $report->status;

            Log::info('CustomReportObserver: Status changed', [
                'report_id' => $report->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
            ]);

            // Handle different status transitions
            match ($newStatus) {
                CustomReport::STATUS_PUBLISHED => $this->handleReportPublished($report),
                CustomReport::STATUS_ARCHIVED => $this->handleReportArchived($report),
                default => null,
            };
        }

        // Check if assigned_to changed
        if ($report->isDirty('assigned_to')) {
            $this->handleAssignmentChanged($report);
        }
    }

    /**
     * Handle report published - notify assigned users.
     */
    protected function handleReportPublished(CustomReport $report): void
    {
        $assignedUserIds = $this->getAssignedUserIds($report);

        if (empty($assignedUserIds)) {
            Log::info('CustomReportObserver: No assigned users for report', [
                'report_id' => $report->id,
            ]);
            return;
        }

        // Create notifications for all assigned users
        $count = $this->notificationService->notifyMany(
            $assignedUserIds,
            UserNotification::CATEGORY_REPORT,
            'report_published',
            [
                'title' => "Report Published: {$report->report_name}",
                'body' => $report->report_description
                    ? \Illuminate\Support\Str::limit($report->report_description, 100)
                    : 'A new report has been published and is ready for review.',
                'action_url' => route('reports.show', $report->id),
                'action_label' => 'View Report',
                'icon' => 'document-chart-bar',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'notifiable_type' => CustomReport::class,
                'notifiable_id' => $report->id,
                'metadata' => [
                    'report_type' => $report->report_type,
                    'has_public_link' => !empty($report->public_token),
                ],
            ]
        );

        Log::info('CustomReportObserver: Notified assigned users of published report', [
            'report_id' => $report->id,
            'notifications_created' => $count,
        ]);

        // Dispatch multi-channel delivery
        if ($count > 0) {
            $notifications = UserNotification::where('type', 'report_published')
                ->where('notifiable_type', CustomReport::class)
                ->where('notifiable_id', $report->id)
                ->where('created_at', '>=', now()->subMinute())
                ->get();

            $this->deliveryService->deliverMany($notifications);
        }
    }

    /**
     * Handle report archived - notify creator.
     */
    protected function handleReportArchived(CustomReport $report): void
    {
        if (!$report->created_by) {
            return;
        }

        $notification = $this->notificationService->notify(
            $report->created_by,
            UserNotification::CATEGORY_REPORT,
            'report_archived',
            [
                'title' => "Report Archived: {$report->report_name}",
                'body' => 'This report has been archived and is no longer active.',
                'action_url' => route('reports.show', $report->id),
                'action_label' => 'View Report',
                'icon' => 'archive-box',
                'priority' => UserNotification::PRIORITY_LOW,
                'notifiable_type' => CustomReport::class,
                'notifiable_id' => $report->id,
            ]
        );

        if ($notification) {
            $this->deliveryService->deliver($notification);
        }
    }

    /**
     * Handle assignment changes - notify newly assigned users.
     */
    protected function handleAssignmentChanged(CustomReport $report): void
    {
        $oldAssigned = $report->getOriginal('assigned_to') ?? [];
        $newAssigned = $report->assigned_to ?? [];

        // Find newly assigned users
        $newlyAssignedIds = array_diff($newAssigned, $oldAssigned);

        if (empty($newlyAssignedIds)) {
            return;
        }

        // Only notify if report is published
        if ($report->status !== CustomReport::STATUS_PUBLISHED) {
            return;
        }

        $count = $this->notificationService->notifyMany(
            $newlyAssignedIds,
            UserNotification::CATEGORY_REPORT,
            'report_assigned',
            [
                'title' => "Report Assigned: {$report->report_name}",
                'body' => 'You have been assigned to view this report.',
                'action_url' => route('reports.show', $report->id),
                'action_label' => 'View Report',
                'icon' => 'document-chart-bar',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'notifiable_type' => CustomReport::class,
                'notifiable_id' => $report->id,
            ]
        );

        Log::info('CustomReportObserver: Notified newly assigned users', [
            'report_id' => $report->id,
            'newly_assigned' => $newlyAssignedIds,
            'notifications_created' => $count,
        ]);

        // Dispatch multi-channel delivery
        if ($count > 0) {
            $notifications = UserNotification::where('type', 'report_assigned')
                ->where('notifiable_type', CustomReport::class)
                ->where('notifiable_id', $report->id)
                ->where('created_at', '>=', now()->subMinute())
                ->get();

            $this->deliveryService->deliverMany($notifications);
        }
    }

    /**
     * Get user IDs from the assigned_to array.
     */
    protected function getAssignedUserIds(CustomReport $report): array
    {
        $assigned = $report->assigned_to ?? [];

        if (empty($assigned)) {
            return [];
        }

        // Validate that these are actual user IDs
        return User::whereIn('id', $assigned)
            ->where('org_id', $report->org_id)
            ->pluck('id')
            ->toArray();
    }
}
