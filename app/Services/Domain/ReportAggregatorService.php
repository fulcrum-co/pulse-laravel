<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\ContactMetric;
use App\Models\Participant;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * ReportAggregatorService
 *
 * Encapsulates all aggregation and calculation logic for report data.
 * Handles metric aggregation, time series grouping, and data transformations.
 * Designed to be used by ReportDataService for data resolution.
 */
class ReportAggregatorService
{
    /**
     * Aggregate metrics by key and calculate statistics.
     *
     * Computes average, min, max, count, and latest values for metric keys.
     *
     * @param  Collection  $metrics  Collection of ContactMetric models
     * @param  array<string>  $metricKeys  Keys to aggregate
     * @return array{string: array} Aggregated statistics per metric key
     */
    public function aggregateMetrics(Collection $metrics, array $metricKeys): array
    {
        $aggregates = [];

        foreach ($metricKeys as $key) {
            $keyMetrics = $metrics->where('metric_key', $key);

            if ($keyMetrics->isEmpty()) {
                $aggregates[$key] = [
                    'average' => null,
                    'min' => null,
                    'max' => null,
                    'count' => 0,
                    'latest' => null,
                ];
                continue;
            }

            $values = $keyMetrics->pluck('numeric_value')->filter();

            $aggregates[$key] = [
                'average' => $values->isNotEmpty() ? round($values->avg(), 2) : null,
                'min' => $values->isNotEmpty() ? $values->min() : null,
                'max' => $values->isNotEmpty() ? $values->max() : null,
                'count' => $values->count(),
                'latest' => $keyMetrics->first()?->numeric_value,
            ];
        }

        return $aggregates;
    }

    /**
     * Group metrics by time period for time series data.
     *
     * Supports day, week, and month grouping.
     *
     * @param  Collection  $metrics  Collection of ContactMetric models
     * @param  array<string>  $metricKeys  Keys to include
     * @param  string  $groupBy  Grouping period: 'day', 'week', 'month'
     * @return array{string: array} Time series data per metric
     */
    public function groupByPeriod(Collection $metrics, array $metricKeys, string $groupBy = 'week'): array
    {
        // Group metrics by period
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
     * Calculate participant count aggregates by risk level.
     *
     * @param  int  $orgId  Organization ID
     * @return array{total: int, good_standing: int, at_risk: int, risk_distribution: array}
     */
    public function getLearnerCountAggregates(int $orgId): array
    {
        $learnerCounts = Participant::where('org_id', $orgId)
            ->selectRaw('
                COUNT(*) as total,
                SUM(CASE WHEN risk_level = "good" THEN 1 ELSE 0 END) as good_standing,
                SUM(CASE WHEN risk_level IN ("low", "high") THEN 1 ELSE 0 END) as at_risk
            ')
            ->first();

        $riskDistribution = Participant::where('org_id', $orgId)
            ->select('risk_level')
            ->selectRaw('COUNT(*) as count')
            ->groupBy('risk_level')
            ->pluck('count', 'risk_level')
            ->toArray();

        return [
            'total' => $learnerCounts->total ?? 0,
            'good_standing' => $learnerCounts->good_standing ?? 0,
            'at_risk' => $learnerCounts->at_risk ?? 0,
            'risk_distribution' => $riskDistribution,
        ];
    }

    /**
     * Collect all metric keys needed from report layout elements.
     *
     * Extracts metric keys from various element types in a report layout.
     *
     * @param  array  $elements  Report layout elements
     * @return array<string> Unique metric keys needed
     */
    public function collectNeededMetrics(array $elements): array
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
     * Build participant table row data from loaded metrics.
     *
     * Transforms participant data and metric values into table row format.
     *
     * @param  Participant  $participant
     * @param  array<string>  $columns  Columns to include
     * @param  array  $userMap  User data map (id => user)
     * @param  array  $latestMetrics  Latest metrics map (participant_id => Collection)
     * @return array Row data
     */
    public function buildLearnerTableRow(
        Participant $participant,
        array $columns,
        array $userMap,
        array $latestMetrics
    ): array {
        $row = [];

        foreach ($columns as $column) {
            $row[$column] = match ($column) {
                'name' => $userMap[$participant->id]?->full_name ?? 'Unknown',
                'email' => $userMap[$participant->id]?->email,
                'level' => $participant->level,
                'risk_level' => $participant->risk_level,
                'gpa' => $latestMetrics[$participant->id]?->get('gpa')?->numeric_value,
                'attendance', 'attendance_rate' => $latestMetrics[$participant->id]?->get('attendance_rate')?->numeric_value,
                'wellness_score' => $latestMetrics[$participant->id]?->get('wellness_score')?->numeric_value,
                'engagement_score' => $latestMetrics[$participant->id]?->get('engagement_score')?->numeric_value,
                default => null,
            };
        }

        return $row;
    }

    /**
     * Determine which metrics need to be loaded from database.
     *
     * Maps column names to their corresponding metric keys.
     *
     * @param  array<string>  $columns  Column names
     * @return array<string> Unique metric keys
     */
    public function getMetricColumnsNeeded(array $columns): array
    {
        $metricColumns = array_intersect($columns, ['gpa', 'attendance', 'attendance_rate', 'wellness_score', 'engagement_score']);
        $metricKeys = array_map(function ($col) {
            return $col === 'attendance' ? 'attendance_rate' : $col;
        }, $metricColumns);

        return array_unique($metricKeys);
    }

    /**
     * Extract participant filter criteria from raw filter array.
     *
     * Validates and normalizes filter values.
     *
     * @param  array  $filters  Raw filter array
     * @return array Cleaned filter array
     */
    public function extractLearnerFilters(array $filters): array
    {
        $learnerFilters = [];

        if (! empty($filters['level'])) {
            $learnerFilters['level'] = $filters['level'];
        }

        if (! empty($filters['risk_level'])) {
            $learnerFilters['risk_level'] = $filters['risk_level'];
        }

        return $learnerFilters;
    }

    /**
     * Calculate pagination offsets.
     *
     * @param  int  $page  Page number (1-indexed)
     * @param  int  $pageSize  Items per page
     * @return array{skip: int, take: int}
     */
    public function calculatePaginationOffsets(int $page, int $pageSize): array
    {
        return [
            'skip' => ($page - 1) * $pageSize,
            'take' => $pageSize,
        ];
    }
}
