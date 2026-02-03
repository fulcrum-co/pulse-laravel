<?php

declare(strict_types=1);

namespace App\Services\Domain;

class WorkflowActionInterpolationService
{
    /**
     * Interpolate template variables with context values.
     */
    public function interpolateTemplate(string $template, array $context): string
    {
        return preg_replace_callback('/\{\{([^}]+)\}\}/', function ($matches) use ($context) {
            $key = trim($matches[1]);

            return data_get($context, $key, $matches[0]);
        }, $template);
    }

    /**
     * Interpolate array values recursively.
     */
    public function interpolateArrayValues(array $array, array $context): array
    {
        $result = [];

        foreach ($array as $key => $value) {
            if (is_string($value)) {
                $result[$key] = $this->interpolateTemplate($value, $context);
            } elseif (is_array($value)) {
                $result[$key] = $this->interpolateArrayValues($value, $context);
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * Check if string is a context reference.
     */
    public function isContextReference(string $value): bool
    {
        return str_starts_with($value, '{{') && str_ends_with($value, '}}');
    }

    /**
     * Extract context key from reference.
     */
    public function extractContextKey(string $reference): string
    {
        return trim($reference, '{} ');
    }
}
