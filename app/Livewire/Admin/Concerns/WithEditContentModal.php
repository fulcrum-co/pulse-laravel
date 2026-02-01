<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\ContentModerationResult;
use App\Models\CourseApprovalWorkflow;
use App\Models\MiniCourse;
use App\Services\Moderation\ModerationAssignmentService;

trait WithEditContentModal
{
    public bool $showEditModal = false;

    public array $editForm = [];

    public function openEditModal(int $resultId): void
    {
        $result = ContentModerationResult::with('moderatable')->find($resultId);

        if (! $result?->moderatable) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Content not found.',
            ]);

            return;
        }

        $this->selectedResultId = $resultId;
        $this->editForm = $this->buildEditForm($result->moderatable);
        $this->showEditModal = true;
    }

    protected function buildEditForm($moderatable): array
    {
        $class = get_class($moderatable);

        return match ($class) {
            'App\\Models\\MiniCourse' => [
                'type' => 'MiniCourse',
                'id' => $moderatable->id,
                'title' => $moderatable->title,
                'description' => $moderatable->description,
                'rationale' => $moderatable->rationale ?? '',
                'expected_experience' => $moderatable->expected_experience ?? '',
                'objectives' => $moderatable->objectives ?? [],
            ],
            'App\\Models\\ContentBlock' => [
                'type' => 'ContentBlock',
                'id' => $moderatable->id,
                'title' => $moderatable->title,
                'description' => $moderatable->description ?? '',
            ],
            default => [
                'type' => 'Unknown',
                'id' => $moderatable->id ?? null,
            ],
        };
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editForm = [];

        if (! $this->showReviewModal && ! $this->showAssignModal) {
            $this->selectedResultId = null;
        }
    }

    public function saveContentEdits(): void
    {
        $result = $this->selectedResult;
        if (! $result?->moderatable) {
            return;
        }

        $moderatable = $result->moderatable;

        match ($this->editForm['type']) {
            'MiniCourse' => $this->updateMiniCourse($moderatable),
            'ContentBlock' => $this->updateContentBlock($moderatable),
            default => null,
        };

        // Trigger re-moderation if available
        if (method_exists($moderatable, 'queueModeration')) {
            $moderatable->queueModeration();
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content updated. Re-moderation has been triggered.',
        ]);

        $this->closeEditModal();
    }

    public function saveAndApprove(): void
    {
        $result = $this->selectedResult;
        if (! $result?->moderatable) {
            return;
        }

        $moderatable = $result->moderatable;

        match ($this->editForm['type']) {
            'MiniCourse' => $this->updateMiniCourse($moderatable),
            'ContentBlock' => $this->updateContentBlock($moderatable),
            default => null,
        };

        $result->approve(auth()->id(), 'Approved after content edits');

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'approved');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content updated and approved.',
        ]);

        $this->closeEditModal();
        $this->closeReviewModal();
    }

    public function saveAndPublish(): void
    {
        $result = $this->selectedResult;
        if (! $result?->moderatable) {
            return;
        }

        $moderatable = $result->moderatable;

        if (! ($moderatable instanceof MiniCourse)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Only Mini Courses can be published.',
            ]);

            return;
        }

        $this->updateMiniCourse($moderatable);

        $result->approve(auth()->id(), 'Approved and published after content edits');

        $workflow = CourseApprovalWorkflow::firstOrNew([
            'mini_course_id' => $moderatable->id,
        ]);

        $workflow->fill([
            'status' => CourseApprovalWorkflow::STATUS_APPROVED,
            'workflow_mode' => CourseApprovalWorkflow::MODE_CREATE_APPROVE,
            'submitted_by' => $moderatable->created_by ?? auth()->id(),
            'submitted_at' => $workflow->submitted_at ?? now(),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => 'Approved and published from moderation queue',
        ]);

        $workflow->save();

        $moderatable->update([
            'approval_status' => MiniCourse::APPROVAL_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => 'Approved and published from moderation queue',
            'status' => MiniCourse::STATUS_ACTIVE,
        ]);

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'approved');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Course updated, approved, and published successfully.',
        ]);

        $this->closeEditModal();
        $this->closeReviewModal();
    }

    protected function updateMiniCourse($course): void
    {
        $this->validate([
            'editForm.title' => 'required|string|max:255',
            'editForm.description' => 'required|string',
        ]);

        $course->update([
            'title' => $this->editForm['title'],
            'description' => $this->editForm['description'],
            'rationale' => $this->editForm['rationale'],
            'expected_experience' => $this->editForm['expected_experience'],
            'objectives' => $this->editForm['objectives'],
        ]);
    }

    protected function updateContentBlock($block): void
    {
        $this->validate([
            'editForm.title' => 'required|string|max:255',
        ]);

        $block->update([
            'title' => $this->editForm['title'],
            'description' => $this->editForm['description'],
        ]);
    }
}
