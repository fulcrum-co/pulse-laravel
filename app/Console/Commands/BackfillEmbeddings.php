<?php

namespace App\Console\Commands;

use App\Models\ContentBlock;
use App\Models\MiniCourse;
use App\Models\Program;
use App\Models\Provider;
use App\Models\Resource;
use App\Services\Embeddings\EmbeddingService;
use Illuminate\Console\Command;

class BackfillEmbeddings extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pulse:embeddings:backfill
                            {--model= : Specific model class to backfill (e.g., Resource)}
                            {--org= : Specific organization ID to backfill}
                            {--limit= : Maximum number of records to process}
                            {--force : Regenerate embeddings even if they exist}
                            {--sync : Process synchronously instead of queueing}';

    /**
     * The console command description.
     */
    protected $description = 'Backfill vector embeddings for existing content';

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
    public function handle(EmbeddingService $service): int
    {
        $this->info('Starting embedding backfill...');
        $this->line('');

        // Determine which models to process
        $modelsToProcess = $this->models;

        if ($modelName = $this->option('model')) {
            if (! isset($this->models[$modelName])) {
                $this->error("Unknown model: {$modelName}");
                $this->line('Available models: '.implode(', ', array_keys($this->models)));

                return Command::FAILURE;
            }
            $modelsToProcess = [$modelName => $this->models[$modelName]];
        }

        $orgId = $this->option('org');
        $limit = $this->option('limit');
        $force = $this->option('force');
        $sync = $this->option('sync');

        $totalProcessed = 0;
        $totalQueued = 0;

        foreach ($modelsToProcess as $name => $class) {
            $result = $this->processModel($name, $class, $service, $orgId, $limit, $force, $sync);
            $totalProcessed += $result['processed'];
            $totalQueued += $result['queued'];
        }

        $this->line('');
        $this->info('Backfill complete!');
        $this->line("Total records processed: {$totalProcessed}");

        if (! $sync) {
            $this->line("Total jobs queued: {$totalQueued}");
            $this->warn('Run your queue workers to process the embedding jobs:');
            $this->line('  php artisan queue:work --queue=embeddings');
        }

        return Command::SUCCESS;
    }

    /**
     * Process a specific model class.
     */
    protected function processModel(
        string $name,
        string $class,
        EmbeddingService $service,
        ?int $orgId,
        ?int $limit,
        bool $force,
        bool $sync
    ): array {
        $this->info("Processing {$name}...");

        $query = $class::query();

        // Filter by organization if specified
        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        // Only get records missing embeddings unless force is true
        if (! $force) {
            $query->whereNull('embedding');
        }

        // Apply limit if specified
        if ($limit) {
            $query->limit($limit);
        }

        $count = $query->count();
        $this->line("  Found {$count} records to process");

        if ($count === 0) {
            return ['processed' => 0, 'queued' => 0];
        }

        $processed = 0;
        $queued = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        // Process in chunks to manage memory
        $query->chunk(50, function ($records) use ($service, $sync, &$processed, &$queued, &$errors, $bar) {
            foreach ($records as $model) {
                try {
                    if ($sync) {
                        // Process synchronously
                        $service->generateEmbeddingForModel($model);
                        $processed++;
                    } else {
                        // Queue for async processing
                        $model->queueEmbeddingGeneration();
                        $queued++;
                    }
                } catch (\Exception $e) {
                    $errors++;
                    $this->newLine();
                    $this->error("  Error processing {$model->id}: {$e->getMessage()}");
                }

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine();

        if ($errors > 0) {
            $this->warn("  Completed with {$errors} errors");
        }

        if ($sync) {
            $this->info("  Processed {$processed} {$name} records");
        } else {
            $this->info("  Queued {$queued} {$name} records");
        }

        return [
            'processed' => $processed,
            'queued' => $queued,
        ];
    }
}
