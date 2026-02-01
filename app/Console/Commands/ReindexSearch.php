<?php

namespace App\Console\Commands;

use App\Models\ContentBlock;
use App\Models\MiniCourse;
use App\Models\Program;
use App\Models\Provider;
use App\Models\Resource;
use App\Services\Search\MeilisearchService;
use Illuminate\Console\Command;

class ReindexSearch extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pulse:search:reindex
                            {--model= : Specific model class to reindex (e.g., Resource)}
                            {--org= : Specific organization ID to reindex}
                            {--fresh : Delete and recreate indexes before reindexing}';

    /**
     * The console command description.
     */
    protected $description = 'Reindex all searchable content into Meilisearch';

    protected array $models = [
        'Resource' => Resource::class,
        'MiniCourse' => MiniCourse::class,
        'ContentBlock' => ContentBlock::class,
        'Provider' => Provider::class,
        'Program' => Program::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle(MeilisearchService $service): int
    {
        // Health check
        $health = $service->healthCheck();
        if ($health['status'] !== 'healthy') {
            $this->error('Meilisearch is not accessible: ' . ($health['error'] ?? 'Unknown error'));
            return Command::FAILURE;
        }

        $this->info('Connected to Meilisearch');
        $this->line('');

        // Determine which models to reindex
        $modelsToReindex = $this->models;

        if ($modelName = $this->option('model')) {
            if (!isset($this->models[$modelName])) {
                $this->error("Unknown model: {$modelName}");
                $this->line('Available models: ' . implode(', ', array_keys($this->models)));
                return Command::FAILURE;
            }
            $modelsToReindex = [$modelName => $this->models[$modelName]];
        }

        $orgId = $this->option('org');
        $fresh = $this->option('fresh');

        if ($fresh) {
            $this->warn('Fresh mode: Indexes will be cleared before reindexing.');
            if (!$this->confirm('Continue?')) {
                return Command::SUCCESS;
            }
        }

        // Reindex each model
        foreach ($modelsToReindex as $name => $class) {
            $this->reindexModel($name, $class, $orgId, $fresh);
        }

        $this->line('');
        $this->info('Reindexing complete!');

        // Show final stats
        $stats = $service->getIndexStats();
        $this->table(
            ['Index', 'Documents'],
            collect($stats)->map(fn($stat, $index) => [
                $index,
                $stat['numberOfDocuments'] ?? 'N/A',
            ])->toArray()
        );

        return Command::SUCCESS;
    }

    /**
     * Reindex a specific model.
     */
    protected function reindexModel(string $name, string $class, ?int $orgId, bool $fresh): void
    {
        $this->info("Reindexing {$name}...");

        $query = $class::query();

        // Filter by organization if specified
        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        // Get count first
        $count = $query->count();
        $this->line("  Found {$count} records");

        if ($count === 0) {
            $this->line('  Skipping (no records)');
            return;
        }

        // Fresh start - unsearchable first
        if ($fresh) {
            $this->line('  Removing from index...');
            $query->unsearchable();
            // Re-run the query since unsearchable() consumes it
            $query = $class::query();
            if ($orgId) {
                $query->where('org_id', $orgId);
            }
        }

        // Index in chunks with progress bar
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        $query->chunk(100, function ($records) use ($bar) {
            $records->searchable();
            $bar->advance($records->count());
        });

        $bar->finish();
        $this->line('');
        $this->info("  Indexed {$count} {$name} records");
    }
}
