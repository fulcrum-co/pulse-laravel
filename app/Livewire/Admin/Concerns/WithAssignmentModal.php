<?php

namespace App\Livewire\Admin\Concerns;

use App\Models\ContentModerationResult;
use App\Services\Moderation\ModerationAssignmentService;
use Carbon\Carbon;

trait WithAssignmentModal
{
    public bool $showAssignModal = false;

    public ?int $assignToUserId = null;

    public string $assignmentPriority = 'normal';

    public ?string $assignmentDueAt = null;

    public string $assignmentNotes = '';

    public array $selectedCollaborators = [];

    public function openAssignModal(?int $resultId = null): void
    {
        if ($resultId) {
            $this->selectedResultId = $resultId;
            $result = ContentModerationResult::find($resultId);
            if ($result) {
                $this->assignToUserId = $result->assigned_to;
                $this->selectedCollaborators = $result->collaborator_ids ?? [];
                $this->assignmentPriority = $result->assignment_priority ?? 'normal';
                $this->assignmentDueAt = $result->due_at?->format('Y-m-d');
                $this->assignmentNotes = $result->assignment_notes ?? '';
            }
        }
        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->assignToUserId = null;
        $this->assignmentPriority = 'normal';
        $this->assignmentDueAt = null;
        $this->assignmentNotes = '';
        $this->selectedCollaborators = [];

        if (! $this->showReviewModal && ! $this->showEditModal) {
            $this->selectedResultId = null;
        }
    }

    public function saveAssignment(): void
    {
        $this->validate([
            'assignToUserId' => 'required|exists:users,id',
            'assignmentPriority' => 'required|in:low,normal,high,urgent',
            'assignmentDueAt' => 'nullable|date|after_or_equal:today',
        ]);

        $service = app(ModerationAssignmentService::class);

        // Single assignment
        if ($this->selectedResultId && empty($this->selectedItems)) {
            $result = ContentModerationResult::find($this->selectedResultId);
            if ($result) {
                $service->assign(
                    $result,
                    $this->assignToUserId,
                    auth()->id(),
                    [
                        'priority' => $this->assignmentPriority,
                        'due_at' => $this->assignmentDueAt ? Carbon::parse($this->assignmentDueAt) : null,
                        'notes' => $this->assignmentNotes,
                    ],
                );

                // Add collaborators
                foreach ($this->selectedCollaborators as $collaboratorId) {
                    if ($collaboratorId != $this->assignToUserId) {
                        $service->addCollaborator($result, (int) $collaboratorId, auth()->id());
                    }
                }
            }
        }
        // Bulk assignment
        elseif (! empty($this->selectedItems)) {
            $service->bulkAssign(
                $this->selectedItems,
                $this->assignToUserId,
                auth()->id(),
                [
                    'priority' => $this->assignmentPriority,
                    'due_at' => $this->assignmentDueAt ? Carbon::parse($this->assignmentDueAt) : null,
                    'notes' => $this->assignmentNotes,
                ],
            );
            $this->selectedItems = [];
            $this->selectAll = false;
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Assignment saved successfully.',
        ]);

        $this->closeAssignModal();
    }

    public function unassign(int $resultId): void
    {
        $result = ContentModerationResult::find($resultId);
        if ($result && $this->canManageAssignment($result)) {
            $result->unassign();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Assignment removed.',
            ]);
        }
    }

    public function bulkAssign(): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Please select items to assign.',
            ]);

            return;
        }

        $this->selectedResultId = null;
        $this->openAssignModal();
    }
}
