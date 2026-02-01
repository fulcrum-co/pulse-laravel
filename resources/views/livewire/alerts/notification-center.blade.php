<div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <h2 class="text-lg font-semibold text-gray-900">Notifications</h2>
            @if($unreadCount > 0)
                <span class="px-2.5 py-1 text-xs font-medium bg-pulse-orange-100 text-pulse-orange-700 rounded-full">
                    {{ $unreadCount }} unread
                </span>
            @endif
        </div>
        @if($unreadCount > 0)
            <button wire:click="markAllAsRead" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700 font-medium">
                Mark all as read
            </button>
        @endif
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
        <!-- Search -->
        <div class="relative w-full sm:w-64">
            <x-icon name="magnifying-glass" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search notifications..."
                class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            />
        </div>

        <!-- Status Filter Pills -->
        <div class="flex flex-wrap items-center gap-2">
            @foreach($statuses as $value => $label)
                <button
                    wire:click="$set('statusFilter', '{{ $value }}')"
                    class="px-3 py-1 text-xs font-medium rounded-full transition-colors {{ $statusFilter === $value ? 'bg-pulse-orange-100 text-pulse-orange-700 ring-1 ring-pulse-orange-300' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                >
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Category Filter Pills -->
    @if(count($categoryCounts) > 0)
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-xs text-gray-500 mr-1">Category:</span>
            <button
                wire:click="$set('categoryFilter', '')"
                class="px-2.5 py-1 text-xs font-medium rounded-full transition-colors {{ $categoryFilter === '' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
            >
                All
            </button>
            @foreach($categories as $key => $category)
                @if(isset($categoryCounts[$key]))
                    <button
                        wire:click="$set('categoryFilter', '{{ $key }}')"
                        class="px-2.5 py-1 text-xs font-medium rounded-full transition-colors inline-flex items-center gap-1 {{ $categoryFilter === $key ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                    >
                        {{ $category['label'] }}
                        <span class="opacity-60">({{ $categoryCounts[$key] }})</span>
                    </button>
                @endif
            @endforeach
        </div>
    @endif

    <!-- Bulk Actions Bar -->
    @if(count($selected) > 0)
        <div class="flex items-center gap-3 p-3 bg-pulse-orange-50 border border-pulse-orange-200 rounded-lg">
            <span class="text-sm font-medium text-pulse-orange-700">{{ count($selected) }} selected</span>
            <button wire:click="deselectAll" class="text-xs text-pulse-orange-600 hover:text-pulse-orange-800 underline">Clear</button>
            <div class="flex items-center gap-2 ml-auto">
                <button wire:click="markSelectedAsRead" class="px-3 py-1 text-xs font-medium text-gray-700 bg-white border border-gray-300 rounded hover:bg-gray-50">
                    Mark as Read
                </button>
                <button wire:click="resolveSelected" class="px-3 py-1 text-xs font-medium text-white bg-green-600 rounded hover:bg-green-700">
                    Resolve
                </button>
                <button wire:click="dismissSelected" class="px-3 py-1 text-xs font-medium text-white bg-gray-600 rounded hover:bg-gray-700">
                    Dismiss
                </button>
            </div>
        </div>
    @endif

    <!-- Notification List -->
    @if($notifications->count() > 0)
        <div class="space-y-2">
            @foreach($notifications as $notification)
                <x-notification-card
                    :notification="$notification"
                    :selected="in_array($notification->id, $selected)"
                    :selectable="true"
                />
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $notifications->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="text-center py-12">
            <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <x-icon name="bell-slash" class="w-8 h-8 text-gray-400" />
            </div>
            <h3 class="text-sm font-medium text-gray-900 mb-1">
                @if($statusFilter === 'all_active')
                    You're all caught up!
                @elseif($statusFilter === 'unread')
                    No unread notifications
                @elseif($statusFilter === 'snoozed')
                    No snoozed notifications
                @elseif($statusFilter === 'resolved')
                    No resolved notifications
                @elseif($statusFilter === 'dismissed')
                    No dismissed notifications
                @endif
            </h3>
            <p class="text-sm text-gray-500">
                @if($statusFilter === 'all_active')
                    No actions needed right now.
                @elseif($search || $categoryFilter)
                    Try adjusting your filters.
                @else
                    Check back later for updates.
                @endif
            </p>
            @if($search || $categoryFilter)
                <button wire:click="clearFilters" class="mt-4 text-sm text-pulse-orange-600 hover:text-pulse-orange-700 font-medium">
                    Clear filters
                </button>
            @endif
        </div>
    @endif
</div>
