<?php

namespace App\Livewire\Alerts;

use App\Models\UserNotification;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Url;
use Livewire\Component;

class AlertsHub extends Component
{
    #[Url(except: 'notifications')]
    public string $tab = 'notifications';

    public function setTab(string $tab): void
    {
        $this->tab = $tab;
    }

    /**
     * Get unread notification count for badge.
     */
    public function getUnreadCountProperty(): int
    {
        $user = auth()->user();
        if (! $user) {
            return 0;
        }

        try {
            if (! Schema::hasTable('user_notifications')) {
                return 0;
            }

            return UserNotification::getUnreadCountForUser($user->id);
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function render()
    {
        return view('livewire.alerts.alerts-hub', [
            'unreadCount' => $this->unreadCount,
        ]);
    }
}
