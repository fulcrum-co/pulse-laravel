<?php

namespace App\Jobs;

use App\Models\Workflow;
use App\Services\WorkflowEvaluationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessWorkflow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Workflow $workflow,
        public array $triggerData = []
    ) {
        $this->onQueue('workflows');
    }

    /**
     * Get the unique ID for the job.
     * Prevents duplicate processing of the same workflow for the same entity.
     */
    public function uniqueId(): string
    {
        $entityId = $this->triggerData['entity_id']
            ?? $this->triggerData['student_id']
            ?? $this->triggerData['contact_id']
            ?? 'general';

        // Unique per workflow + entity + 30 second window
        return "workflow:{$this->workflow->_id}:{$entityId}:" . floor(time() / 30);
    }

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public function uniqueFor(): int
    {
        return 30;
    }

    /**
     * Execute the job.
     */
    public function handle(WorkflowEvaluationService $evaluationService): void
    {
        Log::info('Processing workflow', [
            'workflow_id' => $this->workflow->_id,
            'workflow_name' => $this->workflow->name,
            'trigger_data' => $this->triggerData,
        ]);

        try {
            // Check if workflow should still trigger (conditions might have changed)
            if (!$evaluationService->shouldTrigger($this->workflow, $this->triggerData)) {
                Log::info('Workflow conditions no longer met, skipping', [
                    'workflow_id' => $this->workflow->_id,
                ]);
                return;
            }

            // Execute the workflow
            $execution = $evaluationService->execute($this->workflow, $this->triggerData);

            Log::info('Workflow execution completed', [
                'workflow_id' => $this->workflow->_id,
                'execution_id' => $execution->_id,
                'status' => $execution->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Workflow processing failed', [
                'workflow_id' => $this->workflow->_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ProcessWorkflow job failed permanently', [
            'workflow_id' => $this->workflow->_id,
            'workflow_name' => $this->workflow->name,
            'error' => $exception->getMessage(),
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'workflow',
            'workflow:' . $this->workflow->_id,
            'org:' . $this->workflow->org_id,
        ];
    }
}
