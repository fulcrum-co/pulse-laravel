<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ContactMetric;
use App\Models\CustomReport;
use App\Models\Learner;
use App\Services\Domain\ReportAggregatorService;

class ReportDataService
{
    public function __construct(
        protected ContactMetricService $metricService,
        protected ReportAggregatorService $aggregator
    ) {}

    /**
     * Resolve all data sources for a report's elements.
     */
    public function resolveDataSources(CustomReport $report): array
    {
        $data = [
            'generated_at' => now()->toISOString(),
            'metrics' => [],
            'charts' => [],
            'tables' => [],
            'aggregates' => [],
        ];

        $filters = $report->filters ?? [];
        $elements = $report->report_layout ?? [];

        // Determine date range
        $dateRange = $this->getDateRange($filters['date_range'] ?? '6_months');

        // Collect all needed metrics from elements
        $neededMetrics = $this->collectNeededMetrics($elements);

        // Get data based on scope
        $scope = $filters['scope'] ?? 'individual';

        switch ($scope) {
            case 'individual':
                $data = $this->resolveIndividualData($report, $data, $neededMetrics, $dateRange);
                break;
            case 'cohort':
                $data = $this->resolveCohortData($report, $data, $neededMetrics, $dateRange);
                break;
            case 'organization':
                $data = $this->resolveOrganizationData($report, $data, $neededMetrics, $dateRange);
                break;
        }

        return $data;
    }

    /**
     * Create a snapshot of current data for the report.
     */
    public function createSnapshot(CustomReport $report): array
    {
        $data = $this->resolveDataSources($report);
        $data['snapshot_created_at'] = now()->toISOString();

        $report->update([
            'snapshot_data' => $data,
            'is_live' => false,
        ]);

        return $data;
    }

    /**
     * Get aggregated data for a specific scope.
     */
    public function getAggregatedData(int $orgId, string $scope, array $filters, array $metricKeys): array
    {
        $dateRange = $this->getDateRange($filters['date_range'] ?? '6_months');

        $query = ContactMetric::where('org_id', $orgId)
            ->whereIn('metric_key', $metricKeys)
            ->forPeriod($dateRange['start'], $dateRange['end']);

        // Extract and apply learner filters using domain service
        $learnerFilters = $this->aggregator->extractLearnerFilters($filters);

        // Filter by learner criteria if needed
        if (! empty($learnerFilters)) {
            $learnerIds = Learner::where('org_id', $orgId)
                ->when(isset($learnerFilters['grade_level']), function ($q) use ($learnerFilters) {
                    return $q->where('grade_level', $learnerFilters['grade_level']);
                })
                ->when(isset($learnerFilters['risk_level']), function ($q) use ($learnerFilters) {
                    return $q->where('risk_level', $learnerFilters['risk_level']);
                })
                ->pluck('id');

            if ($learnerIds->isNotEmpty()) {
                $query->where(function ($q) use ($learnerIds) {
                    $q->whereIn('contact_id', $learnerIds->toArray())
                        ->orWhereNotIn('contact_type', [Learner::class]);
                });
            }
        }

        // Get metrics and delegate aggregation to domain service
        $metrics = $query->select('metric_key', 'numeric_value', 'recorded_at')->get();

        return $this->aggregator->aggregateMetrics($metrics, $metricKeys);
    }

    /**
     * Get time series data for charts.
     */
    public function getTimeSeriesData(int $orgId, array $metricKeys, array $filters): array
    {
        $dateRange = $this->getDateRange($filters['date_range'] ?? '6_months');
        $groupBy = $filters['group_by'] ?? 'week';

        $contactType = $filters['contact_type'] ?? Learner::class;
        $contactId = $filters['contact_id'] ?? null;

        if ($contactId) {
            return $this->metricService->getChartData(
                $contactType,
                $contactId,
                $metricKeys,
                $dateRange['start'],
                $dateRange['end'],
                $groupBy
            );
        }

        // Organization-wide aggregation
        $metrics = ContactMetric::where('org_id', $orgId)
            ->whereIn('metric_key', $metricKeys)
            ->forPeriod($dateRange['start'], $dateRange['end'])
            ->select('metric_key', 'numeric_value', 'period_start')
            ->orderBy('period_start')
            ->get();

        // Delegate grouping and transformation to domain service
        return $this->aggregator->groupByPeriod($metrics, $metricKeys, $groupBy);
    }

    /**
     * Get learners data for tables.
     */
    public function getLearnersTableData(int $orgId, array $columns, array $filters, int $pageSize = 100, int $page = 1): array
    {
        $query = Learner::where('org_id', $orgId)
            ->select('id', 'user_id', 'org_id', 'grade_level', 'risk_level');

        // Apply filters early
        if (! empty($filters['grade_level'])) {
            $query->where('grade_level', $filters['grade_level']);
        }

        if (! empty($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }

        // Calculate pagination offsets using domain service
        $offsets = $this->aggregator->calculatePaginationOffsets($page, $pageSize);
        $learners = $query->skip($offsets['skip'])->take($offsets['take'])->get();

        if ($learners->isEmpty()) {
            return [];
        }

        // Load user data only for learners we're displaying
        $learnerIds = $learners->pluck('id')->toArray();
        $userMap = [];
        if (! empty($learnerIds)) {
            // Only load users if needed
            if (in_array('name', $columns) || in_array('email', $columns)) {
                $userMap = Learner::whereIn('id', $learnerIds)
                    ->with('user:id,email,first_name,last_name')
                    ->get()
                    ->reduce(function ($carry, $learner) {
                        $carry[$learner->id] = $learner->user;
                        return $carry;
                    }, []);
            }
        }

        // Determine which metrics we need using domain service
        $metricKeys = $this->aggregator->getMetricColumnsNeeded($columns);

        // Load all required metrics in a single query using aggregation
        $latestMetrics = [];
        if (! empty($metricKeys)) {
            $latestMetrics = ContactMetric::whereIn('contact_id', $learnerIds)
                ->where('contact_type', Learner::class)
                ->whereIn('metric_key', $metricKeys)
                ->select('contact_id', 'metric_key', 'numeric_value', 'recorded_at')
                ->orderBy('contact_id')
                ->orderBy('recorded_at', 'desc')
                ->get()
                ->groupBy('contact_id')
                ->map(function ($group) {
                    return $group->unique('metric_key')->keyBy('metric_key');
                });
        }

        // Build result rows using domain service
        $data = [];
        foreach ($learners as $learner) {
            $row = $this->aggregator->buildLearnerTableRow($learner, $columns, $userMap, $latestMetrics);
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Resolve individual contact data.
     */
    protected function resolveIndividualData(CustomReport $report, array $data, array $neededMetrics, array $dateRange): array
    {
        $filters = $report->filters ?? [];
        $contactType = $filters['contact_type'] ?? Learner::class;
        $contactId = $filters['contact_id'] ?? null;

        if (! $contactId) {
            return $data;
        }

        // Get latest metrics
        $latestMetrics = ContactMetric::forContact($contactType, $contactId)
            ->whereIn('metric_key', $neededMetrics)
            ->orderBy('recorded_at', 'desc')
            ->get()
            ->unique('metric_key');

        foreach ($latestMetrics as $metric) {
            $data['metrics'][$metric->metric_key] = [
                'value' => $metric->numeric_value,
                'status' => $metric->status,
                'label' => $metric->metric_label,
                'recorded_at' => $metric->recorded_at?->toDateTimeString(),
            ];
        }

        // Get time series for charts
        $data['charts'] = $this->metricService->getChartData(
            $contactType,
            $contactId,
            $neededMetrics,
            $dateRange['start'],
            $dateRange['end'],
            'week'
        );

        return $data;
    }

    /**
     * Resolve cohort data.
     */
    protected function resolveCohortData(CustomReport $report, array $data, array $neededMetrics, array $dateRange): array
    {
        $data['aggregates'] = $this->getAggregatedData(
            $report->org_id,
            'cohort',
            $report->filters ?? [],
            $neededMetrics
        );

        $data['charts'] = $this->getTimeSeriesData(
            $report->org_id,
            $neededMetrics,
            $report->filters ?? []
        );

        return $data;
    }

    /**
     * Resolve organization-wide data.
     */
    protected function resolveOrganizationData(CustomReport $report, array $data, array $neededMetrics, array $dateRange): array
    {
        // Get organization-wide aggregates
        $data['aggregates'] = $this->getAggregatedData(
            $report->org_id,
            'organization',
            $report->filters ?? [],
            $neededMetrics
        );

        // Get learner counts and risk distribution using domain service
        $learnerAggregates = $this->aggregator->getLearnerCountAggregates($report->org_id);
        $data['learner_count'] = $learnerAggregates['total'];
        $data['good_standing_count'] = $learnerAggregates['good_standing'];
        $data['at_risk_count'] = $learnerAggregates['at_risk'];
        $data['risk_distribution'] = $learnerAggregates['risk_distribution'];

        // Get time series
        $data['charts'] = $this->getTimeSeriesData(
            $report->org_id,
            $neededMetrics,
            $report->filters ?? []
        );

        return $data;
    }

    /**
     * Collect all metrics needed from report elements.
     */
    protected function collectNeededMetrics(array $elements): array
    {
        return $this->aggregator->collectNeededMetrics($elements);
    }

    /**
     * Get date range from filter value.
     */
    protected function getDateRange(string $range): array
    {
        $end = Carbon::now();

        $start = match ($range) {
            '3_months' => $end->copy()->subMonths(3),
            '6_months' => $end->copy()->subMonths(6),
            '12_months', '1_year' => $end->copy()->subYear(),
            '2_years' => $end->copy()->subYears(2),
            'all' => $end->copy()->subYears(10),
            default => $end->copy()->subMonths(6),
        };

        return [
            'start' => $start,
            'end' => $end,
        ];
    }

    /**
     * Get latest metric value for a learner.
     * Use sparingly - prefer bulk loading via getLearnersTableData for multiple learners.
     */
    protected function getLatestMetric(Learner $learner, string $metricKey): ?float
    {
        $metric = ContactMetric::forContact(Learner::class, $learner->id)
            ->where('metric_key', $metricKey)
            ->select('numeric_value', 'recorded_at')
            ->orderBy('recorded_at', 'desc')
            ->first();

        return $metric?->numeric_value;
    }
}
