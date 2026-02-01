<?php

namespace App\Livewire\Alerts;

use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Livewire\Component;
use Livewire\WithPagination;

class AlertsIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $statusFilter = '';
    public string $triggerTypeFilter = '';
    public string $viewMode = 'grid';
    public ?string $workflowToDelete = null;
    public bool $showDeleteModal = false;
    public array $selected = [];
    public bool $showBulkDeleteModal = false;

    // Tab state
    public string $activeTab = 'workflows';  // 'notifications' | 'workflows'
    public string $notificationStatusFilter = '';

    protected $queryString = [
        'activeTab' => ['except' => 'workflows'],
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'triggerTypeFilter' => ['except' => ''],
        'notificationStatusFilter' => ['except' => ''],
        'viewMode' => ['except' => 'grid'],
    ];

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    /**
     * Switch between tabs.
     */
    public function setActiveTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->selected = [];
    }

    /**
     * Get notification count for badge.
     */
    public function getNotificationCountProperty(): int
    {
        return WorkflowExecution::forOrg(auth()->user()->org_id)
            ->where('created_at', '>=', now()->subHours(24))
            ->count();
    }

    /**
     * Get notifications (workflow executions).
     */
    protected function getNotifications()
    {
        $user = auth()->user();

        return WorkflowExecution::forOrg($user->org_id)
            ->with('workflow:id,name,trigger_type')
            ->when($this->search, fn($q) =>
                $q->whereHas('workflow', fn($wq) =>
                    $wq->where('name', 'like', '%' . $this->search . '%')
                )
            )
            ->when($this->notificationStatusFilter, fn($q) =>
                $q->where('status', $this->notificationStatusFilter)
            )
            ->orderBy('created_at', 'desc')
            ->paginate(12);
    }

    /**
     * Parse node_results to get action summary.
     */
    public function getActionSummary(array $nodeResults): array
    {
        $summary = [];

        foreach ($nodeResults as $nodeId => $result) {
            if (($result['status'] ?? '') !== 'success') {
                continue;
            }

            $output = $result['output'] ?? [];
            $actionType = $output['action_type'] ?? null;

            switch ($actionType) {
                case 'send_email':
                    $count = $output['recipients_count'] ?? 1;
                    $summary[] = "{$count} email" . ($count > 1 ? 's' : '') . " sent";
                    break;
                case 'send_sms':
                    $count = $output['recipients_count'] ?? 1;
                    $summary[] = "{$count} SMS sent";
                    break;
                case 'create_task':
                    $summary[] = "Task created";
                    break;
                case 'webhook':
                    $summary[] = "Webhook called";
                    break;
                case 'in_app_notification':
                    $summary[] = "In-app notification sent";
                    break;
            }
        }

        return $summary;
    }

    /**
     * Retry a failed execution.
     */
    public function retryExecution(string $executionId): void
    {
        $execution = WorkflowExecution::forOrg(auth()->user()->org_id)
            ->with('workflow')
            ->find($executionId);

        if (!$execution || !$execution->workflow) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Execution not found.',
            ]);
            return;
        }

        \App\Jobs\ProcessWorkflow::dispatch(
            $execution->workflow,
            $execution->trigger_data ?? []
        );

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Workflow re-triggered. Check execution history for results.',
        ]);
    }

    public function updatingNotificationStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingTriggerTypeFilter(): void
    {
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->triggerTypeFilter = '';
        $this->notificationStatusFilter = '';
        $this->resetPage();
    }

    /**
     * Toggle selection of a workflow.
     */
    public function toggleSelect(string $workflowId): void
    {
        if (in_array($workflowId, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, [$workflowId]));
        } else {
            $this->selected[] = $workflowId;
        }
    }

    /**
     * Select all workflows on current page.
     */
    public function selectAll(): void
    {
        $user = auth()->user();
        $this->selected = Workflow::forOrg($user->org_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->triggerTypeFilter, function ($query) {
                $query->where('trigger_type', $this->triggerTypeFilter);
            })
            ->pluck('id')
            ->map(fn($id) => (string) $id)
            ->toArray();
    }

    /**
     * Clear selection.
     */
    public function deselectAll(): void
    {
        $this->selected = [];
    }

    /**
     * Show bulk delete confirmation.
     */
    public function confirmBulkDelete(): void
    {
        if (count($this->selected) > 0) {
            $this->showBulkDeleteModal = true;
        }
    }

    /**
     * Cancel bulk delete.
     */
    public function cancelBulkDelete(): void
    {
        $this->showBulkDeleteModal = false;
    }

    /**
     * Delete selected workflows.
     */
    public function deleteSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $user = auth()->user();
        // Cast string IDs to integers for proper PostgreSQL comparison
        $ids = array_map('intval', $this->selected);
        $count = Workflow::forOrg($user->org_id)
            ->whereIn('id', $ids)
            ->delete();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "{$count} alert(s) deleted successfully.",
        ]);

        $this->selected = [];
        $this->showBulkDeleteModal = false;
    }

    /**
     * Toggle workflow active status.
     */
    public function toggleStatus(string $workflowId): void
    {
        $workflow = Workflow::forOrg(auth()->user()->org_id)->find($workflowId);

        if (!$workflow) {
            return;
        }

        $newStatus = $workflow->status === Workflow::STATUS_ACTIVE
            ? Workflow::STATUS_PAUSED
            : Workflow::STATUS_ACTIVE;

        $workflow->update(['status' => $newStatus]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => $newStatus === Workflow::STATUS_ACTIVE
                ? 'Alert activated successfully.'
                : 'Alert paused successfully.',
        ]);
    }

    /**
     * Confirm deletion of a workflow.
     */
    public function confirmDelete(string $workflowId): void
    {
        $this->workflowToDelete = $workflowId;
        $this->showDeleteModal = true;
    }

    /**
     * Cancel deletion.
     */
    public function cancelDelete(): void
    {
        $this->workflowToDelete = null;
        $this->showDeleteModal = false;
    }

    /**
     * Delete the workflow.
     */
    public function deleteWorkflow(): void
    {
        if (!$this->workflowToDelete) {
            return;
        }

        $workflow = Workflow::forOrg(auth()->user()->org_id)->find($this->workflowToDelete);

        if ($workflow) {
            $workflow->delete();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Alert deleted successfully.',
            ]);
        }

        $this->workflowToDelete = null;
        $this->showDeleteModal = false;
    }

    /**
     * Duplicate a workflow.
     */
    public function duplicate(string $workflowId): void
    {
        $workflow = Workflow::forOrg(auth()->user()->org_id)->find($workflowId);

        if (!$workflow) {
            return;
        }

        $newWorkflow = $workflow->replicate();
        $newWorkflow->name = $workflow->name . ' (Copy)';
        $newWorkflow->status = Workflow::STATUS_DRAFT;
        $newWorkflow->execution_count = 0;
        $newWorkflow->last_triggered_at = null;
        $newWorkflow->save();

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Alert duplicated successfully.',
        ]);
    }

    /**
     * Manually trigger a workflow for testing.
     */
    public function testTrigger(string $workflowId): void
    {
        $workflow = Workflow::forOrg(auth()->user()->org_id)->find($workflowId);

        if (!$workflow) {
            return;
        }

        \App\Jobs\ProcessWorkflow::dispatch($workflow, [
            'triggered_by' => 'manual_test',
            'user_id' => auth()->id(),
            'org_id' => auth()->user()->org_id,
        ]);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Test trigger dispatched. Check execution history for results.',
        ]);
    }

    /**
     * Get workflows for the workflows tab.
     */
    protected function getWorkflows()
    {
        $user = auth()->user();

        return Workflow::forOrg($user->org_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->triggerTypeFilter, function ($query) {
                $query->where('trigger_type', $this->triggerTypeFilter);
            })
            ->orderBy('updated_at', 'desc')
            ->paginate(12);
    }

    public function render()
    {
        $data = [
            'statuses' => Workflow::getStatuses(),
            'triggerTypes' => Workflow::getTriggerTypes(),
            'executionStatuses' => WorkflowExecution::getStatuses(),
            'notificationCount' => $this->notificationCount,
            'activeTab' => $this->activeTab,
        ];

        if ($this->activeTab === 'notifications') {
            $data['notifications'] = $this->getNotifications();
        } else {
            $data['workflows'] = $this->getWorkflows();
        }

        return view('livewire.alerts.alerts-index', $data);
    }
}
