<?php

namespace App\Livewire;

use App\Models\Activity;
use App\Models\FocusArea;
use App\Models\Objective;
use App\Models\StrategicPlan;
use Livewire\Component;

class PlanPlanner extends Component
{
    public StrategicPlan $plan;

    // For adding new items
    public $newFocusAreaTitle = '';

    public $newObjectiveTitle = '';

    public $newActivityTitle = '';

    public $addingTo = null; // 'plan', 'focus_area_X', 'objective_X'

    // For inline editing
    public $editingId = null;

    public $editingType = null;

    public $editingTitle = '';

    // Expanded states
    public $expandedFocusAreas = [];

    public $expandedObjectives = [];

    protected $listeners = ['refreshPlanner' => '$refresh'];

    public function mount(StrategicPlan $plan)
    {
        $this->plan = $plan;

        // Expand all by default
        foreach ($plan->focusAreas as $fa) {
            $this->expandedFocusAreas[$fa->id] = true;
            foreach ($fa->objectives as $obj) {
                $this->expandedObjectives[$obj->id] = true;
            }
        }
    }

    public function toggleFocusArea($id)
    {
        $this->expandedFocusAreas[$id] = ! ($this->expandedFocusAreas[$id] ?? false);
    }

    public function toggleObjective($id)
    {
        $this->expandedObjectives[$id] = ! ($this->expandedObjectives[$id] ?? false);
    }

    // Start adding
    public function startAddFocusArea()
    {
        $this->addingTo = 'plan';
        $this->newFocusAreaTitle = '';
    }

    public function startAddObjective($focusAreaId)
    {
        $this->addingTo = 'focus_area_'.$focusAreaId;
        $this->newObjectiveTitle = '';
    }

    public function startAddActivity($objectiveId)
    {
        $this->addingTo = 'objective_'.$objectiveId;
        $this->newActivityTitle = '';
    }

    public function cancelAdd()
    {
        $this->addingTo = null;
        $this->newFocusAreaTitle = '';
        $this->newObjectiveTitle = '';
        $this->newActivityTitle = '';
    }

    // Save new items
    public function saveFocusArea()
    {
        $this->validate(['newFocusAreaTitle' => 'required|string|max:255']);

        $maxSortOrder = $this->plan->focusAreas()->max('sort_order') ?? -1;

        $fa = FocusArea::create([
            'strategic_plan_id' => $this->plan->id,
            'title' => $this->newFocusAreaTitle,
            'sort_order' => $maxSortOrder + 1,
            'status' => 'not_started',
        ]);

        $this->expandedFocusAreas[$fa->id] = true;
        $this->cancelAdd();
        $this->plan->refresh();
    }

    public function saveObjective($focusAreaId)
    {
        $this->validate(['newObjectiveTitle' => 'required|string|max:255']);

        $focusArea = FocusArea::findOrFail($focusAreaId);
        $maxSortOrder = $focusArea->objectives()->max('sort_order') ?? -1;

        $obj = Objective::create([
            'focus_area_id' => $focusAreaId,
            'title' => $this->newObjectiveTitle,
            'sort_order' => $maxSortOrder + 1,
            'status' => 'not_started',
        ]);

        $this->expandedObjectives[$obj->id] = true;
        $this->cancelAdd();
        $this->plan->refresh();
    }

    public function saveActivity($objectiveId)
    {
        $this->validate(['newActivityTitle' => 'required|string|max:255']);

        $objective = Objective::findOrFail($objectiveId);
        $maxSortOrder = $objective->activities()->max('sort_order') ?? -1;

        Activity::create([
            'objective_id' => $objectiveId,
            'title' => $this->newActivityTitle,
            'sort_order' => $maxSortOrder + 1,
            'status' => 'not_started',
        ]);

        $this->cancelAdd();
        $this->plan->refresh();
    }

    // Inline editing
    public function startEdit($type, $id, $title)
    {
        $this->editingType = $type;
        $this->editingId = $id;
        $this->editingTitle = $title;
    }

    public function cancelEdit()
    {
        $this->editingType = null;
        $this->editingId = null;
        $this->editingTitle = '';
    }

    public function saveEdit()
    {
        $this->validate(['editingTitle' => 'required|string|max:255']);

        $model = match ($this->editingType) {
            'focus_area' => FocusArea::findOrFail($this->editingId),
            'objective' => Objective::findOrFail($this->editingId),
            'activity' => Activity::findOrFail($this->editingId),
            default => null,
        };

        if ($model) {
            $model->update(['title' => $this->editingTitle]);
        }

        $this->cancelEdit();
        $this->plan->refresh();
    }

    // Update status
    public function updateStatus($type, $id, $status)
    {
        $model = match ($type) {
            'focus_area' => FocusArea::findOrFail($id),
            'objective' => Objective::findOrFail($id),
            'activity' => Activity::findOrFail($id),
            default => null,
        };

        if ($model) {
            $model->update(['status' => $status]);

            // Update parent statuses
            if ($type === 'activity') {
                $model->objective->updateStatusFromChildren();
            } elseif ($type === 'objective') {
                $model->focusArea->updateStatusFromChildren();
            }
        }

        $this->plan->refresh();
    }

    // Delete items
    public function deleteFocusArea($id)
    {
        FocusArea::findOrFail($id)->delete();
        $this->plan->refresh();
    }

    public function deleteObjective($id)
    {
        $objective = Objective::findOrFail($id);
        $focusArea = $objective->focusArea;
        $objective->delete();
        $focusArea->updateStatusFromChildren();
        $this->plan->refresh();
    }

    public function deleteActivity($id)
    {
        $activity = Activity::findOrFail($id);
        $objective = $activity->objective;
        $activity->delete();
        $objective->updateStatusFromChildren();
        $this->plan->refresh();
    }

    public function render()
    {
        $this->plan->load(['focusAreas.objectives.activities']);

        return view('livewire.plan-planner', [
            'focusAreas' => $this->plan->focusAreas,
        ]);
    }
}
