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

    protected $queryString = [
        'search' => ['except' => ''],
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

    /**
     * Get counts for each section card.
     */
    public function getCountsProperty(): array
    {
        $user = auth()->user();

        return [
            'content' => Resource::forOrganization($user->org_id)->active()->count(),
            'providers' => Provider::where('org_id', $user->org_id)->active()->count(),
            'programs' => Program::where('org_id', $user->org_id)->active()->count(),
            'courses' => MiniCourse::where('org_id', $user->org_id)->where('status', MiniCourse::STATUS_ACTIVE)->count(),
        ];
    }

    /**
     * Get recently added items across all categories.
     */
    public function getRecentItemsProperty(): \Illuminate\Support\Collection
    {
        $user = auth()->user();

        // Get 2 recent items from each category
        $content = Resource::forOrganization($user->org_id)
            ->active()
            ->latest('created_at')
            ->limit(2)
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

        $providers = Provider::where('org_id', $user->org_id)
            ->active()
            ->latest('created_at')
            ->limit(2)
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

        $programs = Program::where('org_id', $user->org_id)
            ->active()
            ->latest('created_at')
            ->limit(2)
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

        $courses = MiniCourse::where('org_id', $user->org_id)
            ->where('status', MiniCourse::STATUS_ACTIVE)
            ->withCount('steps')
            ->latest('created_at')
            ->limit(2)
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'type' => 'course',
                'title' => $c->title,
                'description' => $c->description,
                'subtitle' => $c->steps_count . ' steps',
                'icon' => 'academic-cap',
                'icon_bg' => 'orange',
                'url' => route('resources.courses.show', $c),
                'created_at' => $c->created_at,
            ]);

        return $content->concat($providers)->concat($programs)->concat($courses)
            ->sortByDesc('created_at')
            ->take(8)
            ->values();
    }

    /**
     * Get search results grouped by type.
     */
    public function getSearchResultsProperty(): array
    {
        if (!$this->isSearching) {
            return [];
        }

        $user = auth()->user();
        $searchTerm = '%' . $this->search . '%';

        // Search content resources
        $content = Resource::forOrganization($user->org_id)
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
        $providers = Provider::where('org_id', $user->org_id)
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
        $programs = Program::where('org_id', $user->org_id)
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
                'meta' => $p->duration_weeks ? $p->duration_weeks . ' weeks' : null,
                'url' => route('resources.programs.show', $p),
            ]);

        // Search courses
        $courses = MiniCourse::where('org_id', $user->org_id)
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
                'meta' => $c->steps_count . ' steps',
                'url' => route('resources.courses.show', $c),
            ]);

        return [
            'content' => [
                'items' => $content,
                'count' => $content->count(),
                'total' => Resource::forOrganization($user->org_id)
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
                'total' => Provider::where('org_id', $user->org_id)
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
                'total' => Program::where('org_id', $user->org_id)
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
                'total' => MiniCourse::where('org_id', $user->org_id)
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
        ])->layout('layouts.dashboard', ['title' => 'Resources']);
    }
}
