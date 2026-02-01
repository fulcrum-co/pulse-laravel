<?php

namespace App\Livewire;

use App\Models\ContentModerationResult;
use App\Models\MiniCourse;
use App\Models\Program;
use App\Models\Provider;
use App\Models\Resource;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;

class ResourceLibrary extends Component
{
    use WithPagination;
    use WithFileUploads;

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

    // Add Resource Modal
    public bool $showAddModal = false;
    public string $addResourceType = 'resource'; // resource, provider, program

    // Resource form fields
    public string $resourceTitle = '';
    public string $resourceDescription = '';
    public string $resourceTypeField = 'article';
    public string $resourceCategory = '';
    public string $resourceUrl = '';
    public ?int $resourceDuration = null;
    public $resourceFile = null;

    // Provider form fields
    public string $providerName = '';
    public string $providerBio = '';
    public string $providerTypeField = 'therapist';
    public string $providerEmail = '';
    public string $providerPhone = '';
    public bool $providerServesRemote = false;

    // Program form fields
    public string $programName = '';
    public string $programDescription = '';
    public string $programTypeField = 'therapy';
    public ?int $programDurationWeeks = null;
    public ?int $programCapacity = null;

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

    // ============================================
    // ADD RESOURCE MODAL
    // ============================================

    #[On('openAddResourceModal')]
    public function openAddModal(): void
    {
        $this->resetAddForm();
        $this->showAddModal = true;
    }

    public function closeAddModal(): void
    {
        $this->showAddModal = false;
        $this->resetAddForm();
    }

    public function setAddResourceType(string $type): void
    {
        $this->addResourceType = $type;
    }

    protected function resetAddForm(): void
    {
        $this->addResourceType = 'resource';
        $this->resourceTitle = '';
        $this->resourceDescription = '';
        $this->resourceTypeField = 'article';
        $this->resourceCategory = '';
        $this->resourceUrl = '';
        $this->resourceDuration = null;
        $this->resourceFile = null;
        $this->providerName = '';
        $this->providerBio = '';
        $this->providerTypeField = 'therapist';
        $this->providerEmail = '';
        $this->providerPhone = '';
        $this->providerServesRemote = false;
        $this->programName = '';
        $this->programDescription = '';
        $this->programTypeField = 'therapy';
        $this->programDurationWeeks = null;
        $this->programCapacity = null;
    }

    public function saveResource(): void
    {
        $user = auth()->user();

        if ($this->addResourceType === 'resource') {
            $this->validate([
                'resourceTitle' => 'required|string|max:255',
                'resourceTypeField' => 'required|string',
                'resourceFile' => 'nullable|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,mp4,mp3,wav,jpg,jpeg,png,gif',
            ]);

            // Handle file upload
            $filePath = null;
            if ($this->resourceFile) {
                $filePath = $this->resourceFile->store(
                    'resources/' . $user->org_id,
                    'public'
                );
            }

            Resource::create([
                'org_id' => $user->org_id,
                'title' => $this->resourceTitle,
                'description' => $this->resourceDescription,
                'resource_type' => $this->resourceTypeField,
                'category' => $this->resourceCategory ?: null,
                'url' => $this->resourceUrl ?: null,
                'file_path' => $filePath,
                'estimated_duration_minutes' => $this->resourceDuration,
                'active' => true,
                'created_by' => $user->id,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Resource created successfully.',
            ]);

        } elseif ($this->addResourceType === 'provider') {
            $this->validate([
                'providerName' => 'required|string|max:255',
                'providerTypeField' => 'required|string',
            ]);

            Provider::create([
                'org_id' => $user->org_id,
                'name' => $this->providerName,
                'bio' => $this->providerBio ?: null,
                'provider_type' => $this->providerTypeField,
                'contact_email' => $this->providerEmail ?: null,
                'contact_phone' => $this->providerPhone ?: null,
                'serves_remote' => $this->providerServesRemote,
                'active' => true,
                'created_by' => $user->id,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Provider created successfully.',
            ]);

        } elseif ($this->addResourceType === 'program') {
            $this->validate([
                'programName' => 'required|string|max:255',
                'programTypeField' => 'required|string',
            ]);

            Program::create([
                'org_id' => $user->org_id,
                'name' => $this->programName,
                'description' => $this->programDescription ?: null,
                'program_type' => $this->programTypeField,
                'duration_weeks' => $this->programDurationWeeks,
                'capacity' => $this->programCapacity,
                'active' => true,
                'created_by' => $user->id,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Program created successfully.',
            ]);
        }

        $this->closeAddModal();
    }

    public function getContentResourcesProperty()
    {
        $user = auth()->user();
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();

        $query = Resource::whereIn('org_id', $accessibleOrgIds)
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
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();

        $query = Provider::whereIn('org_id', $accessibleOrgIds)
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
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();

        $query = Program::whereIn('org_id', $accessibleOrgIds)
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
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();

        $query = MiniCourse::whereIn('org_id', $accessibleOrgIds)
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
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();
        $items = collect();

        // Get content resources
        $resources = Resource::whereIn('org_id', $accessibleOrgIds)
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
        $providers = Provider::whereIn('org_id', $accessibleOrgIds)
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
        $programs = Program::whereIn('org_id', $accessibleOrgIds)
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
        $courses = MiniCourse::whereIn('org_id', $accessibleOrgIds)
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
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();

        return Resource::whereIn('org_id', $accessibleOrgIds)
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
        $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();

        return [
            'resources' => Resource::whereIn('org_id', $accessibleOrgIds)->active()->count(),
            'providers' => Provider::whereIn('org_id', $accessibleOrgIds)->active()->count(),
            'programs' => Program::whereIn('org_id', $accessibleOrgIds)->active()->count(),
            'courses' => MiniCourse::whereIn('org_id', $accessibleOrgIds)->where('status', MiniCourse::STATUS_ACTIVE)->count(),
        ];
    }

    /**
     * Check if user has moderation access.
     */
    public function getCanModerateProperty(): bool
    {
        $user = auth()->user();
        return in_array($user->effective_role, [
            'admin', 'consultant', 'superintendent', 'school_admin'
        ]);
    }

    /**
     * Get pending moderation count for current user's org.
     */
    public function getModerationCountProperty(): int
    {
        if (!$this->canModerate) {
            return 0;
        }

        $user = auth()->user();
        return ContentModerationResult::where('org_id', $user->org_id)
            ->needsReview()
            ->count();
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
            'canModerate' => $this->canModerate,
            'moderationCount' => $this->moderationCount,
        ])->layout('layouts.dashboard', ['title' => 'Resource Library']);
    }
}
