<?php

namespace App\Jobs;

use App\Services\Moderation\ContentModerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ModerateContentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying.
     */
    public array $backoff = [30, 60, 120];

    /**
     * The content model to moderate.
     */
    protected Model $model;

    /**
     * Create a new job instance.
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
        $this->onQueue(config('services.moderation.queue', 'moderation'));
    }

    /**
     * Execute the job.
     */
    public function handle(ContentModerationService $service): void
    {
        Log::info('Starting content moderation', [
            'model' => get_class($this->model),
            'id' => $this->model->getKey(),
        ]);

        try {
            $result = $service->moderate($this->model);

            Log::info('Content moderation completed', [
                'model' => get_class($this->model),
                'id' => $this->model->getKey(),
                'status' => $result->status,
                'score' => $result->overall_score,
            ]);

        } catch (\Exception $e) {
            Log::error('Content moderation job failed', [
                'model' => get_class($this->model),
                'id' => $this->model->getKey(),
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Content moderation job permanently failed', [
            'model' => get_class($this->model),
            'id' => $this->model->getKey(),
            'error' => $exception->getMessage(),
        ]);
    }
}
