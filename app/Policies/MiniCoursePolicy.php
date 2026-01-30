<?php

namespace App\Policies;

use App\Models\MiniCourse;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MiniCoursePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any mini courses.
     */
    public function viewAny(User $user): bool
    {
        // Any authenticated user can view the list
        return true;
    }

    /**
     * Determine whether the user can view the mini course.
     */
    public function view(User $user, MiniCourse $course): bool
    {
        // User can view courses in their organization
        if ($course->org_id === $user->org_id) {
            return true;
        }

        // User can view public courses
        if ($course->is_public && $course->status === MiniCourse::STATUS_ACTIVE) {
            return true;
        }

        // User can view templates that are public
        if ($course->is_template && $course->is_public) {
            return true;
        }

        // Check if user's org is a parent (consultant/district viewing downstream)
        if ($user->organization && method_exists($user->organization, 'canPushContentTo')) {
            if ($user->organization->canPushContentTo($course->organization)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine whether the user can create mini courses.
     */
    public function create(User $user): bool
    {
        // Admins, consultants, teachers, and counselors can create courses
        return $user->isAdmin() || in_array($user->role, ['consultant', 'teacher', 'counselor']);
    }

    /**
     * Determine whether the user can update the mini course.
     */
    public function update(User $user, MiniCourse $course): bool
    {
        // Must be in same organization
        if ($course->org_id !== $user->org_id) {
            return false;
        }

        // Org admin can always update
        if ($user->isAdmin()) {
            return true;
        }

        // Creator can update
        if ($course->created_by === $user->id) {
            return true;
        }

        // Teachers and counselors can update courses in their org
        return in_array($user->role, ['consultant', 'teacher', 'counselor']);
    }

    /**
     * Determine whether the user can delete the mini course.
     */
    public function delete(User $user, MiniCourse $course): bool
    {
        // Must be in same organization
        if ($course->org_id !== $user->org_id) {
            return false;
        }

        // Org admin can delete
        if ($user->isAdmin()) {
            return true;
        }

        // Creator can delete (if draft)
        if ($course->created_by === $user->id && $course->status === MiniCourse::STATUS_DRAFT) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can publish the mini course.
     */
    public function publish(User $user, MiniCourse $course): bool
    {
        // Must be in same organization
        if ($course->org_id !== $user->org_id) {
            return false;
        }

        // Must be a draft
        if ($course->status !== MiniCourse::STATUS_DRAFT) {
            return false;
        }

        // Org admin can always publish
        if ($user->isAdmin()) {
            return true;
        }

        // Creator or consultant/teacher can publish
        if ($course->created_by === $user->id || in_array($user->role, ['consultant', 'teacher', 'counselor'])) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can archive the mini course.
     */
    public function archive(User $user, MiniCourse $course): bool
    {
        // Must be in same organization
        if ($course->org_id !== $user->org_id) {
            return false;
        }

        // Must be active
        if ($course->status !== MiniCourse::STATUS_ACTIVE) {
            return false;
        }

        // Same permissions as update
        return $this->update($user, $course);
    }

    /**
     * Determine whether the user can duplicate the mini course.
     */
    public function duplicate(User $user, MiniCourse $course): bool
    {
        // Must be able to view the course and create new ones
        return $this->view($user, $course) && $this->create($user);
    }

    /**
     * Determine whether the user can enroll students in the mini course.
     */
    public function enroll(User $user, MiniCourse $course): bool
    {
        // Course must be active
        if ($course->status !== MiniCourse::STATUS_ACTIVE) {
            return false;
        }

        // Must be in same organization
        if ($course->org_id !== $user->org_id) {
            return false;
        }

        // Admins, teachers, counselors can enroll students
        return $user->isAdmin() || in_array($user->role, ['consultant', 'teacher', 'counselor']);
    }

    /**
     * Determine whether the user can manage versions of the mini course.
     */
    public function manageVersions(User $user, MiniCourse $course): bool
    {
        return $this->update($user, $course);
    }

    /**
     * Determine whether the user can restore the mini course.
     */
    public function restore(User $user, MiniCourse $course): bool
    {
        return $this->delete($user, $course);
    }

    /**
     * Determine whether the user can permanently delete the mini course.
     */
    public function forceDelete(User $user, MiniCourse $course): bool
    {
        // Only org admin can force delete
        return $course->org_id === $user->org_id && $user->isAdmin();
    }
}
