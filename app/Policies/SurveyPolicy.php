<?php

namespace App\Policies;

use App\Models\Survey;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SurveyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any surveys.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the survey.
     */
    public function view(User $user, Survey $survey): bool
    {
        // Must be in same organization
        return $survey->org_id === $user->org_id;
    }

    /**
     * Determine whether the user can create surveys.
     */
    public function create(User $user): bool
    {
        // Admins and teachers can create surveys
        return $user->isAdmin() || $user->primary_role === 'teacher';
    }

    /**
     * Determine whether the user can update the survey.
     */
    public function update(User $user, Survey $survey): bool
    {
        // Must be in same organization
        if ($survey->org_id !== $user->org_id) {
            return false;
        }

        // Admin can always update
        if ($user->isAdmin()) {
            return true;
        }

        // Creator can update
        return $survey->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the survey.
     */
    public function delete(User $user, Survey $survey): bool
    {
        // Must be in same organization
        if ($survey->org_id !== $user->org_id) {
            return false;
        }

        // Admin can delete
        if ($user->isAdmin()) {
            return true;
        }

        // Creator can delete
        return $survey->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the survey.
     */
    public function restore(User $user, Survey $survey): bool
    {
        return $this->delete($user, $survey);
    }

    /**
     * Determine whether the user can permanently delete the survey.
     */
    public function forceDelete(User $user, Survey $survey): bool
    {
        return $survey->org_id === $user->org_id && $user->isAdmin();
    }
}
