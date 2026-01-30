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

    public function render()
    {
        $user = auth()->user();

        $reports = CustomReport::where('org_id', $user->org_id)
            ->when($this->search, function ($query) {
                $query->where('report_name', 'like', '%' . $this->search . '%');
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        return view('livewire.report-list', [
            'reports' => $reports,
        ]);
    }
}
