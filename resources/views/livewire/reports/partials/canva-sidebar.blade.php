{{-- Canva-Style Sidebar --}}
<div class="w-80 bg-white border-r border-gray-200 flex flex-col h-full">
    {{-- Sidebar Header with Tabs --}}
    <div class="border-b border-gray-200">
        <div class="flex">
            <button
                wire:click="$set('sidebarTab', 'elements')"
                class="flex-1 px-4 py-3 text-sm font-medium transition-colors {{ ($sidebarTab ?? 'elements') === 'elements' ? 'text-purple-600 border-b-2 border-purple-600 bg-purple-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}"
            >
                <x-icon name="squares-plus" class="w-4 h-4 inline-block mr-1" />
                Elements
            </button>
            <button
                wire:click="$set('sidebarTab', 'templates')"
                class="flex-1 px-4 py-3 text-sm font-medium transition-colors {{ ($sidebarTab ?? 'elements') === 'templates' ? 'text-purple-600 border-b-2 border-purple-600 bg-purple-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}"
            >
                <x-icon name="rectangle-group" class="w-4 h-4 inline-block mr-1" />
                Templates
            </button>
            <button
                wire:click="$set('sidebarTab', 'uploads')"
                class="flex-1 px-4 py-3 text-sm font-medium transition-colors {{ ($sidebarTab ?? 'elements') === 'uploads' ? 'text-purple-600 border-b-2 border-purple-600 bg-purple-50' : 'text-gray-600 hover:text-gray-900 hover:bg-gray-50' }}"
            >
                <x-icon name="cloud-arrow-up" class="w-4 h-4 inline-block mr-1" />
                Uploads
            </button>
        </div>
    </div>

    {{-- Sidebar Content --}}
    <div class="flex-1 overflow-y-auto p-4">
        @if(($sidebarTab ?? 'elements') === 'elements')
            {{-- Elements Tab --}}
            <div class="space-y-6">
                {{-- Text Elements --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Text</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            wire:click="addElement('heading')"
                            class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                        >
                            <span class="text-lg font-bold text-gray-700">H</span>
                            <span class="text-xs text-gray-500 mt-1">Heading</span>
                        </button>
                        <button
                            wire:click="addElement('text')"
                            class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                        >
                            <x-icon name="document-text" class="w-5 h-5 text-gray-600" />
                            <span class="text-xs text-gray-500 mt-1">Text</span>
                        </button>
                    </div>
                </div>

                {{-- Data Elements --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Data</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            wire:click="addElement('chart')"
                            class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                        >
                            <x-icon name="chart-bar" class="w-5 h-5 text-gray-600" />
                            <span class="text-xs text-gray-500 mt-1">Chart</span>
                        </button>
                        <button
                            wire:click="addElement('table')"
                            class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                        >
                            <x-icon name="table-cells" class="w-5 h-5 text-gray-600" />
                            <span class="text-xs text-gray-500 mt-1">Table</span>
                        </button>
                        <button
                            wire:click="addElement('metric')"
                            class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                        >
                            <x-icon name="presentation-chart-line" class="w-5 h-5 text-gray-600" />
                            <span class="text-xs text-gray-500 mt-1">Metric</span>
                        </button>
                        <button
                            wire:click="addElement('progress')"
                            class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                        >
                            <x-icon name="chart-bar-square" class="w-5 h-5 text-gray-600" />
                            <span class="text-xs text-gray-500 mt-1">Progress</span>
                        </button>
                    </div>
                </div>

                {{-- Shape Elements --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Shapes</h3>
                    <div class="grid grid-cols-4 gap-2">
                        <button
                            wire:click="addElement('rectangle')"
                            class="flex items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                            title="Rectangle"
                        >
                            <div class="w-6 h-4 bg-gray-400 rounded-sm"></div>
                        </button>
                        <button
                            wire:click="addElement('circle')"
                            class="flex items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                            title="Circle"
                        >
                            <div class="w-5 h-5 bg-gray-400 rounded-full"></div>
                        </button>
                        <button
                            wire:click="addElement('line')"
                            class="flex items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                            title="Line"
                        >
                            <div class="w-6 h-0.5 bg-gray-400"></div>
                        </button>
                        <button
                            wire:click="addElement('divider')"
                            class="flex items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                            title="Divider"
                        >
                            <div class="w-6 h-0.5 bg-gray-400 border-t-2 border-dashed border-gray-500"></div>
                        </button>
                    </div>
                </div>

                {{-- Media Elements --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Media</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            wire:click="addElement('image')"
                            class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                        >
                            <x-icon name="photo" class="w-5 h-5 text-gray-600" />
                            <span class="text-xs text-gray-500 mt-1">Image</span>
                        </button>
                        <button
                            wire:click="addElement('logo')"
                            class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                        >
                            <x-icon name="building-office" class="w-5 h-5 text-gray-600" />
                            <span class="text-xs text-gray-500 mt-1">Logo</span>
                        </button>
                    </div>
                </div>

                {{-- Layout Elements --}}
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Layout</h3>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            wire:click="addElement('container')"
                            class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                        >
                            <x-icon name="square-2-stack" class="w-5 h-5 text-gray-600" />
                            <span class="text-xs text-gray-500 mt-1">Container</span>
                        </button>
                        <button
                            wire:click="addElement('grid')"
                            class="flex flex-col items-center justify-center p-3 bg-gray-50 rounded-lg hover:bg-purple-50 hover:border-purple-200 border border-transparent transition-colors"
                        >
                            <x-icon name="squares-2x2" class="w-5 h-5 text-gray-600" />
                            <span class="text-xs text-gray-500 mt-1">Grid</span>
                        </button>
                    </div>
                </div>
            </div>

        @elseif(($sidebarTab ?? 'elements') === 'templates')
            {{-- Templates Tab --}}
            <div class="space-y-4">
                <div class="relative">
                    <input
                        type="text"
                        placeholder="Search templates..."
                        class="w-full pl-9 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                    >
                    <x-icon name="magnifying-glass" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    @foreach(['Basic Report', 'Dashboard', 'Progress Report', 'Summary'] as $template)
                        <button class="aspect-[4/3] bg-gray-100 rounded-lg border-2 border-transparent hover:border-purple-300 transition-colors flex items-center justify-center">
                            <span class="text-xs text-gray-500">{{ $template }}</span>
                        </button>
                    @endforeach
                </div>
            </div>

        @elseif(($sidebarTab ?? 'elements') === 'uploads')
            {{-- Uploads Tab --}}
            <div class="space-y-4">
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-purple-400 transition-colors cursor-pointer">
                    <x-icon name="cloud-arrow-up" class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                    <p class="text-sm text-gray-600">Drop files here or click to upload</p>
                    <p class="text-xs text-gray-400 mt-1">PNG, JPG, SVG up to 10MB</p>
                </div>

                <div class="text-sm text-gray-500 text-center py-4">
                    No uploads yet
                </div>
            </div>
        @endif
    </div>

    {{-- Selected Element Properties --}}
    @if($selectedElement)
        <div class="border-t border-gray-200 p-4 bg-gray-50">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Properties</h3>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Position</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input
                            type="number"
                            wire:model.live.debounce.300ms="elements.{{ $selectedElement }}.x"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
                            placeholder="X"
                        >
                        <input
                            type="number"
                            wire:model.live.debounce.300ms="elements.{{ $selectedElement }}.y"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
                            placeholder="Y"
                        >
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Size</label>
                    <div class="grid grid-cols-2 gap-2">
                        <input
                            type="number"
                            wire:model.live.debounce.300ms="elements.{{ $selectedElement }}.width"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
                            placeholder="W"
                        >
                        <input
                            type="number"
                            wire:model.live.debounce.300ms="elements.{{ $selectedElement }}.height"
                            class="w-full px-2 py-1 text-sm border border-gray-300 rounded"
                            placeholder="H"
                        >
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
