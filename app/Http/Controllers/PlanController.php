<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\StrategicPlan;
use App\Models\StrategyCollaborator;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Display a listing of plans.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $type = $request->get('type', 'all');

        $query = StrategicPlan::where('org_id', $user->org_id)
            ->with(['focusAreas', 'goals', 'collaborators.user', 'creator'])
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

        $plans = $query->paginate(20);

        return view('plans.index', [
            'plans' => $plans,
            'currentType' => $type,
        ]);
    }

    /**
     * Show the form for creating a new plan.
     */
    public function create(Request $request)
    {
        $type = $request->get('type', 'organizational');

        return view('plans.create', [
            'type' => $type,
        ]);
    }

    /**
     * Store a newly created plan.
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

        $plan = StrategicPlan::create([
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
            'strategic_plan_id' => $plan->id,
            'user_id' => $user->id,
            'role' => StrategyCollaborator::ROLE_OWNER,
        ]);

        return redirect()->route('plans.show', $plan)
            ->with('success', 'Plan created successfully.');
    }

    /**
     * Display the specified plan.
     */
    public function show(Request $request, StrategicPlan $plan)
    {
        $this->authorizeView($request->user(), $plan);

        // Load appropriate relationships based on plan type
        if ($plan->isOkrStyle()) {
            $plan->load([
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
            $plan->load([
                'focusAreas.objectives.activities',
                'collaborators.user',
                'assignments.assignable',
            ]);
            // Default to planner view for traditional plans
            $defaultView = 'planner';
        }

        $view = $request->get('view', $defaultView);

        return view('plans.show', [
            'plan' => $plan,
            'view' => $view,
        ]);
    }

    /**
     * Show the form for editing the specified plan.
     */
    public function edit(Request $request, StrategicPlan $plan)
    {
        $this->authorizeEdit($request->user(), $plan);

        return view('plans.edit', [
            'plan' => $plan,
        ]);
    }

    /**
     * Update the specified plan.
     */
    public function update(Request $request, StrategicPlan $plan)
    {
        $this->authorizeEdit($request->user(), $plan);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'nullable|in:draft,active,completed,archived',
            'consultant_visible' => 'nullable|boolean',
        ]);

        $plan->update($validated);

        return redirect()->route('plans.show', $plan)
            ->with('success', 'Plan updated successfully.');
    }

    /**
     * Remove the specified plan.
     */
    public function destroy(Request $request, StrategicPlan $plan)
    {
        $this->authorizeDelete($request->user(), $plan);

        $plan->delete();

        return redirect()->route('plans.index')
            ->with('success', 'Plan deleted successfully.');
    }

    /**
     * Duplicate a plan within the same organization.
     */
    public function duplicate(Request $request, StrategicPlan $plan)
    {
        $this->authorizeView($request->user(), $plan);

        $newPlan = $plan->duplicate();

        // Add current user as owner of the copy
        StrategyCollaborator::create([
            'strategic_plan_id' => $newPlan->id,
            'user_id' => $request->user()->id,
            'role' => StrategyCollaborator::ROLE_OWNER,
        ]);

        return redirect()->route('plans.show', $newPlan)
            ->with('success', 'Plan duplicated successfully.');
    }

    /**
     * Push a plan to a downstream organization.
     */
    public function push(Request $request, StrategicPlan $plan)
    {
        $this->authorizeEdit($request->user(), $plan);

        $validated = $request->validate([
            'target_org_id' => 'required|exists:organizations,id',
        ]);

        $targetOrg = Organization::findOrFail($validated['target_org_id']);
        $userOrg = Organization::findOrFail($request->user()->org_id);

        // Check if user's org can push to target org
        if (! $userOrg->canPushContentTo($targetOrg)) {
            return back()->withErrors(['target_org_id' => 'You cannot push content to this organization.']);
        }

        $newPlan = $plan->pushToOrganization($targetOrg);

        return redirect()->route('plans.show', $plan)
            ->with('success', 'Plan pushed to '.$targetOrg->org_name.' successfully.');
    }

    /**
     * Check if user can view the plan.
     */
    protected function authorizeView($user, StrategicPlan $plan): void
    {
        if (! $user->canAccessOrganization($plan->org_id)) {
            abort(403, 'You do not have access to this plan.');
        }
    }

    /**
     * Check if user can edit the plan.
     */
    protected function authorizeEdit($user, StrategicPlan $plan): void
    {
        $this->authorizeView($user, $plan);

        // Check if user is owner or collaborator with edit rights
        $collaborator = $plan->collaborators()->where('user_id', $user->id)->first();

        if (! $collaborator || ! $collaborator->canEdit()) {
            // Allow org admins
            if (! $user->isAdmin()) {
                abort(403, 'You do not have permission to edit this plan.');
            }
        }
    }

    /**
     * Check if user can delete the plan.
     */
    protected function authorizeDelete($user, StrategicPlan $plan): void
    {
        $this->authorizeView($user, $plan);

        // Only owners or admins can delete
        $collaborator = $plan->collaborators()->where('user_id', $user->id)->first();

        if (! $collaborator || ! $collaborator->isOwner()) {
            if (! $user->isAdmin()) {
                abort(403, 'You do not have permission to delete this plan.');
            }
        }
    }
}
