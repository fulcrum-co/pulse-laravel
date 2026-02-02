{{-- Canva-Style Sidebar --}}
<aside
    class="flex bg-white border-r border-gray-200 flex-shrink-0 h-full"
    x-data="{
        activePanel: @entangle('activeSidebarPanel'),
        expanded: @entangle('sidebarExpanded'),
        searchQuery: '',
        recentElements: ['text', 'chart', 'metric_card'],
    }"
>
    {{-- Icon Strip (always visible) --}}
    <div class="w-16 bg-gray-50 border-r border-gray-100 flex flex-col py-2">
        {{-- Panel Icons --}}
        <div class="flex-1 space-y-1 px-2">
            {{-- Templates --}}
            <button
                @click="activePanel = 'templates'; expanded = true"
                :class="activePanel === 'templates' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                class="w-full p-3 rounded-xl flex flex-col items-center gap-1 transition-colors"
                title="Templates"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                <span class="text-[10px] font-medium">Templates</span>
            </button>

            {{-- Elements --}}
            <button
                @click="activePanel = 'elements'; expanded = true"
                :class="activePanel === 'elements' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                class="w-full p-3 rounded-xl flex flex-col items-center gap-1 transition-colors"
                title="Elements"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                </svg>
                <span class="text-[10px] font-medium">Elements</span>
            </button>

            {{-- Data --}}
            <button
                @click="activePanel = 'data'; expanded = true"
                :class="activePanel === 'data' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                class="w-full p-3 rounded-xl flex flex-col items-center gap-1 transition-colors"
                title="Data"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span class="text-[10px] font-medium">Data</span>
            </button>

            {{-- Smart Blocks --}}
            <button
                @click="activePanel = 'smart_blocks'; expanded = true"
                :class="activePanel === 'smart_blocks' ? 'bg-purple-100 text-purple-600' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                class="w-full p-3 rounded-xl flex flex-col items-center gap-1 transition-colors"
                title="Smart Blocks"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                </svg>
                <span class="text-[10px] font-medium">Smart</span>
            </button>

            {{-- Design --}}
            <button
                @click="activePanel = 'design'; expanded = true"
                :class="activePanel === 'design' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                class="w-full p-3 rounded-xl flex flex-col items-center gap-1 transition-colors"
                title="Design"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                </svg>
                <span class="text-[10px] font-medium">Design</span>
            </button>

            {{-- Layers (Phase 6) --}}
            <button
                @click="activePanel = 'layers'; expanded = true"
                :class="activePanel === 'layers' ? 'bg-blue-100 text-blue-600' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-700'"
                class="w-full p-3 rounded-xl flex flex-col items-center gap-1 transition-colors"
                title="Layers"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <span class="text-[10px] font-medium">Layers</span>
            </button>
        </div>

        {{-- Collapse Toggle --}}
        <div class="px-2 pt-2 border-t border-gray-200">
            <button
                @click="expanded = !expanded"
                class="w-full p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                :title="expanded ? 'Collapse sidebar' : 'Expand sidebar'"
            >
                <svg class="w-5 h-5 mx-auto transition-transform" :class="expanded ? '' : 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Expandable Panel --}}
    <div
        x-show="expanded"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-x-4"
        x-transition:enter-end="opacity-100 translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-x-0"
        x-transition:leave-end="opacity-0 -translate-x-4"
        class="w-64 flex flex-col overflow-hidden"
    >
        {{-- Search Bar --}}
        <div class="p-3 border-b border-gray-100">
            <div class="relative">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="text"
                    x-model="searchQuery"
                    placeholder="Search..."
                    class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                >
            </div>
        </div>

        {{-- Panel Content --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar">
            {{-- Templates Panel --}}
            <div x-show="activePanel === 'templates'" class="p-3 space-y-3">
                {{-- Quick Start --}}
                <div>
                    <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Quick Start</h4>
                    <button
                        wire:click="startBlank"
                        class="w-full flex items-center gap-2.5 p-2 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors text-left border border-gray-200"
                    >
                        <div class="w-8 h-8 bg-white border-2 border-dashed border-gray-300 rounded flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="text-xs font-medium text-gray-900">Blank Canvas</span>
                            <p class="text-[10px] text-gray-500">Start from scratch</p>
                        </div>
                    </button>
                </div>

                {{-- Student Reports --}}
                <div>
                    <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Student Reports</h4>
                    <div class="space-y-1.5">
                        @foreach($templates as $template)
                            @if(($template['category'] ?? 'student') === 'student')
                            <button
                                wire:click="loadTemplate('{{ $template['id'] }}')"
                                class="w-full flex items-center gap-2.5 p-2 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 rounded-lg transition-colors text-left border border-blue-200"
                            >
                                <div class="w-8 h-8 bg-white rounded flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-700 truncate">{{ $template['name'] }}</span>
                            </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Cohort Analysis --}}
                <div>
                    <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Cohort Analysis</h4>
                    <div class="space-y-1.5">
                        @foreach($templates as $template)
                            @if(($template['category'] ?? '') === 'cohort')
                            <button
                                wire:click="loadTemplate('{{ $template['id'] }}')"
                                class="w-full flex items-center gap-2.5 p-2 bg-gradient-to-r from-green-50 to-emerald-50 hover:from-green-100 hover:to-emerald-100 rounded-lg transition-colors text-left border border-green-200"
                            >
                                <div class="w-8 h-8 bg-white rounded flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-700 truncate">{{ $template['name'] }}</span>
                            </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- School Dashboards --}}
                <div>
                    <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">School Dashboards</h4>
                    <div class="space-y-1.5">
                        @foreach($templates as $template)
                            @if(($template['category'] ?? '') === 'school')
                            <button
                                wire:click="loadTemplate('{{ $template['id'] }}')"
                                class="w-full flex items-center gap-2.5 p-2 bg-gradient-to-r from-purple-50 to-violet-50 hover:from-purple-100 hover:to-violet-100 rounded-lg transition-colors text-left border border-purple-200"
                            >
                                <div class="w-8 h-8 bg-white rounded flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                    </svg>
                                </div>
                                <span class="text-xs font-medium text-gray-700 truncate">{{ $template['name'] }}</span>
                            </button>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- Browse All Templates --}}
                <button
                    wire:click="$set('showTemplateGallery', true)"
                    class="w-full flex items-center justify-center gap-1.5 p-2 text-pulse-orange-600 bg-pulse-orange-50 hover:bg-pulse-orange-100 rounded-lg transition-colors text-xs font-medium"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Browse All Templates
                </button>
            </div>

            {{-- Elements Panel --}}
            <div x-show="activePanel === 'elements'" class="p-3 space-y-4">
                {{-- Recently Used --}}
                <div x-show="recentElements.length > 0">
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Recently Used</h4>
                    <div class="flex gap-2 overflow-x-auto pb-2">
                        <template x-for="type in recentElements" :key="type">
                            <button
                                @click="$wire.addElement(type)"
                                class="flex-shrink-0 flex items-center gap-2 px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors text-sm"
                            >
                                <span x-text="type.charAt(0).toUpperCase() + type.slice(1).replace('_', ' ')"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Text Elements --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Text</h4>
                    <div class="space-y-2">
                        <button
                            wire:click="addElement('text')"
                            class="w-full flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors text-left group"
                        >
                            <div class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center group-hover:border-pulse-orange-300">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900">Text Block</span>
                                <p class="text-xs text-gray-500">Add a paragraph of text</p>
                            </div>
                        </button>

                        <button
                            wire:click="addElement('text', {'config': {'content': '<h2>Heading</h2>'}})"
                            class="w-full flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors text-left group"
                        >
                            <div class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center group-hover:border-pulse-orange-300">
                                <span class="text-lg font-bold text-gray-500">H</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900">Heading</span>
                                <p class="text-xs text-gray-500">Add a title or heading</p>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- Media --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Media</h4>
                    <div class="space-y-2">
                        <button
                            wire:click="addElement('image')"
                            class="w-full flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors text-left group"
                        >
                            <div class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center group-hover:border-pulse-orange-300">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900">Image</span>
                                <p class="text-xs text-gray-500">Upload or link an image</p>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- Layout --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Layout</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            wire:click="addElement('spacer')"
                            class="flex flex-col items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors group"
                        >
                            <div class="w-8 h-8 border-2 border-dashed border-gray-300 rounded flex items-center justify-center mb-1 group-hover:border-gray-400">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                </svg>
                            </div>
                            <span class="text-xs text-gray-600">Spacer</span>
                        </button>

                        <button
                            wire:click="addElement('text', {'config': {'content': '<hr>'}, 'size': {'width': 720, 'height': 20}})"
                            class="flex flex-col items-center p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors group"
                        >
                            <div class="w-8 h-8 flex items-center justify-center mb-1">
                                <div class="w-6 h-0.5 bg-gray-400 group-hover:bg-gray-500"></div>
                            </div>
                            <span class="text-xs text-gray-600">Divider</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Data Panel --}}
            <div x-show="activePanel === 'data'" class="p-3 space-y-4">
                {{-- Charts --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Charts</h4>
                    <div class="grid grid-cols-2 gap-2">
                        <button
                            wire:click="addElement('chart', {'config': {'chart_type': 'line'}})"
                            class="flex flex-col items-center p-3 bg-gray-50 hover:bg-blue-50 rounded-xl transition-colors group"
                        >
                            <div class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center mb-2 group-hover:border-blue-300">
                                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700">Line Chart</span>
                        </button>

                        <button
                            wire:click="addElement('chart', {'config': {'chart_type': 'bar'}})"
                            class="flex flex-col items-center p-3 bg-gray-50 hover:bg-green-50 rounded-xl transition-colors group"
                        >
                            <div class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center mb-2 group-hover:border-green-300">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700">Bar Chart</span>
                        </button>

                        <button
                            wire:click="addElement('chart', {'config': {'chart_type': 'pie'}})"
                            class="flex flex-col items-center p-3 bg-gray-50 hover:bg-purple-50 rounded-xl transition-colors group"
                        >
                            <div class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center mb-2 group-hover:border-purple-300">
                                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700">Pie Chart</span>
                        </button>

                        <button
                            wire:click="addElement('chart', {'config': {'chart_type': 'doughnut'}})"
                            class="flex flex-col items-center p-3 bg-gray-50 hover:bg-orange-50 rounded-xl transition-colors group"
                        >
                            <div class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center mb-2 group-hover:border-orange-300">
                                <svg class="w-5 h-5 text-orange-500" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <circle cx="12" cy="12" r="8" stroke-width="4" class="opacity-30"/>
                                    <path d="M12 4a8 8 0 018 8" stroke-width="4" stroke-linecap="round"/>
                                </svg>
                            </div>
                            <span class="text-xs font-medium text-gray-700">Doughnut</span>
                        </button>
                    </div>
                </div>

                {{-- Metrics --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Metrics</h4>
                    <div class="space-y-2">
                        <button
                            wire:click="addElement('metric_card')"
                            class="w-full flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors text-left group"
                        >
                            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center text-white">
                                <span class="text-sm font-bold">3.8</span>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900">Metric Card</span>
                                <p class="text-xs text-gray-500">Display a single KPI value</p>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- Tables --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Tables</h4>
                    <div class="space-y-2">
                        <button
                            wire:click="addElement('table')"
                            class="w-full flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors text-left group"
                        >
                            <div class="w-10 h-10 bg-white border border-gray-200 rounded-lg flex items-center justify-center group-hover:border-pulse-orange-300">
                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-gray-900">Data Table</span>
                                <p class="text-xs text-gray-500">Display tabular data</p>
                            </div>
                        </button>
                    </div>
                </div>

                {{-- AI Content --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">AI-Powered</h4>
                    <div class="space-y-2">
                        <button
                            wire:click="addElement('ai_text')"
                            class="w-full flex items-center gap-3 p-3 bg-purple-50 hover:bg-purple-100 rounded-xl transition-colors text-left group border border-purple-200"
                        >
                            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                </svg>
                            </div>
                            <div>
                                <span class="text-sm font-medium text-purple-900">AI Text</span>
                                <p class="text-xs text-purple-600">Generate insights from data</p>
                            </div>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Smart Blocks Panel --}}
            <div x-show="activePanel === 'smart_blocks'" class="p-3 space-y-3">
                <p class="text-xs text-gray-500">Pre-built components that auto-populate with data.</p>

                {{-- Student Blocks --}}
                <div>
                    <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Student Reports</h4>
                    <div class="space-y-1.5">
                        <button
                            wire:click="addSmartBlock('student_header')"
                            class="w-full flex items-center gap-2.5 p-2 bg-gradient-to-r from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 rounded-lg transition-colors text-left border border-blue-200"
                        >
                            <div class="w-8 h-8 bg-white rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-medium text-gray-900">Student Header</span>
                                <p class="text-[10px] text-gray-500 truncate">Name, photo, grade</p>
                            </div>
                            <span class="px-1.5 py-0.5 text-[9px] font-medium bg-blue-100 text-blue-700 rounded flex-shrink-0">Auto</span>
                        </button>

                        <button
                            wire:click="addSmartBlock('metrics_row')"
                            class="w-full flex items-center gap-2.5 p-2 bg-gradient-to-r from-green-50 to-emerald-50 hover:from-green-100 hover:to-emerald-100 rounded-lg transition-colors text-left border border-green-200"
                        >
                            <div class="w-8 h-8 bg-white rounded flex items-center justify-center flex-shrink-0">
                                <div class="flex gap-px">
                                    <div class="w-1 h-2.5 bg-green-400 rounded-sm"></div>
                                    <div class="w-1 h-4 bg-green-500 rounded-sm"></div>
                                    <div class="w-1 h-2 bg-green-400 rounded-sm"></div>
                                    <div class="w-1 h-3 bg-green-500 rounded-sm"></div>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-medium text-gray-900">Metrics Row</span>
                                <p class="text-[10px] text-gray-500 truncate">4 key metrics</p>
                            </div>
                            <span class="px-1.5 py-0.5 text-[9px] font-medium bg-green-100 text-green-700 rounded flex-shrink-0">Auto</span>
                        </button>

                        <button
                            wire:click="addSmartBlock('trend_section')"
                            class="w-full flex items-center gap-2.5 p-2 bg-gradient-to-r from-purple-50 to-violet-50 hover:from-purple-100 hover:to-violet-100 rounded-lg transition-colors text-left border border-purple-200"
                        >
                            <div class="w-8 h-8 bg-white rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-medium text-gray-900">Performance Trend</span>
                                <p class="text-[10px] text-gray-500 truncate">Chart + AI insights</p>
                            </div>
                            <span class="px-1.5 py-0.5 text-[9px] font-medium bg-purple-100 text-purple-700 rounded flex-shrink-0">AI</span>
                        </button>

                        <button
                            wire:click="addSmartBlock('risk_banner')"
                            class="w-full flex items-center gap-2.5 p-2 bg-gradient-to-r from-amber-50 to-orange-50 hover:from-amber-100 hover:to-orange-100 rounded-lg transition-colors text-left border border-amber-200"
                        >
                            <div class="w-8 h-8 bg-white rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-medium text-gray-900">Risk Banner</span>
                                <p class="text-[10px] text-gray-500 truncate">Color-coded indicator</p>
                            </div>
                            <span class="px-1.5 py-0.5 text-[9px] font-medium bg-amber-100 text-amber-700 rounded flex-shrink-0">Auto</span>
                        </button>
                    </div>
                </div>

                {{-- Analysis Blocks --}}
                <div>
                    <h4 class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider mb-2">Analysis</h4>
                    <div class="space-y-1.5">
                        <button
                            wire:click="addSmartBlock('comparison_chart')"
                            class="w-full flex items-center gap-2.5 p-2 bg-gradient-to-r from-cyan-50 to-sky-50 hover:from-cyan-100 hover:to-sky-100 rounded-lg transition-colors text-left border border-cyan-200"
                        >
                            <div class="w-8 h-8 bg-white rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-medium text-gray-900">Comparison Chart</span>
                                <p class="text-[10px] text-gray-500 truncate">Student vs cohort</p>
                            </div>
                        </button>

                        <button
                            wire:click="addSmartBlock('executive_summary')"
                            class="w-full flex items-center gap-2.5 p-2 bg-gradient-to-r from-rose-50 to-pink-50 hover:from-rose-100 hover:to-pink-100 rounded-lg transition-colors text-left border border-rose-200"
                        >
                            <div class="w-8 h-8 bg-white rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <span class="text-xs font-medium text-gray-900">Executive Summary</span>
                                <p class="text-[10px] text-gray-500 truncate">AI narrative + metrics</p>
                            </div>
                            <span class="px-1.5 py-0.5 text-[9px] font-medium bg-rose-100 text-rose-700 rounded flex-shrink-0">AI</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Design Panel --}}
            <div x-show="activePanel === 'design'" class="p-3 space-y-4">
                {{-- Page Settings --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Page</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Size</label>
                            <select wire:model.live="pageSettings.size" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                                <option value="letter">Letter (8.5" x 11")</option>
                                <option value="a4">A4</option>
                                <option value="legal">Legal</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">Orientation</label>
                            <div class="flex gap-2">
                                <button
                                    wire:click="$set('pageSettings.orientation', 'portrait')"
                                    class="flex-1 px-3 py-2 text-sm rounded-lg border {{ ($pageSettings['orientation'] ?? 'portrait') === 'portrait' ? 'border-pulse-orange-500 bg-pulse-orange-50 text-pulse-orange-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}"
                                >
                                    Portrait
                                </button>
                                <button
                                    wire:click="$set('pageSettings.orientation', 'landscape')"
                                    class="flex-1 px-3 py-2 text-sm rounded-lg border {{ ($pageSettings['orientation'] ?? 'portrait') === 'landscape' ? 'border-pulse-orange-500 bg-pulse-orange-50 text-pulse-orange-700' : 'border-gray-200 text-gray-600 hover:bg-gray-50' }}"
                                >
                                    Landscape
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Colors --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Colors</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm text-gray-600 mb-2">Primary Color</label>
                            <div class="flex gap-2">
                                <input
                                    type="color"
                                    wire:model.live="branding.primary_color"
                                    class="w-10 h-10 rounded-lg border border-gray-200 cursor-pointer"
                                >
                                <input
                                    type="text"
                                    wire:model.live="branding.primary_color"
                                    class="flex-1 border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono"
                                    placeholder="#000000"
                                >
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-600 mb-2">Theme Presets</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach([
                                    ['#F97316', 'Pulse Orange'],
                                    ['#3B82F6', 'Blue'],
                                    ['#10B981', 'Green'],
                                    ['#8B5CF6', 'Purple'],
                                    ['#EC4899', 'Pink'],
                                    ['#6B7280', 'Gray'],
                                ] as [$color, $name])
                                <button
                                    wire:click="$set('branding.primary_color', '{{ $color }}')"
                                    class="w-8 h-8 rounded-lg border-2 {{ ($branding['primary_color'] ?? '') === $color ? 'border-gray-900 ring-2 ring-offset-1 ring-gray-400' : 'border-transparent hover:border-gray-300' }}"
                                    style="background-color: {{ $color }}"
                                    title="{{ $name }}"
                                ></button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Data Mode --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Data Mode</h4>
                    <div class="flex gap-2">
                        <button
                            wire:click="$set('isLive', true)"
                            class="flex-1 px-3 py-3 text-sm rounded-lg border {{ $isLive ? 'border-green-500 bg-green-50' : 'border-gray-200 hover:bg-gray-50' }}"
                        >
                            <div class="flex items-center justify-center gap-2 {{ $isLive ? 'text-green-700' : 'text-gray-600' }}">
                                <span class="w-2 h-2 rounded-full {{ $isLive ? 'bg-green-500 animate-pulse' : 'bg-gray-300' }}"></span>
                                <span class="font-medium">Live</span>
                            </div>
                            <p class="text-xs {{ $isLive ? 'text-green-600' : 'text-gray-500' }} mt-1">Always current data</p>
                        </button>
                        <button
                            wire:click="$set('isLive', false)"
                            class="flex-1 px-3 py-3 text-sm rounded-lg border {{ !$isLive ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:bg-gray-50' }}"
                        >
                            <div class="flex items-center justify-center gap-2 {{ !$isLive ? 'text-blue-700' : 'text-gray-600' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-medium">Snapshot</span>
                            </div>
                            <p class="text-xs {{ !$isLive ? 'text-blue-600' : 'text-gray-500' }} mt-1">Frozen in time</p>
                        </button>
                    </div>
                </div>

                {{-- Logo Upload --}}
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Branding</h4>
                    <div class="border-2 border-dashed border-gray-300 rounded-xl p-4 text-center hover:border-pulse-orange-300 transition-colors cursor-pointer">
                        <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-sm text-gray-500">Upload logo</span>
                        <p class="text-xs text-gray-400 mt-1">PNG, JPG up to 2MB</p>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            {{-- PHASE 6: LAYERS PANEL --}}
            {{-- ═══════════════════════════════════════════════════════════════════ --}}
            <div x-show="activePanel === 'layers'" class="p-3 space-y-3">
                {{-- Header --}}
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-gray-900">Layers</h3>
                    <span class="text-xs text-gray-400">{{ count($elements) }} elements</span>
                </div>

                {{-- Helpful Hint --}}
                <p class="text-xs text-gray-500">
                    Reorder, hide, or lock your report elements.
                </p>

                {{-- Layers List --}}
                @if(count($elements) > 0)
                    <div class="space-y-1" x-data="{
                        draggingId: null,
                        dragOverId: null
                    }">
                        @foreach(array_reverse($elements) as $index => $element)
                            @php
                                $isSelected = $selectedElementId === $element['id'];
                                $isLocked = $element['config']['locked'] ?? false;
                                $isHidden = $element['config']['hidden'] ?? false;
                                $type = $element['type'] ?? 'unknown';
                                $icons = [
                                    'text' => '📝',
                                    'chart' => '📊',
                                    'metric_card' => '📈',
                                    'table' => '📋',
                                    'ai_text' => '✨',
                                    'image' => '🖼️',
                                    'spacer' => '↕️',
                                ];
                                $icon = $icons[$type] ?? '📦';
                                $title = $element['config']['title'] ?? $element['config']['label'] ?? ucfirst(str_replace('_', ' ', $type));
                            @endphp
                            <div
                                class="group flex items-center gap-2 px-2 py-1.5 rounded-lg cursor-pointer transition-colors {{ $isSelected ? 'bg-blue-50 border border-blue-200' : 'hover:bg-gray-50 border border-transparent' }} {{ $isHidden ? 'opacity-50' : '' }}"
                                wire:click="selectElement('{{ $element['id'] }}')"
                                draggable="true"
                                @dragstart="draggingId = '{{ $element['id'] }}'"
                                @dragend="draggingId = null; dragOverId = null"
                                @dragover.prevent="dragOverId = '{{ $element['id'] }}'"
                                @drop="$wire.reorderElements([...document.querySelectorAll('[data-layer-id]')].map(el => el.dataset.layerId))"
                                data-layer-id="{{ $element['id'] }}"
                                :class="dragOverId === '{{ $element['id'] }}' ? 'border-blue-400 border-t-2' : ''"
                            >
                                {{-- Drag Handle --}}
                                <div class="text-gray-300 cursor-grab active:cursor-grabbing">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M7 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 2zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 7 14zm6-8a2 2 0 1 0-.001-4.001A2 2 0 0 0 13 6zm0 2a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 8zm0 6a2 2 0 1 0 .001 4.001A2 2 0 0 0 13 14z"/>
                                    </svg>
                                </div>

                                {{-- Icon --}}
                                <span class="text-sm">{{ $icon }}</span>

                                {{-- Title --}}
                                <span class="flex-1 text-sm truncate {{ $isSelected ? 'text-blue-700 font-medium' : 'text-gray-700' }}">
                                    {{ \Illuminate\Support\Str::limit($title, 18) }}
                                </span>

                                {{-- Actions --}}
                                <div class="flex items-center gap-0.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                    {{-- Visibility Toggle --}}
                                    <button
                                        wire:click.stop="toggleElementVisibility('{{ $element['id'] }}')"
                                        class="p-1 rounded {{ $isHidden ? 'text-gray-400' : 'text-gray-500 hover:text-gray-700' }}"
                                        title="{{ $isHidden ? 'Show' : 'Hide' }}"
                                    >
                                        @if($isHidden)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        @endif
                                    </button>

                                    {{-- Lock Toggle --}}
                                    <button
                                        wire:click.stop="toggleElementLock('{{ $element['id'] }}')"
                                        class="p-1 rounded {{ $isLocked ? 'text-yellow-500' : 'text-gray-500 hover:text-gray-700' }}"
                                        title="{{ $isLocked ? 'Unlock' : 'Lock' }}"
                                    >
                                        @if($isLocked)
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        @else
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                                            </svg>
                                        @endif
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <p class="text-sm text-gray-500">No layers yet</p>
                        <p class="text-xs text-gray-400 mt-1">Add elements to see them here</p>
                    </div>
                @endif

                {{-- Quick Actions --}}
                @if(count($elements) > 0)
                    <div class="border-t border-gray-200 pt-3 mt-3">
                        <div class="flex gap-2">
                            <button
                                wire:click="selectAll"
                                class="flex-1 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                            >
                                Select All
                            </button>
                            <button
                                wire:click="clearSelection"
                                class="flex-1 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                            >
                                Deselect
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</aside>
