<?php

namespace App\Services\Embeddings;

use App\Jobs\GenerateEmbeddingJob;
use App\Models\EmbeddingJob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class EmbeddingService
{
    protected EmbeddingProviderInterface $provider;

    public function __construct(?EmbeddingProviderInterface $provider = null)
    {
        $this->provider = $provider ?? new OpenAIEmbeddingProvider;
    }

    /**
     * Generate and store embedding for a single model.
     */
    public function generateEmbeddingForModel(Model $model): bool
    {
        if (! method_exists($model, 'getEmbeddingText')) {
            throw new \InvalidArgumentException(
                get_class($model).' must implement getEmbeddingText() method'
            );
        }

        $text = $model->getEmbeddingText();

        if (empty($text)) {
            Log::warning('Empty text for embedding', [
                'model' => get_class($model),
                'id' => $model->getKey(),
            ]);

            return false;
        }

        // Truncate if needed
        $text = $this->provider->truncateToFit($text);

        try {
            $startTime = microtime(true);
            $result = $this->provider->embed($text);
            $processingTime = (microtime(true) - $startTime) * 1000;

            // Store the embedding directly in the model
            $model->embedding = $this->formatEmbeddingForStorage($result['embedding']);
            $model->embedding_generated_at = now();
            $model->embedding_model = $result['model'];
            $model->save();

            Log::info('Generated embedding', [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'tokens' => $result['tokens'],
                'time_ms' => round($processingTime, 2),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to generate embedding', [
                'model' => get_class($model),
                'id' => $model->getKey(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Queue embedding generation for a model.
     */
    public function queueEmbeddingGeneration(Model $model): EmbeddingJob
    {
        // Create tracking record
        $job = EmbeddingJob::create([
            'org_id' => $model->org_id ?? null,
            'embeddable_type' => get_class($model),
            'embeddable_id' => $model->getKey(),
            'status' => 'pending',
            'embedding_model' => $this->provider->getModel(),
        ]);

        // Dispatch the actual job
        GenerateEmbeddingJob::dispatch($model);

        return $job;
    }

    /**
     * Generate embeddings for multiple models in batch.
     */
    public function generateEmbeddingsForModels(Collection $models): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        // Collect texts and models that have valid text
        $validModels = [];
        $texts = [];

        foreach ($models as $model) {
            if (! method_exists($model, 'getEmbeddingText')) {
                $results['skipped']++;

                continue;
            }

            $text = $model->getEmbeddingText();
            if (empty($text)) {
                $results['skipped']++;

                continue;
            }

            $validModels[] = $model;
            $texts[] = $this->provider->truncateToFit($text);
        }

        if (empty($texts)) {
            return $results;
        }

        try {
            $startTime = microtime(true);
            $embeddings = $this->provider->embedBatch($texts);

            foreach ($validModels as $index => $model) {
                if (isset($embeddings[$index])) {
                    $model->embedding = $this->formatEmbeddingForStorage($embeddings[$index]['embedding']);
                    $model->embedding_generated_at = now();
                    $model->embedding_model = $embeddings[$index]['model'];
                    $model->save();
                    $results['success']++;
                } else {
                    $results['failed']++;
                }
            }

            $processingTime = (microtime(true) - $startTime) * 1000;
            Log::info('Batch embedding generation complete', [
                'count' => count($texts),
                'success' => $results['success'],
                'time_ms' => round($processingTime, 2),
            ]);
        } catch (\Exception $e) {
            Log::error('Batch embedding generation failed', [
                'error' => $e->getMessage(),
            ]);
            $results['failed'] += count($validModels);
        }

        return $results;
    }

    /**
     * Generate embedding for raw text (without storing).
     */
    public function generateEmbedding(string $text): array
    {
        $text = $this->provider->truncateToFit($text);

        return $this->provider->embed($text);
    }

    /**
     * Format embedding array for PostgreSQL vector storage.
     */
    protected function formatEmbeddingForStorage(array $embedding): string
    {
        return '['.implode(',', $embedding).']';
    }

    /**
     * Check if embedding generation is needed for a model.
     */
    public function needsEmbedding(Model $model): bool
    {
        // No embedding yet
        if (empty($model->embedding)) {
            return true;
        }

        // Check if content has changed since embedding was generated
        if ($model->embedding_generated_at && $model->updated_at) {
            return $model->updated_at > $model->embedding_generated_at;
        }

        return false;
    }

    /**
     * Get the embedding provider instance.
     */
    public function getProvider(): EmbeddingProviderInterface
    {
        return $this->provider;
    }
}
