<?php

namespace App\Livewire;

use App\Models\MiniCourse;
use App\Models\Organization;
use App\Models\Resource;
use App\Models\ResourceHubLead;
use App\Services\VectorSearchService;
use Illuminate\Support\Facades\Cookie;
use Livewire\Component;

class PublicResourceHub extends Component
{
    // Organization context
    public ?int $orgId = null;
    public ?string $orgSlug = null;
    public ?string $orgName = null;
    public ?string $orgLogo = null;

    // Search & Filters
    public string $search = '';
    public string $category = 'all'; // all, resources, courses
    public bool $isSearching = false;

    // Lead capture state
    public bool $showLeadGate = false;
    public bool $isUnlocked = false;
    public ?int $leadId = null;
    public int $freeViewsRemaining = 3;

    // Lead form fields
    public string $leadEmail = '';
    public string $leadName = '';
    public string $leadOrganization = '';
    public string $leadRole = '';

    // UTM tracking
    public array $utmParams = [];

    // View tracking
    public array $viewedItems = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'category' => ['except' => 'all'],
    ];

    protected $rules = [
        'leadEmail' => 'required|email',
        'leadName' => 'nullable|string|max:100',
        'leadOrganization' => 'nullable|string|max:100',
        'leadRole' => 'nullable|string|max:50',
    ];

    public function mount(?string $org = null): void
    {
        // Load organization by slug or ID
        if ($org) {
            $organization = Organization::where('slug', $org)
                ->orWhere('id', $org)
                ->first();

            if ($organization) {
                $this->orgId = $organization->id;
                $this->orgSlug = $organization->slug;
                $this->orgName = $organization->org_name;
                $this->orgLogo = $organization->logo_url;
            }
        }

        // Check for existing lead cookie
        $leadToken = Cookie::get('resource_hub_lead');
        if ($leadToken && $this->orgId) {
            $lead = ResourceHubLead::where('org_id', $this->orgId)
                ->where('verification_token', $leadToken)
                ->first();

            if ($lead) {
                $this->leadId = $lead->id;
                $this->isUnlocked = true;
                $this->leadEmail = $lead->email;
            }
        }

        // Parse UTM parameters
        $this->utmParams = [
            'utm_source' => request()->get('utm_source'),
            'utm_medium' => request()->get('utm_medium'),
            'utm_campaign' => request()->get('utm_campaign'),
            'utm_content' => request()->get('utm_content'),
            'utm_term' => request()->get('utm_term'),
        ];

        // Load viewed items from session
        $this->viewedItems = session('public_hub_viewed', []);
        $this->freeViewsRemaining = max(0, 3 - count($this->viewedItems));
    }

    public function updatedSearch(): void
    {
        $this->isSearching = strlen($this->search) >= 2;
    }

    /**
     * View a resource (tracks views and may trigger lead gate).
     */
    public function viewResource(int $resourceId): void
    {
        if (! $this->isUnlocked && ! in_array("resource_{$resourceId}", $this->viewedItems)) {
            $this->viewedItems[] = "resource_{$resourceId}";
            session(['public_hub_viewed' => $this->viewedItems]);
            $this->freeViewsRemaining = max(0, 3 - count($this->viewedItems));

            if ($this->freeViewsRemaining <= 0) {
                $this->showLeadGate = true;
                return;
            }
        }

        // Record view if lead exists
        if ($this->leadId) {
            $lead = ResourceHubLead::find($this->leadId);
            $lead?->recordResourceView($resourceId);
        }

        // Redirect to resource detail
        $this->redirect(route('public.resources.show', [
            'org' => $this->orgSlug ?? $this->orgId,
            'resource' => $resourceId,
        ]));
    }

    /**
     * View a course (tracks views and may trigger lead gate).
     */
    public function viewCourse(int $courseId): void
    {
        if (! $this->isUnlocked && ! in_array("course_{$courseId}", $this->viewedItems)) {
            $this->viewedItems[] = "course_{$courseId}";
            session(['public_hub_viewed' => $this->viewedItems]);
            $this->freeViewsRemaining = max(0, 3 - count($this->viewedItems));

            if ($this->freeViewsRemaining <= 0) {
                $this->showLeadGate = true;
                return;
            }
        }

        // Record view if lead exists
        if ($this->leadId) {
            $lead = ResourceHubLead::find($this->leadId);
            $lead?->recordCourseView($courseId);
        }

        // Redirect to course detail
        $this->redirect(route('public.courses.show', [
            'org' => $this->orgSlug ?? $this->orgId,
            'course' => $courseId,
        ]));
    }

    /**
     * Submit lead capture form.
     */
    public function submitLead(): void
    {
        $this->validate();

        if (! $this->orgId) {
            return;
        }

        $lead = ResourceHubLead::findOrCreateForOrg($this->orgId, $this->leadEmail, [
            'name' => $this->leadName,
            'organization_name' => $this->leadOrganization,
            'role' => $this->leadRole,
            'source' => ResourceHubLead::SOURCE_RESOURCE_HUB,
            'source_url' => request()->fullUrl(),
            'utm_params' => array_filter($this->utmParams),
        ]);

        // Generate access token
        $token = $lead->generateVerificationToken();

        // Set cookie for 30 days
        Cookie::queue('resource_hub_lead', $token, 60 * 24 * 30);

        $this->leadId = $lead->id;
        $this->isUnlocked = true;
        $this->showLeadGate = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Welcome! You now have full access to our resource library.',
        ]);
    }

    /**
     * Close the lead gate modal.
     */
    public function closeLeadGate(): void
    {
        $this->showLeadGate = false;
    }

    /**
     * Get public resources.
     */
    public function getResourcesProperty()
    {
        if (! $this->orgId) {
            return collect();
        }

        $query = Resource::where('org_id', $this->orgId)
            ->where('active', true)
            ->where('is_public', true);

        if ($this->isSearching) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'ilike', $searchTerm)
                    ->orWhere('description', 'ilike', $searchTerm);
            });
        }

        return $query->latest()->limit(12)->get();
    }

    /**
     * Get public courses.
     */
    public function getCoursesProperty()
    {
        if (! $this->orgId) {
            return collect();
        }

        $query = MiniCourse::where('org_id', $this->orgId)
            ->where('status', MiniCourse::STATUS_ACTIVE)
            ->whereIn('visibility', [MiniCourse::VISIBILITY_PUBLIC, MiniCourse::VISIBILITY_GATED]);

        if ($this->isSearching) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'ilike', $searchTerm)
                    ->orWhere('description', 'ilike', $searchTerm);
            });
        }

        return $query->withCount('steps')->latest()->limit(12)->get();
    }

    /**
     * Get counts for display.
     */
    public function getCountsProperty(): array
    {
        if (! $this->orgId) {
            return ['resources' => 0, 'courses' => 0];
        }

        return [
            'resources' => Resource::where('org_id', $this->orgId)
                ->where('active', true)
                ->where('is_public', true)
                ->count(),
            'courses' => MiniCourse::where('org_id', $this->orgId)
                ->where('status', MiniCourse::STATUS_ACTIVE)
                ->whereIn('visibility', [MiniCourse::VISIBILITY_PUBLIC, MiniCourse::VISIBILITY_GATED])
                ->count(),
        ];
    }

    public function render()
    {
        return view('livewire.public-resource-hub', [
            'resources' => $this->resources,
            'courses' => $this->courses,
            'counts' => $this->counts,
        ])->layout('layouts.public', [
            'title' => ($this->orgName ? $this->orgName . ' - ' : '') . 'Resource Hub',
            'orgName' => $this->orgName,
            'orgLogo' => $this->orgLogo,
        ]);
    }
}
