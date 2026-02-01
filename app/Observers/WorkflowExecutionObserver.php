<?php

namespace App\Observers;

use App\Models\UserNotification;
use App\Models\WorkflowExecution;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class WorkflowExecutionObserver
{
    public function __construct(
        protected NotificationService $notificationService,
        protected NotificationDeliveryService $deliveryService
    ) {}

    /**
     * Handle the WorkflowExecution "updated" event.
     * Triggered when workflow status changes to completed or failed.
     */
    public function updated(WorkflowExecution $execution): void
    {
        // Only proceed if status changed
        if (!$execution->isDirty('status')) {
            return;
        }

        $newStatus = $execution->status;

        // Only notify on terminal states
        if (!in_array($newStatus, [
            WorkflowExecution::STATUS_COMPLETED,
            WorkflowExecution::STATUS_FAILED,
        ])) {
            return;
        }

        $this->notifyWorkflowCreator($execution);
    }

    /**
     * Notify the workflow creator about execution completion or failure.
     */
    protected function notifyWorkflowCreator(WorkflowExecution $execution): void
    {
        $workflow = $execution->workflow;

        if (!$workflow) {
            Log::warning('WorkflowExecutionObserver: Workflow not found', [
                'execution_id' => $execution->id,
            ]);
            return;
        }

        // Get the workflow creator
        $creatorId = $workflow->created_by;

        if (!$creatorId) {
            Log::info('WorkflowExecutionObserver: No creator_id for workflow', [
                'workflow_id' => $workflow->id,
            ]);
            return;
        }

        $isSuccess = $execution->status === WorkflowExecution::STATUS_COMPLETED;

        // Build notification data
        $data = [
            'title' => $isSuccess
                ? "Alert Completed: {$workflow->name}"
                : "Alert Failed: {$workflow->name}",
            'body' => $isSuccess
                ? $this->summarizeActions($execution)
                : $execution->error_message ?? 'An error occurred during execution.',
            'action_url' => route('alerts.index', [
                'tab' => 'workflows',
                'workflow' => $workflow->id,
            ]),
            'action_label' => 'View Details',
            'priority' => $isSuccess
                ? UserNotification::PRIORITY_NORMAL
                : UserNotification::PRIORITY_HIGH,
            'notifiable_type' => WorkflowExecution::class,
            'notifiable_id' => $execution->id,
            'metadata' => [
                'workflow_id' => $workflow->id,
                'workflow_name' => $workflow->name,
                'execution_duration' => $execution->duration,
                'nodes_executed' => count($execution->node_results ?? []),
            ],
        ];

        // Create notification
        $notification = $this->notificationService->notify(
            $creatorId,
            UserNotification::CATEGORY_WORKFLOW_ALERT,
            $isSuccess ? 'workflow_completed' : 'workflow_failed',
            $data
        );

        // Dispatch multi-channel delivery
        if ($notification) {
            $this->deliveryService->deliver($notification);
        }

        Log::info('WorkflowExecutionObserver: Creator notified', [
            'execution_id' => $execution->id,
            'workflow_id' => $workflow->id,
            'creator_id' => $creatorId,
            'status' => $execution->status,
        ]);
    }

    /**
     * Summarize actions taken during workflow execution.
     */
    protected function summarizeActions(WorkflowExecution $execution): string
    {
        $nodeResults = $execution->node_results ?? [];
        $successCount = 0;
        $actionSummary = [];

        foreach ($nodeResults as $nodeId => $result) {
            if (($result['status'] ?? '') === 'success') {
                $successCount++;

                // Extract action type and details
                $actionType = $result['output']['action_type'] ?? null;
                if ($actionType) {
                    $key = $this->formatActionType($actionType);
                    $actionSummary[$key] = ($actionSummary[$key] ?? 0) + 1;
                }
            }
        }

        if (empty($actionSummary)) {
            return "{$successCount} actions completed successfully.";
        }

        // Build summary like "2 emails sent, 1 SMS sent, 3 notifications created"
        $parts = [];
        foreach ($actionSummary as $action => $count) {
            $parts[] = "{$count} {$action}" . ($count !== 1 ? 's' : '');
        }

        return implode(', ', $parts) . '.';
    }

    /**
     * Format action type for human readability.
     */
    protected function formatActionType(string $actionType): string
    {
        return match ($actionType) {
            'send_email' => 'email sent',
            'send_sms' => 'SMS sent',
            'send_whatsapp' => 'WhatsApp message sent',
            'make_call' => 'call made',
            'webhook' => 'webhook triggered',
            'create_task' => 'task created',
            'assign_resource' => 'resource assigned',
            'in_app_notification' => 'notification sent',
            'trigger_workflow' => 'workflow triggered',
            'update_field' => 'field updated',
            default => 'action completed',
        };
    }
}
