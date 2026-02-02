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
        'contact_list_id' => null, // Selected contact list for contact_list scope
        'grade_level' => null,
        'risk_level' => null,
    ];

    // Contact search query for filtering
    public string $contactSearchQuery = '';

    // UI state
    public bool $showTemplateGallery = false;

    public bool $showCanvasSelector = false; // Step 1: Choose canvas type

    public string $canvasMode = 'document'; // document or dashboard

    public bool $showBrandingPanel = false;

    public bool $showFilterBar = false;

    public string $activeTab = 'elements';

    // Canva-style sidebar panel state
    public string $activeSidebarPanel = 'elements'; // templates, elements, data, smart_blocks, design, layers

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

    public function selectCanvasMode(string $mode): void
    {
        $this->canvasMode = $mode;
        $this->showCanvasSelector = false;
        $this->showTemplateGallery = true;

        // Set default page settings based on canvas mode
        if ($mode === 'dashboard') {
            // Dashboard: landscape, wider format
            $this->pageSettings['orientation'] = 'landscape';
            $this->pageSettings['size'] = 'letter';
            // Update current page dimensions for dashboard
            if (isset($this->pages[$this->currentPageIndex])) {
                $this->pages[$this->currentPageIndex]['settings'] = [
                    'width' => 1056, // Landscape
                    'height' => 816,
                ];
            }
        } else {
            // Document: portrait, standard format
            $this->pageSettings['orientation'] = 'portrait';
            $this->pageSettings['size'] = 'letter';
            if (isset($this->pages[$this->currentPageIndex])) {
                $this->pages[$this->currentPageIndex]['settings'] = [
                    'width' => 816, // Portrait
                    'height' => 1056,
                ];
            }
        }
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
                'count' => $list->contacts_count ?? $list->contacts()->count(),
            ])
            ->toArray();
    }

    public function createContactList(string $name): void
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

        // Select the newly created list
        $this->filters['contact_list_id'] = $list->id;

        $this->dispatch('notify', type: 'success', message: 'Contact list created');
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
