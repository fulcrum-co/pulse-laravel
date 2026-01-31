<div class="flex flex-col h-full bg-gray-50">
    @if($conversation)
    <!-- Chat Header -->
    <div class="flex items-center justify-between px-6 py-4 bg-white border-b border-gray-200">
        <div class="flex items-center gap-4">
            <!-- Provider Avatar -->
            @if($conversation->provider->thumbnail_url)
            <img src="{{ $conversation->provider->thumbnail_url }}" alt="" class="w-12 h-12 rounded-full object-cover">
            @else
            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-pulse-orange-400 to-pulse-orange-600 flex items-center justify-center">
                <span class="text-white font-semibold text-lg">{{ substr($conversation->provider->name, 0, 1) }}</span>
            </div>
            @endif

            <!-- Provider Info -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900">{{ $conversation->provider->display_name }}</h2>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <span>{{ ucfirst($conversation->provider->provider_type) }}</span>
                    @if($conversation->provider->isVerified())
                    <span class="flex items-center gap-1 text-green-600">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Verified
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-2">
            <!-- Book Session Button -->
            <button
                wire:click="openBookingModal"
                class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Book Session
            </button>

            <!-- More Options -->
            <div x-data="{ open: false }" class="relative">
                <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                    </svg>
                </button>

                <div
                    x-show="open"
                    @click.away="open = false"
                    x-transition
                    class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10"
                >
                    <a href="{{ route('resources.providers.show', $conversation->provider_id) }}" class="flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        View Profile
                    </a>
                    <button wire:click="$parent.archiveConversation({{ $conversation->id }})" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path>
                        </svg>
                        Archive
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages Area -->
    <div class="flex-1 overflow-y-auto p-6 space-y-4" id="messages-container">
        @if($conversation->student)
        <div class="flex justify-center">
            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded-full">
                Regarding: {{ $conversation->student->full_name }}
            </span>
        </div>
        @endif

        @forelse($messages as $message)
        @php
            $isCurrentUser = str_starts_with($message['user']['id'] ?? '', 'user_' . auth()->id());
            $isProvider = str_starts_with($message['user']['id'] ?? '', 'provider_');
        @endphp

        <div class="flex {{ $isCurrentUser ? 'justify-end' : 'justify-start' }}">
            <div class="flex items-end gap-2 max-w-lg {{ $isCurrentUser ? 'flex-row-reverse' : '' }}">
                <!-- Avatar -->
                @if(!$isCurrentUser)
                <div class="flex-shrink-0">
                    @if($isProvider && $conversation->provider->thumbnail_url)
                    <img src="{{ $conversation->provider->thumbnail_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                    @else
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-600 text-xs font-medium">{{ substr($message['user']['name'] ?? 'P', 0, 1) }}</span>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Message Bubble -->
                <div class="{{ $isCurrentUser ? 'bg-pulse-orange-500 text-white' : 'bg-white border border-gray-200' }} px-4 py-3 rounded-2xl {{ $isCurrentUser ? 'rounded-br-md' : 'rounded-bl-md' }}">
                    <p class="text-sm whitespace-pre-wrap">{{ $message['text'] ?? '' }}</p>
                    <p class="text-xs {{ $isCurrentUser ? 'text-orange-200' : 'text-gray-400' }} mt-1">
                        {{ $this->formatMessageTime($message['created_at'] ?? now()) }}
                    </p>
                </div>
            </div>
        </div>
        @empty
        <div class="flex flex-col items-center justify-center h-full text-center">
            <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">Start the conversation</h3>
            <p class="text-sm text-gray-500">Send a message to {{ $conversation->provider->name }}</p>
        </div>
        @endforelse
    </div>

    <!-- Message Input -->
    <div class="px-6 py-4 bg-white border-t border-gray-200">
        <form wire:submit="sendMessage" class="flex items-end gap-3">
            <div class="flex-1">
                <textarea
                    wire:model="messageText"
                    placeholder="Type your message..."
                    rows="1"
                    class="w-full px-4 py-3 text-sm border border-gray-200 rounded-xl resize-none focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                    @keydown.enter.prevent="if (!event.shiftKey) { $wire.sendMessage(); }"
                    x-data
                    x-init="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                    @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'"
                ></textarea>
            </div>

            <button
                type="submit"
                class="flex-shrink-0 px-6 py-3 bg-pulse-orange-500 text-white rounded-xl font-medium hover:bg-pulse-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>Send</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Sending
                </span>
            </button>
        </form>
    </div>

    @else
    <!-- Empty State -->
    <div class="flex flex-col items-center justify-center h-full text-center p-8">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-6">
            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
            </svg>
        </div>
        <h2 class="text-xl font-semibold text-gray-900 mb-2">Select a conversation</h2>
        <p class="text-gray-500 max-w-sm">
            Choose a conversation from the list or start a new one by selecting a provider.
        </p>
    </div>
    @endif
</div>

@push('scripts')
<script>
    // Auto-scroll to bottom when new messages arrive
    document.addEventListener('livewire:initialized', () => {
        const container = document.getElementById('messages-container');
        if (container) {
            container.scrollTop = container.scrollHeight;
        }

        Livewire.hook('morph.updated', ({ el }) => {
            if (el.id === 'messages-container') {
                el.scrollTop = el.scrollHeight;
            }
        });
    });
</script>
@endpush
