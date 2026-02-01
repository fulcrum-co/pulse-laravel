<?php

namespace App\Livewire;

use App\Models\Goal;
use App\Models\KeyResult;
use App\Models\StrategicPlan;
use Livewire\Component;

class GoalPlanner extends Component
{
    public StrategicPlan $plan;

    // Goal management
    public bool $showAddGoal = false;

    public string $newGoalTitle = '';

    public ?string $newGoalDescription = null;

    public ?string $newGoalDueDate = null;

    // Key Result management
    public ?int $addingKrToGoalId = null;

    public string $newKrTitle = '';

    public string $newKrMetricType = 'percentage';

    public ?float $newKrTargetValue = null;

    public ?float $newKrStartingValue = 0;

    public ?string $newKrUnit = '';

    public ?string $newKrDueDate = null;

    // Editing states
    public ?int $editingGoalId = null;

    public ?int $editingKrId = null;

    public array $editData = [];

    // Expanded states
    public array $expandedGoals = [];

    protected $listeners = ['goalUpdated' => '$refresh', 'krUpdated' => '$refresh'];

    public function mount(StrategicPlan $plan)
    {
        $this->plan = $plan;
        // Expand all goals by default
        $this->expandedGoals = $plan->goals->pluck('id')->toArray();
    }

    public function toggleGoal(int $goalId)
    {
        if (in_array($goalId, $this->expandedGoals)) {
            $this->expandedGoals = array_diff($this->expandedGoals, [$goalId]);
        } else {
            $this->expandedGoals[] = $goalId;
        }
    }

    public function showAddGoalForm()
    {
        $this->showAddGoal = true;
        $this->resetGoalForm();
    }

    public function cancelAddGoal()
    {
        $this->showAddGoal = false;
        $this->resetGoalForm();
    }

    protected function resetGoalForm()
    {
        $this->newGoalTitle = '';
        $this->newGoalDescription = null;
        $this->newGoalDueDate = null;
    }

    public function addGoal()
    {
        $this->validate([
            'newGoalTitle' => 'required|string|max:255',
            'newGoalDescription' => 'nullable|string',
            'newGoalDueDate' => 'nullable|date',
        ]);

        $maxSortOrder = $this->plan->goals()->max('sort_order') ?? 0;

        $goal = $this->plan->allGoals()->create([
            'title' => $this->newGoalTitle,
            'description' => $this->newGoalDescription,
            'due_date' => $this->newGoalDueDate,
            'goal_type' => Goal::TYPE_OBJECTIVE,
            'status' => Goal::STATUS_NOT_STARTED,
            'sort_order' => $maxSortOrder + 1,
            'owner_id' => auth()->id(),
        ]);

        $this->expandedGoals[] = $goal->id;
        $this->showAddGoal = false;
        $this->resetGoalForm();
        $this->plan->refresh();
    }

    public function startEditGoal(int $goalId)
    {
        $goal = Goal::find($goalId);
        if (! $goal) {
            return;
        }

        $this->editingGoalId = $goalId;
        $this->editData = [
            'title' => $goal->title,
            'description' => $goal->description,
            'due_date' => $goal->due_date?->format('Y-m-d'),
            'status' => $goal->status,
        ];
    }

    public function cancelEditGoal()
    {
        $this->editingGoalId = null;
        $this->editData = [];
    }

    public function saveGoal()
    {
        $this->validate([
            'editData.title' => 'required|string|max:255',
            'editData.description' => 'nullable|string',
            'editData.due_date' => 'nullable|date',
            'editData.status' => 'required|in:not_started,in_progress,at_risk,completed',
        ]);

        $goal = Goal::find($this->editingGoalId);
        if ($goal) {
            $goal->update([
                'title' => $this->editData['title'],
                'description' => $this->editData['description'] ?? null,
                'due_date' => $this->editData['due_date'] ?? null,
                'status' => $this->editData['status'],
            ]);
        }

        $this->editingGoalId = null;
        $this->editData = [];
        $this->plan->refresh();
    }

    public function deleteGoal(int $goalId)
    {
        $goal = Goal::find($goalId);
        if ($goal && $goal->strategic_plan_id === $this->plan->id) {
            $goal->delete();
            $this->plan->refresh();
        }
    }

    public function updateGoalStatus(int $goalId, string $status)
    {
        $goal = Goal::find($goalId);
        if ($goal && $goal->strategic_plan_id === $this->plan->id) {
            $goal->update(['status' => $status]);
            $this->plan->refresh();
        }
    }

    // Key Result methods
    public function showAddKrForm(int $goalId)
    {
        $this->addingKrToGoalId = $goalId;
        $this->resetKrForm();
    }

    public function cancelAddKr()
    {
        $this->addingKrToGoalId = null;
        $this->resetKrForm();
    }

    protected function resetKrForm()
    {
        $this->newKrTitle = '';
        $this->newKrMetricType = 'percentage';
        $this->newKrTargetValue = null;
        $this->newKrStartingValue = 0;
        $this->newKrUnit = '';
        $this->newKrDueDate = null;
    }

    public function addKeyResult()
    {
        $this->validate([
            'newKrTitle' => 'required|string|max:255',
            'newKrMetricType' => 'required|in:percentage,number,currency,boolean,milestone',
            'newKrTargetValue' => 'nullable|numeric',
            'newKrStartingValue' => 'nullable|numeric',
            'newKrUnit' => 'nullable|string|max:50',
            'newKrDueDate' => 'nullable|date',
        ]);

        $goal = Goal::find($this->addingKrToGoalId);
        if (! $goal) {
            return;
        }

        $maxSortOrder = $goal->keyResults()->max('sort_order') ?? 0;

        $goal->keyResults()->create([
            'title' => $this->newKrTitle,
            'metric_type' => $this->newKrMetricType,
            'target_value' => $this->newKrTargetValue,
            'current_value' => $this->newKrStartingValue ?? 0,
            'starting_value' => $this->newKrStartingValue ?? 0,
            'unit' => $this->newKrUnit,
            'due_date' => $this->newKrDueDate,
            'status' => KeyResult::STATUS_NOT_STARTED,
            'sort_order' => $maxSortOrder + 1,
        ]);

        $this->addingKrToGoalId = null;
        $this->resetKrForm();
        $this->plan->refresh();
    }

    public function startEditKr(int $krId)
    {
        $kr = KeyResult::find($krId);
        if (! $kr) {
            return;
        }

        $this->editingKrId = $krId;
        $this->editData = [
            'title' => $kr->title,
            'metric_type' => $kr->metric_type,
            'target_value' => $kr->target_value,
            'current_value' => $kr->current_value,
            'starting_value' => $kr->starting_value,
            'unit' => $kr->unit,
            'due_date' => $kr->due_date?->format('Y-m-d'),
        ];
    }

    public function cancelEditKr()
    {
        $this->editingKrId = null;
        $this->editData = [];
    }

    public function saveKr()
    {
        $kr = KeyResult::find($this->editingKrId);
        if (! $kr) {
            return;
        }

        $this->validate([
            'editData.title' => 'required|string|max:255',
            'editData.target_value' => 'nullable|numeric',
            'editData.current_value' => 'nullable|numeric',
        ]);

        // Use updateValue if current_value changed
        if (isset($this->editData['current_value']) && $this->editData['current_value'] != $kr->current_value) {
            $kr->updateValue($this->editData['current_value'], auth()->id());
        }

        $kr->update([
            'title' => $this->editData['title'],
            'target_value' => $this->editData['target_value'] ?? null,
            'unit' => $this->editData['unit'] ?? null,
            'due_date' => $this->editData['due_date'] ?? null,
        ]);

        $this->editingKrId = null;
        $this->editData = [];
        $this->plan->refresh();
    }

    public function updateKrValue(int $krId, float $value)
    {
        $kr = KeyResult::find($krId);
        if ($kr && $kr->goal->strategic_plan_id === $this->plan->id) {
            $kr->updateValue($value, auth()->id());
            $this->plan->refresh();
        }
    }

    public function deleteKr(int $krId)
    {
        $kr = KeyResult::find($krId);
        if ($kr && $kr->goal->strategic_plan_id === $this->plan->id) {
            $goal = $kr->goal;
            $kr->delete();
            $goal->updateStatusFromKeyResults();
            $this->plan->refresh();
        }
    }

    public function render()
    {
        $goals = $this->plan->goals()
            ->with(['keyResults', 'owner', 'milestones'])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.goal-planner', [
            'goals' => $goals,
        ]);
    }
}
