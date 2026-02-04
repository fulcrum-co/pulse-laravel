<?php

namespace App\Livewire\Components;

use App\Models\Resource;
use App\Services\VectorSearchService;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class ResourcePickerModal extends Component
{
    use WithPagination;

    public bool $show = false;

    public string $search = '';

    public string $filterType = '';

    public string $filterCategory = '';

    public bool $includeUnapproved = false;

    public bool $useSemanticSearch = false;

    public ?int $selectedResourceId = null;

    // Context from parent (passed when opening)
    public ?int $stepIndex = null;

    protected $listeners = ['open-resource-picker' => 'openModal'];

    public function openModal(array $params = []): void
    {
        $this->show = true;
        $this->stepIndex = $params['stepIndex'] ?? null;
        $this->resetFilters();
    }

    public function closeModal(): void
    {
        $this->show = false;
        $this->selectedResourceId = null;
        $this->stepIndex = null;
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->filterType = '';
        $this->filterCategory = '';
        $this->selectedResourceId = null;
        $this->resetPage();
    }

    /**
     * Get filtered resources.
     */
    public function getResourcesProperty(): Collection|\Illuminate\Pagination\LengthAwarePaginator
    {
        $user = auth()->user();
        $orgId = $user->org_id;

        // If using semantic search with a query
        if ($this->useSemanticSearch && strlen($this->search) >= 3) {
            return $this->performSemanticSearch($orgId);
        }

        // Standard database query
        $query = Resource::query()
            ->where('org_id', $orgId);

        // Filter by active status
        if (! $this->includeUnapproved) {
            $query->where('active', true);
        }

        // Apply keyword search
        if (strlen($this->search) >= 2) {
            $searchTerm = '%'.$this->search.'%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('description', 'like', $searchTerm);
            });
        }

        // Apply type filter
        if ($this->filterType) {
            $query->where('resource_type', $this->filterType);
        }

        // Apply category filter
        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        return $query->orderBy('title')->paginate(12);
    }

    /**
     * Perform semantic search using vector embeddings.
     */
    protected function performSemanticSearch(int $orgId): Collection
    {
        try {
            $vectorService = app(VectorSearchService::class);

            $results = $vectorService->search(
                query: $this->search,
                orgIds: [$orgId],
                modelTypes: ['Resource'],
                limit: 20
            );

            // If not including unapproved, filter results
            if (! $this->includeUnapproved) {
                $results = $results->filter(fn ($item) => $item->active);
            }

            // Apply additional filters
            if ($this->filterType) {
                $results = $results->filter(fn ($item) => $item->resource_type === $this->filterType);
            }

            if ($this->filterCategory) {
                $results = $results->filter(fn ($item) => $item->category === $this->filterCategory);
            }

            return $results->values();
        } catch (\Exception $e) {
            // Fall back to regular search on error
            \Log::warning('Semantic search failed, falling back to keyword search', ['error' => $e->getMessage()]);

            return Resource::query()
                ->where('org_id', $orgId)
                ->when(! $this->includeUnapproved, fn ($q) => $q->where('active', true))
                ->where('title', 'like', '%'.$this->search.'%')
                ->orderBy('title')
                ->limit(20)
                ->get();
        }
    }

    /**
     * Select a resource and dispatch event.
     */
    public function selectResource(int $resourceId): void
    {
        $this->selectedResourceId = $resourceId;
    }

    /**
     * Confirm selection and close.
     */
    public function confirmSelection(): void
    {
        if ($this->selectedResourceId) {
            $resource = Resource::find($this->selectedResourceId);

            $this->dispatch('resource-selected', [
                'resourceId' => $this->selectedResourceId,
                'stepIndex' => $this->stepIndex,
                'resource' => $resource ? [
                    'id' => $resource->id,
                    'title' => $resource->title,
                    'resource_type' => $resource->resource_type,
                    'active' => $resource->active,
                ] : null,
            ]);

            $this->closeModal();
        }
    }

    /**
     * Get available resource types.
     */
    public function getResourceTypesProperty(): array
    {
        return [
            'article' => 'Article',
            'video' => 'Video',
            'worksheet' => 'Worksheet',
            'activity' => 'Activity',
            'link' => 'Link',
            'document' => 'Document',
            'presentation' => 'Presentation',
            'audio' => 'Audio',
        ];
    }

    /**
     * Get available categories from existing resources.
     */
    public function getCategoriesProperty(): array
    {
        $user = auth()->user();

        return Resource::query()
            ->where('org_id', $user->org_id)
            ->whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->mapWithKeys(fn ($cat) => [$cat => ucfirst($cat)])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.components.resource-picker-modal', [
            'resources' => $this->resources,
            'resourceTypes' => $this->resourceTypes,
            'categories' => $this->categories,
        ]);
    }
}
