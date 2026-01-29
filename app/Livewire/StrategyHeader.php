<?php

namespace App\Livewire;

use App\Models\StrategicPlan;
use App\Models\User;
use App\Models\StrategyCollaborator;
use App\Models\StrategyAssignment;
use Livewire\Component;

class StrategyHeader extends Component
{
    public StrategicPlan $strategy;

    public $editingTitle = false;
    public $newTitle = '';

    // For adding collaborators
    public $showCollaboratorModal = false;
    public $searchCollaborator = '';
    public $selectedCollaboratorRole = 'collaborator';

    // For adding assignments
    public $showAssignmentModal = false;
    public $searchAssignment = '';

    public function mount(StrategicPlan $strategy)
    {
        $this->strategy = $strategy;
        $this->newTitle = $strategy->title;
    }

    public function startEditTitle()
    {
        $this->editingTitle = true;
        $this->newTitle = $this->strategy->title;
    }

    public function saveTitle()
    {
        $this->validate(['newTitle' => 'required|string|max:255']);

        $this->strategy->update(['title' => $this->newTitle]);
        $this->editingTitle = false;
    }

    public function cancelEditTitle()
    {
        $this->editingTitle = false;
        $this->newTitle = $this->strategy->title;
    }

    // Collaborator management
    public function openCollaboratorModal()
    {
        $this->showCollaboratorModal = true;
        $this->searchCollaborator = '';
    }

    public function closeCollaboratorModal()
    {
        $this->showCollaboratorModal = false;
        $this->searchCollaborator = '';
    }

    public function addCollaborator($userId)
    {
        // Check if already a collaborator
        $exists = StrategyCollaborator::where('strategic_plan_id', $this->strategy->id)
            ->where('user_id', $userId)
            ->exists();

        if (!$exists) {
            StrategyCollaborator::create([
                'strategic_plan_id' => $this->strategy->id,
                'user_id' => $userId,
                'role' => $this->selectedCollaboratorRole,
            ]);

            $this->strategy->refresh();
        }

        $this->closeCollaboratorModal();
    }

    public function removeCollaborator($collaboratorId)
    {
        $collaborator = StrategyCollaborator::find($collaboratorId);

        // Don't remove the last owner
        if ($collaborator && $collaborator->role === 'owner') {
            $ownerCount = $this->strategy->collaborators()->where('role', 'owner')->count();
            if ($ownerCount <= 1) {
                return; // Can't remove last owner
            }
        }

        if ($collaborator) {
            $collaborator->delete();
            $this->strategy->refresh();
        }
    }

    public function updateCollaboratorRole($collaboratorId, $role)
    {
        $collaborator = StrategyCollaborator::find($collaboratorId);

        if ($collaborator) {
            // Don't demote the last owner
            if ($collaborator->role === 'owner' && $role !== 'owner') {
                $ownerCount = $this->strategy->collaborators()->where('role', 'owner')->count();
                if ($ownerCount <= 1) {
                    return;
                }
            }

            $collaborator->update(['role' => $role]);
            $this->strategy->refresh();
        }
    }

    // Assignment management
    public function openAssignmentModal()
    {
        $this->showAssignmentModal = true;
        $this->searchAssignment = '';
    }

    public function closeAssignmentModal()
    {
        $this->showAssignmentModal = false;
        $this->searchAssignment = '';
    }

    public function addAssignment($type, $id)
    {
        // Check if already assigned
        $exists = StrategyAssignment::where('strategic_plan_id', $this->strategy->id)
            ->where('assignable_type', $type)
            ->where('assignable_id', $id)
            ->exists();

        if (!$exists) {
            StrategyAssignment::create([
                'strategic_plan_id' => $this->strategy->id,
                'assignable_type' => $type,
                'assignable_id' => $id,
                'assigned_by' => auth()->id(),
            ]);

            $this->strategy->refresh();
        }

        $this->closeAssignmentModal();
    }

    public function removeAssignment($assignmentId)
    {
        StrategyAssignment::find($assignmentId)?->delete();
        $this->strategy->refresh();
    }

    public function getSearchedUsersProperty()
    {
        if (empty($this->searchCollaborator)) {
            return collect();
        }

        return User::where('org_id', auth()->user()->org_id)
            ->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->searchCollaborator . '%')
                  ->orWhere('last_name', 'like', '%' . $this->searchCollaborator . '%')
                  ->orWhere('email', 'like', '%' . $this->searchCollaborator . '%');
            })
            ->whereNotIn('id', $this->strategy->collaborators->pluck('user_id'))
            ->limit(10)
            ->get();
    }

    public function render()
    {
        $this->strategy->load(['collaborators.user', 'assignments.assignable']);

        return view('livewire.strategy-header', [
            'collaborators' => $this->strategy->collaborators,
            'assignments' => $this->strategy->assignments,
        ]);
    }
}
