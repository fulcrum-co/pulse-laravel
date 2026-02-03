<?php

namespace App\Livewire\Reports\Concerns;

trait WithComments
{
    public array $comments = [];
    public bool $showComments = false;
    public ?string $activeCommentId = null;
    public string $newCommentText = '';
    public ?array $commentPosition = null;
    public string $commentFilter = 'all'; // all, unresolved, resolved
    public ?int $replyingToCommentId = null;
    public bool $showCommentsPanel = false;

    public function toggleComments(): void
    {
        $this->showComments = !$this->showComments;
    }

    public function openCommentsPanel(): void
    {
        $this->showCommentsPanel = true;
    }

    public function closeCommentsPanel(): void
    {
        $this->showCommentsPanel = false;
    }

    public function getMentionableUsers(): array
    {
        $user = auth()->user();
        if (!$user || !$user->org_id) {
            return [];
        }

        return \App\Models\User::where('org_id', $user->org_id)
            ->select('id', 'first_name', 'last_name', 'email')
            ->limit(50)
            ->get()
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->full_name ?? trim($u->first_name . ' ' . $u->last_name),
                'email' => $u->email,
            ])
            ->toArray();
    }

    public function startComment(int $x, int $y): void
    {
        $this->commentPosition = ['x' => $x, 'y' => $y];
    }

    public function cancelComment(): void
    {
        $this->commentPosition = null;
        $this->newCommentText = '';
    }

    public function addComment(): void
    {
        if (empty($this->newCommentText) || !$this->commentPosition) {
            return;
        }

        $commentId = uniqid('comment_');
        $this->comments[$commentId] = [
            'id' => $commentId,
            'text' => $this->newCommentText,
            'x' => $this->commentPosition['x'],
            'y' => $this->commentPosition['y'],
            'author' => auth()->user()?->name ?? 'Anonymous',
            'author_id' => auth()->id(),
            'created_at' => now()->toISOString(),
            'resolved' => false,
            'replies' => [],
        ];

        $this->newCommentText = '';
        $this->commentPosition = null;
        $this->markDirty();
    }

    public function selectComment(string $commentId): void
    {
        $this->activeCommentId = $commentId;
    }

    public function deselectComment(): void
    {
        $this->activeCommentId = null;
    }

    public function resolveComment(string $commentId): void
    {
        if (isset($this->comments[$commentId])) {
            $this->comments[$commentId]['resolved'] = true;
            $this->comments[$commentId]['resolved_at'] = now()->toISOString();
            $this->comments[$commentId]['resolved_by'] = auth()->user()?->name;
            $this->markDirty();
        }
    }

    public function unresolveComment(string $commentId): void
    {
        if (isset($this->comments[$commentId])) {
            $this->comments[$commentId]['resolved'] = false;
            unset($this->comments[$commentId]['resolved_at']);
            unset($this->comments[$commentId]['resolved_by']);
            $this->markDirty();
        }
    }

    public function deleteComment(string $commentId): void
    {
        unset($this->comments[$commentId]);
        if ($this->activeCommentId === $commentId) {
            $this->activeCommentId = null;
        }
        $this->markDirty();
    }

    public function replyToComment(string $commentId, string $replyText): void
    {
        if (empty($replyText) || !isset($this->comments[$commentId])) {
            return;
        }

        $this->comments[$commentId]['replies'][] = [
            'id' => uniqid('reply_'),
            'text' => $replyText,
            'author' => auth()->user()?->name ?? 'Anonymous',
            'author_id' => auth()->id(),
            'created_at' => now()->toISOString(),
        ];
        $this->markDirty();
    }

    public function getUnresolvedCommentsCount(): int
    {
        return collect($this->comments)->where('resolved', false)->count();
    }

    public function getUnresolvedCount(): int
    {
        return $this->getUnresolvedCommentsCount();
    }

    public function setCommentFilter(string $filter): void
    {
        $this->commentFilter = $filter;
    }

    public function getFilteredComments(): array
    {
        return collect($this->comments)
            ->when($this->commentFilter === 'unresolved', fn ($c) => $c->where('resolved', false))
            ->when($this->commentFilter === 'resolved', fn ($c) => $c->where('resolved', true))
            ->values()
            ->toArray();
    }

    public function startReply(int $commentId): void
    {
        $this->replyingToCommentId = $commentId;
    }

    public function cancelReply(): void
    {
        $this->replyingToCommentId = null;
    }

    public function loadComments(): void
    {
        // Load comments from report if persisted
        if (property_exists($this, 'reportId') && $this->reportId) {
            $report = \App\Models\CustomReport::find($this->reportId);
            if ($report && isset($report->comments)) {
                $this->comments = $report->comments ?? [];
            }
        }
    }

    protected function markDirty(): void
    {
        if (property_exists($this, 'isDirty')) {
            $this->isDirty = true;
        }
    }
}
