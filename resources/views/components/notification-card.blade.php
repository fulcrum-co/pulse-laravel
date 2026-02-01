@props([
    'notification',
    'selectable' => false,
    'selected' => false,
])

@php
    $categoryInfo = $notification->category_info;
    $isUnread = $notification->isUnread();
    $isHighPriority = $notification->isHighPriority();

    $colorMap = [
        'blue' => 'bg-blue-100 text-blue-600',
        'purple' => 'bg-purple-100 text-purple-600',
        'green' => 'bg-green-100 text-green-600',
        'orange' => 'bg-orange-100 text-orange-600',
        'teal' => 'bg-teal-100 text-teal-600',
        'gray' => 'bg-gray-100 text-gray-600',
    ];
    $iconColor = $colorMap[$categoryInfo['color']] ?? 'bg-gray-100 text-gray-600';

    $priorityColors = [
        'high' => 'bg-amber-100 text-amber-700',
        'urgent' => 'bg-red-100 text-red-700',
    ];
@endphp

<div
    class="relative flex items-start gap-3 p-4 bg-white border border-gray-200 rounded-lg transition-all {{ $isUnread ? 'ring-1 ring-pulse-orange-200 bg-pulse-orange-50/30' : '' }} {{ $selected ? 'ring-2 ring-pulse-orange-500' : '' }} hover:shadow-sm"
    wire:click="markAsRead({{ $notification->id }})"
>
    <!-- Selection Checkbox -->
    @if($selectable)
        <div class="flex-shrink-0 pt-0.5">
            <input
                type="checkbox"
                wire:click.stop="toggleSelect({{ $notification->id }})"
                @checked($selected)
                class="h-4 w-4 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500 cursor-pointer"
            />
        </div>
    @endif

    <!-- Unread Indicator -->
    @if($isUnread)
        <div class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-pulse-orange-500 rounded-r"></div>
    @endif

    <!-- Category Icon -->
    <div class="flex-shrink-0 w-9 h-9 rounded-lg {{ $iconColor }} flex items-center justify-center">
        <x-icon :name="$notification->display_icon" class="w-5 h-5" />
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <!-- Title + Priority Badge -->
                <div class="flex items-center gap-2 flex-wrap">
                    <h4 class="text-sm {{ $isUnread ? 'font-semibold text-gray-900' : 'font-medium text-gray-700' }} truncate">
                        {{ $notification->title }}
                    </h4>
                    @if($isHighPriority)
                        <span class="px-1.5 py-0.5 text-xs font-medium rounded {{ $priorityColors[$notification->priority] ?? '' }}">
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
                        <span class="w-1.5 h-1.5 rounded-full bg-{{ $categoryInfo['color'] }}-400"></span>
                        {{ $categoryInfo['label'] }}
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
                        @if($isUnread)
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
