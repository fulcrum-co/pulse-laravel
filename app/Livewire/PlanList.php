<?php

namespace App\Livewire;

use App\Models\StrategicPlan;
use Livewire\Component;
use Livewire\WithPagination;

class PlanList extends Component
{
    use WithPagination;

    public $search = '';

    public $typeFilter = 'all';

    public $statusFilter = '';

    public string $viewMode = 'grid';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
    ];

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingTypeFilter()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function setTypeFilter($type)
    {
        $this->typeFilter = $type;
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->typeFilter = 'all';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();

        $query = StrategicPlan::where('org_id', $user->org_id)
            ->with(['focusAreas', 'goals', 'collaborators.user', 'creator']);

        // Apply type filter
        if ($this->typeFilter !== 'all') {
            $query->where('plan_type', $this->typeFilter);
        }

        // Apply search
        if ($this->search) {
            $query->where('title', 'like', '%'.$this->search.'%');
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $plans = $query->orderBy('created_at', 'desc')->paginate(12);

        // Get counts for dropdown
        $counts = [
            'all' => StrategicPlan::where('org_id', $user->org_id)->count(),
            'organizational' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'organizational')->count(),
            'instructor' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'instructor')->count(),
            'participant' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'participant')->count(),
            'department' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'department')->count(),
            'level' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'level')->count(),
            'improvement' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'improvement')->count(),
            'growth' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'growth')->count(),
            'strategic' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'strategic')->count(),
            'action' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'action')->count(),
        ];

        return view('livewire.plan-list', [
            'plans' => $plans,
            'counts' => $counts,
        ]);
    }
}
