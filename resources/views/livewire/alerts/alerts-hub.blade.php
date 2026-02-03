<div class="space-y-4">
    <!-- Tab Toggle -->
    <div class="flex items-center justify-between border-b border-gray-200">
        <nav class="flex gap-6">
            <button
                wire:click="setTab('notifications')"
                class="relative py-3 text-sm font-medium border-b-2 -mb-px transition-colors {{ $tab === 'notifications' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
            >
                <x-icon name="bell" class="w-4 h-4 inline-block mr-1" />
                @term('notifications_label')
                @if($unreadCount > 0)
                    <span class="ml-1.5 px-2 py-0.5 text-xs rounded-full {{ $tab === 'notifications' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-gray-100 text-gray-600' }}">
                        {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                    </span>
                @endif
            </button>
            <button
                wire:click="setTab('workflows')"
                class="py-3 text-sm font-medium border-b-2 -mb-px transition-colors {{ $tab === 'workflows' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
            >
                <x-icon name="bolt" class="w-4 h-4 inline-block mr-1" />
                @term('alert_workflows_label')
            </button>
        </nav>

        {{-- Admin Announcement Button --}}
        @if(auth()->user()->role === 'admin' && $tab === 'notifications')
            <button
                wire:click="$dispatch('openAnnouncementModal')"
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
            >
                <x-icon name="megaphone" class="w-4 h-4 mr-1.5" />
                @term('create_announcement_label')
            </button>
        @endif
    </div>

    <!-- Tab Content -->
    @if($tab === 'notifications')
        <livewire:alerts.notification-center wire:key="notification-center" />
    @else
        <livewire:alerts.alerts-index wire:key="alerts-index" />
    @endif

    {{-- Admin Announcement Modal --}}
    @if(auth()->user()->role === 'admin')
        <livewire:alerts.create-announcement />
    @endif
</div>
