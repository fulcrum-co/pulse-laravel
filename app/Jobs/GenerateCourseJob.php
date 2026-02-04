<?php

namespace App\Jobs;

use App\Services\CourseOrchestrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class GenerateCourseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 120;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $jobId,
        public array $params
    ) {}

    /**
     * Execute the job.
     */
    public function handle(CourseOrchestrator $orchestrator): void
    {
        $cacheKey = "course_generation_{$this->jobId}";

        try {
            // Update status to processing
            $this->updateStatus($cacheKey, 'processing');

            // Generate the course
            $course = $orchestrator->generateCourse($this->params);

            // Update status to completed
            Cache::put($cacheKey, [
                'status' => 'completed',
                'course_id' => $course->id,
                'completed_at' => now()->toIso8601String(),
            ], 3600);

            Log::info('Background course generation completed', [
                'job_id' => $this->jobId,
                'course_id' => $course->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Background course generation failed', [
                'job_id' => $this->jobId,
                'error' => $e->getMessage(),
            ]);

            Cache::put($cacheKey, [
                'status' => 'failed',
                'error' => 'Course generation failed. Please try again.',
                'failed_at' => now()->toIso8601String(),
            ], 3600);

            throw $e;
        }
    }

    /**
     * Update the generation status in cache.
     */
    protected function updateStatus(string $cacheKey, string $status): void
    {
        $existing = Cache::get($cacheKey, []);
        $existing['status'] = $status;
        Cache::put($cacheKey, $existing, 3600);
    }

    /**
     * Handle a job failure.
     */
    public function failed(?\Throwable $exception): void
    {
        $cacheKey = "course_generation_{$this->jobId}";

        Cache::put($cacheKey, [
            'status' => 'failed',
            'error' => 'Course generation failed after multiple attempts.',
            'failed_at' => now()->toIso8601String(),
        ], 3600);

        Log::error('Course generation job failed permanently', [
            'job_id' => $this->jobId,
            'error' => $exception?->getMessage(),
        ]);
    }
}
