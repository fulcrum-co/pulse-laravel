<?php

namespace App\Livewire;

use App\Models\Resource;
use App\Models\ResourceAssignment;
use App\Models\AuditLog;
use Livewire\Component;
use Livewire\WithPagination;

class ContactResources extends Component
{
    use WithPagination;

    public string $contactType;
    public int $contactId;

    // Assign resource modal
    public bool $showAssignModal = false;
    public ?int $selectedResourceId = null;
    public string $assignmentNotes = '';
    public string $searchResources = '';

    // Expanded assignment tracking
    public ?int $expandedAssignmentId = null;

    // Edit mode
    public ?int $editingAssignmentId = null;
    public ?int $editingProgress = null;
    public string $editingStatus = '';
    public string $editingNotes = '';

    // Filter
    public string $filterStatus = 'all';

    public function mount(string $contactType, int $contactId)
    {
        $this->contactType = $contactType;
        $this->contactId = $contactId;
    }

    public function openAssignModal(): void
    {
        $this->showAssignModal = true;
        $this->selectedResourceId = null;
        $this->assignmentNotes = '';
        $this->searchResources = '';
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->selectedResourceId = null;
        $this->assignmentNotes = '';
        $this->searchResources = '';
    }

    public function assignResource(): void
    {
        $this->validate([
            'selectedResourceId' => 'required|exists:resources,id',
        ]);

        $user = auth()->user();

        $assignment = ResourceAssignment::create([
            'resource_id' => $this->selectedResourceId,
            'student_id' => $this->contactId,
            'assigned_by' => $user->id,
            'status' => 'pending',
            'notes' => $this->assignmentNotes ?: null,
            'assigned_at' => now(),
            'progress_percent' => 0,
        ]);

        AuditLog::log('create', $assignment);

        $this->closeAssignModal();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Resource assigned successfully.',
        ]);
    }

    public function toggleExpand(int $assignmentId): void
    {
        if ($this->expandedAssignmentId === $assignmentId) {
            $this->expandedAssignmentId = null;
        } else {
            $this->expandedAssignmentId = $assignmentId;
            $this->editingAssignmentId = null;
        }
    }

    public function startEdit(int $assignmentId): void
    {
        $assignment = ResourceAssignment::findOrFail($assignmentId);

        $this->editingAssignmentId = $assignmentId;
        $this->expandedAssignmentId = $assignmentId;
        $this->editingProgress = $assignment->progress_percent ?? 0;
        $this->editingStatus = $assignment->status ?? 'pending';
        $this->editingNotes = $assignment->notes ?? '';
    }

    public function cancelEdit(): void
    {
        $this->editingAssignmentId = null;
        $this->editingProgress = null;
        $this->editingStatus = '';
        $this->editingNotes = '';
    }

    public function saveChanges(): void
    {
        $assignment = ResourceAssignment::findOrFail($this->editingAssignmentId);

        $oldValues = $assignment->only(['progress_percent', 'status', 'notes']);

        $updateData = [
            'progress_percent' => $this->editingProgress,
            'status' => $this->editingStatus,
            'notes' => $this->editingNotes ?: null,
        ];

        // Auto-update timestamps based on status
        if ($this->editingStatus === 'in_progress' && !$assignment->started_at) {
            $updateData['started_at'] = now();
        }
        if ($this->editingStatus === 'completed' && !$assignment->completed_at) {
            $updateData['completed_at'] = now();
            $updateData['progress_percent'] = 100;
        }

        $assignment->update($updateData);

        AuditLog::log('update', $assignment, $oldValues, $updateData);

        $this->cancelEdit();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Resource assignment updated.',
        ]);
    }

    public function removeAssignment(int $assignmentId): void
    {
        $assignment = ResourceAssignment::findOrFail($assignmentId);

        AuditLog::log('delete', $assignment);
        $assignment->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Resource assignment removed.',
        ]);
    }

    public function setFilterStatus(string $status): void
    {
        $this->filterStatus = $status;
        $this->resetPage();
    }

    public function getAssignmentsProperty()
    {
        $query = ResourceAssignment::where('student_id', $this->contactId)
            ->with(['resource', 'assigner'])
            ->orderByDesc('assigned_at');

        if ($this->filterStatus !== 'all') {
            $query->where('status', $this->filterStatus);
        }

        return $query->paginate(10);
    }

    public function getAvailableResourcesProperty()
    {
        $user = auth()->user();

        $query = Resource::forOrganization($user->org_id)
            ->active()
            ->orderBy('title');

        if ($this->searchResources) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->searchResources . '%')
                  ->orWhere('description', 'like', '%' . $this->searchResources . '%');
            });
        }

        return $query->get();
    }

    public function render()
    {
        return view('livewire.contact-resources', [
            'assignments' => $this->assignments,
            'availableResources' => $this->availableResources,
        ]);
    }
}
