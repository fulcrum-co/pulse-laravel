<?php

namespace App\Livewire;

use App\Models\Provider;
use Livewire\Component;
use Livewire\WithPagination;

class ProviderDirectory extends Component
{
    use WithPagination;

    public string $search = '';

    public string $filterType = '';

    public string $filterAvailability = '';

    public string $filterLocation = '';

    public string $viewMode = 'grid';

    protected function term(string $key): string
    {
        return app(\App\Services\TerminologyService::class)->get($key);
    }

    protected $queryString = [
        'search' => ['except' => '', 'as' => 'q'],
        'filterType' => ['except' => '', 'as' => 'type'],
        'filterAvailability' => ['except' => '', 'as' => 'availability'],
        'filterLocation' => ['except' => '', 'as' => 'location'],
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
        $this->filterAvailability = '';
        $this->filterLocation = '';
        $this->resetPage();
    }

    public function getProvidersProperty()
    {
        $user = auth()->user();
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();

        $query = Provider::whereIn('org_id', $accessibleOrgIds)
            ->active();

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%'.$this->search.'%')
                    ->orWhere('bio', 'ilike', '%'.$this->search.'%')
                    ->orWhereRaw('specialty_areas::text ilike ?', ['%'.$this->search.'%']);
            });
        }

        // Type filter
        if ($this->filterType) {
            $query->where('provider_type', $this->filterType);
        }

        // Availability filter (verified status)
        if ($this->filterAvailability === 'verified') {
            $query->whereNotNull('verified_at');
        } elseif ($this->filterAvailability === 'unverified') {
            $query->whereNull('verified_at');
        }

        // Location filter (remote/in-person)
        if ($this->filterLocation === 'remote') {
            $query->where('serves_remote', true);
        } elseif ($this->filterLocation === 'in_person') {
            $query->where('serves_remote', false);
        }

        return $query->orderBy('name')->paginate(15);
    }

    public function getProviderTypesProperty(): array
    {
        return [
            Provider::TYPE_THERAPIST => $this->term('provider_type_therapist_label'),
            Provider::TYPE_TUTOR => $this->term('provider_type_tutor_label'),
            Provider::TYPE_COACH => $this->term('provider_type_coach_label'),
            Provider::TYPE_MENTOR => $this->term('provider_type_mentor_label'),
            Provider::TYPE_COUNSELOR => $this->term('support_person_singular'),
            Provider::TYPE_SPECIALIST => $this->term('provider_type_specialist_label'),
        ];
    }

    public function getAvailabilityOptionsProperty(): array
    {
        return [
            'verified' => $this->term('verified_only_label'),
            'unverified' => $this->term('unverified_only_label'),
        ];
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return $this->search !== '' ||
            $this->filterType !== '' ||
            $this->filterAvailability !== '' ||
            $this->filterLocation !== '';
    }

    public function render()
    {
        return view('livewire.provider-directory', [
            'providers' => $this->providers,
        ])->layout('layouts.dashboard', ['title' => $this->term('provider_directory_label'), 'hideHeader' => true]);
    }
}
