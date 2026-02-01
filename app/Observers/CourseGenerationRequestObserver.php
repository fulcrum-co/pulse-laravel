<?php

namespace App\Observers;

use App\Models\CourseGenerationRequest;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class CourseGenerationRequestObserver
{
    public function __construct(
        protected NotificationService $notificationService,
        protected NotificationDeliveryService $deliveryService
    ) {}

    /**
     * Handle the CourseGenerationRequest "updated" event.
     */
    public function updated(CourseGenerationRequest $request): void
    {
        // Check if status changed
        if (!$request->isDirty('status')) {
            return;
        }

        $newStatus = $request->status;

        Log::info('CourseGenerationRequestObserver: Status changed', [
            'request_id' => $request->id,
            'new_status' => $newStatus,
        ]);

        match ($newStatus) {
            CourseGenerationRequest::STATUS_PENDING_APPROVAL => $this->notifyApprovers($request),
            CourseGenerationRequest::STATUS_APPROVED => $this->notifyRequester($request, true),
            CourseGenerationRequest::STATUS_REJECTED => $this->notifyRequester($request, false),
            CourseGenerationRequest::STATUS_FAILED => $this->notifyRequesterOfFailure($request),
            default => null,
        };
    }

    /**
     * Notify approvers that a course needs approval.
     */
    protected function notifyApprovers(CourseGenerationRequest $request): void
    {
        $approverIds = $this->getApproverUserIds($request->org_id);

        if (empty($approverIds)) {
            Log::warning('CourseGenerationRequestObserver: No approvers found', [
                'request_id' => $request->id,
                'org_id' => $request->org_id,
            ]);
            return;
        }

        $course = $request->generatedCourse;
        $courseTitle = $course?->title ?? 'Generated Course';

        $count = $this->notificationService->notifyMany(
            $approverIds,
            UserNotification::CATEGORY_COURSE,
            'course_approval_needed',
            [
                'title' => "Course Approval Needed: {$courseTitle}",
                'body' => $this->buildApprovalBody($request, $course),
                'action_url' => route('courses.review', $request->id),
                'action_label' => 'Review Course',
                'icon' => 'academic-cap',
                'priority' => UserNotification::PRIORITY_HIGH,
                'notifiable_type' => CourseGenerationRequest::class,
                'notifiable_id' => $request->id,
                'metadata' => [
                    'trigger_type' => $request->trigger_type,
                    'student_count' => $request->student_count,
                    'generation_strategy' => $request->generation_strategy,
                ],
            ]
        );

        Log::info('CourseGenerationRequestObserver: Notified approvers', [
            'request_id' => $request->id,
            'notifications_created' => $count,
        ]);

        // Dispatch multi-channel delivery
        if ($count > 0) {
            $notifications = UserNotification::where('type', 'course_approval_needed')
                ->where('notifiable_type', CourseGenerationRequest::class)
                ->where('notifiable_id', $request->id)
                ->where('created_at', '>=', now()->subMinute())
                ->get();

            $this->deliveryService->deliverMany($notifications);
        }
    }

    /**
     * Notify the requester of approval or rejection.
     */
    protected function notifyRequester(CourseGenerationRequest $request, bool $approved): void
    {
        if (!$request->triggered_by_user_id) {
            return;
        }

        $course = $request->generatedCourse;
        $courseTitle = $course?->title ?? 'Generated Course';

        $notification = $this->notificationService->notify(
            $request->triggered_by_user_id,
            UserNotification::CATEGORY_COURSE,
            $approved ? 'course_approved' : 'course_rejected',
            [
                'title' => $approved
                    ? "Course Approved: {$courseTitle}"
                    : "Course Rejected: {$courseTitle}",
                'body' => $approved
                    ? 'The course has been approved and is now available for assignment.'
                    : ($request->rejection_reason ?? 'The course was not approved. Please review and resubmit.'),
                'action_url' => $approved
                    ? route('courses.show', $course?->id ?? $request->id)
                    : route('courses.review', $request->id),
                'action_label' => $approved ? 'View Course' : 'View Details',
                'icon' => $approved ? 'check-circle' : 'x-circle',
                'priority' => $approved
                    ? UserNotification::PRIORITY_NORMAL
                    : UserNotification::PRIORITY_HIGH,
                'notifiable_type' => CourseGenerationRequest::class,
                'notifiable_id' => $request->id,
                'metadata' => [
                    'approved_by' => $request->approved_by,
                    'approved_at' => $request->approved_at?->toIso8601String(),
                    'rejection_reason' => $request->rejection_reason,
                ],
            ]
        );

        if ($notification) {
            $this->deliveryService->deliver($notification);
        }
    }

    /**
     * Notify the requester of generation failure.
     */
    protected function notifyRequesterOfFailure(CourseGenerationRequest $request): void
    {
        if (!$request->triggered_by_user_id) {
            return;
        }

        $notification = $this->notificationService->notify(
            $request->triggered_by_user_id,
            UserNotification::CATEGORY_COURSE,
            'course_generation_failed',
            [
                'title' => 'Course Generation Failed',
                'body' => $this->getFailureMessage($request),
                'action_url' => route('courses.requests'),
                'action_label' => 'View Request',
                'icon' => 'exclamation-triangle',
                'priority' => UserNotification::PRIORITY_HIGH,
                'notifiable_type' => CourseGenerationRequest::class,
                'notifiable_id' => $request->id,
            ]
        );

        if ($notification) {
            $this->deliveryService->deliver($notification);
        }
    }

    /**
     * Get user IDs who can approve courses.
     */
    protected function getApproverUserIds(int $orgId): array
    {
        // Get users with admin or consultant roles in this org
        return User::where('org_id', $orgId)
            ->whereIn('primary_role', ['admin', 'consultant', 'superintendent'])
            ->pluck('id')
            ->toArray();
    }

    /**
     * Build body for approval notification.
     */
    protected function buildApprovalBody(CourseGenerationRequest $request, $course): string
    {
        $parts = [];

        if ($course?->description) {
            $parts[] = \Illuminate\Support\Str::limit($course->description, 80);
        }

        $triggerLabel = match ($request->trigger_type) {
            CourseGenerationRequest::TRIGGER_RISK_THRESHOLD => 'Generated from risk threshold alert',
            CourseGenerationRequest::TRIGGER_WORKFLOW => 'Generated from alert workflow',
            CourseGenerationRequest::TRIGGER_MANUAL => 'Manually requested',
            default => 'Auto-generated',
        };
        $parts[] = $triggerLabel;

        if ($request->student_count > 0) {
            $parts[] = "Target: {$request->student_count} students";
        }

        return implode('. ', $parts) . '.';
    }

    /**
     * Get failure message from request log.
     */
    protected function getFailureMessage(CourseGenerationRequest $request): string
    {
        $log = $request->generation_log ?? [];
        $error = $log['error'] ?? null;

        if ($error) {
            return \Illuminate\Support\Str::limit($error, 150);
        }

        return 'The course generation process encountered an error. Please try again or contact support.';
    }
}
