<?php

namespace App\Services\Moderation;

use App\Models\ContentModerationResult;
use App\Models\ModerationQueueItem;
use App\Models\ModerationWorkflow;
use App\Models\User;
use App\Models\Workflow;
use Illuminate\Support\Facades\Log;

class ModerationWorkflowService
{
    public function __construct(
        protected ModerationQueueService $queueService
    ) {}

    /**
     * Select the appropriate workflow for a moderation result.
     */
    public function selectWorkflowForContent(ContentModerationResult $result): ?ModerationWorkflow
    {
        $contentType = class_basename($result->moderatable_type);
        $contentTypeMap = [
            'MiniCourse' => ModerationWorkflow::CONTENT_TYPE_MINI_COURSE,
            'ContentBlock' => ModerationWorkflow::CONTENT_TYPE_CONTENT_BLOCK,
        ];

        $mappedType = $contentTypeMap[$contentType] ?? ModerationWorkflow::CONTENT_TYPE_ALL;

        // Get workflows that match content type, ordered by priority
        $workflows = ModerationWorkflow::forOrganization($result->org_id)
            ->forContentType($mappedType)
            ->active()
            ->orderByDesc('priority')
            ->get();

        // Find the first workflow whose trigger conditions match
        foreach ($workflows as $workflow) {
            if ($workflow->matchesTriggerConditions($result)) {
                return $workflow;
            }
        }

        // Fall back to default workflow
        return ModerationWorkflow::forOrganization($result->org_id)
            ->default()
            ->active()
            ->first();
    }

    /**
     * Execute a workflow for a queue item.
     */
    public function executeWorkflow(ModerationQueueItem $item): void
    {
        $workflow = $item->workflow;

        if (! $workflow) {
            Log::warning('No workflow found for queue item', ['queue_item_id' => $item->id]);

            return;
        }

        $baseWorkflow = $workflow->workflow;

        if (! $baseWorkflow || empty($baseWorkflow->nodes)) {
            Log::warning('No workflow nodes found', [
                'queue_item_id' => $item->id,
                'workflow_id' => $workflow->id,
            ]);

            return;
        }

        // Find the start node
        $nodes = collect($baseWorkflow->nodes);
        $edges = collect($baseWorkflow->edges ?? []);

        $startNode = $nodes->firstWhere('type', 'trigger');

        if (! $startNode) {
            $startNode = $nodes->first();
        }

        // Process nodes starting from the start node
        $this->processNode($startNode, $item, $nodes, $edges);
    }

    /**
     * Process a workflow node.
     */
    protected function processNode(
        array $node,
        ModerationQueueItem $item,
        $nodes,
        $edges
    ): void {
        $nodeType = $node['type'] ?? null;
        $nodeData = $node['data'] ?? [];

        Log::debug('Processing workflow node', [
            'node_id' => $node['id'],
            'node_type' => $nodeType,
            'queue_item_id' => $item->id,
        ]);

        // Update current step
        $item->update(['current_step_id' => $node['id']]);

        // Process based on node type
        $result = match ($nodeType) {
            'trigger' => true, // Just pass through
            'ai_score_condition' => $this->evaluateAiScoreCondition($nodeData, $item),
            'flag_check' => $this->evaluateFlagCheck($nodeData, $item),
            'route_to_moderator' => $this->executeRouteToModerator($nodeData, $item),
            'human_decision' => $this->pauseForHumanDecision($nodeData, $item),
            'auto_approve' => $this->executeAutoApprove($nodeData, $item),
            'auto_reject' => $this->executeAutoReject($nodeData, $item),
            'escalate' => $this->executeEscalate($nodeData, $item),
            default => true, // Unknown nodes pass through
        };

        // If this is a pause point (human decision), stop processing
        if ($nodeType === 'human_decision') {
            return;
        }

        // If this is a terminal action (auto_approve, auto_reject), stop
        if (in_array($nodeType, ['auto_approve', 'auto_reject'])) {
            return;
        }

        // Find next node based on edges
        $nextNodeId = $this->findNextNode($node['id'], $result, $edges);

        if ($nextNodeId) {
            $nextNode = $nodes->firstWhere('id', $nextNodeId);

            if ($nextNode) {
                $this->processNode($nextNode, $item, $nodes, $edges);
            }
        }
    }

    /**
     * Find the next node based on edges and condition result.
     */
    protected function findNextNode(string $currentNodeId, $conditionResult, $edges): ?string
    {
        // Find edges from current node
        $outgoingEdges = $edges->filter(fn ($e) => $e['source'] === $currentNodeId);

        if ($outgoingEdges->isEmpty()) {
            return null;
        }

        // If there's only one edge, use it
        if ($outgoingEdges->count() === 1) {
            return $outgoingEdges->first()['target'];
        }

        // If result is boolean, find edge with matching label
        if (is_bool($conditionResult)) {
            $label = $conditionResult ? 'true' : 'false';
            $matchingEdge = $outgoingEdges->firstWhere('label', $label);

            if ($matchingEdge) {
                return $matchingEdge['target'];
            }
        }

        // If result is a string (decision), find edge with matching label
        if (is_string($conditionResult)) {
            $matchingEdge = $outgoingEdges->firstWhere('label', $conditionResult);

            if ($matchingEdge) {
                return $matchingEdge['target'];
            }
        }

        // Default to first edge
        return $outgoingEdges->first()['target'];
    }

    /**
     * Evaluate AI score condition node.
     */
    protected function evaluateAiScoreCondition(array $data, ModerationQueueItem $item): bool
    {
        $result = $item->moderationResult;
        $dimension = $data['dimension'] ?? 'overall';
        $operator = $data['operator'] ?? 'gte';
        $threshold = $data['threshold'] ?? 0.85;

        $score = match ($dimension) {
            'overall' => $result->overall_score,
            'age_appropriateness' => $result->age_appropriateness_score,
            'clinical_safety' => $result->clinical_safety_score,
            'cultural_sensitivity' => $result->cultural_sensitivity_score,
            'accuracy' => $result->accuracy_score,
            default => $result->overall_score,
        };

        return match ($operator) {
            'gte', '>=' => $score >= $threshold,
            'lte', '<=' => $score <= $threshold,
            'gt', '>' => $score > $threshold,
            'lt', '<' => $score < $threshold,
            'between' => $score >= $threshold && $score <= ($data['threshold_max'] ?? 1.0),
            default => $score >= $threshold,
        };
    }

    /**
     * Evaluate flag check node.
     */
    protected function evaluateFlagCheck(array $data, ModerationQueueItem $item): bool
    {
        $result = $item->moderationResult;
        $flagCategory = $data['flag_category'] ?? null;
        $hasFlag = $data['has_flag'] ?? true;

        $flags = $result->flags ?? [];

        if (empty($flags)) {
            return ! $hasFlag;
        }

        // If checking for specific category
        if ($flagCategory) {
            $categoryFound = isset($flags[$flagCategory]) ||
                             in_array($flagCategory, array_keys($flags));

            return $hasFlag ? $categoryFound : ! $categoryFound;
        }

        // Check if any flags exist
        $anyFlags = ! empty($flags);

        return $hasFlag ? $anyFlags : ! $anyFlags;
    }

    /**
     * Execute route to moderator node.
     */
    protected function executeRouteToModerator(array $data, ModerationQueueItem $item): bool
    {
        $strategy = $data['strategy'] ?? 'least_loaded';
        $priority = $data['priority'] ?? $item->priority;

        // Update priority if specified
        if ($priority !== $item->priority) {
            $item->update(['priority' => $priority]);
        }

        $assignedUser = match ($strategy) {
            'round_robin' => $this->queueService->assignRoundRobin($item),
            'least_loaded' => $this->queueService->assignLeastLoaded($item),
            'skill_based' => $this->queueService->assignBySkill($item, $data['required_skills'] ?? []),
            'specific_user' => $this->assignToSpecificUser($item, $data['user_id'] ?? null),
            default => $this->queueService->assignLeastLoaded($item),
        };

        return $assignedUser !== null;
    }

    /**
     * Assign to a specific user.
     */
    protected function assignToSpecificUser(ModerationQueueItem $item, ?int $userId): ?User
    {
        if (! $userId) {
            return null;
        }

        $user = User::find($userId);

        if ($user) {
            $this->queueService->assignToUser($item, $user);
        }

        return $user;
    }

    /**
     * Pause workflow for human decision.
     */
    protected function pauseForHumanDecision(array $data, ModerationQueueItem $item): void
    {
        $slaHours = $data['sla_hours'] ?? null;

        if ($slaHours) {
            $item->update(['due_at' => now()->addHours($slaHours)]);
        }

        $item->update([
            'status' => ModerationQueueItem::STATUS_PENDING,
            'metadata' => array_merge($item->metadata ?? [], [
                'awaiting_decision' => true,
                'decision_options' => $data['decision_options'] ?? ['approve', 'reject'],
                'required_note' => $data['required_note'] ?? false,
            ]),
        ]);
    }

    /**
     * Execute auto-approve action.
     */
    protected function executeAutoApprove(array $data, ModerationQueueItem $item): void
    {
        $result = $item->moderationResult;

        // Update moderation result
        $result->update([
            'status' => ContentModerationResult::STATUS_APPROVED_OVERRIDE,
            'human_reviewed' => false,
            'reviewed_at' => now(),
        ]);

        // Complete the queue item
        $item->complete();

        // Notify creator if enabled
        if ($data['notify_creator'] ?? true) {
            $this->notifyContentOwner($result, 'approved');
        }

        // Publish if enabled
        if ($data['publish_immediately'] ?? false) {
            $this->publishContent($result);
        }

        Log::info('Content auto-approved by workflow', [
            'queue_item_id' => $item->id,
            'moderation_result_id' => $result->id,
        ]);
    }

    /**
     * Execute auto-reject action.
     */
    protected function executeAutoReject(array $data, ModerationQueueItem $item): void
    {
        $result = $item->moderationResult;
        $reasonTemplate = $data['reason_template'] ?? 'Content did not meet quality standards.';

        // Update moderation result
        $result->update([
            'status' => ContentModerationResult::STATUS_REJECTED,
            'human_reviewed' => false,
            'reviewed_at' => now(),
            'review_notes' => $reasonTemplate,
        ]);

        // Complete the queue item
        $item->complete();

        // Notify creator if enabled
        if ($data['notify_creator'] ?? true) {
            $this->notifyContentOwner($result, 'rejected');
        }

        Log::info('Content auto-rejected by workflow', [
            'queue_item_id' => $item->id,
            'moderation_result_id' => $result->id,
            'reason' => $reasonTemplate,
        ]);
    }

    /**
     * Execute escalation action.
     */
    protected function executeEscalate(array $data, ModerationQueueItem $item): void
    {
        $item->escalate();

        $escalateTo = $data['escalate_to_user_id'] ?? null;
        $escalateToRole = $data['escalate_to_role'] ?? 'admin';

        if ($escalateTo) {
            $user = User::find($escalateTo);
        } else {
            $user = User::where('org_id', $item->org_id)
                ->where('primary_role', $escalateToRole)
                ->first();
        }

        if ($user) {
            $this->queueService->assignToUser($item, $user);
        }

        Log::info('Content escalated by workflow', [
            'queue_item_id' => $item->id,
            'escalated_to' => $user?->id,
        ]);
    }

    /**
     * Handle human decision after workflow pause.
     */
    public function handleHumanDecision(
        ModerationQueueItem $item,
        string $decision,
        ?string $notes = null
    ): void {
        $this->queueService->processDecision(
            $item,
            auth()->user(),
            $decision,
            $notes
        );

        // Continue workflow if there are more nodes after human decision
        if ($item->workflow && $item->current_step_id) {
            $baseWorkflow = $item->workflow->workflow;
            $nodes = collect($baseWorkflow->nodes ?? []);
            $edges = collect($baseWorkflow->edges ?? []);

            $nextNodeId = $this->findNextNode($item->current_step_id, $decision, $edges);

            if ($nextNodeId) {
                $nextNode = $nodes->firstWhere('id', $nextNodeId);

                if ($nextNode) {
                    $this->processNode($nextNode, $item, $nodes, $edges);
                }
            }
        }
    }

    /**
     * Notify content owner of moderation result.
     */
    protected function notifyContentOwner(ContentModerationResult $result, string $action): void
    {
        // Use existing notification service from ModerationAssignmentService
        app(ModerationAssignmentService::class)->notifyModerationComplete($result, $action);
    }

    /**
     * Publish content after approval.
     */
    protected function publishContent(ContentModerationResult $result): void
    {
        $moderatable = $result->moderatable;

        if ($moderatable && method_exists($moderatable, 'publish')) {
            $moderatable->publish();
        } elseif ($moderatable && isset($moderatable->status)) {
            $moderatable->update(['status' => 'published']);
        }
    }

    /**
     * Create a default moderation workflow.
     */
    public static function createDefaultWorkflow(int $orgId): ModerationWorkflow
    {
        // First create the base workflow
        $baseWorkflow = Workflow::create([
            'org_id' => $orgId,
            'name' => 'Standard Content Moderation',
            'description' => 'Default workflow for content moderation',
            'trigger_type' => Workflow::TRIGGER_MANUAL,
            'status' => Workflow::STATUS_ACTIVE,
            'created_by' => auth()->id(),
            'nodes' => [
                [
                    'id' => 'start',
                    'type' => 'trigger',
                    'position' => ['x' => 250, 'y' => 50],
                    'data' => ['event' => 'content_moderated'],
                ],
                [
                    'id' => 'check_score',
                    'type' => 'ai_score_condition',
                    'position' => ['x' => 250, 'y' => 150],
                    'data' => [
                        'dimension' => 'overall',
                        'operator' => 'gte',
                        'threshold' => 0.85,
                    ],
                ],
                [
                    'id' => 'check_flags',
                    'type' => 'flag_check',
                    'position' => ['x' => 100, 'y' => 250],
                    'data' => ['has_flag' => true],
                ],
                [
                    'id' => 'auto_approve',
                    'type' => 'auto_approve',
                    'position' => ['x' => 400, 'y' => 250],
                    'data' => ['notify_creator' => true, 'publish_immediately' => false],
                ],
                [
                    'id' => 'route_review',
                    'type' => 'route_to_moderator',
                    'position' => ['x' => 100, 'y' => 350],
                    'data' => ['strategy' => 'least_loaded', 'priority' => 'normal'],
                ],
                [
                    'id' => 'human_decision',
                    'type' => 'human_decision',
                    'position' => ['x' => 100, 'y' => 450],
                    'data' => [
                        'decision_options' => ['approve', 'reject', 'request_changes'],
                        'required_note' => false,
                        'sla_hours' => 48,
                    ],
                ],
            ],
            'edges' => [
                ['id' => 'e1', 'source' => 'start', 'target' => 'check_score'],
                ['id' => 'e2', 'source' => 'check_score', 'target' => 'auto_approve', 'label' => 'true'],
                ['id' => 'e3', 'source' => 'check_score', 'target' => 'check_flags', 'label' => 'false'],
                ['id' => 'e4', 'source' => 'check_flags', 'target' => 'route_review', 'label' => 'true'],
                ['id' => 'e5', 'source' => 'check_flags', 'target' => 'auto_approve', 'label' => 'false'],
                ['id' => 'e6', 'source' => 'route_review', 'target' => 'human_decision'],
            ],
        ]);

        // Create the moderation workflow linking to base workflow
        return ModerationWorkflow::create([
            'org_id' => $orgId,
            'workflow_id' => $baseWorkflow->id,
            'content_type' => ModerationWorkflow::CONTENT_TYPE_ALL,
            'trigger_conditions' => [],
            'is_default' => true,
            'priority' => 0,
        ]);
    }
}
