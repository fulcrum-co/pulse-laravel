<?php

namespace App\Livewire\Alerts;

use App\Models\Workflow;
use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;

class AlertWizard extends Component
{
    // Current step (1-5)
    public int $currentStep = 1;

    // Workflow being edited (null for new)
    public ?string $workflowId = null;

    // Step 1: Basic Info
    public string $name = '';
    public string $description = '';

    // Step 2: Trigger Configuration
    public string $triggerType = 'metric_threshold';
    public array $conditions = [];
    public string $conditionLogic = 'and';

    // Step 3: Delay (optional)
    public bool $hasDelay = false;
    public int $delayDuration = 1;
    public string $delayUnit = 'hours';

    // Step 4: Actions
    public array $actions = [];

    // Step 5: Settings
    public int $cooldownMinutes = 60;
    public int $maxExecutionsPerDay = 100;

    // UI state
    public bool $showConditionModal = false;
    public bool $showActionModal = false;
    public ?int $editingConditionIndex = null;
    public ?int $editingActionIndex = null;

    // Temp form data for modals
    public array $conditionForm = [
        'field' => '',
        'operator' => 'equals',
        'value' => '',
    ];

    public array $actionForm = [
        'action_type' => 'send_sms',
        'config' => [],
    ];

    protected $rules = [
        'name' => 'required|string|max:255',
        'description' => 'nullable|string|max:1000',
        'triggerType' => 'required|string',
        'conditions' => 'required|array|min:1',
        'actions' => 'required|array|min:1',
    ];

    public function mount(?string $workflowId = null): void
    {
        $this->workflowId = $workflowId;

        if ($workflowId) {
            $workflow = Workflow::forOrg(auth()->user()->org_id)->findOrFail($workflowId);
            $this->loadWorkflow($workflow);
        }
    }

    protected function loadWorkflow(Workflow $workflow): void
    {
        $this->name = $workflow->name;
        $this->description = $workflow->description ?? '';
        $this->triggerType = $workflow->trigger_type;
        $this->conditions = $workflow->trigger_config['conditions'] ?? [];
        $this->conditionLogic = $workflow->trigger_config['logic'] ?? 'and';

        // Load delay from nodes
        $delayNode = collect($workflow->nodes)->firstWhere('type', 'delay');
        if ($delayNode) {
            $this->hasDelay = true;
            $this->delayDuration = $delayNode['data']['duration'] ?? 1;
            $this->delayUnit = $delayNode['data']['unit'] ?? 'hours';
        }

        // Load actions from nodes
        $actionNodes = collect($workflow->nodes)->where('type', 'action');
        $this->actions = $actionNodes->map(fn($node) => [
            'action_type' => $node['data']['action_type'] ?? 'send_sms',
            'config' => $node['data']['config'] ?? [],
        ])->values()->toArray();

        // Load settings
        $this->cooldownMinutes = $workflow->settings['cooldown_minutes'] ?? 60;
        $this->maxExecutionsPerDay = $workflow->settings['max_executions_per_day'] ?? 100;
    }

    public function nextStep(): void
    {
        $this->validateStep();
        $this->currentStep = min($this->currentStep + 1, 5);
    }

    public function previousStep(): void
    {
        $this->currentStep = max($this->currentStep - 1, 1);
    }

    public function goToStep(int $step): void
    {
        if ($step <= $this->currentStep || $this->canAccessStep($step)) {
            $this->currentStep = $step;
        }
    }

    protected function canAccessStep(int $step): bool
    {
        return match ($step) {
            1 => true,
            2 => !empty($this->name),
            3 => !empty($this->conditions),
            4 => true, // Delay is optional
            5 => !empty($this->actions),
            default => false,
        };
    }

    protected function validateStep(): void
    {
        match ($this->currentStep) {
            1 => $this->validate(['name' => 'required|string|max:255']),
            2 => $this->validate(['conditions' => 'required|array|min:1']),
            3 => true, // Delay is optional
            4 => $this->validate(['actions' => 'required|array|min:1']),
            default => true,
        };
    }

    // Condition Modal Methods
    public function openConditionModal(?int $index = null): void
    {
        $this->editingConditionIndex = $index;

        if ($index !== null && isset($this->conditions[$index])) {
            $this->conditionForm = $this->conditions[$index];
        } else {
            $this->conditionForm = [
                'field' => '',
                'operator' => 'equals',
                'value' => '',
            ];
        }

        $this->showConditionModal = true;
    }

    public function closeConditionModal(): void
    {
        $this->showConditionModal = false;
        $this->editingConditionIndex = null;
        $this->conditionForm = ['field' => '', 'operator' => 'equals', 'value' => ''];
    }

    public function saveCondition(): void
    {
        $this->validate([
            'conditionForm.field' => 'required|string',
            'conditionForm.operator' => 'required|string',
            'conditionForm.value' => 'required',
        ]);

        if ($this->editingConditionIndex !== null) {
            $this->conditions[$this->editingConditionIndex] = $this->conditionForm;
        } else {
            $this->conditions[] = $this->conditionForm;
        }

        $this->closeConditionModal();
    }

    public function removeCondition(int $index): void
    {
        unset($this->conditions[$index]);
        $this->conditions = array_values($this->conditions);
    }

    // Action Modal Methods
    public function openActionModal(?int $index = null): void
    {
        $this->editingActionIndex = $index;

        if ($index !== null && isset($this->actions[$index])) {
            $this->actionForm = $this->actions[$index];
        } else {
            $this->actionForm = [
                'action_type' => 'send_sms',
                'config' => [
                    'recipients' => [],
                    'message' => '',
                ],
            ];
        }

        $this->showActionModal = true;
    }

    public function closeActionModal(): void
    {
        $this->showActionModal = false;
        $this->editingActionIndex = null;
        $this->actionForm = ['action_type' => 'send_sms', 'config' => []];
    }

    public function saveAction(): void
    {
        $this->validate([
            'actionForm.action_type' => 'required|string',
        ]);

        if ($this->editingActionIndex !== null) {
            $this->actions[$this->editingActionIndex] = $this->actionForm;
        } else {
            $this->actions[] = $this->actionForm;
        }

        $this->closeActionModal();
    }

    public function removeAction(int $index): void
    {
        unset($this->actions[$index]);
        $this->actions = array_values($this->actions);
    }

    public function save(bool $activate = false)
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'conditions' => 'required|array|min:1',
            'actions' => 'required|array|min:1',
        ]);

        $user = auth()->user();

        // Build nodes array
        $nodes = [];
        $edges = [];
        $nodeIndex = 0;

        // 1. Trigger node
        $triggerId = (string) Str::uuid();
        $nodes[] = [
            'id' => $triggerId,
            'type' => 'trigger',
            'position' => ['x' => 250, 'y' => 0],
            'data' => [
                'trigger_type' => $this->triggerType,
                'conditions' => $this->conditions,
                'logic' => $this->conditionLogic,
            ],
        ];
        $lastNodeId = $triggerId;

        // 2. Delay node (optional)
        if ($this->hasDelay) {
            $delayId = (string) Str::uuid();
            $nodes[] = [
                'id' => $delayId,
                'type' => 'delay',
                'position' => ['x' => 250, 'y' => 150],
                'data' => [
                    'duration' => $this->delayDuration,
                    'unit' => $this->delayUnit,
                ],
            ];
            $edges[] = [
                'id' => (string) Str::uuid(),
                'source' => $lastNodeId,
                'target' => $delayId,
            ];
            $lastNodeId = $delayId;
        }

        // 3. Action nodes
        $yOffset = $this->hasDelay ? 300 : 150;
        foreach ($this->actions as $index => $action) {
            $actionId = (string) Str::uuid();
            $nodes[] = [
                'id' => $actionId,
                'type' => 'action',
                'position' => ['x' => 100 + ($index * 200), 'y' => $yOffset],
                'data' => [
                    'action_type' => $action['action_type'],
                    'config' => $action['config'],
                ],
            ];
            $edges[] = [
                'id' => (string) Str::uuid(),
                'source' => $lastNodeId,
                'target' => $actionId,
            ];
        }

        $data = [
            'org_id' => $user->org_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $activate ? Workflow::STATUS_ACTIVE : Workflow::STATUS_DRAFT,
            'mode' => Workflow::MODE_SIMPLE,
            'trigger_type' => $this->triggerType,
            'trigger_config' => [
                'conditions' => $this->conditions,
                'logic' => $this->conditionLogic,
            ],
            'nodes' => $nodes,
            'edges' => $edges,
            'settings' => [
                'cooldown_minutes' => $this->cooldownMinutes,
                'max_executions_per_day' => $this->maxExecutionsPerDay,
            ],
        ];

        if ($this->workflowId) {
            $workflow = Workflow::forOrg($user->org_id)->findOrFail($this->workflowId);
            $workflow->update($data);
        } else {
            $data['created_by'] = $user->_id;
            $workflow = Workflow::create($data);
            $this->workflowId = $workflow->_id;
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $activate
                ? 'Alert created and activated successfully!'
                : 'Alert saved as draft.',
        ]);

        return redirect()->route('alerts.index');
    }

    public function getAvailableFieldsProperty(): array
    {
        return match ($this->triggerType) {
            'metric_threshold' => [
                'gpa' => 'GPA',
                'attendance_rate' => 'Attendance Rate (%)',
                'wellness_score' => 'Wellness Score',
                'engagement_score' => 'Engagement Score',
                'emotional_wellbeing' => 'Emotional Well-being',
                'plan_progress' => 'Plan Progress (%)',
            ],
            'survey_response' => [
                'risk_level' => 'Risk Level',
                'survey_score' => 'Survey Score',
                'sentiment' => 'Sentiment',
            ],
            'attendance' => [
                'consecutive_absences' => 'Consecutive Absences',
                'monthly_absence_count' => 'Monthly Absence Count',
                'tardy_count' => 'Tardy Count',
            ],
            default => [],
        };
    }

    public function getOperatorsProperty(): array
    {
        return [
            'equals' => 'Equals',
            'not_equals' => 'Not Equals',
            'greater_than' => 'Greater Than',
            'less_than' => 'Less Than',
            'greater_or_equal' => 'Greater or Equal',
            'less_or_equal' => 'Less or Equal',
            'contains' => 'Contains',
            'is_empty' => 'Is Empty',
            'is_not_empty' => 'Is Not Empty',
        ];
    }

    public function getActionTypesProperty(): array
    {
        return [
            'send_sms' => ['label' => 'Send SMS', 'icon' => 'chat-bubble-left'],
            'send_email' => ['label' => 'Send Email', 'icon' => 'envelope'],
            'send_whatsapp' => ['label' => 'Send WhatsApp', 'icon' => 'chat-bubble-oval-left'],
            'make_call' => ['label' => 'Make Voice Call', 'icon' => 'phone'],
            'in_app_notification' => ['label' => 'In-App Notification', 'icon' => 'bell'],
            'webhook' => ['label' => 'Webhook', 'icon' => 'arrow-top-right-on-square'],
            'create_task' => ['label' => 'Create Task', 'icon' => 'clipboard-document-check'],
        ];
    }

    public function getStaffMembersProperty(): array
    {
        return User::where('org_id', auth()->user()->org_id)
            ->whereIn('primary_role', ['admin', 'support_rep', 'teacher'])
            ->get()
            ->map(fn($user) => [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->primary_role,
            ])
            ->toArray();
    }

    public function render()
    {
        return view('livewire.alerts.alert-wizard');
    }
}
