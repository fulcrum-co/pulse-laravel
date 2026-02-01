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
        <div class="flex items-center gap-3">
            {{-- View Mode Toggle --}}
            <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                <button
                    type="button"
                    wire:click="setViewMode('list')"
                    class="p-2 transition-colors {{ $viewMode === 'list' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }}"
                    title="List view"
                >
                    <x-icon name="bars-3" class="w-4 h-4" />
                </button>
                <button
                    type="button"
                    wire:click="setViewMode('grouped')"
                    class="p-2 border-l border-gray-300 transition-colors {{ $viewMode === 'grouped' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }}"
                    title="Grouped view"
                >
                    <x-icon name="squares-2x2" class="w-4 h-4" />
                </button>
                <button
                    type="button"
                    wire:click="setViewMode('table')"
                    class="p-2 border-l border-gray-300 transition-colors {{ $viewMode === 'table' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }}"
                    title="Table view"
                >
                    <x-icon name="table-cells" class="w-4 h-4" />
                </button>
            </div>
            @if($unreadCount > 0)
                <button wire:click="markAllAsRead" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700 font-medium">
                    Mark all as read
                </button>
            @endif
            {{-- Start Tasks Button --}}
            @if(count($this->actionableNotifications) > 0)
                <button
                    onclick="window.startTaskFlow({{ Js::from($this->actionableNotifications) }})"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors shadow-sm"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Start Tasks ({{ count($this->actionableNotifications) }})
                </button>
            @endif
        </div>
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
                    type="button"
                    wire:click="setStatusFilter('{{ $value }}')"
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
                type="button"
                wire:click="setCategoryFilter('')"
                class="px-2.5 py-1 text-xs font-medium rounded-full transition-colors {{ $categoryFilter === '' ? 'bg-gray-800 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
            >
                All
            </button>
            @foreach($categories as $key => $category)
                @if(isset($categoryCounts[$key]))
                    <button
                        type="button"
                        wire:click="setCategoryFilter('{{ $key }}')"
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
    @if($viewMode === 'table')
        {{-- Table View --}}
        @if($notifications->count() > 0)
            <div class="overflow-x-auto border border-gray-200 rounded-lg">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="w-8 px-3 py-3">
                                <input
                                    type="checkbox"
                                    wire:click="selectAll"
                                    class="h-4 w-4 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500"
                                />
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Title
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Category
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Priority
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Date
                            </th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($notifications as $notification)
                            @php
                                $categoryInfo = $notification->category_info;
                                $isUnread = $notification->isUnread();
                                $priorityColors = [
                                    'low' => 'bg-gray-100 text-gray-600',
                                    'normal' => 'bg-blue-100 text-blue-700',
                                    'high' => 'bg-amber-100 text-amber-700',
                                    'urgent' => 'bg-red-100 text-red-700',
                                ];
                                $statusColors = [
                                    'unread' => 'bg-pulse-orange-100 text-pulse-orange-700',
                                    'read' => 'bg-gray-100 text-gray-600',
                                    'snoozed' => 'bg-yellow-100 text-yellow-700',
                                    'resolved' => 'bg-green-100 text-green-700',
                                    'dismissed' => 'bg-gray-100 text-gray-500',
                                ];
                            @endphp
                            <tr class="{{ $isUnread ? 'bg-pulse-orange-50/30' : '' }} hover:bg-gray-50 {{ in_array($notification->id, $selected) ? 'bg-pulse-orange-50' : '' }}">
                                <td class="px-3 py-3">
                                    <input
                                        type="checkbox"
                                        wire:click.stop="toggleSelect({{ $notification->id }})"
                                        @checked(in_array($notification->id, $selected))
                                        class="h-4 w-4 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500"
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        @if($isUnread)
                                            <span class="w-2 h-2 bg-pulse-orange-500 rounded-full flex-shrink-0"></span>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="text-sm {{ $isUnread ? 'font-semibold text-gray-900' : 'font-medium text-gray-700' }} truncate max-w-xs">
                                                {{ $notification->title }}
                                            </p>
                                            @if($notification->body)
                                                <p class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($notification->body, 50) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="inline-flex items-center gap-1 text-xs text-gray-600">
                                        <x-icon :name="$categoryInfo['icon']" class="w-3.5 h-3.5" />
                                        {{ $categoryInfo['label'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $priorityColors[$notification->priority] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($notification->priority) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $statusColors[$notification->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($notification->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                                    {{ $notification->created_at->diffForHumans() }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($notification->action_url)
                                            <a
                                                href="{{ $notification->action_url }}"
                                                class="px-2 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600"
                                            >
                                                {{ $notification->action_label ?? 'View' }}
                                            </a>
                                        @endif
                                        <div class="relative" x-data="{ open: false }">
                                            <button
                                                @click.stop="open = !open"
                                                class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded"
                                            >
                                                <x-icon name="ellipsis-vertical" class="w-4 h-4" />
                                            </button>
                                            <div
                                                x-show="open"
                                                @click.outside="open = false"
                                                x-transition
                                                class="absolute right-0 mt-1 w-40 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-10"
                                            >
                                                @if($isUnread)
                                                    <button
                                                        wire:click.stop="markAsRead({{ $notification->id }})"
                                                        @click="open = false"
                                                        class="w-full px-3 py-1.5 text-left text-xs text-gray-700 hover:bg-gray-50"
                                                    >
                                                        Mark as read
                                                    </button>
                                                @else
                                                    <button
                                                        wire:click.stop="markAsUnread({{ $notification->id }})"
                                                        @click="open = false"
                                                        class="w-full px-3 py-1.5 text-left text-xs text-gray-700 hover:bg-gray-50"
                                                    >
                                                        Mark as unread
                                                    </button>
                                                @endif
                                                @if($notification->isActive())
                                                    <button
                                                        wire:click.stop="resolve({{ $notification->id }})"
                                                        @click="open = false"
                                                        class="w-full px-3 py-1.5 text-left text-xs text-green-700 hover:bg-green-50"
                                                    >
                                                        Resolve
                                                    </button>
                                                    <button
                                                        wire:click.stop="dismiss({{ $notification->id }})"
                                                        @click="open = false"
                                                        class="w-full px-3 py-1.5 text-left text-xs text-gray-700 hover:bg-gray-50"
                                                    >
                                                        Dismiss
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $notifications->links() }}
            </div>
        @else
            {{-- Table view but no notifications --}}
            <div class="text-center py-12">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <x-icon name="bell-slash" class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-sm font-medium text-gray-900 mb-1">No notifications</h3>
                <p class="text-sm text-gray-500">You're all caught up!</p>
            </div>
        @endif
    @elseif($viewMode === 'grouped')
        @if(count($groupedNotifications) > 0)
        {{-- Grouped View --}}
        <div x-data="{ expanded: {} }" x-init="expanded = Object.fromEntries(Object.keys(@js($groupedNotifications)).map(k => [k, true]))" class="space-y-3">
            @foreach($groupedNotifications as $category => $group)
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    {{-- Category Header --}}
                    <button
                        @click="expanded['{{ $category }}'] = !expanded['{{ $category }}']"
                        class="w-full flex items-center justify-between p-3 bg-gray-50 hover:bg-gray-100 transition-colors"
                    >
                        <div class="flex items-center gap-2">
                            <x-icon :name="$group['icon']" class="w-5 h-5 text-gray-500" />
                            <span class="font-medium text-gray-900">{{ $group['label'] }}</span>
                            <span class="text-sm text-gray-500">({{ $group['total_count'] }})</span>
                            @if($group['unread_count'] > 0)
                                <span class="px-2 py-0.5 text-xs font-medium bg-pulse-orange-100 text-pulse-orange-700 rounded-full">
                                    {{ $group['unread_count'] }} unread
                                </span>
                            @endif
                        </div>
                        <x-icon
                            name="chevron-down"
                            class="w-4 h-4 text-gray-400 transition-transform"
                            x-bind:class="{ 'rotate-180': expanded['{{ $category }}'] }"
                        />
                    </button>

                    {{-- Category Notifications --}}
                    <div
                        x-show="expanded['{{ $category }}']"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 -translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 translate-y-0"
                        x-transition:leave-end="opacity-0 -translate-y-2"
                        class="border-t border-gray-200"
                    >
                        <div class="p-2 space-y-2">
                            @foreach($group['notifications'] as $notification)
                                @php
                                    $cardCategoryInfo = $notification->category_info;
                                    $cardIsUnread = $notification->isUnread();
                                    $cardIsHighPriority = $notification->isHighPriority();
                                    $cardIsSelected = in_array($notification->id, $selected);

                                    $cardColorMap = [
                                        'blue' => 'bg-blue-100 text-blue-600',
                                        'purple' => 'bg-purple-100 text-purple-600',
                                        'green' => 'bg-green-100 text-green-600',
                                        'orange' => 'bg-orange-100 text-orange-600',
                                        'teal' => 'bg-teal-100 text-teal-600',
                                        'indigo' => 'bg-indigo-100 text-indigo-600',
                                        'gray' => 'bg-gray-100 text-gray-600',
                                    ];
                                    $cardIconColor = $cardColorMap[$cardCategoryInfo['color']] ?? 'bg-gray-100 text-gray-600';

                                    $cardDotColorMap = [
                                        'blue' => 'bg-blue-400',
                                        'purple' => 'bg-purple-400',
                                        'green' => 'bg-green-400',
                                        'orange' => 'bg-orange-400',
                                        'teal' => 'bg-teal-400',
                                        'indigo' => 'bg-indigo-400',
                                        'gray' => 'bg-gray-400',
                                    ];
                                    $cardDotColor = $cardDotColorMap[$cardCategoryInfo['color']] ?? 'bg-gray-400';

                                    $cardPriorityColors = [
                                        'high' => 'bg-amber-100 text-amber-700',
                                        'urgent' => 'bg-red-100 text-red-700',
                                    ];
                                @endphp
                                <div
                                    class="relative flex items-start gap-3 p-4 bg-white border border-gray-200 rounded-lg transition-all cursor-pointer {{ $cardIsUnread ? 'ring-1 ring-pulse-orange-200 bg-pulse-orange-50/30' : '' }} {{ $cardIsSelected ? 'ring-2 ring-pulse-orange-500' : '' }} hover:shadow-sm"
                                    wire:click="markAsRead({{ $notification->id }})"
                                >
                                    <!-- Selection Checkbox -->
                                    <div class="flex-shrink-0 pt-0.5">
                                        <input
                                            type="checkbox"
                                            wire:click.stop="toggleSelect({{ $notification->id }})"
                                            @checked($cardIsSelected)
                                            class="h-4 w-4 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500 cursor-pointer"
                                        />
                                    </div>

                                    <!-- Unread Indicator -->
                                    @if($cardIsUnread)
                                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-pulse-orange-500 rounded-r"></div>
                                    @endif

                                    <!-- Category Icon -->
                                    <div class="flex-shrink-0 w-9 h-9 rounded-lg {{ $cardIconColor }} flex items-center justify-center">
                                        <x-icon :name="$notification->display_icon" class="w-5 h-5" />
                                    </div>

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-2">
                                            <div class="flex-1 min-w-0">
                                                <!-- Title + Priority Badge -->
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="text-sm {{ $cardIsUnread ? 'font-semibold text-gray-900' : 'font-medium text-gray-700' }} truncate">
                                                        {{ $notification->title }}
                                                    </h4>
                                                    @if($cardIsHighPriority)
                                                        <span class="px-1.5 py-0.5 text-xs font-medium rounded {{ $cardPriorityColors[$notification->priority] ?? '' }}">
                                                            {{ ucfirst($notification->priority) }}
                                                        </span>
                                                    @endif
                                                </div>

                                                <!-- Body -->
                                                @if($notification->body)
                                                    <p class="mt-0.5 text-sm text-gray-500 line-clamp-2">
                                                        {{ $notification->body }}
                                                    </p>
                                                @endif

                                                <!-- Meta: Category + Timestamp -->
                                                <div class="mt-1.5 flex items-center gap-3 text-xs text-gray-400">
                                                    <span class="inline-flex items-center gap-1">
                                                        <span class="w-1.5 h-1.5 rounded-full {{ $cardDotColor }}"></span>
                                                        {{ $cardCategoryInfo['label'] }}
                                                    </span>
                                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                                    @if($notification->status === 'snoozed' && $notification->snoozed_until)
                                                        <span class="text-amber-600">
                                                            Snoozed until {{ $notification->snoozed_until->format('M j, g:i A') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Actions -->
                                            <div class="flex items-center gap-2 flex-shrink-0">
                                                <!-- Action Button -->
                                                @if($notification->action_url)
                                                    <a
                                                        href="{{ $notification->action_url }}"
                                                        wire:click.stop
                                                        class="px-3 py-1.5 text-xs font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors"
                                                    >
                                                        {{ $notification->action_label ?? 'View' }}
                                                    </a>
                                                @endif

                                                <!-- Overflow Menu -->
                                                <div class="relative" x-data="{ open: false }">
                                                    <button
                                                        type="button"
                                                        @click.stop="open = !open"
                                                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
                                                    >
                                                        <x-icon name="ellipsis-vertical" class="w-4 h-4" />
                                                    </button>

                                                    <div
                                                        x-show="open"
                                                        @click.outside="open = false"
                                                        x-transition
                                                        class="absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-10"
                                                    >
                                                        @if($cardIsUnread)
                                                            <button
                                                                wire:click.stop="markAsRead({{ $notification->id }})"
                                                                @click="open = false"
                                                                class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                                                            >
                                                                <x-icon name="eye" class="w-4 h-4" />
                                                                Mark as read
                                                            </button>
                                                        @else
                                                            <button
                                                                wire:click.stop="markAsUnread({{ $notification->id }})"
                                                                @click="open = false"
                                                                class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                                                            >
                                                                <x-icon name="eye-slash" class="w-4 h-4" />
                                                                Mark as unread
                                                            </button>
                                                        @endif

                                                        @if($notification->isActive())
                                                            <div x-data="{ snoozeOpen: false }" class="relative">
                                                                <button
                                                                    @click.stop="snoozeOpen = !snoozeOpen"
                                                                    class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between"
                                                                >
                                                                    <span class="flex items-center gap-2">
                                                                        <x-icon name="clock" class="w-4 h-4" />
                                                                        Snooze
                                                                    </span>
                                                                    <x-icon name="chevron-right" class="w-3 h-3" />
                                                                </button>

                                                                <div
                                                                    x-show="snoozeOpen"
                                                                    x-transition
                                                                    class="absolute left-full top-0 ml-1 w-44 bg-white border border-gray-200 rounded-lg shadow-lg py-1"
                                                                >
                                                                    <button
                                                                        wire:click.stop="snooze({{ $notification->id }}, '1_hour')"
                                                                        @click="open = false; snoozeOpen = false"
                                                                        class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                                                    >
                                                                        1 hour
                                                                    </button>
                                                                    <button
                                                                        wire:click.stop="snooze({{ $notification->id }}, '4_hours')"
                                                                        @click="open = false; snoozeOpen = false"
                                                                        class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                                                    >
                                                                        4 hours
                                                                    </button>
                                                                    <button
                                                                        wire:click.stop="snooze({{ $notification->id }}, 'tomorrow')"
                                                                        @click="open = false; snoozeOpen = false"
                                                                        class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                                                    >
                                                                        Tomorrow morning
                                                                    </button>
                                                                    <button
                                                                        wire:click.stop="snooze({{ $notification->id }}, 'next_monday')"
                                                                        @click="open = false; snoozeOpen = false"
                                                                        class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                                                    >
                                                                        Next Monday
                                                                    </button>
                                                                </div>
                                                            </div>

                                                            <button
                                                                wire:click.stop="resolve({{ $notification->id }})"
                                                                @click="open = false"
                                                                class="w-full px-3 py-2 text-left text-sm text-green-700 hover:bg-green-50 flex items-center gap-2"
                                                            >
                                                                <x-icon name="check-circle" class="w-4 h-4" />
                                                                Mark as resolved
                                                            </button>

                                                            <button
                                                                wire:click.stop="dismiss({{ $notification->id }})"
                                                                @click="open = false"
                                                                class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                                                            >
                                                                <x-icon name="x-circle" class="w-4 h-4" />
                                                                Dismiss
                                                            </button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @else
            {{-- Grouped view but no notifications --}}
            <div class="text-center py-12">
                <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                    <x-icon name="bell-slash" class="w-8 h-8 text-gray-400" />
                </div>
                <h3 class="text-sm font-medium text-gray-900 mb-1">No notifications</h3>
                <p class="text-sm text-gray-500">You're all caught up!</p>
            </div>
        @endif
    @elseif($notifications->count() > 0)
        {{-- List View --}}
        <div class="space-y-2">
            @foreach($notifications as $notification)
                @php
                    $cardCategoryInfo = $notification->category_info;
                    $cardIsUnread = $notification->isUnread();
                    $cardIsHighPriority = $notification->isHighPriority();
                    $cardIsSelected = in_array($notification->id, $selected);

                    $cardColorMap = [
                        'blue' => 'bg-blue-100 text-blue-600',
                        'purple' => 'bg-purple-100 text-purple-600',
                        'green' => 'bg-green-100 text-green-600',
                        'orange' => 'bg-orange-100 text-orange-600',
                        'teal' => 'bg-teal-100 text-teal-600',
                        'indigo' => 'bg-indigo-100 text-indigo-600',
                        'gray' => 'bg-gray-100 text-gray-600',
                    ];
                    $cardIconColor = $cardColorMap[$cardCategoryInfo['color']] ?? 'bg-gray-100 text-gray-600';

                    $cardDotColorMap = [
                        'blue' => 'bg-blue-400',
                        'purple' => 'bg-purple-400',
                        'green' => 'bg-green-400',
                        'orange' => 'bg-orange-400',
                        'teal' => 'bg-teal-400',
                        'indigo' => 'bg-indigo-400',
                        'gray' => 'bg-gray-400',
                    ];
                    $cardDotColor = $cardDotColorMap[$cardCategoryInfo['color']] ?? 'bg-gray-400';

                    $cardPriorityColors = [
                        'high' => 'bg-amber-100 text-amber-700',
                        'urgent' => 'bg-red-100 text-red-700',
                    ];
                @endphp
                <div
                    class="relative flex items-start gap-3 p-4 bg-white border border-gray-200 rounded-lg transition-all cursor-pointer {{ $cardIsUnread ? 'ring-1 ring-pulse-orange-200 bg-pulse-orange-50/30' : '' }} {{ $cardIsSelected ? 'ring-2 ring-pulse-orange-500' : '' }} hover:shadow-sm"
                    wire:click="markAsRead({{ $notification->id }})"
                >
                    <!-- Selection Checkbox -->
                    <div class="flex-shrink-0 pt-0.5">
                        <input
                            type="checkbox"
                            wire:click.stop="toggleSelect({{ $notification->id }})"
                            @checked($cardIsSelected)
                            class="h-4 w-4 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500 cursor-pointer"
                        />
                    </div>

                    <!-- Unread Indicator -->
                    @if($cardIsUnread)
                        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-pulse-orange-500 rounded-r"></div>
                    @endif

                    <!-- Category Icon -->
                    <div class="flex-shrink-0 w-9 h-9 rounded-lg {{ $cardIconColor }} flex items-center justify-center">
                        <x-icon :name="$notification->display_icon" class="w-5 h-5" />
                    </div>

                    <!-- Content -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex-1 min-w-0">
                                <!-- Title + Priority Badge -->
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-sm {{ $cardIsUnread ? 'font-semibold text-gray-900' : 'font-medium text-gray-700' }} truncate">
                                        {{ $notification->title }}
                                    </h4>
                                    @if($cardIsHighPriority)
                                        <span class="px-1.5 py-0.5 text-xs font-medium rounded {{ $cardPriorityColors[$notification->priority] ?? '' }}">
                                            {{ ucfirst($notification->priority) }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Body -->
                                @if($notification->body)
                                    <p class="mt-0.5 text-sm text-gray-500 line-clamp-2">
                                        {{ $notification->body }}
                                    </p>
                                @endif

                                <!-- Meta: Category + Timestamp -->
                                <div class="mt-1.5 flex items-center gap-3 text-xs text-gray-400">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $cardDotColor }}"></span>
                                        {{ $cardCategoryInfo['label'] }}
                                    </span>
                                    <span>{{ $notification->created_at->diffForHumans() }}</span>
                                    @if($notification->status === 'snoozed' && $notification->snoozed_until)
                                        <span class="text-amber-600">
                                            Snoozed until {{ $notification->snoozed_until->format('M j, g:i A') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-2 flex-shrink-0">
                                <!-- Action Button -->
                                @if($notification->action_url)
                                    <a
                                        href="{{ $notification->action_url }}"
                                        wire:click.stop
                                        class="px-3 py-1.5 text-xs font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors"
                                    >
                                        {{ $notification->action_label ?? 'View' }}
                                    </a>
                                @endif

                                <!-- Overflow Menu -->
                                <div class="relative" x-data="{ open: false }">
                                    <button
                                        type="button"
                                        @click.stop="open = !open"
                                        class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded transition-colors"
                                    >
                                        <x-icon name="ellipsis-vertical" class="w-4 h-4" />
                                    </button>

                                    <div
                                        x-show="open"
                                        @click.outside="open = false"
                                        x-transition
                                        class="absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-lg shadow-lg py-1 z-10"
                                    >
                                        @if($cardIsUnread)
                                            <button
                                                wire:click.stop="markAsRead({{ $notification->id }})"
                                                @click="open = false"
                                                class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                                            >
                                                <x-icon name="eye" class="w-4 h-4" />
                                                Mark as read
                                            </button>
                                        @else
                                            <button
                                                wire:click.stop="markAsUnread({{ $notification->id }})"
                                                @click="open = false"
                                                class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                                            >
                                                <x-icon name="eye-slash" class="w-4 h-4" />
                                                Mark as unread
                                            </button>
                                        @endif

                                        @if($notification->isActive())
                                            <div x-data="{ snoozeOpen: false }" class="relative">
                                                <button
                                                    @click.stop="snoozeOpen = !snoozeOpen"
                                                    class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center justify-between"
                                                >
                                                    <span class="flex items-center gap-2">
                                                        <x-icon name="clock" class="w-4 h-4" />
                                                        Snooze
                                                    </span>
                                                    <x-icon name="chevron-right" class="w-3 h-3" />
                                                </button>

                                                <div
                                                    x-show="snoozeOpen"
                                                    x-transition
                                                    class="absolute left-full top-0 ml-1 w-44 bg-white border border-gray-200 rounded-lg shadow-lg py-1"
                                                >
                                                    <button
                                                        wire:click.stop="snooze({{ $notification->id }}, '1_hour')"
                                                        @click="open = false; snoozeOpen = false"
                                                        class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                                    >
                                                        1 hour
                                                    </button>
                                                    <button
                                                        wire:click.stop="snooze({{ $notification->id }}, '4_hours')"
                                                        @click="open = false; snoozeOpen = false"
                                                        class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                                    >
                                                        4 hours
                                                    </button>
                                                    <button
                                                        wire:click.stop="snooze({{ $notification->id }}, 'tomorrow')"
                                                        @click="open = false; snoozeOpen = false"
                                                        class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                                    >
                                                        Tomorrow morning
                                                    </button>
                                                    <button
                                                        wire:click.stop="snooze({{ $notification->id }}, 'next_monday')"
                                                        @click="open = false; snoozeOpen = false"
                                                        class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                                    >
                                                        Next Monday
                                                    </button>
                                                </div>
                                            </div>

                                            <button
                                                wire:click.stop="resolve({{ $notification->id }})"
                                                @click="open = false"
                                                class="w-full px-3 py-2 text-left text-sm text-green-700 hover:bg-green-50 flex items-center gap-2"
                                            >
                                                <x-icon name="check-circle" class="w-4 h-4" />
                                                Mark as resolved
                                            </button>

                                            <button
                                                wire:click.stop="dismiss({{ $notification->id }})"
                                                @click="open = false"
                                                class="w-full px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2"
                                            >
                                                <x-icon name="x-circle" class="w-4 h-4" />
                                                Dismiss
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
