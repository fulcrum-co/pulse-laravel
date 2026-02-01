{{-- Toast Notifications Component --}}
{{-- Displays real-time notification popups when new notifications arrive via WebSocket --}}
{{-- JavaScript logic is in resources/js/toast-notifications.js (loaded via Vite bundle) --}}
@auth
@php
    $toastPrefs = auth()->user()->notification_preferences['toast'] ?? ['enabled' => true, 'priority_threshold' => 'low'];
@endphp
<div
    x-data="toastNotifications({{ auth()->id() }}, {{ Js::from($toastPrefs) }})"
    class="fixed bottom-4 right-4 z-50 space-y-3 max-w-sm w-full pointer-events-none"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transform ease-out duration-300"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transform ease-in duration-200"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="bg-white border border-gray-200 rounded-lg shadow-lg p-4 cursor-pointer pointer-events-auto hover:shadow-xl transition-shadow"
            @click="handleClick(toast)"
        >
            <div class="flex items-start gap-3">
                {{-- Priority-colored icon container --}}
                <div
                    class="flex-shrink-0 w-10 h-10 rounded-lg flex items-center justify-center"
                    :class="getPriorityClasses(toast.priority)"
                >
                    <template x-if="toast.icon">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <template x-if="toast.icon === 'bell' || toast.icon === 'bell-alert'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </template>
                            <template x-if="toast.icon === 'bolt'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </template>
                            <template x-if="toast.icon === 'clipboard-document-list'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </template>
                            <template x-if="toast.icon === 'chart-bar' || toast.icon === 'document-chart-bar'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </template>
                            <template x-if="toast.icon === 'academic-cap'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z M12 14l9-5-9-5-9 5 9 5zm0 0v6"></path>
                            </template>
                            <template x-if="toast.icon === 'circle-stack'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                            </template>
                            <template x-if="toast.icon === 'cog-6-tooth' || toast.icon === 'cog'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </template>
                            <template x-if="toast.icon === 'megaphone'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"></path>
                            </template>
                            <template x-if="toast.icon === 'exclamation-triangle'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                            </template>
                            <template x-if="toast.icon === 'check-circle'">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </template>
                            <template x-if="!['bell', 'bell-alert', 'bolt', 'clipboard-document-list', 'chart-bar', 'document-chart-bar', 'academic-cap', 'circle-stack', 'cog-6-tooth', 'cog', 'megaphone', 'exclamation-triangle', 'check-circle'].includes(toast.icon)">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </template>
                        </svg>
                    </template>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate" x-text="toast.title"></p>
                    <p class="text-sm text-gray-500 line-clamp-2 mt-0.5" x-text="toast.body"></p>
                    <p class="text-xs text-gray-400 mt-1" x-text="formatTime(toast.created_at)"></p>
                </div>

                {{-- Dismiss button --}}
                <button
                    @click.stop="dismiss(toast)"
                    class="flex-shrink-0 text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            {{-- Progress bar for auto-dismiss --}}
            <div class="mt-3 h-1 bg-gray-100 rounded-full overflow-hidden">
                <div
                    class="h-full transition-all duration-100 ease-linear"
                    :class="getPriorityBarClass(toast.priority)"
                    :style="`width: ${toast.progress}%`"
                ></div>
            </div>
        </div>
    </template>
</div>
@endauth
