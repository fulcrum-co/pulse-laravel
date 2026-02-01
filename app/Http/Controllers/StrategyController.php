<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\StrategicPlan;
use App\Models\StrategyCollaborator;
use Illuminate\Http\Request;

class StrategyController extends Controller
{
    /**
     * Display a listing of strategies.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $type = $request->get('type', 'all');

        $query = StrategicPlan::where('org_id', $user->org_id)
            ->with(['focusAreas', 'collaborators.user', 'creator'])
            ->orderBy('created_at', 'desc');

        // Filter by plan type
        if ($type !== 'all') {
            $query->where('plan_type', $type);
        }

        // Search by title
        if ($request->has('search')) {
            $query->where('title', 'like', '%'.$request->get('search').'%');
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        $strategies = $query->paginate(20);

        return view('strategies.index', [
            'strategies' => $strategies,
            'currentType' => $type,
        ]);
    }

    /**
     * Show the form for creating a new strategy.
     */
    public function create(Request $request)
    {
        $type = $request->get('type', 'organizational');

        return view('strategies.create', [
            'type' => $type,
        ]);
    }

    /**
     * Store a newly created strategy.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'plan_type' => 'required|in:organizational,teacher,student,department,grade,improvement,growth,strategic,action',
            'category' => 'nullable|in:pip,idp,okr,action_plan',
            'target_type' => 'nullable|string',
            'target_id' => 'nullable|integer',
            'manager_id' => 'nullable|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $user = $request->user();

        // Determine category based on plan type if not provided
        $category = $validated['category'] ?? match ($validated['plan_type']) {
            'improvement' => 'pip',
            'growth' => 'idp',
            'strategic' => 'okr',
            'action' => 'action_plan',
            default => null,
        };

        $strategy = StrategicPlan::create([
            'org_id' => $user->org_id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'plan_type' => $validated['plan_type'],
            'category' => $category,
            'target_type' => $validated['target_type'] ?? null,
            'target_id' => $validated['target_id'] ?? null,
            'manager_id' => $validated['manager_id'] ?? null,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'status' => StrategicPlan::STATUS_DRAFT,
            'created_by' => $user->id,
        ]);

        // Add creator as owner
        StrategyCollaborator::create([
            'strategic_plan_id' => $strategy->id,
            'user_id' => $user->id,
            'role' => StrategyCollaborator::ROLE_OWNER,
        ]);

        return redirect()->route('strategies.show', $strategy)
            ->with('success', 'Strategy created successfully.');
    }

    /**
     * Display the specified strategy.
     */
    public function show(Request $request, StrategicPlan $strategy)
    {
        $this->authorizeView($request->user(), $strategy);

        // Load appropriate relationships based on plan type
        if ($strategy->isOkrStyle()) {
            $strategy->load([
                'goals.keyResults',
                'goals.owner',
                'milestones.goal',
                'milestones.completedByUser',
                'progressUpdates.creator',
                'progressSummaries',
                'collaborators.user',
                'assignments.assignable',
                'manager',
            ]);
            // Default to goals view for OKR plans
            $defaultView = 'goals';
        } else {
            $strategy->load([
                'focusAreas.objectives.activities',
                'collaborators.user',
                'assignments.assignable',
            ]);
            // Default to planner view for traditional plans
            $defaultView = 'planner';
        }

        $view = $request->get('view', $defaultView);

        return view('strategies.show', [
            'strategy' => $strategy,
            'view' => $view,
        ]);
    }

    /**
     * Show the form for editing the specified strategy.
     */
    public function edit(Request $request, StrategicPlan $strategy)
    {
        $this->authorizeEdit($request->user(), $strategy);

        return view('strategies.edit', [
            'strategy' => $strategy,
        ]);
    }

    /**
     * Update the specified strategy.
     */
    public function update(Request $request, StrategicPlan $strategy)
    {
        $this->authorizeEdit($request->user(), $strategy);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'nullable|in:draft,active,completed,archived',
            'consultant_visible' => 'nullable|boolean',
        ]);

        $strategy->update($validated);

        return redirect()->route('strategies.show', $strategy)
            ->with('success', 'Strategy updated successfully.');
    }

    /**
     * Remove the specified strategy.
     */
    public function destroy(Request $request, StrategicPlan $strategy)
    {
        $this->authorizeDelete($request->user(), $strategy);

        $strategy->delete();

        return redirect()->route('strategies.index')
            ->with('success', 'Strategy deleted successfully.');
    }

    /**
     * Duplicate a strategy within the same organization.
     */
    public function duplicate(Request $request, StrategicPlan $strategy)
    {
        $this->authorizeView($request->user(), $strategy);

        $newStrategy = $strategy->duplicate();

        // Add current user as owner of the copy
        StrategyCollaborator::create([
            'strategic_plan_id' => $newStrategy->id,
            'user_id' => $request->user()->id,
            'role' => StrategyCollaborator::ROLE_OWNER,
        ]);

        return redirect()->route('strategies.show', $newStrategy)
            ->with('success', 'Strategy duplicated successfully.');
    }

    /**
     * Push a strategy to a downstream organization.
     */
    public function push(Request $request, StrategicPlan $strategy)
    {
        $this->authorizeEdit($request->user(), $strategy);

        $validated = $request->validate([
            'target_org_id' => 'required|exists:organizations,id',
        ]);

        $targetOrg = Organization::findOrFail($validated['target_org_id']);
        $userOrg = Organization::findOrFail($request->user()->org_id);

        // Check if user's org can push to target org
        if (! $userOrg->canPushContentTo($targetOrg)) {
            return back()->withErrors(['target_org_id' => 'You cannot push content to this organization.']);
        }

        $newStrategy = $strategy->pushToOrganization($targetOrg);

        return redirect()->route('strategies.show', $strategy)
            ->with('success', 'Strategy pushed to '.$targetOrg->org_name.' successfully.');
    }

    /**
     * Check if user can view the strategy.
     */
    protected function authorizeView($user, StrategicPlan $strategy): void
    {
        if ($strategy->org_id !== $user->org_id) {
            abort(403, 'You do not have access to this strategy.');
        }
    }

    /**
     * Check if user can edit the strategy.
     */
    protected function authorizeEdit($user, StrategicPlan $strategy): void
    {
        $this->authorizeView($user, $strategy);

        // Check if user is owner or collaborator with edit rights
        $collaborator = $strategy->collaborators()->where('user_id', $user->id)->first();

        if (! $collaborator || ! $collaborator->canEdit()) {
            // Allow org admins
            if (! $user->isAdmin()) {
                abort(403, 'You do not have permission to edit this strategy.');
            }
        }
    }

    /**
     * Check if user can delete the strategy.
     */
    protected function authorizeDelete($user, StrategicPlan $strategy): void
    {
        $this->authorizeView($user, $strategy);

        // Only owners or admins can delete
        $collaborator = $strategy->collaborators()->where('user_id', $user->id)->first();

        if (! $collaborator || ! $collaborator->isOwner()) {
            if (! $user->isAdmin()) {
                abort(403, 'You do not have permission to delete this strategy.');
            }
        }
    }
}
