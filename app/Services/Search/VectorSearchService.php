<?php

namespace App\Services\Search;

use App\Models\ContentBlock;
use App\Models\MiniCourse;
use App\Models\Program;
use App\Models\Provider;
use App\Models\Resource;
use App\Models\Learner;
use App\Services\Embeddings\EmbeddingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class VectorSearchService
{
    protected EmbeddingService $embeddingService;

    protected array $searchableModels = [
        'resources' => Resource::class,
        'courses' => MiniCourse::class,
        'content_blocks' => ContentBlock::class,
        'providers' => Provider::class,
        'programs' => Program::class,
    ];

    public function __construct(EmbeddingService $embeddingService)
    {
        $this->embeddingService = $embeddingService;
    }

    /**
     * Find similar items to a given model.
     *
     * @param  Model  $model  The model to find similar items for
     * @param  int  $limit  Maximum results
     * @param  float  $threshold  Minimum similarity (0-1)
     */
    public function findSimilar(Model $model, int $limit = 10, float $threshold = 0.5): Collection
    {
        if (empty($model->embedding)) {
            // Generate embedding if missing
            try {
                $this->embeddingService->generateEmbeddingForModel($model);
                $model->refresh();
            } catch (\Exception $e) {
                Log::warning('Could not generate embedding for similarity search', [
                    'model' => get_class($model),
                    'id' => $model->getKey(),
                    'error' => $e->getMessage(),
                ]);

                return collect();
            }
        }

        return $model->findSimilar($limit, $threshold);
    }

    /**
     * Search by text query using semantic similarity.
     *
     * @param  string  $query  The search query
     * @param  string  $modelClass  The model class to search
     * @param  array  $filters  Additional filters (org_id, etc.)
     * @param  int  $limit  Maximum results
     * @param  float  $threshold  Minimum similarity
     */
    public function searchByText(
        string $query,
        string $modelClass,
        array $filters = [],
        int $limit = 20,
        float $threshold = 0.3
    ): Collection {
        try {
            // Generate embedding for the query
            $result = $this->embeddingService->generateEmbedding($query);
            $queryEmbedding = '['.implode(',', $result['embedding']).']';

            // Build query
            $builder = $modelClass::query()
                ->whereNotNull('embedding')
                ->selectRaw('*, 1 - (embedding <=> ?) as similarity', [$queryEmbedding]);

            // Apply filters
            if (isset($filters['org_id'])) {
                $builder->where('org_id', $filters['org_id']);
            }

            if (isset($filters['org_ids'])) {
                $builder->whereIn('org_id', $filters['org_ids']);
            }

            // Apply model-specific filters
            $this->applyModelFilters($builder, $modelClass, $filters);

            return $builder
                ->having('similarity', '>=', $threshold)
                ->orderByDesc('similarity')
                ->limit($limit)
                ->get();
        } catch (\Exception $e) {
            Log::error('Semantic search failed', [
                'query' => $query,
                'model' => $modelClass,
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * Search across multiple model types.
     *
     * @param  string  $query  The search query
     * @param  array  $types  Model types to search (keys from $searchableModels)
     * @param  array  $filters  Common filters
     * @param  int  $limitPerType  Results per model type
     */
    public function searchAcrossTypes(
        string $query,
        array $types = [],
        array $filters = [],
        int $limitPerType = 10
    ): array {
        // Default to all types if none specified
        if (empty($types)) {
            $types = array_keys($this->searchableModels);
        }

        $results = [];

        foreach ($types as $type) {
            if (! isset($this->searchableModels[$type])) {
                continue;
            }

            $results[$type] = $this->searchByText(
                $query,
                $this->searchableModels[$type],
                $filters,
                $limitPerType
            );
        }

        return $results;
    }

    /**
     * Get resource recommendations for a learner based on their profile.
     *
     * @param  Learner  $learner  The learner to get recommendations for
     * @param  int  $limit  Maximum results
     */
    public function getRecommendationsForLearner(Learner $learner, int $limit = 10): Collection
    {
        // Build context text from learner profile
        $context = $this->buildLearnerContext($learner);

        if (empty($context)) {
            return collect();
        }

        try {
            // Generate embedding for learner context
            $result = $this->embeddingService->generateEmbedding($context);
            $contextEmbedding = '['.implode(',', $result['embedding']).']';

            // Search for matching resources and courses
            $resources = Resource::query()
                ->whereNotNull('embedding')
                ->where('active', true)
                ->where('org_id', $learner->org_id)
                ->selectRaw('*, 1 - (embedding <=> ?) as similarity, ? as type', [$contextEmbedding, 'resource'])
                ->having('similarity', '>=', 0.3)
                ->orderByDesc('similarity')
                ->limit($limit);

            $courses = MiniCourse::query()
                ->whereNotNull('embedding')
                ->where('status', MiniCourse::STATUS_ACTIVE)
                ->where('org_id', $learner->org_id)
                ->selectRaw('*, 1 - (embedding <=> ?) as similarity, ? as type', [$contextEmbedding, 'course'])
                ->having('similarity', '>=', 0.3)
                ->orderByDesc('similarity')
                ->limit($limit);

            // Combine and sort by similarity
            return $resources->get()
                ->concat($courses->get())
                ->sortByDesc('similarity')
                ->take($limit)
                ->values();
        } catch (\Exception $e) {
            Log::error('Failed to get learner recommendations', [
                'learner_id' => $learner->id,
                'error' => $e->getMessage(),
            ]);

            return collect();
        }
    }

    /**
     * Build context text from learner profile for recommendations.
     */
    protected function buildLearnerContext(Learner $learner): string
    {
        $parts = [];

        // Grade level
        if ($learner->grade_level) {
            $parts[] = "Grade {$learner->grade_level} learner";
        }

        // Risk level
        if ($learner->risk_level) {
            $parts[] = "Risk level: {$learner->risk_level}";
        }

        // Tags/needs
        if (! empty($learner->tags)) {
            $tags = is_array($learner->tags) ? $learner->tags : json_decode($learner->tags, true);
            if (! empty($tags)) {
                $parts[] = 'Needs: '.implode(', ', $tags);
            }
        }

        // IEP/ELL status
        if ($learner->iep_status) {
            $parts[] = 'Has IEP';
        }
        if ($learner->ell_status) {
            $parts[] = 'English Language Learner';
        }

        // Recent survey data could be added here
        // $parts[] = $this->getRecentSurveyInsights($learner);

        return implode('. ', $parts);
    }

    /**
     * Apply model-specific filters to query builder.
     */
    protected function applyModelFilters($builder, string $modelClass, array $filters): void
    {
        switch ($modelClass) {
            case Resource::class:
                if (isset($filters['resource_type'])) {
                    $builder->where('resource_type', $filters['resource_type']);
                }
                if (isset($filters['category'])) {
                    $builder->where('category', $filters['category']);
                }
                if (isset($filters['target_grades'])) {
                    $builder->whereJsonContains('target_grades', $filters['target_grades']);
                }
                $builder->where('active', true);
                break;

            case MiniCourse::class:
                if (isset($filters['course_type'])) {
                    $builder->where('course_type', $filters['course_type']);
                }
                if (isset($filters['status'])) {
                    $builder->where('status', $filters['status']);
                } else {
                    $builder->where('status', MiniCourse::STATUS_ACTIVE);
                }
                break;

            case ContentBlock::class:
                if (isset($filters['block_type'])) {
                    $builder->where('block_type', $filters['block_type']);
                }
                $builder->where('status', ContentBlock::STATUS_ACTIVE);
                break;

            case Provider::class:
                if (isset($filters['provider_type'])) {
                    $builder->where('provider_type', $filters['provider_type']);
                }
                $builder->where('active', true);
                break;

            case Program::class:
                if (isset($filters['program_type'])) {
                    $builder->where('program_type', $filters['program_type']);
                }
                $builder->where('active', true);
                break;
        }
    }

    /**
     * Get search statistics.
     */
    public function getStats(): array
    {
        $stats = [];

        foreach ($this->searchableModels as $name => $class) {
            $stats[$name] = [
                'total' => $class::count(),
                'with_embedding' => $class::whereNotNull('embedding')->count(),
                'missing_embedding' => $class::whereNull('embedding')->count(),
            ];
        }

        return $stats;
    }
}
