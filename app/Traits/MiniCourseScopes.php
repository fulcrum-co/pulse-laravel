<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Query scopes for the MiniCourse model.
 *
 * Extracted from MiniCourse to reduce model size and improve organization.
 * Contains all scope methods for filtering and querying courses.
 */
trait MiniCourseScopes
{
    /**
     * Scope to published courses only.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
                     ->whereNotNull('published_at');
    }

    /**
     * Scope to draft courses only.
     */
    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to courses pending review.
     */
    public function scopePendingReview(Builder $query): Builder
    {
        return $query->where('status', 'pending_review');
    }

    /**
     * Scope to archived courses only.
     */
    public function scopeArchived(Builder $query): Builder
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope to courses needing revision.
     */
    public function scopeNeedsRevision(Builder $query): Builder
    {
        return $query->where('status', 'needs_revision');
    }

    /**
     * Scope to courses for a specific organization.
     */
    public function scopeForOrganization(Builder $query, int $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope to courses by difficulty level.
     */
    public function scopeByDifficulty(Builder $query, string $level): Builder
    {
        return $query->where('difficulty_level', $level);
    }

    /**
     * Scope to beginner courses.
     */
    public function scopeBeginner(Builder $query): Builder
    {
        return $query->where('difficulty_level', 'beginner');
    }

    /**
     * Scope to intermediate courses.
     */
    public function scopeIntermediate(Builder $query): Builder
    {
        return $query->where('difficulty_level', 'intermediate');
    }

    /**
     * Scope to advanced courses.
     */
    public function scopeAdvanced(Builder $query): Builder
    {
        return $query->where('difficulty_level', 'advanced');
    }

    /**
     * Scope to courses by type.
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('course_type', $type);
    }

    /**
     * Scope to template courses only.
     */
    public function scopeTemplates(Builder $query): Builder
    {
        return $query->where('is_template', true);
    }

    /**
     * Scope to non-template courses only.
     */
    public function scopeNotTemplates(Builder $query): Builder
    {
        return $query->where('is_template', false);
    }

    /**
     * Scope to AI-generated courses.
     */
    public function scopeGenerated(Builder $query): Builder
    {
        return $query->where('course_type', 'generated');
    }

    /**
     * Scope to courses for specific target levels.
     */
    public function scopeForGrades(Builder $query, array $levels): Builder
    {
        return $query->where(function ($q) use ($levels) {
            foreach ($levels as $level) {
                $q->orWhereJsonContains('target_levels', $level);
            }
        });
    }

    /**
     * Scope to courses created by a specific user.
     */
    public function scopeCreatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope to courses from a specific provider.
     */
    public function scopeFromProvider(Builder $query, int $providerId): Builder
    {
        return $query->where('provider_id', $providerId);
    }

    /**
     * Scope to courses in a specific program.
     */
    public function scopeInProgram(Builder $query, int $programId): Builder
    {
        return $query->where('program_id', $programId);
    }

    /**
     * Scope to featured courses.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to recently published courses.
     */
    public function scopeRecentlyPublished(Builder $query, int $days = 30): Builder
    {
        return $query->published()
                     ->where('published_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to search courses by title or description.
     */
    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('title', 'ILIKE', "%{$term}%")
              ->orWhere('description', 'ILIKE', "%{$term}%")
              ->orWhere('short_description', 'ILIKE', "%{$term}%");
        });
    }

    /**
     * Scope to courses with a minimum duration.
     */
    public function scopeMinDuration(Builder $query, int $minutes): Builder
    {
        return $query->where('estimated_duration_minutes', '>=', $minutes);
    }

    /**
     * Scope to courses with a maximum duration.
     */
    public function scopeMaxDuration(Builder $query, int $minutes): Builder
    {
        return $query->where('estimated_duration_minutes', '<=', $minutes);
    }

    /**
     * Scope to courses within a duration range.
     */
    public function scopeDurationBetween(Builder $query, int $min, int $max): Builder
    {
        return $query->whereBetween('estimated_duration_minutes', [$min, $max]);
    }

    /**
     * Scope to order by popularity (view count or enrollment count).
     */
    public function scopePopular(Builder $query): Builder
    {
        return $query->orderByDesc('view_count');
    }

    /**
     * Scope to order by newest first.
     */
    public function scopeNewest(Builder $query): Builder
    {
        return $query->orderByDesc('created_at');
    }

    /**
     * Scope to order by recently updated.
     */
    public function scopeRecentlyUpdated(Builder $query): Builder
    {
        return $query->orderByDesc('updated_at');
    }

    /**
     * Scope to courses with tags.
     */
    public function scopeWithTags(Builder $query, array $tags): Builder
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    /**
     * Scope to accessible courses (published or owned by user).
     */
    public function scopeAccessibleBy(Builder $query, int $userId): Builder
    {
        return $query->where(function ($q) use ($userId) {
            $q->published()
              ->orWhere('created_by', $userId);
        });
    }
}
