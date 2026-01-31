<div class="flex h-[calc(100vh-8rem)]">
    <!-- Conversations Sidebar -->
    <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
        <!-- Header -->
        <div class="p-4 border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">Messages</h2>
                @if($unreadCount > 0)
                <span class="px-2 py-1 bg-pulse-orange-100 text-pulse-orange-600 text-xs font-medium rounded-full">
                    {{ $unreadCount }} unread
                </span>
                @endif
            </div>

            <!-- Search -->
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search conversations..."
                    class="w-full pl-10 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                >
                <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </div>
        </div>

        <!-- Conversations List -->
        <div class="flex-1 overflow-y-auto">
            @forelse($conversations as $conv)
            <button
                wire:click="selectConversation({{ $conv->id }})"
                class="w-full flex items-start gap-3 p-4 text-left hover:bg-gray-50 transition-colors border-b border-gray-100 {{ $selectedConversationId === $conv->id ? 'bg-pulse-orange-50 border-l-4 border-l-pulse-orange-500' : '' }}"
            >
                <!-- Avatar -->
                <div class="flex-shrink-0">
                    @if($conv->provider->thumbnail_url)
                    <img src="{{ $conv->provider->thumbnail_url }}" alt="" class="w-12 h-12 rounded-full object-cover">
                    @else
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-pulse-orange-400 to-pulse-orange-600 flex items-center justify-center">
                        <span class="text-white font-semibold text-lg">{{ substr($conv->provider->name, 0, 1) }}</span>
                    </div>
                    @endif
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $conv->provider->name }}</h3>
                        @if($conv->last_message_at)
                        <span class="text-xs text-gray-500">{{ $conv->last_message_at->diffForHumans(null, true) }}</span>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mb-1">{{ ucfirst($conv->provider->provider_type) }}</p>
                    @if($conv->last_message_preview)
                    <p class="text-sm text-gray-600 truncate">{{ $conv->last_message_preview }}</p>
                    @endif
                </div>

                <!-- Unread indicator -->
                @if($conv->unread_count_initiator > 0)
                <span class="flex-shrink-0 w-5 h-5 bg-pulse-orange-500 text-white text-xs font-medium rounded-full flex items-center justify-center">
                    {{ $conv->unread_count_initiator }}
                </span>
                @endif
            </button>
            @empty
            <div class="p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <p class="text-gray-500 text-sm">No conversations yet</p>
                <p class="text-gray-400 text-xs mt-1">Start a conversation with a provider below</p>
            </div>
            @endforelse
        </div>

        <!-- New Conversation Section -->
        @if($availableProviders->count() > 0)
        <div class="p-4 border-t border-gray-200 bg-gray-50">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Start New Conversation</p>
            <div class="space-y-2 max-h-32 overflow-y-auto">
                @foreach($availableProviders as $provider)
                <button
                    wire:click="startConversation({{ $provider->id }})"
                    class="w-full flex items-center gap-2 p-2 text-left text-sm rounded-lg hover:bg-white transition-colors"
                >
                    @if($provider->thumbnail_url)
                    <img src="{{ $provider->thumbnail_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                    @else
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-600 text-xs font-medium">{{ substr($provider->name, 0, 1) }}</span>
                    </div>
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="font-medium text-gray-900 truncate">{{ $provider->name }}</p>
                        <p class="text-xs text-gray-500">{{ ucfirst($provider->provider_type) }}</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                </button>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Chat Window -->
    <div class="flex-1">
        @livewire('chat.provider-chat-window', ['conversationId' => $selectedConversationId])
    </div>
</div>
