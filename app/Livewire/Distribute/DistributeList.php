<?php

namespace App\Livewire\Distribute;

use App\Models\Distribution;
use Livewire\Component;
use Livewire\WithPagination;

class DistributeList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $channelFilter = '';

    public string $viewMode = 'grid';

    public ?int $distributionToDelete = null;

    public bool $showDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'channelFilter' => ['except' => ''],
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

    public function updatingChannelFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->channelFilter = '';
        $this->resetPage();
    }

    public function confirmDelete(int $distributionId): void
    {
        $this->distributionToDelete = $distributionId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->distributionToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteDistribution(): void
    {
        if (! $this->distributionToDelete) {
            return;
        }

        $distribution = Distribution::where('org_id', auth()->user()->org_id)
            ->find($this->distributionToDelete);

        if ($distribution) {
            $distribution->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Distribution deleted successfully.',
            ]);
        }

        $this->distributionToDelete = null;
        $this->showDeleteModal = false;
    }

    public function toggleStatus(int $distributionId): void
    {
        $distribution = Distribution::where('org_id', auth()->user()->org_id)
            ->find($distributionId);

        if (! $distribution) {
            return;
        }

        if ($distribution->status === Distribution::STATUS_ACTIVE) {
            $distribution->update(['status' => Distribution::STATUS_PAUSED]);
            $message = 'Distribution paused successfully.';
        } elseif ($distribution->status === Distribution::STATUS_PAUSED) {
            $distribution->update(['status' => Distribution::STATUS_ACTIVE]);
            $message = 'Distribution activated successfully.';
        } else {
            return;
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    public function duplicate(int $distributionId): void
    {
        $distribution = Distribution::where('org_id', auth()->user()->org_id)
            ->find($distributionId);

        if (! $distribution) {
            return;
        }

        $newDistribution = $distribution->replicate();
        $newDistribution->title = $distribution->title.' (Copy)';
        $newDistribution->status = Distribution::STATUS_DRAFT;
        $newDistribution->created_by = auth()->id();
        $newDistribution->save();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Distribution duplicated successfully.',
        ]);
    }

    public function render()
    {
        $user = auth()->user();

        $distributions = Distribution::where('org_id', $user->org_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->channelFilter, function ($query) {
                $query->where('channel', $this->channelFilter);
            })
            ->with(['contactList', 'report', 'schedule'])
            ->withCount('deliveries')
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        return view('livewire.distribute.distribute-list', [
            'distributions' => $distributions,
            'statuses' => Distribution::getStatuses(),
            'channels' => Distribution::getChannels(),
        ])->layout('components.layouts.dashboard', ['title' => 'Distribute']);
    }
}
