<?php

namespace App\Http\Controllers;

use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    /**
     * Display a listing of alerts/workflows.
     */
    public function index()
    {
        return view('alerts.index');
    }

    /**
     * Show the form for creating a new alert.
     */
    public function create()
    {
        return view('alerts.create');
    }

    /**
     * Show the form for editing an alert (wizard mode).
     */
    public function edit(string $workflow)
    {
        $workflowModel = Workflow::forOrg(auth()->user()->org_id)->findOrFail($workflow);

        return view('alerts.edit', ['workflowId' => $workflow, 'workflow' => $workflowModel]);
    }

    /**
     * Show the visual canvas editor.
     */
    public function canvas(string $workflow)
    {
        $workflowModel = Workflow::forOrg(auth()->user()->org_id)->findOrFail($workflow);

        return view('alerts.canvas', ['workflow' => $workflowModel]);
    }

    /**
     * Display execution history for an alert.
     */
    public function history(string $workflow)
    {
        $workflowModel = Workflow::forOrg(auth()->user()->org_id)->findOrFail($workflow);

        $executions = WorkflowExecution::forWorkflow($workflow)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('alerts.history', [
            'workflow' => $workflowModel,
            'executions' => $executions,
        ]);
    }

    /**
     * Toggle workflow status (active/paused).
     */
    public function toggle(Request $request, string $workflow)
    {
        $workflowModel = Workflow::forOrg(auth()->user()->org_id)->findOrFail($workflow);

        $newStatus = $workflowModel->status === Workflow::STATUS_ACTIVE
            ? Workflow::STATUS_PAUSED
            : Workflow::STATUS_ACTIVE;

        $workflowModel->update(['status' => $newStatus]);

        return back()->with('success', 'Alert status updated successfully.');
    }

    /**
     * Test trigger a workflow manually.
     */
    public function test(Request $request, string $workflow)
    {
        $workflowModel = Workflow::forOrg(auth()->user()->org_id)->findOrFail($workflow);

        \App\Jobs\ProcessWorkflow::dispatch($workflowModel, [
            'triggered_by' => 'manual_test',
            'user_id' => auth()->id(),
            'org_id' => auth()->user()->org_id,
            'test_mode' => true,
        ]);

        return back()->with('success', 'Test trigger dispatched. Check execution history for results.');
    }

    /**
     * Delete an alert.
     */
    public function destroy(string $workflow)
    {
        $workflowModel = Workflow::forOrg(auth()->user()->org_id)->findOrFail($workflow);
        $workflowModel->delete();

        return redirect()->route('alerts.index')
            ->with('success', 'Alert deleted successfully.');
    }

    /**
     * Save workflow data from React Flow canvas.
     */
    public function saveWorkflow(Request $request, string $workflow)
    {
        $workflowModel = Workflow::forOrg(auth()->user()->org_id)->findOrFail($workflow);

        $validated = $request->validate([
            'nodes' => 'present|array',
            'edges' => 'present|array',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:1000',
        ]);

        $workflowModel->update([
            'nodes' => $validated['nodes'],
            'edges' => $validated['edges'],
            'name' => $validated['name'] ?? $workflowModel->name,
            'description' => $validated['description'] ?? $workflowModel->description,
            'mode' => Workflow::MODE_ADVANCED,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Workflow saved successfully.',
            'workflow' => $workflowModel->fresh(),
        ]);
    }

    /**
     * Create a new workflow (for canvas mode).
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'mode' => 'required|in:simple,advanced',
        ]);

        $workflow = Workflow::create([
            'org_id' => auth()->user()->org_id,
            'created_by' => auth()->id(),
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'mode' => $validated['mode'],
            'status' => Workflow::STATUS_DRAFT,
            'trigger_type' => Workflow::TRIGGER_MANUAL,
            'nodes' => [],
            'edges' => [],
        ]);

        if ($validated['mode'] === Workflow::MODE_ADVANCED) {
            return redirect()->route('alerts.canvas', $workflow);
        }

        return redirect()->route('alerts.edit', $workflow);
    }

    /**
     * Get workflow data as JSON (for React Flow).
     */
    public function show(string $workflow)
    {
        $workflowModel = Workflow::forOrg(auth()->user()->org_id)->findOrFail($workflow);

        return response()->json([
            'workflow' => $workflowModel,
        ]);
    }

    /**
     * View execution details.
     */
    public function executionDetails(string $workflow, string $execution)
    {
        $workflowModel = Workflow::forOrg(auth()->user()->org_id)->findOrFail($workflow);
        $executionModel = WorkflowExecution::forWorkflow($workflow)->findOrFail($execution);

        return view('alerts.execution-details', [
            'workflow' => $workflowModel,
            'execution' => $executionModel,
        ]);
    }
}
