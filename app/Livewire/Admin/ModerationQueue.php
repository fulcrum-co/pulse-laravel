<?php

namespace App\Livewire\Admin;

use App\Models\ContentModerationResult;
use App\Services\Moderation\ContentModerationService;
use Livewire\Component;
use Livewire\WithPagination;

class ModerationQueue extends Component
{
    use WithPagination;

    public string $statusFilter = 'needs_review';
    public string $contentTypeFilter = '';
    public string $sortBy = 'created_at';
    public string $sortDirection = 'desc';

    // Modal state
    public bool $showReviewModal = false;
    public ?ContentModerationResult $selectedResult = null;
    public string $reviewNotes = '';
    public string $reviewAction = '';

    protected $queryString = [
        'statusFilter' => ['except' => 'needs_review'],
        'contentTypeFilter' => ['except' => ''],
        'sortBy' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount(): void
    {
        // Default to showing items needing review
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatedContentTypeFilter(): void
    {
        $this->resetPage();
    }

    public function openReviewModal(int $resultId): void
    {
        $this->selectedResult = ContentModerationResult::with('moderatable', 'reviewer')->find($resultId);
        $this->reviewNotes = '';
        $this->reviewAction = '';
        $this->showReviewModal = true;
    }

    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
        $this->selectedResult = null;
        $this->reviewNotes = '';
        $this->reviewAction = '';
    }

    public function approveContent(): void
    {
        if (!$this->selectedResult) {
            return;
        }

        $this->selectedResult->approve(auth()->id(), $this->reviewNotes);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content approved successfully.',
        ]);

        $this->closeReviewModal();
    }

    public function rejectContent(): void
    {
        if (!$this->selectedResult) {
            return;
        }

        $this->selectedResult->confirmRejection(auth()->id(), $this->reviewNotes);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Content rejection confirmed.',
        ]);

        $this->closeReviewModal();
    }

    public function requestRevision(): void
    {
        if (!$this->selectedResult) {
            return;
        }

        // Update the result with revision request
        $this->selectedResult->update([
            'status' => ContentModerationResult::STATUS_FLAGGED,
            'human_reviewed' => true,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'review_notes' => $this->reviewNotes ?: 'Revision requested',
        ]);

        // Update the moderatable content if it has approval workflow
        if ($this->selectedResult->moderatable && method_exists($this->selectedResult->moderatable, 'needsRevision')) {
            $this->selectedResult->moderatable->update([
                'approval_status' => 'revision_requested',
                'approval_notes' => $this->reviewNotes,
            ]);
        }

        $this->dispatch('notify', [
            'type' => 'info',
            'message' => 'Revision requested for this content.',
        ]);

        $this->closeReviewModal();
    }

    public function getStatsProperty(): array
    {
        $service = app(ContentModerationService::class);
        $orgId = auth()->user()->org_id ?? null;

        return $service->getStats($orgId);
    }

    public function sortBy(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }

    public function render()
    {
        $query = ContentModerationResult::with(['moderatable', 'reviewer']);

        // Apply organization filter
        $orgId = auth()->user()->org_id ?? null;
        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        // Apply status filter
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

        // Apply content type filter
        if ($this->contentTypeFilter) {
            $query->where('moderatable_type', $this->contentTypeFilter);
        }

        // Apply sorting
        $query->orderBy($this->sortBy, $this->sortDirection);

        return view('livewire.admin.moderation-queue', [
            'results' => $query->paginate(15),
            'stats' => $this->stats,
            'contentTypes' => $this->getContentTypes(),
        ]);
    }

    protected function getContentTypes(): array
    {
        return [
            'App\\Models\\MiniCourse' => 'Mini Courses',
            'App\\Models\\ContentBlock' => 'Content Blocks',
        ];
    }
}
