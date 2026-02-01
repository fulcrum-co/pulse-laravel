<div class="flex items-center gap-1">
    <!-- Messages -->
    <a href="{{ route('messages.index') }}"
       class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
       title="Messages">
        <x-icon name="chat-bubble-oval-left" class="w-5 h-5" />
        @if($unreadMessages > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-4 h-4 px-1 text-xs font-medium text-white bg-pulse-orange-500 rounded-full flex items-center justify-center">
                {{ $unreadMessages > 99 ? '99+' : $unreadMessages }}
            </span>
        @endif
    </a>

    <!-- Alerts -->
    <a href="{{ route('alerts.index') }}"
       class="relative p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
       title="Alerts">
        <x-icon name="bell" class="w-5 h-5" />
        @if($unreadAlerts > 0)
            <span class="absolute -top-0.5 -right-0.5 min-w-4 h-4 px-1 text-xs font-medium text-white bg-pulse-orange-500 rounded-full flex items-center justify-center">
                {{ $unreadAlerts > 99 ? '99+' : $unreadAlerts }}
            </span>
        @endif
    </a>
</div>
