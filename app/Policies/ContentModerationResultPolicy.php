<?php

namespace App\Policies;

use App\Models\ContentModerationResult;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContentModerationResultPolicy
{
    use HandlesAuthorization;

    /**
     * Roles that can access the moderation queue.
     */
    protected array $moderatorRoles = ['admin', 'consultant', 'superintendent', 'organization_admin'];

    /**
     * Can user view the moderation queue?
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->effective_role, $this->moderatorRoles);
    }

    /**
     * Can user view this specific moderation result?
     */
    public function view(User $user, ContentModerationResult $result): bool
    {
        if (! $user->canAccessOrganization($result->org_id)) {
            return false;
        }

        // Admins can view all
        if (in_array($user->effective_role, ['admin', 'consultant', 'superintendent'])) {
            return true;
        }

        // Organization admins see their org's items
        if ($user->effective_role === 'organization_admin') {
            return true;
        }

        // Assignees and collaborators can view
        return $result->canBeReviewedBy($user);
    }

    /**
     * Can user review (approve/reject) this result?
     */
    public function review(User $user, ContentModerationResult $result): bool
    {
        if (! $user->canAccessOrganization($result->org_id)) {
            return false;
        }

        return $result->canBeReviewedBy($user);
    }

    /**
     * Can user assign this result to someone?
     */
    public function assign(User $user, ContentModerationResult $result): bool
    {
        if (! $user->canAccessOrganization($result->org_id)) {
            return false;
        }

        // Only admins can assign
        return in_array($user->effective_role, ['admin', 'consultant', 'superintendent', 'organization_admin']);
    }

    /**
     * Can user edit the underlying content?
     */
    public function editContent(User $user, ContentModerationResult $result): bool
    {
        if (! $user->canAccessOrganization($result->org_id)) {
            return false;
        }

        // Must be able to review
        if (! $result->canBeReviewedBy($user)) {
            return false;
        }

        // Must be able to edit the underlying content
        $moderatable = $result->moderatable;
        if (! $moderatable) {
            return false;
        }

        // Use existing policies for content types
        return $user->can('update', $moderatable);
    }

    /**
     * Can user manage (reassign/unassign) this result?
     */
    public function manage(User $user, ContentModerationResult $result): bool
    {
        if (! $user->canAccessOrganization($result->org_id)) {
            return false;
        }

        // Admins can manage any assignment in their org
        if (in_array($user->effective_role, ['admin', 'consultant', 'superintendent'])) {
            return true;
        }

        // Organization admin can manage in their org
        if ($user->effective_role === 'organization_admin') {
            return true;
        }

        // Assigner can manage their own assignments
        if ($result->assigned_by === $user->id) {
            return true;
        }

        return false;
    }
}
