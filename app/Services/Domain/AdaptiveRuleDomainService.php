<?php

declare(strict_types=1);

namespace App\Services\Domain;

use Illuminate\Support\Collection;

/**
 * Domain service for adaptive trigger rules and evaluations.
 * Handles all business logic for trigger condition evaluation, signal analysis,
 * and theme extraction from participant data.
 */
class AdaptiveRuleDomainService
{
    /**
     * Calculate the change in GPA between two periods.
     */
    public function calculateGpaChange(float $currentGpa, float $previousGpa): float
    {
        return $currentGpa - $previousGpa;
    }

    /**
     * Calculate resource completion rate as a percentage.
     */
    public function calculateCompletionRate(int $completedCount, int $totalCount): float
    {
        if ($totalCount === 0) {
            return 0;
        }

        return ($completedCount / $totalCount) * 100;
    }

    /**
     * Extract themes from text based on keyword matching.
     * Maps content keywords to identified themes.
     */
    public function extractThemes(string $text): array
    {
        $themes = [];
        $keywords = [
            'anxiety' => ['anxiety', 'anxious', 'worried', 'nervous', 'stressed'],
            'depression' => ['depression', 'depressed', 'sad', 'hopeless', 'withdrawn'],
            'academic' => ['levels', 'homework', 'studying', 'test', 'failing', 'academic'],
            'behavior' => ['behavior', 'conduct', 'discipline', 'outburst', 'disruptive'],
            'attendance' => ['absence', 'absent', 'tardy', 'late', 'attendance', 'missing'],
            'social' => ['friends', 'bullying', 'isolated', 'social', 'peer'],
            'family' => ['family', 'home', 'direct_supervisors', 'divorce', 'custody'],
        ];

        $lowerText = strtolower($text);

        foreach ($keywords as $theme => $words) {
            foreach ($words as $word) {
                if (str_contains($lowerText, $word)) {
                    $themes[] = $theme;
                    break;
                }
            }
        }

        return array_unique($themes);
    }

    /**
     * Evaluate trigger conditions against signals using AND/OR logic.
     */
    public function evaluateConditions(?array $conditions, array $flatSignals): bool
    {
        if (empty($conditions)) {
            return false;
        }

        // Handle 'all' conditions (AND)
        if (isset($conditions['all'])) {
            foreach ($conditions['all'] as $condition) {
                if (!$this->evaluateSingleCondition($condition, $flatSignals)) {
                    return false;
                }
            }

            return true;
        }

        // Handle 'any' conditions (OR)
        if (isset($conditions['any'])) {
            foreach ($conditions['any'] as $condition) {
                if ($this->evaluateSingleCondition($condition, $flatSignals)) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    /**
     * Evaluate a single condition against a signal value.
     */
    public function evaluateSingleCondition(array $condition, array $signals): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? null;
        $value = $condition['value'] ?? null;

        if (!$field || !$operator) {
            return false;
        }

        // Get the signal value (supports dot notation)
        $signalValue = data_get($signals, $field);

        return $this->compareValues($signalValue, $operator, $value);
    }

    /**
     * Compare values based on operator.
     */
    protected function compareValues($signalValue, string $operator, $value): bool
    {
        return match ($operator) {
            'equals' => $signalValue == $value,
            'not_equals' => $signalValue != $value,
            'greater_than' => is_numeric($signalValue) && $signalValue > $value,
            'less_than' => is_numeric($signalValue) && $signalValue < $value,
            'greater_than_or_equals' => is_numeric($signalValue) && $signalValue >= $value,
            'less_than_or_equals' => is_numeric($signalValue) && $signalValue <= $value,
            'contains' => is_string($signalValue) && str_contains($signalValue, $value),
            'contains_any' => is_array($signalValue) && !empty(array_intersect($signalValue, (array) $value)),
            'is_empty' => empty($signalValue),
            'is_not_empty' => !empty($signalValue),
            'in' => in_array($signalValue, (array) $value),
            'not_in' => !in_array($signalValue, (array) $value),
            default => false,
        };
    }

    /**
     * Flatten nested signals array for easier condition evaluation.
     */
    public function flattenSignals(array $signals, string $prefix = ''): array
    {
        $result = [];

        foreach ($signals as $key => $value) {
            $fullKey = $prefix ? "{$prefix}.{$key}" : $key;

            if (is_array($value) && !$this->isIndexedArray($value)) {
                $result = array_merge($result, $this->flattenSignals($value, $fullKey));
            } else {
                $result[$fullKey] = $value;
            }
        }

        return $result;
    }

    /**
     * Check if array is indexed (not associative).
     */
    protected function isIndexedArray(array $arr): bool
    {
        return array_keys($arr) === range(0, count($arr) - 1);
    }
}
