<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\WithApprovalWorkflow;
use App\Livewire\Admin\Concerns\WithAssignmentModal;
use App\Livewire\Admin\Concerns\WithBulkSelection;
use App\Livewire\Admin\Concerns\WithEditContentModal;
use App\Livewire\Admin\Concerns\WithModerationFilters;
use App\Livewire\Admin\Concerns\WithReviewModal;
use App\Models\ContentModerationResult;
use App\Services\Moderation\ContentModerationService;
use App\Services\Moderation\ModerationAssignmentService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ModerationQueue extends Component
{
    use WithApprovalWorkflow;
    use WithAssignmentModal;
    use WithBulkSelection;
    use WithEditContentModal;
    use WithModerationFilters;
    use WithPagination;
    use WithReviewModal;

    protected $queryString = [
        'statusFilter' => ['except' => 'needs_review'],
        'contentTypeFilter' => ['except' => ''],
        'assignmentFilter' => ['except' => 'all'],
        'priorityFilter' => ['except' => ''],
        'sortBy' => ['except' => 'newest'],
        'sortDirection' => ['except' => 'desc'],
        'search' => ['except' => ''],
        'viewMode' => ['except' => 'list'],
    ];

    protected $listeners = ['refreshQueue' => '$refresh'];

    public function mount(): void
    {
        if (request()->has('review')) {
            $this->openReviewModal((int) request()->get('review'));
        }
    }

    // ============================================
    // COMPUTED PROPERTIES
    // ============================================

    public function getStatsProperty(): array
    {
        $service = app(ContentModerationService::class);
        $orgId = auth()->user()->org_id ?? null;

        return $service->getStats($orgId);
    }

    public function getAssignmentStatsProperty(): array
    {
        $orgId = auth()->user()->org_id ?? null;
        $userId = auth()->id();

        if (! $orgId) {
            return ['my_assignments' => 0, 'collaborating' => 0, 'unassigned' => 0, 'overdue' => 0];
        }

        return app(ModerationAssignmentService::class)->getAssignmentStats($orgId, $userId);
    }

    public function getEligibleModeratorsProperty(): Collection
    {
        $orgId = auth()->user()->org_id ?? null;
        if (! $orgId) {
            return collect();
        }

        return app(ModerationAssignmentService::class)->getEligibleModerators($orgId);
    }

    // ============================================
    // AUTHORIZATION HELPERS
    // ============================================

    protected function canManageAssignment(ContentModerationResult $result): bool
    {
        $user = auth()->user();

        if ($user->isAdmin() && $user->canAccessOrganization($result->org_id)) {
            return true;
        }

        if ($user->effective_role === 'organization_admin' && $user->canAccessOrganization($result->org_id)) {
            return true;
        }

        if ($result->assigned_by === $user->id) {
            return true;
        }

        return false;
    }

    protected function canViewAllItems(): bool
    {
        $user = auth()->user();

        return in_array($user->effective_role, ['admin', 'consultant', 'superintendent']);
    }

    protected function canAssign(): bool
    {
        $user = auth()->user();

        return in_array($user->effective_role, ['admin', 'consultant', 'superintendent', 'organization_admin']);
    }

    // ============================================
    // QUERY BUILDING
    // ============================================

    protected function getFilteredQuery(): Builder
    {
        $user = auth()->user();
        $query = ContentModerationResult::with(['moderatable', 'reviewer', 'assignee']);

        $orgId = $user->org_id ?? null;
        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        // Role-based visibility
        if (! $this->canViewAllItems()) {
            $query->assignedToOrCollaborator($user->id);
        }

        $this->applyAssignmentFilter($query, $user);
        $this->applyStatusFilter($query);
        $this->applyContentTypeFilter($query);
        $this->applySearchFilter($query);
        $this->applyPriorityFilter($query);
        $this->applySorting($query);

        return $query;
    }

    protected function applyAssignmentFilter(Builder $query, $user): void
    {
        match ($this->assignmentFilter) {
            'my_assignments' => $query->assignedTo($user->id),
            'collaborating' => $query->whereJsonContains('collaborator_ids', $user->id),
            'unassigned' => $query->unassigned(),
            default => null,
        };
    }

    protected function applyStatusFilter(Builder $query): void
    {
        match ($this->statusFilter) {
            'needs_review' => $query->needsReview(),
            'flagged' => $query->flagged(),
            'rejected' => $query->rejected(),
            'passed' => $query->passed(),
            'pending' => $query->pending(),
            default => null,
        };
    }

    protected function applyContentTypeFilter(Builder $query): void
    {
        if ($this->contentTypeFilter) {
            $query->where('moderatable_type', $this->contentTypeFilter);
        }
    }

    protected function applySearchFilter(Builder $query): void
    {
        if (! $this->search) {
            return;
        }

        $searchTerm = '%' . $this->search . '%';
        $query->where(function ($q) use ($searchTerm) {
            $q->whereHasMorph('moderatable', '*', function ($subQuery) use ($searchTerm) {
                $subQuery->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        });
    }

    protected function applyPriorityFilter(Builder $query): void
    {
        if ($this->priorityFilter) {
            $query->byAssignmentPriority($this->priorityFilter);
        }
    }

    protected function applySorting(Builder $query): void
    {
        match ($this->sortBy) {
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            'score_low' => $query->orderBy('overall_score', 'asc'),
            'score_high' => $query->orderBy('overall_score', 'desc'),
            'priority' => $query->orderByPriorityAndDue(),
            default => $query->orderBy('created_at', 'desc'),
        };
    }

    // ============================================
    // RENDER
    // ============================================

    public function render()
    {
        return view('livewire.admin.moderation-queue', [
            'results' => $this->getFilteredQuery()->paginate(15),
            'stats' => $this->stats,
            'assignmentStats' => $this->assignmentStats,
            'contentTypes' => $this->getContentTypes(),
            'eligibleModerators' => $this->eligibleModerators,
            'assignmentPriorities' => ContentModerationResult::getPriorities(),
            'canAssign' => $this->canAssign(),
            'canViewAll' => $this->canViewAllItems(),
        ])->layout('components.layouts.dashboard', ['title' => 'Content Moderation Queue']);
    }

    protected function getContentTypes(): array
    {
        return [
            'App\\Models\\MiniCourse' => 'Mini Courses',
            'App\\Models\\ContentBlock' => 'Content Blocks',
        ];
    }
}
