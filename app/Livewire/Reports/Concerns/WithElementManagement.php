<?php

namespace App\Livewire\Reports\Concerns;

use Illuminate\Support\Str;

trait WithElementManagement
{
    public array $elements = [];

    public ?string $selectedElementId = null;

    public array $selectedElementIds = [];

    public function selectElement(?string $elementId): void
    {
        $this->selectedElementId = $elementId;
        // Clear multi-selection when selecting a single element
        if ($elementId !== null) {
            $this->selectedElementIds = [$elementId];
        } else {
            $this->selectedElementIds = [];
        }
    }

    public function toggleInSelection(string $elementId): void
    {
        if (in_array($elementId, $this->selectedElementIds)) {
            $this->selectedElementIds = array_values(array_filter(
                $this->selectedElementIds,
                fn ($id) => $id !== $elementId
            ));
        } else {
            $this->selectedElementIds[] = $elementId;
        }

        // Update selected element to last in selection
        $this->selectedElementId = end($this->selectedElementIds) ?: null;
    }

    public function clearSelection(): void
    {
        $this->selectedElementId = null;
        $this->selectedElementIds = [];
    }

    public function getSelectedElement(): ?array
    {
        if (! $this->selectedElementId) {
            return null;
        }

        return collect($this->elements)->firstWhere('id', $this->selectedElementId);
    }

    public function addElement(string $type, ?array $config = null): void
    {
        $element = $this->createDefaultElement($type, $config);
        $this->elements[] = $element;
        $this->selectedElementId = $element['id'];
        $this->pushHistory();
    }

    public function updateElementPosition(string $elementId, float $x, float $y): void
    {
        foreach ($this->elements as &$element) {
            if ($element['id'] === $elementId) {
                // Don't update position if element is locked
                if ($element['config']['locked'] ?? false) {
                    return;
                }
                $element['position']['x'] = max(0, (int) $x);
                $element['position']['y'] = max(0, (int) $y);
                break;
            }
        }
    }

    public function updateElementSize(string $elementId, float $width, float $height): void
    {
        foreach ($this->elements as &$element) {
            if ($element['id'] === $elementId) {
                $element['size']['width'] = max(50, (int) $width);
                $element['size']['height'] = max(30, (int) $height);
                break;
            }
        }
    }

    public function commitElementChange(): void
    {
        $this->pushHistory();
    }

    public function updateElementConfig(string $elementId, array $config): void
    {
        foreach ($this->elements as &$element) {
            if ($element['id'] === $elementId) {
                $element['config'] = array_merge($element['config'] ?? [], $config);
                break;
            }
        }
        $this->pushHistory();
    }

    public function updateElementStyles(string $elementId, array $styles): void
    {
        foreach ($this->elements as &$element) {
            if ($element['id'] === $elementId) {
                $element['styles'] = array_merge($element['styles'] ?? [], $styles);
                break;
            }
        }
        $this->pushHistory();
    }

    public function duplicateElement(string $elementId): void
    {
        $original = collect($this->elements)->firstWhere('id', $elementId);

        if ($original) {
            $duplicate = $original;
            $duplicate['id'] = Str::uuid()->toString();
            $duplicate['position']['x'] += 20;
            $duplicate['position']['y'] += 20;

            $this->elements[] = $duplicate;
            $this->selectedElementId = $duplicate['id'];
            $this->pushHistory();
        }
    }

    public function deleteElement(string $elementId): void
    {
        $this->elements = array_values(array_filter(
            $this->elements,
            fn ($el) => $el['id'] !== $elementId,
        ));

        if ($this->selectedElementId === $elementId) {
            $this->selectedElementId = null;
        }

        $this->pushHistory();
    }

    public function deleteSelectedElement(): void
    {
        if ($this->selectedElementId) {
            $this->deleteElement($this->selectedElementId);
        }
    }

    public function moveElementUp(string $elementId): void
    {
        $index = collect($this->elements)->search(fn ($el) => $el['id'] === $elementId);

        if ($index !== false && $index < count($this->elements) - 1) {
            $temp = $this->elements[$index];
            $this->elements[$index] = $this->elements[$index + 1];
            $this->elements[$index + 1] = $temp;
            $this->pushHistory();
        }
    }

    public function moveElementDown(string $elementId): void
    {
        $index = collect($this->elements)->search(fn ($el) => $el['id'] === $elementId);

        if ($index !== false && $index > 0) {
            $temp = $this->elements[$index];
            $this->elements[$index] = $this->elements[$index - 1];
            $this->elements[$index - 1] = $temp;
            $this->pushHistory();
        }
    }

    protected function getNextY(): int
    {
        if (empty($this->elements)) {
            return 40;
        }

        $maxY = 0;
        foreach ($this->elements as $element) {
            $bottom = ($element['position']['y'] ?? 0) + ($element['size']['height'] ?? 100);
            if ($bottom > $maxY) {
                $maxY = $bottom;
            }
        }

        return $maxY + 20;
    }

    /**
     * Toggle element lock status
     */
    public function toggleElementLock(string $elementId): void
    {
        foreach ($this->elements as &$element) {
            if ($element['id'] === $elementId) {
                $element['config']['locked'] = ! ($element['config']['locked'] ?? false);
                break;
            }
        }
        $this->pushHistory();
    }

    /**
     * Toggle element visibility (for layers panel)
     */
    public function toggleElementVisibility(string $elementId): void
    {
        foreach ($this->elements as &$element) {
            if ($element['id'] === $elementId) {
                $element['config']['hidden'] = ! ($element['config']['hidden'] ?? false);
                break;
            }
        }
        $this->pushHistory();
    }

    /**
     * Reorder elements (for layers panel drag-drop)
     */
    public function reorderElements(array $orderedIds): void
    {
        $reordered = [];
        foreach ($orderedIds as $id) {
            $element = collect($this->elements)->firstWhere('id', $id);
            if ($element) {
                $reordered[] = $element;
            }
        }

        // Only update if we have the same number of elements
        if (count($reordered) === count($this->elements)) {
            $this->elements = $reordered;
            $this->pushHistory();
        }
    }

    /**
     * Get element display name for layers panel
     */
    public function getElementDisplayName(array $element): string
    {
        $type = $element['type'] ?? 'unknown';
        $icons = [
            'text' => '📝',
            'chart' => '📊',
            'metric_card' => '📈',
            'table' => '📋',
            'ai_text' => '✨',
            'image' => '🖼️',
            'spacer' => '↕️',
        ];

        $icon = $icons[$type] ?? '📦';
        $title = $element['config']['title'] ?? $element['config']['label'] ?? ucfirst(str_replace('_', ' ', $type));

        return $icon.' '.substr($title, 0, 20).(strlen($title) > 20 ? '...' : '');
    }
}
