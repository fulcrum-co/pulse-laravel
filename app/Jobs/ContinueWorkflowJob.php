<?php

namespace App\Jobs;

use App\Models\WorkflowExecution;
use App\Services\WorkflowEvaluationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ContinueWorkflowJob implements ShouldQueue
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
        public string $executionId
    ) {
        $this->onQueue('workflows');
    }

    /**
     * Execute the job.
     */
    public function handle(WorkflowEvaluationService $evaluationService): void
    {
        $execution = WorkflowExecution::find($this->executionId);

        if (! $execution) {
            Log::warning('ContinueWorkflowJob: Execution not found', [
                'execution_id' => $this->executionId,
            ]);

            return;
        }

        // Only resume if still waiting
        if (! $execution->isWaiting()) {
            Log::info('ContinueWorkflowJob: Execution no longer waiting, skipping', [
                'execution_id' => $this->executionId,
                'status' => $execution->status,
            ]);

            return;
        }

        // Check if it's time to resume
        if ($execution->resume_at && $execution->resume_at->isFuture()) {
            Log::info('ContinueWorkflowJob: Not yet time to resume, re-scheduling', [
                'execution_id' => $this->executionId,
                'resume_at' => $execution->resume_at->toISOString(),
            ]);

            // Re-dispatch with correct delay
            self::dispatch($this->executionId)
                ->delay($execution->resume_at);

            return;
        }

        Log::info('ContinueWorkflowJob: Resuming workflow execution', [
            'execution_id' => $this->executionId,
            'workflow_id' => $execution->workflow_id,
        ]);

        try {
            $evaluationService->resumeExecution($execution);

            Log::info('ContinueWorkflowJob: Workflow resumed successfully', [
                'execution_id' => $this->executionId,
                'status' => $execution->fresh()->status,
            ]);
        } catch (\Exception $e) {
            Log::error('ContinueWorkflowJob: Resume failed', [
                'execution_id' => $this->executionId,
                'error' => $e->getMessage(),
            ]);

            $execution->markFailed('Resume failed: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('ContinueWorkflowJob failed permanently', [
            'execution_id' => $this->executionId,
            'error' => $exception->getMessage(),
        ]);

        $execution = WorkflowExecution::find($this->executionId);
        if ($execution && ! $execution->isComplete()) {
            $execution->markFailed('Job failed: '.$exception->getMessage());
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'workflow',
            'workflow-continue',
            'execution:'.$this->executionId,
        ];
    }
}
