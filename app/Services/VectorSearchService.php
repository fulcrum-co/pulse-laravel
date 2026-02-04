<?php

namespace App\Services;

use App\Models\MiniCourse;
use App\Models\Program;
use App\Models\Provider;
use App\Models\Resource;
use App\Services\Embeddings\EmbeddingService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class VectorSearchService
{
    protected EmbeddingService $embeddingService;

    /**
     * Models that support semantic search via embeddings.
     */
    protected array $searchableModels = [
        'content' => Resource::class,
        'provider' => Provider::class,
        'program' => Program::class,
        'course' => MiniCourse::class,
    ];

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Search across multiple model types using vector similarity.
     *
     * @param  string  $query  The search query text
     * @param  array  $orgIds  Organization IDs to search within
     * @param  array  $modelTypes  Model types to search (content, provider, program, course)
     * @param  int  $limitPerType  Maximum results per model type
     * @param  float  $minSimilarity  Minimum similarity threshold (0-1)
     * @return array Results grouped by type with similarity scores
     */
    public function search(
        string $query,
        array $orgIds,
        array $modelTypes = [],
        int $limitPerType = 10,
        float $minSimilarity = 0.3
    ): array {
        if (strlen($query) < 3) {
            return [];
        }

        // Generate embedding for the query (with caching)
        $queryEmbedding = $this->getQueryEmbedding($query);

        if (! $queryEmbedding) {
            Log::warning('Failed to generate query embedding', ['query' => $query]);

            return [];
        }

        // If no specific types requested, search all
        $typesToSearch = empty($modelTypes)
            ? array_keys($this->searchableModels)
            : array_intersect($modelTypes, array_keys($this->searchableModels));

        $results = [];

        foreach ($typesToSearch as $type) {
            $modelClass = $this->searchableModels[$type];
            $results[$type] = $this->searchModel(
                $modelClass,
                $queryEmbedding,
                $orgIds,
                $limitPerType,
                $minSimilarity,
                $type
            );
        }

        return $results;
    }

    /**
     * Search a single model type by vector similarity.
     */
    protected function searchModel(
        string $modelClass,
        string $embedding,
        array $orgIds,
        int $limit,
        float $minSimilarity,
        string $type
    ): array {
        $query = $modelClass::query()
            ->whereIn('org_id', $orgIds)
            ->whereNotNull('embedding')
            ->selectRaw('*, 1 - (embedding <=> ?) as similarity', [$embedding])
            ->having('similarity', '>=', $minSimilarity)
            ->orderByDesc('similarity')
            ->limit($limit);

        // Apply model-specific filters
        $query = $this->applyModelFilters($query, $modelClass);

        $items = $query->get();

        return [
            'items' => $items->map(fn ($item) => $this->transformResult($item, $type)),
            'count' => $items->count(),
        ];
    }

    /**
     * Apply model-specific active/status filters.
     */
    protected function applyModelFilters($query, string $modelClass)
    {
        return match ($modelClass) {
            Resource::class => $query->where('active', true),
            Provider::class => $query->where('active', true),
            Program::class => $query->where('active', true),
            MiniCourse::class => $query->where('status', MiniCourse::STATUS_ACTIVE),
            default => $query,
        };
    }

    /**
     * Transform a model result into a standardized format.
     */
    protected function transformResult($item, string $type): array
    {
        $base = [
            'id' => $item->id,
            'type' => $type,
            'similarity' => round($item->similarity * 100, 1),
            'similarity_raw' => $item->similarity,
        ];

        return match ($type) {
            'content' => array_merge($base, [
                'title' => $item->title,
                'description' => $item->description,
                'subtitle' => ucfirst($item->resource_type ?? 'Resource'),
                'icon' => $this->getResourceIcon($item->resource_type ?? 'document'),
                'icon_bg' => 'blue',
                'url' => route('resources.show', $item),
            ]),
            'provider' => array_merge($base, [
                'title' => $item->name,
                'description' => $item->bio,
                'subtitle' => ucfirst($item->provider_type ?? 'Provider'),
                'icon' => 'user',
                'icon_bg' => 'purple',
                'url' => route('resources.providers.show', $item),
            ]),
            'program' => array_merge($base, [
                'title' => $item->name,
                'description' => $item->description,
                'subtitle' => ucfirst(str_replace('_', ' ', $item->program_type ?? 'Program')),
                'icon' => 'building-office',
                'icon_bg' => 'green',
                'url' => route('resources.programs.show', $item),
            ]),
            'course' => array_merge($base, [
                'title' => $item->title,
                'description' => $item->description,
                'subtitle' => ucfirst(str_replace('_', ' ', $item->course_type ?? 'Course')),
                'icon' => 'academic-cap',
                'icon_bg' => 'orange',
                'url' => route('resources.courses.show', $item),
            ]),
            default => $base,
        };
    }

    /**
     * Get or generate embedding for a query string.
     * Caches embeddings for frequently used queries.
     */
    protected function getQueryEmbedding(string $query): ?string
    {
        $cacheKey = 'query_embedding_'.md5(strtolower(trim($query)));

        return Cache::remember($cacheKey, 3600, function () use ($query) {
            try {
                $result = $this->embeddingService->generateEmbedding($query);

                return '['.implode(',', $result['embedding']).']';
            } catch (\Exception $e) {
                Log::error('Query embedding generation failed', [
                    'query' => $query,
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    /**
     * Find similar items to a given model.
     */
    public function findSimilarTo(
        $model,
        array $orgIds,
        int $limit = 5,
        float $minSimilarity = 0.5
    ): Collection {
        if (empty($model->embedding)) {
            return collect();
        }

        return $model->findSimilar($limit, $minSimilarity)
            ->filter(fn ($item) => in_array($item->org_id, $orgIds));
    }

    /**
     * Search for resources relevant to a topic (for RAG/course generation).
     */
    public function findRelevantResources(
        string $topic,
        array $orgIds,
        int $limit = 20,
        float $minSimilarity = 0.4
    ): Collection {
        $queryEmbedding = $this->getQueryEmbedding($topic);

        if (! $queryEmbedding) {
            return collect();
        }

        return Resource::query()
            ->whereIn('org_id', $orgIds)
            ->where('active', true)
            ->whereNotNull('embedding')
            ->selectRaw('*, 1 - (embedding <=> ?) as similarity', [$queryEmbedding])
            ->having('similarity', '>=', $minSimilarity)
            ->orderByDesc('similarity')
            ->limit($limit)
            ->get();
    }

    /**
     * Get icon for resource type.
     */
    protected function getResourceIcon(string $type): string
    {
        return match ($type) {
            'article' => 'document-text',
            'video' => 'play-circle',
            'worksheet' => 'clipboard-document-list',
            'activity' => 'puzzle-piece',
            'link' => 'link',
            'document' => 'document',
            default => 'document',
        };
    }

    /**
     * Check if vector search is available (embeddings configured).
     */
    public function isAvailable(): bool
    {
        return config('services.openai.api_key') !== null;
    }
}
