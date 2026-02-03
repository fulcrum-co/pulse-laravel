<?php

declare(strict_types=1);

namespace App\Services\Domain;

use App\Models\MetricThreshold;

class MetricNormalizationService
{
    /**
     * Normalize score to 0-100 scale.
     */
    public function normalizeScore(float $value, MetricThreshold $threshold): float
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
    public function getMetricLabel(string $key): string
    {
        return match ($key) {
            'gpa' => 'GPA',
            'wellness_score' => 'Health & Wellness',
            'emotional_wellbeing' => 'Emotional Well-Being',
            'engagement_score' => 'Engagement',
            'plan_progress' => 'Learner Plan Progress',
            'attendance_rate' => 'Attendance',
            default => ucwords(str_replace('_', ' ', $key)),
        };
    }

    /**
     * Get category from metric key.
     */
    public function getCategoryFromKey(string $key): string
    {
        $categoryMap = [
            'gpa' => 'academics',
            'homework_completion' => 'academics',
            'test_scores' => 'academics',
            'attendance_rate' => 'attendance',
            'absences' => 'attendance',
            'tardies' => 'attendance',
            'discipline_incidents' => 'behavior',
            'behavior_score' => 'behavior',
            'wellness_score' => 'wellness',
            'emotional_wellbeing' => 'wellness',
            'engagement_score' => 'engagement',
            'life_skills_score' => 'life_skills',
            'plan_progress' => 'academics',
        ];

        return $categoryMap[$key] ?? 'academics';
    }
}
