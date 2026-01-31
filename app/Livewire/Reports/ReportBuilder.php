<?php

namespace App\Livewire\Reports;

use App\Models\CustomReport;
use App\Models\Student;
use App\Services\ContactMetricService;
use App\Services\ReportAIService;
use App\Services\ReportDataService;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class ReportBuilder extends Component
{
    // Listen for events from JavaScript
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
    ];

    // Temporary storage for chart images during PDF export
    public array $chartImages = [];
    // Report model
    public ?string $reportId = null;
    public string $reportName = 'Untitled Report';
    public string $reportDescription = '';
    public string $reportType = 'custom';
    public string $status = 'draft';

    // Layout state
    public array $elements = [];
    public ?string $selectedElementId = null;

    // Page settings
    public array $pageSettings = [
        'size' => 'letter',
        'orientation' => 'portrait',
        'margins' => ['top' => 40, 'right' => 40, 'bottom' => 40, 'left' => 40],
    ];

    // Branding
    public array $branding = [
        'logo_path' => null,
        'primary_color' => '#3B82F6',
        'secondary_color' => '#1E40AF',
        'font_family' => 'Inter, sans-serif',
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

    // Data freshness
    public bool $isLive = true;

    // Undo/redo
    protected array $history = [];
    protected int $historyIndex = -1;
    protected int $maxHistory = 50;

    // UI state
    public bool $showTemplateGallery = false;
    public bool $showBrandingPanel = false;
    public bool $showFilterBar = false;
    public bool $showPublishModal = false;
    public string $activeTab = 'elements'; // elements, settings

    // Publish state
    public ?string $publicUrl = null;
    public ?string $embedCode = null;

    // Available templates
    public array $templates = [];

    public function mount(?CustomReport $report = null, array $templates = [])
    {
        $this->templates = $templates;

        if ($report && $report->exists) {
            $this->loadReport($report);
        } else {
            // Show template gallery for new reports
            $this->showTemplateGallery = true;
            $this->filters['start_date'] = now()->subMonths(6)->format('Y-m-d');
            $this->filters['end_date'] = now()->format('Y-m-d');
        }
    }

    public function loadReport(CustomReport $report): void
    {
        $this->reportId = (string) $report->id;
        $this->reportName = $report->report_name ?? 'Untitled Report';
        $this->reportDescription = $report->report_description ?? '';
        $this->reportType = $report->report_type ?? 'custom';
        $this->status = $report->status ?? 'draft';
        $this->elements = $report->report_layout ?? [];
        $this->pageSettings = array_merge($this->pageSettings, $report->page_settings ?? []);
        $this->branding = array_merge($this->branding, $report->branding ?? []);
        $this->filters = array_merge($this->filters, $report->filters ?? []);
        $this->isLive = $report->is_live ?? true;

        // Initialize history
        $this->pushHistory();
    }

    public function loadTemplate(string $templateId): void
    {
        $template = collect($this->templates)->firstWhere('id', $templateId);

        if ($template) {
            $this->elements = $template['layout'] ?? [];
            $this->reportType = $template['type'] ?? 'custom';
            $this->reportName = $template['name'] ?? 'Untitled Report';
            $this->showTemplateGallery = false;

            // Generate new IDs for elements
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

    public function addElement(string $type, ?array $config = null): void
    {
        $element = $this->createDefaultElement($type, $config);
        $this->elements[] = $element;
        $this->selectedElementId = $element['id'];
        $this->pushHistory();
    }

    protected function createDefaultElement(string $type, ?array $config = null): array
    {
        $id = Str::uuid()->toString();

        $defaults = match ($type) {
            'text' => [
                'position' => ['x' => 40, 'y' => $this->getNextY()],
                'size' => ['width' => 400, 'height' => 60],
                'config' => ['content' => '<p>Enter your text here...</p>', 'format' => 'html'],
                'styles' => ['backgroundColor' => 'transparent', 'padding' => 8, 'borderRadius' => 4],
            ],
            'chart' => [
                'position' => ['x' => 40, 'y' => $this->getNextY()],
                'size' => ['width' => 500, 'height' => 300],
                'config' => [
                    'chart_type' => 'line',
                    'title' => 'Chart Title',
                    'metric_keys' => ['gpa'],
                    'colors' => ['#3B82F6'],
                ],
                'styles' => [
                    'backgroundColor' => '#FFFFFF',
                    'borderRadius' => 8,
                    'padding' => 16,
                    'borderWidth' => 1,
                    'borderColor' => '#E5E7EB',
                ],
            ],
            'table' => [
                'position' => ['x' => 40, 'y' => $this->getNextY()],
                'size' => ['width' => 600, 'height' => 250],
                'config' => [
                    'title' => 'Data Table',
                    'columns' => ['name', 'gpa', 'attendance'],
                    'data_source' => 'students',
                    'sortable' => true,
                ],
                'styles' => ['backgroundColor' => '#FFFFFF', 'borderRadius' => 8],
            ],
            'metric_card' => [
                'position' => ['x' => 40, 'y' => $this->getNextY()],
                'size' => ['width' => 180, 'height' => 100],
                'config' => [
                    'metric_key' => 'gpa',
                    'label' => 'GPA',
                    'show_trend' => true,
                    'comparison_period' => 'last_month',
                ],
                'styles' => [
                    'backgroundColor' => '#F0F9FF',
                    'borderRadius' => 8,
                    'padding' => 16,
                ],
            ],
            'ai_text' => [
                'position' => ['x' => 40, 'y' => $this->getNextY()],
                'size' => ['width' => 600, 'height' => 150],
                'config' => [
                    'prompt' => 'Write a summary of the student performance data.',
                    'format' => 'narrative',
                    'context_metrics' => ['gpa', 'attendance_rate', 'wellness_score'],
                    'generated_content' => null,
                    'generated_at' => null,
                ],
                'styles' => [
                    'backgroundColor' => '#F9FAFB',
                    'borderRadius' => 8,
                    'padding' => 20,
                ],
            ],
            'image' => [
                'position' => ['x' => 40, 'y' => $this->getNextY()],
                'size' => ['width' => 300, 'height' => 200],
                'config' => ['src' => null, 'alt' => '', 'fit' => 'contain'],
                'styles' => ['borderRadius' => 4],
            ],
            'spacer' => [
                'position' => ['x' => 40, 'y' => $this->getNextY()],
                'size' => ['width' => 600, 'height' => 40],
                'config' => [],
                'styles' => ['backgroundColor' => 'transparent'],
            ],
            default => [
                'position' => ['x' => 40, 'y' => $this->getNextY()],
                'size' => ['width' => 200, 'height' => 100],
                'config' => [],
                'styles' => [],
            ],
        };

        return array_merge([
            'id' => $id,
            'type' => $type,
            'locked' => false,
        ], $defaults, $config ?? []);
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

    public function selectElement(?string $elementId): void
    {
        $this->selectedElementId = $elementId;
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
        // Don't push to history on every move - only on end
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
            fn($el) => $el['id'] !== $elementId
        ));

        if ($this->selectedElementId === $elementId) {
            $this->selectedElementId = null;
        }

        $this->pushHistory();
    }

    /**
     * Delete the currently selected element.
     */
    public function deleteSelectedElement(): void
    {
        if ($this->selectedElementId) {
            $this->deleteElement($this->selectedElementId);
        }
    }

    public function moveElementUp(string $elementId): void
    {
        $index = collect($this->elements)->search(fn($el) => $el['id'] === $elementId);

        if ($index !== false && $index < count($this->elements) - 1) {
            $temp = $this->elements[$index];
            $this->elements[$index] = $this->elements[$index + 1];
            $this->elements[$index + 1] = $temp;
            $this->pushHistory();
        }
    }

    public function moveElementDown(string $elementId): void
    {
        $index = collect($this->elements)->search(fn($el) => $el['id'] === $elementId);

        if ($index !== false && $index > 0) {
            $temp = $this->elements[$index];
            $this->elements[$index] = $this->elements[$index - 1];
            $this->elements[$index - 1] = $temp;
            $this->pushHistory();
        }
    }

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

    public function save(): void
    {
        $user = auth()->user();

        $data = [
            'org_id' => $user->org_id,
            'created_by' => $user->id,
            'last_edited_by' => $user->id,
            'report_name' => $this->reportName,
            'report_description' => $this->reportDescription,
            'report_type' => $this->reportType,
            'report_layout' => $this->elements,
            'page_settings' => $this->pageSettings,
            'branding' => $this->branding,
            'filters' => $this->filters,
            'status' => $this->status,
            'is_live' => $this->isLive,
        ];

        if ($this->reportId) {
            $report = CustomReport::find($this->reportId);
            if ($report) {
                $report->update($data);
                $report->incrementVersion();
            }
        } else {
            $data['version'] = 1;
            $data['status'] = CustomReport::STATUS_DRAFT;
            $report = CustomReport::create($data);
            $this->reportId = (string) $report->id;
        }

        $this->dispatch('report-saved', reportId: $this->reportId);
        session()->flash('message', 'Report saved successfully!');
    }

    public function publish(): void
    {
        if (!$this->reportId) {
            $this->save();
        }

        $report = CustomReport::find($this->reportId);
        if ($report) {
            $report->publish();
            $this->status = CustomReport::STATUS_PUBLISHED;
            $this->publicUrl = $report->getPublicUrl();
            $this->embedCode = $report->getEmbedCode();

            $this->dispatch('report-published', [
                'publicUrl' => $this->publicUrl,
                'embedCode' => $this->embedCode,
            ]);
        }
    }

    public function openPublishModal(): void
    {
        // Ensure report is saved first
        if (!$this->reportId) {
            $this->save();
        }

        // If already published, load the URLs
        if ($this->status === CustomReport::STATUS_PUBLISHED && $this->reportId) {
            $report = CustomReport::find($this->reportId);
            if ($report) {
                $this->publicUrl = $report->getPublicUrl();
                $this->embedCode = $report->getEmbedCode();
            }
        }

        $this->showPublishModal = true;
    }

    /**
     * Preview the report (saves first if needed, then opens in new tab).
     */
    public function previewReport(): void
    {
        // Ensure report is saved first
        if (!$this->reportId) {
            $this->save();
        }

        // Dispatch event to open preview in new tab (handled by Alpine.js)
        $this->dispatch('openPreview', url: route('reports.preview', ['report' => $this->reportId]));
    }

    /**
     * Start PDF export process - triggers chart image capture first.
     */
    public function exportPdf(): void
    {
        // First, ensure the report is saved
        if (!$this->reportId) {
            $this->save();
        }

        // Dispatch event to capture chart images
        $this->dispatch('prepareForPdf');
    }

    /**
     * Receive chart images and complete PDF generation.
     */
    public function chartImagesReady(array $images = []): void
    {
        $this->chartImages = $images;

        // Now redirect to PDF download
        if ($this->reportId) {
            $this->redirect(route('reports.pdf', ['report' => $this->reportId]), navigate: false);
        }
    }

    public function updateFilters(array $newFilters): void
    {
        $this->filters = array_merge($this->filters, $newFilters);
    }

    public function updateBranding(array $newBranding): void
    {
        $this->branding = array_merge($this->branding, $newBranding);
    }

    public function getSelectedElement(): ?array
    {
        if (!$this->selectedElementId) {
            return null;
        }

        return collect($this->elements)->firstWhere('id', $this->selectedElementId);
    }

    /**
     * Get chart data for all chart elements.
     */
    #[Computed]
    public function chartData(): array
    {
        $user = auth()->user();
        $metricService = app(ContactMetricService::class);

        $chartsData = [];

        foreach ($this->elements as $element) {
            if ($element['type'] !== 'chart') {
                continue;
            }

            $metricKeys = $element['config']['metric_keys'] ?? [];
            if (empty($metricKeys)) {
                continue;
            }

            // Determine date range
            $dateRange = $this->getDateRangeForFilters();

            // Get data based on scope
            if ($this->filters['scope'] === 'individual' && $this->filters['contact_id']) {
                $contactType = $this->filters['contact_type'] === 'student' ? Student::class : 'App\\Models\\User';
                $data = $metricService->getChartData(
                    $contactType,
                    (int) $this->filters['contact_id'],
                    $metricKeys,
                    $dateRange['start'],
                    $dateRange['end'],
                    'week'
                );
            } else {
                // School-wide or cohort data
                $dataService = app(ReportDataService::class);
                $data = $dataService->getTimeSeriesData(
                    $user->org_id,
                    $metricKeys,
                    $this->filters
                );
            }

            $chartsData[$element['id']] = $data;
        }

        return $chartsData;
    }

    /**
     * Generate AI content for an AI text element.
     */
    public function generateAiContent(string $elementId): void
    {
        $element = collect($this->elements)->firstWhere('id', $elementId);
        if (!$element || $element['type'] !== 'ai_text') {
            return;
        }

        $user = auth()->user();
        $aiService = app(ReportAIService::class);

        // Build context from metrics
        $contextMetrics = $element['config']['context_metrics'] ?? ['gpa', 'attendance_rate', 'wellness_score'];
        $format = $element['config']['format'] ?? 'narrative';

        // Get metrics data
        $metricsData = [];
        if ($this->filters['scope'] === 'individual' && $this->filters['contact_id']) {
            $metricsData = $aiService->getMetricsForContext(
                $this->filters['contact_type'] === 'student' ? Student::class : 'App\\Models\\User',
                (int) $this->filters['contact_id'],
                $contextMetrics
            );
        } else {
            $dataService = app(ReportDataService::class);
            $metricsData = $dataService->getAggregatedData(
                $user->org_id,
                $this->filters['scope'] ?? 'school',
                $this->filters,
                $contextMetrics
            );
        }

        // Build context
        $context = [
            'metrics' => $metricsData,
            'period' => $this->filters['date_range'] ?? '6 months',
            'scope' => $this->filters['scope'] ?? 'individual',
            'custom_prompt' => $element['config']['prompt'] ?? null,
        ];

        // Generate content
        $content = $aiService->generateAdaptiveText(
            $context,
            $format,
            $user->organization?->name ?? 'School'
        );

        // Update element with generated content
        foreach ($this->elements as &$el) {
            if ($el['id'] === $elementId) {
                $el['config']['generated_content'] = $content;
                $el['config']['generated_at'] = now()->toISOString();
                break;
            }
        }

        $this->pushHistory();
        $this->dispatch('aiContentGenerated', elementId: $elementId);
    }

    /**
     * Update text element content.
     */
    public function updateTextContent(string $elementId, string $content): void
    {
        foreach ($this->elements as &$element) {
            if ($element['id'] === $elementId && $element['type'] === 'text') {
                $element['config']['content'] = $content;
                break;
            }
        }
        $this->pushHistory();
    }

    /**
     * Update chart element configuration.
     */
    public function updateChartConfig(string $elementId, string $chartType, array $metricKeys, ?string $title = null): void
    {
        foreach ($this->elements as &$element) {
            if ($element['id'] === $elementId && $element['type'] === 'chart') {
                $element['config']['chart_type'] = $chartType;
                $element['config']['metric_keys'] = $metricKeys;
                if ($title !== null) {
                    $element['config']['title'] = $title;
                }
                break;
            }
        }
        $this->pushHistory();
        $this->dispatch('chartsUpdated');
    }

    /**
     * Update metric card configuration.
     */
    public function updateMetricCardConfig(string $elementId, string $metricKey, string $label, bool $showTrend = true): void
    {
        foreach ($this->elements as &$element) {
            if ($element['id'] === $elementId && $element['type'] === 'metric_card') {
                $element['config']['metric_key'] = $metricKey;
                $element['config']['label'] = $label;
                $element['config']['show_trend'] = $showTrend;
                break;
            }
        }
        $this->pushHistory();
    }

    /**
     * Get date range from filters.
     */
    protected function getDateRangeForFilters(): array
    {
        $end = Carbon::now();
        $range = $this->filters['date_range'] ?? '6_months';

        $start = match ($range) {
            '3_months' => $end->copy()->subMonths(3),
            '6_months' => $end->copy()->subMonths(6),
            '12_months', '1_year' => $end->copy()->subYear(),
            '2_years' => $end->copy()->subYears(2),
            'all' => $end->copy()->subYears(10),
            default => $end->copy()->subMonths(6),
        };

        return ['start' => $start, 'end' => $end];
    }

    /**
     * Get metric card value.
     */
    public function getMetricCardValue(string $metricKey): ?float
    {
        $user = auth()->user();

        if ($this->filters['scope'] === 'individual' && $this->filters['contact_id']) {
            $metricService = app(ContactMetricService::class);
            // Get latest value for the contact
            $metric = \App\Models\ContactMetric::forContact(
                $this->filters['contact_type'] === 'student' ? Student::class : 'App\\Models\\User',
                (int) $this->filters['contact_id']
            )
            ->where('metric_key', $metricKey)
            ->orderBy('recorded_at', 'desc')
            ->first();

            return $metric?->numeric_value;
        }

        // School-wide average
        $dataService = app(ReportDataService::class);
        $aggregates = $dataService->getAggregatedData(
            $user->org_id,
            'school',
            $this->filters,
            [$metricKey]
        );

        return $aggregates[$metricKey]['average'] ?? null;
    }

    /**
     * Get all students for the filter dropdown.
     */
    #[Computed]
    public function availableStudents(): array
    {
        $user = auth()->user();

        return Student::where('org_id', $user->org_id)
            ->with('user')
            ->limit(100)
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->user?->full_name ?? 'Unknown',
            ])
            ->toArray();
    }

    /**
     * Set the contact filter.
     */
    public function setContactFilter(?int $contactId, string $contactType = 'student'): void
    {
        $this->filters['contact_id'] = $contactId;
        $this->filters['contact_type'] = $contactType;
        $this->dispatch('chartsUpdated');
    }

    /**
     * Set date range filter.
     */
    public function setDateRange(string $range): void
    {
        $this->filters['date_range'] = $range;
        $this->dispatch('chartsUpdated');
    }

    /**
     * Open push modal for this report.
     */
    public function openPushModal(): void
    {
        if ($this->reportId) {
            $this->dispatch('openPushReport', (int) $this->reportId);
        }
    }

    /**
     * Check if the current user can push content.
     */
    #[Computed]
    public function canPush(): bool
    {
        $user = auth()->user();
        $hasDownstream = $user->organization?->getDownstreamOrganizations()->count() > 0;
        $hasAssignedOrgs = $user->organizations()->count() > 0;
        return ($user->isAdmin() && $hasDownstream) || ($user->primary_role === 'consultant' && $hasAssignedOrgs);
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
