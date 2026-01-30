<?php

namespace App\Jobs;

use App\Services\AutoCourseGenerationService;
use App\Models\Organization;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class BatchCourseGenerationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ?int $orgId;
    protected array $options;

    /**
     * Create a new job instance.
     *
     * @param int|null $orgId Specific org to process, or null for all
     * @param array $options Generation options
     */
    public function __construct(?int $orgId = null, array $options = [])
    {
        $this->orgId = $orgId;
        $this->options = $options;
    }

    /**
     * Execute the job.
     */
    public function handle(AutoCourseGenerationService $generationService): void
    {
        Log::info('Starting batch course generation job', [
            'org_id' => $this->orgId,
            'options' => $this->options,
        ]);

        if ($this->orgId) {
            // Process specific organization
            $this->processOrganization($generationService, $this->orgId);
        } else {
            // Process all organizations with auto-generation enabled
            $organizations = Organization::all();

            foreach ($organizations as $org) {
                $settings = $org->settings['ai_course_settings'] ?? [];

                if ($settings['auto_generate_enabled'] ?? false) {
                    $this->processOrganization($generationService, $org->id);
                }
            }
        }

        Log::info('Batch course generation job completed');
    }

    /**
     * Process a single organization.
     */
    protected function processOrganization(AutoCourseGenerationService $generationService, int $orgId): void
    {
        try {
            $results = $generationService->runBatchGeneration($orgId, $this->options);

            Log::info('Batch generation completed for organization', [
                'org_id' => $orgId,
                'results' => $results,
            ]);
        } catch (\Exception $e) {
            Log::error('Batch generation failed for organization', [
                'org_id' => $orgId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Batch course generation job failed', [
            'org_id' => $this->orgId,
            'error' => $exception->getMessage(),
        ]);
    }
}
