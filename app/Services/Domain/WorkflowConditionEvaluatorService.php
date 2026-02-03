<?php

declare(strict_types=1);

namespace App\Services\Domain;

class WorkflowConditionEvaluatorService
{
    /**
     * Evaluate conditions with AND/OR logic.
     */
    public function evaluateConditions(array $conditions, string $logic, array $context): bool
    {
        if (empty($conditions)) {
            return true;
        }

        $results = [];

        foreach ($conditions as $condition) {
            $results[] = $this->evaluateCondition($condition, $context);
        }

        return strtolower($logic) === 'and'
            ? !in_array(false, $results, true)
            : in_array(true, $results, true);
    }

    /**
     * Evaluate a single condition.
     */
    public function evaluateCondition(array $condition, array $context): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? 'equals';
        $value = $condition['value'] ?? null;

        if (!$field) {
            return false;
        }

        $actualValue = data_get($context, $field);

        return $this->compareValues($actualValue, $operator, $value, $condition, $context);
    }

    /**
     * Compare values based on operator.
     */
    public function compareValues($actual, string $operator, $expected, array $condition = [], array $context = []): bool
    {
        return match ($operator) {
            'equals', '=', '==' => $actual == $expected,
            'not_equals', '!=', '<>' => $actual != $expected,
            'greater_than', '>' => is_numeric($actual) && $actual > $expected,
            'less_than', '<' => is_numeric($actual) && $actual < $expected,
            'greater_or_equal', '>=' => is_numeric($actual) && $actual >= $expected,
            'less_or_equal', '<=' => is_numeric($actual) && $actual <= $expected,
            'contains' => is_string($actual) && str_contains(strtolower($actual), strtolower($expected)),
            'not_contains' => is_string($actual) && !str_contains(strtolower($actual), strtolower($expected)),
            'starts_with' => is_string($actual) && str_starts_with(strtolower($actual), strtolower($expected)),
            'ends_with' => is_string($actual) && str_ends_with(strtolower($actual), strtolower($expected)),
            'in' => is_array($expected) && in_array($actual, $expected),
            'not_in' => is_array($expected) && !in_array($actual, $expected),
            'is_empty' => empty($actual),
            'is_not_empty' => !empty($actual),
            'is_null' => is_null($actual),
            'is_not_null' => !is_null($actual),
            'changed_to' => isset($context['_previous'][$condition['field'] ?? ''])
                && $context['_previous'][$condition['field']] != $actual
                && $actual == $expected,
            'changed_from' => isset($context['_previous'][$condition['field'] ?? ''])
                && $context['_previous'][$condition['field']] == $expected
                && $actual != $expected,
            'between' => is_numeric($actual) && is_array($expected) && count($expected) >= 2
                && $actual >= $expected[0] && $actual <= $expected[1],
            default => false,
        };
    }
}
