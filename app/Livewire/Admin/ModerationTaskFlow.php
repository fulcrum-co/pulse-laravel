<?php

namespace App\Livewire\Admin;

use App\Models\ModerationDecision;
use App\Models\ModerationQueueItem;
use App\Services\Moderation\ModerationQueueService;
use App\Services\Moderation\ModerationWorkflowService;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.dashboard')]
#[Title('Moderation Task Flow')]
class ModerationTaskFlow extends Component
{
    #[Url]
    public ?int $item = null;

    public ?ModerationQueueItem $currentItem = null;

    public string $viewMode = 'queue'; // queue, reviewing, complete

    public string $notes = '';

    public bool $showEditModal = false;

    public array $editableContent = [];

    public int $reviewStartTime = 0;

    public int $itemsReviewedToday = 0;

    protected ModerationQueueService $queueService;

    protected ModerationWorkflowService $workflowService;

    public function boot(
        ModerationQueueService $queueService,
        ModerationWorkflowService $workflowService
    ): void {
        $this->queueService = $queueService;
        $this->workflowService = $workflowService;
    }

    public function mount(): void
    {
        $this->loadStats();

        // If specific item requested, load it
        if ($this->item) {
            $this->claimItem($this->item);
        }
    }

    /**
     * Check if user can perform moderation actions (approve/reject).
     */
    protected function canModerate(): bool
    {
        $user = auth()->user();

        return in_array($user->effective_role, ['admin', 'consultant', 'superintendent', 'school_admin']);
    }

    #[Computed]
    public function queueStats(): array
    {
        $accessibleOrgIds = auth()->user()->getAccessibleOrganizations()->pluck('id')->toArray();

        return $this->queueService->getQueueStats($accessibleOrgIds);
    }

    #[Computed]
    public function userStats(): array
    {
        return $this->queueService->getUserStats(auth()->user());
    }

    #[Computed]
    public function myQueue(): Collection
    {
        return $this->queueService->getQueueForUser(auth()->user());
    }

    public function loadStats(): void
    {
        $this->itemsReviewedToday = ModerationDecision::byUser(auth()->id())
            ->today()
            ->count();
    }

    /**
     * Claim an item and start reviewing.
     */
    public function claimItem(int $itemId): void
    {
        $item = ModerationQueueItem::with(['moderationResult.moderatable'])
            ->find($itemId);

        if (! $item) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Item not found.',
            ]);

            return;
        }

        // Check authorization
        if (! auth()->user()->canAccessOrganization($item->org_id)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You do not have access to this item.',
            ]);

            return;
        }

        // Assign if not already assigned
        if (! $item->assigned_to) {
            $this->queueService->assignToUser($item, auth()->user());
            $item->refresh();
        }

        // Start review
        $item->startReview();

        $this->currentItem = $item;
        $this->viewMode = 'reviewing';
        $this->notes = '';
        $this->reviewStartTime = time();

        // Load editable content
        $this->loadEditableContent();
    }

    /**
     * Get the next item automatically.
     */
    public function startNextItem(): void
    {
        $nextItem = $this->queueService->getNextItemForUser(auth()->user());

        if ($nextItem) {
            $this->claimItem($nextItem->id);
        } else {
            $this->viewMode = 'complete';
            $this->currentItem = null;
        }
    }

    /**
     * Submit a decision on the current item.
     */
    public function submitDecision(string $decision): void
    {
        if (! $this->currentItem) {
            return;
        }

        // Check authorization
        if (! $this->canModerate()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You do not have permission to submit moderation decisions. Demo mode is view-only.',
            ]);

            return;
        }

        // Validate decision
        if (! in_array($decision, ModerationDecision::$decisions)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Invalid decision.',
            ]);

            return;
        }

        // Check if notes required
        $metadata = $this->currentItem->metadata ?? [];
        if (($metadata['required_note'] ?? false) && empty($this->notes)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please add notes before submitting.',
            ]);

            return;
        }

        // Process the decision
        $this->queueService->processDecision(
            $this->currentItem,
            auth()->user(),
            $decision,
            $this->notes ?: null
        );

        // Update stats
        $this->itemsReviewedToday++;

        // Clear current item and auto-advance
        $this->currentItem = null;
        $this->notes = '';

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Decision recorded. Loading next item...',
        ]);

        // Automatically load next item
        $this->startNextItem();
    }

    /**
     * Quick approve action.
     */
    public function approve(): void
    {
        $this->submitDecision(ModerationDecision::DECISION_APPROVE);
    }

    /**
     * Quick reject action.
     */
    public function reject(): void
    {
        $this->submitDecision(ModerationDecision::DECISION_REJECT);
    }

    /**
     * Request changes action.
     */
    public function requestChanges(): void
    {
        if (empty($this->notes)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Please add notes explaining what changes are needed.',
            ]);

            return;
        }

        $this->submitDecision(ModerationDecision::DECISION_REQUEST_CHANGES);
    }

    /**
     * Escalate action.
     */
    public function escalate(): void
    {
        $this->submitDecision(ModerationDecision::DECISION_ESCALATE);
    }

    /**
     * Skip current item.
     */
    public function skipItem(): void
    {
        if (! $this->currentItem) {
            return;
        }

        $this->queueService->processDecision(
            $this->currentItem,
            auth()->user(),
            ModerationDecision::DECISION_SKIP,
            $this->notes ?: null
        );

        $this->currentItem = null;
        $this->notes = '';

        // Load next item
        $this->startNextItem();
    }

    /**
     * Exit review mode.
     */
    public function exitReview(): void
    {
        if ($this->currentItem) {
            // Return item to pending if we're abandoning
            $this->currentItem->update([
                'status' => ModerationQueueItem::STATUS_PENDING,
                'started_at' => null,
            ]);
        }

        $this->currentItem = null;
        $this->viewMode = 'queue';
        $this->notes = '';
    }

    /**
     * Open edit content modal.
     */
    public function openEditModal(): void
    {
        $this->loadEditableContent();
        $this->showEditModal = true;
    }

    /**
     * Close edit modal.
     */
    public function closeEditModal(): void
    {
        $this->showEditModal = false;
    }

    /**
     * Load editable content fields.
     */
    protected function loadEditableContent(): void
    {
        if (! $this->currentItem?->moderationResult?->moderatable) {
            $this->editableContent = [];

            return;
        }

        $moderatable = $this->currentItem->moderationResult->moderatable;
        $contentType = class_basename(get_class($moderatable));

        $this->editableContent = match ($contentType) {
            'MiniCourse' => [
                'title' => $moderatable->title ?? '',
                'description' => $moderatable->description ?? '',
                'rationale' => $moderatable->rationale ?? '',
                'objectives' => is_array($moderatable->objectives)
                    ? implode("\n", $moderatable->objectives)
                    : ($moderatable->objectives ?? ''),
            ],
            'ContentBlock' => [
                'title' => $moderatable->title ?? '',
                'description' => $moderatable->description ?? '',
                'content' => $moderatable->content_data['content'] ?? '',
            ],
            default => [
                'title' => $moderatable->title ?? '',
                'description' => $moderatable->description ?? '',
            ],
        };
    }

    /**
     * Save edited content.
     */
    public function saveEditedContent(): void
    {
        if (! $this->currentItem?->moderationResult?->moderatable) {
            return;
        }

        $moderatable = $this->currentItem->moderationResult->moderatable;
        $contentType = class_basename(get_class($moderatable));

        $previousValues = [];
        $newValues = [];

        match ($contentType) {
            'MiniCourse' => $this->updateMiniCourse($moderatable, $previousValues, $newValues),
            'ContentBlock' => $this->updateContentBlock($moderatable, $previousValues, $newValues),
            default => null,
        };

        // Track field changes for audit
        if (! empty($newValues)) {
            $this->currentItem->update([
                'metadata' => array_merge($this->currentItem->metadata ?? [], [
                    'content_edited' => true,
                    'edited_at' => now()->toIso8601String(),
                    'edited_by' => auth()->id(),
                ]),
            ]);
        }

        $this->showEditModal = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content updated successfully.',
        ]);
    }

    /**
     * Update MiniCourse fields.
     */
    protected function updateMiniCourse($course, array &$previous, array &$new): void
    {
        $updates = [];

        if (isset($this->editableContent['title']) && $course->title !== $this->editableContent['title']) {
            $previous['title'] = $course->title;
            $new['title'] = $this->editableContent['title'];
            $updates['title'] = $this->editableContent['title'];
        }

        if (isset($this->editableContent['description']) && $course->description !== $this->editableContent['description']) {
            $previous['description'] = $course->description;
            $new['description'] = $this->editableContent['description'];
            $updates['description'] = $this->editableContent['description'];
        }

        if (isset($this->editableContent['rationale']) && $course->rationale !== $this->editableContent['rationale']) {
            $previous['rationale'] = $course->rationale;
            $new['rationale'] = $this->editableContent['rationale'];
            $updates['rationale'] = $this->editableContent['rationale'];
        }

        if (isset($this->editableContent['objectives'])) {
            $objectives = array_filter(array_map('trim', explode("\n", $this->editableContent['objectives'])));
            if ($course->objectives !== $objectives) {
                $previous['objectives'] = $course->objectives;
                $new['objectives'] = $objectives;
                $updates['objectives'] = $objectives;
            }
        }

        if (! empty($updates)) {
            $course->update($updates);
        }
    }

    /**
     * Update ContentBlock fields.
     */
    protected function updateContentBlock($block, array &$previous, array &$new): void
    {
        $updates = [];

        if (isset($this->editableContent['title']) && $block->title !== $this->editableContent['title']) {
            $previous['title'] = $block->title;
            $new['title'] = $this->editableContent['title'];
            $updates['title'] = $this->editableContent['title'];
        }

        if (isset($this->editableContent['description']) && $block->description !== $this->editableContent['description']) {
            $previous['description'] = $block->description;
            $new['description'] = $this->editableContent['description'];
            $updates['description'] = $this->editableContent['description'];
        }

        if (isset($this->editableContent['content'])) {
            $contentData = $block->content_data ?? [];
            if (($contentData['content'] ?? '') !== $this->editableContent['content']) {
                $previous['content'] = $contentData['content'] ?? '';
                $new['content'] = $this->editableContent['content'];
                $contentData['content'] = $this->editableContent['content'];
                $updates['content_data'] = $contentData;
            }
        }

        if (! empty($updates)) {
            $block->update($updates);
        }
    }

    /**
     * Get time spent on current review.
     */
    #[Computed]
    public function timeSpent(): string
    {
        if (! $this->reviewStartTime) {
            return '0:00';
        }

        $seconds = time() - $this->reviewStartTime;
        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        return sprintf('%d:%02d', $minutes, $remainingSeconds);
    }

    /**
     * Get progress percentage for today.
     */
    #[Computed]
    public function todayProgress(): array
    {
        $target = 20; // Target reviews per day
        $percentage = min(100, ($this->itemsReviewedToday / $target) * 100);

        return [
            'completed' => $this->itemsReviewedToday,
            'target' => $target,
            'percentage' => round($percentage, 1),
        ];
    }

    public function render()
    {
        return view('livewire.admin.moderation-task-flow', [
            'canModerate' => $this->canModerate(),
        ]);
    }
}
