<?php

declare(strict_types=1);

namespace App\Services\Domain;

/**
 * LearnerNeedsInferenceService
 *
 * Centralizes learner needs inference logic used across multiple services.
 * This domain service analyzes learner metrics and behavioral patterns to identify
 * learner needs, concerns, and areas requiring intervention.
 *
 * @package App\Services\Domain
 */
class LearnerNeedsInferenceService
{
    /**
     * Metric severity thresholds configuration
     */
    private const SEVERITY_THRESHOLDS = [
        'attendance_rate' => ['critical' => 50, 'high' => 70, 'moderate' => 85],
        'engagement_score' => ['critical' => 20, 'high' => 40, 'moderate' => 70],
        'academic_performance' => ['critical' => 40, 'high' => 60, 'moderate' => 80],
        'behavioral_incidents' => ['critical' => 5, 'high' => 3, 'moderate' => 1],
        'wellness_score' => ['critical' => 30, 'high' => 50, 'moderate' => 75],
    ];

    /**
     * Trend analysis sensitivity settings
     */
    private const TREND_SENSITIVITY = [
        'declining' => -10, // percentage point change threshold
        'improving' => 5,   // percentage point change threshold
    ];

    /**
     * Infer learner needs based on current metrics and optional additional data
     *
     * Analyzes learner metrics against severity thresholds and patterns to identify
     * areas of concern that require support or intervention.
     *
     * @param object $learner Learner object with metrics data
     * @param array<string, mixed> $metrics Optional additional metrics to consider
     * @return array<string, mixed> Array of inferred needs with severity and context
     *
     * @example
     *   $needs = $service->inferNeeds($learner, ['custom_metric' => 85]);
     *   // Returns: ['academic_support' => ['severity' => 'high', ...], ...]
     */
    public function inferNeeds(object $learner, array $metrics = []): array
    {
        $needs = [];
        $allMetrics = $this->normalizeMetrics($learner, $metrics);

        foreach ($allMetrics as $metricKey => $value) {
            $severity = $this->classifySeverity($metricKey, $value);

            if ($severity !== 'low') {
                $needs[$metricKey] = [
                    'severity' => $severity,
                    'value' => $value,
                    'threshold' => $this->getThresholdForMetric($metricKey, $severity),
                ];
            }
        }

        // Infer compound needs from metric combinations
        $compoundNeeds = $this->inferCompoundNeeds($allMetrics);
        $needs = array_merge($needs, $compoundNeeds);

        return $needs;
    }

    /**
     * Analyze metric trends over a specified period
     *
     * Compares historical metric values to identify improving or declining trends.
     * Used to detect patterns that may not be apparent from single-point metrics.
     *
     * @param object $learner Learner object with historical metrics
     * @param int $daysBack Number of days to analyze (default: 30)
     * @return array<string, mixed> Array with trend analysis per metric
     *
     * @example
     *   $trends = $service->analyzeTrends($learner, 14);
     *   // Returns: ['attendance_rate' => ['trend' => 'declining', 'change' => -8.5], ...]
     */
    public function analyzeTrends(object $learner, int $daysBack = 30): array
    {
        $trends = [];
        $historicalMetrics = $this->getHistoricalMetrics($learner, $daysBack);

        if (empty($historicalMetrics)) {
            return $trends;
        }

        foreach ($historicalMetrics as $metricKey => $history) {
            if (count($history) < 2) {
                continue;
            }

            $oldest = reset($history);
            $newest = end($history);
            $change = $newest - $oldest;

            $trend = match (true) {
                $change <= self::TREND_SENSITIVITY['declining'] => 'declining',
                $change >= self::TREND_SENSITIVITY['improving'] => 'improving',
                default => 'stable',
            };

            $trends[$metricKey] = [
                'trend' => $trend,
                'change' => round($change, 2),
                'change_percent' => count($history) > 0 ? round(($change / $oldest) * 100, 2) : 0,
                'data_points' => count($history),
                'period_days' => $daysBack,
            ];
        }

        return $trends;
    }

    /**
     * Classify a metric value into a severity category
     *
     * Uses predefined thresholds to categorize metric values as 'low', 'moderate',
     * 'high', or 'critical'. Metric keys must match configured thresholds.
     *
     * @param string $metricKey Metric identifier (e.g., 'attendance_rate')
     * @param float $value Numeric value of the metric
     * @return string Severity classification: 'low', 'moderate', 'high', or 'critical'
     *
     * @example
     *   $severity = $service->classifySeverity('attendance_rate', 65);
     *   // Returns: 'high'
     */
    public function classifySeverity(string $metricKey, float $value): string
    {
        if (!isset(self::SEVERITY_THRESHOLDS[$metricKey])) {
            return 'low'; // Unknown metrics default to low severity
        }

        $thresholds = self::SEVERITY_THRESHOLDS[$metricKey];

        if ($value <= $thresholds['critical']) {
            return 'critical';
        }

        if ($value <= $thresholds['high']) {
            return 'high';
        }

        if ($value <= $thresholds['moderate']) {
            return 'moderate';
        }

        return 'low';
    }

    /**
     * Extract specific concerns from a metrics array
     *
     * Identifies actionable concerns from metric data that can be addressed
     * through resources or interventions.
     *
     * @param array<string, mixed> $metrics Metrics to analyze for concerns
     * @return array<string, mixed> Array of identified concerns with details
     *
     * @example
     *   $concerns = $service->extractConcerns($metrics);
     *   // Returns: ['low_attendance' => [...], 'poor_engagement' => [...], ...]
     */
    public function extractConcerns(array $metrics): array
    {
        $concerns = [];

        // Map metrics to concern types
        $concernMapping = [
            'attendance_rate' => ['key' => 'low_attendance', 'label' => 'Low Attendance'],
            'engagement_score' => ['key' => 'poor_engagement', 'label' => 'Poor Engagement'],
            'academic_performance' => ['key' => 'academic_struggles', 'label' => 'Academic Struggles'],
            'behavioral_incidents' => ['key' => 'behavior_issues', 'label' => 'Behavioral Issues'],
            'wellness_score' => ['key' => 'wellness_concern', 'label' => 'Wellness Concern'],
        ];

        foreach ($concernMapping as $metricKey => $concernInfo) {
            if (!isset($metrics[$metricKey])) {
                continue;
            }

            $severity = $this->classifySeverity($metricKey, $metrics[$metricKey]);

            if ($severity !== 'low') {
                $concerns[$concernInfo['key']] = [
                    'label' => $concernInfo['label'],
                    'severity' => $severity,
                    'metric_key' => $metricKey,
                    'metric_value' => $metrics[$metricKey],
                ];
            }
        }

        return $concerns;
    }

    /**
     * Normalize metrics from learner object and merge with additional metrics
     *
     * @param object $learner
     * @param array<string, mixed> $additionalMetrics
     * @return array<string, mixed>
     */
    private function normalizeMetrics(object $learner, array $additionalMetrics = []): array
    {
        $metrics = [];

        // Extract standard metrics from learner object if available
        if (method_exists($learner, 'getMetrics')) {
            $metrics = $learner->getMetrics();
        } elseif (property_exists($learner, 'metrics')) {
            $metrics = (array) $learner->metrics;
        }

        // Merge with additional metrics
        $metrics = array_merge($metrics, $additionalMetrics);

        return array_filter($metrics, fn($value) => is_numeric($value));
    }

    /**
     * Get historical metrics for trend analysis
     *
     * @param object $learner
     * @param int $daysBack
     * @return array<string, array>
     */
    private function getHistoricalMetrics(object $learner, int $daysBack): array
    {
        if (!method_exists($learner, 'getHistoricalMetrics')) {
            return [];
        }

        return $learner->getHistoricalMetrics($daysBack) ?? [];
    }

    /**
     * Infer compound needs from metric combinations
     *
     * Identifies needs that emerge from combinations of metrics rather than
     * individual metric values alone.
     *
     * @param array<string, float> $metrics
     * @return array<string, mixed>
     */
    private function inferCompoundNeeds(array $metrics): array
    {
        $compoundNeeds = [];

        // Low attendance + low engagement = disengagement concern
        if (
            isset($metrics['attendance_rate'], $metrics['engagement_score'])
            && $metrics['attendance_rate'] < 75 && $metrics['engagement_score'] < 50
        ) {
            $compoundNeeds['learner_disengagement'] = [
                'severity' => 'high',
                'contributing_factors' => ['low_attendance', 'poor_engagement'],
            ];
        }

        // Poor academic performance + behavioral issues = comprehensive support needed
        if (
            isset($metrics['academic_performance'], $metrics['behavioral_incidents'])
            && $metrics['academic_performance'] < 60 && $metrics['behavioral_incidents'] > 2
        ) {
            $compoundNeeds['comprehensive_support'] = [
                'severity' => 'high',
                'contributing_factors' => ['academic_struggles', 'behavioral_issues'],
            ];
        }

        return $compoundNeeds;
    }

    /**
     * Get the threshold value for a metric at a specific severity level
     *
     * @param string $metricKey
     * @param string $severity
     * @return float|null
     */
    private function getThresholdForMetric(string $metricKey, string $severity): ?float
    {
        return self::SEVERITY_THRESHOLDS[$metricKey][$severity] ?? null;
    }
}
