<?php

namespace App\Livewire;

use App\Models\MiniCourse;
use Livewire\Component;
use Livewire\WithPagination;

class LearningCenter extends Component
{
    use WithPagination;

    public string $search = '';
    public string $activeCategory = 'all';
    public string $viewMode = 'grid';

    protected $queryString = [
        'search' => ['except' => '', 'as' => 'q'],
        'activeCategory' => ['except' => 'all', 'as' => 'category'],
        'viewMode' => ['except' => 'grid', 'as' => 'view'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function setCategory(string $category): void
    {
        $this->activeCategory = $category;
        $this->resetPage();
    }

    public function getCoursesProperty()
    {
        $user = auth()->user();

        $query = MiniCourse::where('org_id', $user->org_id)
            ->where('status', MiniCourse::STATUS_ACTIVE)
            ->withCount('steps')
            ->withCount('enrollments');

        // Search
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'ilike', '%' . $this->search . '%')
                  ->orWhere('description', 'ilike', '%' . $this->search . '%');
            });
        }

        // Category filter
        if ($this->activeCategory !== 'all') {
            $query->where('course_type', $this->activeCategory);
        }

        return $query->orderBy('created_at', 'desc')->paginate(12);
    }

    public function getCategoriesProperty(): array
    {
        return [
            'all' => 'All',
            MiniCourse::TYPE_WELLNESS => 'Wellness',
            MiniCourse::TYPE_ACADEMIC => 'Academic',
            MiniCourse::TYPE_BEHAVIORAL => 'Behavioral',
            MiniCourse::TYPE_SKILL_BUILDING => 'Skill Building',
            MiniCourse::TYPE_INTERVENTION => 'Intervention',
            MiniCourse::TYPE_ENRICHMENT => 'Enrichment',
        ];
    }

    public function getCategoryCountsProperty(): array
    {
        $user = auth()->user();
        $counts = ['all' => 0];

        $results = MiniCourse::where('org_id', $user->org_id)
            ->where('status', MiniCourse::STATUS_ACTIVE)
            ->selectRaw('course_type, count(*) as count')
            ->groupBy('course_type')
            ->pluck('count', 'course_type')
            ->toArray();

        foreach ($this->categories as $key => $label) {
            if ($key === 'all') {
                $counts['all'] = array_sum($results);
            } else {
                $counts[$key] = $results[$key] ?? 0;
            }
        }

        return $counts;
    }

    public function render()
    {
        return view('livewire.learning-center', [
            'courses' => $this->courses,
        ])->layout('layouts.dashboard', ['title' => 'Learning Center', 'hideHeader' => true]);
    }
}
