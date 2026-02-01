<?php

namespace App\Livewire\Admin\Concerns;

trait WithBulkSelection
{
    public array $selectedItems = [];

    public bool $selectAll = false;

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
            $this->selectedItems = array_values(
                array_filter($this->selectedItems, fn ($i) => $i !== $id),
            );
        } else {
            $this->selectedItems[] = $id;
        }

        $this->selectAll = false;
    }

    public function clearSelection(): void
    {
        $this->selectedItems = [];
        $this->selectAll = false;
    }
}
