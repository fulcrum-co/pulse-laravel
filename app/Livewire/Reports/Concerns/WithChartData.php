<?php

namespace App\Livewire\Reports\Concerns;

use App\Models\Learner;
use App\Services\ContactMetricService;
use App\Services\ReportAIService;
use App\Services\ReportDataService;
use Carbon\Carbon;
use Livewire\Attributes\Computed;

trait WithChartData
{
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

            $dateRange = $this->getDateRangeForFilters();

            if ($this->filters['scope'] === 'individual' && $this->filters['contact_id']) {
                $contactType = $this->filters['contact_type'] === 'learner' ? Learner::class : 'App\\Models\\User';
                $data = $metricService->getChartData(
                    $contactType,
                    (int) $this->filters['contact_id'],
                    $metricKeys,
                    $dateRange['start'],
                    $dateRange['end'],
                    'week',
                );
            } else {
                $dataService = app(ReportDataService::class);
                $data = $dataService->getTimeSeriesData(
                    $user->org_id,
                    $metricKeys,
                    $this->filters,
                );
            }

            $chartsData[$element['id']] = $data;
        }

        return $chartsData;
    }

    public function generateAiContent(string $elementId): void
    {
        $element = collect($this->elements)->firstWhere('id', $elementId);
        if (! $element || $element['type'] !== 'ai_text') {
            return;
        }

        $user = auth()->user();
        $aiService = app(ReportAIService::class);

        $contextMetrics = $element['config']['context_metrics'] ?? ['gpa', 'attendance_rate', 'wellness_score'];
        $format = $element['config']['format'] ?? 'narrative';

        $metricsData = [];
        if ($this->filters['scope'] === 'individual' && $this->filters['contact_id']) {
            $metricsData = $aiService->getMetricsForContext(
                $this->filters['contact_type'] === 'learner' ? Learner::class : 'App\\Models\\User',
                (int) $this->filters['contact_id'],
                $contextMetrics,
            );
        } else {
            $dataService = app(ReportDataService::class);
            $metricsData = $dataService->getAggregatedData(
                $user->org_id,
                $this->filters['scope'] ?? 'organization',
                $this->filters,
                $contextMetrics,
            );
        }

        $context = [
            'metrics' => $metricsData,
            'period' => $this->filters['date_range'] ?? '6 months',
            'scope' => $this->filters['scope'] ?? 'individual',
            'custom_prompt' => $element['config']['prompt'] ?? null,
        ];

        $content = $aiService->generateAdaptiveText(
            $context,
            $format,
            $user->organization?->name ?? 'Organization',
        );

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

    public function getMetricCardValue(string $metricKey): ?float
    {
        $user = auth()->user();

        if ($this->filters['scope'] === 'individual' && $this->filters['contact_id']) {
            $metric = \App\Models\ContactMetric::forContact(
                $this->filters['contact_type'] === 'learner' ? Learner::class : 'App\\Models\\User',
                (int) $this->filters['contact_id'],
            )
                ->where('metric_key', $metricKey)
                ->orderBy('recorded_at', 'desc')
                ->first();

            return $metric?->numeric_value;
        }

        $dataService = app(ReportDataService::class);
        $aggregates = $dataService->getAggregatedData(
            $user->org_id,
            'organization',
            $this->filters,
            [$metricKey],
        );

        return $aggregates[$metricKey]['average'] ?? null;
    }
}
