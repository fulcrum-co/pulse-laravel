<?php

namespace App\Services;

use App\Models\ContactMetric;
use App\Models\MetricThreshold;
use App\Models\Student;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ContactMetricService
{
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
            $data['normalized_score'] = $this->normalizeScore($data['numeric_value'], $threshold);
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
                'quarter' => $metric->school_year . '-Q' . $metric->quarter,
                default => $metric->period_start->format('Y-m-d'),
            };
        });
    }

    /**
     * Get heat map data for student plan progress.
     */
    public function getHeatMapData(
        Student $student,
        string $schoolYear,
        array $categories = ['academics', 'attendance', 'behavior', 'life_skills']
    ): array {
        $metrics = ContactMetric::forContact(Student::class, $student->id)
            ->forSchoolYear($schoolYear)
            ->whereIn('metric_category', $categories)
            ->get();

        $thresholds = MetricThreshold::where('org_id', $student->org_id)
            ->whereIn('metric_category', $categories)
            ->active()
            ->get()
            ->keyBy(fn ($t) => $t->metric_category . ':' . $t->metric_key);

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
     * Get chart data formatted for Chart.js.
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

        $labels = $data->keys()->toArray();
        $datasets = [];

        $colors = [
            'gpa' => '#f97316',              // orange
            'wellness_score' => '#8b5cf6',   // purple
            'emotional_wellbeing' => '#3b82f6', // blue
            'engagement_score' => '#22c55e', // green
            'plan_progress' => '#ef4444',    // red
            'attendance_rate' => '#06b6d4',  // cyan
        ];

        foreach ($metricKeys as $metric) {
            $datasets[] = [
                'label' => $this->getMetricLabel($metric),
                'data' => $data->map(fn ($group) => $group->firstWhere('metric_key', $metric)?->numeric_value)->values()->toArray(),
                'borderColor' => $colors[$metric] ?? '#6b7280',
                'backgroundColor' => ($colors[$metric] ?? '#6b7280') . '20',
                'tension' => 0.3,
                'fill' => false,
            ];
        }

        return [
            'labels' => $labels,
            'datasets' => $datasets,
        ];
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
                'contact_type' => Student::class,
                'contact_id' => $record['student_id'],
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
    public function importFromSurvey(int $orgId, int $studentId, int $surveyAttemptId, array $scores): int
    {
        $count = 0;
        foreach ($scores as $key => $value) {
            $category = $this->getCategoryFromKey($key);

            $this->ingestMetric([
                'org_id' => $orgId,
                'contact_type' => Student::class,
                'contact_id' => $studentId,
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
     * Normalize score to 0-100 scale.
     */
    private function normalizeScore(float $value, MetricThreshold $threshold): float
    {
        $min = $threshold->off_track_min ?? 0;
        $max = $threshold->on_track_min ?? 100;

        if ($max == $min) {
            return 50;
        }

        return min(100, max(0, (($value - $min) / ($max - $min)) * 100));
    }

    /**
     * Get human-readable label for metric key.
     */
    private function getMetricLabel(string $key): string
    {
        return match ($key) {
            'gpa' => 'GPA',
            'wellness_score' => 'Health & Wellness',
            'emotional_wellbeing' => 'Emotional Well-Being',
            'engagement_score' => 'Engagement',
            'plan_progress' => 'Student Plan Progress',
            'attendance_rate' => 'Attendance',
            default => ucwords(str_replace('_', ' ', $key)),
        };
    }

    /**
     * Get category from metric key.
     */
    private function getCategoryFromKey(string $key): string
    {
        $categoryMap = [
            'gpa' => ContactMetric::CATEGORY_ACADEMICS,
            'homework_completion' => ContactMetric::CATEGORY_ACADEMICS,
            'test_scores' => ContactMetric::CATEGORY_ACADEMICS,
            'attendance_rate' => ContactMetric::CATEGORY_ATTENDANCE,
            'absences' => ContactMetric::CATEGORY_ATTENDANCE,
            'tardies' => ContactMetric::CATEGORY_ATTENDANCE,
            'discipline_incidents' => ContactMetric::CATEGORY_BEHAVIOR,
            'behavior_score' => ContactMetric::CATEGORY_BEHAVIOR,
            'wellness_score' => ContactMetric::CATEGORY_WELLNESS,
            'emotional_wellbeing' => ContactMetric::CATEGORY_WELLNESS,
            'engagement_score' => ContactMetric::CATEGORY_ENGAGEMENT,
            'life_skills_score' => ContactMetric::CATEGORY_LIFE_SKILLS,
            'plan_progress' => ContactMetric::CATEGORY_ACADEMICS,
        ];

        return $categoryMap[$key] ?? ContactMetric::CATEGORY_ACADEMICS;
    }

    /**
     * Get current school year string.
     */
    public function getCurrentSchoolYear(): string
    {
        $now = now();
        $year = $now->month >= 8 ? $now->year : $now->year - 1;

        return $year . '-' . ($year + 1);
    }

    /**
     * Get current quarter.
     */
    public function getCurrentQuarter(): int
    {
        $month = now()->month;

        // Assuming school year starts in August
        // Q1: Aug-Oct, Q2: Nov-Jan, Q3: Feb-Apr, Q4: May-Jul
        return match (true) {
            $month >= 8 && $month <= 10 => 1,
            $month >= 11 || $month <= 1 => 2,
            $month >= 2 && $month <= 4 => 3,
            default => 4,
        };
    }
}
