<?php

namespace App\Services;

use App\Jobs\ContinueWorkflowJob;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Illuminate\Support\Facades\Log;

class WorkflowEvaluationService
{
    public function __construct(
        protected WorkflowActionService $actionService
    ) {}

    /**
     * Check if a workflow should trigger based on event data.
     */
    public function shouldTrigger(Workflow $workflow, array $eventData): bool
    {
        // Check if workflow is active
        if (! $workflow->isActive()) {
            return false;
        }

        // Check cooldown
        if ($workflow->isInCooldown()) {
            return false;
        }

        // Check daily limit
        if ($workflow->hasExceededDailyLimit()) {
            return false;
        }

        // Get trigger configuration
        $triggerConfig = $workflow->trigger_config ?? [];
        $conditions = $triggerConfig['conditions'] ?? [];
        $logic = $triggerConfig['logic'] ?? 'and';

        // If no conditions, always trigger (for manual/schedule triggers)
        if (empty($conditions)) {
            return true;
        }

        // Evaluate conditions
        return $this->evaluateConditions($conditions, $logic, $eventData);
    }

    /**
     * Evaluate conditions with AND/OR logic.
     */
    public function evaluateConditions(array $conditions, string $logic, array $context): bool
    {
        if (empty($conditions)) {
            return true;
        }

        $results = [];

        foreach ($conditions as $condition) {
            $results[] = $this->evaluateCondition($condition, $context);
        }

        return strtolower($logic) === 'and'
            ? ! in_array(false, $results, true)
            : in_array(true, $results, true);
    }

    /**
     * Evaluate a single condition.
     */
    public function evaluateCondition(array $condition, array $context): bool
    {
        $field = $condition['field'] ?? null;
        $operator = $condition['operator'] ?? 'equals';
        $value = $condition['value'] ?? null;

        if (! $field) {
            return false;
        }

        $actualValue = data_get($context, $field);

        return $this->compareValues($actualValue, $operator, $value);
    }

    /**
     * Compare values based on operator.
     */
    public function compareValues($actual, string $operator, $expected): bool
    {
        return match ($operator) {
            'equals', '=', '==' => $actual == $expected,
            'not_equals', '!=', '<>' => $actual != $expected,
            'greater_than', '>' => is_numeric($actual) && $actual > $expected,
            'less_than', '<' => is_numeric($actual) && $actual < $expected,
            'greater_or_equal', '>=' => is_numeric($actual) && $actual >= $expected,
            'less_or_equal', '<=' => is_numeric($actual) && $actual <= $expected,
            'contains' => is_string($actual) && str_contains(strtolower($actual), strtolower($expected)),
            'not_contains' => is_string($actual) && ! str_contains(strtolower($actual), strtolower($expected)),
            'starts_with' => is_string($actual) && str_starts_with(strtolower($actual), strtolower($expected)),
            'ends_with' => is_string($actual) && str_ends_with(strtolower($actual), strtolower($expected)),
            'in' => is_array($expected) && in_array($actual, $expected),
            'not_in' => is_array($expected) && ! in_array($actual, $expected),
            'is_empty' => empty($actual),
            'is_not_empty' => ! empty($actual),
            'is_null' => is_null($actual),
            'is_not_null' => ! is_null($actual),
            'changed_to' => isset($context['_previous'][$condition['field'] ?? ''])
                && $context['_previous'][$condition['field']] != $actual
                && $actual == $expected,
            'changed_from' => isset($context['_previous'][$condition['field'] ?? ''])
                && $context['_previous'][$condition['field']] == $expected
                && $actual != $expected,
            'between' => is_numeric($actual) && is_array($expected) && count($expected) >= 2
                && $actual >= $expected[0] && $actual <= $expected[1],
            default => false,
        };
    }

    /**
     * Execute a workflow from the beginning.
     */
    public function execute(Workflow $workflow, array $triggerData): WorkflowExecution
    {
        // Create execution record
        $execution = WorkflowExecution::create([
            'workflow_id' => $workflow->_id,
            'org_id' => $workflow->org_id,
            'triggered_by' => $triggerData['triggered_by'] ?? 'manual',
            'trigger_data' => $triggerData,
            'context' => $triggerData,
            'status' => WorkflowExecution::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        // Record workflow trigger
        $workflow->recordTrigger();

        try {
            // Get entry node
            $entryNode = $workflow->getEntryNode();

            if (! $entryNode) {
                $execution->markFailed('No entry node found in workflow');

                return $execution;
            }

            // Execute from entry node
            $this->executeFromNode($workflow, $execution, $entryNode['id']);

            // Check if still running (might be waiting for delay)
            if ($execution->refresh()->isRunning()) {
                $execution->markCompleted();
            }
        } catch (\Exception $e) {
            Log::error('Workflow execution failed', [
                'workflow_id' => $workflow->_id,
                'execution_id' => $execution->_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $execution->markFailed($e->getMessage());
        }

        return $execution;
    }

    /**
     * Resume execution from a specific node (after delay).
     */
    public function resumeExecution(WorkflowExecution $execution): void
    {
        if (! $execution->isWaiting()) {
            return;
        }

        $workflow = $execution->workflow;
        $nodeId = $execution->current_node_id;

        // Get next nodes after the delay node
        $nextNodes = $workflow->getNextNodes($nodeId);

        $execution->update(['status' => WorkflowExecution::STATUS_RUNNING]);

        try {
            foreach ($nextNodes as $node) {
                $this->executeFromNode($workflow, $execution, $node['id']);
            }

            if ($execution->refresh()->isRunning()) {
                $execution->markCompleted();
            }
        } catch (\Exception $e) {
            $execution->markFailed($e->getMessage());
        }
    }

    /**
     * Execute workflow starting from a specific node.
     */
    protected function executeFromNode(Workflow $workflow, WorkflowExecution $execution, string $nodeId): void
    {
        // Prevent infinite loops
        $maxIterations = 100;
        $iterations = 0;
        $queue = [$nodeId];
        $executed = [];

        while (! empty($queue) && $iterations < $maxIterations) {
            $iterations++;
            $currentNodeId = array_shift($queue);

            // Skip if already executed in this run
            if (in_array($currentNodeId, $executed)) {
                continue;
            }

            $node = $workflow->getNode($currentNodeId);
            if (! $node) {
                continue;
            }

            // Check if execution has been paused/cancelled
            $execution->refresh();
            if (! $execution->isRunning()) {
                return;
            }

            // Execute the node
            $result = $this->executeNode($node, $execution);
            $executed[] = $currentNodeId;

            // Handle special results
            if ($result['status'] === 'waiting') {
                // Delay node - execution will be resumed later
                return;
            }

            if ($result['status'] === 'branch') {
                // Branch node - add selected branches to queue
                foreach ($result['next_nodes'] ?? [] as $nextId) {
                    if (! in_array($nextId, $executed)) {
                        $queue[] = $nextId;
                    }
                }
            } else {
                // Normal flow - add all next nodes to queue
                $nextNodes = $workflow->getNextNodes($currentNodeId);
                foreach ($nextNodes as $nextNode) {
                    if (! in_array($nextNode['id'], $executed)) {
                        $queue[] = $nextNode['id'];
                    }
                }
            }
        }

        if ($iterations >= $maxIterations) {
            Log::warning('Workflow execution hit max iterations', [
                'workflow_id' => $workflow->_id,
                'execution_id' => $execution->_id,
            ]);
        }
    }

    /**
     * Execute a single node.
     */
    protected function executeNode(array $node, WorkflowExecution $execution): array
    {
        $nodeId = $node['id'];
        $nodeType = $node['type'] ?? 'unknown';
        $nodeData = $node['data'] ?? [];
        $context = $execution->context ?? [];

        try {
            $result = match ($nodeType) {
                'trigger' => $this->executeTriggerNode($nodeData, $context, $execution),
                'condition' => $this->executeConditionNode($nodeData, $context, $execution),
                'delay' => $this->executeDelayNode($nodeData, $context, $execution),
                'action' => $this->executeActionNode($nodeData, $context, $execution),
                'branch' => $this->executeBranchNode($node, $context, $execution),
                'merge' => $this->executeMergeNode($nodeData, $context, $execution),
                'subworkflow' => $this->executeSubworkflowNode($nodeData, $context, $execution),
                default => ['status' => 'skipped', 'output' => ['reason' => 'Unknown node type']],
            };

            $execution->recordNodeResult(
                $nodeId,
                $result['status'] === 'success' || $result['status'] === 'waiting' || $result['status'] === 'branch' ? 'success' : 'failed',
                $result['output'] ?? [],
                $result['error'] ?? null
            );

            return $result;
        } catch (\Exception $e) {
            $execution->recordNodeResult($nodeId, 'failed', [], $e->getMessage());

            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Execute trigger node (entry point, mainly for validation/logging).
     */
    protected function executeTriggerNode(array $data, array $context, WorkflowExecution $execution): array
    {
        return [
            'status' => 'success',
            'output' => [
                'trigger_type' => $data['trigger_type'] ?? 'unknown',
                'conditions_met' => true,
            ],
        ];
    }

    /**
     * Execute condition node (IF/ELSE).
     */
    protected function executeConditionNode(array $data, array $context, WorkflowExecution $execution): array
    {
        $conditions = $data['conditions'] ?? [];
        $logic = $data['logic'] ?? 'and';

        $result = $this->evaluateConditions($conditions, $logic, $context);

        return [
            'status' => 'success',
            'output' => [
                'result' => $result,
                'conditions_evaluated' => count($conditions),
            ],
            'condition_result' => $result,
        ];
    }

    /**
     * Execute delay node.
     */
    protected function executeDelayNode(array $data, array $context, WorkflowExecution $execution): array
    {
        $duration = $data['duration'] ?? 1;
        $unit = $data['unit'] ?? 'minutes';

        $resumeAt = match ($unit) {
            'seconds' => now()->addSeconds($duration),
            'minutes' => now()->addMinutes($duration),
            'hours' => now()->addHours($duration),
            'days' => now()->addDays($duration),
            default => now()->addMinutes($duration),
        };

        // Mark execution as waiting
        $execution->markWaiting(
            $execution->current_node_id,
            $resumeAt,
            ['delayed_at' => now()->toISOString()]
        );

        // Schedule job to resume execution
        ContinueWorkflowJob::dispatch($execution->_id)
            ->delay($resumeAt);

        return [
            'status' => 'waiting',
            'output' => [
                'duration' => $duration,
                'unit' => $unit,
                'resume_at' => $resumeAt->toISOString(),
            ],
        ];
    }

    /**
     * Execute action node.
     */
    protected function executeActionNode(array $data, array $context, WorkflowExecution $execution): array
    {
        $actionType = $data['action_type'] ?? null;
        $config = $data['config'] ?? [];

        if (! $actionType) {
            return [
                'status' => 'failed',
                'error' => 'No action type specified',
            ];
        }

        // Add workflow context to action config
        $config['_workflow_id'] = $execution->workflow_id;
        $config['_execution_id'] = $execution->_id;

        $result = $this->actionService->execute($actionType, $config, $context);

        // Update execution context with any output
        if (! empty($result['details'])) {
            $execution->updateContext(['last_action_result' => $result['details']]);
        }

        return [
            'status' => $result['success'] ? 'success' : 'failed',
            'output' => $result,
            'error' => $result['error'] ?? null,
        ];
    }

    /**
     * Execute branch node (multi-path branching based on conditions).
     */
    protected function executeBranchNode(array $node, array $context, WorkflowExecution $execution): array
    {
        $data = $node['data'] ?? [];
        $branches = $data['branches'] ?? [];
        $workflow = $execution->workflow;

        $selectedBranches = [];

        foreach ($branches as $index => $branch) {
            $conditions = $branch['conditions'] ?? [];
            $logic = $branch['logic'] ?? 'and';

            if (empty($conditions) || $this->evaluateConditions($conditions, $logic, $context)) {
                // Find the edge for this branch
                $edges = $workflow->getOutgoingEdges($node['id']);
                foreach ($edges as $edge) {
                    $sourceHandle = $edge['sourceHandle'] ?? null;
                    // Match by handle or by index
                    if ($sourceHandle === "branch_{$index}" || $sourceHandle === $branch['id'] ?? null) {
                        $selectedBranches[] = $edge['target'];
                    }
                }

                // If this is the default branch or no specific handle, use it
                if (($branch['is_default'] ?? false) && empty($selectedBranches)) {
                    $edges = $workflow->getOutgoingEdges($node['id']);
                    foreach ($edges as $edge) {
                        $selectedBranches[] = $edge['target'];
                    }
                }
            }
        }

        // If no branches matched, use default (first outgoing edge)
        if (empty($selectedBranches)) {
            $edges = $workflow->getOutgoingEdges($node['id']);
            if (! empty($edges)) {
                $selectedBranches[] = array_values($edges)[0]['target'];
            }
        }

        return [
            'status' => 'branch',
            'next_nodes' => array_unique($selectedBranches),
            'output' => [
                'branches_evaluated' => count($branches),
                'branches_selected' => count($selectedBranches),
            ],
        ];
    }

    /**
     * Execute merge node (join point for branches).
     */
    protected function executeMergeNode(array $data, array $context, WorkflowExecution $execution): array
    {
        // Merge node is a pass-through, just continues execution
        return [
            'status' => 'success',
            'output' => ['merged' => true],
        ];
    }

    /**
     * Execute sub-workflow node.
     */
    protected function executeSubworkflowNode(array $data, array $context, WorkflowExecution $execution): array
    {
        $workflowId = $data['workflow_id'] ?? null;

        if (! $workflowId) {
            return [
                'status' => 'failed',
                'error' => 'No sub-workflow ID specified',
            ];
        }

        $subworkflow = Workflow::find($workflowId);

        if (! $subworkflow || ! $subworkflow->isActive()) {
            return [
                'status' => 'failed',
                'error' => 'Sub-workflow not found or not active',
            ];
        }

        // Execute sub-workflow synchronously or dispatch as job based on config
        $async = $data['async'] ?? true;

        if ($async) {
            \App\Jobs\ProcessWorkflow::dispatch($subworkflow, array_merge($context, [
                'parent_workflow_id' => $execution->workflow_id,
                'parent_execution_id' => $execution->_id,
            ]));

            return [
                'status' => 'success',
                'output' => [
                    'workflow_id' => $workflowId,
                    'async' => true,
                ],
            ];
        } else {
            $subExecution = $this->execute($subworkflow, array_merge($context, [
                'parent_workflow_id' => $execution->workflow_id,
                'parent_execution_id' => $execution->_id,
            ]));

            return [
                'status' => $subExecution->isSuccess() ? 'success' : 'failed',
                'output' => [
                    'workflow_id' => $workflowId,
                    'execution_id' => $subExecution->_id,
                    'async' => false,
                ],
            ];
        }
    }
}
