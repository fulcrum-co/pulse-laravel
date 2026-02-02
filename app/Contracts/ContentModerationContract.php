<?php

namespace App\Contracts;

/**
 * Contract for models that support content moderation.
 *
 * Models using the HasContentModeration trait must implement this interface
 * to ensure they provide the required methods for moderation processing.
 */
interface ContentModerationContract
{
    /**
     * Get the content to be moderated.
     *
     * This should return the primary text content that will be analyzed
     * by the AI moderation system.
     *
     * @return string The content text for moderation
     */
    public function getModerationContent(): string;

    /**
     * Get metadata about the content for moderation context.
     *
     * This should return contextual information that helps the moderation
     * system make better decisions (e.g., target audience, content type).
     *
     * @return array Metadata array with keys like:
     *               - 'content_type': Type of content (course, resource, etc.)
     *               - 'target_audience': Target grades or age group
     *               - 'subject_area': Subject matter
     *               - 'created_by': Creator information
     */
    public function getModerationMetadata(): array;

    /**
     * Get the priority level for moderation.
     *
     * Determines how quickly this content should be reviewed.
     *
     * @return string Priority level: 'low', 'normal', 'high', or 'urgent'
     */
    public function getModerationPriority(): string;

    /**
     * Get the SLA hours for this content type.
     *
     * Returns how many hours this type of content should be moderated within.
     *
     * @return int Number of hours for SLA deadline
     */
    public function getModerationSlaHours(): int;

    /**
     * Handle the approval of this content.
     *
     * Called when content is approved through moderation.
     * Should update the model's status accordingly.
     *
     * @return void
     */
    public function onModerationApproved(): void;

    /**
     * Handle the rejection of this content.
     *
     * Called when content is rejected through moderation.
     * Should update the model's status accordingly.
     *
     * @return void
     */
    public function onModerationRejected(): void;

    /**
     * Handle when revisions are requested.
     *
     * Called when the moderator requests changes.
     * Should update the model's status accordingly.
     *
     * @return void
     */
    public function onModerationRevisionRequested(): void;
}
