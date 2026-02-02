<?php

namespace App\Livewire\Reports\Concerns;

use Illuminate\Support\Str;

trait WithMultiPageSupport
{
    // Multi-page state
    public int $currentPageIndex = 0;

    public array $pages = [];

    public bool $showPageThumbnails = false;

    /**
     * Initialize pages from elements (for backward compatibility).
     */
    public function initializePages(): void
    {
        if (empty($this->pages) && ! empty($this->elements)) {
            // Convert single-page to multi-page structure
            $this->pages = [
                [
                    'id' => Str::uuid()->toString(),
                    'name' => 'Page 1',
                    'elements' => $this->elements,
                    'settings' => [
                        'width' => 800,
                        'height' => 1056,
                    ],
                ],
            ];
        } elseif (empty($this->pages)) {
            // Create empty first page
            $this->pages = [
                [
                    'id' => Str::uuid()->toString(),
                    'name' => 'Page 1',
                    'elements' => [],
                    'settings' => [
                        'width' => 800,
                        'height' => 1056,
                    ],
                ],
            ];
        }
    }

    /**
     * Get current page elements.
     */
    public function getCurrentPageElements(): array
    {
        return $this->pages[$this->currentPageIndex]['elements'] ?? $this->elements;
    }

    /**
     * Switch to a specific page.
     */
    public function switchToPage(int $index): void
    {
        if ($index >= 0 && $index < count($this->pages)) {
            // Save current elements to current page
            if (isset($this->pages[$this->currentPageIndex])) {
                $this->pages[$this->currentPageIndex]['elements'] = $this->elements;
            }

            $this->currentPageIndex = $index;

            // Load elements from new page
            $this->elements = $this->pages[$this->currentPageIndex]['elements'] ?? [];

            // Clear selection
            $this->selectedElementId = null;
            $this->selectedElementIds = [];
        }
    }

    /**
     * Add a new page.
     */
    public function addPage(): void
    {
        // Save current page elements first
        if (isset($this->pages[$this->currentPageIndex])) {
            $this->pages[$this->currentPageIndex]['elements'] = $this->elements;
        }

        $newPage = [
            'id' => Str::uuid()->toString(),
            'name' => 'Page '.($this->getTotalPages() + 1),
            'elements' => [],
            'settings' => [
                'width' => 800,
                'height' => 1056,
            ],
        ];

        $this->pages[] = $newPage;

        // Switch to new page
        $this->switchToPage(count($this->pages) - 1);

        $this->pushHistory();
    }

    /**
     * Delete a page.
     */
    public function deletePage(int $index): void
    {
        if (count($this->pages) <= 1) {
            return; // Can't delete the last page
        }

        if ($index < 0 || $index >= count($this->pages)) {
            return;
        }

        // Remove the page
        array_splice($this->pages, $index, 1);

        // Adjust current page index if needed
        if ($this->currentPageIndex >= count($this->pages)) {
            $this->currentPageIndex = count($this->pages) - 1;
        }

        // Load elements from current page
        $this->elements = $this->pages[$this->currentPageIndex]['elements'] ?? [];

        // Update page names
        foreach ($this->pages as $i => &$page) {
            if (! isset($page['name']) || preg_match('/^Page \d+$/', $page['name'])) {
                $page['name'] = 'Page '.($i + 1);
            }
        }

        $this->pushHistory();
    }

    /**
     * Duplicate a page.
     */
    public function duplicatePage(int $index): void
    {
        if ($index < 0 || $index >= count($this->pages)) {
            return;
        }

        // Save current page first
        if (isset($this->pages[$this->currentPageIndex])) {
            $this->pages[$this->currentPageIndex]['elements'] = $this->elements;
        }

        $sourcePage = $this->pages[$index];

        // Deep copy elements with new IDs
        $newElements = array_map(function ($element) {
            $newElement = $element;
            $newElement['id'] = Str::uuid()->toString();

            return $newElement;
        }, $sourcePage['elements'] ?? []);

        $newPage = [
            'id' => Str::uuid()->toString(),
            'name' => $sourcePage['name'].' (Copy)',
            'elements' => $newElements,
            'settings' => $sourcePage['settings'] ?? [
                'width' => 800,
                'height' => 1056,
            ],
        ];

        // Insert after the source page
        array_splice($this->pages, $index + 1, 0, [$newPage]);

        // Switch to new page
        $this->switchToPage($index + 1);

        $this->pushHistory();
    }

    /**
     * Reorder pages.
     */
    public function reorderPages(array $newOrder): void
    {
        // Save current page first
        if (isset($this->pages[$this->currentPageIndex])) {
            $this->pages[$this->currentPageIndex]['elements'] = $this->elements;
        }

        $currentPageId = $this->pages[$this->currentPageIndex]['id'] ?? null;

        // Reorder pages
        $reorderedPages = [];
        foreach ($newOrder as $pageId) {
            foreach ($this->pages as $page) {
                if ($page['id'] === $pageId) {
                    $reorderedPages[] = $page;
                    break;
                }
            }
        }

        if (count($reorderedPages) === count($this->pages)) {
            $this->pages = $reorderedPages;

            // Find new index of current page
            if ($currentPageId) {
                foreach ($this->pages as $i => $page) {
                    if ($page['id'] === $currentPageId) {
                        $this->currentPageIndex = $i;
                        break;
                    }
                }
            }

            $this->pushHistory();
        }
    }

    /**
     * Rename a page.
     */
    public function renamePage(int $index, string $name): void
    {
        if ($index >= 0 && $index < count($this->pages)) {
            $this->pages[$index]['name'] = $name;
            $this->pushHistory();
        }
    }

    /**
     * Toggle page thumbnails visibility.
     */
    public function togglePageThumbnails(): void
    {
        $this->showPageThumbnails = ! $this->showPageThumbnails;
    }

    /**
     * Get total number of pages.
     */
    public function getTotalPages(): int
    {
        return count($this->pages);
    }

    /**
     * Get page at index.
     */
    public function getPage(int $index): ?array
    {
        return $this->pages[$index] ?? null;
    }

    /**
     * Move page forward (increase index).
     */
    public function movePageForward(int $index): void
    {
        if ($index < count($this->pages) - 1) {
            $temp = $this->pages[$index];
            $this->pages[$index] = $this->pages[$index + 1];
            $this->pages[$index + 1] = $temp;

            // Adjust current index if needed
            if ($this->currentPageIndex === $index) {
                $this->currentPageIndex = $index + 1;
            } elseif ($this->currentPageIndex === $index + 1) {
                $this->currentPageIndex = $index;
            }

            $this->pushHistory();
        }
    }

    /**
     * Move page backward (decrease index).
     */
    public function movePageBackward(int $index): void
    {
        if ($index > 0) {
            $temp = $this->pages[$index];
            $this->pages[$index] = $this->pages[$index - 1];
            $this->pages[$index - 1] = $temp;

            // Adjust current index if needed
            if ($this->currentPageIndex === $index) {
                $this->currentPageIndex = $index - 1;
            } elseif ($this->currentPageIndex === $index - 1) {
                $this->currentPageIndex = $index;
            }

            $this->pushHistory();
        }
    }
}
