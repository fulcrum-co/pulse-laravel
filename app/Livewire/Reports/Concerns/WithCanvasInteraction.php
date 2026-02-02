<?php

namespace App\Livewire\Reports\Concerns;

trait WithCanvasInteraction
{
    // Canvas settings
    public int $gridSize = 20;

    public bool $showGrid = false;

    public bool $snapToGrid = true;

    public float $canvasZoom = 1.0;

    // Zoom presets for dropdown
    public array $zoomPresets = [
        ['value' => 0.5, 'label' => '50%'],
        ['value' => 0.75, 'label' => '75%'],
        ['value' => 1.0, 'label' => '100%'],
        ['value' => 1.25, 'label' => '125%'],
        ['value' => 1.5, 'label' => '150%'],
        ['value' => 2.0, 'label' => '200%'],
    ];

    // Multi-select support
    public array $selectedElementIds = [];

    // Clipboard for copy/paste
    public array $clipboard = [];

    /**
     * Set grid size (5-50px)
     */
    public function setGridSize(int $size): void
    {
        $this->gridSize = max(5, min(50, $size));
    }

    /**
     * Toggle grid visibility
     */
    public function toggleGrid(): void
    {
        $this->showGrid = ! $this->showGrid;
    }

    /**
     * Toggle snap to grid
     */
    public function toggleSnap(): void
    {
        $this->snapToGrid = ! $this->snapToGrid;
    }

    /**
     * Set zoom level (0.25 to 2.0)
     */
    public function setZoom(float $zoom): void
    {
        $this->canvasZoom = max(0.25, min(2.0, $zoom));
    }

    /**
     * Zoom in by 10%
     */
    public function zoomIn(): void
    {
        $this->setZoom($this->canvasZoom + 0.1);
    }

    /**
     * Zoom out by 10%
     */
    public function zoomOut(): void
    {
        $this->setZoom($this->canvasZoom - 0.1);
    }

    /**
     * Reset zoom to 100%
     */
    public function resetZoom(): void
    {
        $this->canvasZoom = 1.0;
    }

    /**
     * Fit canvas to show all elements
     */
    public function fitToScreen(): void
    {
        if (empty($this->elements)) {
            $this->canvasZoom = 1.0;

            return;
        }

        $maxX = 0;
        $maxY = 0;

        foreach ($this->elements as $el) {
            $right = ($el['position']['x'] ?? 0) + ($el['size']['width'] ?? 100);
            $bottom = ($el['position']['y'] ?? 0) + ($el['size']['height'] ?? 100);
            $maxX = max($maxX, $right);
            $maxY = max($maxY, $bottom);
        }

        // Add padding
        $maxX += 80;
        $maxY += 80;

        // Calculate zoom to fit (800px canvas width assumed)
        $canvasWidth = 800;
        $viewportWidth = 800; // Approximate viewport

        $this->canvasZoom = min($viewportWidth / $maxX, 1.0);
    }

    /**
     * Add element to multi-selection (Shift+click)
     */
    public function addToSelection(string $elementId): void
    {
        if (! in_array($elementId, $this->selectedElementIds)) {
            $this->selectedElementIds[] = $elementId;
        }
        $this->selectedElementId = $elementId;
    }

    /**
     * Toggle element in selection
     */
    public function toggleInSelection(string $elementId): void
    {
        if (in_array($elementId, $this->selectedElementIds)) {
            $this->selectedElementIds = array_values(array_filter(
                $this->selectedElementIds,
                fn ($id) => $id !== $elementId
            ));
            if ($this->selectedElementId === $elementId) {
                $this->selectedElementId = $this->selectedElementIds[0] ?? null;
            }
        } else {
            $this->addToSelection($elementId);
        }
    }

    /**
     * Select multiple elements
     */
    public function selectMultiple(array $elementIds): void
    {
        $this->selectedElementIds = $elementIds;
        $this->selectedElementId = count($elementIds) === 1 ? $elementIds[0] : null;
    }

    /**
     * Clear all selections
     */
    public function clearSelection(): void
    {
        $this->selectedElementIds = [];
        $this->selectedElementId = null;
    }

    /**
     * Select all elements
     */
    public function selectAll(): void
    {
        $this->selectedElementIds = array_column($this->elements, 'id');
        $this->selectedElementId = null;
    }

    /**
     * Copy selected elements to clipboard
     */
    public function copySelected(): void
    {
        $ids = ! empty($this->selectedElementIds) ? $this->selectedElementIds : ($this->selectedElementId ? [$this->selectedElementId] : []);

        $this->clipboard = array_filter($this->elements, fn ($el) => in_array($el['id'], $ids));
        $this->clipboard = array_values($this->clipboard);
    }

    /**
     * Cut selected elements (copy + delete)
     */
    public function cutSelected(): void
    {
        $this->copySelected();
        $this->deleteSelected();
    }

    /**
     * Paste elements from clipboard
     */
    public function pasteFromClipboard(): void
    {
        if (empty($this->clipboard)) {
            return;
        }

        $offset = 20; // Offset pasted elements slightly
        $newIds = [];

        foreach ($this->clipboard as $element) {
            $newElement = $element;
            $newElement['id'] = \Illuminate\Support\Str::uuid()->toString();
            $newElement['position']['x'] = ($element['position']['x'] ?? 0) + $offset;
            $newElement['position']['y'] = ($element['position']['y'] ?? 0) + $offset;

            $this->elements[] = $newElement;
            $newIds[] = $newElement['id'];
        }

        // Select pasted elements
        $this->selectedElementIds = $newIds;
        $this->selectedElementId = count($newIds) === 1 ? $newIds[0] : null;

        $this->pushHistory();
    }

    /**
     * Delete selected elements
     */
    public function deleteSelected(): void
    {
        $ids = ! empty($this->selectedElementIds) ? $this->selectedElementIds : ($this->selectedElementId ? [$this->selectedElementId] : []);

        if (empty($ids)) {
            return;
        }

        $this->elements = array_values(array_filter(
            $this->elements,
            fn ($el) => ! in_array($el['id'], $ids)
        ));

        $this->clearSelection();
        $this->pushHistory();
    }

    /**
     * Bring selected elements to front (increase z-index)
     */
    public function bringToFront(): void
    {
        $ids = ! empty($this->selectedElementIds) ? $this->selectedElementIds : ($this->selectedElementId ? [$this->selectedElementId] : []);

        if (empty($ids)) {
            return;
        }

        // Remove selected elements and add them at the end
        $selected = array_filter($this->elements, fn ($el) => in_array($el['id'], $ids));
        $others = array_filter($this->elements, fn ($el) => ! in_array($el['id'], $ids));

        $this->elements = array_values(array_merge($others, $selected));
        $this->pushHistory();
    }

    /**
     * Send selected elements to back (decrease z-index)
     */
    public function sendToBack(): void
    {
        $ids = ! empty($this->selectedElementIds) ? $this->selectedElementIds : ($this->selectedElementId ? [$this->selectedElementId] : []);

        if (empty($ids)) {
            return;
        }

        // Remove selected elements and add them at the beginning
        $selected = array_filter($this->elements, fn ($el) => in_array($el['id'], $ids));
        $others = array_filter($this->elements, fn ($el) => ! in_array($el['id'], $ids));

        $this->elements = array_values(array_merge($selected, $others));
        $this->pushHistory();
    }

    /**
     * Align selected elements
     */
    public function alignSelected(string $direction): void
    {
        $ids = ! empty($this->selectedElementIds) ? $this->selectedElementIds : [];

        if (count($ids) < 2) {
            return;
        }

        $selected = array_filter($this->elements, fn ($el) => in_array($el['id'], $ids));

        switch ($direction) {
            case 'left':
                $minX = min(array_column(array_column($selected, 'position'), 'x'));
                foreach ($this->elements as &$el) {
                    if (in_array($el['id'], $ids)) {
                        $el['position']['x'] = $minX;
                    }
                }
                break;

            case 'center-h':
                $positions = [];
                foreach ($selected as $el) {
                    $positions[] = $el['position']['x'] + ($el['size']['width'] ?? 100) / 2;
                }
                $centerX = array_sum($positions) / count($positions);
                foreach ($this->elements as &$el) {
                    if (in_array($el['id'], $ids)) {
                        $el['position']['x'] = $centerX - ($el['size']['width'] ?? 100) / 2;
                    }
                }
                break;

            case 'right':
                $maxRight = 0;
                foreach ($selected as $el) {
                    $right = $el['position']['x'] + ($el['size']['width'] ?? 100);
                    $maxRight = max($maxRight, $right);
                }
                foreach ($this->elements as &$el) {
                    if (in_array($el['id'], $ids)) {
                        $el['position']['x'] = $maxRight - ($el['size']['width'] ?? 100);
                    }
                }
                break;

            case 'top':
                $minY = min(array_column(array_column($selected, 'position'), 'y'));
                foreach ($this->elements as &$el) {
                    if (in_array($el['id'], $ids)) {
                        $el['position']['y'] = $minY;
                    }
                }
                break;

            case 'center-v':
                $positions = [];
                foreach ($selected as $el) {
                    $positions[] = $el['position']['y'] + ($el['size']['height'] ?? 50) / 2;
                }
                $centerY = array_sum($positions) / count($positions);
                foreach ($this->elements as &$el) {
                    if (in_array($el['id'], $ids)) {
                        $el['position']['y'] = $centerY - ($el['size']['height'] ?? 50) / 2;
                    }
                }
                break;

            case 'bottom':
                $maxBottom = 0;
                foreach ($selected as $el) {
                    $bottom = $el['position']['y'] + ($el['size']['height'] ?? 50);
                    $maxBottom = max($maxBottom, $bottom);
                }
                foreach ($this->elements as &$el) {
                    if (in_array($el['id'], $ids)) {
                        $el['position']['y'] = $maxBottom - ($el['size']['height'] ?? 50);
                    }
                }
                break;
        }

        $this->pushHistory();
    }

    /**
     * Distribute selected elements evenly
     */
    public function distributeSelected(string $direction): void
    {
        $ids = ! empty($this->selectedElementIds) ? $this->selectedElementIds : [];

        if (count($ids) < 3) {
            return;
        }

        $selected = array_values(array_filter($this->elements, fn ($el) => in_array($el['id'], $ids)));

        if ($direction === 'horizontal') {
            // Sort by X position
            usort($selected, fn ($a, $b) => $a['position']['x'] <=> $b['position']['x']);

            $first = $selected[0];
            $last = end($selected);

            $startX = $first['position']['x'];
            $endX = $last['position']['x'];
            $totalWidth = $endX - $startX;
            $step = $totalWidth / (count($selected) - 1);

            foreach ($selected as $i => $sel) {
                foreach ($this->elements as &$el) {
                    if ($el['id'] === $sel['id'] && $i > 0 && $i < count($selected) - 1) {
                        $el['position']['x'] = $startX + ($step * $i);
                    }
                }
            }
        } else {
            // Sort by Y position
            usort($selected, fn ($a, $b) => $a['position']['y'] <=> $b['position']['y']);

            $first = $selected[0];
            $last = end($selected);

            $startY = $first['position']['y'];
            $endY = $last['position']['y'];
            $totalHeight = $endY - $startY;
            $step = $totalHeight / (count($selected) - 1);

            foreach ($selected as $i => $sel) {
                foreach ($this->elements as &$el) {
                    if ($el['id'] === $sel['id'] && $i > 0 && $i < count($selected) - 1) {
                        $el['position']['y'] = $startY + ($step * $i);
                    }
                }
            }
        }

        $this->pushHistory();
    }
}
