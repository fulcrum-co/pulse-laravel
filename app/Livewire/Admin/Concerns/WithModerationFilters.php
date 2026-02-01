<?php

namespace App\Livewire\Admin\Concerns;

trait WithModerationFilters
{
    public string $statusFilter = 'needs_review';

    public string $contentTypeFilter = '';

    public string $sortBy = 'newest';

    public string $sortDirection = 'desc';

    public string $search = '';

    public string $viewMode = 'list';

    public string $assignmentFilter = 'all';

    public string $priorityFilter = '';

    protected function getFilterQueryString(): array
    {
        return [
            'statusFilter' => ['except' => 'needs_review'],
            'contentTypeFilter' => ['except' => ''],
            'assignmentFilter' => ['except' => 'all'],
            'priorityFilter' => ['except' => ''],
            'sortBy' => ['except' => 'newest'],
            'sortDirection' => ['except' => 'desc'],
            'search' => ['except' => ''],
            'viewMode' => ['except' => 'list'],
        ];
    }

    public function updatedStatusFilter(): void
    {
        $this->resetFilterState();
    }

    public function updatedContentTypeFilter(): void
    {
        $this->resetFilterState();
    }

    public function updatedAssignmentFilter(): void
    {
        $this->resetFilterState();
    }

    public function updatedSearch(): void
    {
        $this->resetFilterState();
    }

    public function updatedPriorityFilter(): void
    {
        $this->resetFilterState();
    }

    /**
     * Reset pagination and selection when filters change.
     */
    protected function resetFilterState(): void
    {
        $this->resetPage();
        $this->selectedItems = [];
        $this->selectAll = false;
    }

    public function sortByColumn(string $field): void
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'desc';
        }
    }
}
