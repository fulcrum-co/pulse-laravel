<?php

namespace App\Livewire\Collect;

use App\Models\Collection;
use App\Services\CollectionService;
use Livewire\Component;
use Livewire\WithPagination;

class CollectionList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $typeFilter = '';

    public string $viewMode = 'grid';

    public ?int $collectionToDelete = null;

    public bool $showDeleteModal = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'typeFilter' => ['except' => ''],
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

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->typeFilter = '';
        $this->resetPage();
    }

    public function confirmDelete(int $collectionId): void
    {
        $this->collectionToDelete = $collectionId;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->collectionToDelete = null;
        $this->showDeleteModal = false;
    }

    public function deleteCollection(): void
    {
        if (! $this->collectionToDelete) {
            return;
        }

        $terminology = app(\App\Services\TerminologyService::class);
        $collection = Collection::forOrganization(auth()->user()->org_id)
            ->find($this->collectionToDelete);

        if ($collection) {
            $collection->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => $terminology->get('collection_deleted_label') ?? 'Collection deleted successfully.',
            ]);
        }

        $this->collectionToDelete = null;
        $this->showDeleteModal = false;
    }

    public function toggleStatus(int $collectionId): void
    {
        $collection = Collection::forOrganization(auth()->user()->org_id)
            ->find($collectionId);

        if (! $collection) {
            return;
        }

        $service = app(CollectionService::class);

        if ($collection->status === Collection::STATUS_ACTIVE) {
            $service->pause($collection);
            $message = $terminology->get('collection_paused_label') ?? 'Collection paused successfully.';
        } else {
            $service->activate($collection);
            $message = $terminology->get('collection_activated_label') ?? 'Collection activated successfully.';
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $message,
        ]);
    }

    public function duplicate(int $collectionId): void
    {
        $collection = Collection::forOrganization(auth()->user()->org_id)
            ->find($collectionId);

        if (! $collection) {
            return;
        }

        $service = app(CollectionService::class);
        $service->duplicate($collection, auth()->user());

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $terminology->get('collection_duplicated_label') ?? 'Collection duplicated successfully.',
        ]);
    }

    public function render()
    {
        $user = auth()->user();

        $collections = Collection::forOrganization($user->org_id)
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
                $query->where('collection_type', $this->typeFilter);
            })
            ->withCount(['sessions', 'entries'])
            ->with(['schedules' => function ($q) {
                $q->where('is_active', true)->limit(1);
            }])
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        return view('livewire.collect.collection-list', [
            'collections' => $collections,
            'statuses' => Collection::getStatuses(),
            'collectionTypes' => Collection::getCollectionTypes(),
        ])->layout('components.layouts.dashboard', ['title' => app(\App\Services\TerminologyService::class)->get('collect_label') ?? 'Collect']);
    }
}
