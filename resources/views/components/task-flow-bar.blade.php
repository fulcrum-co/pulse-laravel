{{-- Task Flow Bar Component --}}
{{-- Persistent top bar for guided task flow through notifications --}}
@auth
<div
    x-data="taskFlowManager()"
    x-show="isActive"
    x-transition:enter="transform ease-out duration-300"
    x-transition:enter-start="-translate-y-full"
    x-transition:enter-end="translate-y-0"
    x-transition:leave="transform ease-in duration-200"
    x-transition:leave-start="translate-y-0"
    x-transition:leave-end="-translate-y-full"
    x-cloak
    class="fixed top-0 left-0 right-0 bg-gradient-to-r from-purple-600 to-purple-700 text-white shadow-lg z-[100]"
    style="display: none;"
>
    <div class="max-w-7xl mx-auto px-4 py-3">
        <div class="flex items-center justify-between gap-4">
            {{-- Progress Info --}}
            <div class="flex items-center gap-3 min-w-0 flex-shrink-0">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-white/20 text-white font-semibold text-sm"
                          x-text="currentIndex + 1"></span>
                    <span class="text-sm text-purple-200">of</span>
                    <span class="text-sm font-medium text-white" x-text="queue.length"></span>
                </div>
                <div class="hidden sm:block h-4 w-px bg-purple-400/50"></div>
                <div class="hidden sm:flex items-center gap-2 min-w-0">
                    <template x-if="currentTask?.priority === 'urgent' || currentTask?.priority === 'high'">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                              :class="currentTask?.priority === 'urgent' ? 'bg-red-500 text-white' : 'bg-amber-400 text-amber-900'"
                              x-text="currentTask?.priority === 'urgent' ? 'Urgent' : 'High'"></span>
                    </template>
                    <span class="text-sm font-medium text-white truncate max-w-xs" x-text="currentTask?.title"></span>
                </div>
            </div>

            {{-- Progress Bar (hidden on mobile) --}}
            <div class="flex-1 max-w-xs hidden md:block">
                <div class="h-2 bg-purple-400/30 rounded-full overflow-hidden">
                    <div class="h-full bg-white transition-all duration-300 ease-out rounded-full"
                         :style="`width: ${progress}%`"></div>
                </div>
                <p class="text-xs text-purple-200 mt-1 text-center" x-show="remainingCount > 0">
                    <span x-text="remainingCount"></span> task<span x-show="remainingCount !== 1">s</span> remaining
                </p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
                {{-- Previous Button --}}
                <button @click="goToPrevious"
                        :disabled="currentIndex === 0"
                        class="p-2 text-purple-200 hover:text-white hover:bg-white/10 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                        title="Previous task">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                {{-- Skip Button --}}
                <button @click="skipCurrentTask"
                        class="px-3 py-1.5 text-sm text-purple-200 hover:text-white hover:bg-white/10 rounded-lg transition-colors hidden sm:inline-flex">
                    Skip
                </button>

                {{-- Complete Button --}}
                <button @click="completeCurrentTask"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-purple-700 bg-white rounded-lg hover:bg-purple-50 transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span class="hidden sm:inline">Done</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                {{-- Next Button --}}
                <button @click="goToNext"
                        :disabled="currentIndex >= queue.length - 1"
                        class="p-2 text-purple-200 hover:text-white hover:bg-white/10 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed"
                        title="Next task">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>

                {{-- Divider --}}
                <div class="w-px h-6 bg-purple-400/50 mx-1 hidden sm:block"></div>

                {{-- Exit Button --}}
                <button @click="exitFlow()"
                        class="p-2 text-purple-200 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
                        title="Exit task flow">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
</div>
@endauth
