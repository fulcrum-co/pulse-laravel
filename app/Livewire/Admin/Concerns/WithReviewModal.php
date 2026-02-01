<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\ContentModerationResult;
use App\Services\Moderation\ModerationAssignmentService;

trait WithReviewModal
{
    public bool $showReviewModal = false;

    public ?int $selectedResultId = null;

    public string $reviewNotes = '';

    public function openReviewModal(int $resultId): void
    {
        $this->selectedResultId = $resultId;
        $this->reviewNotes = '';
        $this->showReviewModal = true;
    }

    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
        $this->selectedResultId = null;
        $this->reviewNotes = '';
    }

    public function getSelectedResultProperty(): ?ContentModerationResult
    {
        if (! $this->selectedResultId) {
            return null;
        }

        try {
            return ContentModerationResult::with(['moderatable', 'reviewer', 'assignee'])
                ->find($this->selectedResultId);
        } catch (\Exception $e) {
            \Log::error('Error loading moderation result: ' . $e->getMessage());

            return ContentModerationResult::find($this->selectedResultId);
        }
    }

    public function approveContent(): void
    {
        $result = $this->selectedResult;
        if (! $result) {
            return;
        }

        if (! $result->canBeReviewedBy(auth()->user())) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You do not have permission to review this item.',
            ]);

            return;
        }

        $result->approve(auth()->id(), $this->reviewNotes);

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'approved');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content approved successfully.',
        ]);

        $this->closeReviewModal();
    }

    public function rejectContent(): void
    {
        $result = $this->selectedResult;
        if (! $result) {
            return;
        }

        $result->confirmRejection(auth()->id(), $this->reviewNotes);

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'rejected');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content rejection confirmed.',
        ]);

        $this->closeReviewModal();
    }

    public function requestRevision(): void
    {
        $result = $this->selectedResult;
        if (! $result) {
            return;
        }

        $result->update([
            'status' => ContentModerationResult::STATUS_FLAGGED,
            'human_reviewed' => true,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $this->reviewNotes ?: 'Revision requested',
        ]);

        if ($result->moderatable && method_exists($result->moderatable, 'needsRevision')) {
            $result->moderatable->update([
                'approval_status' => 'revision_requested',
                'approval_notes' => $this->reviewNotes,
            ]);
        }

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'revision_requested');

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Revision requested for this content.',
        ]);

        $this->closeReviewModal();
    }

    public function quickApprove(int $resultId): void
    {
        $result = ContentModerationResult::find($resultId);
        if (! $result) {
            return;
        }

        if (! $result->canBeReviewedBy(auth()->user())) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You do not have permission to review this item.',
            ]);

            return;
        }

        $result->approve(auth()->id(), 'Quick approved from queue');

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'approved');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content approved successfully.',
        ]);
    }

    public function quickReject(int $resultId): void
    {
        $result = ContentModerationResult::find($resultId);
        if (! $result) {
            return;
        }

        if (! $result->canBeReviewedBy(auth()->user())) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You do not have permission to review this item.',
            ]);

            return;
        }

        $result->confirmRejection(auth()->id(), 'Rejected from queue');

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'rejected');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content rejected.',
        ]);
    }
}
