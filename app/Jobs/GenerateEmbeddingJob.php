<?php

namespace App\Jobs;

use App\Models\EmbeddingJob;
use App\Services\Embeddings\EmbeddingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateEmbeddingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public array $backoff = [30, 60, 120];

    /**
     * Delete the job if its models no longer exist.
     */
    public bool $deleteWhenMissingModels = true;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Model $model
    ) {}

    /**
     * Execute the job.
     */
    public function handle(EmbeddingService $service): void
    {
        $modelClass = get_class($this->model);
        $modelId = $this->model->getKey();

        // Find or create tracking record
        $embeddingJob = EmbeddingJob::firstOrCreate([
            'embeddable_type' => $modelClass,
            'embeddable_id' => $modelId,
            'status' => 'pending',
        ], [
            'org_id' => $this->model->org_id ?? null,
            'embedding_model' => $service->getProvider()->getModel(),
        ]);

        // Mark as processing
        $embeddingJob->update([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $startTime = microtime(true);

            // Generate the embedding
            $service->generateEmbeddingForModel($this->model);

            $processingTime = (microtime(true) - $startTime) * 1000;

            // Mark as completed
            $embeddingJob->update([
                'status' => 'completed',
                'completed_at' => now(),
                'processing_time_ms' => $processingTime,
            ]);

            Log::info('Embedding job completed', [
                'model' => $modelClass,
                'id' => $modelId,
                'time_ms' => round($processingTime, 2),
            ]);
        } catch (\Exception $e) {
            $embeddingJob->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'retry_count' => $embeddingJob->retry_count + 1,
            ]);

            Log::error('Embedding job failed', [
                'model' => $modelClass,
                'id' => $modelId,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        $modelClass = get_class($this->model);
        $modelId = $this->model->getKey();

        // Update tracking record
        EmbeddingJob::where('embeddable_type', $modelClass)
            ->where('embeddable_id', $modelId)
            ->where('status', 'processing')
            ->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
            ]);

        Log::error('Embedding job permanently failed', [
            'model' => $modelClass,
            'id' => $modelId,
            'error' => $exception->getMessage(),
        ]);
    }
}
