<?php

namespace App\Livewire;

use App\Models\MiniCourse;
use App\Models\Program;
use App\Models\Provider;
use App\Models\Resource;
use Livewire\Component;

class ResourceHub extends Component
{
    public string $search = '';

    public bool $isSearching = false;

    public array $selectedCategories = [];

    public array $selectedContentTypes = [];

    public string $sortBy = 'recent';

    public string $viewMode = 'grid';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedCategories' => ['except' => [], 'as' => 'category'],
        'selectedContentTypes' => ['except' => [], 'as' => 'type'],
        'sortBy' => ['except' => 'recent', 'as' => 'sort'],
        'viewMode' => ['except' => 'grid', 'as' => 'view'],
    ];

    public function updatedSearch(): void
    {
        $this->isSearching = strlen($this->search) >= 2;
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->isSearching = false;
    }

    public function toggleCategory(string $category): void
    {
        if (in_array($category, $this->selectedCategories)) {
            $this->selectedCategories = array_values(array_diff($this->selectedCategories, [$category]));
        } else {
            $this->selectedCategories[] = $category;
        }
        // Clear content types if content category is deselected
        if ($category === 'content' && ! in_array('content', $this->selectedCategories)) {
            $this->selectedContentTypes = [];
        }
    }

    public function toggleContentType(string $type): void
    {
        if (in_array($type, $this->selectedContentTypes)) {
            $this->selectedContentTypes = array_values(array_diff($this->selectedContentTypes, [$type]));
        } else {
            $this->selectedContentTypes[] = $type;
        }
    }

    public function clearFilters(): void
    {
        $this->selectedCategories = [];
        $this->selectedContentTypes = [];
        $this->sortBy = 'recent';
    }

    public function selectAllCategories(): void
    {
        $this->selectedCategories = ['content', 'provider', 'program', 'course'];
    }

    public function clearCategories(): void
    {
        $this->selectedCategories = [];
        $this->selectedContentTypes = [];
    }

    public function selectAllContentTypes(): void
    {
        $this->selectedContentTypes = array_keys($this->contentTypes);
    }

    public function clearContentTypes(): void
    {
        $this->selectedContentTypes = [];
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return count($this->selectedCategories) > 0
            || count($this->selectedContentTypes) > 0
            || $this->sortBy !== 'recent';
    }

    public function getContentTypesProperty(): array
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

    /**
     * Get counts for each section card.
     */
    public function getCountsProperty(): array
    {
        $user = auth()->user();
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();

        return [
            'content' => Resource::whereIn('org_id', $accessibleOrgIds)->active()->count(),
            'providers' => Provider::whereIn('org_id', $accessibleOrgIds)->active()->count(),
            'programs' => Program::whereIn('org_id', $accessibleOrgIds)->active()->count(),
            'courses' => MiniCourse::whereIn('org_id', $accessibleOrgIds)->where('status', MiniCourse::STATUS_ACTIVE)->count(),
        ];
    }

    /**
     * Get recently added items across all categories (with filtering).
     */
    public function getRecentItemsProperty(): \Illuminate\Support\Collection
    {
        $user = auth()->user();
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();

        $items = collect();
        $hasFilters = count($this->selectedCategories) > 0;

        // Get content items
        if (! $hasFilters || in_array('content', $this->selectedCategories)) {
            $contentQuery = Resource::whereIn('org_id', $accessibleOrgIds)->active();

            // Apply content type filter
            if (count($this->selectedContentTypes) > 0) {
                $contentQuery->whereIn('resource_type', $this->selectedContentTypes);
            }

            $content = $contentQuery->latest('created_at')
                ->limit(8)
                ->get()
                ->map(fn ($r) => [
                    'id' => $r->id,
                    'type' => 'content',
                    'title' => $r->title,
                    'description' => $r->description,
                    'subtitle' => ucfirst($r->resource_type),
                    'icon' => $this->getResourceIcon($r->resource_type),
                    'icon_bg' => 'blue',
                    'url' => route('resources.show', $r),
                    'created_at' => $r->created_at,
                ]);
            $items = $items->concat($content);
        }

        // Get providers
        if (! $hasFilters || in_array('provider', $this->selectedCategories)) {
            $providers = Provider::whereIn('org_id', $accessibleOrgIds)
                ->active()
                ->latest('created_at')
                ->limit(8)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'type' => 'provider',
                    'title' => $p->name,
                    'description' => $p->bio,
                    'subtitle' => ucfirst($p->provider_type),
                    'icon' => 'user',
                    'icon_bg' => 'purple',
                    'url' => route('resources.providers.show', $p),
                    'created_at' => $p->created_at,
                ]);
            $items = $items->concat($providers);
        }

        // Get programs
        if (! $hasFilters || in_array('program', $this->selectedCategories)) {
            $programs = Program::whereIn('org_id', $accessibleOrgIds)
                ->active()
                ->latest('created_at')
                ->limit(8)
                ->get()
                ->map(fn ($p) => [
                    'id' => $p->id,
                    'type' => 'program',
                    'title' => $p->name,
                    'description' => $p->description,
                    'subtitle' => ucfirst(str_replace('_', ' ', $p->program_type)),
                    'icon' => 'building-office',
                    'icon_bg' => 'green',
                    'url' => route('resources.programs.show', $p),
                    'created_at' => $p->created_at,
                ]);
            $items = $items->concat($programs);
        }

        // Get courses
        if (! $hasFilters || in_array('course', $this->selectedCategories)) {
            $courses = MiniCourse::whereIn('org_id', $accessibleOrgIds)
                ->where('status', MiniCourse::STATUS_ACTIVE)
                ->withCount('steps')
                ->latest('created_at')
                ->limit(8)
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'type' => 'course',
                    'title' => $c->title,
                    'description' => $c->description,
                    'subtitle' => $c->steps_count.' steps',
                    'icon' => 'academic-cap',
                    'icon_bg' => 'orange',
                    'url' => route('resources.courses.show', $c),
                    'created_at' => $c->created_at,
                ]);
            $items = $items->concat($courses);
        }

        // Apply sorting
        if ($this->sortBy === 'alphabetical') {
            $items = $items->sortBy('title');
        } else {
            $items = $items->sortByDesc('created_at');
        }

        return $items->take(12)->values();
    }

    /**
     * Get search results grouped by type.
     */
    public function getSearchResultsProperty(): array
    {
        if (! $this->isSearching) {
            return [];
        }

        $user = auth()->user();
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();
        $searchTerm = '%'.$this->search.'%';

        // Search content resources
        $content = Resource::whereIn('org_id', $accessibleOrgIds)
            ->active()
            ->where(function ($q) use ($searchTerm) {
                $q->where('title', 'ilike', $searchTerm)
                    ->orWhere('description', 'ilike', $searchTerm);
            })
            ->limit(4)
            ->get()
            ->map(fn ($r) => [
                'id' => $r->id,
                'type' => 'content',
                'title' => $r->title,
                'description' => $r->description,
                'subtitle' => ucfirst($r->resource_type),
                'icon' => $this->getResourceIcon($r->resource_type),
                'url' => route('resources.show', $r),
            ]);

        // Search providers
        $providers = Provider::whereIn('org_id', $accessibleOrgIds)
            ->active()
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ilike', $searchTerm)
                    ->orWhere('bio', 'ilike', $searchTerm);
            })
            ->limit(4)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'type' => 'provider',
                'title' => $p->name,
                'description' => $p->bio,
                'subtitle' => ucfirst($p->provider_type),
                'availability' => $p->availability_status ?? 'unknown',
                'serves_remote' => $p->serves_remote,
                'url' => route('resources.providers.show', $p),
            ]);

        // Search programs
        $programs = Program::whereIn('org_id', $accessibleOrgIds)
            ->active()
            ->where(function ($q) use ($searchTerm) {
                $q->where('name', 'ilike', $searchTerm)
                    ->orWhere('description', 'ilike', $searchTerm);
            })
            ->limit(4)
            ->get()
            ->map(fn ($p) => [
                'id' => $p->id,
                'type' => 'program',
                'title' => $p->name,
                'description' => $p->description,
                'subtitle' => ucfirst(str_replace('_', ' ', $p->program_type)),
                'meta' => $p->duration_weeks ? $p->duration_weeks.' weeks' : null,
                'url' => route('resources.programs.show', $p),
            ]);

        // Search courses
        $courses = MiniCourse::whereIn('org_id', $accessibleOrgIds)
            ->where('status', MiniCourse::STATUS_ACTIVE)
            ->withCount('steps')
            ->where(function ($q) use ($searchTerm) {
                $q->where('title', 'ilike', $searchTerm)
                    ->orWhere('description', 'ilike', $searchTerm);
            })
            ->limit(4)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'type' => 'course',
                'title' => $c->title,
                'description' => $c->description,
                'subtitle' => ucfirst(str_replace('_', ' ', $c->course_type)),
                'meta' => $c->steps_count.' steps',
                'url' => route('resources.courses.show', $c),
            ]);

        return [
            'content' => [
                'items' => $content,
                'count' => $content->count(),
                'total' => Resource::whereIn('org_id', $accessibleOrgIds)
                    ->active()
                    ->where(function ($q) use ($searchTerm) {
                        $q->where('title', 'ilike', $searchTerm)
                            ->orWhere('description', 'ilike', $searchTerm);
                    })
                    ->count(),
            ],
            'providers' => [
                'items' => $providers,
                'count' => $providers->count(),
                'total' => Provider::whereIn('org_id', $accessibleOrgIds)
                    ->active()
                    ->where(function ($q) use ($searchTerm) {
                        $q->where('name', 'ilike', $searchTerm)
                            ->orWhere('bio', 'ilike', $searchTerm);
                    })
                    ->count(),
            ],
            'programs' => [
                'items' => $programs,
                'count' => $programs->count(),
                'total' => Program::whereIn('org_id', $accessibleOrgIds)
                    ->active()
                    ->where(function ($q) use ($searchTerm) {
                        $q->where('name', 'ilike', $searchTerm)
                            ->orWhere('description', 'ilike', $searchTerm);
                    })
                    ->count(),
            ],
            'courses' => [
                'items' => $courses,
                'count' => $courses->count(),
                'total' => MiniCourse::whereIn('org_id', $accessibleOrgIds)
                    ->where('status', MiniCourse::STATUS_ACTIVE)
                    ->where(function ($q) use ($searchTerm) {
                        $q->where('title', 'ilike', $searchTerm)
                            ->orWhere('description', 'ilike', $searchTerm);
                    })
                    ->count(),
            ],
        ];
    }

    protected function getResourceIcon(string $type): string
    {
        return match ($type) {
            'article' => 'document-text',
            'video' => 'play-circle',
            'worksheet' => 'clipboard-document-list',
            'activity' => 'puzzle-piece',
            'link' => 'link',
            'document' => 'document',
            default => 'document',
        };
    }

    public function render()
    {
        return view('livewire.resource-hub', [
            'counts' => $this->counts,
            'searchResults' => $this->searchResults,
            'recentItems' => $this->recentItems,
            'hasActiveFilters' => $this->hasActiveFilters,
            'contentTypes' => $this->contentTypes,
        ])->layout('layouts.dashboard', ['title' => 'Resources']);
    }
}
