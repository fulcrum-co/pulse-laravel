<?php

namespace App\Livewire;

use App\Models\StrategicPlan;
use App\Models\StrategyAssignment;
use App\Models\StrategyCollaborator;
use App\Models\User;
use Livewire\Component;

class PlanHeader extends Component
{
    public StrategicPlan $plan;

    public $editingTitle = false;

    public $newTitle = '';

    // For adding collaborators
    public $showCollaboratorModal = false;

    public $searchCollaborator = '';

    public $selectedCollaboratorRole = 'collaborator';

    // For adding assignments
    public $showAssignmentModal = false;

    public $searchAssignment = '';

    public function mount(StrategicPlan $plan)
    {
        $this->plan = $plan;
        $this->newTitle = $plan->title;
    }

    public function startEditTitle()
    {
        $this->editingTitle = true;
        $this->newTitle = $this->plan->title;
    }

    public function saveTitle()
    {
        $this->validate(['newTitle' => 'required|string|max:255']);

        $this->plan->update(['title' => $this->newTitle]);
        $this->editingTitle = false;
    }

    public function cancelEditTitle()
    {
        $this->editingTitle = false;
        $this->newTitle = $this->plan->title;
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
        $exists = StrategyCollaborator::where('strategic_plan_id', $this->plan->id)
            ->where('user_id', $userId)
            ->exists();

        if (! $exists) {
            StrategyCollaborator::create([
                'strategic_plan_id' => $this->plan->id,
                'user_id' => $userId,
                'role' => $this->selectedCollaboratorRole,
            ]);

            $this->plan->refresh();
        }

        $this->closeCollaboratorModal();
    }

    public function removeCollaborator($collaboratorId)
    {
        $collaborator = StrategyCollaborator::find($collaboratorId);

        // Don't remove the last owner
        if ($collaborator && $collaborator->role === 'owner') {
            $ownerCount = $this->plan->collaborators()->where('role', 'owner')->count();
            if ($ownerCount <= 1) {
                return; // Can't remove last owner
            }
        }

        if ($collaborator) {
            $collaborator->delete();
            $this->plan->refresh();
        }
    }

    public function updateCollaboratorRole($collaboratorId, $role)
    {
        $collaborator = StrategyCollaborator::find($collaboratorId);

        if ($collaborator) {
            // Don't demote the last owner
            if ($collaborator->role === 'owner' && $role !== 'owner') {
                $ownerCount = $this->plan->collaborators()->where('role', 'owner')->count();
                if ($ownerCount <= 1) {
                    return;
                }
            }

            $collaborator->update(['role' => $role]);
            $this->plan->refresh();
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
        $exists = StrategyAssignment::where('strategic_plan_id', $this->plan->id)
            ->where('assignable_type', $type)
            ->where('assignable_id', $id)
            ->exists();

        if (! $exists) {
            StrategyAssignment::create([
                'strategic_plan_id' => $this->plan->id,
                'assignable_type' => $type,
                'assignable_id' => $id,
                'assigned_by' => auth()->id(),
            ]);

            $this->plan->refresh();
        }

        $this->closeAssignmentModal();
    }

    public function removeAssignment($assignmentId)
    {
        StrategyAssignment::find($assignmentId)?->delete();
        $this->plan->refresh();
    }

    public function getSearchedUsersProperty()
    {
        if (empty($this->searchCollaborator)) {
            return collect();
        }

        return User::where('org_id', auth()->user()->org_id)
            ->where(function ($q) {
                $q->where('first_name', 'like', '%'.$this->searchCollaborator.'%')
                    ->orWhere('last_name', 'like', '%'.$this->searchCollaborator.'%')
                    ->orWhere('email', 'like', '%'.$this->searchCollaborator.'%');
            })
            ->whereNotIn('id', $this->plan->collaborators->pluck('user_id'))
            ->limit(10)
            ->get();
    }

    public function render()
    {
        $this->plan->load(['collaborators.user', 'assignments.assignable']);

        return view('livewire.plan-header', [
            'collaborators' => $this->plan->collaborators,
            'assignments' => $this->plan->assignments,
        ]);
    }
}
