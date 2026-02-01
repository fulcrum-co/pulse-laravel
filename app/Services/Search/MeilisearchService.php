<?php

namespace App\Services\Search;

use App\Models\ContentBlock;
use App\Models\MiniCourse;
use App\Models\Program;
use App\Models\Provider;
use App\Models\Resource;
use Illuminate\Support\Facades\Log;
use Meilisearch\Client;

class MeilisearchService
{
    protected Client $client;

    protected array $searchableModels = [
        Resource::class,
        MiniCourse::class,
        ContentBlock::class,
        Provider::class,
        Program::class,
    ];

    public function __construct()
    {
        $this->client = new Client(
            config('scout.meilisearch.host'),
            config('scout.meilisearch.key')
        );
    }

    /**
     * Get the Meilisearch client instance.
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Configure all indexes with their settings from config.
     */
    public function configureIndexes(): array
    {
        $results = [];
        $settings = config('scout.meilisearch.index-settings', []);

        foreach ($settings as $indexName => $indexSettings) {
            try {
                $index = $this->client->index($indexName);

                // Update settings
                $task = $index->updateSettings($indexSettings);
                $results[$indexName] = [
                    'status' => 'configured',
                    'task_uid' => $task['taskUid'] ?? null,
                ];

                Log::info("Meilisearch: Configured index {$indexName}");
            } catch (\Exception $e) {
                $results[$indexName] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];

                Log::error("Meilisearch: Failed to configure index {$indexName}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Perform a unified search across multiple model types.
     */
    public function unifiedSearch(
        string $query,
        array $filters = [],
        array $facets = [],
        int $limit = 20,
        int $offset = 0
    ): array {
        $results = [
            'resources' => [],
            'courses' => [],
            'content_blocks' => [],
            'providers' => [],
            'programs' => [],
            'total_hits' => 0,
            'facet_distribution' => [],
        ];

        // Build filter string for organization scope
        $filterString = $this->buildFilterString($filters);

        // Search each index
        $searches = [
            'resources' => [
                'indexUid' => config('scout.prefix').'resources',
                'q' => $query,
                'filter' => $filterString,
                'limit' => $limit,
                'offset' => $offset,
                'facets' => ['resource_type', 'category', 'target_grades'],
            ],
            'courses' => [
                'indexUid' => config('scout.prefix').'mini_courses',
                'q' => $query,
                'filter' => $filterString,
                'limit' => $limit,
                'offset' => $offset,
                'facets' => ['course_type', 'status', 'target_grades'],
            ],
            'content_blocks' => [
                'indexUid' => config('scout.prefix').'content_blocks',
                'q' => $query,
                'filter' => $filterString,
                'limit' => $limit,
                'offset' => $offset,
                'facets' => ['block_type', 'grade_levels', 'topics'],
            ],
            'providers' => [
                'indexUid' => config('scout.prefix').'providers',
                'q' => $query,
                'filter' => $filterString,
                'limit' => $limit,
                'offset' => $offset,
                'facets' => ['provider_type', 'specialties'],
            ],
            'programs' => [
                'indexUid' => config('scout.prefix').'programs',
                'q' => $query,
                'filter' => $filterString,
                'limit' => $limit,
                'offset' => $offset,
                'facets' => ['program_type', 'cost_structure', 'location_type'],
            ],
        ];

        try {
            $multiSearchResults = $this->client->multiSearch($searches);

            foreach ($multiSearchResults['results'] as $index => $searchResult) {
                $key = array_keys($searches)[$index];
                $results[$key] = $searchResult['hits'] ?? [];
                $results['total_hits'] += $searchResult['estimatedTotalHits'] ?? 0;

                if (isset($searchResult['facetDistribution'])) {
                    $results['facet_distribution'][$key] = $searchResult['facetDistribution'];
                }
            }
        } catch (\Exception $e) {
            Log::error('Meilisearch: Unified search failed', [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);
        }

        return $results;
    }

    /**
     * Search a specific model type.
     */
    public function searchModel(
        string $modelClass,
        string $query,
        array $filters = [],
        int $limit = 20,
        int $offset = 0,
        array $facets = []
    ): array {
        $model = new $modelClass;
        $indexName = $model->searchableAs();

        $filterString = $this->buildFilterString($filters);

        try {
            $searchParams = [
                'filter' => $filterString ?: null,
                'limit' => $limit,
                'offset' => $offset,
            ];

            if (! empty($facets)) {
                $searchParams['facets'] = $facets;
            }

            $result = $this->client->index($indexName)->search($query, $searchParams);

            return [
                'hits' => $result->getHits(),
                'total' => $result->getEstimatedTotalHits(),
                'facets' => $result->getFacetDistribution(),
                'processing_time_ms' => $result->getProcessingTimeMs(),
            ];
        } catch (\Exception $e) {
            Log::error("Meilisearch: Search failed for {$modelClass}", [
                'query' => $query,
                'error' => $e->getMessage(),
            ]);

            return [
                'hits' => [],
                'total' => 0,
                'facets' => [],
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Reindex all searchable models.
     */
    public function reindexAll(bool $fresh = false): array
    {
        $results = [];

        foreach ($this->searchableModels as $modelClass) {
            try {
                $model = new $modelClass;
                $indexName = $model->searchableAs();

                if ($fresh) {
                    // Delete existing index
                    try {
                        $this->client->deleteIndex($indexName);
                    } catch (\Exception $e) {
                        // Index may not exist, ignore
                    }
                }

                // Import all records
                $count = $modelClass::query()
                    ->when(method_exists($modelClass, 'withTrashed'), fn ($q) => $q->withoutTrashed())
                    ->searchable();

                $results[$modelClass] = [
                    'status' => 'reindexed',
                    'index' => $indexName,
                ];

                Log::info("Meilisearch: Reindexed {$modelClass}");
            } catch (\Exception $e) {
                $results[$modelClass] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];

                Log::error("Meilisearch: Failed to reindex {$modelClass}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Reindex all content for a specific organization.
     */
    public function reindexOrganization(int $orgId): array
    {
        $results = [];

        foreach ($this->searchableModels as $modelClass) {
            try {
                $count = $modelClass::where('org_id', $orgId)->searchable();
                $results[$modelClass] = [
                    'status' => 'reindexed',
                    'org_id' => $orgId,
                ];
            } catch (\Exception $e) {
                $results[$modelClass] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Get index statistics.
     */
    public function getIndexStats(): array
    {
        $stats = [];

        foreach ($this->searchableModels as $modelClass) {
            $model = new $modelClass;
            $indexName = $model->searchableAs();

            try {
                $indexStats = $this->client->index($indexName)->getStats();
                $stats[$indexName] = [
                    'numberOfDocuments' => $indexStats['numberOfDocuments'] ?? 0,
                    'isIndexing' => $indexStats['isIndexing'] ?? false,
                ];
            } catch (\Exception $e) {
                $stats[$indexName] = [
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $stats;
    }

    /**
     * Build a Meilisearch filter string from an array of filters.
     */
    protected function buildFilterString(array $filters): string
    {
        $filterParts = [];

        foreach ($filters as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            if (is_array($value)) {
                // Handle array values (IN clause)
                $escaped = array_map(fn ($v) => $this->escapeValue($v), $value);
                $filterParts[] = "{$field} IN [".implode(', ', $escaped).']';
            } elseif (is_bool($value)) {
                $filterParts[] = "{$field} = ".($value ? 'true' : 'false');
            } else {
                $filterParts[] = "{$field} = ".$this->escapeValue($value);
            }
        }

        return implode(' AND ', $filterParts);
    }

    /**
     * Escape a value for use in Meilisearch filters.
     */
    protected function escapeValue($value): string
    {
        if (is_numeric($value)) {
            return (string) $value;
        }

        // Escape quotes and wrap in quotes
        return '"'.str_replace('"', '\\"', $value).'"';
    }

    /**
     * Check if Meilisearch is healthy and accessible.
     */
    public function healthCheck(): array
    {
        try {
            $health = $this->client->health();
            $version = $this->client->version();

            return [
                'status' => 'healthy',
                'health' => $health,
                'version' => $version,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }
}
