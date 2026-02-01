<?php

namespace App\Livewire;

use App\Models\Milestone;
use App\Models\ProgressUpdate;
use App\Models\StrategicPlan;
use Livewire\Component;

class MilestoneTracker extends Component
{
    public StrategicPlan $plan;

    public bool $showAddForm = false;

    public string $newMilestoneTitle = '';

    public ?string $newMilestoneDescription = null;

    public ?string $newMilestoneDueDate = null;

    public ?int $newMilestoneGoalId = null;

    public ?int $editingMilestoneId = null;

    public array $editData = [];

    protected $listeners = ['milestoneUpdated' => '$refresh'];

    public function mount(StrategicPlan $plan)
    {
        $this->plan = $plan;
    }

    public function showForm()
    {
        $this->showAddForm = true;
        $this->resetForm();
    }

    public function cancelForm()
    {
        $this->showAddForm = false;
        $this->resetForm();
    }

    protected function resetForm()
    {
        $this->newMilestoneTitle = '';
        $this->newMilestoneDescription = null;
        $this->newMilestoneDueDate = null;
        $this->newMilestoneGoalId = null;
    }

    public function addMilestone()
    {
        $this->validate([
            'newMilestoneTitle' => 'required|string|max:255',
            'newMilestoneDescription' => 'nullable|string',
            'newMilestoneDueDate' => 'required|date',
            'newMilestoneGoalId' => 'nullable|exists:goals,id',
        ]);

        $maxSortOrder = $this->plan->milestones()->max('sort_order') ?? 0;

        $this->plan->milestones()->create([
            'title' => $this->newMilestoneTitle,
            'description' => $this->newMilestoneDescription,
            'due_date' => $this->newMilestoneDueDate,
            'goal_id' => $this->newMilestoneGoalId,
            'status' => Milestone::STATUS_PENDING,
            'sort_order' => $maxSortOrder + 1,
        ]);

        $this->showAddForm = false;
        $this->resetForm();
        $this->plan->refresh();
    }

    public function startEdit(int $milestoneId)
    {
        $milestone = Milestone::find($milestoneId);
        if (! $milestone) {
            return;
        }

        $this->editingMilestoneId = $milestoneId;
        $this->editData = [
            'title' => $milestone->title,
            'description' => $milestone->description,
            'due_date' => $milestone->due_date->format('Y-m-d'),
            'goal_id' => $milestone->goal_id,
        ];
    }

    public function cancelEdit()
    {
        $this->editingMilestoneId = null;
        $this->editData = [];
    }

    public function saveMilestone()
    {
        $this->validate([
            'editData.title' => 'required|string|max:255',
            'editData.due_date' => 'required|date',
        ]);

        $milestone = Milestone::find($this->editingMilestoneId);
        if ($milestone) {
            $milestone->update([
                'title' => $this->editData['title'],
                'description' => $this->editData['description'] ?? null,
                'due_date' => $this->editData['due_date'],
                'goal_id' => $this->editData['goal_id'] ?? null,
            ]);
        }

        $this->editingMilestoneId = null;
        $this->editData = [];
        $this->plan->refresh();
    }

    public function markComplete(int $milestoneId)
    {
        $milestone = Milestone::find($milestoneId);
        if ($milestone && $milestone->strategic_plan_id === $this->plan->id) {
            $milestone->markComplete(auth()->id());

            // Create a system progress update
            ProgressUpdate::create([
                'strategic_plan_id' => $this->plan->id,
                'milestone_id' => $milestone->id,
                'goal_id' => $milestone->goal_id,
                'content' => "Milestone completed: {$milestone->title}",
                'update_type' => ProgressUpdate::TYPE_SYSTEM,
                'status_change' => 'completed',
                'created_by' => auth()->id(),
            ]);

            $this->plan->refresh();
        }
    }

    public function markInProgress(int $milestoneId)
    {
        $milestone = Milestone::find($milestoneId);
        if ($milestone && $milestone->strategic_plan_id === $this->plan->id) {
            $milestone->update(['status' => Milestone::STATUS_IN_PROGRESS]);
            $this->plan->refresh();
        }
    }

    public function deleteMilestone(int $milestoneId)
    {
        $milestone = Milestone::find($milestoneId);
        if ($milestone && $milestone->strategic_plan_id === $this->plan->id) {
            $milestone->delete();
            $this->plan->refresh();
        }
    }

    public function render()
    {
        $milestones = $this->plan->milestones()
            ->with(['goal', 'completedByUser'])
            ->orderBy('due_date')
            ->get();

        $goals = $this->plan->allGoals()->orderBy('title')->get();

        // Group milestones
        $overdue = $milestones->filter(fn ($m) => $m->isOverdue());
        $upcoming = $milestones->filter(fn ($m) => ! $m->isOverdue() && $m->status !== Milestone::STATUS_COMPLETED);
        $completed = $milestones->filter(fn ($m) => $m->status === Milestone::STATUS_COMPLETED);

        return view('livewire.milestone-tracker', [
            'milestones' => $milestones,
            'goals' => $goals,
            'overdue' => $overdue,
            'upcoming' => $upcoming,
            'completed' => $completed,
        ]);
    }
}
