<?php

namespace App\Services\Moderation;

use App\Models\ContentModerationResult;
use App\Models\ModerationDecision;
use App\Models\ModerationQueueItem;
use App\Models\ModerationSlaConfig;
use App\Models\ModerationTeamSetting;
use App\Models\ModerationWorkflow;
use App\Models\Organization;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\NotificationService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ModerationQueueService
{
    public function __construct(
        protected NotificationService $notificationService,
        protected ModerationAssignmentService $assignmentService
    ) {}

    /**
     * Enqueue a moderation result for review.
     */
    public function enqueueForModeration(
        ContentModerationResult $result,
        ?string $priority = null,
        ?ModerationWorkflow $workflow = null
    ): ModerationQueueItem {
        // Determine priority based on AI scores if not specified
        $priority = $priority ?? $this->determinePriority($result);

        // Calculate due date based on SLA
        $targetHours = ModerationSlaConfig::getTargetHours($result->org_id, $priority);
        $dueAt = now()->addHours($targetHours);

        $item = ModerationQueueItem::create([
            'org_id' => $result->org_id,
            'moderation_result_id' => $result->id,
            'workflow_id' => $workflow?->id,
            'status' => ModerationQueueItem::STATUS_PENDING,
            'priority' => $priority,
            'due_at' => $dueAt,
            'metadata' => [
                'content_type' => class_basename($result->moderatable_type),
                'ai_score' => $result->overall_score,
                'flags_count' => is_array($result->flags) ? count($result->flags) : 0,
            ],
        ]);

        Log::info('Content enqueued for moderation', [
            'queue_item_id' => $item->id,
            'moderation_result_id' => $result->id,
            'priority' => $priority,
            'due_at' => $dueAt->toIso8601String(),
        ]);

        return $item;
    }

    /**
     * Determine priority based on AI moderation scores.
     */
    protected function determinePriority(ContentModerationResult $result): string
    {
        // Urgent: Very low scores or safety flags
        if ($result->overall_score < 0.40 || $result->clinical_safety_score < 0.50) {
            return ModerationQueueItem::PRIORITY_URGENT;
        }

        // High: Low scores or multiple flags
        $flagCount = is_array($result->flags) ? count($result->flags) : 0;
        if ($result->overall_score < 0.60 || $flagCount >= 3) {
            return ModerationQueueItem::PRIORITY_HIGH;
        }

        // Normal: Moderate scores
        if ($result->overall_score < 0.80) {
            return ModerationQueueItem::PRIORITY_NORMAL;
        }

        // Low: High scores, just needs verification
        return ModerationQueueItem::PRIORITY_LOW;
    }

    /**
     * Get queue items for a specific user.
     */
    public function getQueueForUser(User $user): Collection
    {
        return ModerationQueueItem::with(['moderationResult.moderatable', 'assignee'])
            ->forOrganization($user->org_id)
            ->assignedTo($user->id)
            ->active()
            ->byPriority()
            ->orderBy('due_at')
            ->get();
    }

    /**
     * Get unassigned items in the queue.
     */
    public function getUnassignedItems(int $orgId): Collection
    {
        return ModerationQueueItem::with(['moderationResult.moderatable'])
            ->forOrganization($orgId)
            ->unassigned()
            ->pending()
            ->byPriority()
            ->orderBy('created_at')
            ->get();
    }

    /**
     * Get the next item for a user to review.
     */
    public function getNextItemForUser(User $user): ?ModerationQueueItem
    {
        // First check for assigned items
        $assignedItem = ModerationQueueItem::with(['moderationResult.moderatable'])
            ->forOrganization($user->org_id)
            ->assignedTo($user->id)
            ->pending()
            ->byPriority()
            ->orderBy('due_at')
            ->first();

        if ($assignedItem) {
            return $assignedItem;
        }

        // If no assigned items, check if user has capacity for auto-assignment
        $teamSetting = ModerationTeamSetting::getOrCreateForUser($user->org_id, $user->id);

        if (! $teamSetting->is_available) {
            return null;
        }

        // Get an unassigned item
        $unassignedItem = ModerationQueueItem::with(['moderationResult.moderatable'])
            ->forOrganization($user->org_id)
            ->unassigned()
            ->pending()
            ->byPriority()
            ->orderBy('created_at')
            ->first();

        if ($unassignedItem) {
            // Auto-assign to user
            $this->assignToUser($unassignedItem, $user);
        }

        return $unassignedItem;
    }

    /**
     * Assign a queue item to a user.
     */
    public function assignToUser(
        ModerationQueueItem $item,
        User $user,
        ?User $assigner = null
    ): void {
        $previousAssignee = $item->assigned_to;

        $item->assign($user, $assigner);

        // Update team settings load
        $teamSetting = ModerationTeamSetting::getOrCreateForUser($item->org_id, $user->id);
        $teamSetting->incrementLoad();

        // If reassigned, decrement previous assignee's load
        if ($previousAssignee && $previousAssignee !== $user->id) {
            $prevTeamSetting = ModerationTeamSetting::where('user_id', $previousAssignee)->first();
            $prevTeamSetting?->decrementLoad();
        }

        Log::info('Queue item assigned', [
            'queue_item_id' => $item->id,
            'assigned_to' => $user->id,
            'assigned_by' => $assigner?->id,
        ]);
    }

    /**
     * Assign using round-robin strategy.
     */
    public function assignRoundRobin(ModerationQueueItem $item): ?User
    {
        $eligibleModerators = ModerationTeamSetting::forOrganization($item->org_id)
            ->available()
            ->with('user')
            ->get()
            ->pluck('user');

        if ($eligibleModerators->isEmpty()) {
            return null;
        }

        // Get the moderator who was last assigned (rotate through)
        $lastAssignment = ModerationQueueItem::forOrganization($item->org_id)
            ->whereNotNull('assigned_to')
            ->latest('assigned_at')
            ->first();

        $lastAssignedIndex = $lastAssignment
            ? $eligibleModerators->search(fn ($u) => $u->id === $lastAssignment->assigned_to)
            : -1;

        $nextIndex = ($lastAssignedIndex + 1) % $eligibleModerators->count();
        $nextModerator = $eligibleModerators->get($nextIndex);

        if ($nextModerator) {
            $this->assignToUser($item, $nextModerator);
        }

        return $nextModerator;
    }

    /**
     * Assign to moderator with least current load.
     */
    public function assignLeastLoaded(ModerationQueueItem $item): ?User
    {
        $teamSetting = ModerationTeamSetting::forOrganization($item->org_id)
            ->available()
            ->orderByLoad('asc')
            ->with('user')
            ->first();

        if ($teamSetting?->user) {
            $this->assignToUser($item, $teamSetting->user);

            return $teamSetting->user;
        }

        return null;
    }

    /**
     * Assign based on skill match.
     */
    public function assignBySkill(ModerationQueueItem $item, array $requiredSkills): ?User
    {
        foreach ($requiredSkills as $skill) {
            $teamSetting = ModerationTeamSetting::forOrganization($item->org_id)
                ->available()
                ->withSpecialization($skill)
                ->orderByLoad('asc')
                ->with('user')
                ->first();

            if ($teamSetting?->user) {
                $this->assignToUser($item, $teamSetting->user);

                return $teamSetting->user;
            }
        }

        // Fallback to least loaded if no skill match
        return $this->assignLeastLoaded($item);
    }

    /**
     * Reassign an item to a different user.
     */
    public function reassign(
        ModerationQueueItem $item,
        User $newAssignee,
        User $reassigner
    ): void {
        $previousAssigneeId = $item->assigned_to;

        $this->assignToUser($item, $newAssignee, $reassigner);

        // Notify previous assignee
        if ($previousAssigneeId) {
            $this->notifyReassignment($item, $previousAssigneeId);
        }

        // Notify new assignee
        $this->notifyNewAssignment($item, $newAssignee, $reassigner);
    }

    /**
     * Process a decision on a queue item.
     */
    public function processDecision(
        ModerationQueueItem $item,
        User $user,
        string $decision,
        ?string $notes = null,
        ?array $fieldChanges = null
    ): ModerationDecision {
        // Record the decision
        $decisionRecord = $item->recordDecision($user, $decision, $notes, $fieldChanges);

        // Update item status
        match ($decision) {
            ModerationDecision::DECISION_APPROVE,
            ModerationDecision::DECISION_REJECT => $this->completeItem($item),
            ModerationDecision::DECISION_REQUEST_CHANGES => $this->requestChanges($item),
            ModerationDecision::DECISION_ESCALATE => $this->escalateItem($item),
            ModerationDecision::DECISION_SKIP => $this->skipItem($item),
            default => null,
        };

        // Update moderation result based on decision
        $this->updateModerationResult($item, $decision, $notes);

        // Notify content owner
        $this->notifyContentOwner($item, $decision);

        Log::info('Moderation decision processed', [
            'queue_item_id' => $item->id,
            'decision' => $decision,
            'user_id' => $user->id,
        ]);

        return $decisionRecord;
    }

    /**
     * Complete a queue item.
     */
    protected function completeItem(ModerationQueueItem $item): void
    {
        $item->complete();

        // Decrement team load
        if ($item->assigned_to) {
            $teamSetting = ModerationTeamSetting::where('user_id', $item->assigned_to)->first();
            $teamSetting?->decrementLoad();
        }
    }

    /**
     * Handle request changes decision.
     */
    protected function requestChanges(ModerationQueueItem $item): void
    {
        $item->update([
            'status' => ModerationQueueItem::STATUS_PENDING,
            'metadata' => array_merge($item->metadata ?? [], [
                'changes_requested_at' => now()->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Escalate a queue item.
     */
    protected function escalateItem(ModerationQueueItem $item): void
    {
        $item->escalate();

        // Find supervisor to escalate to
        $supervisor = $this->findSupervisor($item->org_id);

        if ($supervisor) {
            $this->assignToUser($item, $supervisor);
            $this->notifyEscalation($item, $supervisor);
        }
    }

    /**
     * Skip an item (return to queue).
     */
    protected function skipItem(ModerationQueueItem $item): void
    {
        $item->unassign();

        // Decrement team load
        if ($item->assigned_to) {
            $teamSetting = ModerationTeamSetting::where('user_id', $item->assigned_to)->first();
            $teamSetting?->decrementLoad();
        }
    }

    /**
     * Update the moderation result based on decision.
     */
    protected function updateModerationResult(ModerationQueueItem $item, string $decision, ?string $notes): void
    {
        $result = $item->moderationResult;

        $statusMap = [
            ModerationDecision::DECISION_APPROVE => ContentModerationResult::STATUS_APPROVED_OVERRIDE,
            ModerationDecision::DECISION_REJECT => ContentModerationResult::STATUS_REJECTED,
            ModerationDecision::DECISION_REQUEST_CHANGES => ContentModerationResult::STATUS_FLAGGED,
        ];

        if (isset($statusMap[$decision])) {
            $result->update([
                'status' => $statusMap[$decision],
                'human_reviewed' => true,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'review_notes' => $notes,
            ]);
        }
    }

    /**
     * Find a supervisor for escalation.
     */
    protected function findSupervisor(int $orgId): ?User
    {
        return User::where('org_id', $orgId)
            ->whereIn('primary_role', ['admin', 'administrative_role'])
            ->first();
    }

    /**
     * Check SLA status of an item.
     */
    public function checkSlaStatus(ModerationQueueItem $item): string
    {
        return $item->sla_status;
    }

    /**
     * Get items due soon.
     */
    public function getItemsDueSoon(int $orgId, int $hoursAhead = 24): Collection
    {
        return ModerationQueueItem::with(['moderationResult.moderatable', 'assignee'])
            ->forOrganization($orgId)
            ->dueSoon($hoursAhead)
            ->byPriority()
            ->get();
    }

    /**
     * Escalate overdue items.
     */
    public function escalateOverdueItems(): int
    {
        $overdueItems = ModerationQueueItem::overdue()
            ->where('status', '!=', ModerationQueueItem::STATUS_ESCALATED)
            ->get();

        $count = 0;

        foreach ($overdueItems as $item) {
            $item->escalate();

            $supervisor = $this->findSupervisor($item->org_id);

            if ($supervisor) {
                $this->assignToUser($item, $supervisor);
                $this->notifySlaBreach($item, $supervisor);
            }

            $count++;
        }

        return $count;
    }

    /**
     * Get queue statistics for an organization.
     */
    public function getQueueStats(int $orgId): array
    {
        $baseQuery = ModerationQueueItem::forOrganization($orgId);

        return [
            'total' => (clone $baseQuery)->active()->count(),
            'pending' => (clone $baseQuery)->pending()->count(),
            'in_progress' => (clone $baseQuery)->inProgress()->count(),
            'completed_today' => (clone $baseQuery)->completed()
                ->whereDate('completed_at', today())
                ->count(),
            'overdue' => (clone $baseQuery)->overdue()->count(),
            'by_priority' => [
                'urgent' => (clone $baseQuery)->active()->where('priority', 'urgent')->count(),
                'high' => (clone $baseQuery)->active()->where('priority', 'high')->count(),
                'normal' => (clone $baseQuery)->active()->where('priority', 'normal')->count(),
                'low' => (clone $baseQuery)->active()->where('priority', 'low')->count(),
            ],
            'sla_compliance' => $this->calculateSlaCompliance($orgId),
            'avg_review_time' => $this->calculateAverageReviewTime($orgId),
        ];
    }

    /**
     * Get statistics for a specific user.
     */
    public function getUserStats(User $user): array
    {
        $decisions = ModerationDecision::byUser($user->id);

        return [
            'assigned' => ModerationQueueItem::assignedTo($user->id)->active()->count(),
            'completed_today' => (clone $decisions)->today()->count(),
            'completed_week' => (clone $decisions)->thisWeek()->count(),
            'completed_month' => (clone $decisions)->thisMonth()->count(),
            'avg_time_seconds' => (clone $decisions)->avg('time_spent_seconds') ?? 0,
            'decisions_breakdown' => [
                'approved' => (clone $decisions)->byDecision('approve')->count(),
                'rejected' => (clone $decisions)->byDecision('reject')->count(),
                'changes_requested' => (clone $decisions)->byDecision('request_changes')->count(),
                'escalated' => (clone $decisions)->byDecision('escalate')->count(),
            ],
        ];
    }

    /**
     * Calculate SLA compliance rate.
     */
    protected function calculateSlaCompliance(int $orgId): float
    {
        $completed = ModerationQueueItem::forOrganization($orgId)
            ->completed()
            ->where('created_at', '>=', now()->subDays(30))
            ->get();

        if ($completed->isEmpty()) {
            return 100.0;
        }

        $onTime = $completed->filter(function ($item) {
            return ! $item->completed_at || ! $item->due_at ||
                   $item->completed_at->lte($item->due_at);
        })->count();

        return round(($onTime / $completed->count()) * 100, 1);
    }

    /**
     * Calculate average review time in seconds.
     */
    protected function calculateAverageReviewTime(int $orgId): int
    {
        return (int) ModerationDecision::whereHas('queueItem', function ($q) use ($orgId) {
            $q->forOrganization($orgId);
        })
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('time_spent_seconds')
            ->avg('time_spent_seconds') ?? 0;
    }

    // Notification methods

    protected function notifyNewAssignment(ModerationQueueItem $item, User $assignee, User $assigner): void
    {
        $contentTitle = $item->moderationResult?->moderatable?->title ?? 'Content';

        $this->notificationService->notify(
            $assignee->id,
            UserNotification::CATEGORY_WORKFLOW_ALERT,
            'moderation_assigned',
            [
                'title' => 'Moderation Task Assigned',
                'body' => "{$assigner->full_name} assigned you to review \"{$contentTitle}\"",
                'icon' => 'shield-check',
                'priority' => $this->mapPriorityToNotification($item->priority),
                'action_url' => route('admin.moderation.task-flow').'?item='.$item->id,
                'action_label' => 'Start Review',
                'metadata' => [
                    'queue_item_id' => $item->id,
                    'priority' => $item->priority,
                    'due_at' => $item->due_at?->toIso8601String(),
                ],
            ]
        );
    }

    protected function notifyReassignment(ModerationQueueItem $item, int $previousAssigneeId): void
    {
        $contentTitle = $item->moderationResult?->moderatable?->title ?? 'Content';

        $this->notificationService->notify(
            $previousAssigneeId,
            UserNotification::CATEGORY_WORKFLOW_ALERT,
            'moderation_reassigned',
            [
                'title' => 'Task Reassigned',
                'body' => "The review for \"{$contentTitle}\" has been reassigned.",
                'icon' => 'arrow-path',
                'priority' => UserNotification::PRIORITY_LOW,
            ]
        );
    }

    protected function notifyEscalation(ModerationQueueItem $item, User $escalateTo): void
    {
        $contentTitle = $item->moderationResult?->moderatable?->title ?? 'Content';

        $this->notificationService->notify(
            $escalateTo->id,
            UserNotification::CATEGORY_WORKFLOW_ALERT,
            'moderation_escalated',
            [
                'title' => 'Escalated Moderation Task',
                'body' => "A moderation task for \"{$contentTitle}\" has been escalated to you.",
                'icon' => 'exclamation-triangle',
                'priority' => UserNotification::PRIORITY_HIGH,
                'action_url' => route('admin.moderation.task-flow').'?item='.$item->id,
                'action_label' => 'Review Now',
            ]
        );
    }

    protected function notifySlaBreach(ModerationQueueItem $item, User $supervisor): void
    {
        $contentTitle = $item->moderationResult?->moderatable?->title ?? 'Content';

        $this->notificationService->notify(
            $supervisor->id,
            UserNotification::CATEGORY_WORKFLOW_ALERT,
            'moderation_sla_breach',
            [
                'title' => 'SLA Breach - Urgent Review',
                'body' => "Moderation for \"{$contentTitle}\" has exceeded SLA. Immediate attention required.",
                'icon' => 'clock',
                'priority' => UserNotification::PRIORITY_URGENT,
                'action_url' => route('admin.moderation.task-flow').'?item='.$item->id,
                'action_label' => 'Review Immediately',
            ]
        );
    }

    protected function notifyContentOwner(ModerationQueueItem $item, string $decision): void
    {
        if (in_array($decision, [ModerationDecision::DECISION_SKIP])) {
            return;
        }

        $this->assignmentService->notifyModerationComplete(
            $item->moderationResult,
            match ($decision) {
                ModerationDecision::DECISION_APPROVE => 'approved',
                ModerationDecision::DECISION_REJECT => 'rejected',
                ModerationDecision::DECISION_REQUEST_CHANGES => 'revision_requested',
                default => 'completed',
            }
        );
    }

    protected function mapPriorityToNotification(string $priority): string
    {
        return match ($priority) {
            ModerationQueueItem::PRIORITY_URGENT => UserNotification::PRIORITY_URGENT,
            ModerationQueueItem::PRIORITY_HIGH => UserNotification::PRIORITY_HIGH,
            ModerationQueueItem::PRIORITY_NORMAL => UserNotification::PRIORITY_NORMAL,
            ModerationQueueItem::PRIORITY_LOW => UserNotification::PRIORITY_LOW,
            default => UserNotification::PRIORITY_NORMAL,
        };
    }
}
