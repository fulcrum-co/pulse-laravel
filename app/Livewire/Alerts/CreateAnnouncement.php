<?php

namespace App\Livewire\Alerts;

use App\Models\User;
use App\Models\UserNotification;
use App\Services\NotificationDeliveryService;
use App\Services\NotificationService;
use Carbon\Carbon;
use Livewire\Component;

class CreateAnnouncement extends Component
{
    public bool $showModal = false;

    public string $title = '';

    public string $body = '';

    public string $priority = 'normal';

    public string $targetType = 'all'; // all, role, specific

    public array $targetRoles = [];

    public array $targetUserIds = [];

    public ?string $expiresAt = null;

    protected $listeners = ['openAnnouncementModal' => 'open'];

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:2000',
            'priority' => 'required|in:low,normal,high,urgent',
            'targetType' => 'required|in:all,role,specific',
            'targetRoles' => 'required_if:targetType,role|array',
            'targetUserIds' => 'required_if:targetType,specific|array',
            'expiresAt' => 'nullable|date|after:now',
        ];
    }

    public function open(): void
    {
        $this->reset(['title', 'body', 'priority', 'targetType', 'targetRoles', 'targetUserIds', 'expiresAt']);
        $this->priority = 'normal';
        $this->targetType = 'all';
        $this->showModal = true;
    }

    public function close(): void
    {
        $this->showModal = false;
    }

    public function toggleRole(string $role): void
    {
        if (in_array($role, $this->targetRoles)) {
            $this->targetRoles = array_values(array_diff($this->targetRoles, [$role]));
        } else {
            $this->targetRoles[] = $role;
        }
    }

    public function send(): void
    {
        $this->validate();

        $userIds = $this->resolveTargetUsers();

        if (empty($userIds)) {
            $this->addError('targetType', 'No users match the selected criteria.');

            return;
        }

        $notificationService = app(NotificationService::class);
        $deliveryService = app(NotificationDeliveryService::class);

        $count = $notificationService->notifyMany(
            $userIds,
            UserNotification::CATEGORY_SYSTEM,
            'admin_announcement',
            [
                'title' => $this->title,
                'body' => $this->body,
                'priority' => $this->priority,
                'icon' => 'megaphone',
                'expires_at' => $this->expiresAt ? Carbon::parse($this->expiresAt) : null,
                'created_by' => auth()->id(),
            ]
        );

        // Dispatch multi-channel delivery for urgent announcements
        if ($count > 0 && in_array($this->priority, ['high', 'urgent'])) {
            $notifications = UserNotification::where('type', 'admin_announcement')
                ->where('created_by', auth()->id())
                ->where('created_at', '>=', now()->subMinute())
                ->get();

            $deliveryService->deliverMany($notifications);
        }

        $this->dispatch('notify', [
            'type' => 'success',
            'message' => "Announcement sent to {$count} users.",
        ]);

        $this->dispatch('announcement-sent', count: $count);
        $this->close();
    }

    /**
     * Resolve target users based on selection.
     */
    protected function resolveTargetUsers(): array
    {
        $user = auth()->user();
        $orgId = $user->org_id;

        return match ($this->targetType) {
            'all' => User::where('org_id', $orgId)
                ->where('id', '!=', $user->id)
                ->pluck('id')
                ->toArray(),
            'role' => User::where('org_id', $orgId)
                ->whereIn('role', $this->targetRoles)
                ->where('id', '!=', $user->id)
                ->pluck('id')
                ->toArray(),
            'specific' => array_filter($this->targetUserIds, fn ($id) => $id != $user->id),
            default => [],
        };
    }

    /**
     * Get available roles for targeting.
     */
    public function getAvailableRolesProperty(): array
    {
        return ['admin', 'manager', 'teacher', 'learner', 'parent'];
    }

    /**
     * Get users for specific user selection.
     */
    public function getUserSearchResultsProperty()
    {
        return User::where('org_id', auth()->user()->org_id)
            ->where('id', '!=', auth()->id())
            ->orderBy('name')
            ->limit(100)
            ->get(['id', 'name', 'email', 'role']);
    }

    public function render()
    {
        return view('livewire.alerts.create-announcement');
    }
}
