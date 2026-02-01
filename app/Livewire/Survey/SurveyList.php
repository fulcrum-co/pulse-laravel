<?php

namespace App\Livewire\Survey;

use App\Models\Survey;
use Livewire\Component;
use Livewire\WithPagination;

class SurveyList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $typeFilter = '';

    public string $orgFilter = '';

    public string $viewMode = 'grid';

    public ?string $surveyToDelete = null;

    public bool $showDeleteModal = false;

    public array $selected = [];

    public bool $showBulkDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
        'orgFilter' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
    ];

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatingOrgFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->typeFilter = '';
        $this->orgFilter = '';
        $this->resetPage();
    }

    /**
     * Open push modal for a survey.
     */
    public function openPushModal(int $surveyId): void
    {
        $this->dispatch('openPushSurvey', $surveyId);
    }

    /**
     * Check if the current user can push content.
     */
    public function getCanPushProperty(): bool
    {
        $user = auth()->user();
        $hasDownstream = $user->organization?->getDownstreamOrganizations()->count() > 0;
        $hasAssignedOrgs = $user->organizations()->count() > 0;

        return ($user->isAdmin() && $hasDownstream) || ($user->primary_role === 'consultant' && $hasAssignedOrgs);
    }

    /**
     * Toggle selection of a survey.
     */
    public function toggleSelect(string $surveyId): void
    {
        if (in_array($surveyId, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$surveyId]));
        } else {
            $this->selected[] = $surveyId;
        }
    }

    /**
     * Select all surveys on current page.
     */
    public function selectAll(): void
    {
        $user = auth()->user();
        $this->selected = Survey::forOrganization($user->org_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('survey_type', $this->typeFilter);
            })
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
            ->toArray();
    }

    /**
     * Clear selection.
     */
    public function deselectAll(): void
    {
        $this->selected = [];
    }

    /**
     * Show bulk delete confirmation.
     */
    public function confirmBulkDelete(): void
    {
        if (count($this->selected) > 0) {
            $this->showBulkDeleteModal = true;
        }
    }

    /**
     * Cancel bulk delete.
     */
    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
    }

    /**
     * Delete selected surveys.
     */
    public function deleteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $user = auth()->user();
        $ids = array_map('intval', $this->selected);
        $count = Survey::forOrganization($user->org_id)
            ->whereIn('id', $ids)
            ->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "{$count} survey(s) deleted successfully.",
        ]);

        $this->selected = [];
        $this->showBulkDeleteModal = false;
    }

    /**
     * Toggle survey active/paused status.
     */
    public function toggleStatus(string $surveyId): void
    {
        $survey = Survey::forOrganization(auth()->user()->org_id)->find($surveyId);

        if (! $survey) {
            return;
        }

        $newStatus = $survey->status === Survey::STATUS_ACTIVE
            ? Survey::STATUS_PAUSED
            : Survey::STATUS_ACTIVE;

        $survey->update(['status' => $newStatus]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $newStatus === Survey::STATUS_ACTIVE
                ? 'Survey activated successfully.'
                : 'Survey paused successfully.',
        ]);
    }

    /**
     * Confirm deletion of a survey.
     */
    public function confirmDelete(string $surveyId): void
    {
        $this->surveyToDelete = $surveyId;
        $this->showDeleteModal = true;
    }

    /**
     * Cancel deletion.
     */
    public function cancelDelete(): void
    {
        $this->surveyToDelete = null;
        $this->showDeleteModal = false;
    }

    /**
     * Delete the survey.
     */
    public function deleteSurvey(): void
    {
        if (! $this->surveyToDelete) {
            return;
        }

        $survey = Survey::forOrganization(auth()->user()->org_id)->find($this->surveyToDelete);

        if ($survey) {
            $survey->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Survey deleted successfully.',
            ]);
        }

        $this->surveyToDelete = null;
        $this->showDeleteModal = false;
    }

    /**
     * Duplicate a survey.
     */
    public function duplicate(string $surveyId): void
    {
        $survey = Survey::forOrganization(auth()->user()->org_id)->find($surveyId);

        if (! $survey) {
            return;
        }

        $newSurvey = $survey->replicate();
        $newSurvey->title = $survey->title.' (Copy)';
        $newSurvey->status = Survey::STATUS_DRAFT;
        $newSurvey->save();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Survey duplicated successfully.',
        ]);
    }

    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        // Build base query
        $query = Survey::query();

        // If user is admin/consultant, they can see surveys from all accessible orgs
        if ($isAdmin && $user->organization) {
            $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();
            $query->whereIn('org_id', $accessibleOrgIds);

            // Filter by specific org if selected
            if ($this->orgFilter && in_array((int) $this->orgFilter, $accessibleOrgIds)) {
                $query->where('org_id', $this->orgFilter);
            }
        } else {
            $query->where('org_id', $user->effective_org_id);
        }

        $surveys = $query
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'ilike', '%'.$this->search.'%')
                        ->orWhere('description', 'ilike', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->typeFilter, function ($query) {
                $query->where('survey_type', $this->typeFilter);
            })
            ->with('organization')
            ->withCount(['attempts', 'completedAttempts'])
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        // Get accessible orgs for filter dropdown (if admin)
        $accessibleOrgs = $isAdmin ? $user->getAccessibleOrganizations() : collect();

        return view('livewire.survey.survey-list', [
            'surveys' => $surveys,
            'statuses' => Survey::getStatuses(),
            'surveyTypes' => [
                'wellness' => 'Wellness',
                'academic' => 'Academic',
                'behavioral' => 'Behavioral',
                'custom' => 'Custom',
            ],
            'accessibleOrgs' => $accessibleOrgs,
            'isAdmin' => $isAdmin,
            'canPush' => $this->canPush,
        ]);
    }
}
