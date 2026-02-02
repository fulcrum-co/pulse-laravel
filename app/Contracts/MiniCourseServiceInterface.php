<?php

namespace App\Contracts;

use App\Models\MiniCourse;
use App\Models\MiniCourseVersion;
use App\Models\User;
use App\Models\Organization;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface for Mini Course Service operations.
 *
 * Defines the contract for managing mini-courses,
 * including CRUD, versioning, publishing, and enrollment.
 */
interface MiniCourseServiceInterface
{
    /**
     * Create a new mini-course.
     *
     * @param array $data Course data
     * @param User $creator The user creating the course
     * @param Organization $organization The organization
     * @return MiniCourse The created course
     */
    public function create(array $data, User $creator, Organization $organization): MiniCourse;

    /**
     * Update a mini-course.
     *
     * @param MiniCourse $course The course to update
     * @param array $data Updated data
     * @param bool $createVersion Whether to create a new version
     * @return MiniCourse The updated course
     */
    public function update(MiniCourse $course, array $data, bool $createVersion = false): MiniCourse;

    /**
     * Delete a mini-course (soft delete).
     *
     * @param MiniCourse $course The course to delete
     * @return bool Whether deletion was successful
     */
    public function delete(MiniCourse $course): bool;

    /**
     * Publish a mini-course.
     *
     * @param MiniCourse $course The course to publish
     * @return MiniCourse The published course
     * @throws \App\Exceptions\CourseNotReadyException If course isn't ready
     */
    public function publish(MiniCourse $course): MiniCourse;

    /**
     * Unpublish a mini-course.
     *
     * @param MiniCourse $course The course to unpublish
     * @return MiniCourse The unpublished course
     */
    public function unpublish(MiniCourse $course): MiniCourse;

    /**
     * Archive a mini-course.
     *
     * @param MiniCourse $course The course to archive
     * @return MiniCourse The archived course
     */
    public function archive(MiniCourse $course): MiniCourse;

    /**
     * Submit a mini-course for review.
     *
     * @param MiniCourse $course The course to submit
     * @param User $submitter The user submitting
     * @return MiniCourse The submitted course
     */
    public function submitForReview(MiniCourse $course, User $submitter): MiniCourse;

    /**
     * Duplicate a mini-course.
     *
     * @param MiniCourse $course The course to duplicate
     * @param User $creator The user who will own the copy
     * @param bool $includeSteps Whether to duplicate steps
     * @return MiniCourse The duplicated course
     */
    public function duplicate(MiniCourse $course, User $creator, bool $includeSteps = true): MiniCourse;

    /**
     * Create a new version of a course.
     *
     * @param MiniCourse $course The course to version
     * @param string|null $notes Version notes
     * @return MiniCourseVersion The new version
     */
    public function createVersion(MiniCourse $course, ?string $notes = null): MiniCourseVersion;

    /**
     * Restore a previous version.
     *
     * @param MiniCourse $course The course
     * @param MiniCourseVersion $version The version to restore
     * @return MiniCourse The course with restored version
     */
    public function restoreVersion(MiniCourse $course, MiniCourseVersion $version): MiniCourse;

    /**
     * Get courses for an organization with filters.
     *
     * @param Organization $organization The organization
     * @param array $filters Filters (status, difficulty, search, etc.)
     * @param int $perPage Items per page
     * @return LengthAwarePaginator Paginated courses
     */
    public function getForOrganization(
        Organization $organization,
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator;

    /**
     * Get featured courses.
     *
     * @param Organization $organization The organization
     * @param int $limit Maximum number to return
     * @return Collection Collection of featured courses
     */
    public function getFeatured(Organization $organization, int $limit = 10): Collection;

    /**
     * Get course analytics.
     *
     * @param MiniCourse $course The course
     * @return array Analytics data (enrollments, completion rate, etc.)
     */
    public function getAnalytics(MiniCourse $course): array;

    /**
     * Check if a course is ready for publishing.
     *
     * @param MiniCourse $course The course to check
     * @return array Validation result with 'ready' boolean and 'issues' array
     */
    public function validateForPublishing(MiniCourse $course): array;
}
