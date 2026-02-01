<?php

namespace App\Livewire;

use App\Models\Program;
use Livewire\Component;
use Livewire\WithPagination;

class ProgramCatalog extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';
    public string $filterLocation = '';
    public string $filterCost = '';
    public bool $showActiveOnly = true;
    public string $viewMode = 'grid';

    protected $queryString = [
        'search' => ['except' => '', 'as' => 'q'],
        'filterType' => ['except' => '', 'as' => 'type'],
        'filterLocation' => ['except' => '', 'as' => 'location'],
        'filterCost' => ['except' => '', 'as' => 'cost'],
        'showActiveOnly' => ['except' => true, 'as' => 'active'],
        'viewMode' => ['except' => 'grid', 'as' => 'view'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->filterType = '';
        $this->filterLocation = '';
        $this->filterCost = '';
        $this->showActiveOnly = true;
        $this->resetPage();
    }

    public function getProgramsProperty()
    {
        $user = auth()->user();
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();

        $query = Program::whereIn('org_id', $accessibleOrgIds);

        if ($this->showActiveOnly) {
            $query->active();
        }

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%' . $this->search . '%')
                  ->orWhere('description', 'ilike', '%' . $this->search . '%');
            });
        }

        // Type filter
        if ($this->filterType) {
            $query->where('program_type', $this->filterType);
        }

        // Location filter
        if ($this->filterLocation === 'virtual') {
            $query->where('location_type', 'virtual');
        } elseif ($this->filterLocation === 'in_person') {
            $query->where('location_type', 'in_person');
        } elseif ($this->filterLocation === 'hybrid') {
            $query->where('location_type', 'hybrid');
        }

        // Cost filter
        if ($this->filterCost === 'free') {
            $query->where(function ($q) {
                $q->whereNull('cost_amount')->orWhere('cost_amount', 0);
            });
        } elseif ($this->filterCost === 'paid') {
            $query->where('cost_amount', '>', 0);
        }

        return $query->orderBy('name')->paginate(12);
    }

    public function getProgramTypesProperty(): array
    {
        return [
            Program::TYPE_THERAPY => 'Therapy',
            Program::TYPE_TUTORING => 'Tutoring',
            Program::TYPE_MENTORSHIP => 'Mentorship',
            Program::TYPE_ENRICHMENT => 'Enrichment',
            Program::TYPE_INTERVENTION => 'Intervention',
            Program::TYPE_SUPPORT_GROUP => 'Support Group',
            Program::TYPE_EXTERNAL_SERVICE => 'External Service',
        ];
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return $this->search !== '' ||
            $this->filterType !== '' ||
            $this->filterLocation !== '' ||
            $this->filterCost !== '' ||
            !$this->showActiveOnly;
    }

    public function render()
    {
        return view('livewire.program-catalog', [
            'programs' => $this->programs,
        ])->layout('layouts.dashboard', ['title' => 'Program Catalog', 'hideHeader' => true]);
    }
}
