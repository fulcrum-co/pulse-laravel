<?php

namespace App\Livewire;

use App\Models\Resource;
use Livewire\Component;
use Livewire\WithPagination;

class ContentLibrary extends Component
{
    use WithPagination;

    public string $search = '';
    public array $selectedTypes = [];
    public array $selectedGrades = [];
    public array $selectedCategories = [];
    public array $selectedRiskLevels = [];
    public string $sortBy = 'recent';
    public string $viewMode = 'grid';

    protected $queryString = [
        'search' => ['except' => '', 'as' => 'q'],
        'selectedTypes' => ['except' => [], 'as' => 'type'],
        'selectedGrades' => ['except' => [], 'as' => 'grade'],
        'selectedCategories' => ['except' => [], 'as' => 'category'],
        'selectedRiskLevels' => ['except' => [], 'as' => 'risk'],
        'sortBy' => ['except' => 'recent', 'as' => 'sort'],
        'viewMode' => ['except' => 'grid', 'as' => 'view'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleType(string $type): void
    {
        if (in_array($type, $this->selectedTypes)) {
            $this->selectedTypes = array_values(array_diff($this->selectedTypes, [$type]));
        } else {
            $this->selectedTypes[] = $type;
        }
        $this->resetPage();
    }

    public function toggleGrade(string $grade): void
    {
        if (in_array($grade, $this->selectedGrades)) {
            $this->selectedGrades = array_values(array_diff($this->selectedGrades, [$grade]));
        } else {
            $this->selectedGrades[] = $grade;
        }
        $this->resetPage();
    }

    public function toggleCategory(string $category): void
    {
        if (in_array($category, $this->selectedCategories)) {
            $this->selectedCategories = array_values(array_diff($this->selectedCategories, [$category]));
        } else {
            $this->selectedCategories[] = $category;
        }
        $this->resetPage();
    }

    public function toggleRiskLevel(string $level): void
    {
        if (in_array($level, $this->selectedRiskLevels)) {
            $this->selectedRiskLevels = array_values(array_diff($this->selectedRiskLevels, [$level]));
        } else {
            $this->selectedRiskLevels[] = $level;
        }
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->selectedTypes = [];
        $this->selectedGrades = [];
        $this->selectedCategories = [];
        $this->selectedRiskLevels = [];
        $this->resetPage();
    }

    public function getResourcesProperty()
    {
        $user = auth()->user();

        $query = Resource::forOrganization($user->org_id)
            ->active();

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'ilike', '%' . $this->search . '%')
                  ->orWhere('description', 'ilike', '%' . $this->search . '%');
            });
        }

        // Type filter
        if (count($this->selectedTypes) > 0) {
            $query->whereIn('resource_type', $this->selectedTypes);
        }

        // Category filter
        if (count($this->selectedCategories) > 0) {
            $query->whereIn('category', $this->selectedCategories);
        }

        // Grade filter (JSON array field)
        if (count($this->selectedGrades) > 0) {
            $query->where(function ($q) {
                foreach ($this->selectedGrades as $grade) {
                    $q->orWhereJsonContains('target_grade_levels', $grade);
                }
            });
        }

        // Risk level filter (JSON array field)
        if (count($this->selectedRiskLevels) > 0) {
            $query->where(function ($q) {
                foreach ($this->selectedRiskLevels as $level) {
                    $q->orWhereJsonContains('target_risk_levels', $level);
                }
            });
        }

        // Sorting
        $query = match ($this->sortBy) {
            'title' => $query->orderBy('title'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        return $query->paginate(12);
    }

    public function getTypesProperty(): array
    {
        return [
            'article' => 'Article',
            'video' => 'Video',
            'worksheet' => 'Worksheet',
            'activity' => 'Activity',
            'link' => 'Link',
            'document' => 'Document',
        ];
    }

    public function getGradesProperty(): array
    {
        return [
            'K-2' => 'K-2',
            '3-5' => '3-5',
            '6-8' => '6-8',
            '9-12' => '9-12',
        ];
    }

    public function getCategoriesProperty(): array
    {
        $user = auth()->user();

        return Resource::forOrganization($user->org_id)
            ->active()
            ->distinct()
            ->whereNotNull('category')
            ->pluck('category')
            ->sort()
            ->values()
            ->toArray();
    }

    public function getRiskLevelsProperty(): array
    {
        return [
            'high' => 'High Risk',
            'moderate' => 'Moderate Risk',
            'low' => 'Low Risk',
        ];
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return $this->search !== '' ||
            count($this->selectedTypes) > 0 ||
            count($this->selectedGrades) > 0 ||
            count($this->selectedCategories) > 0 ||
            count($this->selectedRiskLevels) > 0;
    }

    public function render()
    {
        return view('livewire.content-library', [
            'resources' => $this->resources,
        ])->layout('layouts.dashboard', ['title' => 'Content Library', 'hideHeader' => true]);
    }
}
