<?php

namespace App\Livewire\Reports;

use App\Livewire\Reports\Concerns\WithCanvasInteraction;
use App\Livewire\Reports\Concerns\WithChartData;
use App\Livewire\Reports\Concerns\WithElementDefaults;
use App\Livewire\Reports\Concerns\WithElementManagement;
use App\Livewire\Reports\Concerns\WithHistory;
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
    use WithElementDefaults;
    use WithElementManagement;
    use WithHistory;
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
        'zoomIn',
        'zoomOut',
        'resetZoom',
    ];

    // Global filters
    public array $filters = [
        'date_range' => '6_months',
        'start_date' => null,
        'end_date' => null,
        'scope' => 'individual',
        'contact_type' => 'student',
        'contact_id' => null,
        'grade_level' => null,
        'risk_level' => null,
    ];

    // UI state
    public bool $showTemplateGallery = false;

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
        } else {
            $this->showTemplateGallery = true;
            $this->filters['start_date'] = now()->subMonths(6)->format('Y-m-d');
            $this->filters['end_date'] = now()->format('Y-m-d');
        }
    }

    public function loadTemplate(string $templateId): void
    {
        $template = collect($this->templates)->firstWhere('id', $templateId);

        if ($template) {
            $this->elements = $template['layout'] ?? [];
            $this->reportType = $template['type'] ?? 'custom';
            $this->reportName = $template['name'] ?? 'Untitled Report';
            $this->showTemplateGallery = false;

            foreach ($this->elements as &$element) {
                $element['id'] = Str::uuid()->toString();
            }

            $this->pushHistory();
        }
    }

    public function startBlank(): void
    {
        $this->elements = [];
        $this->reportType = 'custom';
        $this->showTemplateGallery = false;
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

        return Student::where('org_id', $user->org_id)
            ->with('user')
            ->limit(100)
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->user?->full_name ?? 'Unknown',
            ])
            ->toArray();
    }

    public function setContactFilter(?int $contactId, string $contactType = 'student'): void
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
