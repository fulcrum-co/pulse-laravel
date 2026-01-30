<?php

namespace App\Services;

use App\Models\CustomReport;
use App\Models\Student;
use App\Models\ContactMetric;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ReportDataService
{
    public function __construct(
        protected ContactMetricService $metricService
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
            case 'school':
                $data = $this->resolveSchoolData($report, $data, $neededMetrics, $dateRange);
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

        // Apply filters
        if (!empty($filters['grade_level'])) {
            // Would need to join with students table
            // For now, we'll skip this filter
        }

        if (!empty($filters['risk_level'])) {
            // Would need to join with students table
        }

        $metrics = $query->get();

        // Calculate aggregates
        $aggregates = [];
        foreach ($metricKeys as $key) {
            $keyMetrics = $metrics->where('metric_key', $key);
            $values = $keyMetrics->pluck('numeric_value')->filter();

            $aggregates[$key] = [
                'average' => $values->isNotEmpty() ? round($values->avg(), 2) : null,
                'min' => $values->isNotEmpty() ? $values->min() : null,
                'max' => $values->isNotEmpty() ? $values->max() : null,
                'count' => $values->count(),
                'latest' => $keyMetrics->sortByDesc('recorded_at')->first()?->numeric_value,
            ];
        }

        return $aggregates;
    }

    /**
     * Get time series data for charts.
     */
    public function getTimeSeriesData(int $orgId, array $metricKeys, array $filters): array
    {
        $dateRange = $this->getDateRange($filters['date_range'] ?? '6_months');
        $groupBy = $filters['group_by'] ?? 'week';

        $contactType = $filters['contact_type'] ?? Student::class;
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

        // School-wide aggregation
        $metrics = ContactMetric::where('org_id', $orgId)
            ->whereIn('metric_key', $metricKeys)
            ->forPeriod($dateRange['start'], $dateRange['end'])
            ->orderBy('period_start')
            ->get();

        // Group by period
        $grouped = $metrics->groupBy(function ($metric) use ($groupBy) {
            return match ($groupBy) {
                'day' => $metric->period_start->format('Y-m-d'),
                'week' => $metric->period_start->startOfWeek()->format('Y-m-d'),
                'month' => $metric->period_start->format('Y-m'),
                default => $metric->period_start->format('Y-m-d'),
            };
        });

        $result = [];
        foreach ($metricKeys as $key) {
            $result[$key] = $grouped->map(function ($group, $period) use ($key) {
                $values = $group->where('metric_key', $key)->pluck('numeric_value')->filter();
                return [
                    'period' => $period,
                    'value' => $values->isNotEmpty() ? round($values->avg(), 2) : null,
                ];
            })->values()->toArray();
        }

        return $result;
    }

    /**
     * Get students data for tables.
     */
    public function getStudentsTableData(int $orgId, array $columns, array $filters): array
    {
        $query = Student::where('org_id', $orgId)
            ->with('user');

        // Apply filters
        if (!empty($filters['grade_level'])) {
            $query->where('grade_level', $filters['grade_level']);
        }

        if (!empty($filters['risk_level'])) {
            $query->where('risk_level', $filters['risk_level']);
        }

        $students = $query->limit(100)->get();

        $data = [];
        foreach ($students as $student) {
            $row = [];

            foreach ($columns as $column) {
                $row[$column] = match ($column) {
                    'name' => $student->user?->full_name ?? 'Unknown',
                    'email' => $student->user?->email,
                    'grade_level' => $student->grade_level,
                    'risk_level' => $student->risk_level,
                    'gpa' => $this->getLatestMetric($student, 'gpa'),
                    'attendance' => $this->getLatestMetric($student, 'attendance_rate'),
                    'attendance_rate' => $this->getLatestMetric($student, 'attendance_rate'),
                    'wellness_score' => $this->getLatestMetric($student, 'wellness_score'),
                    'engagement_score' => $this->getLatestMetric($student, 'engagement_score'),
                    default => null,
                };
            }

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
        $contactType = $filters['contact_type'] ?? Student::class;
        $contactId = $filters['contact_id'] ?? null;

        if (!$contactId) {
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
     * Resolve school-wide data.
     */
    protected function resolveSchoolData(CustomReport $report, array $data, array $neededMetrics, array $dateRange): array
    {
        // Get school-wide aggregates
        $data['aggregates'] = $this->getAggregatedData(
            $report->org_id,
            'school',
            $report->filters ?? [],
            $neededMetrics
        );

        // Get student counts
        $data['student_count'] = Student::where('org_id', $report->org_id)->count();
        $data['good_standing_count'] = Student::where('org_id', $report->org_id)->where('risk_level', 'good')->count();
        $data['at_risk_count'] = Student::where('org_id', $report->org_id)->whereIn('risk_level', ['low', 'high'])->count();

        // Get time series
        $data['charts'] = $this->getTimeSeriesData(
            $report->org_id,
            $neededMetrics,
            $report->filters ?? []
        );

        // Get risk distribution
        $data['risk_distribution'] = Student::where('org_id', $report->org_id)
            ->selectRaw('risk_level, count(*) as count')
            ->groupBy('risk_level')
            ->pluck('count', 'risk_level')
            ->toArray();

        return $data;
    }

    /**
     * Collect all metrics needed from report elements.
     */
    protected function collectNeededMetrics(array $elements): array
    {
        $metrics = [];

        foreach ($elements as $element) {
            $type = $element['type'] ?? '';
            $config = $element['config'] ?? [];

            switch ($type) {
                case 'chart':
                    $metrics = array_merge($metrics, $config['metric_keys'] ?? []);
                    break;
                case 'metric_card':
                    if (isset($config['metric_key'])) {
                        $metrics[] = $config['metric_key'];
                    }
                    break;
                case 'ai_text':
                    $metrics = array_merge($metrics, $config['context_metrics'] ?? []);
                    break;
            }
        }

        return array_unique($metrics);
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
     * Get latest metric value for a student.
     */
    protected function getLatestMetric(Student $student, string $metricKey): ?float
    {
        $metric = ContactMetric::forContact(Student::class, $student->id)
            ->where('metric_key', $metricKey)
            ->orderBy('recorded_at', 'desc')
            ->first();

        return $metric?->numeric_value;
    }
}
