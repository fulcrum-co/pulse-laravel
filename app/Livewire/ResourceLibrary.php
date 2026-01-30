<?php

namespace App\Livewire;

use App\Models\MiniCourse;
use App\Models\Program;
use App\Models\Provider;
use App\Models\Resource;
use Livewire\Component;
use Livewire\WithPagination;

class ResourceLibrary extends Component
{
    use WithPagination;

    // Active tab
    public string $activeTab = 'all';

    // Search and filters
    public string $search = '';
    public string $filterType = '';
    public string $filterCategory = '';
    public array $filterGrades = [];
    public array $filterTags = [];

    // View mode
    public string $viewMode = 'grid';

    protected $queryString = [
        'activeTab' => ['except' => 'all'],
        'search' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterCategory' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterCategory(): void
    {
        $this->resetPage();
    }

    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetFilters();
        $this->resetPage();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterType = '';
        $this->filterCategory = '';
        $this->filterGrades = [];
        $this->filterTags = [];
    }

    public function getContentResourcesProperty()
    {
        $user = auth()->user();

        $query = Resource::forOrganization($user->org_id)
            ->active()
            ->orderBy('title');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterType) {
            $query->where('resource_type', $this->filterType);
        }

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        return $query->paginate(12);
    }

    public function getProvidersProperty()
    {
        $user = auth()->user();

        $query = Provider::where('org_id', $user->org_id)
            ->active()
            ->orderBy('name');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('bio', 'like', '%' . $this->search . '%')
                  ->orWhereJsonContains('specialty_areas', $this->search);
            });
        }

        if ($this->filterType) {
            $query->where('provider_type', $this->filterType);
        }

        return $query->paginate(12);
    }

    public function getProgramsProperty()
    {
        $user = auth()->user();

        $query = Program::where('org_id', $user->org_id)
            ->active()
            ->orderBy('name');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterType) {
            $query->where('program_type', $this->filterType);
        }

        return $query->paginate(12);
    }

    public function getMiniCoursesProperty()
    {
        $user = auth()->user();

        $query = MiniCourse::where('org_id', $user->org_id)
            ->where('status', MiniCourse::STATUS_ACTIVE)
            ->withCount('steps')
            ->withCount('enrollments')
            ->orderBy('title');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterType) {
            $query->where('course_type', $this->filterType);
        }

        return $query->paginate(12);
    }

    public function getAllItemsProperty()
    {
        // For "All" tab, we'll collect items from each type
        // This is a simplified approach - could be optimized with a union query
        $user = auth()->user();
        $items = collect();

        // Get content resources
        $resources = Resource::forOrganization($user->org_id)
            ->active()
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->limit(6)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'type' => 'resource',
                'title' => $r->title,
                'description' => $r->description,
                'subtitle' => ucfirst($r->resource_type),
                'icon' => $this->getResourceIcon($r->resource_type),
                'meta' => $r->estimated_duration_minutes ? $r->estimated_duration_minutes . ' min' : null,
                'model' => $r,
            ]);

        // Get providers
        $providers = Provider::where('org_id', $user->org_id)
            ->active()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->limit(6)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'type' => 'provider',
                'title' => $p->name,
                'description' => $p->bio,
                'subtitle' => ucfirst($p->provider_type),
                'icon' => 'user',
                'meta' => $p->serves_remote ? 'Remote available' : 'In-person only',
                'model' => $p,
            ]);

        // Get programs
        $programs = Program::where('org_id', $user->org_id)
            ->active()
            ->when($this->search, function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            })
            ->limit(6)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'type' => 'program',
                'title' => $p->name,
                'description' => $p->description,
                'subtitle' => ucfirst(str_replace('_', ' ', $p->program_type)),
                'icon' => 'building',
                'meta' => $p->duration_weeks ? $p->duration_weeks . ' weeks' : null,
                'model' => $p,
            ]);

        // Get mini-courses
        $courses = MiniCourse::where('org_id', $user->org_id)
            ->where('status', MiniCourse::STATUS_ACTIVE)
            ->withCount('steps')
            ->when($this->search, function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%');
            })
            ->limit(6)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'type' => 'course',
                'title' => $c->title,
                'description' => $c->description,
                'subtitle' => ucfirst(str_replace('_', ' ', $c->course_type)),
                'icon' => 'academic-cap',
                'meta' => $c->steps_count . ' steps',
                'model' => $c,
            ]);

        return $items->merge($resources)
            ->merge($providers)
            ->merge($programs)
            ->merge($courses)
            ->sortBy('title')
            ->values();
    }

    public function getResourceTypesProperty(): array
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

    public function getProviderTypesProperty(): array
    {
        return [
            Provider::TYPE_THERAPIST => 'Therapist',
            Provider::TYPE_TUTOR => 'Tutor',
            Provider::TYPE_COACH => 'Coach',
            Provider::TYPE_MENTOR => 'Mentor',
            Provider::TYPE_COUNSELOR => 'Counselor',
            Provider::TYPE_SPECIALIST => 'Specialist',
        ];
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

    public function getCourseTypesProperty(): array
    {
        return [
            MiniCourse::TYPE_INTERVENTION => 'Intervention',
            MiniCourse::TYPE_ENRICHMENT => 'Enrichment',
            MiniCourse::TYPE_SKILL_BUILDING => 'Skill Building',
            MiniCourse::TYPE_WELLNESS => 'Wellness',
            MiniCourse::TYPE_ACADEMIC => 'Academic',
            MiniCourse::TYPE_BEHAVIORAL => 'Behavioral',
        ];
    }

    public function getCategoriesProperty(): array
    {
        $user = auth()->user();

        return Resource::forOrganization($user->org_id)
            ->active()
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values()
            ->toArray();
    }

    public function getCountsProperty(): array
    {
        $user = auth()->user();

        return [
            'resources' => Resource::forOrganization($user->org_id)->active()->count(),
            'providers' => Provider::where('org_id', $user->org_id)->active()->count(),
            'programs' => Program::where('org_id', $user->org_id)->active()->count(),
            'courses' => MiniCourse::where('org_id', $user->org_id)->where('status', MiniCourse::STATUS_ACTIVE)->count(),
        ];
    }

    protected function getResourceIcon(string $type): string
    {
        return match ($type) {
            'article' => 'document-text',
            'video' => 'play-circle',
            'worksheet' => 'clipboard-list',
            'activity' => 'puzzle-piece',
            'link' => 'link',
            'document' => 'document',
            default => 'document',
        };
    }

    public function render()
    {
        return view('livewire.resource-library', [
            'contentResources' => $this->activeTab === 'content' ? $this->contentResources : null,
            'providers' => $this->activeTab === 'providers' ? $this->providers : null,
            'programs' => $this->activeTab === 'programs' ? $this->programs : null,
            'miniCourses' => $this->activeTab === 'courses' ? $this->miniCourses : null,
            'allItems' => $this->activeTab === 'all' ? $this->allItems : null,
            'counts' => $this->counts,
        ])->layout('layouts.app');
    }
}
