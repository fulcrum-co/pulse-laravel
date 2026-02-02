<?php

namespace App\Traits;

use App\Models\Organization;
use App\Models\User;
use App\Models\Provider;
use App\Models\Program;
use App\Models\MiniCourseVersion;
use App\Models\MiniCourseStep;
use App\Models\MiniCourseEnrollment;
use App\Models\MiniCourseStepProgress;
use App\Models\MiniCourseSuggestion;
use App\Models\ContentModerationResult;
use App\Models\ModerationQueueItem;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Relationship definitions for the MiniCourse model.
 *
 * Extracted from MiniCourse to reduce model size and improve organization.
 * Contains all relationship methods.
 */
trait MiniCourseRelationships
{
    /**
     * Get the organization that owns the course.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'org_id');
    }

    /**
     * Get the user who created the course.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Alias for creator relationship.
     */
    public function createdBy(): BelongsTo
    {
        return $this->creator();
    }

    /**
     * Get the provider that offers this course.
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * Get the program this course belongs to.
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    /**
     * Get all versions of this course.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(MiniCourseVersion::class, 'mini_course_id')
                    ->orderByDesc('version_number');
    }

    /**
     * Get the current/active version of this course.
     */
    public function currentVersion(): BelongsTo
    {
        return $this->belongsTo(MiniCourseVersion::class, 'current_version_id');
    }

    /**
     * Get the latest version of this course.
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(MiniCourseVersion::class, 'mini_course_id')
                    ->latestOfMany('version_number');
    }

    /**
     * Get all steps in this course.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(MiniCourseStep::class, 'mini_course_id')
                    ->orderBy('sort_order');
    }

    /**
     * Get all enrollments for this course.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(MiniCourseEnrollment::class, 'mini_course_id');
    }

    /**
     * Get active enrollments for this course.
     */
    public function activeEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', 'active');
    }

    /**
     * Get completed enrollments for this course.
     */
    public function completedEnrollments(): HasMany
    {
        return $this->enrollments()->where('status', 'completed');
    }

    /**
     * Get all step progress records for this course.
     */
    public function stepProgress(): HasMany
    {
        return $this->hasMany(MiniCourseStepProgress::class, 'mini_course_id');
    }

    /**
     * Get suggestions related to this course.
     */
    public function suggestions(): HasMany
    {
        return $this->hasMany(MiniCourseSuggestion::class, 'mini_course_id');
    }

    /**
     * Get all moderation results for this course.
     */
    public function moderationResults(): MorphMany
    {
        return $this->morphMany(ContentModerationResult::class, 'moderatable')
                    ->orderByDesc('created_at');
    }

    /**
     * Get the latest moderation result.
     */
    public function latestModerationResult(): MorphMany
    {
        return $this->moderationResults()->limit(1);
    }

    /**
     * Get moderation queue items for this course.
     */
    public function moderationQueueItems(): MorphMany
    {
        return $this->morphMany(ModerationQueueItem::class, 'content');
    }

    /**
     * Get the active moderation queue item.
     */
    public function activeModerationQueueItem(): MorphMany
    {
        return $this->moderationQueueItems()
                    ->whereIn('status', ['pending', 'in_review'])
                    ->limit(1);
    }

    /**
     * Get students enrolled in this course.
     */
    public function students()
    {
        return $this->belongsToMany(
            \App\Models\Student::class,
            'mini_course_enrollments',
            'mini_course_id',
            'student_id'
        )->withPivot(['status', 'progress_percentage', 'started_at', 'completed_at'])
         ->withTimestamps();
    }

    /**
     * Get users enrolled in this course (if enrollment is user-based).
     */
    public function enrolledUsers()
    {
        return $this->belongsToMany(
            User::class,
            'mini_course_enrollments',
            'mini_course_id',
            'user_id'
        )->withPivot(['status', 'progress_percentage', 'started_at', 'completed_at'])
         ->withTimestamps();
    }

    /**
     * Get the course this was duplicated from (if any).
     */
    public function sourceCourse(): BelongsTo
    {
        return $this->belongsTo(self::class, 'source_course_id');
    }

    /**
     * Get courses duplicated from this one.
     */
    public function derivedCourses(): HasMany
    {
        return $this->hasMany(self::class, 'source_course_id');
    }
}
