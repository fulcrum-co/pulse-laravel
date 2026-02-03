<?php

namespace App\Services\Moderation;

use App\Models\ContentModerationResult;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ModerationAssignmentService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Assign a moderation task to a user.
     */
    public function assign(
        ContentModerationResult $result,
        int $assigneeId,
        int $assignerId,
        array $options = []
    ): void {
        $previousAssignee = $result->assigned_to;

        $result->assignTo($assigneeId, $assignerId, $options);

        // Notify the new assignee
        $this->notifyAssignment($result, $assigneeId, $assignerId, $options);

        // If reassigned, notify previous assignee
        if ($previousAssignee && $previousAssignee !== $assigneeId) {
            $this->notifyReassignment($result, $previousAssignee);
        }

        Log::info('Moderation task assigned', [
            'result_id' => $result->id,
            'assigned_to' => $assigneeId,
            'assigned_by' => $assignerId,
        ]);
    }

    /**
     * Add a collaborator to a moderation task.
     */
    public function addCollaborator(
        ContentModerationResult $result,
        int $collaboratorId,
        int $addedBy
    ): void {
        $result->addCollaborator($collaboratorId);

        $this->notifyCollaboratorAdded($result, $collaboratorId, $addedBy);
    }

    /**
     * Bulk assign multiple moderation results.
     */
    public function bulkAssign(
        array $resultIds,
        int $assigneeId,
        int $assignerId,
        array $options = []
    ): int {
        $results = ContentModerationResult::whereIn('id', $resultIds)->get();

        foreach ($results as $result) {
            $this->assign($result, $assigneeId, $assignerId, $options);
        }

        return $results->count();
    }

    /**
     * Get eligible moderators for assignment dropdown.
     */
    public function getEligibleModerators(int $orgId): Collection
    {
        return User::where('org_id', $orgId)
            ->whereIn('primary_role', ['admin', 'consultant', 'superintendent', 'organization_admin'])
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'primary_role', 'email']);
    }

    /**
     * Get assignment stats for the queue.
     */
    public function getAssignmentStats(int $orgId, int $userId): array
    {
        return [
            'my_assignments' => ContentModerationResult::where('org_id', $orgId)
                ->assignedTo($userId)
                ->needsReview()
                ->count(),
            'collaborating' => ContentModerationResult::where('org_id', $orgId)
                ->whereJsonContains('collaborator_ids', $userId)
                ->needsReview()
                ->count(),
            'unassigned' => ContentModerationResult::where('org_id', $orgId)
                ->unassigned()
                ->needsReview()
                ->count(),
            'overdue' => ContentModerationResult::where('org_id', $orgId)
                ->assignedTo($userId)
                ->where('due_at', '<', now())
                ->where('human_reviewed', false)
                ->count(),
        ];
    }

    /**
     * Notify user of new assignment.
     */
    protected function notifyAssignment(
        ContentModerationResult $result,
        int $assigneeId,
        int $assignerId,
        array $options
    ): void {
        $contentTitle = $result->moderatable?->title ?? 'Content';
        $assigner = User::find($assignerId);

        $priorityMap = [
            ContentModerationResult::PRIORITY_LOW => UserNotification::PRIORITY_LOW,
            ContentModerationResult::PRIORITY_NORMAL => UserNotification::PRIORITY_NORMAL,
            ContentModerationResult::PRIORITY_HIGH => UserNotification::PRIORITY_HIGH,
            ContentModerationResult::PRIORITY_URGENT => UserNotification::PRIORITY_URGENT,
        ];

        $notificationPriority = $priorityMap[$options['priority'] ?? 'normal'] ?? UserNotification::PRIORITY_NORMAL;

        $this->notificationService->notify(
            $assigneeId,
            UserNotification::CATEGORY_WORKFLOW_ALERT,
            'moderation_assigned',
            [
                'title' => 'Content Moderation Assigned',
                'body' => ($assigner?->full_name ?? 'Someone')." assigned you to review \"{$contentTitle}\"",
                'icon' => 'shield-check',
                'priority' => $notificationPriority,
                'action_url' => route('admin.moderation').'?review='.$result->id,
                'action_label' => 'Review Content',
                'notifiable_type' => ContentModerationResult::class,
                'notifiable_id' => $result->id,
                'metadata' => [
                    'content_type' => class_basename($result->moderatable_type ?? 'Content'),
                    'content_title' => $contentTitle,
                    'assigned_by_name' => $assigner?->full_name,
                    'due_at' => $options['due_at']?->toIso8601String() ?? null,
                    'assignment_notes' => $options['notes'] ?? null,
                ],
                'created_by' => $assignerId,
            ]
        );
    }

    /**
     * Notify previous assignee of reassignment.
     */
    protected function notifyReassignment(
        ContentModerationResult $result,
        int $previousAssigneeId
    ): void {
        $contentTitle = $result->moderatable?->title ?? 'Content';

        $this->notificationService->notify(
            $previousAssigneeId,
            UserNotification::CATEGORY_WORKFLOW_ALERT,
            'moderation_reassigned',
            [
                'title' => 'Moderation Task Reassigned',
                'body' => "The review for \"{$contentTitle}\" has been reassigned to another moderator.",
                'icon' => 'arrow-path',
                'priority' => UserNotification::PRIORITY_LOW,
                'notifiable_type' => ContentModerationResult::class,
                'notifiable_id' => $result->id,
            ]
        );
    }

    /**
     * Notify collaborator they were added.
     */
    protected function notifyCollaboratorAdded(
        ContentModerationResult $result,
        int $collaboratorId,
        int $addedBy
    ): void {
        $contentTitle = $result->moderatable?->title ?? 'Content';
        $adder = User::find($addedBy);

        $this->notificationService->notify(
            $collaboratorId,
            UserNotification::CATEGORY_WORKFLOW_ALERT,
            'moderation_collaborator_added',
            [
                'title' => 'Added as Moderation Collaborator',
                'body' => ($adder?->full_name ?? 'Someone')." added you as a collaborator on \"{$contentTitle}\"",
                'icon' => 'users',
                'priority' => UserNotification::PRIORITY_NORMAL,
                'action_url' => route('admin.moderation').'?review='.$result->id,
                'action_label' => 'View Content',
                'notifiable_type' => ContentModerationResult::class,
                'notifiable_id' => $result->id,
                'created_by' => $addedBy,
            ]
        );
    }

    /**
     * Notify when moderation is complete (for content owner).
     */
    public function notifyModerationComplete(
        ContentModerationResult $result,
        string $action
    ): void {
        // Find the content owner
        $moderatable = $result->moderatable;
        if (! $moderatable || ! isset($moderatable->created_by)) {
            return;
        }

        $ownerId = $moderatable->created_by;
        $contentTitle = $moderatable->title ?? 'Content';

        $actionMessages = [
            'approved' => [
                'title' => 'Content Approved',
                'body' => "Your content \"{$contentTitle}\" has been approved.",
                'icon' => 'check-circle',
                'priority' => UserNotification::PRIORITY_NORMAL,
            ],
            'rejected' => [
                'title' => 'Content Rejected',
                'body' => "Your content \"{$contentTitle}\" was not approved.",
                'icon' => 'x-circle',
                'priority' => UserNotification::PRIORITY_NORMAL,
            ],
            'revision_requested' => [
                'title' => 'Revision Requested',
                'body' => "Revisions have been requested for \"{$contentTitle}\".",
                'icon' => 'pencil-square',
                'priority' => UserNotification::PRIORITY_HIGH,
            ],
        ];

        $messageConfig = $actionMessages[$action] ?? [
            'title' => 'Moderation Complete',
            'body' => "Moderation complete for \"{$contentTitle}\".",
            'icon' => 'shield-check',
            'priority' => UserNotification::PRIORITY_NORMAL,
        ];

        $this->notificationService->notify(
            $ownerId,
            UserNotification::CATEGORY_COURSE,
            'moderation_'.$action,
            [
                'title' => $messageConfig['title'],
                'body' => $messageConfig['body'],
                'icon' => $messageConfig['icon'],
                'priority' => $messageConfig['priority'],
                'action_url' => $this->getContentEditUrl($moderatable),
                'action_label' => $action === 'revision_requested' ? 'Edit Content' : 'View Content',
                'notifiable_type' => get_class($moderatable),
                'notifiable_id' => $moderatable->getKey(),
                'metadata' => [
                    'moderation_result_id' => $result->id,
                    'review_notes' => $result->review_notes,
                    'flags' => $result->flags,
                    'recommendations' => $result->recommendations,
                ],
            ]
        );
    }

    /**
     * Get the edit URL for different content types.
     */
    protected function getContentEditUrl($moderatable): string
    {
        $class = get_class($moderatable);

        return match ($class) {
            'App\\Models\\MiniCourse' => route('resources.courses.edit', $moderatable->id),
            default => '#',
        };
    }
}
