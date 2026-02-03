{{-- Text/Heading Element Properties --}}
@props(['elementIndex', 'element'])

<div class="space-y-3">
    {{-- Font Size --}}
    <div>
        <label class="block text-[10px] text-gray-500 mb-1">Font Size</label>
        <div class="flex items-center gap-2">
            <select
                wire:model.live="elements.{{ $elementIndex }}.styles.fontSize"
                class="flex-1 px-2 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="12">12px - Small</option>
                <option value="14">14px - Normal</option>
                <option value="16">16px - Medium</option>
                <option value="18">18px - Large</option>
                <option value="20">20px - X-Large</option>
                <option value="24">24px - Heading 3</option>
                <option value="28">28px - Heading 2</option>
                <option value="32">32px - Heading 1</option>
                <option value="40">40px - Display</option>
            </select>
        </div>
    </div>

    {{-- Font Weight --}}
    <div>
        <label class="block text-[10px] text-gray-500 mb-1">Font Weight</label>
        <div class="flex items-center gap-1">
            @foreach([['normal', 'Aa'], ['medium', 'Aa'], ['semibold', 'Aa'], ['bold', 'Aa']] as $idx => $weight)
                <button
                    type="button"
                    wire:click="$set('elements.{{ $elementIndex }}.styles.fontWeight', '{{ $weight[0] }}')"
                    class="flex-1 px-2 py-1.5 text-xs border rounded-md transition-colors {{ ($element['styles']['fontWeight'] ?? 'normal') === $weight[0] ? 'border-pulse-orange-500 bg-pulse-orange-50 text-pulse-orange-700' : 'border-gray-300 hover:bg-gray-50' }}"
                    style="font-weight: {{ $weight[0] === 'medium' ? '500' : ($weight[0] === 'semibold' ? '600' : $weight[0]) }}"
                    title="{{ ucfirst($weight[0]) }}"
                >
                    {{ $weight[1] }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- Text Alignment --}}
    <div>
        <label class="block text-[10px] text-gray-500 mb-1">Alignment</label>
        <div class="flex items-center gap-1">
            @foreach(['left', 'center', 'right', 'justify'] as $align)
                <button
                    type="button"
                    wire:click="$set('elements.{{ $elementIndex }}.styles.textAlign', '{{ $align }}')"
                    class="flex-1 p-1.5 border rounded-md transition-colors {{ ($element['styles']['textAlign'] ?? 'left') === $align ? 'border-pulse-orange-500 bg-pulse-orange-50 text-pulse-orange-600' : 'border-gray-300 text-gray-500 hover:bg-gray-50' }}"
                    title="{{ ucfirst($align) }}"
                >
                    @if($align === 'left')
                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h14"/>
                        </svg>
                    @elseif($align === 'center')
                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M5 18h14"/>
                        </svg>
                    @elseif($align === 'right')
                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M10 12h10M6 18h14"/>
                        </svg>
                    @else
                        <svg class="w-4 h-4 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Text Color --}}
    <div>
        <label class="block text-[10px] text-gray-500 mb-1">Text Color</label>
        <div class="flex items-center gap-2">
            <input
                type="color"
                wire:model.live="elements.{{ $elementIndex }}.styles.color"
                class="w-8 h-8 rounded border border-gray-300 cursor-pointer"
                title="Choose text color"
            >
            <input
                type="text"
                wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.styles.color"
                class="flex-1 px-2 py-1.5 text-xs border border-gray-300 rounded-md font-mono"
                placeholder="#111827"
            >
        </div>
        {{-- Color presets --}}
        <div class="flex items-center gap-1 mt-1.5">
            @foreach(['#111827', '#374151', '#6B7280', '#9CA3AF', '#F97316', '#3B82F6'] as $color)
                <button
                    type="button"
                    wire:click="$set('elements.{{ $elementIndex }}.styles.color', '{{ $color }}')"
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
                placeholder="transparent"
            >
        </div>
        {{-- Transparency option --}}
        <label class="flex items-center gap-2 mt-1.5 cursor-pointer">
            <input
                type="checkbox"
                wire:click="$set('elements.{{ $elementIndex }}.styles.backgroundColor', '{{ ($element['styles']['backgroundColor'] ?? 'transparent') === 'transparent' ? '#FFFFFF' : 'transparent' }}')"
                {{ ($element['styles']['backgroundColor'] ?? 'transparent') === 'transparent' ? 'checked' : '' }}
                class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500 w-3 h-3"
            >
            <span class="text-[10px] text-gray-500">Transparent</span>
        </label>
    </div>

    {{-- Padding --}}
    <div>
        <label class="block text-[10px] text-gray-500 mb-1">Padding</label>
        <div class="flex items-center gap-2">
            <input
                type="range"
                min="0"
                max="32"
                wire:model.live="elements.{{ $elementIndex }}.styles.padding"
                class="flex-1 h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-pulse-orange-500"
            >
            <span class="text-xs text-gray-500 w-8 text-right">{{ $element['styles']['padding'] ?? 8 }}px</span>
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
            <span class="text-xs text-gray-500 w-8 text-right">{{ $element['styles']['borderRadius'] ?? 4 }}px</span>
        </div>
    </div>
</div>
