<?php

namespace App\Console\Commands;

use App\Events\StrategyDriftDetected;
use App\Models\ContactNote;
use App\Models\StrategyDriftScore;
use App\Services\StrategyDriftService;
use Illuminate\Console\Command;

class DetectStrategyDrift extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'strategy:detect-drift
                            {--org= : Process only notes for a specific organization}
                            {--days=1 : Re-process notes not scored in this many days}
                            {--limit=100 : Maximum number of notes to process}
                            {--alert : Send alerts for weak alignment scores}';

    /**
     * The console command description.
     */
    protected $description = 'Detect strategy drift by scoring narrative alignment against strategic plans';

    /**
     * Execute the console command.
     */
    public function handle(StrategyDriftService $driftService): int
    {
        $orgId = $this->option('org') ? (int) $this->option('org') : null;
        $days = (int) $this->option('days');
        $limit = (int) $this->option('limit');
        $sendAlerts = $this->option('alert');

        $this->info('Strategy Drift Detection');
        $this->info('========================');
        $this->line("Options: org={$orgId}, days={$days}, limit={$limit}, alerts=".($sendAlerts ? 'yes' : 'no'));

        // Find notes that need scoring
        $query = ContactNote::query()
            ->whereNotNull('embedding')
            ->where(function ($q) use ($days) {
                $q->whereNull('drift_scored_at')
                    ->orWhere('drift_scored_at', '<', now()->subDays($days));
            });

        if ($orgId) {
            $query->where('org_id', $orgId);
        }

        $notes = $query->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();

        if ($notes->isEmpty()) {
            $this->info('No notes need drift scoring.');

            return Command::SUCCESS;
        }

        $this->info("Found {$notes->count()} notes to process");

        $bar = $this->output->createProgressBar($notes->count());
        $bar->start();

        $processed = 0;
        $weak = 0;
        $moderate = 0;
        $strong = 0;
        $errors = 0;

        foreach ($notes as $note) {
            try {
                // Check if org has strategic context
                if (! $driftService->hasStrategicContext($note->org_id)) {
                    $bar->advance();

                    continue;
                }

                // Calculate alignment
                $score = $driftService->calculateAlignment($note);
                $processed++;

                // Count by level
                match ($score->alignment_level) {
                    StrategyDriftScore::LEVEL_WEAK => $weak++,
                    StrategyDriftScore::LEVEL_MODERATE => $moderate++,
                    StrategyDriftScore::LEVEL_STRONG => $strong++,
                    default => null,
                };

                // Send alert if requested and weak
                if ($sendAlerts && $score->alignment_level === StrategyDriftScore::LEVEL_WEAK) {
                    event(new StrategyDriftDetected($note, $score));
                }
            } catch (\Exception $e) {
                $errors++;
                $this->newLine();
                $this->error("Error processing note {$note->id}: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Summary
        $this->info('Results:');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Processed', $processed],
                ['Strong Alignment', $strong],
                ['Moderate Alignment', $moderate],
                ['Weak Alignment', $weak],
                ['Errors', $errors],
            ]
        );

        if ($weak > 0 && ! $sendAlerts) {
            $this->warn("Found {$weak} weak alignments. Run with --alert to send notifications.");
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
