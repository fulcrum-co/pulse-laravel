<?php

namespace App\Observers;

use App\Models\Survey;
use App\Models\UserNotification;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class SurveyObserver
{
    public function __construct(
        protected NotificationService $notificationService,
        protected NotificationDeliveryService $deliveryService
    ) {}

    /**
     * Handle the Survey "updated" event.
     */
    public function updated(Survey $survey): void
    {
        // Check if status changed
        if (!$survey->isDirty('status')) {
            return;
        }

        $oldStatus = $survey->getOriginal('status');
        $newStatus = $survey->status;

        Log::info('SurveyObserver: Status changed', [
            'survey_id' => $survey->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
        ]);

        // Handle different status transitions
        match ($newStatus) {
            Survey::STATUS_ACTIVE => $this->handleSurveyActivated($survey),
            Survey::STATUS_COMPLETED => $this->handleSurveyCompleted($survey),
            Survey::STATUS_PAUSED => $this->handleSurveyPaused($survey),
            default => null,
        };
    }

    /**
     * Handle survey activation - notify assigned users.
     */
    protected function handleSurveyActivated(Survey $survey): void
    {
        // Get all users who have been assigned this survey via deliveries
        $assignedUserIds = $survey->deliveries()
            ->whereNotNull('user_id')
            ->where('status', 'pending')
            ->pluck('user_id')
            ->unique()
            ->toArray();

        if (empty($assignedUserIds)) {
            Log::info('SurveyObserver: No pending deliveries for survey', [
                'survey_id' => $survey->id,
            ]);
            return;
        }

        // Create notifications for all assigned users
        $count = $this->notificationService->notifyMany(
            $assignedUserIds,
            UserNotification::CATEGORY_SURVEY,
            'survey_assigned',
            [
                'title' => "New Survey: {$survey->title}",
                'body' => $survey->description
                    ? \Illuminate\Support\Str::limit($survey->description, 100)
                    : "You've been assigned a new survey to complete.",
                'action_url' => route('surveys.take', $survey->id),
                'action_label' => 'Take Survey',
                'icon' => 'clipboard-document-list',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'notifiable_type' => Survey::class,
                'notifiable_id' => $survey->id,
                'expires_at' => $survey->end_date,
                'metadata' => [
                    'survey_type' => $survey->survey_type,
                    'estimated_duration' => $survey->estimated_duration_minutes,
                    'question_count' => $survey->question_count,
                ],
            ]
        );

        Log::info('SurveyObserver: Notified assigned users', [
            'survey_id' => $survey->id,
            'notifications_created' => $count,
        ]);

        // Dispatch multi-channel delivery
        if ($count > 0) {
            $notifications = UserNotification::where('type', 'survey_assigned')
                ->where('notifiable_type', Survey::class)
                ->where('notifiable_id', $survey->id)
                ->where('created_at', '>=', now()->subMinute())
                ->get();

            $this->deliveryService->deliverMany($notifications);
        }
    }

    /**
     * Handle survey completion - notify the creator.
     */
    protected function handleSurveyCompleted(Survey $survey): void
    {
        if (!$survey->created_by) {
            return;
        }

        $completedCount = $survey->completedAttempts()->count();
        $totalDeliveries = $survey->deliveries()->count();

        $notification = $this->notificationService->notify(
            $survey->created_by,
            UserNotification::CATEGORY_SURVEY,
            'survey_completed',
            [
                'title' => "Survey Completed: {$survey->title}",
                'body' => "{$completedCount} responses collected. View results to analyze the data.",
                'action_url' => route('surveys.results', $survey->id),
                'action_label' => 'View Results',
                'icon' => 'chart-bar',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'notifiable_type' => Survey::class,
                'notifiable_id' => $survey->id,
                'metadata' => [
                    'completed_count' => $completedCount,
                    'total_deliveries' => $totalDeliveries,
                    'response_rate' => $totalDeliveries > 0
                        ? round(($completedCount / $totalDeliveries) * 100, 1)
                        : 0,
                ],
            ]
        );

        if ($notification) {
            $this->deliveryService->deliver($notification);
        }
    }

    /**
     * Handle survey paused - notify the creator.
     */
    protected function handleSurveyPaused(Survey $survey): void
    {
        if (!$survey->created_by) {
            return;
        }

        $notification = $this->notificationService->notify(
            $survey->created_by,
            UserNotification::CATEGORY_SURVEY,
            'survey_paused',
            [
                'title' => "Survey Paused: {$survey->title}",
                'body' => 'The survey has been paused and is no longer accepting responses.',
                'action_url' => route('surveys.edit', $survey->id),
                'action_label' => 'Manage Survey',
                'icon' => 'pause-circle',
                'priority' => UserNotification::PRIORITY_LOW,
                'notifiable_type' => Survey::class,
                'notifiable_id' => $survey->id,
            ]
        );

        if ($notification) {
            $this->deliveryService->deliver($notification);
        }
    }
}
