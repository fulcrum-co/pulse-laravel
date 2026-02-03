<?php

declare(strict_types=1);

namespace App\Services\Domain;

class AIResponseParserDomainService
{
    /**
     * Extract JSON from AI response content.
     */
    public function extractJson(string $content): ?array
    {
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            try {
                $data = json_decode($matches[0], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $data;
                }
            } catch (\Exception $e) {
                // JSON parsing failed
            }
        }

        return null;
    }

    /**
     * Extract array from AI response content.
     */
    public function extractArray(string $content): ?array
    {
        if (preg_match('/\[[\d,\s]+\]/', $content, $matches)) {
            try {
                $data = json_decode($matches[0], true);
                if (is_array($data)) {
                    return $data;
                }
            } catch (\Exception $e) {
                // JSON parsing failed
            }
        }

        return null;
    }

    /**
     * Validate extracted JSON structure.
     */
    public function validateStructure(array $data, array $requiredKeys): bool
    {
        foreach ($requiredKeys as $key) {
            if (!isset($data[$key])) {
                return false;
            }
        }

        return true;
    }
}
