<?php

namespace App\Livewire\Reports\Concerns;

trait WithHistory
{
    protected array $history = [];

    protected int $historyIndex = -1;

    protected int $maxHistory = 50;

    protected function pushHistory(): void
    {
        // Truncate redo history
        $this->history = array_slice($this->history, 0, $this->historyIndex + 1);

        // Add current state
        $this->history[] = json_encode($this->elements);
        $this->historyIndex++;

        // Limit history size
        if (count($this->history) > $this->maxHistory) {
            array_shift($this->history);
            $this->historyIndex--;
        }
    }

    public function undo(): void
    {
        if ($this->historyIndex > 0) {
            $this->historyIndex--;
            $this->elements = json_decode($this->history[$this->historyIndex], true);
            $this->selectedElementId = null;
        }
    }

    public function redo(): void
    {
        if ($this->historyIndex < count($this->history) - 1) {
            $this->historyIndex++;
            $this->elements = json_decode($this->history[$this->historyIndex], true);
            $this->selectedElementId = null;
        }
    }

    public function canUndo(): bool
    {
        return $this->historyIndex > 0;
    }

    public function canRedo(): bool
    {
        return $this->historyIndex < count($this->history) - 1;
    }
}
