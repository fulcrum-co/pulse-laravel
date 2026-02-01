<?php

namespace App\Traits;

use App\Jobs\GenerateEmbeddingJob;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Trait for models that support vector embeddings for semantic search.
 *
 * Models using this trait must implement:
 * - getEmbeddingText(): string - Returns the text to be embedded
 */
trait HasEmbedding
{
    /**
     * Boot the trait.
     */
    protected static function bootHasEmbedding(): void
    {
        // Auto-generate embedding when model is created
        static::created(function ($model) {
            if (config('services.embeddings.auto_generate', true)) {
                $model->queueEmbeddingGeneration();
            }
        });

        // Regenerate embedding when model is updated (if content changed)
        static::updated(function ($model) {
            if (config('services.embeddings.auto_generate', true)) {
                $model->queueEmbeddingGenerationIfNeeded();
            }
        });
    }

    /**
     * Queue embedding generation for this model.
     */
    public function queueEmbeddingGeneration(): void
    {
        GenerateEmbeddingJob::dispatch($this);
    }

    /**
     * Queue embedding generation only if needed (content changed).
     */
    public function queueEmbeddingGenerationIfNeeded(): void
    {
        // Check if any fields that affect the embedding text have changed
        $embeddingFields = $this->getEmbeddingTextFields();

        foreach ($embeddingFields as $field) {
            if ($this->wasChanged($field)) {
                $this->queueEmbeddingGeneration();

                return;
            }
        }
    }

    /**
     * Get the fields that contribute to the embedding text.
     * Override in model to customize.
     */
    protected function getEmbeddingTextFields(): array
    {
        return ['title', 'name', 'description', 'content'];
    }

    /**
     * Get the text to be embedded.
     * Must be implemented by the model.
     */
    abstract public function getEmbeddingText(): string;

    /**
     * Find similar models using cosine similarity.
     *
     * @param  int  $limit  Maximum number of results
     * @param  float  $threshold  Minimum similarity score (0-1)
     */
    public function findSimilar(int $limit = 10, float $threshold = 0.5): Collection
    {
        if (empty($this->embedding)) {
            return collect();
        }

        return static::query()
            ->where('id', '!=', $this->id)
            ->whereNotNull('embedding')
            ->when($this->org_id ?? null, fn ($q) => $q->where('org_id', $this->org_id))
            ->selectRaw('*, 1 - (embedding <=> ?) as similarity', [$this->embedding])
            ->having('similarity', '>=', $threshold)
            ->orderByDesc('similarity')
            ->limit($limit)
            ->get();
    }

    /**
     * Scope to find nearest neighbors to a given embedding.
     *
     * @param  string  $embedding  The embedding vector (formatted as '[...]')
     */
    public function scopeNearestTo(Builder $query, string $embedding, int $limit = 10): Builder
    {
        return $query
            ->whereNotNull('embedding')
            ->selectRaw('*, 1 - (embedding <=> ?) as similarity', [$embedding])
            ->orderByDesc('similarity')
            ->limit($limit);
    }

    /**
     * Scope to filter by minimum similarity to an embedding.
     */
    public function scopeWithMinSimilarity(Builder $query, string $embedding, float $threshold = 0.5): Builder
    {
        return $query
            ->whereNotNull('embedding')
            ->selectRaw('*, 1 - (embedding <=> ?) as similarity', [$embedding])
            ->having('similarity', '>=', $threshold);
    }

    /**
     * Scope to models that have embeddings.
     */
    public function scopeHasEmbedding(Builder $query): Builder
    {
        return $query->whereNotNull('embedding');
    }

    /**
     * Scope to models missing embeddings.
     */
    public function scopeMissingEmbedding(Builder $query): Builder
    {
        return $query->whereNull('embedding');
    }

    /**
     * Scope to models with stale embeddings (updated after embedding was generated).
     */
    public function scopeStaleEmbedding(Builder $query): Builder
    {
        return $query
            ->whereNotNull('embedding')
            ->whereNotNull('embedding_generated_at')
            ->whereColumn('updated_at', '>', 'embedding_generated_at');
    }

    /**
     * Check if this model has an embedding.
     */
    public function hasEmbedding(): bool
    {
        return ! empty($this->embedding);
    }

    /**
     * Check if the embedding needs to be regenerated.
     */
    public function needsEmbeddingUpdate(): bool
    {
        if (! $this->hasEmbedding()) {
            return true;
        }

        if ($this->embedding_generated_at && $this->updated_at) {
            return $this->updated_at > $this->embedding_generated_at;
        }

        return false;
    }

    /**
     * Calculate cosine similarity between this model and another.
     */
    public function cosineSimilarityTo(self $other): ?float
    {
        if (empty($this->embedding) || empty($other->embedding)) {
            return null;
        }

        // Use PostgreSQL's built-in cosine distance
        $result = DB::selectOne(
            'SELECT 1 - (? <=> ?) as similarity',
            [$this->embedding, $other->embedding]
        );

        return $result->similarity;
    }
}
