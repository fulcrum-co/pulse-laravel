<?php

namespace App\Services\Embeddings;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAIEmbeddingProvider implements EmbeddingProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected int $dimensions;
    protected int $maxTokens;

    public function __construct()
    {
        $this->apiKey = config('services.openai.api_key');
        $this->model = config('services.embeddings.model', 'text-embedding-3-small');
        $this->dimensions = (int) config('services.embeddings.dimensions', 1536);
        $this->maxTokens = (int) config('services.embeddings.max_tokens', 8191);
    }

    /**
     * Generate an embedding for a single text.
     */
    public function embed(string $text): array
    {
        $result = $this->callApi([$text]);

        return [
            'embedding' => $result['data'][0]['embedding'],
            'model' => $result['model'],
            'tokens' => $result['usage']['total_tokens'],
        ];
    }

    /**
     * Generate embeddings for multiple texts (batch).
     */
    public function embedBatch(array $texts): array
    {
        if (empty($texts)) {
            return [];
        }

        // OpenAI allows up to 2048 inputs per batch
        $batches = array_chunk($texts, 100);
        $results = [];

        foreach ($batches as $batch) {
            $response = $this->callApi($batch);

            foreach ($response['data'] as $item) {
                $results[] = [
                    'embedding' => $item['embedding'],
                    'model' => $response['model'],
                    'tokens' => (int) ceil($response['usage']['total_tokens'] / count($batch)),
                ];
            }
        }

        return $results;
    }

    /**
     * Get the number of dimensions for embeddings.
     */
    public function getDimensions(): int
    {
        return $this->dimensions;
    }

    /**
     * Get the model identifier.
     */
    public function getModel(): string
    {
        return $this->model;
    }

    /**
     * Get maximum tokens supported.
     */
    public function getMaxTokens(): int
    {
        return $this->maxTokens;
    }

    /**
     * Call the OpenAI embeddings API.
     */
    protected function callApi(array $inputs): array
    {
        $response = Http::withHeaders([
            'Authorization' => "Bearer {$this->apiKey}",
            'Content-Type' => 'application/json',
        ])->timeout(60)->post('https://api.openai.com/v1/embeddings', [
            'model' => $this->model,
            'input' => $inputs,
            'dimensions' => $this->dimensions,
        ]);

        if (!$response->successful()) {
            $error = $response->json('error.message', 'Unknown error');
            Log::error('OpenAI Embeddings API error', [
                'status' => $response->status(),
                'error' => $error,
            ]);
            throw new \RuntimeException("OpenAI API error: {$error}");
        }

        return $response->json();
    }

    /**
     * Estimate token count for text (rough approximation).
     * More accurate would be to use tiktoken, but this is a reasonable estimate.
     */
    public function estimateTokens(string $text): int
    {
        // Rough estimate: ~4 characters per token for English
        return (int) ceil(strlen($text) / 4);
    }

    /**
     * Truncate text to fit within token limit.
     */
    public function truncateToFit(string $text): string
    {
        $estimatedTokens = $this->estimateTokens($text);

        if ($estimatedTokens <= $this->maxTokens) {
            return $text;
        }

        // Truncate to approximate max length
        $maxChars = $this->maxTokens * 4;
        return mb_substr($text, 0, $maxChars);
    }
}
