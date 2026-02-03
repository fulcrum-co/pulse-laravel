<?php

declare(strict_types=1);

namespace App\Services\Domain;

/**
 * AIResponseParserService
 *
 * Centralizes parsing and extraction of AI (Claude API) responses used across
 * multiple services. Handles JSON extraction, code block parsing, structured
 * data extraction, and schema validation.
 *
 * @package App\Services\Domain
 */
class AIResponseParserService
{
    /**
     * Supported code block languages for extraction
     */
    private const SUPPORTED_LANGUAGES = ['json', 'php', 'python', 'javascript', 'sql'];

    /**
     * JSON schema validation error messages
     */
    private const SCHEMA_ERROR_MESSAGES = [
        'missing_required' => 'Missing required field: %s',
        'invalid_type' => 'Invalid type for field %s: expected %s, got %s',
        'invalid_format' => 'Invalid format for field %s',
    ];

    /**
     * Parse JSON response from Claude API
     *
     * Extracts valid JSON from Claude response text. Claude often includes
     * explanatory text before and after JSON, so this method identifies and
     * extracts the JSON object/array portion.
     *
     * @param string $response Raw response text from Claude API
     * @return array<mixed> Parsed JSON as associative array
     *
     * @throws \RuntimeException If no valid JSON found in response
     *
     * @example
     *   $response = "Here's the analysis: {\"needs\": [\"math\"], \"score\": 0.8}";
     *   $data = $service->parseJsonResponse($response);
     *   // Returns: ['needs' => ['math'], 'score' => 0.8]
     */
    public function parseJsonResponse(string $response): array
    {
        // Try direct JSON parsing first
        $directParse = $this->attemptJsonParse($response);
        if ($directParse !== null) {
            return $directParse;
        }

        // Extract JSON from code blocks
        $jsonBlock = $this->extractCodeBlock($response, 'json');
        if (!empty($jsonBlock)) {
            $parsed = $this->attemptJsonParse($jsonBlock);
            if ($parsed !== null) {
                return $parsed;
            }
        }

        // Try to find JSON object/array patterns in text
        $extracted = $this->findJsonInText($response);
        if ($extracted !== null) {
            $parsed = $this->attemptJsonParse($extracted);
            if ($parsed !== null) {
                return $parsed;
            }
        }

        throw new \RuntimeException('No valid JSON found in response');
    }

    /**
     * Extract code block content from response
     *
     * Finds and extracts code blocks enclosed in markdown code fences (```).
     * Optionally filters by language identifier.
     *
     * @param string $response Response text containing code blocks
     * @param string $language Language identifier to filter (e.g., 'json', 'php')
     * @return string Extracted code block content, empty string if not found
     *
     * @example
     *   $response = "```json\n{\"key\": \"value\"}\n```";
     *   $json = $service->extractCodeBlock($response, 'json');
     *   // Returns: '{"key": "value"}'
     */
    public function extractCodeBlock(string $response, string $language = 'json'): string
    {
        // Pattern for markdown code blocks
        $pattern = sprintf(
            '/```\s*%s\s*\n(.*?)\n```/is',
            preg_quote($language, '/')
        );

        if (preg_match($pattern, $response, $matches)) {
            return trim($matches[1]);
        }

        // If language not specified, try generic code block
        if ($language === 'json') {
            $genericPattern = '/```\n(.*?)\n```/s';
            if (preg_match($genericPattern, $response, $matches)) {
                return trim($matches[1]);
            }
        }

        return '';
    }

    /**
     * Parse structured data from response text
     *
     * Extracts and parses structured data that may be formatted as JSON,
     * key-value pairs, bullet points, or other common formats.
     *
     * @param string $response Response text containing structured data
     * @return array<string, mixed> Parsed structured data
     *
     * @example
     *   $response = "Need 1: Math Support\nNeed 2: Counseling";
     *   $data = $service->parseStructuredData($response);
     */
    public function parseStructuredData(string $response): array
    {
        $data = [];

        // First try JSON parsing
        $jsonData = null;
        try {
            $jsonData = $this->parseJsonResponse($response);
            return $jsonData;
        } catch (\RuntimeException $e) {
            // Continue to other parsing methods
        }

        // Parse key: value pairs
        if (preg_match_all('/(?:^|\n)\s*([a-z_]+):\s*(.+?)(?=\n[a-z_]+:|$)/is', $response, $matches)) {
            for ($i = 0; $i < count($matches[1]); $i++) {
                $key = strtolower(trim($matches[1][$i]));
                $value = trim($matches[2][$i]);
                $data[$key] = $this->parseValue($value);
            }

            if (!empty($data)) {
                return $data;
            }
        }

        // Parse bullet lists
        $bulletData = $this->parseBulletList($response);
        if (!empty($bulletData)) {
            return ['items' => $bulletData];
        }

        // If nothing matched, return response as single item
        return ['content' => trim($response)];
    }

    /**
     * Validate and normalize data against schema
     *
     * Validates that provided data matches the expected schema structure,
     * including required fields and type checking. Normalizes values and
     * removes extraneous data.
     *
     * @param array<string, mixed> $data Data to validate
     * @param array<string, mixed> $schema Schema definition with required/types
     * @return array<string, mixed> Validated and normalized data
     *
     * @throws \RuntimeException If validation fails
     *
     * @example
     *   $schema = [
     *       'required' => ['learner_id', 'needs'],
     *       'fields' => [
     *           'learner_id' => 'int',
     *           'needs' => 'array',
     *           'severity' => 'string',
     *       ]
     *   ];
     *   $validated = $service->validateAndNormalize($data, $schema);
     */
    public function validateAndNormalize(array $data, array $schema): array
    {
        $validated = [];
        $requiredFields = $schema['required'] ?? [];
        $fieldDefinitions = $schema['fields'] ?? [];

        // Check required fields
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                throw new \RuntimeException(sprintf(self::SCHEMA_ERROR_MESSAGES['missing_required'], $field));
            }
        }

        // Validate and normalize each field
        foreach ($fieldDefinitions as $field => $type) {
            if (!isset($data[$field])) {
                continue;
            }

            $value = $data[$field];

            // Type validation and normalization
            try {
                $validated[$field] = $this->normalizeValue($value, $type);
            } catch (\RuntimeException $e) {
                throw new \RuntimeException(sprintf(
                    self::SCHEMA_ERROR_MESSAGES['invalid_type'],
                    $field,
                    $type,
                    gettype($value)
                ));
            }
        }

        return $validated;
    }

    /**
     * Attempt to parse string as JSON
     *
     * @param string $text
     * @return array<mixed>|null
     */
    private function attemptJsonParse(string $text): ?array
    {
        $text = trim($text);
        if (empty($text)) {
            return null;
        }

        $decoded = json_decode($text, true);
        $jsonError = json_last_error();

        if ($jsonError === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return null;
    }

    /**
     * Find JSON object/array in text using bracket matching
     *
     * @param string $text
     * @return string|null
     */
    private function findJsonInText(string $text): ?string
    {
        // Look for opening brace or bracket
        $bracePos = strpos($text, '{');
        $bracketPos = strpos($text, '[');

        $startPos = false;
        $startChar = null;

        if ($bracePos !== false && $bracketPos !== false) {
            if ($bracePos < $bracketPos) {
                $startPos = $bracePos;
                $startChar = '{';
            } else {
                $startPos = $bracketPos;
                $startChar = '[';
            }
        } elseif ($bracePos !== false) {
            $startPos = $bracePos;
            $startChar = '{';
        } elseif ($bracketPos !== false) {
            $startPos = $bracketPos;
            $startChar = '[';
        }

        if ($startPos === false) {
            return null;
        }

        // Find matching closing character
        $endChar = $startChar === '{' ? '}' : ']';
        $depth = 0;
        $inString = false;
        $escapeNext = false;

        for ($i = $startPos; $i < strlen($text); $i++) {
            $char = $text[$i];

            if ($escapeNext) {
                $escapeNext = false;
                continue;
            }

            if ($char === '\\') {
                $escapeNext = true;
                continue;
            }

            if ($char === '"') {
                $inString = !$inString;
                continue;
            }

            if ($inString) {
                continue;
            }

            if ($char === $startChar) {
                $depth++;
            } elseif ($char === $endChar) {
                $depth--;
                if ($depth === 0) {
                    return substr($text, $startPos, $i - $startPos + 1);
                }
            }
        }

        return null;
    }

    /**
     * Parse bullet list from text
     *
     * @param string $text
     * @return array<string>
     */
    private function parseBulletList(string $text): array
    {
        $items = [];

        // Match bullet points (-, *, •, numbered lists)
        if (preg_match_all('/(?:^|\n)\s*[-*•]\s+(.+?)(?=\n\s*[-*•]|\n\n|$)/s', $text, $matches)) {
            $items = array_map('trim', $matches[1]);
        }

        // Also try numbered lists
        if (empty($items)) {
            if (preg_match_all('/(?:^|\n)\s*\d+\.\s+(.+?)(?=\n\s*\d+\.|\n\n|$)/s', $text, $matches)) {
                $items = array_map('trim', $matches[1]);
            }
        }

        return $items;
    }

    /**
     * Parse and interpret a value (handle booleans, numbers, etc.)
     *
     * @param string|mixed $value
     * @return mixed
     */
    private function parseValue(string|mixed $value): mixed
    {
        if (!is_string($value)) {
            return $value;
        }

        $value = trim($value);

        // Boolean values
        if (in_array(strtolower($value), ['true', 'yes', 'on'], true)) {
            return true;
        }
        if (in_array(strtolower($value), ['false', 'no', 'off'], true)) {
            return false;
        }

        // Numeric values
        if (is_numeric($value)) {
            return str_contains($value, '.') ? (float) $value : (int) $value;
        }

        // JSON arrays/objects
        if ((str_starts_with($value, '[') && str_ends_with($value, ']'))
            || (str_starts_with($value, '{') && str_ends_with($value, '}'))) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        return $value;
    }

    /**
     * Normalize a value to expected type
     *
     * @param mixed $value
     * @param string $type Expected type (int, float, string, bool, array, object)
     * @return mixed
     *
     * @throws \RuntimeException If normalization fails
     */
    private function normalizeValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'int', 'integer' => (int) $value,
            'float', 'double' => (float) $value,
            'string' => (string) $value,
            'bool', 'boolean' => (bool) $value,
            'array' => is_array($value) ? $value : [$value],
            'object' => is_object($value) ? $value : (object) $value,
            default => throw new \RuntimeException("Unknown type: $type"),
        };
    }
}
