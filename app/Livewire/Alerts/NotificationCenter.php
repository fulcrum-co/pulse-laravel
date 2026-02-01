<?php

namespace App\Livewire\Alerts;

use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class NotificationCenter extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: 'all_active')]
    public string $statusFilter = 'all_active';

    #[Url(except: '')]
    public string $categoryFilter = '';

    public array $selected = [];
    public bool $showBulkActions = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all_active'],
        'categoryFilter' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
        $this->selected = [];
    }

    /**
     * Get filtered notifications.
     */
    public function getNotificationsProperty()
    {
        try {
            if (!Schema::hasTable('user_notifications')) {
                return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
            }

            $user = auth()->user();

            $query = UserNotification::forUser($user->id)
                ->notExpired()
                ->with('notifiable');

            // Status filter
            switch ($this->statusFilter) {
                case 'unread':
                    $query->unread();
                    break;
                case 'snoozed':
                    $query->snoozed();
                    break;
                case 'resolved':
                    $query->resolved();
                    break;
                case 'dismissed':
                    $query->dismissed();
                    break;
                case 'all_active':
                default:
                    $query->active();
                    break;
            }

            // Category filter
            if ($this->categoryFilter) {
                $query->byCategory($this->categoryFilter);
            }

            // Search
            if ($this->search) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $this->search . '%')
                      ->orWhere('body', 'like', '%' . $this->search . '%');
                });
            }

            return $query->orderByPriorityAndDate()->paginate(20);
        } catch (\Exception $e) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }
    }

    /**
     * Get unread count.
     */
    public function getUnreadCountProperty(): int
    {
        try {
            if (!Schema::hasTable('user_notifications')) {
                return 0;
            }
            return UserNotification::getUnreadCountForUser(auth()->id());
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get category counts for current filter.
     */
    public function getCategoryCountsProperty(): array
    {
        try {
            if (!Schema::hasTable('user_notifications')) {
                return [];
            }

            $user = auth()->user();

            $query = UserNotification::forUser($user->id)->notExpired();

            // Apply status filter for accurate counts
            switch ($this->statusFilter) {
                case 'unread':
                    $query->unread();
                    break;
                case 'snoozed':
                    $query->snoozed();
                    break;
                case 'resolved':
                    $query->resolved();
                    break;
                case 'dismissed':
                    $query->dismissed();
                    break;
                case 'all_active':
                default:
                    $query->active();
                    break;
            }

            return $query->selectRaw('category, count(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    // ==================== Single Notification Actions ====================

    /**
     * Mark notification as read.
     */
    public function markAsRead(int $id): void
    {
        $notification = $this->getNotification($id);
        if ($notification) {
            $notification->markAsRead();
        }
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread(int $id): void
    {
        $notification = $this->getNotification($id);
        if ($notification) {
            $notification->markAsUnread();
        }
    }

    /**
     * Snooze notification.
     */
    public function snooze(int $id, string $duration): void
    {
        $notification = $this->getNotification($id);
        if (!$notification) {
            return;
        }

        $until = match ($duration) {
            '1_hour' => now()->addHour(),
            '4_hours' => now()->addHours(4),
            'tomorrow' => now()->addDay()->setTime(9, 0),
            'next_monday' => now()->next(Carbon::MONDAY)->setTime(9, 0),
            default => now()->addHour(),
        };

        $notification->snooze($until);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Notification snoozed until ' . $until->format('M j, g:i A'),
        ]);
    }

    /**
     * Snooze with custom date.
     */
    public function snoozeUntil(int $id, string $datetime): void
    {
        $notification = $this->getNotification($id);
        if (!$notification) {
            return;
        }

        $until = Carbon::parse($datetime);
        if ($until->isPast()) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please select a future date and time.',
            ]);
            return;
        }

        $notification->snooze($until);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'Notification snoozed until ' . $until->format('M j, g:i A'),
        ]);
    }

    /**
     * Resolve notification.
     */
    public function resolve(int $id): void
    {
        $notification = $this->getNotification($id);
        if ($notification) {
            $notification->resolve();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Notification marked as resolved.',
            ]);
        }
    }

    /**
     * Dismiss notification.
     */
    public function dismiss(int $id): void
    {
        $notification = $this->getNotification($id);
        if ($notification) {
            $notification->dismiss();

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'Notification dismissed.',
            ]);
        }
    }

    // ==================== Bulk Actions ====================

    /**
     * Toggle selection of a notification.
     */
    public function toggleSelect(int $id): void
    {
        $key = array_search($id, $this->selected);
        if ($key !== false) {
            unset($this->selected[$key]);
            $this->selected = array_values($this->selected);
        } else {
            $this->selected[] = $id;
        }

        $this->showBulkActions = count($this->selected) > 0;
    }

    /**
     * Select all visible notifications.
     */
    public function selectAll(): void
    {
        $this->selected = $this->notifications->pluck('id')->toArray();
        $this->showBulkActions = count($this->selected) > 0;
    }

    /**
     * Clear selection.
     */
    public function deselectAll(): void
    {
        $this->selected = [];
        $this->showBulkActions = false;
    }

    /**
     * Mark all as read (visible + selected).
     */
    public function markAllAsRead(): void
    {
        $user = auth()->user();

        UserNotification::forUser($user->id)
            ->unread()
            ->notExpired()
            ->update([
                'status' => UserNotification::STATUS_READ,
                'read_at' => now(),
            ]);

        UserNotification::invalidateUnreadCountForUser($user->id);

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => 'All notifications marked as read.',
        ]);
    }

    /**
     * Mark selected as read.
     */
    public function markSelectedAsRead(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $user = auth()->user();

        UserNotification::forUser($user->id)
            ->whereIn('id', $this->selected)
            ->unread()
            ->update([
                'status' => UserNotification::STATUS_READ,
                'read_at' => now(),
            ]);

        UserNotification::invalidateUnreadCountForUser($user->id);

        $count = count($this->selected);
        $this->selected = [];
        $this->showBulkActions = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "{$count} notification(s) marked as read.",
        ]);
    }

    /**
     * Resolve selected notifications.
     */
    public function resolveSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $user = auth()->user();

        UserNotification::forUser($user->id)
            ->whereIn('id', $this->selected)
            ->active()
            ->update([
                'status' => UserNotification::STATUS_RESOLVED,
                'resolved_at' => now(),
            ]);

        UserNotification::invalidateUnreadCountForUser($user->id);

        $count = count($this->selected);
        $this->selected = [];
        $this->showBulkActions = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "{$count} notification(s) resolved.",
        ]);
    }

    /**
     * Dismiss selected notifications.
     */
    public function dismissSelected(): void
    {
        if (empty($this->selected)) {
            return;
        }

        $user = auth()->user();

        UserNotification::forUser($user->id)
            ->whereIn('id', $this->selected)
            ->active()
            ->update([
                'status' => UserNotification::STATUS_DISMISSED,
                'dismissed_at' => now(),
            ]);

        UserNotification::invalidateUnreadCountForUser($user->id);

        $count = count($this->selected);
        $this->selected = [];
        $this->showBulkActions = false;

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "{$count} notification(s) dismissed.",
        ]);
    }

    // ==================== Helpers ====================

    /**
     * Get a notification by ID for current user.
     */
    protected function getNotification(int $id): ?UserNotification
    {
        return UserNotification::forUser(auth()->id())->find($id);
    }

    /**
     * Clear all filters.
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->statusFilter = 'all_active';
        $this->categoryFilter = '';
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.alerts.notification-center', [
            'notifications' => $this->notifications,
            'unreadCount' => $this->unreadCount,
            'categoryCounts' => $this->categoryCounts,
            'categories' => UserNotification::getCategories(),
            'statuses' => [
                'all_active' => 'All Active',
                'unread' => 'Unread Only',
                'snoozed' => 'Snoozed',
                'resolved' => 'Resolved',
                'dismissed' => 'Dismissed',
            ],
        ]);
    }
}
