<?php

namespace App\Services\Embeddings;

interface EmbeddingProviderInterface
{
    /**
     * Generate an embedding for a single text.
     *
     * @param  string  $text  The text to embed
     * @return array{embedding: array<float>, model: string, tokens: int}
     */
    public function embed(string $text): array;

    /**
     * Generate embeddings for multiple texts.
     *
     * @param  array<string>  $texts  The texts to embed
     * @return array<array{embedding: array<float>, model: string, tokens: int}>
     */
    public function embedBatch(array $texts): array;

    /**
     * Get the number of dimensions for this provider's embeddings.
     */
    public function getDimensions(): int;

    /**
     * Get the model identifier used by this provider.
     */
    public function getModel(): string;

    /**
     * Get the maximum input tokens supported.
     */
    public function getMaxTokens(): int;
}
