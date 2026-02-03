<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactMetric;
use App\Models\MetricThreshold;
use App\Models\Learner;
use Carbon\Carbon;
use Illuminate\Support\Collection;

    protected \App\Services\Domain\MetricNormalizationService $normalizationService;
    protected \App\Services\Domain\OrganizationYearCalculationService $organizationYearService;

    public function __construct()
    {
        $this->normalizationService = app(\App\Services\Domain\MetricNormalizationService::class);
        $this->organizationYearService = app(\App\Services\Domain\OrganizationYearCalculationService::class);
    }

    /**
     * Ingest a metric from any source.
     */
    public function ingestMetric(array $data): ContactMetric
    {
        // Apply threshold to determine status
        $threshold = $this->getThreshold(
            $data['org_id'],
            $data['metric_category'],
            $data['metric_key'],
            $data['contact_type'] ?? null
        );

        if ($threshold && isset($data['numeric_value'])) {
            $data['status'] = $threshold->calculateStatus($data['numeric_value']);
            $data['normalized_score'] = $this->normalizationService->normalizeScore($data['numeric_value'], $threshold);
        }

        return ContactMetric::create($data);
    }

    /**
     * Get time-series data for charts.
     */
    public function getTimeSeriesData(
        string $contactType,
        int $contactId,
        array $metricKeys,
        Carbon $startDate,
        Carbon $endDate,
        string $groupBy = 'week'
    ): Collection {
        $query = ContactMetric::forContact($contactType, $contactId)
            ->whereIn('metric_key', $metricKeys)
            ->forPeriod($startDate, $endDate)
            ->orderBy('period_start');

        return $query->get()->groupBy(function ($metric) use ($groupBy) {
            return match ($groupBy) {
                'day' => $metric->period_start->format('Y-m-d'),
                'week' => $metric->period_start->startOfWeek()->format('Y-m-d'),
                'month' => $metric->period_start->format('Y-m'),
                'quarter' => $metric->organization_year.'-Q'.$metric->quarter,
                default => $metric->period_start->format('Y-m-d'),
            };
        });
    }

    /**
     * Get heat map data for learner plan progress.
     */
    public function getHeatMapData(
        Learner $learner,
        string $organizationYear,
        array $categories = ['academics', 'attendance', 'behavior', 'life_skills']
    ): array {
        $metrics = ContactMetric::forContact(Learner::class, $learner->id)
            ->forOrganizationYear($organizationYear)
            ->whereIn('metric_category', $categories)
            ->get();

        $thresholds = MetricThreshold::where('org_id', $learner->org_id)
            ->whereIn('metric_category', $categories)
            ->active()
            ->get()
            ->keyBy(fn ($t) => $t->metric_category.':'.$t->metric_key);

        $heatMap = [];
        foreach ($categories as $category) {
            for ($q = 1; $q <= 4; $q++) {
                $metric = $metrics
                    ->where('metric_category', $category)
                    ->where('quarter', $q)
                    ->first();

                $threshold = $thresholds->first(fn ($t) => str_starts_with($t->getKey(), $category));

                $heatMap[$category][$q] = [
                    'value' => $metric?->numeric_value,
                    'status' => $metric?->status ?? 'no_data',
                    'color' => $threshold
                        ? $threshold->getColorForStatus($metric?->status)
                        : MetricThreshold::DEFAULT_COLOR_NO_DATA,
                    'label' => $threshold
                        ? $threshold->getLabelForStatus($metric?->status)
                        : 'No Data',
                ];
            }
        }

        return $heatMap;
    }

    /**
     * Get chart data formatted for the Livewire component.
     * Returns data keyed by metric with array of {period, value} objects.
     */
    public function getChartData(
        string $contactType,
        int $contactId,
        array $metricKeys,
        Carbon $startDate,
        Carbon $endDate,
        string $groupBy = 'week'
    ): array {
        $data = $this->getTimeSeriesData(
            $contactType,
            $contactId,
            $metricKeys,
            $startDate,
            $endDate,
            $groupBy
        );

        $result = [];

        foreach ($metricKeys as $metric) {
            $result[$metric] = $data->map(function ($group, $period) use ($metric) {
                $metricData = $group->firstWhere('metric_key', $metric);

                return [
                    'period' => $period,
                    'value' => $metricData?->numeric_value,
                ];
            })->values()->toArray();
        }

        return $result;
    }

    /**
     * Bulk import metrics from SIS API.
     */
    public function importFromSis(int $orgId, array $sisData): int
    {
        $count = 0;
        foreach ($sisData as $record) {
            $this->ingestMetric([
                'org_id' => $orgId,
                'contact_type' => Learner::class,
                'contact_id' => $record['learner_id'],
                'metric_category' => $record['category'],
                'metric_key' => $record['metric'],
                'numeric_value' => $record['value'],
                'source_type' => ContactMetric::SOURCE_SIS_API,
                'source_id' => $record['sis_record_id'] ?? null,
                'period_start' => $record['date'],
                'period_end' => $record['date'],
                'recorded_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Import metrics from survey response.
     */
    public function importFromSurvey(int $orgId, int $learnerId, int $surveyAttemptId, array $scores): int
    {
        $count = 0;
        foreach ($scores as $key => $value) {
            $category = $this->normalizationService->getCategoryFromKey($key);

            $this->ingestMetric([
                'org_id' => $orgId,
                'contact_type' => Learner::class,
                'contact_id' => $learnerId,
                'metric_category' => $category,
                'metric_key' => $key,
                'numeric_value' => $value,
                'source_type' => ContactMetric::SOURCE_SURVEY,
                'source_survey_attempt_id' => $surveyAttemptId,
                'period_start' => now()->toDateString(),
                'period_end' => now()->toDateString(),
                'recorded_at' => now(),
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * Get threshold configuration for a metric.
     */
    private function getThreshold(int $orgId, string $category, string $key, ?string $contactType = null): ?MetricThreshold
    {
        return MetricThreshold::where('org_id', $orgId)
            ->where('metric_category', $category)
            ->where('metric_key', $key)
            ->where(fn ($q) => $q->where('contact_type', $contactType)->orWhereNull('contact_type'))
            ->where('active', true)
            ->first();
    }

    /**
     * Get current organization year string.
     */
    public function getCurrentOrganizationYear(): string
    {
        return $this->organizationYearService->getCurrentOrganizationYear();
    }

    /**
     * Get current quarter.
     */
    public function getCurrentQuarter(): int
    {
        return $this->organizationYearService->getCurrentQuarter();
    }
}
