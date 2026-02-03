{{-- Chart Element Properties --}}
@props(['elementIndex', 'element'])

<div class="space-y-3">
    {{-- Chart Type --}}
    <div>
        <label class="block text-[10px] text-gray-500 mb-1">Chart Type</label>
        <select
            wire:model.live="elements.{{ $elementIndex }}.config.chart_type"
            class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
        >
            <option value="line">Line Chart</option>
            <option value="bar">Bar Chart</option>
            <option value="pie">Pie Chart</option>
            <option value="doughnut">Doughnut Chart</option>
            <option value="area">Area Chart</option>
            <option value="radar">Radar Chart</option>
        </select>
    </div>

    {{-- Chart Title --}}
    <div>
        <label class="block text-[10px] text-gray-500 mb-1">Title</label>
        <input
            type="text"
            wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.config.title"
            class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            placeholder="Chart Title"
        >
    </div>

    {{-- Metric Keys Multi-select --}}
    <div x-data="{ open: false }">
        <label class="block text-[10px] text-gray-500 mb-1">Metrics</label>
        <div class="relative">
            <button
                @click="open = !open"
                type="button"
                class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded-md bg-white text-left flex items-center justify-between focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <span class="truncate">
                    @php
                        $selectedMetrics = $element['config']['metric_keys'] ?? [];
                    @endphp
                    {{ count($selectedMetrics) > 0 ? implode(', ', array_map(fn($m) => ucwords(str_replace('_', ' ', $m)), $selectedMetrics)) : 'Select metrics...' }}
                </span>
                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div
                x-show="open"
                @click.away="open = false"
                x-transition
                class="absolute z-50 mt-1 w-full bg-white rounded-md shadow-lg border border-gray-200 max-h-40 overflow-y-auto"
            >
                @foreach(['gpa', 'attendance_rate', 'wellness_score', 'engagement_score', 'course_completion', 'grade_average', 'risk_score'] as $metric)
                    <label class="flex items-center gap-2 px-2 py-1.5 hover:bg-gray-50 cursor-pointer">
                        <input
                            type="checkbox"
                            value="{{ $metric }}"
                            wire:model.live="elements.{{ $elementIndex }}.config.metric_keys"
                            class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500 w-3 h-3"
                        >
                        <span class="text-xs text-gray-700">{{ ucwords(str_replace('_', ' ', $metric)) }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Primary Color --}}
    <div>
        <label class="block text-[10px] text-gray-500 mb-1">Primary Color</label>
        <div class="flex items-center gap-2">
            <input
                type="color"
                wire:model.live="elements.{{ $elementIndex }}.config.colors.0"
                class="w-8 h-8 rounded border border-gray-300 cursor-pointer"
                title="Choose chart color"
            >
            <input
                type="text"
                wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.config.colors.0"
                class="flex-1 px-2 py-1.5 text-xs border border-gray-300 rounded-md font-mono"
                placeholder="#3B82F6"
            >
        </div>
        {{-- Color presets --}}
        <div class="flex items-center gap-1 mt-1.5">
            @foreach(['#3B82F6', '#F97316', '#10B981', '#8B5CF6', '#EF4444', '#EC4899'] as $color)
                <button
                    type="button"
                    wire:click="$set('elements.{{ $elementIndex }}.config.colors.0', '{{ $color }}')"
                    class="w-5 h-5 rounded border border-gray-200 hover:scale-110 transition-transform"
                    style="background-color: {{ $color }}"
                    title="{{ $color }}"
                ></button>
            @endforeach
        </div>
    </div>

    {{-- Background Color --}}
    <div>
        <label class="block text-[10px] text-gray-500 mb-1">Background</label>
        <div class="flex items-center gap-2">
            <input
                type="color"
                wire:model.live="elements.{{ $elementIndex }}.styles.backgroundColor"
                class="w-8 h-8 rounded border border-gray-300 cursor-pointer"
                title="Choose background color"
            >
            <input
                type="text"
                wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.styles.backgroundColor"
                class="flex-1 px-2 py-1.5 text-xs border border-gray-300 rounded-md font-mono"
                placeholder="#FFFFFF"
            >
        </div>
    </div>

    {{-- Border Radius --}}
    <div>
        <label class="block text-[10px] text-gray-500 mb-1">Border Radius</label>
        <div class="flex items-center gap-2">
            <input
                type="range"
                min="0"
                max="24"
                wire:model.live="elements.{{ $elementIndex }}.styles.borderRadius"
                class="flex-1 h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-pulse-orange-500"
            >
            <span class="text-xs text-gray-500 w-8 text-right">{{ $element['styles']['borderRadius'] ?? 8 }}px</span>
        </div>
    </div>
</div>
