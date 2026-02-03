{{-- Metric Card Element Properties --}}
@props(['elementIndex', 'element'])

<div class="space-y-3">
    {{-- Metric Key --}}
    <div>
        <label class="block text-[10px] text-gray-500 mb-1">Metric</label>
        <select
            wire:model.live="elements.{{ $elementIndex }}.config.metric_key"
            class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
        >
            <option value="">Select metric...</option>
            <option value="gpa">GPA</option>
            <option value="attendance_rate">Attendance Rate</option>
            <option value="wellness_score">Wellness Score</option>
            <option value="engagement_score">Engagement Score</option>
            <option value="course_completion">Course Completion</option>
            <option value="grade_average">Grade Average</option>
            <option value="risk_score">Risk Score</option>
        </select>
    </div>

    {{-- Display Label --}}
    <div>
        <label class="block text-[10px] text-gray-500 mb-1">Display Label</label>
        <input
            type="text"
            wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.config.label"
            class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            placeholder="e.g., Average GPA"
        >
    </div>

    {{-- Show Trend Toggle --}}
    <div>
        <label class="flex items-center gap-2 cursor-pointer">
            <input
                type="checkbox"
                wire:model.live="elements.{{ $elementIndex }}.config.show_trend"
                class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500 w-4 h-4"
            >
            <span class="text-xs text-gray-700">Show trend indicator</span>
        </label>
        <p class="text-[10px] text-gray-400 mt-0.5 ml-6">Display change from previous period</p>
    </div>

    {{-- Comparison Period (shown when show_trend is enabled) --}}
    @if($element['config']['show_trend'] ?? true)
        <div>
            <label class="block text-[10px] text-gray-500 mb-1">Comparison Period</label>
            <select
                wire:model.live="elements.{{ $elementIndex }}.config.comparison_period"
                class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="last_week">vs Last Week</option>
                <option value="last_month">vs Last Month</option>
                <option value="last_quarter">vs Last Quarter</option>
                <option value="last_year">vs Last Year</option>
            </select>
        </div>
    @endif

    <hr class="border-gray-200">

    {{-- Card Style Section --}}
    <div>
        <label class="block text-[10px] text-gray-500 uppercase tracking-wider mb-2">Card Style</label>

        {{-- Background Color --}}
        <div class="mb-2">
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
                    placeholder="#F0F9FF"
                >
            </div>
            {{-- Color presets optimized for metric cards --}}
            <div class="flex items-center gap-1 mt-1.5">
                @foreach(['#F0F9FF', '#ECFDF5', '#FEF3C7', '#FCE7F3', '#F3E8FF', '#FEE2E2'] as $color)
                    <button
                        type="button"
                        wire:click="$set('elements.{{ $elementIndex }}.styles.backgroundColor', '{{ $color }}')"
                        class="w-5 h-5 rounded border border-gray-200 hover:scale-110 transition-transform"
                        style="background-color: {{ $color }}"
                        title="{{ $color }}"
                    ></button>
                @endforeach
            </div>
        </div>

        {{-- Value Color --}}
        <div class="mb-2">
            <label class="block text-[10px] text-gray-500 mb-1">Value Color</label>
            <div class="flex items-center gap-2">
                <input
                    type="color"
                    wire:model.live="elements.{{ $elementIndex }}.styles.valueColor"
                    class="w-8 h-8 rounded border border-gray-300 cursor-pointer"
                    title="Choose value text color"
                >
                <input
                    type="text"
                    wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.styles.valueColor"
                    class="flex-1 px-2 py-1.5 text-xs border border-gray-300 rounded-md font-mono"
                    placeholder="#1E40AF"
                >
            </div>
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

    {{-- Padding --}}
    <div>
        <label class="block text-[10px] text-gray-500 mb-1">Padding</label>
        <div class="flex items-center gap-2">
            <input
                type="range"
                min="8"
                max="32"
                wire:model.live="elements.{{ $elementIndex }}.styles.padding"
                class="flex-1 h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-pulse-orange-500"
            >
            <span class="text-xs text-gray-500 w-8 text-right">{{ $element['styles']['padding'] ?? 16 }}px</span>
        </div>
    </div>
</div>
