<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class AIExtractionService
{
    public function __construct(
        protected ClaudeService $claudeService
    ) {}

    public function extract(string $transcript, array $schemaMapping): array
    {
        $schema = json_encode($schemaMapping, JSON_PRETTY_PRINT);
        $prompt = "Extract values for these fields: {$schema}\n\n".
            "Transcript:\n{$transcript}\n\n".
            'Return only valid JSON.';

        $response = $this->claudeService->sendMessage($prompt);

        if (! ($response['success'] ?? false)) {
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Extraction failed',
            ];
        }

        $content = $response['content'] ?? '';
        if (preg_match('/\{[\s\S]*\}/', $content, $matches)) {
            $json = $matches[0];
            $data = json_decode($json, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return [
                    'success' => true,
                    'data' => $data,
                    'raw' => $content,
                ];
            }
        }

        Log::warning('AIExtractionService: invalid JSON response', ['content' => $content]);

        return [
            'success' => false,
            'error' => 'Invalid JSON returned by AI',
            'raw' => $content,
        ];
    }
}
