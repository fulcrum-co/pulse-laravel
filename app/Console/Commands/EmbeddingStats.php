<?php

namespace App\Console\Commands;

use App\Models\ContentBlock;
use App\Models\EmbeddingJob;
use App\Models\MiniCourse;
use App\Models\Program;
use App\Models\Provider;
use App\Models\Resource;
use Illuminate\Console\Command;

class EmbeddingStats extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pulse:embeddings:stats
                            {--org= : Filter by organization ID}';

    /**
     * The console command description.
     */
    protected $description = 'Display embedding statistics for all content types';

    protected array $models = [
        'Resources' => Resource::class,
        'Mini Courses' => MiniCourse::class,
        'Content Blocks' => ContentBlock::class,
        'Providers' => Provider::class,
        'Programs' => Program::class,
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $orgId = $this->option('org');

        $this->info('Embedding Statistics');
        $this->line('');

        if ($orgId) {
            $this->line("Filtered by Organization ID: {$orgId}");
            $this->line('');
        }

        // Content statistics
        $stats = [];
        $totalRecords = 0;
        $totalWithEmbedding = 0;
        $totalMissing = 0;
        $totalStale = 0;

        foreach ($this->models as $name => $class) {
            $query = $class::query();

            if ($orgId) {
                $query->where('org_id', $orgId);
            }

            $total = $query->count();
            $withEmbedding = (clone $query)->whereNotNull('embedding')->count();
            $missing = $total - $withEmbedding;

            // Count stale embeddings (content updated after embedding was generated)
            $stale = (clone $query)
                ->whereNotNull('embedding')
                ->whereNotNull('embedding_generated_at')
                ->whereColumn('updated_at', '>', 'embedding_generated_at')
                ->count();

            $percentage = $total > 0 ? round(($withEmbedding / $total) * 100, 1) : 0;

            $stats[] = [
                $name,
                $total,
                $withEmbedding,
                $missing,
                $stale,
                "{$percentage}%",
            ];

            $totalRecords += $total;
            $totalWithEmbedding += $withEmbedding;
            $totalMissing += $missing;
            $totalStale += $stale;
        }

        // Add totals row
        $totalPercentage = $totalRecords > 0 ? round(($totalWithEmbedding / $totalRecords) * 100, 1) : 0;
        $stats[] = [
            'TOTAL',
            $totalRecords,
            $totalWithEmbedding,
            $totalMissing,
            $totalStale,
            "{$totalPercentage}%",
        ];

        $this->table(
            ['Content Type', 'Total', 'With Embedding', 'Missing', 'Stale', 'Coverage'],
            $stats
        );

        // Job queue statistics
        $this->line('');
        $this->info('Job Queue Status');

        $jobStats = [
            ['Pending', EmbeddingJob::where('status', 'pending')->count()],
            ['Processing', EmbeddingJob::where('status', 'processing')->count()],
            ['Completed', EmbeddingJob::where('status', 'completed')->count()],
            ['Failed', EmbeddingJob::where('status', 'failed')->count()],
        ];

        $this->table(['Status', 'Count'], $jobStats);

        // Configuration info
        $this->line('');
        $this->info('Configuration');
        $this->line('  Provider: ' . config('services.embeddings.provider', 'openai'));
        $this->line('  Model: ' . config('services.embeddings.model', 'text-embedding-3-small'));
        $this->line('  Dimensions: ' . config('services.embeddings.dimensions', 1536));
        $this->line('  Auto-generate: ' . (config('services.embeddings.auto_generate', true) ? 'Yes' : 'No'));
        $this->line('  Queue: ' . config('services.embeddings.queue', 'embeddings'));

        return Command::SUCCESS;
    }
}
