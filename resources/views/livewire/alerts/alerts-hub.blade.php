<div class="space-y-4">
    <!-- Tab Toggle -->
    <div class="flex items-center justify-between border-b border-gray-200">
        <nav class="flex gap-6">
            <button
                wire:click="setTab('notifications')"
                class="relative py-3 text-sm font-medium border-b-2 -mb-px transition-colors {{ $tab === 'notifications' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
            >
                <x-icon name="bell" class="w-4 h-4 inline-block mr-1" />
                Notifications
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
                Alert Workflows
            </button>
        </nav>

        <!-- Create Button (only on workflows tab) -->
        @if($tab === 'workflows')
            <a href="{{ route('alerts.create') }}" class="inline-flex items-center px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors">
                <x-icon name="plus" class="w-4 h-4 mr-1.5" />
                Create Alert
            </a>
        @endif
    </div>

    <!-- Tab Content -->
    @if($tab === 'notifications')
        <livewire:alerts.notification-center />
    @else
        <livewire:alerts.alerts-index />
    @endif
</div>
