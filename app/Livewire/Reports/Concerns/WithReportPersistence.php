<?php

namespace App\Livewire\Reports\Concerns;

use App\Models\CustomReport;

trait WithReportPersistence
{
    public ?CustomReport $report = null;

    public ?string $reportId = null;

    public string $reportName = 'Untitled Report';

    public string $reportDescription = '';

    public string $reportType = 'custom';

    public string $status = 'draft';

    public bool $isLive = true;

    public array $pageSettings = [
        'size' => 'letter',
        'orientation' => 'portrait',
        'margins' => ['top' => 40, 'right' => 40, 'bottom' => 40, 'left' => 40],
    ];

    public array $branding = [
        'logo_path' => null,
        'primary_color' => '#3B82F6',
        'secondary_color' => '#1E40AF',
        'font_family' => 'Inter, sans-serif',
    ];

    public ?string $publicUrl = null;

    public ?string $embedCode = null;

    public bool $showPublishModal = false;

    public array $chartImages = [];

    public function loadReport(CustomReport $report): void
    {
        $this->report = $report;
        $this->reportId = (string) $report->id;
        $this->reportName = $report->report_name ?? 'Untitled Report';
        $this->reportDescription = $report->report_description ?? '';
        $this->reportType = $report->report_type ?? 'custom';
        $this->status = $report->status ?? 'draft';
        $this->pageSettings = array_merge($this->pageSettings, $report->page_settings ?? []);
        $this->branding = array_merge($this->branding, $report->branding ?? []);
        $this->filters = array_merge($this->filters, $report->filters ?? []);
        $this->isLive = $report->is_live ?? true;

        // Check for multi-page structure
        $layout = $report->report_layout ?? [];
        if (isset($layout['pages']) && is_array($layout['pages'])) {
            // Multi-page report - load pages data FIRST
            $this->pages = $layout['pages'];
            $this->currentPageIndex = $layout['currentPageIndex'] ?? 0;
            // Ensure currentPageIndex is valid
            if ($this->currentPageIndex >= count($this->pages)) {
                $this->currentPageIndex = 0;
            }
            // Load elements from current page
            $this->elements = $this->pages[$this->currentPageIndex]['elements'] ?? [];
        } else {
            // Legacy single-page report - validate layout is an array
            $this->elements = is_array($layout) ? $layout : [];
        }

        // Initialize pages structure if needed (will use existing $this->pages or convert from elements)
        if (method_exists($this, 'initializePages')) {
            $this->initializePages();
        }

        $this->pushHistory();
    }

    public function save(): void
    {
        $user = auth()->user();

        // Get layout data - use multi-page structure if available
        $layoutData = $this->elements;
        if (method_exists($this, 'getPagesForSave') && ! empty($this->pages)) {
            $layoutData = [
                'pages' => $this->getPagesForSave(),
                'currentPageIndex' => $this->currentPageIndex ?? 0,
            ];
        }

        $data = [
            'org_id' => $user->org_id,
            'created_by' => $user->id,
            'last_edited_by' => $user->id,
            'report_name' => $this->reportName,
            'report_description' => $this->reportDescription,
            'report_type' => $this->reportType,
            'report_layout' => $layoutData,
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
            $this->report = $report;
        }

        $this->dispatch('report-saved', reportId: $this->reportId);
        session()->flash('message', 'Report saved successfully!');
    }

    public function publish(): void
    {
        if (! $this->reportId) {
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
        if (! $this->reportId) {
            $this->save();
        }

        if ($this->status === CustomReport::STATUS_PUBLISHED && $this->reportId) {
            $report = CustomReport::find($this->reportId);
            if ($report) {
                $this->publicUrl = $report->getPublicUrl();
                $this->embedCode = $report->getEmbedCode();
            }
        }

        $this->showPublishModal = true;
    }

    public function previewReport(): void
    {
        if (! $this->reportId) {
            $this->save();
        }

        $this->dispatch('openPreview', url: route('reports.preview', ['report' => $this->reportId]));
    }

    public function exportPdf(): void
    {
        if (! $this->reportId) {
            $this->save();
        }

        $this->dispatch('prepareForPdf');
    }

    public function chartImagesReady(array $images = []): void
    {
        $this->chartImages = $images;

        if ($this->reportId) {
            $this->redirect(route('reports.pdf', ['report' => $this->reportId]), navigate: false);
        }
    }

    public function updateBranding(array $newBranding): void
    {
        $this->branding = array_merge($this->branding, $newBranding);
    }
}
