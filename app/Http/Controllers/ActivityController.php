<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Objective;
use App\Models\StrategicPlan;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    /**
     * Store a new activity.
     */
    public function store(Request $request, Objective $objective)
    {
        $this->authorizeEdit($request->user(), $objective->focusArea->strategicPlan);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        // Get next sort order
        $maxSortOrder = $objective->activities()->max('sort_order') ?? -1;

        $activity = Activity::create([
            'objective_id' => $objective->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'sort_order' => $maxSortOrder + 1,
            'status' => Activity::STATUS_NOT_STARTED,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'activity' => $activity,
            ]);
        }

        return back()->with('success', 'Activity added successfully.');
    }

    /**
     * Update an activity.
     */
    public function update(Request $request, Activity $activity)
    {
        $this->authorizeEdit($request->user(), $activity->objective->focusArea->strategicPlan);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'sometimes|in:on_track,at_risk,off_track,not_started',
        ]);

        $activity->update($validated);

        // If status changed, update parent hierarchy
        if (isset($validated['status'])) {
            $activity->objective->updateStatusFromChildren();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'activity' => $activity,
            ]);
        }

        return back()->with('success', 'Activity updated successfully.');
    }

    /**
     * Delete an activity.
     */
    public function destroy(Request $request, Activity $activity)
    {
        $this->authorizeEdit($request->user(), $activity->objective->focusArea->strategicPlan);

        $objective = $activity->objective;
        $activity->delete();

        // Update parent hierarchy status
        $objective->updateStatusFromChildren();

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Activity deleted successfully.');
    }

    /**
     * Reorder activities.
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:activities,id',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        foreach ($validated['items'] as $item) {
            $activity = Activity::find($item['id']);
            $this->authorizeEdit($request->user(), $activity->objective->focusArea->strategicPlan);
            $activity->update(['sort_order' => $item['sort_order']]);
        }

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Activities reordered successfully.');
    }

    /**
     * Check if user can edit the strategy.
     */
    protected function authorizeEdit($user, StrategicPlan $strategy): void
    {
        if ($strategy->org_id !== $user->org_id) {
            abort(403, 'You do not have access to this strategy.');
        }

        $collaborator = $strategy->collaborators()->where('user_id', $user->id)->first();

        if (!$collaborator || !$collaborator->canEdit()) {
            if (!$user->isAdmin()) {
                abort(403, 'You do not have permission to edit this strategy.');
            }
        }
    }
}
