<?php

namespace App\Livewire;

use App\Models\StrategicPlan;
use Livewire\Component;
use Livewire\WithPagination;

class StrategyList extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = 'all';
    public $statusFilter = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'typeFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => ''],
    ];

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
            ->with(['focusAreas', 'collaborators.user', 'creator']);

        // Apply type filter
        if ($this->typeFilter !== 'all') {
            $query->where('plan_type', $this->typeFilter);
        }

        // Apply search
        if ($this->search) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        // Apply status filter
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        $strategies = $query->orderBy('created_at', 'desc')->paginate(12);

        // Get counts for tabs
        $counts = [
            'all' => StrategicPlan::where('org_id', $user->org_id)->count(),
            'organizational' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'organizational')->count(),
            'teacher' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'teacher')->count(),
            'student' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'student')->count(),
            'department' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'department')->count(),
            'grade' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'grade')->count(),
        ];

        return view('livewire.strategy-list', [
            'strategies' => $strategies,
            'counts' => $counts,
        ]);
    }
}
