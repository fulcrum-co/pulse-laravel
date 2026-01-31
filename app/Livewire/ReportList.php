<?php

namespace App\Livewire;

use App\Models\CustomReport;
use Livewire\Component;
use Livewire\WithPagination;

class ReportList extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $viewMode = 'grid';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
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

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->resetPage();
    }

    public function duplicate(int $reportId): void
    {
        $user = auth()->user();
        $report = CustomReport::where('org_id', $user->org_id)->find($reportId);

        if ($report) {
            $newReport = $report->duplicate($user->id);
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Report duplicated successfully.',
            ]);
        }
    }

    public function delete(int $reportId): void
    {
        $user = auth()->user();
        $report = CustomReport::where('org_id', $user->org_id)->find($reportId);

        if ($report) {
            $report->delete();
            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Report deleted successfully.',
            ]);
        }
    }

    /**
     * Open push modal for a report.
     */
    public function openPushModal(int $reportId): void
    {
        $this->dispatch('openPushReport', $reportId);
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

    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        // Build base query
        $query = CustomReport::query();

        // If user is admin, they can see reports from all accessible orgs
        if ($isAdmin && $user->organization) {
            $accessibleOrgIds = $user->getAccessibleOrganizations()->pluck('id')->toArray();
            $query->whereIn('org_id', $accessibleOrgIds);
        } else {
            $query->where('org_id', $user->effective_org_id);
        }

        $reports = $query
            ->when($this->search, function ($query) {
                $query->where('report_name', 'ilike', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->with('organization')
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        return view('livewire.report-list', [
            'reports' => $reports,
            'isAdmin' => $isAdmin,
            'canPush' => $this->canPush,
        ]);
    }
}
