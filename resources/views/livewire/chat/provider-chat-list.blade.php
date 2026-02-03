@php
    $terminology = app(\App\Services\TerminologyService::class);
    $providerTypeLabels = [
        'coach' => $terminology->get('provider_type_coach_label'),
        'mentor' => $terminology->get('provider_type_mentor_label'),
        'specialist' => $terminology->get('provider_type_specialist_label'),
        'consultant' => $terminology->get('provider_type_consultant_label'),
        'advisor' => $terminology->get('provider_type_advisor_label'),
        'organization' => $terminology->get('provider_type_organization_label'),
        'section' => $terminology->get('provider_type_section_label'),
        'learning_group' => $terminology->get('provider_type_learning_group_label'),
    ];
@endphp

<div class="flex h-[calc(100vh-8rem)]" x-data="{ showSidebar: window.innerWidth >= 768 || !{{ $selectedConversationId ? 'true' : 'false' }} }" x-on:resize.window="showSidebar = window.innerWidth >= 768 || !{{ $selectedConversationId ? 'true' : 'false' }}">
    <!-- Conversations Sidebar -->
    <div
        class="bg-white border-r border-gray-200 flex flex-col transition-all duration-200"
        :class="showSidebar ? 'w-full md:w-72 lg:w-80' : 'w-0 overflow-hidden md:w-72 lg:w-80'"
    >
        <!-- Header -->
        <div class="p-3 md:p-4 border-b border-gray-200">
            <div class="flex items-center justify-between mb-3 md:mb-4">
                <h2 class="text-base md:text-lg font-semibold text-gray-900">@term('messages_label')</h2>
                <div class="flex items-center gap-1.5 md:gap-2">
                    @if($unreadCount > 0)
                    <span class="px-1.5 md:px-2 py-0.5 md:py-1 bg-pulse-orange-100 text-pulse-orange-600 text-xs font-medium rounded-full">
                        {{ $unreadCount }} @term('unread_label')
                    </span>
                    @endif
                    @if($useDemoData)
                    <span class="px-1.5 md:px-2 py-0.5 md:py-1 bg-purple-100 text-purple-600 text-xs font-medium rounded-full">
                        @term('demo_label')
                    </span>
                    @endif
                </div>
            </div>

            <!-- Search -->
            <div class="relative">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ $terminology->get('search_conversations_placeholder') }}"
                    class="w-full pl-9 md:pl-10 pr-3 md:pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
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
                wire:click="selectConversation('{{ $conv->id }}')"
                @click="showSidebar = window.innerWidth >= 768"
                class="w-full flex items-start gap-2 md:gap-3 p-3 md:p-4 text-left hover:bg-gray-50 transition-colors border-b border-gray-100 {{ $selectedConversationId === $conv->id ? 'bg-pulse-orange-50 border-l-4 border-l-pulse-orange-500' : '' }}"
            >
                <!-- Avatar -->
                <div class="flex-shrink-0 relative">
                    @if($conv->provider->thumbnail_url)
                    <img src="{{ $conv->provider->thumbnail_url }}" alt="" class="w-10 h-10 md:w-12 md:h-12 rounded-full object-cover">
                    @else
                    <div class="w-10 h-10 md:w-12 md:h-12 rounded-full bg-gradient-to-br from-pulse-orange-400 to-pulse-orange-600 flex items-center justify-center">
                        <span class="text-white font-semibold text-base md:text-lg">{{ substr($conv->provider->name, 0, 1) }}</span>
                    </div>
                    @endif
                    @if($conv->provider->online ?? false)
                    <span class="absolute bottom-0 right-0 w-2.5 h-2.5 md:w-3 md:h-3 bg-green-500 border-2 border-white rounded-full"></span>
                    @endif
                </div>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between gap-2">
                        <h3 class="text-sm font-semibold text-gray-900 truncate">{{ $conv->provider->name }}</h3>
                        @if($conv->last_message_at)
                        <span class="text-xs text-gray-500 flex-shrink-0">{{ $conv->last_message_at->diffForHumans(null, true) }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-1 text-xs text-gray-500 mb-1">
                        <span>{{ $providerTypeLabels[$conv->provider->provider_type] ?? ucfirst($conv->provider->provider_type) }}</span>
                        @if($conv->provider->verified ?? false)
                        <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                        </svg>
                        @endif
                    </div>
                    @if($conv->participant)
                    <p class="text-xs text-blue-600 mb-1 truncate">@term('re_label') {{ $conv->participant->name }}</p>
                    @endif
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
            <div class="p-6 md:p-8 text-center">
                <svg class="w-10 h-10 md:w-12 md:h-12 mx-auto text-gray-300 mb-3 md:mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <p class="text-gray-500 text-sm">@term('no_conversations_yet_label')</p>
                <p class="text-gray-400 text-xs mt-1">@term('start_conversation_help_label')</p>
            </div>
            @endforelse
        </div>

        <!-- New Conversation Section -->
        @if($availableProviders->count() > 0)
        <div class="p-4 border-t border-gray-200 bg-gray-50">
            <p class="text-xs font-semibold text-gray-500 uppercase mb-2">@term('start_new_conversation_label')</p>
            <div class="space-y-2 max-h-32 overflow-y-auto">
                @foreach($availableProviders as $provider)
                <button
                    wire:click="startConversation('{{ $provider->id }}')"
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
                        <p class="text-xs text-gray-500">{{ $providerTypeLabels[$provider->provider_type] ?? ucfirst($provider->provider_type) }}</p>
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
    <div class="flex-1 flex flex-col" :class="showSidebar ? 'hidden md:flex' : 'flex'">
        <!-- Mobile Back Button -->
        <div class="md:hidden p-2 bg-white border-b border-gray-200" x-show="!showSidebar">
            <button @click="showSidebar = true" class="flex items-center gap-2 px-3 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                @term('back_to_conversations_label')
            </button>
        </div>
        <div class="flex-1">
            @livewire('chat.provider-chat-window', ['conversationId' => $selectedConversationId, 'isDemo' => $useDemoData])
        </div>
    </div>
</div>
