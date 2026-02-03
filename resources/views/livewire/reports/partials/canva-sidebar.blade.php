{{-- Canva-Style Sidebar with Pulse Orange Theme --}}
<div class="{{ $sidebarExpanded ? 'w-72' : 'w-14' }} bg-white border-r border-gray-200 flex flex-col h-full transition-all duration-200">
    {{-- Sidebar Toggle Button --}}
    <div class="p-1.5 border-b border-gray-200 flex {{ $sidebarExpanded ? 'justify-end' : 'justify-center' }}">
        <button
            wire:click="toggleSidebar"
            class="p-1.5 text-gray-500 hover:text-pulse-orange-600 hover:bg-pulse-orange-50 rounded-lg transition-colors"
            title="{{ $sidebarExpanded ? 'Collapse sidebar' : 'Expand sidebar' }}"
        >
            @if($sidebarExpanded)
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            @else
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7"/>
                </svg>
            @endif
        </button>
    </div>

    @if($sidebarExpanded)
        {{-- Sidebar Header with Tabs --}}
        <div class="border-b border-gray-200">
            <div class="flex">
                <button
                    wire:click="$set('sidebarTab', 'elements')"
                    class="flex-1 px-3 py-2 text-xs font-medium transition-colors {{ ($sidebarTab ?? 'elements') === 'elements' ? 'text-pulse-orange-600 border-b-2 border-pulse-orange-500 bg-pulse-orange-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}"
                    title="Add elements to canvas"
                >
                    <x-icon name="squares-plus" class="w-3.5 h-3.5 inline-block mr-0.5" />
                    Elements
                </button>
                <button
                    wire:click="$set('sidebarTab', 'templates')"
                    class="flex-1 px-3 py-2 text-xs font-medium transition-colors {{ ($sidebarTab ?? 'elements') === 'templates' ? 'text-pulse-orange-600 border-b-2 border-pulse-orange-500 bg-pulse-orange-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}"
                    title="Browse report templates"
                >
                    <x-icon name="rectangle-group" class="w-3.5 h-3.5 inline-block mr-0.5" />
                    Templates
                </button>
                <button
                    wire:click="$set('sidebarTab', 'uploads')"
                    class="flex-1 px-3 py-2 text-xs font-medium transition-colors {{ ($sidebarTab ?? 'elements') === 'uploads' ? 'text-pulse-orange-600 border-b-2 border-pulse-orange-500 bg-pulse-orange-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}"
                    title="Upload your own images"
                >
                    <x-icon name="cloud-arrow-up" class="w-3.5 h-3.5 inline-block mr-0.5" />
                    Uploads
                </button>
            </div>
        </div>

        {{-- Sidebar Content --}}
        <div class="flex-1 overflow-y-auto p-3">
            @if(($sidebarTab ?? 'elements') === 'elements')
                {{-- Elements Tab --}}
                <div class="space-y-4">
                    {{-- Text Elements --}}
                    <div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Text</h3>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button
                                wire:click="addElement('heading')"
                                class="flex flex-col items-center justify-center p-2 bg-gray-50 rounded-md hover:bg-pulse-orange-50 hover:border-pulse-orange-200 border border-transparent transition-colors"
                                title="Add a heading element"
                            >
                                <span class="text-base font-bold text-gray-700">H</span>
                                <span class="text-[10px] text-gray-500 mt-0.5">Heading</span>
                            </button>
                            <button
                                wire:click="addElement('text')"
                                class="flex flex-col items-center justify-center p-2 bg-gray-50 rounded-md hover:bg-pulse-orange-50 hover:border-pulse-orange-200 border border-transparent transition-colors"
                                title="Add a text block"
                            >
                                <x-icon name="document-text" class="w-4 h-4 text-gray-600" />
                                <span class="text-[10px] text-gray-500 mt-0.5">Text</span>
                            </button>
                        </div>
                    </div>

                    {{-- Data Elements --}}
                    <div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Data</h3>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button
                                wire:click="addElement('chart')"
                                class="flex flex-col items-center justify-center p-2 bg-gray-50 rounded-md hover:bg-pulse-orange-50 hover:border-pulse-orange-200 border border-transparent transition-colors"
                                title="Add a data chart (bar, line, pie)"
                            >
                                <x-icon name="chart-bar" class="w-4 h-4 text-gray-600" />
                                <span class="text-[10px] text-gray-500 mt-0.5">Chart</span>
                            </button>
                            <button
                                wire:click="addElement('table')"
                                class="flex flex-col items-center justify-center p-2 bg-gray-50 rounded-md hover:bg-pulse-orange-50 hover:border-pulse-orange-200 border border-transparent transition-colors"
                                title="Add a data table"
                            >
                                <x-icon name="table-cells" class="w-4 h-4 text-gray-600" />
                                <span class="text-[10px] text-gray-500 mt-0.5">Table</span>
                            </button>
                            <button
                                wire:click="addElement('metric_card')"
                                class="flex flex-col items-center justify-center p-2 bg-gray-50 rounded-md hover:bg-pulse-orange-50 hover:border-pulse-orange-200 border border-transparent transition-colors"
                                title="Add a metric card with KPI"
                            >
                                <x-icon name="presentation-chart-line" class="w-4 h-4 text-gray-600" />
                                <span class="text-[10px] text-gray-500 mt-0.5">Metric</span>
                            </button>
                            <button
                                wire:click="addElement('ai_text')"
                                class="flex flex-col items-center justify-center p-2 bg-gray-50 rounded-md hover:bg-pulse-orange-50 hover:border-pulse-orange-200 border border-transparent transition-colors"
                                title="Add AI-generated text content"
                            >
                                <x-icon name="sparkles" class="w-4 h-4 text-gray-600" />
                                <span class="text-[10px] text-gray-500 mt-0.5">AI Text</span>
                            </button>
                        </div>
                    </div>

                    {{-- Shape Elements --}}
                    <div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Shapes</h3>
                        <div class="grid grid-cols-4 gap-1.5">
                            <button
                                wire:click="addElement('rectangle')"
                                class="flex items-center justify-center p-2 bg-gray-50 rounded-md hover:bg-pulse-orange-50 hover:border-pulse-orange-200 border border-transparent transition-colors"
                                title="Rectangle"
                            >
                                <div class="w-5 h-3 bg-gray-400 rounded-sm"></div>
                            </button>
                            <button
                                wire:click="addElement('circle')"
                                class="flex items-center justify-center p-2 bg-gray-50 rounded-md hover:bg-pulse-orange-50 hover:border-pulse-orange-200 border border-transparent transition-colors"
                                title="Circle"
                            >
                                <div class="w-4 h-4 bg-gray-400 rounded-full"></div>
                            </button>
                            <button
                                wire:click="addElement('line')"
                                class="flex items-center justify-center p-2 bg-gray-50 rounded-md hover:bg-pulse-orange-50 hover:border-pulse-orange-200 border border-transparent transition-colors"
                                title="Line"
                            >
                                <div class="w-5 h-0.5 bg-gray-400"></div>
                            </button>
                            <button
                                wire:click="addElement('spacer')"
                                class="flex items-center justify-center p-2 bg-gray-50 rounded-md hover:bg-pulse-orange-50 hover:border-pulse-orange-200 border border-transparent transition-colors"
                                title="Spacer"
                            >
                                <div class="w-5 h-2 border border-dashed border-gray-400 rounded"></div>
                            </button>
                        </div>
                    </div>

                    {{-- Media Elements --}}
                    <div>
                        <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Media</h3>
                        <div class="grid grid-cols-2 gap-1.5">
                            <button
                                wire:click="addElement('image')"
                                class="flex flex-col items-center justify-center p-2 bg-gray-50 rounded-md hover:bg-pulse-orange-50 hover:border-pulse-orange-200 border border-transparent transition-colors"
                                title="Add an image"
                            >
                                <x-icon name="photo" class="w-4 h-4 text-gray-600" />
                                <span class="text-[10px] text-gray-500 mt-0.5">Image</span>
                            </button>
                            <button
                                wire:click="addElement('logo')"
                                class="flex flex-col items-center justify-center p-2 bg-gray-50 rounded-md hover:bg-pulse-orange-50 hover:border-pulse-orange-200 border border-transparent transition-colors"
                                title="Add organization logo"
                            >
                                <x-icon name="building-office" class="w-4 h-4 text-gray-600" />
                                <span class="text-[10px] text-gray-500 mt-0.5">Logo</span>
                            </button>
                        </div>
                    </div>
                </div>

            @elseif(($sidebarTab ?? 'elements') === 'templates')
                {{-- Templates Tab --}}
                <div class="space-y-3">
                    <div class="relative">
                        <input
                            type="text"
                            placeholder="Search templates..."
                            class="w-full pl-8 pr-3 py-1.5 text-sm border border-gray-300 rounded-md focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                        <x-icon name="magnifying-glass" class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        @foreach($templates ?? [] as $template)
                            <button
                                wire:click="loadTemplate('{{ $template['id'] ?? '' }}')"
                                class="aspect-[4/3] bg-gray-100 rounded-md border-2 border-transparent hover:border-pulse-orange-300 transition-colors flex items-center justify-center"
                            >
                                <span class="text-[10px] text-gray-500">{{ $template['name'] ?? 'Template' }}</span>
                            </button>
                        @endforeach
                        @if(empty($templates))
                            <div class="col-span-2 text-center py-6 text-gray-400 text-xs">
                                No templates available
                            </div>
                        @endif
                    </div>
                </div>

            @elseif(($sidebarTab ?? 'elements') === 'uploads')
                {{-- Uploads Tab --}}
                <div class="space-y-3">
                    <div class="border-2 border-dashed border-gray-300 rounded-md p-4 text-center hover:border-pulse-orange-400 transition-colors cursor-pointer">
                        <x-icon name="cloud-arrow-up" class="w-6 h-6 text-gray-400 mx-auto mb-1.5" />
                        <p class="text-xs text-gray-600">Drop files here or click to upload</p>
                        <p class="text-[10px] text-gray-400 mt-0.5">PNG, JPG, SVG up to 10MB</p>
                    </div>

                    <div class="text-xs text-gray-500 text-center py-3">
                        No uploads yet
                    </div>
                </div>
            @endif
        </div>

        {{-- Selected Element Properties --}}
        @if($selectedElement && $selectedElementId)
            @php
                $elementIndex = collect($elements)->search(fn($e) => $e['id'] === $selectedElementId);
                $elementType = $selectedElement['type'] ?? 'unknown';
            @endphp
            @if($elementIndex !== false)
            <div class="border-t border-gray-200 p-3 bg-gray-50 overflow-y-auto max-h-[50vh]">
                {{-- Element Type Badge --}}
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Properties</h3>
                    <span class="px-2 py-0.5 text-[10px] font-medium bg-pulse-orange-100 text-pulse-orange-700 rounded-full">
                        {{ ucfirst(str_replace('_', ' ', $elementType)) }}
                    </span>
                </div>

                {{-- Common Properties: Position & Size --}}
                <div class="space-y-2 pb-3 mb-3 border-b border-gray-200">
                    <div>
                        <label class="block text-[10px] text-gray-500 mb-0.5">Position</label>
                        <div class="grid grid-cols-2 gap-1.5">
                            <div class="relative">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">X</span>
                                <input
                                    type="number"
                                    wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.position.x"
                                    class="w-full pl-6 pr-2 py-1 text-xs border border-gray-300 rounded focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                >
                            </div>
                            <div class="relative">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">Y</span>
                                <input
                                    type="number"
                                    wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.position.y"
                                    class="w-full pl-6 pr-2 py-1 text-xs border border-gray-300 rounded focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                >
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[10px] text-gray-500 mb-0.5">Size</label>
                        <div class="grid grid-cols-2 gap-1.5">
                            <div class="relative">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">W</span>
                                <input
                                    type="number"
                                    wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.size.width"
                                    class="w-full pl-6 pr-2 py-1 text-xs border border-gray-300 rounded focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                >
                            </div>
                            <div class="relative">
                                <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400">H</span>
                                <input
                                    type="number"
                                    wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.size.height"
                                    class="w-full pl-6 pr-2 py-1 text-xs border border-gray-300 rounded focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                >
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Type-Specific Properties --}}
                @if($elementType === 'chart')
                    @include('livewire.reports.partials.properties.chart-properties', [
                        'elementIndex' => $elementIndex,
                        'element' => $selectedElement,
                    ])
                @elseif(in_array($elementType, ['text', 'heading', 'ai_text']))
                    @include('livewire.reports.partials.properties.text-properties', [
                        'elementIndex' => $elementIndex,
                        'element' => $selectedElement,
                    ])
                @elseif($elementType === 'metric_card')
                    @include('livewire.reports.partials.properties.metric-properties', [
                        'elementIndex' => $elementIndex,
                        'element' => $selectedElement,
                    ])
                @elseif(in_array($elementType, ['rectangle', 'circle', 'line']))
                    {{-- Shape Properties --}}
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[10px] text-gray-500 mb-1">Fill Color</label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="color"
                                    wire:model.live="elements.{{ $elementIndex }}.styles.backgroundColor"
                                    class="w-8 h-8 rounded border border-gray-300 cursor-pointer"
                                >
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.styles.backgroundColor"
                                    class="flex-1 px-2 py-1.5 text-xs border border-gray-300 rounded-md font-mono"
                                >
                            </div>
                        </div>
                        @if($elementType !== 'line')
                        <div>
                            <label class="block text-[10px] text-gray-500 mb-1">Border Radius</label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="range"
                                    min="0"
                                    max="{{ $elementType === 'circle' ? '100' : '24' }}"
                                    wire:model.live="elements.{{ $elementIndex }}.styles.borderRadius"
                                    class="flex-1 h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-pulse-orange-500"
                                >
                                <span class="text-xs text-gray-500 w-8 text-right">{{ $selectedElement['styles']['borderRadius'] ?? 0 }}{{ $elementType === 'circle' ? '%' : 'px' }}</span>
                            </div>
                        </div>
                        @endif
                        <div>
                            <label class="block text-[10px] text-gray-500 mb-1">Opacity</label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="range"
                                    min="0"
                                    max="100"
                                    wire:model.live="elements.{{ $elementIndex }}.styles.opacity"
                                    class="flex-1 h-1.5 bg-gray-200 rounded-lg appearance-none cursor-pointer accent-pulse-orange-500"
                                >
                                <span class="text-xs text-gray-500 w-8 text-right">{{ $selectedElement['styles']['opacity'] ?? 100 }}%</span>
                            </div>
                        </div>
                    </div>
                @elseif(in_array($elementType, ['image', 'logo']))
                    {{-- Image Properties --}}
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[10px] text-gray-500 mb-1">Image Fit</label>
                            <select
                                wire:model.live="elements.{{ $elementIndex }}.config.fit"
                                class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            >
                                <option value="contain">Contain</option>
                                <option value="cover">Cover</option>
                                <option value="fill">Fill</option>
                                <option value="none">Original Size</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] text-gray-500 mb-1">Alt Text</label>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.config.alt"
                                class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="Image description"
                            >
                        </div>
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
                                <span class="text-xs text-gray-500 w-8 text-right">{{ $selectedElement['styles']['borderRadius'] ?? 4 }}px</span>
                            </div>
                        </div>
                    </div>
                @elseif($elementType === 'table')
                    {{-- Table Properties --}}
                    <div class="space-y-3">
                        <div>
                            <label class="block text-[10px] text-gray-500 mb-1">Table Title</label>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.config.title"
                                class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded-md focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="Data Table"
                            >
                        </div>
                        <div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model.live="elements.{{ $elementIndex }}.config.sortable"
                                    class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500 w-4 h-4"
                                >
                                <span class="text-xs text-gray-700">Enable sorting</span>
                            </label>
                        </div>
                        <div>
                            <label class="block text-[10px] text-gray-500 mb-1">Background</label>
                            <div class="flex items-center gap-2">
                                <input
                                    type="color"
                                    wire:model.live="elements.{{ $elementIndex }}.styles.backgroundColor"
                                    class="w-8 h-8 rounded border border-gray-300 cursor-pointer"
                                >
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="elements.{{ $elementIndex }}.styles.backgroundColor"
                                    class="flex-1 px-2 py-1.5 text-xs border border-gray-300 rounded-md font-mono"
                                >
                            </div>
                        </div>
                    </div>
                @else
                    {{-- Generic Properties for unknown types --}}
                    <div class="text-center py-4 text-gray-400 text-xs">
                        No additional properties available
                    </div>
                @endif
            </div>
            @endif
        @endif
    @else
        {{-- Collapsed Sidebar - Icon Only --}}
        <div class="flex-1 flex flex-col items-center py-3 space-y-2">
            <button
                wire:click="toggleSidebar"
                class="p-2 text-gray-600 hover:text-pulse-orange-600 hover:bg-pulse-orange-50 rounded-md transition-colors {{ ($sidebarTab ?? 'elements') === 'elements' ? 'bg-pulse-orange-50 text-pulse-orange-600' : '' }}"
                title="Elements"
            >
                <x-icon name="squares-plus" class="w-4 h-4" />
            </button>
            <button
                wire:click="toggleSidebar"
                class="p-2 text-gray-600 hover:text-pulse-orange-600 hover:bg-pulse-orange-50 rounded-md transition-colors {{ ($sidebarTab ?? 'elements') === 'templates' ? 'bg-pulse-orange-50 text-pulse-orange-600' : '' }}"
                title="Templates"
            >
                <x-icon name="rectangle-group" class="w-4 h-4" />
            </button>
            <button
                wire:click="toggleSidebar"
                class="p-2 text-gray-600 hover:text-pulse-orange-600 hover:bg-pulse-orange-50 rounded-md transition-colors {{ ($sidebarTab ?? 'elements') === 'uploads' ? 'bg-pulse-orange-50 text-pulse-orange-600' : '' }}"
                title="Uploads"
            >
                <x-icon name="cloud-arrow-up" class="w-4 h-4" />
            </button>
        </div>
    @endif
</div>
