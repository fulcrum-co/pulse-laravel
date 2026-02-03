<?php

namespace App\Livewire\Reports\Concerns;

trait WithComments
{
    public array $comments = [];
    public bool $showComments = false;
    public ?string $activeCommentId = null;
    public string $newCommentText = '';
    public ?array $commentPosition = null;

    public function toggleComments(): void
    {
        $this->showComments = !$this->showComments;
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

    protected function markDirty(): void
    {
        if (property_exists($this, 'isDirty')) {
            $this->isDirty = true;
        }
    }
}
