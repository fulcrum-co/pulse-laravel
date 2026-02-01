<?php

namespace App\Policies;

use App\Models\ContactNote;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContactNotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any contact notes.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the contact note.
     */
    public function view(User $user, ContactNote $note): bool
    {
        // Must have access to the organization
        if (! $user->canAccessOrganization($note->org_id)) {
            return false;
        }

        // Check visibility settings
        if ($note->visibility === 'private') {
            return $note->created_by === $user->id;
        }

        return true;
    }

    /**
     * Determine whether the user can create contact notes.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the contact note.
     */
    public function update(User $user, ContactNote $note): bool
    {
        // Must have access to the organization
        if (! $user->canAccessOrganization($note->org_id)) {
            return false;
        }

        // Admin or consultant can always update
        if ($user->isAdmin() || $user->primary_role === 'consultant') {
            return true;
        }

        // Creator can update
        return $note->created_by === $user->id;
    }

    /**
     * Determine whether the user can delete the contact note.
     */
    public function delete(User $user, ContactNote $note): bool
    {
        // Must have access to the organization
        if (! $user->canAccessOrganization($note->org_id)) {
            return false;
        }

        // Admin or consultant can delete
        if ($user->isAdmin() || $user->primary_role === 'consultant') {
            return true;
        }

        // Creator can delete
        return $note->created_by === $user->id;
    }

    /**
     * Determine whether the user can restore the contact note.
     */
    public function restore(User $user, ContactNote $note): bool
    {
        return $this->delete($user, $note);
    }

    /**
     * Determine whether the user can permanently delete the contact note.
     */
    public function forceDelete(User $user, ContactNote $note): bool
    {
        return $user->canAccessOrganization($note->org_id) && ($user->isAdmin() || $user->primary_role === 'consultant');
    }
}
