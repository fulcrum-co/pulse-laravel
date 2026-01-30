<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Workflow extends Model
{
    use SoftDeletes;

    protected $table = 'workflows';

    // Status constants
    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_ARCHIVED = 'archived';

    // Mode constants
    public const MODE_SIMPLE = 'simple';
    public const MODE_ADVANCED = 'advanced';

    // Trigger type constants
    public const TRIGGER_METRIC_THRESHOLD = 'metric_threshold';
    public const TRIGGER_METRIC_CHANGE = 'metric_change';
    public const TRIGGER_SURVEY_RESPONSE = 'survey_response';
    public const TRIGGER_SURVEY_ANSWER = 'survey_answer';
    public const TRIGGER_ATTENDANCE = 'attendance';
    public const TRIGGER_SCHEDULE = 'schedule';
    public const TRIGGER_MANUAL = 'manual';

    protected $fillable = [
        'org_id',
        'name',
        'description',
        'status',
        'mode',
        'trigger_type',
        'trigger_config',
        'nodes',
        'edges',
        'settings',
        'created_by',
        'last_triggered_at',
        'execution_count',
        'legacy_trigger_id',
    ];

    protected $casts = [
        'trigger_config' => 'array',
        'nodes' => 'array',
        'edges' => 'array',
        'settings' => 'array',
        'last_triggered_at' => 'datetime',
        'execution_count' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_DRAFT,
        'mode' => self::MODE_SIMPLE,
        'execution_count' => 0,
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (is_null($model->nodes)) {
                $model->nodes = [];
            }
            if (is_null($model->edges)) {
                $model->edges = [];
            }
            if (is_null($model->settings)) {
                $model->settings = [
                    'cooldown_minutes' => 60,
                    'max_executions_per_day' => 100,
                    'timezone' => 'UTC',
                    'active_hours' => null,
                ];
            }
        });
    }

    /**
     * Get the organization.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user who created this workflow.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get workflow executions.
     */
    public function executions(): HasMany
    {
        return $this->hasMany(WorkflowExecution::class, 'workflow_id');
    }

    /**
     * Get the legacy trigger if this workflow wraps one.
     */
    public function legacyTrigger(): BelongsTo
    {
        return $this->belongsTo(Trigger::class, 'legacy_trigger_id');
    }

    /**
     * Check if workflow is in simple mode (wizard-created).
     */
    public function isSimpleMode(): bool
    {
        return $this->mode === self::MODE_SIMPLE;
    }

    /**
     * Check if workflow is in advanced mode (canvas-created).
     */
    public function isAdvancedMode(): bool
    {
        return $this->mode === self::MODE_ADVANCED;
    }

    /**
     * Check if workflow is active.
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Check if workflow is in cooldown period.
     */
    public function isInCooldown(?string $entityId = null): bool
    {
        if (!$this->last_triggered_at) {
            return false;
        }

        $cooldownMinutes = $this->settings['cooldown_minutes'] ?? 60;

        return $this->last_triggered_at->addMinutes($cooldownMinutes)->isFuture();
    }

    /**
     * Check if workflow has exceeded max daily executions.
     */
    public function hasExceededDailyLimit(): bool
    {
        $maxPerDay = $this->settings['max_executions_per_day'] ?? 100;

        $todayCount = $this->executions()
            ->whereDate('created_at', today())
            ->count();

        return $todayCount >= $maxPerDay;
    }

    /**
     * Record that workflow was triggered.
     */
    public function recordTrigger(): void
    {
        $this->increment('execution_count');
        $this->update(['last_triggered_at' => now()]);
    }

    /**
     * Get the entry/trigger node.
     */
    public function getEntryNode(): ?array
    {
        $nodes = $this->nodes ?? [];

        foreach ($nodes as $node) {
            if (($node['type'] ?? '') === 'trigger') {
                return $node;
            }
        }

        return $nodes[0] ?? null;
    }

    /**
     * Get a node by ID.
     */
    public function getNode(string $nodeId): ?array
    {
        foreach ($this->nodes ?? [] as $node) {
            if (($node['id'] ?? '') === $nodeId) {
                return $node;
            }
        }

        return null;
    }

    /**
     * Get outgoing edges from a node.
     */
    public function getOutgoingEdges(string $nodeId): array
    {
        return array_filter($this->edges ?? [], function ($edge) use ($nodeId) {
            return ($edge['source'] ?? '') === $nodeId;
        });
    }

    /**
     * Get incoming edges to a node.
     */
    public function getIncomingEdges(string $nodeId): array
    {
        return array_filter($this->edges ?? [], function ($edge) use ($nodeId) {
            return ($edge['target'] ?? '') === $nodeId;
        });
    }

    /**
     * Get next nodes after a given node.
     */
    public function getNextNodes(string $nodeId): array
    {
        $edges = $this->getOutgoingEdges($nodeId);
        $nextNodes = [];

        foreach ($edges as $edge) {
            $node = $this->getNode($edge['target'] ?? '');
            if ($node) {
                $nextNodes[] = $node;
            }
        }

        return $nextNodes;
    }

    /**
     * Add a node to the workflow.
     */
    public function addNode(string $type, array $data = [], array $position = ['x' => 0, 'y' => 0]): array
    {
        $node = [
            'id' => (string) Str::uuid(),
            'type' => $type,
            'position' => $position,
            'data' => $data,
        ];

        $nodes = $this->nodes ?? [];
        $nodes[] = $node;
        $this->nodes = $nodes;

        return $node;
    }

    /**
     * Add an edge connecting two nodes.
     */
    public function addEdge(string $sourceId, string $targetId, ?string $sourceHandle = null, ?string $targetHandle = null): array
    {
        $edge = [
            'id' => (string) Str::uuid(),
            'source' => $sourceId,
            'target' => $targetId,
        ];

        if ($sourceHandle) {
            $edge['sourceHandle'] = $sourceHandle;
        }
        if ($targetHandle) {
            $edge['targetHandle'] = $targetHandle;
        }

        $edges = $this->edges ?? [];
        $edges[] = $edge;
        $this->edges = $edges;

        return $edge;
    }

    /**
     * Validate the workflow DAG.
     */
    public function validateDag(): array
    {
        $errors = [];
        $nodes = $this->nodes ?? [];
        $edges = $this->edges ?? [];

        // Check for entry node
        $entryNode = $this->getEntryNode();
        if (!$entryNode) {
            $errors[] = 'Workflow must have at least one trigger node.';
        }

        // Check for cycles (basic DFS)
        $visited = [];
        $recursionStack = [];

        foreach ($nodes as $node) {
            if ($this->hasCycle($node['id'], $visited, $recursionStack)) {
                $errors[] = 'Workflow contains a cycle, which is not allowed.';
                break;
            }
        }

        // Check all nodes have valid connections (except triggers can have no incoming)
        foreach ($nodes as $node) {
            $nodeId = $node['id'];
            $nodeType = $node['type'] ?? '';

            $incoming = $this->getIncomingEdges($nodeId);
            $outgoing = $this->getOutgoingEdges($nodeId);

            // Trigger nodes should have no incoming edges
            if ($nodeType === 'trigger' && count($incoming) > 0) {
                $errors[] = "Trigger node '{$nodeId}' should not have incoming connections.";
            }

            // Non-trigger nodes should have at least one incoming edge
            if ($nodeType !== 'trigger' && count($incoming) === 0) {
                $errors[] = "Node '{$nodeId}' has no incoming connections.";
            }
        }

        return $errors;
    }

    /**
     * Check for cycles using DFS.
     */
    protected function hasCycle(string $nodeId, array &$visited, array &$recursionStack): bool
    {
        if (isset($recursionStack[$nodeId])) {
            return true;
        }

        if (isset($visited[$nodeId])) {
            return false;
        }

        $visited[$nodeId] = true;
        $recursionStack[$nodeId] = true;

        foreach ($this->getOutgoingEdges($nodeId) as $edge) {
            $targetId = $edge['target'] ?? '';
            if ($this->hasCycle($targetId, $visited, $recursionStack)) {
                return true;
            }
        }

        unset($recursionStack[$nodeId]);
        return false;
    }

    /**
     * Scope to filter active workflows.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope to filter by organization.
     */
    public function scopeForOrg($query, $orgId)
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope to filter by trigger type.
     */
    public function scopeByTriggerType($query, string $type)
    {
        return $query->where('trigger_type', $type);
    }

    /**
     * Get available trigger types.
     */
    public static function getTriggerTypes(): array
    {
        return [
            self::TRIGGER_METRIC_THRESHOLD => 'Metric Threshold',
            self::TRIGGER_METRIC_CHANGE => 'Metric Change',
            self::TRIGGER_SURVEY_RESPONSE => 'Survey Response',
            self::TRIGGER_SURVEY_ANSWER => 'Survey Answer',
            self::TRIGGER_ATTENDANCE => 'Attendance',
            self::TRIGGER_SCHEDULE => 'Schedule',
            self::TRIGGER_MANUAL => 'Manual',
        ];
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_PAUSED => 'Paused',
            self::STATUS_ARCHIVED => 'Archived',
        ];
    }
}
