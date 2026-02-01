<?php

namespace App\Livewire;

use App\Models\ProgressSummary;
use App\Models\ProgressUpdate;
use App\Models\StrategicPlan;
use App\Services\PlanProgressService;
use Livewire\Component;
use Livewire\WithPagination;

class ProgressUpdates extends Component
{
    use WithPagination;

    public StrategicPlan $plan;

    public string $newUpdateContent = '';

    public ?int $selectedGoalId = null;

    public bool $showAddForm = false;

    public bool $generatingSummary = false;

    public ?ProgressSummary $latestSummary = null;

    protected $listeners = ['updateAdded' => '$refresh'];

    public function mount(StrategicPlan $plan)
    {
        $this->plan = $plan;
        $this->latestSummary = $plan->progressSummaries()->latest()->first();
    }

    public function showForm()
    {
        $this->showAddForm = true;
    }

    public function cancelForm()
    {
        $this->showAddForm = false;
        $this->newUpdateContent = '';
        $this->selectedGoalId = null;
    }

    public function addUpdate()
    {
        $this->validate([
            'newUpdateContent' => 'required|string|max:5000',
            'selectedGoalId' => 'nullable|exists:goals,id',
        ]);

        ProgressUpdate::create([
            'strategic_plan_id' => $this->plan->id,
            'goal_id' => $this->selectedGoalId,
            'content' => $this->newUpdateContent,
            'update_type' => ProgressUpdate::TYPE_MANUAL,
            'created_by' => auth()->id(),
        ]);

        $this->showAddForm = false;
        $this->newUpdateContent = '';
        $this->selectedGoalId = null;
        $this->resetPage();
    }

    public function generateSummary()
    {
        $this->generatingSummary = true;

        $service = app(PlanProgressService::class);
        $summary = $service->generateProgressSummary($this->plan, ProgressSummary::PERIOD_WEEKLY);

        $this->latestSummary = $summary;
        $this->generatingSummary = false;
    }

    public function render()
    {
        $updates = $this->plan->progressUpdates()
            ->with(['creator', 'goal', 'keyResult', 'milestone'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $goals = $this->plan->allGoals()->orderBy('title')->get();

        return view('livewire.progress-updates', [
            'updates' => $updates,
            'goals' => $goals,
        ]);
    }
}
