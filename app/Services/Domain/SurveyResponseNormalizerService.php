<?php

declare(strict_types=1);

namespace App\Services\Domain;

/**
 * SurveyResponseNormalizerService
 *
 * Handles normalization of survey responses from different input channels:
 * text responses, DTMF digits, and other formats. Converts raw input to
 * standardized response values based on question type.
 */
class SurveyResponseNormalizerService
{
    /**
     * Normalize a text response based on question type.
     *
     * Converts text input to appropriate response value (scale value,
     * multiple choice selection, or literal text).
     *
     * @param  string  $response  Raw text response
     * @param  array  $question  Question structure
     * @return mixed Normalized response value
     */
    public function normalizeTextResponse(string $response, array $question): mixed
    {
        $response = trim($response);

        if ($question['type'] === 'scale') {
            return $this->normalizeScaleResponse($response, $question);
        }

        if ($question['type'] === 'multiple_choice') {
            return $this->normalizeMultipleChoiceResponse($response, $question);
        }

        // For text questions, return as-is
        return $response;
    }

    /**
     * Normalize DTMF (phone keypad) response.
     *
     * Processes digit input from voice calls and converts to response values.
     *
     * @param  string  $dtmf  DTMF digit string
     * @param  array  $question  Question structure
     * @return mixed Normalized response value
     */
    public function normalizeDtmfResponse(string $dtmf, array $question): mixed
    {
        // DTMF digits are typically single characters
        $digit = substr($dtmf, 0, 1);

        if ($question['type'] === 'scale' && is_numeric($digit)) {
            return $this->normalizeScaleResponse($digit, $question);
        }

        if ($question['type'] === 'multiple_choice' && is_numeric($digit)) {
            return $this->normalizeMultipleChoiceResponse($digit, $question);
        }

        return $digit;
    }

    /**
     * Normalize scale question response.
     *
     * Ensures numeric response is within min/max bounds.
     *
     * @param  string|int  $response  Numeric response
     * @param  array  $question  Question structure with min/max
     * @return int Normalized scale value
     */
    protected function normalizeScaleResponse(string|int $response, array $question): int
    {
        if (!is_numeric($response)) {
            // Return neutral default if not numeric
            $min = $question['min'] ?? 1;
            $max = $question['max'] ?? 5;
            return (int) ceil(($max - $min + 1) / 2);
        }

        $value = (int) $response;
        $min = $question['min'] ?? 1;
        $max = $question['max'] ?? 5;

        // Clamp value between min and max
        return max($min, min($max, $value));
    }

    /**
     * Normalize multiple choice response.
     *
     * Converts numeric selection to actual option value.
     *
     * @param  string|int  $response  Option selection (1-based index)
     * @param  array  $question  Question structure with options
     * @return string|int Selected option value or original response if invalid
     */
    protected function normalizeMultipleChoiceResponse(string|int $response, array $question): string|int
    {
        if (!is_numeric($response)) {
            return $response;
        }

        $options = $question['options'] ?? [];
        $index = (int) $response - 1;

        return $options[$index] ?? $response;
    }

    /**
     * Validate phone number format.
     *
     * Checks if a phone number is valid before processing.
     *
     * @param  string  $phone  Phone number to validate
     * @return bool True if valid phone format
     */
    public function isValidPhoneNumber(string $phone): bool
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        return strlen($cleaned) >= 10 && strlen($cleaned) <= 15;
    }

    /**
     * Format phone number to E.164 standard.
     *
     * @param  string  $phone  Raw phone number
     * @return string E.164 formatted number
     */
    public function formatPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Add +1 if US number without country code
        if (strlen($cleaned) === 10) {
            return '+1' . $cleaned;
        }

        // Add + if not present
        if (strlen($cleaned) === 11 && str_starts_with($cleaned, '1')) {
            return '+' . $cleaned;
        }

        // Return with + prefix
        return '+' . $cleaned;
    }
}
