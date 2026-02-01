<?php

namespace App\Livewire\Alerts;

use App\Models\Workflow;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class AlertsIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $statusFilter = '';

    #[Url(except: '')]
    public string $triggerTypeFilter = '';

    #[Url(except: 'grid')]
    public string $viewMode = 'grid';

    public ?string $workflowToDelete = null;

    public bool $showDeleteModal = false;

    public array $selected = [];

    public bool $showBulkDeleteModal = false;

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
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
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->triggerTypeFilter, function ($query) {
                $query->where('trigger_type', $this->triggerTypeFilter);
            })
            ->pluck('id')
            ->map(fn ($id) => (string) $id)
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

        if (! $workflow) {
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
        if (! $this->workflowToDelete) {
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

        if (! $workflow) {
            return;
        }

        $newWorkflow = $workflow->replicate();
        $newWorkflow->name = $workflow->name.' (Copy)';
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

        if (! $workflow) {
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
     * Get workflows for the list.
     */
    protected function getWorkflows()
    {
        $user = auth()->user();

        return Workflow::forOrg($user->org_id)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
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
        return view('livewire.alerts.alerts-index', [
            'workflows' => $this->getWorkflows(),
            'statuses' => Workflow::getStatuses(),
            'triggerTypes' => Workflow::getTriggerTypes(),
        ]);
    }
}
