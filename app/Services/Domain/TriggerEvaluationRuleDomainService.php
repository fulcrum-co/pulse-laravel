<?php

declare(strict_types=1);

namespace App\Services\Domain;

/**
 * Domain service for trigger evaluation rules and conditions.
 * Handles all business logic for evaluating trigger conditions,
 * comparing values, and generating alert messages.
 */
class TriggerEvaluationRuleDomainService
{
    /**
     * Evaluate trigger conditions based on operations and operand condition.
     */
    public function evaluateConditions(
        ?array $operations,
        string $condition = 'AND'
    ): bool {
        if (empty($operations)) {
            return false;
        }

        // For AND condition, all operations must evaluate to true
        if ($condition === 'AND') {
            foreach ($operations as $operation) {
                if (!$this->evaluateSingleOperation($operation)) {
                    return false;
                }
            }

            return true;
        }

        // For OR condition, at least one operation must evaluate to true
        if ($condition === 'OR') {
            foreach ($operations as $operation) {
                if ($this->evaluateSingleOperation($operation)) {
                    return true;
                }
            }

            return false;
        }

        return false;
    }

    /**
     * Evaluate a single operation with all its criteria.
     */
    public function evaluateSingleOperation(array $operation): bool
    {
        $criteria = $operation['criteria'] ?? [];

        // All criteria within an operation must be true (implicit AND)
        foreach ($criteria as $criterion) {
            if (!$this->evaluateCriterion($criterion)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate a single criterion.
     */
    public function evaluateCriterion(array $criterion): bool
    {
        $condition = $criterion['condition'] ?? null;
        $actual = $criterion['actual_value'] ?? null;
        $expected = $criterion['value'] ?? null;

        if ($condition === null) {
            return false;
        }

        return $this->compareValues($actual, $condition, $expected);
    }

    /**
     * Compare values based on condition operator.
     */
    public function compareValues($actual, string $condition, $expected): bool
    {
        return match ($condition) {
            'equals' => $actual == $expected,
            'not_equals' => $actual != $expected,
            'greater_than' => is_numeric($actual) && $actual > $expected,
            'less_than' => is_numeric($actual) && $actual < $expected,
            'greater_than_or_equal' => is_numeric($actual) && $actual >= $expected,
            'less_than_or_equal' => is_numeric($actual) && $actual <= $expected,
            'contains' => is_string($actual) && str_contains($actual, $expected),
            'in' => is_array($expected) && in_array($actual, $expected),
            'not_in' => is_array($expected) && !in_array($actual, $expected),
            default => false,
        };
    }

    /**
     * Generate SMS alert message for a trigger.
     */
    public function generateSmsAlertMessage(string $learnerName): string
    {
        return "Alert: {$learnerName} requires attention based on recent check-in. Please review in Pulse.";
    }

    /**
     * Generate WhatsApp alert message for a trigger.
     */
    public function generateWhatsAppAlertMessage(string $learnerName): string
    {
        return "Alert: {$learnerName} requires attention based on recent check-in. Please review in Pulse.";
    }

    /**
     * Generate phone call alert message for a trigger.
     */
    public function generateCallAlertMessage(string $learnerName): string
    {
        return "This is an automated alert from Pulse. Learner {$learnerName} requires attention based on a recent check-in. Please log in to Pulse for details.";
    }

    /**
     * Determine if trigger should proceed based on priority and metadata.
     */
    public function shouldProceedWithTrigger(
        string $priority = 'normal',
        bool $isInCooldown = false,
        ?bool $aiApproval = null
    ): bool {
        // Skip if in cooldown
        if ($isInCooldown) {
            return false;
        }

        // If AI evaluation requested, use AI approval (null means no AI evaluation)
        if ($aiApproval !== null && !$aiApproval) {
            return false;
        }

        return true;
    }

    /**
     * Get the condition type name (AND/OR) with validation.
     */
    public function normalizeConditionType(string $condition): string
    {
        return in_array(strtoupper($condition), ['AND', 'OR'], true)
            ? strtoupper($condition)
            : 'AND';
    }
}
