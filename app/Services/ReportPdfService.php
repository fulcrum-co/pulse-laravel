<?php

namespace App\Services;

use App\Models\CustomReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportPdfService
{
    public function __construct(
        protected ReportDataService $dataService
    ) {}

    /**
     * Generate PDF for a report.
     */
    public function generate(CustomReport $report): string
    {
        // 1. Get data (live or from snapshot)
        $data = $report->is_live
            ? $this->dataService->resolveDataSources($report)
            : ($report->snapshot_data ?? []);

        // 2. Get branding
        $branding = $report->getEffectiveBranding();

        // 3. Get page settings
        $pageSettings = $report->getPageSettings();

        // 4. Render to PDF view
        $html = view('reports.pdf.layout', [
            'report' => $report,
            'data' => $data,
            'branding' => $branding,
            'elements' => $report->report_layout ?? [],
        ])->render();

        // 5. Generate PDF
        $pdf = PDF::loadHTML($html)
            ->setPaper($pageSettings['size'], $pageSettings['orientation'])
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

        return $pdf->output();
    }

    /**
     * Generate PDF and save to storage.
     */
    public function generateAndSave(CustomReport $report, string $disk = 'local'): string
    {
        $pdfContent = $this->generate($report);

        $filename = sprintf(
            'reports/%s/%s-%s.pdf',
            $report->org_id,
            \Illuminate\Support\Str::slug($report->report_name),
            now()->format('Y-m-d-His')
        );

        Storage::disk($disk)->put($filename, $pdfContent);

        return $filename;
    }

    /**
     * Get PDF download response.
     */
    public function download(CustomReport $report)
    {
        $data = $report->is_live
            ? $this->dataService->resolveDataSources($report)
            : ($report->snapshot_data ?? []);

        $branding = $report->getEffectiveBranding();
        $pageSettings = $report->getPageSettings();

        $pdf = PDF::loadView('reports.pdf.layout', [
            'report' => $report,
            'data' => $data,
            'branding' => $branding,
            'elements' => $report->report_layout ?? [],
        ])
            ->setPaper($pageSettings['size'], $pageSettings['orientation'])
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
            ]);

        $filename = \Illuminate\Support\Str::slug($report->report_name).'.pdf';

        return $pdf->download($filename);
    }

    /**
     * Stream PDF in browser.
     */
    public function stream(CustomReport $report)
    {
        $data = $report->is_live
            ? $this->dataService->resolveDataSources($report)
            : ($report->snapshot_data ?? []);

        $branding = $report->getEffectiveBranding();
        $pageSettings = $report->getPageSettings();

        $pdf = PDF::loadView('reports.pdf.layout', [
            'report' => $report,
            'data' => $data,
            'branding' => $branding,
            'elements' => $report->report_layout ?? [],
        ])
            ->setPaper($pageSettings['size'], $pageSettings['orientation']);

        $filename = \Illuminate\Support\Str::slug($report->report_name).'.pdf';

        return $pdf->stream($filename);
    }
}
