<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search connection that gets used while
    | using Laravel Scout. This connection is used when syncing all models
    | to the search service. You should adjust this based on your needs.
    |
    | Supported: "algolia", "meilisearch", "typesense",
    |            "database", "collection", "null"
    |
    */

    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify a prefix that will be applied to all search index
    | names used by Scout. This prefix may be useful if you have multiple
    | "tenants" or applications sharing the same search infrastructure.
    |
    */

    'prefix' => env('SCOUT_PREFIX', 'pulse_'),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the operations that sync your data
    | with your search engines are queued. When this is set to "true" then
    | all automatic data syncing will get queued for better performance.
    |
    */

    'queue' => env('SCOUT_QUEUE', true),

    /*
    |--------------------------------------------------------------------------
    | Database Transactions
    |--------------------------------------------------------------------------
    |
    | This configuration option determines if your data will only be synced
    | with your search indexes after every open database transaction has
    | been committed, thus preventing any discarded data from syncing.
    |
    */

    'after_commit' => true,

    /*
    |--------------------------------------------------------------------------
    | Chunk Sizes
    |--------------------------------------------------------------------------
    |
    | These options allow you to control the maximum chunk size when you are
    | mass importing data into the search engine. This allows you to fine
    | tune each of these chunk sizes based on the power of the servers.
    |
    */

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Soft Deletes
    |--------------------------------------------------------------------------
    |
    | This option allows to control whether to keep soft deleted records in
    | the search indexes. Maintaining soft deleted records can be useful
    | if your application still needs to search for the deleted records.
    |
    */

    'soft_delete' => false,

    /*
    |--------------------------------------------------------------------------
    | Identify User
    |--------------------------------------------------------------------------
    |
    | This option allows you to control whether to notify the search engine
    | of the user performing the search. This is sometimes useful if the
    | engine supports any analytics based on this application's users.
    |
    | Supported engines: "algolia"
    |
    */

    'identify' => env('SCOUT_IDENTIFY', false),

    /*
    |--------------------------------------------------------------------------
    | Meilisearch Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Meilisearch settings. Meilisearch is an open
    | source search engine with minimal configuration. Below, you can state
    | the host and key information for your own Meilisearch installation.
    |
    | See: https://www.meilisearch.com/docs/learn/configuration/instance_options
    |
    */

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            // Resources index configuration
            'pulse_resources' => [
                'filterableAttributes' => [
                    'org_id',
                    'resource_type',
                    'category',
                    'target_grades',
                    'target_risk_levels',
                    'is_active',
                ],
                'sortableAttributes' => [
                    'created_at',
                    'updated_at',
                    'title',
                ],
                'searchableAttributes' => [
                    'title',
                    'description',
                    'tags',
                    'category',
                ],
                'typoTolerance' => [
                    'enabled' => true,
                    'minWordSizeForTypos' => [
                        'oneTypo' => 4,
                        'twoTypos' => 8,
                    ],
                ],
            ],

            // Mini Courses index configuration
            'pulse_mini_courses' => [
                'filterableAttributes' => [
                    'org_id',
                    'course_type',
                    'status',
                    'creation_source',
                    'target_grades',
                    'target_risk_levels',
                    'target_needs',
                    'is_template',
                    'approval_status',
                ],
                'sortableAttributes' => [
                    'created_at',
                    'updated_at',
                    'title',
                    'calculated_duration_minutes',
                ],
                'searchableAttributes' => [
                    'title',
                    'description',
                    'objectives',
                    'rationale',
                ],
                'typoTolerance' => [
                    'enabled' => true,
                ],
            ],

            // Content Blocks index configuration
            'pulse_content_blocks' => [
                'filterableAttributes' => [
                    'org_id',
                    'block_type',
                    'source_type',
                    'status',
                    'topics',
                    'skills',
                    'grade_levels',
                    'subject_areas',
                    'target_risk_factors',
                    'iep_appropriate',
                    'language',
                ],
                'sortableAttributes' => [
                    'created_at',
                    'title',
                    'usage_count',
                    'avg_rating',
                ],
                'searchableAttributes' => [
                    'title',
                    'description',
                    'topics',
                    'skills',
                ],
            ],

            // Providers index configuration
            'pulse_providers' => [
                'filterableAttributes' => [
                    'org_id',
                    'provider_type',
                    'specialties',
                    'is_verified',
                    'serves_remote',
                    'serves_in_person',
                    'is_active',
                ],
                'sortableAttributes' => [
                    'created_at',
                    'display_name',
                    'avg_rating',
                ],
                'searchableAttributes' => [
                    'display_name',
                    'bio',
                    'specialties',
                    'credentials',
                ],
            ],

            // Programs index configuration
            'pulse_programs' => [
                'filterableAttributes' => [
                    'org_id',
                    'program_type',
                    'cost_structure',
                    'location_type',
                    'target_needs',
                    'is_active',
                    'has_availability',
                ],
                'sortableAttributes' => [
                    'created_at',
                    'name',
                    'start_date',
                ],
                'searchableAttributes' => [
                    'name',
                    'description',
                    'target_needs',
                    'eligibility_criteria',
                ],
            ],
        ],
    ],

];
