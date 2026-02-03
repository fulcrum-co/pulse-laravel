<?php

namespace App\Livewire\Reports;

use App\Livewire\Reports\Concerns\WithCanvasInteraction;
use App\Livewire\Reports\Concerns\WithChartData;
use App\Livewire\Reports\Concerns\WithCollaboration;
use App\Livewire\Reports\Concerns\WithComments;
use App\Livewire\Reports\Concerns\WithElementDefaults;
use App\Livewire\Reports\Concerns\WithElementManagement;
use App\Livewire\Reports\Concerns\WithHistory;
use App\Livewire\Reports\Concerns\WithMultiPageSupport;
use App\Livewire\Reports\Concerns\WithReportPersistence;
use App\Livewire\Reports\Concerns\WithSmartBlocks;
use App\Models\CustomReport;
use App\Models\Student;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ReportBuilder extends Component
{
    use WithCanvasInteraction;
    use WithChartData;
    use WithCollaboration;
    use WithComments;
    use WithElementDefaults;
    use WithElementManagement;
    use WithHistory;
    use WithMultiPageSupport;
    use WithReportPersistence;
    use WithSmartBlocks;

    protected $listeners = [
        'selectElement',
        'updateElementPosition',
        'updateElementSize',
        'commitElementChange',
        'deleteSelectedElement',
        'undo',
        'redo',
        'save',
        'chartsUpdated' => '$refresh',
        'exportPdf',
        'chartImagesReady',
        // Zoom events
        'zoomIn',
        'zoomOut',
        'resetZoom',
        // Multi-page events
        'switchToPage',
        'addPage',
        'deletePage',
        'duplicatePage',
        'reorderPages',
        // Collaboration events
        'broadcastCursor',
        'broadcastSelection',
        // Comment events
        'addComment',
        'resolveComment',
        'deleteComment',
    ];

    // Global filters
    public array $filters = [
        'date_range' => '6_months',
        'start_date' => null,
        'end_date' => null,
        'scope' => 'individual', // individual, contact_list, organization
        'contact_type' => 'contact',
        'contact_id' => null,
        'selected_contacts' => [], // Multi-select contacts for individual scope
        // List comparison mode (single = one list, compare = side-by-side, multi = N-list)
        'list_mode' => 'single', // 'single', 'compare', or 'multi'
        'list_id' => null, // Single list mode
        'list_a_id' => null, // Compare mode - left side
        'list_b_id' => null, // Compare mode - right side
        'list_ids' => [], // Multi-list mode - array of list IDs for N-list comparison
        'show_differences_only' => false, // Filter to only show metrics that differ
        'grade_level' => null,
        'risk_level' => null,
    ];

    // Contact search query for filtering
    public string $contactSearchQuery = '';

    // UI state
    public bool $showTemplateGallery = false;

    public bool $showCanvasSelector = false; // Step 1: Choose canvas type

    public int $canvasSelectorStep = 1; // 1 = type selection, 2 = size selection

    public string $canvasMode = 'document'; // document, widget, social, custom

    public bool $showBrandingPanel = false;

    // Custom dimensions for custom canvas mode
    public int $customWidth = 800;

    public int $customHeight = 600;

    public bool $showFilterBar = false;

    public string $activeTab = 'elements';

    // Canva-style sidebar panel state
    public string $activeSidebarPanel = 'elements'; // templates, elements, data, smart_blocks, design, layers
    public string $sidebarTab = 'elements'; // For canva-sidebar.blade.php compatibility

    public bool $sidebarExpanded = true;

    // Phase 6: Wow factor modals
    public bool $showShortcutsModal = false;

    // Inline text editing state
    public ?string $editingTextElementId = null;

    // Available templates
    public array $templates = [];

    public function mount(?CustomReport $report = null, array $templates = []): void
    {
        $this->templates = $templates;

        if ($report && $report->exists) {
            $this->loadReport($report);
            // Initialize collaboration features
            $this->initializeCollaboration();
            $this->loadComments();
        } else {
            // Show canvas type selector first for new reports
            $this->showCanvasSelector = true;
            $this->filters['start_date'] = now()->subMonths(6)->format('Y-m-d');
            $this->filters['end_date'] = now()->format('Y-m-d');
            // Initialize with a blank page
            $this->initializePages();
        }
    }

    /**
     * Step 1: Select canvas type and move to step 2
     */
    public function selectCanvasType(string $type): void
    {
        $this->canvasMode = $type;
        $this->canvasSelectorStep = 2;
    }

    /**
     * Step 2: Select size and finalize canvas setup
     */
    public function selectCanvasSize(string $size): void
    {
        $dimensions = $this->getCanvasDimensions($this->canvasMode, $size);

        $this->pageSettings['orientation'] = $dimensions['width'] > $dimensions['height'] ? 'landscape' : 'portrait';
        $this->pageSettings['size'] = $size;

        if (isset($this->pages[$this->currentPageIndex])) {
            $this->pages[$this->currentPageIndex]['settings'] = [
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
            ];
        }

        $this->showCanvasSelector = false;
        $this->canvasSelectorStep = 1;
        $this->showTemplateGallery = true;
    }

    /**
     * Select custom dimensions
     */
    public function selectCustomSize(): void
    {
        if (isset($this->pages[$this->currentPageIndex])) {
            $this->pages[$this->currentPageIndex]['settings'] = [
                'width' => max(100, min(2000, $this->customWidth)),
                'height' => max(100, min(2000, $this->customHeight)),
            ];
        }

        $this->pageSettings['orientation'] = $this->customWidth > $this->customHeight ? 'landscape' : 'portrait';
        $this->pageSettings['size'] = 'custom';

        $this->showCanvasSelector = false;
        $this->canvasSelectorStep = 1;
        $this->showTemplateGallery = true;
    }

    /**
     * Go back to step 1
     */
    public function backToCanvasTypeSelector(): void
    {
        $this->canvasSelectorStep = 1;
    }

    /**
     * Get canvas dimensions based on type and size
     */
    protected function getCanvasDimensions(string $type, string $size): array
    {
        $sizes = [
            'document' => [
                'letter' => ['width' => 816, 'height' => 1056],      // 8.5" × 11" at 96dpi
                'a4' => ['width' => 794, 'height' => 1123],          // 210mm × 297mm at 96dpi
                'legal' => ['width' => 816, 'height' => 1344],       // 8.5" × 14" at 96dpi
                'tabloid' => ['width' => 1056, 'height' => 1632],    // 11" × 17" at 96dpi
            ],
            'widget' => [
                'small' => ['width' => 300, 'height' => 250],        // Medium Rectangle
                'medium' => ['width' => 728, 'height' => 90],        // Leaderboard
                'large' => ['width' => 970, 'height' => 250],        // Billboard
                'skyscraper' => ['width' => 160, 'height' => 600],   // Wide Skyscraper
                'square' => ['width' => 300, 'height' => 300],       // Square
            ],
            'social' => [
                'instagram_post' => ['width' => 1080, 'height' => 1080],
                'instagram_story' => ['width' => 1080, 'height' => 1920],
                'facebook_post' => ['width' => 1200, 'height' => 630],
                'twitter' => ['width' => 1600, 'height' => 900],
                'linkedin' => ['width' => 1200, 'height' => 627],
                'youtube_thumbnail' => ['width' => 1280, 'height' => 720],
            ],
        ];

        return $sizes[$type][$size] ?? ['width' => 800, 'height' => 600];
    }

    /**
     * Legacy method for backward compatibility
     */
    public function selectCanvasMode(string $mode): void
    {
        $this->selectCanvasType($mode);
    }

    /**
     * Go back from template gallery to canvas type selector
     */
    public function backToCanvasSelector(): void
    {
        $this->showTemplateGallery = false;
        $this->showCanvasSelector = true;
    }

    public function loadTemplate(string $templateId): void
    {
        $template = collect($this->templates)->firstWhere('id', $templateId);

        if ($template) {
            $elements = $template['layout'] ?? [];
            $this->reportType = $template['type'] ?? 'custom';
            $this->reportName = $template['name'] ?? 'Untitled Report';
            $this->showTemplateGallery = false;

            foreach ($elements as &$element) {
                $element['id'] = Str::uuid()->toString();
            }

            // Initialize as single-page with template elements
            $this->pages = [
                [
                    'id' => 'page-1',
                    'name' => 'Page 1',
                    'elements' => $elements,
                    'settings' => ['width' => 816, 'height' => 1056],
                ],
            ];
            $this->currentPageIndex = 0;
            $this->elements = $elements;

            $this->pushHistory();
        }
    }

    public function startBlank(): void
    {
        $this->elements = [];
        $this->reportType = 'custom';
        $this->showTemplateGallery = false;
        // Reset to single blank page
        $this->pages = [
            [
                'id' => 'page-1',
                'name' => 'Page 1',
                'elements' => [],
                'settings' => ['width' => 816, 'height' => 1056],
            ],
        ];
        $this->currentPageIndex = 0;
        $this->pushHistory();
    }

    public function updateFilters(array $newFilters): void
    {
        $this->filters = array_merge($this->filters, $newFilters);
    }

    #[Computed]
    public function availableStudents(): array
    {
        $user = auth()->user();

        $query = Student::where('org_id', $user->org_id)
            ->with('user');

        // Apply search filter if present
        if (! empty($this->contactSearchQuery)) {
            $search = $this->contactSearchQuery;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query->limit(100)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->user?->full_name ?? 'Unknown',
            ])
            ->toArray();
    }

    #[Computed]
    public function availableContactLists(): array
    {
        $user = auth()->user();

        // Check if ContactList model exists, otherwise return empty
        if (! class_exists(\App\Models\ContactList::class)) {
            return [];
        }

        return \App\Models\ContactList::where('org_id', $user->org_id)
            ->orderBy('name')
            ->get()
            ->map(fn ($list) => [
                'id' => $list->id,
                'name' => $list->name,
                'count' => $list->member_count,
            ])
            ->toArray();
    }

    public function createContactList(string $name, ?string $slot = null): void
    {
        if (empty(trim($name))) {
            $this->dispatch('notify', type: 'error', message: 'Please enter a list name');

            return;
        }

        // Check if ContactList model exists
        if (! class_exists(\App\Models\ContactList::class)) {
            $this->dispatch('notify', type: 'error', message: 'Contact lists are not available');

            return;
        }

        $user = auth()->user();

        $list = \App\Models\ContactList::create([
            'org_id' => $user->org_id,
            'name' => trim($name),
            'created_by' => $user->id,
        ]);

        // Auto-select the newly created list based on the slot/mode
        if ($slot === 'single' || ($slot === null && ($this->filters['list_mode'] ?? 'single') === 'single')) {
            $this->filters['list_id'] = $list->id;
        } elseif ($slot === 'list_a') {
            $this->filters['list_a_id'] = $list->id;
        } elseif ($slot === 'list_b') {
            $this->filters['list_b_id'] = $list->id;
        } elseif ($slot === 'multi') {
            // Add to multi-list comparison
            $this->filters['list_ids'][] = $list->id;
        } else {
            // Legacy: add to selected_list_ids array
            $this->filters['selected_list_ids'][] = $list->id;
        }

        $this->dispatch('notify', type: 'success', message: 'Contact list created');
        $this->dispatch('listCreated', id: $list->id, name: $list->name);
    }

    public function setContactFilter(?int $contactId, string $contactType = 'contact'): void
    {
        $this->filters['contact_id'] = $contactId;
        $this->filters['contact_type'] = $contactType;
        $this->dispatch('chartsUpdated');
    }

    public function setDateRange(string $range): void
    {
        $this->filters['date_range'] = $range;
        $this->dispatch('chartsUpdated');
    }

    /**
     * Add a list to the multi-list comparison.
     */
    public function addListToComparison(int $listId): void
    {
        if (! in_array($listId, $this->filters['list_ids'])) {
            $this->filters['list_ids'][] = $listId;
            $this->dispatch('chartsUpdated');
        }
    }

    /**
     * Remove a list from the multi-list comparison.
     */
    public function removeListFromComparison(int $listId): void
    {
        $this->filters['list_ids'] = array_values(
            array_filter($this->filters['list_ids'], fn ($id) => $id !== $listId)
        );
        $this->dispatch('chartsUpdated');
    }

    /**
     * Toggle "show differences only" filter for multi-list comparison.
     */
    public function toggleShowDifferencesOnly(): void
    {
        $this->filters['show_differences_only'] = ! ($this->filters['show_differences_only'] ?? false);
        $this->dispatch('chartsUpdated');
    }

    /**
     * Toggle a list in the multi-list comparison (add if not present, remove if present).
     */
    public function toggleListInComparison(int $listId): void
    {
        if (in_array($listId, $this->filters['list_ids'])) {
            $this->removeListFromComparison($listId);
        } else {
            $this->addListToComparison($listId);
        }
    }

    /**
     * Select all available lists for multi-list comparison.
     */
    public function selectAllLists(): void
    {
        $availableLists = $this->availableContactLists;
        $this->filters['list_ids'] = collect($availableLists)->pluck('id')->toArray();
        $this->dispatch('chartsUpdated');
    }

    /**
     * Clear all selected lists from multi-list comparison.
     */
    public function clearAllLists(): void
    {
        $this->filters['list_ids'] = [];
        $this->dispatch('chartsUpdated');
    }

    /**
     * Get the selected lists with their details for multi-list mode.
     */
    #[Computed]
    public function selectedListsForComparison(): array
    {
        if (empty($this->filters['list_ids'])) {
            return [];
        }

        if (! class_exists(\App\Models\ContactList::class)) {
            return [];
        }

        return \App\Models\ContactList::whereIn('id', $this->filters['list_ids'])
            ->get()
            ->map(fn ($list) => [
                'id' => $list->id,
                'name' => $list->name,
                'count' => $list->member_count ?? 0,
                'color' => $this->getListColor($list->id),
            ])
            ->toArray();
    }

    /**
     * Generate a consistent color for a list based on its position.
     */
    protected function getListColor(int $listId): string
    {
        $colors = [
            '#3B82F6', // blue
            '#8B5CF6', // violet
            '#10B981', // emerald
            '#F59E0B', // amber
            '#EF4444', // red
            '#EC4899', // pink
            '#06B6D4', // cyan
            '#84CC16', // lime
        ];

        $index = array_search($listId, $this->filters['list_ids']);

        return $colors[$index % count($colors)] ?? $colors[0];
    }

    public function openPushModal(): void
    {
        if ($this->reportId) {
            $this->dispatch('openPushReport', (int) $this->reportId);
        }
    }

    #[Computed]
    public function canPush(): bool
    {
        $user = auth()->user();
        $hasDownstream = $user->organization?->getDownstreamOrganizations()->count() > 0;
        $hasAssignedOrgs = $user->organizations()->count() > 0;

        return ($user->isAdmin() && $hasDownstream) || ($user->primary_role === 'consultant' && $hasAssignedOrgs);
    }

    /**
     * Start inline text editing on canvas.
     */
    public function startEditingText(string $elementId): void
    {
        $this->editingTextElementId = $elementId;
        $this->selectedElementId = $elementId;
    }

    /**
     * Finish inline text editing.
     */
    public function finishEditingText(): void
    {
        $this->editingTextElementId = null;
    }

    /**
     * Toggle sidebar visibility.
     */
    public function toggleSidebar(): void
    {
        $this->sidebarExpanded = !$this->sidebarExpanded;
    }

    /**
     * Set the active sidebar tab.
     */
    public function setSidebarTab(string $tab): void
    {
        $this->sidebarTab = $tab;
        $this->activeSidebarPanel = $tab;
    }

    public function render()
    {
        return view('livewire.reports.report-builder', [
            'selectedElement' => $this->getSelectedElement(),
            'canUndo' => $this->canUndo(),
            'canRedo' => $this->canRedo(),
            'chartData' => $this->chartData,
            'canPush' => $this->canPush,
        ]);
    }
}
