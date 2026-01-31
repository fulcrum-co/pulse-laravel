<div class="flex flex-col h-full bg-gray-50" x-data="{ showAttachments: false }">
    @if($conversation)
    <!-- Chat Header -->
    <div class="flex items-center justify-between px-3 sm:px-6 py-3 sm:py-4 bg-white border-b border-gray-200">
        <div class="flex items-center gap-2 sm:gap-4 min-w-0 flex-1">
            <!-- Provider Avatar -->
            <div class="relative flex-shrink-0">
                @if($conversation->provider->thumbnail_url)
                <img src="{{ $conversation->provider->thumbnail_url }}" alt="" class="w-10 h-10 sm:w-12 sm:h-12 rounded-full object-cover">
                @else
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full bg-gradient-to-br from-pulse-orange-400 to-pulse-orange-600 flex items-center justify-center">
                    <span class="text-white font-semibold text-base sm:text-lg">{{ substr($conversation->provider->name, 0, 1) }}</span>
                </div>
                @endif
                @if($conversation->provider->online ?? false)
                <span class="absolute bottom-0 right-0 w-2.5 h-2.5 sm:w-3 sm:h-3 bg-green-500 border-2 border-white rounded-full"></span>
                @endif
            </div>

            <!-- Provider Info -->
            <div class="min-w-0 flex-1">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900 truncate">{{ $conversation->provider->display_name }}</h2>
                <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-500">
                    <span>{{ ucfirst($conversation->provider->provider_type) }}</span>
                    @if($conversation->provider->verified ?? false)
                    <span class="hidden sm:flex items-center gap-1 text-green-600">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        Verified
                    </span>
                    @endif
                    @if($conversation->provider->online ?? false)
                    <span class="flex items-center gap-1 text-green-600">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        <span class="hidden sm:inline">Online</span>
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
            <!-- Video Call Button -->
            <button
                wire:click="startVideoCall"
                class="inline-flex items-center p-2 sm:px-4 sm:py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition-colors"
                title="Start video call"
            >
                <svg class="w-4 h-4 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                </svg>
                <span class="hidden sm:inline">Video Call</span>
            </button>

            <!-- Book Session Button -->
            <button
                wire:click="openBookingModal"
                class="hidden sm:inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
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
                    <!-- Book Session (shown on mobile) -->
                    <button wire:click="openBookingModal" @click="open = false" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 sm:hidden">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Book Session
                    </button>
                    <button @click="open = false" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        View Profile
                    </button>
                    <button wire:click="$parent.archiveConversation('{{ $conversation->id }}')" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
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
                Regarding: {{ $conversation->student->full_name ?? $conversation->student->name }}
            </span>
        </div>
        @endif

        @if($isDemo)
        <div class="flex justify-center mb-4">
            <span class="px-3 py-1 bg-purple-100 text-purple-700 text-xs rounded-full flex items-center gap-1">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
                Demo Conversation - Try sending a message or starting a video call
            </span>
        </div>
        @endif

        @forelse($messages as $message)
        @php
            $isCurrentUser = $this->isCurrentUserMessage($message);
            $isSystem = ($message['is_system'] ?? false) || ($message['user']['id'] ?? '') === 'system';
        @endphp

        @if($isSystem)
        <div class="flex justify-center">
            <span class="px-3 py-1 bg-gray-100 text-gray-600 text-xs rounded-full">
                {{ $message['text'] ?? '' }}
            </span>
        </div>
        @else
        <div class="flex {{ $isCurrentUser ? 'justify-end' : 'justify-start' }}">
            <div class="flex items-end gap-2 max-w-lg {{ $isCurrentUser ? 'flex-row-reverse' : '' }}">
                <!-- Avatar -->
                @if(!$isCurrentUser)
                <div class="flex-shrink-0">
                    @if($conversation->provider->thumbnail_url)
                    <img src="{{ $conversation->provider->thumbnail_url }}" alt="" class="w-8 h-8 rounded-full object-cover">
                    @else
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                        <span class="text-gray-600 text-xs font-medium">{{ substr($message['user']['name'] ?? 'P', 0, 1) }}</span>
                    </div>
                    @endif
                </div>
                @endif

                <!-- Message Bubble -->
                <div class="{{ $isCurrentUser ? 'bg-pulse-orange-500 text-white' : 'bg-white border border-gray-200' }} px-4 py-3 rounded-2xl {{ $isCurrentUser ? 'rounded-br-md' : 'rounded-bl-md' }} shadow-sm">
                    <p class="text-sm whitespace-pre-wrap">{{ $message['text'] ?? '' }}</p>
                    <p class="text-xs {{ $isCurrentUser ? 'text-orange-200' : 'text-gray-400' }} mt-1">
                        {{ $this->formatMessageTime($message['created_at'] ?? now()) }}
                    </p>
                </div>
            </div>
        </div>
        @endif
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
    <div class="px-3 sm:px-6 py-3 sm:py-4 bg-white border-t border-gray-200">
        <form wire:submit="sendMessage" class="flex items-end gap-2 sm:gap-3">
            <!-- Attachment button -->
            <button
                type="button"
                @click="showAttachments = !showAttachments"
                class="flex-shrink-0 p-2 sm:p-3 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl transition-colors"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                </svg>
            </button>

            <div class="flex-1 min-w-0">
                <textarea
                    wire:model="messageText"
                    placeholder="Type your message..."
                    rows="1"
                    class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm border border-gray-200 rounded-xl resize-none focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                    @keydown.enter.prevent="if (!event.shiftKey) { $wire.sendMessage(); }"
                    x-data
                    x-init="$el.style.height = 'auto'; $el.style.height = $el.scrollHeight + 'px'"
                    @input="$el.style.height = 'auto'; $el.style.height = Math.min($el.scrollHeight, 120) + 'px'"
                ></textarea>
            </div>

            <button
                type="submit"
                class="flex-shrink-0 px-4 sm:px-6 py-2.5 sm:py-3 bg-pulse-orange-500 text-white rounded-xl font-medium hover:bg-pulse-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove>Send</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
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

    <!-- Booking Modal -->
    @if($showBookingModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" x-data x-on:keydown.escape.window="$wire.closeBookingModal()">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-hidden">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">Book a Session</h2>
                    <p class="text-sm text-gray-500">with {{ $conversation->provider->name }}</p>
                </div>
                <button wire:click="closeBookingModal" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="flex flex-col lg:flex-row">
                <!-- Calendar Section -->
                <div class="flex-1 p-6 border-b lg:border-b-0 lg:border-r border-gray-200">
                    <!-- Month Navigation -->
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $this->monthName }}</h3>
                        <div class="flex items-center gap-2">
                            <button wire:click="previousMonth" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                            </button>
                            <button wire:click="nextMonth" class="p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-1 mb-2">
                        @foreach(['SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT'] as $dayName)
                        <div class="text-center text-xs font-medium text-gray-500 py-2">{{ $dayName }}</div>
                        @endforeach
                    </div>
                    <div class="grid grid-cols-7 gap-1">
                        @foreach($this->calendarDays as $day)
                            @if($day === null)
                                <div class="h-10"></div>
                            @else
                                <button
                                    wire:click="selectDate('{{ $day['date'] }}')"
                                    @if(!$day['isAvailable']) disabled @endif
                                    class="h-10 w-full flex items-center justify-center rounded-lg text-sm font-medium transition-colors
                                        {{ $selectedDate === $day['date'] ? 'bg-pulse-orange-500 text-white' : '' }}
                                        {{ $day['isToday'] && $selectedDate !== $day['date'] ? 'ring-2 ring-pulse-orange-300' : '' }}
                                        {{ $day['isAvailable'] && $selectedDate !== $day['date'] ? 'hover:bg-gray-100 text-gray-900' : '' }}
                                        {{ !$day['isAvailable'] ? 'text-gray-300 cursor-not-allowed' : 'cursor-pointer' }}
                                    "
                                >
                                    {{ $day['day'] }}
                                </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                <!-- Time Slots Section -->
                <div class="w-full lg:w-72 p-6">
                    @if($selectedDate)
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-semibold text-gray-900">
                            {{ \Carbon\Carbon::parse($selectedDate)->format('D, M j') }}
                        </h3>
                        <div class="flex items-center gap-1 text-xs">
                            <button class="px-2 py-1 rounded bg-gray-100 text-gray-700">12h</button>
                            <button class="px-2 py-1 rounded text-gray-400 hover:bg-gray-50">24h</button>
                        </div>
                    </div>
                    <div class="space-y-2 max-h-64 overflow-y-auto">
                        @foreach($this->availableTimes as $time)
                        <button
                            wire:click="selectTime('{{ $time }}')"
                            class="w-full flex items-center gap-3 px-4 py-3 border rounded-lg text-left transition-colors
                                {{ $selectedTime === $time ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300 hover:bg-gray-50' }}
                            "
                        >
                            <span class="w-2 h-2 rounded-full bg-green-500"></span>
                            <span class="font-medium text-gray-900">{{ $time }}</span>
                        </button>
                        @endforeach
                    </div>
                    @else
                    <div class="flex flex-col items-center justify-center h-full text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-gray-500 text-sm">Select a date to see<br>available times</p>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    @if($selectedDate && $selectedTime)
                    <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($selectedDate)->format('F j, Y') }}</span>
                    at <span class="font-medium text-gray-900">{{ $selectedTime }}</span>
                    @else
                    Select a date and time
                    @endif
                </div>
                <div class="flex items-center gap-3">
                    <button wire:click="closeBookingModal" class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                        Cancel
                    </button>
                    <button
                        wire:click="confirmBooking"
                        @if(!$selectedDate || !$selectedTime) disabled @endif
                        class="px-6 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Confirm Booking
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Video Call Modal -->
    @if($showVideoModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/75" x-data="videoCall()" x-init="init()">
        <div class="relative w-full max-w-4xl h-[80vh] bg-gray-900 rounded-2xl overflow-hidden shadow-2xl">
            <!-- Video Container -->
            <div class="relative h-full">
                <!-- Remote Video (Full Screen) -->
                <div class="absolute inset-0 bg-gradient-to-br from-gray-800 to-gray-900">
                    @if($videoCallState === 'connecting')
                    <div class="flex flex-col items-center justify-center h-full">
                        <div class="w-32 h-32 rounded-full bg-gradient-to-br from-pulse-orange-400 to-pulse-orange-600 flex items-center justify-center mb-6 animate-pulse">
                            @if($conversation->provider->thumbnail_url)
                            <img src="{{ $conversation->provider->thumbnail_url }}" alt="" class="w-28 h-28 rounded-full object-cover">
                            @else
                            <span class="text-white text-4xl font-bold">{{ substr($conversation->provider->name, 0, 1) }}</span>
                            @endif
                        </div>
                        <h3 class="text-white text-xl font-semibold mb-2">{{ $conversation->provider->name }}</h3>
                        <p class="text-gray-400 flex items-center gap-2">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Connecting...
                        </p>
                    </div>
                    @elseif($videoCallState === 'connected')
                    <div class="flex flex-col items-center justify-center h-full">
                        <!-- Simulated video feed -->
                        <div class="relative">
                            @if($conversation->provider->thumbnail_url)
                            <img src="{{ $conversation->provider->thumbnail_url }}" alt="" class="w-48 h-48 rounded-full object-cover ring-4 ring-green-500 ring-offset-4 ring-offset-gray-900">
                            @else
                            <div class="w-48 h-48 rounded-full bg-gradient-to-br from-pulse-orange-400 to-pulse-orange-600 flex items-center justify-center ring-4 ring-green-500 ring-offset-4 ring-offset-gray-900">
                                <span class="text-white text-6xl font-bold">{{ substr($conversation->provider->name, 0, 1) }}</span>
                            </div>
                            @endif
                            <span class="absolute bottom-2 right-2 w-4 h-4 bg-green-500 rounded-full animate-pulse"></span>
                        </div>
                        <h3 class="text-white text-xl font-semibold mt-6">{{ $conversation->provider->name }}</h3>
                        <p class="text-green-400 flex items-center gap-2 mt-2">
                            <span class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></span>
                            Connected
                        </p>
                        <p class="text-gray-400 text-sm mt-1" x-text="callDuration"></p>
                    </div>
                    @elseif($videoCallState === 'ended')
                    <div class="flex flex-col items-center justify-center h-full">
                        <div class="w-20 h-20 rounded-full bg-red-500/20 flex items-center justify-center mb-4">
                            <svg class="w-10 h-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"></path>
                            </svg>
                        </div>
                        <h3 class="text-white text-xl font-semibold">Call Ended</h3>
                        <p class="text-gray-400 mt-2" x-text="'Duration: ' + callDuration"></p>
                    </div>
                    @endif
                </div>

                <!-- Local Video (Picture in Picture) -->
                @if($videoCallState === 'connected')
                <div class="absolute bottom-24 right-6 w-48 h-36 bg-gray-800 rounded-xl overflow-hidden shadow-lg border-2 border-gray-700">
                    <video id="local-video" autoplay muted playsinline class="w-full h-full object-cover" x-show="!cameraOff"></video>
                    <div x-show="cameraOff" class="w-full h-full flex items-center justify-center bg-gradient-to-br from-gray-700 to-gray-800">
                        <svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                </div>
                @endif

                <!-- Call Controls -->
                <div class="absolute bottom-0 left-0 right-0 p-6 bg-gradient-to-t from-black/80 to-transparent">
                    <div class="flex items-center justify-center gap-4">
                        <!-- Mute -->
                        <button
                            @click="toggleMute()"
                            :class="isMuted ? 'bg-red-500 hover:bg-red-600' : 'bg-gray-700 hover:bg-gray-600'"
                            class="p-4 rounded-full transition-colors"
                        >
                            <svg x-show="!isMuted" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"></path>
                            </svg>
                            <svg x-show="isMuted" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z" clip-rule="evenodd"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"></path>
                            </svg>
                        </button>

                        <!-- Camera Toggle -->
                        <button
                            @click="toggleCamera()"
                            :class="cameraOff ? 'bg-red-500 hover:bg-red-600' : 'bg-gray-700 hover:bg-gray-600'"
                            class="p-4 rounded-full transition-colors"
                        >
                            <svg x-show="!cameraOff" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <svg x-show="cameraOff" class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                            </svg>
                        </button>

                        <!-- End Call -->
                        <button
                            wire:click="endVideoCall"
                            class="p-4 bg-red-600 hover:bg-red-700 rounded-full transition-colors"
                        >
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M5 3a2 2 0 00-2 2v1c0 8.284 6.716 15 15 15h1a2 2 0 002-2v-3.28a1 1 0 00-.684-.948l-4.493-1.498a1 1 0 00-1.21.502l-1.13 2.257a11.042 11.042 0 01-5.516-5.517l2.257-1.128a1 1 0 00.502-1.21L9.228 3.683A1 1 0 008.279 3H5z"></path>
                            </svg>
                        </button>

                        <!-- Screen Share -->
                        <button
                            @click="toggleScreenShare()"
                            :class="isScreenSharing ? 'bg-green-500 hover:bg-green-600' : 'bg-gray-700 hover:bg-gray-600'"
                            class="p-4 rounded-full transition-colors"
                        >
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </button>

                        <!-- Chat Toggle -->
                        <button
                            class="p-4 bg-gray-700 hover:bg-gray-600 rounded-full transition-colors"
                        >
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Close Button -->
                <button
                    wire:click="closeVideoModal"
                    class="absolute top-4 right-4 p-2 bg-gray-800/80 hover:bg-gray-700 rounded-full transition-colors"
                >
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
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

        // Listen for video call started event to auto-connect in demo mode
        Livewire.on('video-call-started', (data) => {
            if (data && data.autoConnect) {
                // Auto-connect after 2 seconds in demo mode
                setTimeout(() => {
                    Livewire.dispatch('connectCall');
                    // Also trigger the component method directly
                    const component = Livewire.find(document.querySelector('[wire\\:id]')?.getAttribute('wire:id'));
                    if (component) {
                        component.call('connectCall');
                    }
                }, 2000);
            }
        });
    });

    // Video call Alpine component
    function videoCall() {
        return {
            isMuted: false,
            cameraOff: false,
            isScreenSharing: false,
            callDuration: '0:00',
            callStart: null,
            interval: null,
            localStream: null,

            init() {
                this.callStart = new Date();
                this.interval = setInterval(() => {
                    const now = new Date();
                    const diff = Math.floor((now - this.callStart) / 1000);
                    const minutes = Math.floor(diff / 60);
                    const seconds = diff % 60;
                    this.callDuration = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                }, 1000);

                // Request camera access for local preview
                this.startLocalVideo();
            },

            async startLocalVideo() {
                try {
                    this.localStream = await navigator.mediaDevices.getUserMedia({
                        video: true,
                        audio: true
                    });
                    const localVideo = document.getElementById('local-video');
                    if (localVideo) {
                        localVideo.srcObject = this.localStream;
                    }
                } catch (err) {
                    console.log('Camera access denied or not available:', err);
                }
            },

            toggleMute() {
                this.isMuted = !this.isMuted;
                if (this.localStream) {
                    this.localStream.getAudioTracks().forEach(track => {
                        track.enabled = !this.isMuted;
                    });
                }
            },

            toggleCamera() {
                this.cameraOff = !this.cameraOff;
                if (this.localStream) {
                    this.localStream.getVideoTracks().forEach(track => {
                        track.enabled = !this.cameraOff;
                    });
                }
            },

            toggleScreenShare() {
                this.isScreenSharing = !this.isScreenSharing;
            },

            destroy() {
                if (this.interval) {
                    clearInterval(this.interval);
                }
                if (this.localStream) {
                    this.localStream.getTracks().forEach(track => track.stop());
                }
            }
        }
    }
</script>
@endpush
