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
        // Check if user has access to the organization
        if (! $user->canAccessOrganization($strategicPlan->org_id)) {
            return false;
        }

        // Admin or consultant can view (respects demo mode)
        if ($user->isAdmin() || $user->hasRole('consultant')) {
            return true;
        }

        // Check if user is a collaborator
        $collaborator = $strategicPlan->collaborators()->where('user_id', $user->id)->first();

        return $collaborator !== null;
    }

    /**
     * Determine whether the user can create strategic plans.
     */
    public function create(User $user): bool
    {
        // Admins, consultants, and teachers can create (respects demo mode)
        return $user->isAdmin() || in_array($user->effective_role, ['consultant', 'teacher']);
    }

    /**
     * Determine whether the user can update the strategic plan.
     */
    public function update(User $user, StrategicPlan $strategicPlan): bool
    {
        // Must have access to the organization
        if (! $user->canAccessOrganization($strategicPlan->org_id)) {
            return false;
        }

        // Org admin or consultant can always update (respects demo mode)
        if ($user->isAdmin() || $user->hasRole('consultant')) {
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
        // Must have access to the organization
        if (! $user->canAccessOrganization($strategicPlan->org_id)) {
            return false;
        }

        // Org admin or consultant can delete (respects demo mode)
        if ($user->isAdmin() || $user->hasRole('consultant')) {
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
        // Must have access to the organization
        if (! $user->canAccessOrganization($strategicPlan->org_id)) {
            return false;
        }

        // Only consultants, admins can push (respects demo mode)
        if (! $user->isAdmin() && ! $user->hasRole('consultant')) {
            return false;
        }

        return true;
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
        // Admin or consultant with access can force delete (respects demo mode)
        return $user->canAccessOrganization($strategicPlan->org_id) && ($user->isAdmin() || $user->hasRole('consultant'));
    }
}
