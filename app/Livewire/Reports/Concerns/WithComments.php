<?php

namespace App\Livewire\Reports\Concerns;

trait WithComments
{
    // Comments state
    public array $comments = [];

    public bool $showCommentsPanel = false;

    public ?string $activeCommentElementId = null;

    public string $newCommentContent = '';

    public ?int $replyToCommentId = null;

    public string $commentFilter = 'all'; // all, unresolved, resolved

    /**
     * Get count of unresolved comments.
     */
    public function getUnresolvedCount(): int
    {
        if (! $this->reportId) {
            return 0;
        }

        // Check if ReportComment model exists
        if (! class_exists(\App\Models\ReportComment::class)) {
            return 0;
        }

        return \App\Models\ReportComment::where('custom_report_id', $this->reportId)
            ->whereNull('parent_id')
            ->where('resolved', false)
            ->count();
    }

    /**
     * Open comments panel.
     */
    public function openCommentsPanel(): void
    {
        $this->showCommentsPanel = true;
        $this->loadComments();
    }

    /**
     * Load comments for the current report.
     */
    public function loadComments(): void
    {
        if (! $this->reportId) {
            $this->comments = [];

            return;
        }

        // Check if ReportComment model exists
        if (! class_exists(\App\Models\ReportComment::class)) {
            $this->comments = [];

            return;
        }

        $query = \App\Models\ReportComment::where('custom_report_id', $this->reportId)
            ->with(['user', 'replies.user', 'mentions.user'])
            ->whereNull('parent_id')
            ->orderBy('created_at', 'desc');

        if ($this->commentFilter === 'unresolved') {
            $query->where('resolved', false);
        } elseif ($this->commentFilter === 'resolved') {
            $query->where('resolved', true);
        }

        $this->comments = $query->get()
            ->map(fn ($comment) => $this->formatComment($comment))
            ->toArray();
    }

    /**
     * Format a comment for the frontend.
     */
    protected function formatComment($comment): array
    {
        return [
            'id' => $comment->id,
            'content' => $comment->content,
            'elementId' => $comment->element_id,
            'pageIndex' => $comment->page_index,
            'position' => $comment->position,
            'resolved' => $comment->resolved,
            'createdAt' => $comment->created_at->diffForHumans(),
            'user' => [
                'id' => $comment->user_id,
                'name' => $comment->user?->full_name ?? $comment->user?->name ?? 'Unknown',
                'avatar' => $comment->user?->profile_photo_url,
            ],
            'replies' => $comment->replies->map(fn ($reply) => [
                'id' => $reply->id,
                'content' => $reply->content,
                'createdAt' => $reply->created_at->diffForHumans(),
                'user' => [
                    'id' => $reply->user_id,
                    'name' => $reply->user?->full_name ?? $reply->user?->name ?? 'Unknown',
                    'avatar' => $reply->user?->profile_photo_url,
                ],
            ])->toArray(),
            'mentions' => $comment->mentions->map(fn ($mention) => [
                'userId' => $mention->user_id,
                'userName' => $mention->user?->full_name ?? $mention->user?->name ?? 'Unknown',
            ])->toArray(),
        ];
    }

    /**
     * Add a new comment.
     */
    public function addComment(?string $elementId = null, ?array $position = null): void
    {
        if (! $this->reportId || empty($this->newCommentContent)) {
            return;
        }

        // Check if ReportComment model exists
        if (! class_exists(\App\Models\ReportComment::class)) {
            session()->flash('error', 'Comments feature is not yet available.');

            return;
        }

        $user = auth()->user();

        // Parse @mentions from content
        $mentions = $this->parseMentions($this->newCommentContent);

        $comment = \App\Models\ReportComment::create([
            'custom_report_id' => $this->reportId,
            'user_id' => $user->id,
            'parent_id' => $this->replyToCommentId,
            'element_id' => $elementId ?? $this->activeCommentElementId,
            'page_index' => $this->currentPageIndex ?? 0,
            'position' => $position,
            'content' => $this->newCommentContent,
            'resolved' => false,
        ]);

        // Create mentions
        foreach ($mentions as $userId) {
            \App\Models\ReportCommentMention::create([
                'comment_id' => $comment->id,
                'user_id' => $userId,
                'notified' => false,
            ]);

            // Send notification
            $mentionedUser = \App\Models\User::find($userId);
            if ($mentionedUser) {
                $mentionedUser->notify(new \App\Notifications\MentionedInReportComment($comment));
            }
        }

        // Reset state
        $this->newCommentContent = '';
        $this->replyToCommentId = null;
        $this->activeCommentElementId = null;

        // Reload comments
        $this->loadComments();
    }

    /**
     * Reply to a comment.
     */
    public function replyToComment(int $commentId): void
    {
        $this->replyToCommentId = $commentId;
    }

    /**
     * Cancel reply.
     */
    public function cancelReply(): void
    {
        $this->replyToCommentId = null;
    }

    /**
     * Resolve a comment.
     */
    public function resolveComment(int $commentId): void
    {
        if (! class_exists(\App\Models\ReportComment::class)) {
            return;
        }

        $comment = \App\Models\ReportComment::find($commentId);

        if ($comment && $comment->custom_report_id == $this->reportId) {
            $comment->update(['resolved' => true]);
            $this->loadComments();
        }
    }

    /**
     * Unresolve a comment.
     */
    public function unresolveComment(int $commentId): void
    {
        if (! class_exists(\App\Models\ReportComment::class)) {
            return;
        }

        $comment = \App\Models\ReportComment::find($commentId);

        if ($comment && $comment->custom_report_id == $this->reportId) {
            $comment->update(['resolved' => false]);
            $this->loadComments();
        }
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(int $commentId): void
    {
        if (! class_exists(\App\Models\ReportComment::class)) {
            return;
        }

        $comment = \App\Models\ReportComment::find($commentId);

        if ($comment && $comment->custom_report_id == $this->reportId) {
            // Can only delete own comments or if user is admin/owner
            $user = auth()->user();
            $isOwner = $this->reportId && \App\Models\CustomReport::find($this->reportId)?->created_by === $user->id;

            if ($comment->user_id === $user->id || $user->isAdmin() || $isOwner) {
                $comment->delete();
                $this->loadComments();
            }
        }
    }

    /**
     * Toggle comments panel.
     */
    public function toggleCommentsPanel(): void
    {
        $this->showCommentsPanel = ! $this->showCommentsPanel;

        if ($this->showCommentsPanel) {
            $this->loadComments();
        }
    }

    /**
     * Set comment filter.
     */
    public function setCommentFilter(string $filter): void
    {
        $this->commentFilter = $filter;
        $this->loadComments();
    }

    /**
     * Start adding comment to an element.
     */
    public function startElementComment(string $elementId): void
    {
        $this->activeCommentElementId = $elementId;
        $this->showCommentsPanel = true;
    }

    /**
     * Get comments for a specific element.
     */
    public function getCommentsForElement(string $elementId): array
    {
        return array_filter($this->comments, fn ($c) => $c['elementId'] === $elementId);
    }

    /**
     * Get comment count for an element.
     */
    public function getCommentCountForElement(string $elementId): int
    {
        return count($this->getCommentsForElement($elementId));
    }

    /**
     * Parse @mentions from comment content.
     * Returns array of user IDs that were mentioned.
     */
    protected function parseMentions(string $content): array
    {
        $mentions = [];

        // Pattern: @[User Name](user:123)
        preg_match_all('/@\[([^\]]+)\]\(user:(\d+)\)/', $content, $matches);

        if (! empty($matches[2])) {
            $mentions = array_map('intval', $matches[2]);
        }

        return array_unique($mentions);
    }

    /**
     * Get mentionable users (collaborators + org members).
     */
    public function getMentionableUsers(): array
    {
        $user = auth()->user();
        $users = [];

        // Add collaborators
        if ($this->reportId) {
            $report = \App\Models\CustomReport::find($this->reportId);
            if ($report) {
                $collaborators = $report->collaborators()
                    ->with('user')
                    ->get()
                    ->map(fn ($c) => [
                        'id' => $c->user_id,
                        'name' => $c->user?->full_name ?? $c->user?->name ?? 'Unknown',
                    ])
                    ->toArray();

                $users = array_merge($users, $collaborators);
            }
        }

        // Add org members (limit to 50)
        if ($user->org_id) {
            $orgMembers = \App\Models\User::where('org_id', $user->org_id)
                ->where('id', '!=', $user->id)
                ->limit(50)
                ->get()
                ->map(fn ($u) => [
                    'id' => $u->id,
                    'name' => $u->full_name ?? $u->name ?? 'Unknown',
                ])
                ->toArray();

            $users = array_merge($users, $orgMembers);
        }

        // Remove duplicates
        $uniqueUsers = [];
        $seenIds = [];
        foreach ($users as $u) {
            if (! in_array($u['id'], $seenIds)) {
                $uniqueUsers[] = $u;
                $seenIds[] = $u['id'];
            }
        }

        return $uniqueUsers;
    }
}
