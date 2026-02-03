<?php

declare(strict_types=1);

namespace App\Services\Domain;

class VoiceMemoExtractionService
{
    /**
     * Generate a note summary from transcription and extracted data.
     */
    public function generateNoteSummary(string $transcription, array $data): string
    {
        $summary = '';

        if (!empty($data['summary'])) {
            $summary = $data['summary'] . "\n\n";
        }

        if (!empty($data['concerns'])) {
            $summary .= '**Concerns:** ' . implode(', ', $data['concerns']) . "\n";
        }

        if (!empty($data['positives'])) {
            $summary .= '**Positives:** ' . implode(', ', $data['positives']) . "\n";
        }

        if (!empty($data['action_items'])) {
            $summary .= '**Action Items:** ' . implode(', ', $data['action_items']) . "\n";
        }

        if (empty($summary)) {
            // Use first 500 characters of transcription
            $summary = strlen($transcription) > 500
                ? substr($transcription, 0, 500) . '...'
                : $transcription;
        }

        return trim($summary);
    }

    /**
     * Build extraction system prompt.
     */
    public function buildExtractionPrompt(): string
    {
        return <<<'PROMPT'
You are analyzing a voice memo from an educator about a participant or contact. Extract structured information from the transcription.

Return a JSON object with:
- summary: A 1-2 sentence summary of the key points
- concerns: Array of any concerns or issues mentioned
- positives: Array of positive observations or achievements
- action_items: Array of any follow-up actions mentioned
- topics: Array of main topics discussed (e.g., "academic progress", "behavior", "attendance", "social-emotional")
- sentiment: Overall sentiment (positive, neutral, negative, mixed)
- urgency: Level of urgency if any concerns (none, low, medium, high)

Only return valid JSON, no other text.
PROMPT;
    }

    /**
     * Build extraction user message.
     */
    public function buildExtractionMessage(string $transcription): string
    {
        return "Analyze this voice memo transcription:\n\n{$transcription}";
    }

    /**
     * Validate extracted data structure.
     */
    public function validateExtractedData(array $data): bool
    {
        $requiredFields = ['summary', 'concerns', 'positives', 'action_items', 'topics', 'sentiment', 'urgency'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }

        return true;
    }
}
