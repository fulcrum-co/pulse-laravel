<?php

namespace App\Livewire\Admin;

use App\Models\ContentModerationResult;
use App\Models\CourseApprovalWorkflow;
use App\Models\MiniCourse;
use App\Models\User;
use App\Services\Moderation\ContentModerationService;
use App\Services\Moderation\ModerationAssignmentService;
use Livewire\Component;
use Livewire\WithPagination;

class ModerationQueue extends Component
{
    use WithPagination;

    // Existing filters
    public string $statusFilter = 'needs_review';

    public string $contentTypeFilter = '';

    public string $sortBy = 'newest';

    public string $sortDirection = 'desc';

    public string $search = '';

    public string $viewMode = 'list';

    // Assignment filters
    public string $assignmentFilter = 'all';

    public string $priorityFilter = '';

    // Review modal state
    public bool $showReviewModal = false;

    public ?int $selectedResultId = null;

    public string $reviewNotes = '';

    // Assignment modal state
    public bool $showAssignModal = false;

    public ?int $assignToUserId = null;

    public string $assignmentPriority = 'normal';

    public ?string $assignmentDueAt = null;

    public string $assignmentNotes = '';

    public array $selectedCollaborators = [];

    // Edit content modal state
    public bool $showEditModal = false;

    public array $editForm = [];

    // Bulk selection
    public array $selectedItems = [];

    public bool $selectAll = false;

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
        // Check for review parameter from notification link
        if (request()->has('review')) {
            $this->openReviewModal((int) request()->get('review'));
        }
    }

    /**
     * Debug method to test if Livewire is working.
     * Can be removed after debugging.
     */
    public function testLivewire(): void
    {
        \Log::info('Livewire test method called - Livewire is working!');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Livewire is working! Test successful.',
        ]);
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
        $this->selectedItems = [];
        $this->selectAll = false;
    }

    public function updatedContentTypeFilter(): void
    {
        $this->resetPage();
        $this->selectedItems = [];
        $this->selectAll = false;
    }

    public function updatedAssignmentFilter(): void
    {
        $this->resetPage();
        $this->selectedItems = [];
        $this->selectAll = false;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        $this->selectedItems = [];
        $this->selectAll = false;
    }

    // ============================================
    // REVIEW MODAL
    // ============================================

    public function openReviewModal(int $resultId): void
    {
        \Log::info('openReviewModal called', ['resultId' => $resultId]);

        $this->selectedResultId = $resultId;
        $this->reviewNotes = '';
        $this->showReviewModal = true;

        \Log::info('Modal opened', ['showReviewModal' => $this->showReviewModal, 'selectedResultId' => $this->selectedResultId]);
    }

    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
        $this->selectedResultId = null;
        $this->reviewNotes = '';
    }

    public function getSelectedResultProperty(): ?ContentModerationResult
    {
        if (! $this->selectedResultId) {
            return null;
        }

        try {
            return ContentModerationResult::with(['moderatable', 'reviewer', 'assignee'])->find($this->selectedResultId);
        } catch (\Exception $e) {
            // Fallback without relationships if there's an issue
            \Log::error('Error loading moderation result: '.$e->getMessage());

            return ContentModerationResult::find($this->selectedResultId);
        }
    }

    public function approveContent(): void
    {
        $result = $this->selectedResult;
        if (! $result) {
            return;
        }

        // Check permission
        if (! $result->canBeReviewedBy(auth()->user())) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You do not have permission to review this item.',
            ]);

            return;
        }

        $result->approve(auth()->id(), $this->reviewNotes);

        // Notify content owner
        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'approved');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content approved successfully.',
        ]);

        $this->closeReviewModal();
    }

    public function rejectContent(): void
    {
        $result = $this->selectedResult;
        if (! $result) {
            return;
        }

        $result->confirmRejection(auth()->id(), $this->reviewNotes);

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'rejected');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content rejection confirmed.',
        ]);

        $this->closeReviewModal();
    }

    public function requestRevision(): void
    {
        $result = $this->selectedResult;
        if (! $result) {
            return;
        }

        $result->update([
            'status' => ContentModerationResult::STATUS_FLAGGED,
            'human_reviewed' => true,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $this->reviewNotes ?: 'Revision requested',
        ]);

        if ($result->moderatable && method_exists($result->moderatable, 'needsRevision')) {
            $result->moderatable->update([
                'approval_status' => 'revision_requested',
                'approval_notes' => $this->reviewNotes,
            ]);
        }

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'revision_requested');

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Revision requested for this content.',
        ]);

        $this->closeReviewModal();
    }

    // ============================================
    // ASSIGNMENT MODAL
    // ============================================

    public function openAssignModal(?int $resultId = null): void
    {
        if ($resultId) {
            $this->selectedResultId = $resultId;
            $result = ContentModerationResult::find($resultId);
            if ($result) {
                $this->assignToUserId = $result->assigned_to;
                $this->selectedCollaborators = $result->collaborator_ids ?? [];
                $this->assignmentPriority = $result->assignment_priority ?? 'normal';
                $this->assignmentDueAt = $result->due_at?->format('Y-m-d');
                $this->assignmentNotes = $result->assignment_notes ?? '';
            }
        }
        $this->showAssignModal = true;
    }

    public function closeAssignModal(): void
    {
        $this->showAssignModal = false;
        $this->assignToUserId = null;
        $this->assignmentPriority = 'normal';
        $this->assignmentDueAt = null;
        $this->assignmentNotes = '';
        $this->selectedCollaborators = [];
        if (! $this->showReviewModal && ! $this->showEditModal) {
            $this->selectedResultId = null;
        }
    }

    public function saveAssignment(): void
    {
        $this->validate([
            'assignToUserId' => 'required|exists:users,id',
            'assignmentPriority' => 'required|in:low,normal,high,urgent',
            'assignmentDueAt' => 'nullable|date|after_or_equal:today',
        ]);

        $service = app(ModerationAssignmentService::class);

        // Single assignment
        if ($this->selectedResultId && empty($this->selectedItems)) {
            $result = ContentModerationResult::find($this->selectedResultId);
            if ($result) {
                $service->assign(
                    $result,
                    $this->assignToUserId,
                    auth()->id(),
                    [
                        'priority' => $this->assignmentPriority,
                        'due_at' => $this->assignmentDueAt ? \Carbon\Carbon::parse($this->assignmentDueAt) : null,
                        'notes' => $this->assignmentNotes,
                    ]
                );

                // Add collaborators
                foreach ($this->selectedCollaborators as $collaboratorId) {
                    if ($collaboratorId != $this->assignToUserId) {
                        $service->addCollaborator($result, (int) $collaboratorId, auth()->id());
                    }
                }
            }
        }
        // Bulk assignment
        elseif (! empty($this->selectedItems)) {
            $service->bulkAssign(
                $this->selectedItems,
                $this->assignToUserId,
                auth()->id(),
                [
                    'priority' => $this->assignmentPriority,
                    'due_at' => $this->assignmentDueAt ? \Carbon\Carbon::parse($this->assignmentDueAt) : null,
                    'notes' => $this->assignmentNotes,
                ]
            );
            $this->selectedItems = [];
            $this->selectAll = false;
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Assignment saved successfully.',
        ]);

        $this->closeAssignModal();
    }

    public function unassign(int $resultId): void
    {
        $result = ContentModerationResult::find($resultId);
        if ($result && $this->canManageAssignment($result)) {
            $result->unassign();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Assignment removed.',
            ]);
        }
    }

    // ============================================
    // EDIT CONTENT MODAL
    // ============================================

    public function openEditModal(int $resultId): void
    {
        $result = ContentModerationResult::with('moderatable')->find($resultId);

        if (! $result?->moderatable) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Content not found.',
            ]);

            return;
        }

        $this->selectedResultId = $resultId;
        $this->editForm = $this->buildEditForm($result->moderatable);
        $this->showEditModal = true;
    }

    protected function buildEditForm($moderatable): array
    {
        $class = get_class($moderatable);

        return match ($class) {
            'App\\Models\\MiniCourse' => [
                'type' => 'MiniCourse',
                'id' => $moderatable->id,
                'title' => $moderatable->title,
                'description' => $moderatable->description,
                'rationale' => $moderatable->rationale ?? '',
                'expected_experience' => $moderatable->expected_experience ?? '',
                'objectives' => $moderatable->objectives ?? [],
            ],
            'App\\Models\\ContentBlock' => [
                'type' => 'ContentBlock',
                'id' => $moderatable->id,
                'title' => $moderatable->title,
                'description' => $moderatable->description ?? '',
            ],
            default => [
                'type' => 'Unknown',
                'id' => $moderatable->id ?? null,
            ],
        };
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editForm = [];
        if (! $this->showReviewModal && ! $this->showAssignModal) {
            $this->selectedResultId = null;
        }
    }

    public function saveContentEdits(): void
    {
        $result = $this->selectedResult;
        if (! $result?->moderatable) {
            return;
        }

        $moderatable = $result->moderatable;

        // Validate and update based on content type
        match ($this->editForm['type']) {
            'MiniCourse' => $this->updateMiniCourse($moderatable),
            'ContentBlock' => $this->updateContentBlock($moderatable),
            default => null,
        };

        // Manually trigger re-moderation if the trait method exists
        if (method_exists($moderatable, 'queueModeration')) {
            $moderatable->queueModeration();
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content updated. Re-moderation has been triggered.',
        ]);

        $this->closeEditModal();
    }

    public function saveAndApprove(): void
    {
        $result = $this->selectedResult;
        if (! $result?->moderatable) {
            return;
        }

        $moderatable = $result->moderatable;

        // Update content first
        match ($this->editForm['type']) {
            'MiniCourse' => $this->updateMiniCourse($moderatable),
            'ContentBlock' => $this->updateContentBlock($moderatable),
            default => null,
        };

        // Approve without re-moderation (user is satisfied with edits)
        $result->approve(auth()->id(), 'Approved after content edits');

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'approved');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content updated and approved.',
        ]);

        $this->closeEditModal();
        $this->closeReviewModal();
    }

    public function saveAndPublish(): void
    {
        $result = $this->selectedResult;
        if (! $result?->moderatable) {
            return;
        }

        $moderatable = $result->moderatable;

        // Only MiniCourses can be published
        if (! ($moderatable instanceof MiniCourse)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Only Mini Courses can be published.',
            ]);

            return;
        }

        // Update content first
        $this->updateMiniCourse($moderatable);

        // Approve the moderation result
        $result->approve(auth()->id(), 'Approved and published after content edits');

        // Create/update approval workflow as approved
        $workflow = CourseApprovalWorkflow::firstOrNew([
            'mini_course_id' => $moderatable->id,
        ]);

        $workflow->fill([
            'status' => CourseApprovalWorkflow::STATUS_APPROVED,
            'workflow_mode' => CourseApprovalWorkflow::MODE_CREATE_APPROVE,
            'submitted_by' => $moderatable->created_by ?? auth()->id(),
            'submitted_at' => $workflow->submitted_at ?? now(),
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => 'Approved and published from moderation queue',
        ]);

        $workflow->save();

        // Update course to approved and active
        $moderatable->update([
            'approval_status' => MiniCourse::APPROVAL_APPROVED,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'approval_notes' => 'Approved and published from moderation queue',
            'status' => MiniCourse::STATUS_ACTIVE,
        ]);

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'approved');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Course updated, approved, and published successfully.',
        ]);

        $this->closeEditModal();
        $this->closeReviewModal();
    }

    protected function updateMiniCourse($course): void
    {
        $this->validate([
            'editForm.title' => 'required|string|max:255',
            'editForm.description' => 'required|string',
        ]);

        $course->update([
            'title' => $this->editForm['title'],
            'description' => $this->editForm['description'],
            'rationale' => $this->editForm['rationale'],
            'expected_experience' => $this->editForm['expected_experience'],
            'objectives' => $this->editForm['objectives'],
        ]);
    }

    protected function updateContentBlock($block): void
    {
        $this->validate([
            'editForm.title' => 'required|string|max:255',
        ]);

        $block->update([
            'title' => $this->editForm['title'],
            'description' => $this->editForm['description'],
        ]);
    }

    // ============================================
    // APPROVAL WORKFLOW
    // ============================================

    public function submitForApproval(): void
    {
        $result = $this->selectedResult;
        if (! $result?->moderatable) {
            return;
        }

        $moderatable = $result->moderatable;

        // Only MiniCourses have approval workflow
        if (! ($moderatable instanceof MiniCourse)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Approval workflow is only available for Mini Courses.',
            ]);

            return;
        }

        // Check if moderation has passed
        if (! $result->status === ContentModerationResult::STATUS_PASSED &&
            ! $result->status === ContentModerationResult::STATUS_APPROVED_OVERRIDE) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Content must pass moderation before submitting for approval.',
            ]);

            return;
        }

        // Create or update approval workflow
        $workflow = CourseApprovalWorkflow::firstOrNew([
            'mini_course_id' => $moderatable->id,
        ]);

        $workflow->fill([
            'status' => CourseApprovalWorkflow::STATUS_PENDING,
            'workflow_mode' => CourseApprovalWorkflow::MODE_CREATE_APPROVE,
            'submitted_by' => auth()->id(),
            'submitted_at' => now(),
        ]);

        $workflow->save();

        // Update course approval status
        $moderatable->update([
            'approval_status' => MiniCourse::APPROVAL_PENDING,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content submitted for approval.',
        ]);

        $this->closeReviewModal();
    }

    public function approveAndPublish(): void
    {
        $result = $this->selectedResult;
        if (! $result?->moderatable) {
            return;
        }

        // First approve the moderation
        $result->approve(auth()->id(), $this->reviewNotes);

        $moderatable = $result->moderatable;

        // If it's a MiniCourse, also approve the workflow and activate
        if ($moderatable instanceof MiniCourse) {
            // Create/update workflow as approved
            $workflow = CourseApprovalWorkflow::firstOrNew([
                'mini_course_id' => $moderatable->id,
            ]);

            $workflow->fill([
                'status' => CourseApprovalWorkflow::STATUS_APPROVED,
                'workflow_mode' => CourseApprovalWorkflow::MODE_CREATE_APPROVE,
                'submitted_by' => $moderatable->created_by ?? auth()->id(),
                'submitted_at' => $workflow->submitted_at ?? now(),
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
                'review_notes' => $this->reviewNotes,
            ]);

            $workflow->save();

            // Update course to approved and active
            $moderatable->update([
                'approval_status' => MiniCourse::APPROVAL_APPROVED,
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'approval_notes' => $this->reviewNotes,
                'status' => MiniCourse::STATUS_ACTIVE,
            ]);

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Content approved and published.',
            ]);
        } else {
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Content approved.',
            ]);
        }

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'approved');

        $this->closeReviewModal();
    }

    // ============================================
    // BULK OPERATIONS
    // ============================================

    public function toggleSelectAll(): void
    {
        if ($this->selectAll) {
            $this->selectedItems = $this->getFilteredQuery()->pluck('id')->toArray();
        } else {
            $this->selectedItems = [];
        }
    }

    public function toggleSelect(int $id): void
    {
        if (in_array($id, $this->selectedItems)) {
            $this->selectedItems = array_values(array_filter($this->selectedItems, fn ($i) => $i !== $id));
        } else {
            $this->selectedItems[] = $id;
        }

        $this->selectAll = false;
    }

    public function bulkAssign(): void
    {
        if (empty($this->selectedItems)) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'Please select items to assign.',
            ]);

            return;
        }

        $this->selectedResultId = null;
        $this->openAssignModal();
    }

    // ============================================
    // QUICK ACTIONS (no modal needed)
    // ============================================

    public function quickApprove(int $resultId): void
    {
        $result = ContentModerationResult::find($resultId);
        if (! $result) {
            return;
        }

        // Check permission
        if (! $result->canBeReviewedBy(auth()->user())) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You do not have permission to review this item.',
            ]);

            return;
        }

        $result->approve(auth()->id(), 'Quick approved from queue');

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'approved');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content approved successfully.',
        ]);
    }

    public function quickReject(int $resultId): void
    {
        $result = ContentModerationResult::find($resultId);
        if (! $result) {
            return;
        }

        // Check permission
        if (! $result->canBeReviewedBy(auth()->user())) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'You do not have permission to review this item.',
            ]);

            return;
        }

        $result->confirmRejection(auth()->id(), 'Rejected from queue');

        app(ModerationAssignmentService::class)->notifyModerationComplete($result, 'rejected');

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content rejected.',
        ]);
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

    public function getEligibleModeratorsProperty(): \Illuminate\Support\Collection
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

        // Admins can manage any assignment in their org
        if ($user->isAdmin() && $user->canAccessOrganization($result->org_id)) {
            return true;
        }

        // School admin can manage in their org
        if ($user->effective_role === 'school_admin' && $user->canAccessOrganization($result->org_id)) {
            return true;
        }

        // Assigner can manage their own assignments
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
    // SORTING
    // ============================================

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    // ============================================
    // QUERY BUILDING
    // ============================================

    protected function getFilteredQuery()
    {
        $user = auth()->user();
        $query = ContentModerationResult::with(['moderatable', 'reviewer', 'assignee']);

        // Organization filter
        $orgId = $user->org_id ?? null;
        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        // Role-based visibility
        if (! $this->canViewAllItems()) {
            // Non-admin users only see their assignments and collaborations
            $query->assignedToOrCollaborator($user->id);
        }

        // Assignment filter
        switch ($this->assignmentFilter) {
            case 'my_assignments':
                $query->assignedTo($user->id);
                break;
            case 'collaborating':
                $query->whereJsonContains('collaborator_ids', $user->id);
                break;
            case 'unassigned':
                $query->unassigned();
                break;
                // 'all' shows everything (respecting role visibility above)
        }

        // Status filter
        if ($this->statusFilter === 'needs_review') {
            $query->needsReview();
        } elseif ($this->statusFilter === 'flagged') {
            $query->flagged();
        } elseif ($this->statusFilter === 'rejected') {
            $query->rejected();
        } elseif ($this->statusFilter === 'passed') {
            $query->passed();
        } elseif ($this->statusFilter === 'pending') {
            $query->pending();
        }

        // Content type filter
        if ($this->contentTypeFilter) {
            $query->where('moderatable_type', $this->contentTypeFilter);
        }

        // Search filter
        if ($this->search) {
            $searchTerm = '%'.$this->search.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->whereHasMorph('moderatable', '*', function ($subQuery) use ($searchTerm) {
                    $subQuery->where('title', 'like', $searchTerm)
                        ->orWhere('description', 'like', $searchTerm);
                });
            });
        }

        // Priority filter
        if ($this->priorityFilter) {
            $query->byAssignmentPriority($this->priorityFilter);
        }

        // Sorting
        match ($this->sortBy) {
            'newest' => $query->orderBy('created_at', 'desc'),
            'oldest' => $query->orderBy('created_at', 'asc'),
            'score_low' => $query->orderBy('overall_score', 'asc'),
            'score_high' => $query->orderBy('overall_score', 'desc'),
            'priority' => $query->orderByPriorityAndDue(),
            default => $query->orderBy('created_at', 'desc'),
        };

        return $query;
    }

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
