<?php

namespace App\Livewire\Reports\Concerns;

use Illuminate\Support\Str;

trait WithElementManagement
{
    public array $elements = [];

    public ?string $selectedElementId = null;

    public function selectElement(?string $elementId): void
    {
        $this->selectedElementId = $elementId;
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
}
