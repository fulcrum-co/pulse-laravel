<?php

namespace App\Livewire\Reports\Concerns;

trait WithCollaboration
{
    // Collaboration state
    public array $activeCollaborators = [];

    public array $remoteCursors = [];

    public array $remoteSelections = [];

    public bool $showShareModal = false;

    public string $shareEmail = '';

    public string $shareRole = 'editor';

    /**
     * Initialize collaboration features.
     */
    public function initializeCollaboration(): void
    {
        $this->loadCollaborators();
    }

    /**
     * Broadcast cursor position to other collaborators.
     */
    public function broadcastCursor(float $x, float $y): void
    {
        if (! $this->reportId) {
            return;
        }

        $user = auth()->user();

        // Broadcast cursor position via Laravel Echo
        broadcast(new \App\Events\Report\CursorMoved(
            reportId: (int) $this->reportId,
            userId: $user->id,
            userName: $user->full_name ?? $user->name,
            x: $x,
            y: $y,
            pageIndex: $this->currentPageIndex ?? 0
        ))->toOthers();
    }

    /**
     * Broadcast element selection to other collaborators.
     */
    public function broadcastSelection(?string $elementId): void
    {
        if (! $this->reportId) {
            return;
        }

        $user = auth()->user();

        broadcast(new \App\Events\Report\ElementSelected(
            reportId: (int) $this->reportId,
            userId: $user->id,
            userName: $user->full_name ?? $user->name,
            elementId: $elementId,
            pageIndex: $this->currentPageIndex ?? 0
        ))->toOthers();
    }

    /**
     * Update remote cursor position (called from Echo listener).
     */
    public function updateRemoteCursor(int $userId, string $userName, float $x, float $y, int $pageIndex): void
    {
        $this->remoteCursors[$userId] = [
            'userId' => $userId,
            'userName' => $userName,
            'x' => $x,
            'y' => $y,
            'pageIndex' => $pageIndex,
            'color' => $this->getCollaboratorColor($userId),
            'timestamp' => now()->timestamp,
        ];
    }

    /**
     * Update remote selection (called from Echo listener).
     */
    public function updateRemoteSelection(int $userId, string $userName, ?string $elementId, int $pageIndex): void
    {
        if ($elementId) {
            $this->remoteSelections[$elementId] = [
                'userId' => $userId,
                'userName' => $userName,
                'color' => $this->getCollaboratorColor($userId),
            ];
        } else {
            // Clear any previous selection by this user
            $this->remoteSelections = array_filter(
                $this->remoteSelections,
                fn ($selection) => $selection['userId'] !== $userId
            );
        }
    }

    /**
     * Get a consistent color for a collaborator based on their user ID.
     */
    public function getCollaboratorColor(int $userId): string
    {
        $colors = [
            '#EF4444', // red
            '#F59E0B', // amber
            '#10B981', // emerald
            '#3B82F6', // blue
            '#8B5CF6', // violet
            '#EC4899', // pink
            '#06B6D4', // cyan
            '#84CC16', // lime
        ];

        return $colors[$userId % count($colors)];
    }

    /**
     * Clean up stale cursors (not updated in last 10 seconds).
     */
    public function cleanupStaleCursors(): void
    {
        $threshold = now()->timestamp - 10;

        $this->remoteCursors = array_filter(
            $this->remoteCursors,
            fn ($cursor) => $cursor['timestamp'] >= $threshold
        );
    }

    /**
     * Open the share modal.
     */
    public function openShareModal(): void
    {
        $this->showShareModal = true;
        $this->shareEmail = '';
        $this->shareRole = 'editor';
    }

    /**
     * Close the share modal.
     */
    public function closeShareModal(): void
    {
        $this->showShareModal = false;
    }

    /**
     * Invite a collaborator by email.
     */
    public function inviteCollaborator(): void
    {
        if (! $this->reportId || empty($this->shareEmail)) {
            return;
        }

        $this->validate([
            'shareEmail' => 'required|email',
            'shareRole' => 'required|in:viewer,editor',
        ]);

        // Find user by email
        $user = \App\Models\User::where('email', $this->shareEmail)->first();

        if (! $user) {
            session()->flash('share-error', 'User not found with that email.');

            return;
        }

        // Add collaborator
        $report = \App\Models\CustomReport::find($this->reportId);

        if ($report) {
            $report->addCollaborator($user->id, $this->shareRole);
            $this->loadCollaborators();

            session()->flash('share-success', 'Collaborator added successfully.');
            $this->shareEmail = '';
        }
    }

    /**
     * Remove a collaborator.
     */
    public function removeCollaborator(int $userId): void
    {
        if (! $this->reportId) {
            return;
        }

        $report = \App\Models\CustomReport::find($this->reportId);

        if ($report) {
            $report->removeCollaborator($userId);
            $this->loadCollaborators();
        }
    }

    /**
     * Update collaborator role.
     */
    public function updateCollaboratorRole(int $userId, string $role): void
    {
        if (! $this->reportId) {
            return;
        }

        $report = \App\Models\CustomReport::find($this->reportId);

        if ($report) {
            $report->collaborators()
                ->where('user_id', $userId)
                ->update(['role' => $role]);

            $this->loadCollaborators();
        }
    }

    /**
     * Load current collaborators.
     */
    public function loadCollaborators(): void
    {
        if (! $this->reportId) {
            $this->activeCollaborators = [];

            return;
        }

        $report = \App\Models\CustomReport::find($this->reportId);

        if ($report && method_exists($report, 'collaborators')) {
            $this->activeCollaborators = $report->collaborators()
                ->with('user')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->user_id,
                    'name' => $c->user?->full_name ?? $c->user?->name ?? 'Unknown',
                    'email' => $c->user?->email,
                    'avatar' => $c->user?->profile_photo_url,
                    'role' => $c->role,
                    'lastSeen' => $c->last_seen_at,
                ])
                ->toArray();
        } else {
            $this->activeCollaborators = [];
        }
    }

    /**
     * Get all collaborators including the owner.
     */
    public function getAllCollaborators(): array
    {
        $all = [];

        // Add owner first
        if ($this->reportId) {
            $report = \App\Models\CustomReport::find($this->reportId);
            if ($report && $report->creator) {
                $all[] = [
                    'id' => $report->created_by,
                    'name' => $report->creator?->full_name ?? $report->creator?->name ?? 'Unknown',
                    'email' => $report->creator?->email,
                    'avatar' => $report->creator?->profile_photo_url,
                    'role' => 'owner',
                    'isOwner' => true,
                ];
            }
        }

        // Add collaborators
        foreach ($this->activeCollaborators as $collab) {
            $all[] = array_merge($collab, ['isOwner' => false]);
        }

        return $all;
    }

    /**
     * Add a collaborator (form submission handler).
     */
    public function addCollaborator(): void
    {
        $this->inviteCollaborator();
    }

    /**
     * Check if element is locked by another user.
     */
    public function isElementLockedByOther(string $elementId): bool
    {
        $selection = $this->remoteSelections[$elementId] ?? null;

        return $selection && $selection['userId'] !== auth()->id();
    }

    /**
     * Get lock info for an element.
     */
    public function getElementLockInfo(string $elementId): ?array
    {
        return $this->remoteSelections[$elementId] ?? null;
    }
}
