<?php

namespace App\Livewire\Layouts;

use App\Models\ProviderConversation;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class HeaderNotifications extends Component
{
    /**
     * Get unread alerts/notifications count.
     */
    public function getUnreadAlertsProperty(): int
    {
        $user = auth()->user();
        if (!$user) {
            return 0;
        }

        try {
            if (!Schema::hasTable('user_notifications')) {
                return 0;
            }
            return UserNotification::getUnreadCountForUser($user->id);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get unread messages count.
     */
    public function getUnreadMessagesProperty(): int
    {
        $user = auth()->user();
        if (!$user) {
            return 0;
        }

        try {
            if (!Schema::hasTable('provider_conversations')) {
                return 0;
            }
            return ProviderConversation::where('initiator_type', get_class($user))
                ->where('initiator_id', $user->id)
                ->where('unread_count_initiator', '>', 0)
                ->count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    public function render()
    {
        return view('livewire.layouts.header-notifications', [
            'unreadAlerts' => $this->unreadAlerts,
            'unreadMessages' => $this->unreadMessages,
        ]);
    }
}
