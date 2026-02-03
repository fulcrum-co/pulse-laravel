<?php

namespace App\Traits;

use App\Jobs\ModerateContentJob;
use App\Models\ContentModerationResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Trait for models that support AI content moderation.
 *
 * Models using this trait should have:
 * - moderation_status column
 * - latest_moderation_id column
 *
 * And must implement:
 * - getModerationContent(): string - Returns the text content to be moderated
 * - getModerationContext(): array - Returns context for moderation (level level, etc.)
 */
trait HasContentModeration
{
    /**
     * Boot the trait.
     */
    protected static function bootHasContentModeration(): void
    {
        // Auto-moderate when content is created or updated
        static::saved(function ($model) {
            if (config('services.moderation.auto_moderate', true)) {
                $model->queueModerationIfNeeded();
            }
        });
    }

    /**
     * Get all moderation results for this content.
     */
    public function moderationResults(): MorphMany
    {
        return $this->morphMany(ContentModerationResult::class, 'moderatable');
    }

    /**
     * Get the latest moderation result.
     */
    public function latestModeration(): BelongsTo
    {
        return $this->belongsTo(ContentModerationResult::class, 'latest_moderation_id');
    }

    /**
     * Queue content for moderation if needed.
     */
    public function queueModerationIfNeeded(): void
    {
        // Check if content fields that need moderation have changed
        $moderationFields = $this->getModerationTextFields();

        foreach ($moderationFields as $field) {
            if ($this->wasChanged($field)) {
                $this->queueModeration();

                return;
            }
        }
    }

    /**
     * Queue this content for moderation.
     */
    public function queueModeration(): void
    {
        // Set status to pending
        $this->update(['moderation_status' => ContentModerationResult::STATUS_PENDING]);

        // Dispatch moderation job
        ModerateContentJob::dispatch($this);
    }

    /**
     * Get the fields that require re-moderation when changed.
     * Override in model to customize.
     */
    protected function getModerationTextFields(): array
    {
        return ['title', 'description', 'content'];
    }

    /**
     * Get the text content to be moderated.
     * Must be implemented by the model.
     */
    abstract public function getModerationContent(): string;

    /**
     * Get context information for moderation.
     * Override in model to provide specific context.
     */
    public function getModerationContext(): array
    {
        return [
            'type' => class_basename($this),
            'id' => $this->getKey(),
            'org_id' => $this->org_id ?? null,
            'target_levels' => $this->target_levels ?? [],
            'is_ai_generated' => $this->isAiGenerated(),
        ];
    }

    /**
     * Check if content is AI-generated.
     * Override in model if needed.
     */
    public function isAiGenerated(): bool
    {
        return false;
    }

    /**
     * Check if content has passed moderation.
     */
    public function hasPassedModeration(): bool
    {
        return in_array($this->moderation_status, [
            ContentModerationResult::STATUS_PASSED,
            ContentModerationResult::STATUS_APPROVED_OVERRIDE,
        ]);
    }

    /**
     * Check if content is flagged for review.
     */
    public function isFlaggedForReview(): bool
    {
        return $this->moderation_status === ContentModerationResult::STATUS_FLAGGED;
    }

    /**
     * Check if content was rejected.
     */
    public function isRejected(): bool
    {
        return $this->moderation_status === ContentModerationResult::STATUS_REJECTED;
    }

    /**
     * Check if content is pending moderation.
     */
    public function isPendingModeration(): bool
    {
        return $this->moderation_status === ContentModerationResult::STATUS_PENDING
            || $this->moderation_status === null;
    }

    /**
     * Get the moderation status label.
     */
    public function getModerationStatusLabelAttribute(): string
    {
        if (! $this->moderation_status) {
            return 'Not Moderated';
        }

        return ContentModerationResult::getStatuses()[$this->moderation_status] ?? 'Unknown';
    }

    /**
     * Get the moderation status color.
     */
    public function getModerationStatusColorAttribute(): string
    {
        if (! $this->moderation_status) {
            return 'gray';
        }

        return ContentModerationResult::getStatusColor($this->moderation_status);
    }

    /**
     * Get flags from latest moderation.
     */
    public function getModerationFlagsAttribute(): array
    {
        return $this->latestModeration?->flags ?? [];
    }

    /**
     * Get recommendations from latest moderation.
     */
    public function getModerationRecommendationsAttribute(): array
    {
        return $this->latestModeration?->recommendations ?? [];
    }

    /**
     * Get the overall moderation score.
     */
    public function getModerationScoreAttribute(): ?float
    {
        return $this->latestModeration?->overall_score;
    }

    /**
     * Scope to content that has passed moderation.
     */
    public function scopeModerated(Builder $query): Builder
    {
        return $query->whereIn('moderation_status', [
            ContentModerationResult::STATUS_PASSED,
            ContentModerationResult::STATUS_APPROVED_OVERRIDE,
        ]);
    }

    /**
     * Scope to content pending moderation review.
     */
    public function scopePendingModerationReview(Builder $query): Builder
    {
        return $query->whereIn('moderation_status', [
            ContentModerationResult::STATUS_FLAGGED,
            ContentModerationResult::STATUS_REJECTED,
        ]);
    }

    /**
     * Scope to flagged content.
     */
    public function scopeFlaggedContent(Builder $query): Builder
    {
        return $query->where('moderation_status', ContentModerationResult::STATUS_FLAGGED);
    }
}
