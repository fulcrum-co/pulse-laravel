<?php

namespace App\Policies;

use App\Models\StrategicPlan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StrategicPlanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any strategic plans.
     */
    public function viewAny(User $user): bool
    {
        // Any authenticated user can view the list
        return true;
    }

    /**
     * Determine whether the user can view the strategic plan.
     */
    public function view(User $user, StrategicPlan $strategicPlan): bool
    {
        // Must be in same organization
        if ($strategicPlan->org_id !== $user->org_id) {
            // Check if user's org is a parent (consultant/district viewing downstream)
            if (!$user->organization->canPushContentTo($strategicPlan->organization)) {
                return false;
            }
            // Consultants can view if consultant_visible is true
            if (!$strategicPlan->consultant_visible) {
                return false;
            }
        }

        // Owner, collaborator, or org admin can view
        if ($user->isAdmin()) {
            return true;
        }

        $collaborator = $strategicPlan->collaborators()->where('user_id', $user->id)->first();
        return $collaborator !== null;
    }

    /**
     * Determine whether the user can create strategic plans.
     */
    public function create(User $user): bool
    {
        // Admins, consultants, and teachers can create
        return $user->isAdmin() || $user->role === 'consultant' || $user->role === 'teacher';
    }

    /**
     * Determine whether the user can update the strategic plan.
     */
    public function update(User $user, StrategicPlan $strategicPlan): bool
    {
        // Must be in same organization
        if ($strategicPlan->org_id !== $user->org_id) {
            return false;
        }

        // Org admin can always update
        if ($user->isAdmin()) {
            return true;
        }

        // Check if user is a collaborator with edit rights
        $collaborator = $strategicPlan->collaborators()->where('user_id', $user->id)->first();
        return $collaborator && $collaborator->canEdit();
    }

    /**
     * Determine whether the user can delete the strategic plan.
     */
    public function delete(User $user, StrategicPlan $strategicPlan): bool
    {
        // Must be in same organization
        if ($strategicPlan->org_id !== $user->org_id) {
            return false;
        }

        // Org admin can delete
        if ($user->isAdmin()) {
            return true;
        }

        // Owner can delete
        $collaborator = $strategicPlan->collaborators()->where('user_id', $user->id)->first();
        return $collaborator && $collaborator->isOwner();
    }

    /**
     * Determine whether the user can push the strategic plan to downstream orgs.
     */
    public function push(User $user, StrategicPlan $strategicPlan): bool
    {
        // Must be in same organization
        if ($strategicPlan->org_id !== $user->org_id) {
            return false;
        }

        // Only if user's org can push content (consultants, districts)
        $orgType = $user->organization->org_type ?? null;
        if (!in_array($orgType, ['pulse_admin', 'consultant', 'district'])) {
            return false;
        }

        // Must have edit rights
        if ($user->isAdmin()) {
            return true;
        }

        $collaborator = $strategicPlan->collaborators()->where('user_id', $user->id)->first();
        return $collaborator && $collaborator->canEdit();
    }

    /**
     * Determine whether the user can duplicate the strategic plan.
     */
    public function duplicate(User $user, StrategicPlan $strategicPlan): bool
    {
        // Must be able to view and create
        return $this->view($user, $strategicPlan) && $this->create($user);
    }

    /**
     * Determine whether the user can restore the strategic plan.
     */
    public function restore(User $user, StrategicPlan $strategicPlan): bool
    {
        return $this->delete($user, $strategicPlan);
    }

    /**
     * Determine whether the user can permanently delete the strategic plan.
     */
    public function forceDelete(User $user, StrategicPlan $strategicPlan): bool
    {
        // Only org admin can force delete
        return $strategicPlan->org_id === $user->org_id && $user->isAdmin();
    }
}
