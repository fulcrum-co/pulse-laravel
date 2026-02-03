@props([
    'section' => '',
    'title' => '',
    'description' => '',
    'position' => 'right'
])

{{-- Section Help Beacon - Pulsating blue dot with hover tooltip, click for full walkthrough --}}
<div
    x-data="{ showTooltip: false }"
    class="inline-flex items-center"
>
    <button
        @mouseenter="showTooltip = true"
        @mouseleave="showTooltip = false"
        @click="$dispatch('start-page-help', { section: '{{ $section }}' })"
        class="relative flex items-center justify-center"
        title="@term('guided_help_title')"
    >
        <!-- Pulsating Blue Dot -->
        <span class="relative flex h-3 w-3">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
        </span>

        <!-- Tooltip -->
        <div
            x-show="showTooltip"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @class([
                'absolute z-50 w-72 p-4 bg-gray-900 text-white rounded-xl shadow-2xl',
                'bottom-full mb-3 left-1/2 -translate-x-1/2' => $position === 'top',
                'top-full mt-3 left-1/2 -translate-x-1/2' => $position === 'bottom',
                'right-full mr-3 top-1/2 -translate-y-1/2' => $position === 'left',
                'left-full ml-3 top-1/2 -translate-y-1/2' => $position === 'right',
            ])
            @click.stop
        >
            <!-- Arrow -->
            <div @class([
                'absolute w-3 h-3 bg-gray-900 transform rotate-45',
                'top-full -mt-1.5 left-1/2 -translate-x-1/2' => $position === 'top',
                'bottom-full -mb-1.5 left-1/2 -translate-x-1/2' => $position === 'bottom',
                'left-full -ml-1.5 top-1/2 -translate-y-1/2' => $position === 'left',
                'right-full -mr-1.5 top-1/2 -translate-y-1/2' => $position === 'right',
            ])></div>

            <!-- Content -->
            <p class="text-sm text-white leading-relaxed">{{ $description }}</p>

            <!-- Footer with navigation hint -->
            <div class="mt-3 pt-3 border-t border-gray-700 flex items-center justify-between">
                <span class="text-xs text-gray-400">@term('click_full_tutorial_label')</span>
                <div class="flex items-center gap-1">
                    <span class="w-6 h-6 rounded-full bg-gray-800 flex items-center justify-center">
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </span>
                    <span class="w-6 h-6 rounded-full bg-gray-800 flex items-center justify-center">
                        <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </span>
                </div>
            </div>
        </div>
    </button>
</div>
