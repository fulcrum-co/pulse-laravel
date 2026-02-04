<?php

namespace App\Jobs;

use App\Events\StrategyDriftDetected;
use App\Models\ContactNote;
use App\Models\StrategyDriftScore;
use App\Services\StrategyDriftService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessNarrativeAlignment implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 30;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ContactNote $note
    ) {}

    /**
     * Execute the job.
     */
    public function handle(StrategyDriftService $driftService): void
    {
        // Refresh the note to get latest state
        $this->note->refresh();

        // Wait for embedding to be generated
        if (! $this->note->embedding) {
            Log::info('ProcessNarrativeAlignment: Note has no embedding yet, releasing', [
                'note_id' => $this->note->id,
            ]);
            $this->release(30); // Retry in 30 seconds

            return;
        }

        // Check if org has strategic context
        if (! $driftService->hasStrategicContext($this->note->org_id)) {
            Log::info('ProcessNarrativeAlignment: No strategic context for org', [
                'note_id' => $this->note->id,
                'org_id' => $this->note->org_id,
            ]);

            return;
        }

        try {
            // Calculate alignment
            $score = $driftService->calculateAlignment($this->note);

            Log::info('ProcessNarrativeAlignment: Alignment calculated', [
                'note_id' => $this->note->id,
                'alignment_score' => $score->alignment_score,
                'alignment_level' => $score->alignment_level,
            ]);

            // Alert if weak alignment
            if ($score->alignment_level === StrategyDriftScore::LEVEL_WEAK) {
                event(new StrategyDriftDetected($this->note, $score));
            }
        } catch (\Exception $e) {
            Log::error('ProcessNarrativeAlignment: Failed to calculate alignment', [
                'note_id' => $this->note->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessNarrativeAlignment: Job failed permanently', [
            'note_id' => $this->note->id,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'narrative-alignment',
            'note:'.$this->note->id,
            'org:'.$this->note->org_id,
        ];
    }
}
