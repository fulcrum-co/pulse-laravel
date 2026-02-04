<?php

namespace App\Livewire\Admin;

use App\Livewire\Admin\Concerns\WithApprovalWorkflow;
use App\Livewire\Admin\Concerns\WithAssignmentModal;
use App\Livewire\Admin\Concerns\WithBulkSelection;
use App\Livewire\Admin\Concerns\WithEditContentModal;
use App\Livewire\Admin\Concerns\WithModerationFilters;
use App\Livewire\Admin\Concerns\WithReviewModal;
use App\Models\ContentModerationResult;
use App\Models\MiniCourse;
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

    // Queue type: 'content' for content moderation, 'courses' for course approval
    public string $queueType = 'content';

    // Course approval filters
    public string $courseApprovalFilter = 'pending_review';

    public string $courseTriggerFilter = '';

    protected $queryString = [
        'queueType' => ['except' => 'content'],
        'statusFilter' => ['except' => 'needs_review'],
        'contentTypeFilter' => ['except' => ''],
        'assignmentFilter' => ['except' => 'all'],
        'priorityFilter' => ['except' => ''],
        'sortBy' => ['except' => 'newest'],
        'sortDirection' => ['except' => 'desc'],
        'search' => ['except' => ''],
        'viewMode' => ['except' => 'list'],
        'courseApprovalFilter' => ['except' => 'pending_review'],
        'courseTriggerFilter' => ['except' => ''],
    ];

    protected $listeners = ['refreshQueue' => '$refresh'];

    public function mount(): void
    {
        if (request()->has('review')) {
            $this->openReviewModal((int) request()->get('review'));
        }
    }

    // ============================================
    // QUEUE TYPE SWITCHING
    // ============================================

    public function switchToQueue(string $type): void
    {
        $this->queueType = $type;
        $this->resetPage();
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

    /**
     * Get course approval stats for the courses tab.
     */
    public function getCourseStatsProperty(): array
    {
        $orgId = auth()->user()->org_id ?? null;

        if (! $orgId) {
            return [
                'pending_review' => 0,
                'approved' => 0,
                'rejected' => 0,
                'revision_requested' => 0,
                'auto_generated_pending' => 0,
            ];
        }

        return [
            'pending_review' => MiniCourse::forOrganization($orgId)
                ->pendingApproval()
                ->count(),
            'approved' => MiniCourse::forOrganization($orgId)
                ->byApprovalStatus(MiniCourse::APPROVAL_APPROVED)
                ->count(),
            'rejected' => MiniCourse::forOrganization($orgId)
                ->byApprovalStatus(MiniCourse::APPROVAL_REJECTED)
                ->count(),
            'revision_requested' => MiniCourse::forOrganization($orgId)
                ->byApprovalStatus(MiniCourse::APPROVAL_REVISION)
                ->count(),
            'auto_generated_pending' => MiniCourse::forOrganization($orgId)
                ->pendingApproval()
                ->whereIn('generation_trigger', [MiniCourse::TRIGGER_WORKFLOW, MiniCourse::TRIGGER_SCHEDULED, MiniCourse::TRIGGER_SIGNAL])
                ->count(),
        ];
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

        if ($user->effective_role === 'school_admin' && $user->canAccessOrganization($result->org_id)) {
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

        return in_array($user->effective_role, ['admin', 'consultant', 'superintendent', 'school_admin']);
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
    // COURSE APPROVAL QUERIES
    // ============================================

    /**
     * Get filtered courses for approval queue.
     */
    protected function getFilteredCoursesQuery(): Builder
    {
        $user = auth()->user();
        $query = MiniCourse::with(['creator', 'organization', 'approver'])
            ->withCount('steps');

        $orgId = $user->org_id ?? null;
        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        // Apply approval status filter
        if ($this->courseApprovalFilter) {
            $query->where('approval_status', $this->courseApprovalFilter);
        }

        // Apply trigger filter (workflow, scheduled, etc.)
        if ($this->courseTriggerFilter) {
            $query->where('generation_trigger', $this->courseTriggerFilter);
        }

        // Apply search
        if ($this->search) {
            $searchTerm = '%'.$this->search.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Apply sorting
        match ($this->sortBy) {
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            default => $query->orderBy('created_at', 'desc'),
        };

        return $query;
    }

    // ============================================
    // COURSE APPROVAL ACTIONS
    // ============================================

    /**
     * Approve a course.
     */
    public function approveCourse(int $courseId): void
    {
        $course = MiniCourse::findOrFail($courseId);

        // Authorization check
        if (! $this->canApproveCourse($course)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You do not have permission to approve this course.',
            ]);

            return;
        }

        $course->update([
            'approval_status' => MiniCourse::APPROVAL_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Course \"{$course->title}\" has been approved.",
        ]);

        $this->dispatch('refreshQueue');
    }

    /**
     * Reject a course.
     */
    public function rejectCourse(int $courseId, string $notes = ''): void
    {
        $course = MiniCourse::findOrFail($courseId);

        if (! $this->canApproveCourse($course)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You do not have permission to reject this course.',
            ]);

            return;
        }

        $course->update([
            'approval_status' => MiniCourse::APPROVAL_REJECTED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => $notes ?: null,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Course \"{$course->title}\" has been rejected.",
        ]);

        $this->dispatch('refreshQueue');
    }

    /**
     * Request revision for a course.
     */
    public function requestCourseRevision(int $courseId, string $notes): void
    {
        $course = MiniCourse::findOrFail($courseId);

        if (! $this->canApproveCourse($course)) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You do not have permission to request revisions.',
            ]);

            return;
        }

        $course->update([
            'approval_status' => MiniCourse::APPROVAL_REVISION,
            'approval_notes' => $notes,
        ]);

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => "Revision requested for course \"{$course->title}\".",
        ]);

        $this->dispatch('refreshQueue');
    }

    /**
     * Check if user can approve courses.
     */
    protected function canApproveCourse(MiniCourse $course): bool
    {
        $user = auth()->user();

        if ($user->isAdmin() && $user->canAccessOrganization($course->org_id)) {
            return true;
        }

        if (in_array($user->effective_role, ['school_admin', 'superintendent', 'consultant'])) {
            return $user->canAccessOrganization($course->org_id);
        }

        return false;
    }

    // ============================================
    // RENDER
    // ============================================

    public function render()
    {
        $data = [
            'stats' => $this->stats,
            'assignmentStats' => $this->assignmentStats,
            'contentTypes' => $this->getContentTypes(),
            'eligibleModerators' => $this->eligibleModerators,
            'assignmentPriorities' => ContentModerationResult::getPriorities(),
            'canAssign' => $this->canAssign(),
            'canViewAll' => $this->canViewAllItems(),
            'queueType' => $this->queueType,
        ];

        if ($this->queueType === 'courses') {
            $data['courses'] = $this->getFilteredCoursesQuery()->paginate(15);
            $data['courseStats'] = $this->courseStats;
            $data['approvalStatuses'] = MiniCourse::getApprovalStatuses();
            $data['generationTriggers'] = MiniCourse::getGenerationTriggers();
        } else {
            $data['results'] = $this->getFilteredQuery()->paginate(15);
        }

        return view('livewire.admin.moderation-queue', $data)
            ->layout('components.layouts.dashboard', ['title' => 'Content Moderation Queue']);
    }

    protected function getContentTypes(): array
    {
        return [
            'App\\Models\\MiniCourse' => 'Mini Courses',
            'App\\Models\\ContentBlock' => 'Content Blocks',
        ];
    }
}
