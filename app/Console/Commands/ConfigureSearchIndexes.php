<?php

namespace App\Console\Commands;

use App\Services\Search\MeilisearchService;
use Illuminate\Console\Command;

class ConfigureSearchIndexes extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'pulse:search:configure
                            {--fresh : Delete existing indexes before configuring}
                            {--status : Show index statistics only}';

    /**
     * The console command description.
     */
    protected $description = 'Configure Meilisearch indexes with settings from config/scout.php';

    /**
     * Execute the console command.
     */
    public function handle(MeilisearchService $service): int
    {
        // Show status only
        if ($this->option('status')) {
            return $this->showStatus($service);
        }

        // Health check first
        $health = $service->healthCheck();
        if ($health['status'] !== 'healthy') {
            $this->error('Meilisearch is not accessible: ' . ($health['error'] ?? 'Unknown error'));
            $this->line('');
            $this->line('Make sure Meilisearch is running and MEILISEARCH_HOST is correctly configured.');
            return Command::FAILURE;
        }

        $this->info('Connected to Meilisearch ' . ($health['version']['pkgVersion'] ?? 'unknown'));
        $this->line('');

        // Fresh start?
        if ($this->option('fresh')) {
            $this->warn('Fresh mode: Existing indexes will be deleted and recreated.');
            if (!$this->confirm('Are you sure?')) {
                return Command::SUCCESS;
            }
        }

        // Configure indexes
        $this->info('Configuring search indexes...');
        $results = $service->configureIndexes();

        $this->table(
            ['Index', 'Status', 'Details'],
            collect($results)->map(fn($result, $index) => [
                $index,
                $result['status'],
                $result['task_uid'] ?? $result['message'] ?? '',
            ])->toArray()
        );

        $this->line('');
        $this->info('Index configuration complete.');
        $this->line('');
        $this->line('Next steps:');
        $this->line('  1. Import data: php artisan scout:import "App\Models\Resource"');
        $this->line('  2. Or import all: php artisan pulse:search:reindex');

        return Command::SUCCESS;
    }

    /**
     * Show index statistics.
     */
    protected function showStatus(MeilisearchService $service): int
    {
        $health = $service->healthCheck();

        if ($health['status'] !== 'healthy') {
            $this->error('Meilisearch is not accessible: ' . ($health['error'] ?? 'Unknown error'));
            return Command::FAILURE;
        }

        $this->info('Meilisearch Status: ' . $health['status']);
        $this->line('Version: ' . ($health['version']['pkgVersion'] ?? 'unknown'));
        $this->line('');

        $stats = $service->getIndexStats();

        $this->table(
            ['Index', 'Documents', 'Indexing'],
            collect($stats)->map(fn($stat, $index) => [
                $index,
                $stat['numberOfDocuments'] ?? ($stat['error'] ?? 'N/A'),
                isset($stat['isIndexing']) ? ($stat['isIndexing'] ? 'Yes' : 'No') : 'N/A',
            ])->toArray()
        );

        return Command::SUCCESS;
    }
}
