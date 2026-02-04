<?php

namespace App\Livewire;

use App\Models\StrategyDriftScore;
use App\Models\StrategicPlan;
use App\Services\StrategyDriftService;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class PlanList extends Component
{
    use WithPagination;

    public string $activeTab = 'plans';

    public $search = '';

    public $typeFilter = 'all';

    public $statusFilter = '';

    public string $viewMode = 'grid';

    // Alignment tab properties
    public string $alignmentTimeRange = '30';

    public string $alignmentFilterLevel = 'all';

    protected $queryString = [
        'activeTab' => ['as' => 'tab', 'except' => 'plans'],
        'search' => ['except' => ''],
        'typeFilter' => ['except' => 'all'],
        'statusFilter' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
        'alignmentTimeRange' => ['as' => 'range', 'except' => '30'],
        'alignmentFilterLevel' => ['as' => 'level', 'except' => 'all'],
    ];

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

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

    public function setAlignmentTimeRange(string $range): void
    {
        $this->alignmentTimeRange = $range;
        $this->resetPage();
    }

    public function setAlignmentFilterLevel(string $level): void
    {
        $this->alignmentFilterLevel = $level;
        $this->resetPage();
    }

    public function getAlignmentSummaryProperty(): array
    {
        $orgId = auth()->user()->org_id;

        return app(StrategyDriftService::class)
            ->getOrgDriftSummary($orgId, (int) $this->alignmentTimeRange);
    }

    public function getAlignmentScoresProperty()
    {
        $orgId = auth()->user()->org_id;

        try {
            return StrategyDriftScore::forOrg($orgId)
                ->with(['contactNote.contact', 'contactNote.author'])
                ->when($this->alignmentFilterLevel !== 'all', fn ($q) => $q->where('alignment_level', $this->alignmentFilterLevel))
                ->recent((int) $this->alignmentTimeRange)
                ->orderBy('scored_at', 'desc')
                ->paginate(20);
        } catch (\Illuminate\Database\QueryException $e) {
            return new LengthAwarePaginator([], 0, 20);
        }
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
            'teacher' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'teacher')->count(),
            'student' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'student')->count(),
            'department' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'department')->count(),
            'grade' => StrategicPlan::where('org_id', $user->org_id)->where('plan_type', 'grade')->count(),
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
