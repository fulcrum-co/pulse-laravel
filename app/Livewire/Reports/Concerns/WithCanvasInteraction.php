<?php

namespace App\Livewire\Reports\Concerns;

trait WithCanvasInteraction
{
    public float $zoom = 1.0;
    public float $canvasZoom = 1.0;
    public bool $showGrid = true;
    public bool $snapToGrid = true;
    public int $gridSize = 20;
    public bool $isPanning = false;
    public array $panOffset = ['x' => 0, 'y' => 0];

    public function zoomIn(): void
    {
        $this->zoom = min(2.0, $this->zoom + 0.1);
        $this->canvasZoom = $this->zoom;
    }

    public function zoomOut(): void
    {
        $this->zoom = max(0.5, $this->zoom - 0.1);
        $this->canvasZoom = $this->zoom;
    }

    public function resetZoom(): void
    {
        $this->zoom = 1.0;
        $this->canvasZoom = 1.0;
    }

    public function setZoom(float $level): void
    {
        $this->zoom = max(0.5, min(2.0, $level));
        $this->canvasZoom = $this->zoom;
    }

    public function toggleGrid(): void
    {
        $this->showGrid = !$this->showGrid;
    }

    public function toggleSnapToGrid(): void
    {
        $this->snapToGrid = !$this->snapToGrid;
    }

    public function setGridSize(int $size): void
    {
        $this->gridSize = max(5, min(50, $size));
    }

    public function snapPosition(int $value): int
    {
        if (!$this->snapToGrid) {
            return $value;
        }

        return round($value / $this->gridSize) * $this->gridSize;
    }

    public function startPan(): void
    {
        $this->isPanning = true;
    }

    public function endPan(): void
    {
        $this->isPanning = false;
    }

    public function updatePanOffset(int $x, int $y): void
    {
        $this->panOffset = ['x' => $x, 'y' => $y];
    }

    public function resetPan(): void
    {
        $this->panOffset = ['x' => 0, 'y' => 0];
    }

    public function fitToScreen(): void
    {
        $this->zoom = 1.0;
        $this->resetPan();
    }
}
