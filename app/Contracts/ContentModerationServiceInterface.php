<?php

namespace App\Contracts;

use App\Models\ContentModerationResult;
use App\Models\ModerationQueueItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Interface for Content Moderation Service operations.
 *
 * Defines the contract for AI-assisted and human content moderation,
 * including queue management, scoring, and workflow transitions.
 */
interface ContentModerationServiceInterface
{
    /**
     * Submit content for moderation.
     *
     * @param Model $content The content model (must use HasContentModeration trait)
     * @param User $submitter The user submitting the content
     * @param string $priority Priority level (low, normal, high, urgent)
     * @return ModerationQueueItem The created queue item
     */
    public function submitForModeration(Model $content, User $submitter, string $priority = 'normal'): ModerationQueueItem;

    /**
     * Perform AI moderation on content.
     *
     * @param Model $content The content to moderate
     * @return ContentModerationResult The AI moderation result
     */
    public function performAiModeration(Model $content): ContentModerationResult;

    /**
     * Approve content after review.
     *
     * @param ModerationQueueItem $queueItem The queue item
     * @param User $reviewer The reviewer approving
     * @param string $feedback Approval feedback
     * @param array $scores Optional quality scores
     * @return ContentModerationResult The approval result
     */
    public function approve(
        ModerationQueueItem $queueItem,
        User $reviewer,
        string $feedback,
        array $scores = []
    ): ContentModerationResult;

    /**
     * Reject content after review.
     *
     * @param ModerationQueueItem $queueItem The queue item
     * @param User $reviewer The reviewer rejecting
     * @param string $feedback Rejection feedback
     * @param array $flaggedIssues List of issues flagged
     * @return ContentModerationResult The rejection result
     */
    public function reject(
        ModerationQueueItem $queueItem,
        User $reviewer,
        string $feedback,
        array $flaggedIssues = []
    ): ContentModerationResult;

    /**
     * Request revisions for content.
     *
     * @param ModerationQueueItem $queueItem The queue item
     * @param User $reviewer The reviewer
     * @param string $feedback Feedback for revisions
     * @param array $suggestions List of suggested improvements
     * @return ContentModerationResult The revision request result
     */
    public function requestRevision(
        ModerationQueueItem $queueItem,
        User $reviewer,
        string $feedback,
        array $suggestions = []
    ): ContentModerationResult;

    /**
     * Claim a queue item for review.
     *
     * @param ModerationQueueItem $queueItem The queue item to claim
     * @param User $reviewer The reviewer claiming it
     * @return ModerationQueueItem The updated queue item
     * @throws \App\Exceptions\QueueItemAlreadyClaimedException If already claimed
     */
    public function claimQueueItem(ModerationQueueItem $queueItem, User $reviewer): ModerationQueueItem;

    /**
     * Release a claimed queue item.
     *
     * @param ModerationQueueItem $queueItem The queue item to release
     * @return ModerationQueueItem The updated queue item
     */
    public function releaseQueueItem(ModerationQueueItem $queueItem): ModerationQueueItem;

    /**
     * Escalate a queue item.
     *
     * @param ModerationQueueItem $queueItem The queue item to escalate
     * @param User $escalatedBy The user escalating
     * @param string $reason Reason for escalation
     * @return ModerationQueueItem The escalated queue item
     */
    public function escalate(ModerationQueueItem $queueItem, User $escalatedBy, string $reason): ModerationQueueItem;

    /**
     * Get moderation history for content.
     *
     * @param Model $content The content model
     * @return \Illuminate\Support\Collection Collection of ContentModerationResult
     */
    public function getModerationHistory(Model $content): \Illuminate\Support\Collection;

    /**
     * Calculate quality scores for content.
     *
     * @param string $content The text content to analyze
     * @param array $metadata Additional context metadata
     * @return array Scores for clarity, engagement, accuracy, appropriateness
     */
    public function calculateScores(string $content, array $metadata = []): array;
}
