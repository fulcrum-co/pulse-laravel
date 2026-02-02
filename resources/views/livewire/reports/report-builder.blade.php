<div
    class="h-screen flex flex-col"
    x-data
    @open-preview.window="window.open($event.detail.url, '_blank')"
>
    <!-- Header -->
    <header class="h-14 bg-white border-b border-gray-200 px-4 flex items-center justify-between flex-shrink-0 z-50">
        <div class="flex items-center gap-4">
            <!-- Back button -->
            <a href="{{ route('reports.index') }}" class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>

            <!-- Report name (editable) -->
            <input
                type="text"
                wire:model.blur="reportName"
                class="text-lg font-semibold text-gray-900 bg-transparent border-0 focus:ring-0 focus:outline-none hover:bg-gray-50 focus:bg-gray-50 px-2 py-1 rounded-lg -ml-2"
                placeholder="Untitled Report"
            >

            <!-- Status badge -->
            @if($status === 'published')
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                    Published
                </span>
            @else
                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                    Draft
                </span>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <!-- Undo/Redo -->
            <div class="flex items-center border-r border-gray-200 pr-2 mr-2">
                <button
                    wire:click="undo"
                    @disabled(!$canUndo)
                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    title="Undo (Ctrl+Z)"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                    </svg>
                </button>
                <button
                    wire:click="redo"
                    @disabled(!$canRedo)
                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    title="Redo (Ctrl+Shift+Z)"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10h-10a8 8 0 00-8 8v2M21 10l-6 6m6-6l-6-6"/>
                    </svg>
                </button>
            </div>

            <!-- Zoom Controls -->
            <div class="flex items-center border-r border-gray-200 pr-2 mr-2">
                <button
                    wire:click="zoomOut"
                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    title="Zoom Out"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM13 10H7"/>
                    </svg>
                </button>
                <button
                    wire:click="resetZoom"
                    class="px-2 py-1 text-xs font-medium text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded transition-colors min-w-[48px]"
                    title="Reset Zoom"
                >
                    {{ number_format($canvasZoom * 100) }}%
                </button>
                <button
                    wire:click="zoomIn"
                    class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
                    title="Zoom In"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v6m3-3H7"/>
                    </svg>
                </button>
            </div>

            <!-- Save button -->
            <button
                wire:click="save"
                wire:loading.attr="disabled"
                wire:target="save"
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="save">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                </span>
                <span wire:loading wire:target="save">
                    <svg class="w-4 h-4 mr-1.5 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                <span wire:loading.remove wire:target="save">Save</span>
                <span wire:loading wire:target="save">Saving...</span>
            </button>

            <!-- Preview button -->
            <button
                wire:click="previewReport"
                wire:loading.attr="disabled"
                wire:target="previewReport"
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors disabled:opacity-50"
            >
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Preview
            </button>

            <!-- Share Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button
                    @click="open = !open"
                    @click.away="open = false"
                    class="inline-flex items-center px-4 py-1.5 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors"
                >
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                    Share
                    <svg class="w-4 h-4 ml-1.5 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-100"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-75"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-200 py-1 z-50"
                    style="display: none;"
                >
                    <!-- Publish to Website -->
                    <button
                        wire:click="openPublishModal"
                        @click="open = false"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <div class="font-medium">Publish to Web</div>
                            <div class="text-xs text-gray-500">Create a shareable link</div>
                        </div>
                    </button>

                    <!-- Download PDF -->
                    <button
                        wire:click="$dispatch('exportPdf')"
                        @click="open = false"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <div class="font-medium">Download PDF</div>
                            <div class="text-xs text-gray-500">Export for printing</div>
                        </div>
                    </button>

                    @if($canPush && $reportId)
                    <div class="border-t border-gray-100 my-1"></div>

                    <!-- Push to Schools -->
                    <button
                        wire:click="openPushModal"
                        @click="open = false"
                        class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                    >
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                        <div class="text-left">
                            <div class="font-medium">Push to Schools</div>
                            <div class="text-xs text-gray-500">Share with organizations</div>
                        </div>
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Filter Bar -->
    <div class="h-12 bg-gray-50 border-b border-gray-200 px-4 flex items-center gap-4 flex-shrink-0">
        <!-- Scope selector -->
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Scope:</span>
            <select
                wire:model.live="filters.scope"
                class="border-0 bg-white shadow-sm rounded-lg px-3 py-1.5 text-sm focus:ring-pulse-orange-500"
            >
                <option value="individual">Individual</option>
                <option value="cohort">Cohort</option>
                <option value="school">School-wide</option>
            </select>
        </div>

        <!-- Contact selector (only for individual scope) -->
        @if($filters['scope'] === 'individual')
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Student:</span>
                <select
                    wire:model.live="filters.contact_id"
                    wire:change="setContactFilter($event.target.value, 'student')"
                    class="border-0 bg-white shadow-sm rounded-lg px-3 py-1.5 text-sm focus:ring-pulse-orange-500 min-w-[200px]"
                >
                    <option value="">Select a student...</option>
                    @foreach($this->availableStudents as $student)
                        <option value="{{ $student['id'] }}">{{ $student['name'] }}</option>
                    @endforeach
                </select>
            </div>
        @endif

        <!-- Cohort filters (only for cohort scope) -->
        @if($filters['scope'] === 'cohort')
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Grade:</span>
                <select
                    wire:model.live="filters.grade_level"
                    class="border-0 bg-white shadow-sm rounded-lg px-3 py-1.5 text-sm focus:ring-pulse-orange-500"
                >
                    <option value="">All Grades</option>
                    @foreach(range(6, 12) as $grade)
                        <option value="{{ $grade }}">Grade {{ $grade }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-sm text-gray-500">Risk:</span>
                <select
                    wire:model.live="filters.risk_level"
                    class="border-0 bg-white shadow-sm rounded-lg px-3 py-1.5 text-sm focus:ring-pulse-orange-500"
                >
                    <option value="">All Levels</option>
                    <option value="good">Good Standing</option>
                    <option value="low">Low Risk</option>
                    <option value="high">High Risk</option>
                </select>
            </div>
        @endif

        <div class="h-6 w-px bg-gray-300"></div>

        <!-- Date range -->
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500">Period:</span>
            <select
                wire:model.live="filters.date_range"
                wire:change="setDateRange($event.target.value)"
                class="border-0 bg-white shadow-sm rounded-lg px-3 py-1.5 text-sm focus:ring-pulse-orange-500"
            >
                <option value="3_months">Last 3 months</option>
                <option value="6_months">Last 6 months</option>
                <option value="12_months">Last 12 months</option>
                <option value="2_years">Last 2 years</option>
                <option value="all">All time</option>
            </select>
        </div>

        <div class="flex-1"></div>

        <!-- Data freshness indicator -->
        <div class="flex items-center gap-2">
            @if($isLive)
                <span class="flex items-center gap-1.5 px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-medium">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                    Live Data
                </span>
            @else
                <span class="flex items-center gap-1.5 px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-medium">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Snapshot
                </span>
            @endif
        </div>
    </div>

    <!-- Main Editor Area -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Canva-Style Sidebar -->
        @include('livewire.reports.partials.canva-sidebar')

        {{-- OLD SIDEBAR REMOVED - Keeping rest of file intact --}}
        {{-- The new canva-sidebar partial replaces the old Elements/Settings tabs --}}

        {{-- Hidden div to preserve the old code structure for elements that follow --}}
        <div class="hidden">
            {{-- Old sidebar content preserved for reference during migration --}}
            @if(false && $activeTab === 'elements')
                    <!-- Element Types -->
                    <div class="space-y-4">
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Content</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    wire:click="addElement('text')"
                                    class="flex flex-col items-center p-3 border border-gray-200 rounded-lg hover:border-pulse-orange-300 hover:bg-pulse-orange-50 transition-colors"
                                >
                                    <svg class="w-6 h-6 text-gray-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/>
                                    </svg>
                                    <span class="text-xs text-gray-600">Text</span>
                                </button>
                                <button
                                    wire:click="addElement('image')"
                                    class="flex flex-col items-center p-3 border border-gray-200 rounded-lg hover:border-pulse-orange-300 hover:bg-pulse-orange-50 transition-colors"
                                >
                                    <svg class="w-6 h-6 text-gray-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-xs text-gray-600">Image</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Data</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    wire:click="addElement('chart')"
                                    class="flex flex-col items-center p-3 border border-gray-200 rounded-lg hover:border-pulse-orange-300 hover:bg-pulse-orange-50 transition-colors"
                                >
                                    <svg class="w-6 h-6 text-gray-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                                    </svg>
                                    <span class="text-xs text-gray-600">Chart</span>
                                </button>
                                <button
                                    wire:click="addElement('table')"
                                    class="flex flex-col items-center p-3 border border-gray-200 rounded-lg hover:border-pulse-orange-300 hover:bg-pulse-orange-50 transition-colors"
                                >
                                    <svg class="w-6 h-6 text-gray-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-xs text-gray-600">Table</span>
                                </button>
                                <button
                                    wire:click="addElement('metric_card')"
                                    class="flex flex-col items-center p-3 border border-gray-200 rounded-lg hover:border-pulse-orange-300 hover:bg-pulse-orange-50 transition-colors"
                                >
                                    <svg class="w-6 h-6 text-gray-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    <span class="text-xs text-gray-600">Metric</span>
                                </button>
                                <button
                                    wire:click="addElement('ai_text')"
                                    class="flex flex-col items-center p-3 border border-purple-200 bg-purple-50 rounded-lg hover:border-purple-300 hover:bg-purple-100 transition-colors"
                                >
                                    <svg class="w-6 h-6 text-purple-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                    <span class="text-xs text-purple-600 font-medium">AI Text</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Layout</h4>
                            <div class="grid grid-cols-2 gap-2">
                                <button
                                    wire:click="addElement('spacer')"
                                    class="flex flex-col items-center p-3 border border-gray-200 rounded-lg hover:border-pulse-orange-300 hover:bg-pulse-orange-50 transition-colors"
                                >
                                    <svg class="w-6 h-6 text-gray-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                                    </svg>
                                    <span class="text-xs text-gray-600">Spacer</span>
                                </button>
                            </div>
                        </div>

                        <!-- Templates button -->
                        <div class="pt-4 border-t border-gray-200">
                            <button
                                wire:click="$set('showTemplateGallery', true)"
                                class="w-full flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium text-pulse-orange-600 bg-pulse-orange-50 rounded-lg hover:bg-pulse-orange-100 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                                </svg>
                                Browse Templates
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Settings Tab -->
                    <div class="space-y-6">
                        <!-- Page Settings -->
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Page Settings</h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Size</label>
                                    <select wire:model.live="pageSettings.size" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
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
                                            class="flex-1 px-3 py-2 text-sm rounded-lg border {{ $pageSettings['orientation'] === 'portrait' ? 'border-pulse-orange-500 bg-pulse-orange-50 text-pulse-orange-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}"
                                        >
                                            Portrait
                                        </button>
                                        <button
                                            wire:click="$set('pageSettings.orientation', 'landscape')"
                                            class="flex-1 px-3 py-2 text-sm rounded-lg border {{ $pageSettings['orientation'] === 'landscape' ? 'border-pulse-orange-500 bg-pulse-orange-50 text-pulse-orange-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}"
                                        >
                                            Landscape
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Settings -->
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Data Settings</h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Data Mode</label>
                                    <div class="flex gap-2">
                                        <button
                                            wire:click="$set('isLive', true)"
                                            class="flex-1 px-3 py-2 text-sm rounded-lg border {{ $isLive ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}"
                                        >
                                            <span class="block font-medium">Live</span>
                                            <span class="block text-xs opacity-75">Always current</span>
                                        </button>
                                        <button
                                            wire:click="$set('isLive', false)"
                                            class="flex-1 px-3 py-2 text-sm rounded-lg border {{ !$isLive ? 'border-blue-500 bg-blue-50 text-blue-700' : 'border-gray-300 text-gray-600 hover:bg-gray-50' }}"
                                        >
                                            <span class="block font-medium">Snapshot</span>
                                            <span class="block text-xs opacity-75">Frozen in time</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Branding -->
                        <div>
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Branding</h4>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Primary Color</label>
                                    <input
                                        type="color"
                                        wire:model.live="branding.primary_color"
                                        class="w-full h-10 rounded-lg border border-gray-300 cursor-pointer"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-1">Logo</label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center hover:border-pulse-orange-300 transition-colors cursor-pointer">
                                        <svg class="w-8 h-8 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span class="text-sm text-gray-500">Upload logo</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            {{-- End of old sidebar code preserved for reference --}}
        </div>
        {{-- End hidden div --}}

        <!-- Canvas Area -->
        <main class="flex-1 overflow-auto bg-gray-100 p-8" data-canvas-wrapper wire:click="selectElement(null)">
            <!-- Zoom container -->
            <div class="canvas-zoom-container" style="transform: scale({{ $canvasZoom }}); transform-origin: top center; transition: transform 0.2s ease;">
                <div
                    data-report-canvas
                    class="bg-white shadow-lg mx-auto canvas-grid relative"
                    style="width: 800px; min-height: 1000px;"
                    wire:click.stop
                >
                @foreach($elements as $element)
                    @php
                        $isLocked = $element['config']['locked'] ?? false;
                        $isHidden = $element['config']['hidden'] ?? false;
                    @endphp
                    @if(!$isHidden)
                    <div
                        data-element-id="{{ $element['id'] }}"
                        data-locked="{{ $isLocked ? 'true' : 'false' }}"
                        wire:click.stop="selectElement('{{ $element['id'] }}')"
                        wire:click.shift.stop="toggleInSelection('{{ $element['id'] }}')"
                        class="absolute {{ $isLocked ? 'element-locked' : 'cursor-move' }} {{ $selectedElementId === $element['id'] ? 'element-selected' : '' }} {{ in_array($element['id'], $selectedElementIds) ? 'multi-selected' : '' }}"
                        style="
                            transform: translate({{ $element['position']['x'] ?? 0 }}px, {{ $element['position']['y'] ?? 0 }}px);
                            width: {{ $element['size']['width'] ?? 200 }}px;
                            height: {{ $element['size']['height'] ?? 100 }}px;
                            background-color: {{ $element['styles']['backgroundColor'] ?? 'transparent' }};
                            border-radius: {{ $element['styles']['borderRadius'] ?? 0 }}px;
                            padding: {{ $element['styles']['padding'] ?? 0 }}px;
                            @if(isset($element['styles']['borderWidth']) && $element['styles']['borderWidth'] > 0)
                                border: {{ $element['styles']['borderWidth'] }}px solid {{ $element['styles']['borderColor'] ?? '#E5E7EB' }};
                            @endif
                        "
                        data-x="{{ $element['position']['x'] ?? 0 }}"
                        data-y="{{ $element['position']['y'] ?? 0 }}"
                    >
                        @switch($element['type'])
                            @case('text')
                                @if($editingTextElementId === $element['id'])
                                    {{-- Edit mode: inline textarea --}}
                                    <textarea
                                        x-data="{ content: @js($element['config']['content'] ?? '') }"
                                        x-init="$nextTick(() => { $el.focus(); $el.select(); })"
                                        x-model="content"
                                        @blur="$wire.updateTextContent('{{ $element['id'] }}', content); $wire.finishEditingText();"
                                        @keydown.escape.prevent="$wire.finishEditingText()"
                                        @click.stop
                                        @dblclick.stop
                                        class="w-full h-full p-2 border-2 border-pulse-orange-500 rounded resize-none focus:outline-none text-sm bg-white"
                                    ></textarea>
                                @else
                                    {{-- View mode --}}
                                    <div
                                        class="w-full h-full overflow-hidden prose prose-sm max-w-none cursor-text"
                                        @dblclick.stop="$wire.startEditingText('{{ $element['id'] }}')"
                                    >
                                        {!! $element['config']['content'] ?? '<p class="text-gray-400">Double-click to edit...</p>' !!}
                                    </div>
                                @endif
                                @break

                            @case('chart')
                                @php
                                    $elementChartData = $chartData[$element['id']] ?? [];
                                @endphp
                                <div
                                    class="w-full h-full flex flex-col"
                                    data-chart-element="{{ $element['id'] }}"
                                    data-chart-config="{{ json_encode($element['config'] ?? []) }}"
                                    data-chart-data="{{ json_encode($elementChartData) }}"
                                >
                                    @if(isset($element['config']['title']))
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">{{ $element['config']['title'] }}</h4>
                                    @endif
                                    <div class="flex-1 relative">
                                        @if(empty($elementChartData))
                                            <div class="absolute inset-0 flex items-center justify-center bg-gray-50 rounded">
                                                <div class="text-center">
                                                    <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
                                                    </svg>
                                                    <span class="text-sm text-gray-400">Select a contact to view data</span>
                                                </div>
                                            </div>
                                        @endif
                                        <canvas></canvas>
                                    </div>
                                </div>
                                @break

                            @case('table')
                                <div class="w-full h-full flex flex-col">
                                    @if(isset($element['config']['title']))
                                        <h4 class="text-sm font-medium text-gray-700 mb-2">{{ $element['config']['title'] }}</h4>
                                    @endif
                                    <div class="flex-1 flex items-center justify-center bg-gray-50 rounded">
                                        <span class="text-sm text-gray-400">Table: {{ implode(', ', $element['config']['columns'] ?? []) }}</span>
                                    </div>
                                </div>
                                @break

                            @case('metric_card')
                                @php
                                    $metricKey = $element['config']['metric_key'] ?? 'gpa';
                                    $metricValue = $this->getMetricCardValue($metricKey);
                                    $formattedValue = $metricValue !== null ? number_format($metricValue, 2) : '--';
                                @endphp
                                <div class="w-full h-full flex flex-col justify-center">
                                    <span class="text-xs text-gray-500 uppercase tracking-wider">{{ $element['config']['label'] ?? ucwords(str_replace('_', ' ', $metricKey)) }}</span>
                                    <span class="text-2xl font-bold text-gray-900 mt-1">{{ $formattedValue }}</span>
                                    @if($element['config']['show_trend'] ?? false)
                                        <span class="text-xs text-green-600 mt-1 flex items-center gap-1">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/>
                                            </svg>
                                            Trend
                                        </span>
                                    @endif
                                </div>
                                @break

                            @case('ai_text')
                                <div class="w-full h-full flex flex-col" wire:loading.class="opacity-50" wire:target="generateAiContent('{{ $element['id'] }}')">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="flex items-center gap-2">
                                            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                            </svg>
                                            <span class="text-xs font-medium text-purple-600">AI Generated</span>
                                        </div>
                                        @if($selectedElementId === $element['id'])
                                            <button
                                                wire:click.stop="generateAiContent('{{ $element['id'] }}')"
                                                class="px-2 py-1 text-xs bg-purple-500 text-white rounded hover:bg-purple-600 transition-colors flex items-center gap-1"
                                            >
                                                <span wire:loading.remove wire:target="generateAiContent('{{ $element['id'] }}')">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                                    </svg>
                                                </span>
                                                <span wire:loading wire:target="generateAiContent('{{ $element['id'] }}')">
                                                    <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                    </svg>
                                                </span>
                                                <span wire:loading.remove wire:target="generateAiContent('{{ $element['id'] }}')">Generate</span>
                                                <span wire:loading wire:target="generateAiContent('{{ $element['id'] }}')">Generating...</span>
                                            </button>
                                        @endif
                                    </div>
                                    <div class="flex-1 text-sm text-gray-600 overflow-auto">
                                        @if($element['config']['generated_content'] ?? null)
                                            {!! nl2br(e($element['config']['generated_content'])) !!}
                                            @if($element['config']['generated_at'] ?? null)
                                                <p class="text-xs text-gray-400 mt-2">Generated {{ \Carbon\Carbon::parse($element['config']['generated_at'])->diffForHumans() }}</p>
                                            @endif
                                        @else
                                            <div class="flex flex-col items-center justify-center h-full text-center">
                                                <svg class="w-8 h-8 text-purple-200 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                                </svg>
                                                <span class="text-gray-400 italic">Select this element and click "Generate" to create AI content</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @break

                            @case('image')
                                <div class="w-full h-full flex items-center justify-center bg-gray-100 rounded">
                                    @if($element['config']['src'] ?? null)
                                        <img src="{{ $element['config']['src'] }}" alt="{{ $element['config']['alt'] ?? '' }}" class="max-w-full max-h-full object-{{ $element['config']['fit'] ?? 'contain' }}">
                                    @else
                                        <div class="text-center">
                                            <svg class="w-12 h-12 text-gray-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="text-xs text-gray-400 mt-1 block">Upload image</span>
                                        </div>
                                    @endif
                                </div>
                                @break

                            @case('spacer')
                                <div class="w-full h-full border border-dashed border-gray-300 flex items-center justify-center">
                                    <span class="text-xs text-gray-400">Spacer</span>
                                </div>
                                @break

                            @default
                                <div class="w-full h-full flex items-center justify-center bg-gray-100 rounded">
                                    <span class="text-sm text-gray-400">{{ $element['type'] }}</span>
                                </div>
                        @endswitch

                        <!-- Resize handles (only show when selected and not locked) -->
                        @if($selectedElementId === $element['id'] && !$isLocked)
                            <div class="resize-handle resize-handle-br"></div>
                        @endif
                    </div>
                    @endif {{-- End hidden check --}}
                @endforeach

                @if(empty($elements))
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="text-center">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-gray-500 mb-4">Drag elements here to build your report</p>
                            <button
                                wire:click="$set('showTemplateGallery', true)"
                                class="text-pulse-orange-600 hover:text-pulse-orange-700 font-medium"
                            >
                                Or start with a template
                            </button>
                        </div>
                    </div>
                @endif
            </div>
            </div><!-- End zoom container -->
        </main>

        <!-- Right Sidebar - Properties -->
        @if($selectedElement)
            <aside class="w-72 bg-white border-l border-gray-200 flex flex-col flex-shrink-0">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="font-medium text-gray-900 capitalize">{{ str_replace('_', ' ', $selectedElement['type']) }}</h3>
                        <button
                            wire:click="deleteElement('{{ $selectedElement['id'] }}')"
                            class="p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto custom-scrollbar p-4 space-y-4">
                    <!-- Element-specific properties -->
                    @php
                        $elementIndex = collect($elements)->search(fn($e) => $e['id'] === $selectedElementId);
                    @endphp

                    @switch($selectedElement['type'])
                        @case('text')
                            <div x-data="{ content: @js($selectedElement['config']['content'] ?? '') }">
                                <label class="block text-sm text-gray-600 mb-1">Content</label>
                                <textarea
                                    x-model="content"
                                    @change="$wire.updateTextContent('{{ $selectedElement['id'] }}', content)"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                    rows="4"
                                    placeholder="Enter your text content..."
                                ></textarea>
                                <p class="text-xs text-gray-400 mt-1">Supports basic HTML tags</p>
                            </div>
                            @break

                        @case('chart')
                            <div x-data="{
                                chartType: @js($selectedElement['config']['chart_type'] ?? 'line'),
                                title: @js($selectedElement['config']['title'] ?? ''),
                                metricKeys: @js($selectedElement['config']['metric_keys'] ?? [])
                            }">
                                <div class="mb-4">
                                    <label class="block text-sm text-gray-600 mb-1">Chart Type</label>
                                    <select
                                        x-model="chartType"
                                        @change="$wire.updateChartConfig('{{ $selectedElement['id'] }}', chartType, metricKeys, title)"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                    >
                                        <option value="line">Line</option>
                                        <option value="bar">Bar</option>
                                        <option value="pie">Pie</option>
                                        <option value="doughnut">Doughnut</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm text-gray-600 mb-1">Title</label>
                                    <input
                                        type="text"
                                        x-model="title"
                                        @change="$wire.updateChartConfig('{{ $selectedElement['id'] }}', chartType, metricKeys, title)"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                        placeholder="Chart title"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-2">Metrics</label>
                                    <div class="space-y-2">
                                        @foreach(['gpa', 'attendance_rate', 'wellness_score', 'engagement_score', 'plan_progress'] as $metric)
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    :checked="metricKeys.includes('{{ $metric }}')"
                                                    @change="
                                                        if ($event.target.checked) {
                                                            metricKeys.push('{{ $metric }}');
                                                        } else {
                                                            metricKeys = metricKeys.filter(k => k !== '{{ $metric }}');
                                                        }
                                                        $wire.updateChartConfig('{{ $selectedElement['id'] }}', chartType, metricKeys, title);
                                                    "
                                                    class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                                                >
                                                <span class="text-sm text-gray-700">{{ ucwords(str_replace('_', ' ', $metric)) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @break

                        @case('metric_card')
                            <div x-data="{
                                metricKey: @js($selectedElement['config']['metric_key'] ?? 'gpa'),
                                label: @js($selectedElement['config']['label'] ?? ''),
                                showTrend: @js($selectedElement['config']['show_trend'] ?? true)
                            }">
                                <div class="mb-4">
                                    <label class="block text-sm text-gray-600 mb-1">Metric</label>
                                    <select
                                        x-model="metricKey"
                                        @change="
                                            if (!label) label = metricKey.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                                            $wire.updateMetricCardConfig('{{ $selectedElement['id'] }}', metricKey, label, showTrend);
                                        "
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                    >
                                        <option value="gpa">GPA</option>
                                        <option value="attendance_rate">Attendance Rate</option>
                                        <option value="wellness_score">Wellness Score</option>
                                        <option value="engagement_score">Engagement Score</option>
                                        <option value="plan_progress">Plan Progress</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm text-gray-600 mb-1">Label</label>
                                    <input
                                        type="text"
                                        x-model="label"
                                        @change="$wire.updateMetricCardConfig('{{ $selectedElement['id'] }}', metricKey, label, showTrend)"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                        placeholder="Display label"
                                    >
                                </div>
                                <div>
                                    <label class="flex items-center gap-2 cursor-pointer">
                                        <input
                                            type="checkbox"
                                            x-model="showTrend"
                                            @change="$wire.updateMetricCardConfig('{{ $selectedElement['id'] }}', metricKey, label, showTrend)"
                                            class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                                        >
                                        <span class="text-sm text-gray-700">Show trend indicator</span>
                                    </label>
                                </div>
                            </div>
                            @break

                        @case('ai_text')
                            <div x-data="{
                                prompt: @js($selectedElement['config']['prompt'] ?? ''),
                                format: @js($selectedElement['config']['format'] ?? 'narrative')
                            }">
                                <div class="mb-4">
                                    <label class="block text-sm text-gray-600 mb-1">AI Prompt</label>
                                    <textarea
                                        x-model="prompt"
                                        @change="$wire.updateElementConfig('{{ $selectedElement['id'] }}', { prompt: prompt, format: format })"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                        rows="3"
                                        placeholder="Describe what you want the AI to write..."
                                    ></textarea>
                                    <p class="text-xs text-gray-400 mt-1">The AI will use available data to generate content</p>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm text-gray-600 mb-1">Format</label>
                                    <select
                                        x-model="format"
                                        @change="$wire.updateElementConfig('{{ $selectedElement['id'] }}', { prompt: prompt, format: format })"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                    >
                                        <option value="narrative">Narrative Paragraph</option>
                                        <option value="bullets">Bullet Points</option>
                                        <option value="executive_summary">Executive Summary</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm text-gray-600 mb-2">Context Metrics</label>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($selectedElement['config']['context_metrics'] ?? ['gpa', 'attendance_rate', 'wellness_score'] as $metric)
                                            <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded">{{ ucwords(str_replace('_', ' ', $metric)) }}</span>
                                        @endforeach
                                    </div>
                                </div>
                                <button
                                    wire:click="generateAiContent('{{ $selectedElement['id'] }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="generateAiContent"
                                    class="w-full px-4 py-2 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors text-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
                                >
                                    <span wire:loading.remove wire:target="generateAiContent('{{ $selectedElement['id'] }}')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                        </svg>
                                    </span>
                                    <span wire:loading wire:target="generateAiContent('{{ $selectedElement['id'] }}')">
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </span>
                                    <span wire:loading.remove wire:target="generateAiContent('{{ $selectedElement['id'] }}')">Generate Content</span>
                                    <span wire:loading wire:target="generateAiContent('{{ $selectedElement['id'] }}')">Generating...</span>
                                </button>
                                @if($selectedElement['config']['generated_content'] ?? null)
                                    <p class="text-xs text-green-600 mt-2 text-center">Content generated successfully</p>
                                @endif
                            </div>
                            @break

                        @case('table')
                            <div>
                                <div class="mb-4">
                                    <label class="block text-sm text-gray-600 mb-1">Title</label>
                                    <input
                                        type="text"
                                        wire:model.blur="elements.{{ $elementIndex }}.config.title"
                                        wire:change="commitElementChange"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                        placeholder="Table title"
                                    >
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 mb-2">Columns</label>
                                    <div class="space-y-2">
                                        @foreach(['name', 'email', 'grade_level', 'gpa', 'attendance_rate', 'wellness_score', 'risk_level'] as $col)
                                            <label class="flex items-center gap-2 cursor-pointer">
                                                <input
                                                    type="checkbox"
                                                    {{ in_array($col, $selectedElement['config']['columns'] ?? []) ? 'checked' : '' }}
                                                    class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                                                >
                                                <span class="text-sm text-gray-700">{{ ucwords(str_replace('_', ' ', $col)) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @break

                        @case('image')
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Image URL</label>
                                <input
                                    type="text"
                                    wire:model.blur="elements.{{ $elementIndex }}.config.src"
                                    wire:change="commitElementChange"
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                    placeholder="https://..."
                                >
                                <p class="text-xs text-gray-400 mt-1">Enter an image URL or upload below</p>

                                <div class="mt-3">
                                    <label class="block text-sm text-gray-600 mb-1">Alt Text</label>
                                    <input
                                        type="text"
                                        wire:model.blur="elements.{{ $elementIndex }}.config.alt"
                                        wire:change="commitElementChange"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                        placeholder="Image description"
                                    >
                                </div>

                                <div class="mt-3">
                                    <label class="block text-sm text-gray-600 mb-1">Fit</label>
                                    <select
                                        wire:model.live="elements.{{ $elementIndex }}.config.fit"
                                        wire:change="commitElementChange"
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                    >
                                        <option value="contain">Contain</option>
                                        <option value="cover">Cover</option>
                                        <option value="fill">Fill</option>
                                    </select>
                                </div>
                            </div>
                            @break

                        @default
                            <p class="text-sm text-gray-500">No properties available for this element type.</p>
                    @endswitch

                    <!-- Common style properties -->
                    <div class="pt-4 border-t border-gray-200" x-data="{
                        bgColor: @js($selectedElement['styles']['backgroundColor'] ?? 'transparent'),
                        borderRadius: @js($selectedElement['styles']['borderRadius'] ?? 0),
                        padding: @js($selectedElement['styles']['padding'] ?? 0),
                        borderWidth: @js($selectedElement['styles']['borderWidth'] ?? 0),
                        borderColor: @js($selectedElement['styles']['borderColor'] ?? '#E5E7EB')
                    }">
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Style</h4>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Background</label>
                                <div class="flex gap-2">
                                    <input
                                        type="color"
                                        x-model="bgColor"
                                        @change="$wire.updateElementStyles('{{ $selectedElement['id'] }}', { backgroundColor: bgColor })"
                                        class="w-10 h-8 rounded border border-gray-300 cursor-pointer"
                                    >
                                    <input
                                        type="text"
                                        x-model="bgColor"
                                        @change="$wire.updateElementStyles('{{ $selectedElement['id'] }}', { backgroundColor: bgColor })"
                                        class="flex-1 border border-gray-300 rounded-lg px-2 py-1 text-sm"
                                        placeholder="#ffffff"
                                    >
                                    <button
                                        @click="bgColor = 'transparent'; $wire.updateElementStyles('{{ $selectedElement['id'] }}', { backgroundColor: 'transparent' })"
                                        class="px-2 py-1 text-xs text-gray-600 border border-gray-300 rounded hover:bg-gray-50"
                                        title="Set transparent"
                                    >
                                        None
                                    </button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Border Radius: <span x-text="borderRadius"></span>px</label>
                                <input
                                    type="range"
                                    min="0"
                                    max="24"
                                    x-model="borderRadius"
                                    @change="$wire.updateElementStyles('{{ $selectedElement['id'] }}', { borderRadius: parseInt(borderRadius) })"
                                    class="w-full accent-pulse-orange-500"
                                >
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Padding: <span x-text="padding"></span>px</label>
                                <input
                                    type="range"
                                    min="0"
                                    max="32"
                                    x-model="padding"
                                    @change="$wire.updateElementStyles('{{ $selectedElement['id'] }}', { padding: parseInt(padding) })"
                                    class="w-full accent-pulse-orange-500"
                                >
                            </div>
                            <div>
                                <label class="block text-sm text-gray-600 mb-1">Border Width: <span x-text="borderWidth"></span>px</label>
                                <input
                                    type="range"
                                    min="0"
                                    max="4"
                                    x-model="borderWidth"
                                    @change="$wire.updateElementStyles('{{ $selectedElement['id'] }}', { borderWidth: parseInt(borderWidth), borderColor: borderColor })"
                                    class="w-full accent-pulse-orange-500"
                                >
                            </div>
                            <div x-show="borderWidth > 0">
                                <label class="block text-sm text-gray-600 mb-1">Border Color</label>
                                <div class="flex gap-2">
                                    <input
                                        type="color"
                                        x-model="borderColor"
                                        @change="$wire.updateElementStyles('{{ $selectedElement['id'] }}', { borderColor: borderColor })"
                                        class="w-10 h-8 rounded border border-gray-300 cursor-pointer"
                                    >
                                    <input
                                        type="text"
                                        x-model="borderColor"
                                        @change="$wire.updateElementStyles('{{ $selectedElement['id'] }}', { borderColor: borderColor })"
                                        class="flex-1 border border-gray-300 rounded-lg px-2 py-1 text-sm"
                                        placeholder="#E5E7EB"
                                    >
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="pt-4 border-t border-gray-200 space-y-2">
                        <button
                            wire:click="duplicateElement('{{ $selectedElement['id'] }}')"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            Duplicate
                        </button>
                        <button
                            wire:click="deleteElement('{{ $selectedElement['id'] }}')"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete
                        </button>
                    </div>
                </div>
            </aside>
        @endif
    </div>

    <!-- Enhanced Template Gallery Modal -->
    @if($showTemplateGallery)
        <div
            class="fixed inset-0 z-50 overflow-y-auto"
            x-data="{
                activeCategory: 'all',
                searchQuery: '',
                get filteredTemplates() {
                    return @js($templates).filter(t => {
                        const matchesCategory = this.activeCategory === 'all' || t.category === this.activeCategory;
                        const matchesSearch = !this.searchQuery ||
                            t.name.toLowerCase().includes(this.searchQuery.toLowerCase()) ||
                            t.description.toLowerCase().includes(this.searchQuery.toLowerCase());
                        return matchesCategory && matchesSearch;
                    });
                }
            }"
            x-init="$el.querySelector('input[type=search]')?.focus()"
        >
            <div class="flex items-center justify-center min-h-screen px-4 py-8">
                <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="$set('showTemplateGallery', false)"></div>

                <div class="relative bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[85vh] overflow-hidden flex flex-col">
                    {{-- Header --}}
                    <div class="p-6 border-b border-gray-200 flex-shrink-0">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <h2 class="text-2xl font-bold text-gray-900">Choose a Template</h2>
                                <p class="text-sm text-gray-500 mt-1">Start with a pre-built template or create from scratch</p>
                            </div>
                            <button wire:click="$set('showTemplateGallery', false)" class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {{-- Search and Filters --}}
                        <div class="flex items-center gap-4">
                            <div class="relative flex-1 max-w-md">
                                <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input
                                    type="search"
                                    x-model="searchQuery"
                                    placeholder="Search templates..."
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                                >
                            </div>

                            <div class="flex items-center gap-1 p-1 bg-gray-100 rounded-xl">
                                <button
                                    @click="activeCategory = 'all'"
                                    :class="activeCategory === 'all' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
                                >
                                    All
                                </button>
                                <button
                                    @click="activeCategory = 'student'"
                                    :class="activeCategory === 'student' ? 'bg-white shadow-sm text-blue-600' : 'text-gray-500 hover:text-gray-700'"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
                                >
                                    Student
                                </button>
                                <button
                                    @click="activeCategory = 'cohort'"
                                    :class="activeCategory === 'cohort' ? 'bg-white shadow-sm text-green-600' : 'text-gray-500 hover:text-gray-700'"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
                                >
                                    Cohort
                                </button>
                                <button
                                    @click="activeCategory = 'school'"
                                    :class="activeCategory === 'school' ? 'bg-white shadow-sm text-purple-600' : 'text-gray-500 hover:text-gray-700'"
                                    class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
                                >
                                    School
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- Content --}}
                    <div class="flex-1 overflow-y-auto p-6">
                        {{-- Quick Start --}}
                        <div class="mb-8">
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Quick Start</h3>
                            <div class="grid grid-cols-4 gap-4">
                                <button
                                    wire:click="startBlank"
                                    class="group flex flex-col items-center p-4 bg-gray-50 hover:bg-gray-100 border-2 border-dashed border-gray-300 hover:border-gray-400 rounded-xl transition-all"
                                >
                                    <div class="w-12 h-12 flex items-center justify-center mb-2">
                                        <svg class="w-8 h-8 text-gray-400 group-hover:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">Blank Canvas</span>
                                    <span class="text-xs text-gray-500 mt-1">Start from scratch</span>
                                </button>

                                @foreach(collect($templates)->where('category', 'student')->take(3) as $template)
                                <button
                                    wire:click="loadTemplate('{{ $template['id'] }}')"
                                    class="group flex flex-col items-center p-4 bg-gradient-to-br from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 border border-blue-200 hover:border-blue-300 rounded-xl transition-all"
                                >
                                    <div class="w-12 h-12 bg-white rounded-lg shadow-sm flex items-center justify-center mb-2 group-hover:shadow">
                                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900 text-center">{{ $template['name'] }}</span>
                                </button>
                                @endforeach
                            </div>
                        </div>

                        {{-- All Templates Grid --}}
                        <div>
                            <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">
                                <span x-text="activeCategory === 'all' ? 'All Templates' : (activeCategory.charAt(0).toUpperCase() + activeCategory.slice(1) + ' Templates')"></span>
                                <span class="text-gray-400 font-normal" x-text="'(' + filteredTemplates.length + ')'"></span>
                            </h3>

                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <template x-for="template in filteredTemplates" :key="template.id">
                                    <button
                                        @click="$wire.loadTemplate(template.id)"
                                        class="group text-left bg-white border border-gray-200 rounded-xl overflow-hidden hover:border-pulse-orange-300 hover:shadow-lg transition-all"
                                    >
                                        <div
                                            class="aspect-video flex items-center justify-center relative"
                                            :class="{
                                                'bg-gradient-to-br from-blue-100 to-blue-50': template.category === 'student',
                                                'bg-gradient-to-br from-green-100 to-green-50': template.category === 'cohort',
                                                'bg-gradient-to-br from-purple-100 to-purple-50': template.category === 'school',
                                                'bg-gradient-to-br from-gray-100 to-gray-50': template.category === 'custom'
                                            }"
                                        >
                                            {{-- Category Badge --}}
                                            <span
                                                class="absolute top-2 left-2 px-2 py-0.5 text-[10px] font-medium rounded-full"
                                                :class="{
                                                    'bg-blue-100 text-blue-700': template.category === 'student',
                                                    'bg-green-100 text-green-700': template.category === 'cohort',
                                                    'bg-purple-100 text-purple-700': template.category === 'school',
                                                    'bg-gray-100 text-gray-700': template.category === 'custom'
                                                }"
                                                x-text="template.category.charAt(0).toUpperCase() + template.category.slice(1)"
                                            ></span>

                                            {{-- Template Icon --}}
                                            <div x-show="template.id === 'blank'">
                                                <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                </svg>
                                            </div>
                                            <div x-show="template.id !== 'blank'" class="text-center">
                                                <svg
                                                    class="w-10 h-10 mx-auto transition-colors"
                                                    :class="{
                                                        'text-blue-400 group-hover:text-blue-500': template.category === 'student',
                                                        'text-green-400 group-hover:text-green-500': template.category === 'cohort',
                                                        'text-purple-400 group-hover:text-purple-500': template.category === 'school',
                                                        'text-gray-400 group-hover:text-gray-500': template.category === 'custom'
                                                    }"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                            </div>

                                            {{-- Hover overlay --}}
                                            <div class="absolute inset-0 bg-pulse-orange-500/0 group-hover:bg-pulse-orange-500/5 flex items-center justify-center transition-colors">
                                                <span class="opacity-0 group-hover:opacity-100 bg-pulse-orange-500 text-white px-3 py-1.5 rounded-lg text-sm font-medium shadow-lg transition-opacity">
                                                    Use Template
                                                </span>
                                            </div>
                                        </div>
                                        <div class="p-4">
                                            <h4 class="font-medium text-gray-900 group-hover:text-pulse-orange-600 transition-colors" x-text="template.name"></h4>
                                            <p class="text-sm text-gray-500 mt-1 line-clamp-2" x-text="template.description"></p>
                                        </div>
                                    </button>
                                </template>
                            </div>

                            {{-- Empty state --}}
                            <div x-show="filteredTemplates.length === 0" class="text-center py-12">
                                <svg class="w-12 h-12 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-gray-500">No templates found matching your search.</p>
                                <button @click="searchQuery = ''; activeCategory = 'all'" class="mt-2 text-pulse-orange-600 hover:text-pulse-orange-700 text-sm font-medium">
                                    Clear filters
                                </button>
                            </div>
                        </div>

                        {{-- AI Generate Section --}}
                        <div class="mt-8 p-5 bg-gradient-to-r from-purple-50 via-indigo-50 to-blue-50 rounded-2xl border border-purple-200">
                            <div class="flex items-start gap-4">
                                <div class="p-3 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl shadow-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="font-semibold text-gray-900">Generate with AI</h3>
                                        <span class="px-2 py-0.5 text-[10px] font-semibold bg-purple-100 text-purple-700 rounded-full">Beta</span>
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">Describe the report you need and AI will create a custom template for you.</p>
                                    <div class="mt-4 flex gap-3">
                                        <input
                                            type="text"
                                            placeholder="e.g., 'Weekly attendance summary for 10th grade with trend charts'"
                                            class="flex-1 px-4 py-3 border border-purple-200 rounded-xl text-sm focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white"
                                        >
                                        <button class="px-6 py-3 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl hover:from-purple-700 hover:to-indigo-700 transition-all text-sm font-semibold shadow-lg shadow-purple-500/25 flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                            Generate
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Context Menu (Right-click) -->
    <div
        x-data="{
            show: false,
            x: 0,
            y: 0,
            elementId: null,
            open(event, elementId = null) {
                event.preventDefault();
                this.x = event.clientX;
                this.y = event.clientY;
                this.elementId = elementId;
                this.show = true;

                // Ensure menu stays within viewport
                this.$nextTick(() => {
                    const menu = this.$refs.contextMenu;
                    if (menu) {
                        const rect = menu.getBoundingClientRect();
                        if (this.x + rect.width > window.innerWidth) {
                            this.x = window.innerWidth - rect.width - 10;
                        }
                        if (this.y + rect.height > window.innerHeight) {
                            this.y = window.innerHeight - rect.height - 10;
                        }
                    }
                });
            },
            close() {
                this.show = false;
                this.elementId = null;
            }
        }"
        @contextmenu.window="if ($event.target.closest('[data-element-id]')) { open($event, $event.target.closest('[data-element-id]').dataset.elementId); } else if ($event.target.closest('[data-report-canvas]')) { open($event); }"
        @click.window="close()"
        @keydown.escape.window="close()"
        class="fixed z-[100]"
        x-show="show"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        :style="`left: ${x}px; top: ${y}px;`"
        x-cloak
    >
        <div
            x-ref="contextMenu"
            class="bg-white rounded-xl shadow-xl border border-gray-200 py-1 min-w-[180px] overflow-hidden"
            @click.stop
        >
            {{-- Element actions (when element selected) --}}
            <template x-if="elementId">
                <div>
                    <button
                        @click="$wire.duplicateElement(elementId); close()"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    >
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        <span>Duplicate</span>
                        <span class="ml-auto text-xs text-gray-400">Ctrl+D</span>
                    </button>
                    <button
                        @click="$wire.copySelected(); close()"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    >
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                        </svg>
                        <span>Copy</span>
                        <span class="ml-auto text-xs text-gray-400">Ctrl+C</span>
                    </button>
                    <button
                        @click="$wire.cutSelected(); close()"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    >
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243 4.243 3 3 0 004.243-4.243zm0-5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z"/>
                        </svg>
                        <span>Cut</span>
                        <span class="ml-auto text-xs text-gray-400">Ctrl+X</span>
                    </button>

                    <div class="border-t border-gray-100 my-1"></div>

                    <button
                        @click="$wire.bringToFront(); close()"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    >
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7"/>
                        </svg>
                        <span>Bring to Front</span>
                    </button>
                    <button
                        @click="$wire.sendToBack(); close()"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    >
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-8l-7 7-7-7"/>
                        </svg>
                        <span>Send to Back</span>
                    </button>

                    <div class="border-t border-gray-100 my-1"></div>

                    <button
                        @click="$wire.deleteElement(elementId); close()"
                        class="w-full px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-3"
                    >
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <span>Delete</span>
                        <span class="ml-auto text-xs text-gray-400">Del</span>
                    </button>
                </div>
            </template>

            {{-- Canvas actions (when no element selected) --}}
            <template x-if="!elementId">
                <div>
                    <button
                        @click="$wire.pasteFromClipboard(); close()"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    >
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <span>Paste</span>
                        <span class="ml-auto text-xs text-gray-400">Ctrl+V</span>
                    </button>

                    <div class="border-t border-gray-100 my-1"></div>

                    <button
                        @click="$wire.selectAll(); close()"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    >
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                        <span>Select All</span>
                        <span class="ml-auto text-xs text-gray-400">Ctrl+A</span>
                    </button>

                    <div class="border-t border-gray-100 my-1"></div>

                    <button
                        @click="$wire.toggleGrid(); close()"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    >
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                        <span>{{ $showGrid ? 'Hide Grid' : 'Show Grid' }}</span>
                    </button>

                    <button
                        @click="$wire.fitToScreen(); close()"
                        class="w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 flex items-center gap-3"
                    >
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                        <span>Fit to Screen</span>
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- Publish Modal -->
    @if($showPublishModal)
    <div
        x-data="{
            copied: null,
            copyToClipboard(text, type) {
                navigator.clipboard.writeText(text).then(() => {
                    this.copied = type;
                    setTimeout(() => this.copied = null, 2000);
                });
            }
        }"
        class="fixed inset-0 z-50 overflow-y-auto"
    >
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50 transition-opacity" wire:click="$set('showPublishModal', false)"></div>

            <div class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-semibold text-gray-900">Publish Report</h2>
                    <button wire:click="$set('showPublishModal', false)" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if($status === 'published')
                    <div class="flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg mb-4">
                        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-green-700">This report is published! Copy the link or embed code below.</span>
                    </div>

                    <div class="space-y-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Shareable Link</label>
                            <div class="flex gap-2">
                                <input
                                    type="text"
                                    readonly
                                    value="{{ $publicUrl }}"
                                    class="flex-1 px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm"
                                >
                                <button
                                    @click="copyToClipboard('{{ $publicUrl }}', 'link')"
                                    class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors flex items-center gap-1"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    <span x-show="copied !== 'link'">Copy</span>
                                    <span x-show="copied === 'link'" class="text-green-600">Copied!</span>
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Embed Code</label>
                            <textarea
                                readonly
                                rows="3"
                                class="w-full px-3 py-2 bg-gray-50 border border-gray-300 rounded-lg text-sm font-mono"
                            >{{ $embedCode }}</textarea>
                            <div class="flex justify-end mt-2">
                                <button
                                    @click="copyToClipboard(`{{ $embedCode }}`, 'embed')"
                                    class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700 flex items-center gap-1"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                    <span x-show="copied !== 'embed'">Copy Embed Code</span>
                                    <span x-show="copied === 'embed'" class="text-green-600">Copied!</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <button
                            wire:click="$set('showPublishModal', false)"
                            class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                        >
                            Close
                        </button>
                        <a
                            href="{{ $publicUrl }}"
                            target="_blank"
                            class="flex-1 px-4 py-2 text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors text-center"
                        >
                            Open Report
                        </a>
                    </div>
                @else
                    <p class="text-gray-600 mb-4">Publishing will generate a shareable link and embed code for this report.</p>

                    <div class="flex gap-3">
                        <button
                            wire:click="$set('showPublishModal', false)"
                            class="flex-1 px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors"
                        >
                            Cancel
                        </button>
                        <button
                            wire:click="publish"
                            wire:loading.attr="disabled"
                            wire:target="publish"
                            class="flex-1 px-4 py-2 text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors disabled:opacity-50 flex items-center justify-center gap-2"
                        >
                            <span wire:loading.remove wire:target="publish">Publish</span>
                            <span wire:loading wire:target="publish">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span wire:loading wire:target="publish">Publishing...</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Push Content Modal -->
    @livewire('push-content-modal')

    <!-- Multi-Selection Count Badge -->
    @if(count($selectedElementIds) > 1)
    <div class="selection-count-badge">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span>{{ count($selectedElementIds) }} elements selected</span>
            <button
                wire:click="clearSelection"
                class="ml-2 text-blue-200 hover:text-white"
                title="Clear selection"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    <!-- ═══════════════════════════════════════════════════════════════════ -->
    <!-- PHASE 6: WOW FACTOR COMPONENTS -->
    <!-- ═══════════════════════════════════════════════════════════════════ -->

    <!-- 6.1 Keyboard Shortcuts Modal (Press ?) -->
    @if($showShortcutsModal)
    <div
        class="fixed inset-0 z-[100] overflow-y-auto"
        x-data
        x-init="$el.querySelector('button[data-close]').focus()"
        @keydown.escape.window="$wire.set('showShortcutsModal', false)"
    >
        <div class="flex items-center justify-center min-h-screen px-4 py-8">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity" wire:click="$set('showShortcutsModal', false)"></div>

            <div class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full overflow-hidden animate-modal-in">
                <!-- Header -->
                <div class="bg-gradient-to-r from-gray-900 to-gray-800 px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-lg font-semibold text-white">Keyboard Shortcuts</h2>
                            <p class="text-sm text-gray-400">Work faster with these shortcuts</p>
                        </div>
                    </div>
                    <button
                        data-close
                        wire:click="$set('showShortcutsModal', false)"
                        class="p-2 text-gray-400 hover:text-white hover:bg-white/10 rounded-lg transition-colors"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Content -->
                <div class="p-6 grid grid-cols-2 gap-6">
                    <!-- Selection -->
                    <div>
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Selection</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Select element</span>
                                <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">Click</kbd>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Multi-select</span>
                                <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">Shift + Click</kbd>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Select all</span>
                                <div class="flex gap-1">
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">⌘</kbd>
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">A</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Deselect</span>
                                <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">Esc</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Editing -->
                    <div>
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Editing</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Copy</span>
                                <div class="flex gap-1">
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">⌘</kbd>
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">C</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Cut</span>
                                <div class="flex gap-1">
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">⌘</kbd>
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">X</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Paste</span>
                                <div class="flex gap-1">
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">⌘</kbd>
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">V</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Duplicate</span>
                                <div class="flex gap-1">
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">⌘</kbd>
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">D</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Delete</span>
                                <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">Delete</kbd>
                            </div>
                        </div>
                    </div>

                    <!-- Canvas -->
                    <div>
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Canvas</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Zoom in</span>
                                <div class="flex gap-1">
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">⌘</kbd>
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">+</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Zoom out</span>
                                <div class="flex gap-1">
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">⌘</kbd>
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">-</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Nudge 1px</span>
                                <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">Arrow keys</kbd>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Nudge grid</span>
                                <div class="flex gap-1">
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">Shift</kbd>
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">↑↓←→</kbd>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- History -->
                    <div>
                        <h3 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">History & Save</h3>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Undo</span>
                                <div class="flex gap-1">
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">⌘</kbd>
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">Z</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Redo</span>
                                <div class="flex gap-1">
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">⌘</kbd>
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">⇧</kbd>
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">Z</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Save</span>
                                <div class="flex gap-1">
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">⌘</kbd>
                                    <kbd class="px-2 py-1 bg-gray-100 rounded text-xs font-mono text-gray-700">S</kbd>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-gray-600">Show shortcuts</span>
                                <kbd class="px-2 py-1 bg-purple-100 text-purple-700 rounded text-xs font-mono">?</kbd>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 text-center">
                        <span class="text-gray-400">Pro tip:</span> On Windows/Linux, use <kbd class="px-1.5 py-0.5 bg-gray-200 rounded text-xs">Ctrl</kbd> instead of <kbd class="px-1.5 py-0.5 bg-gray-200 rounded text-xs">⌘</kbd>
                    </p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- 6.2 Floating Quick Actions Toolbar -->
    @if($selectedElement && !$showShortcutsModal && !$showTemplateGallery)
    <div
        x-data="{
            getPosition() {
                const el = document.querySelector('[data-element-id=\'{{ $selectedElement['id'] }}\']');
                if (!el) return { top: 0, left: 0, show: false };

                const rect = el.getBoundingClientRect();
                const canvas = document.querySelector('[data-report-canvas]');
                const canvasRect = canvas ? canvas.getBoundingClientRect() : { left: 0, top: 0 };

                // Position toolbar above the element
                let top = rect.top - 48;
                let left = rect.left + (rect.width / 2);

                // If too close to top, show below
                if (top < 60) {
                    top = rect.bottom + 8;
                }

                return { top, left, show: true };
            }
        }"
        x-init="$watch('$wire.selectedElementId', () => $nextTick(() => $el.style.opacity = '1'))"
        class="fixed z-50 transition-all duration-150"
        :style="`top: ${getPosition().top}px; left: ${getPosition().left}px; transform: translateX(-50%);`"
        x-show="getPosition().show"
        x-cloak
    >
        <div class="bg-gray-900 rounded-xl shadow-2xl px-1 py-1 flex items-center gap-0.5 quick-actions-toolbar">
            <!-- Copy -->
            <button
                wire:click="copySelected"
                class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors"
                title="Copy (⌘C)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </button>

            <!-- Duplicate -->
            <button
                wire:click="duplicateElement('{{ $selectedElement['id'] }}')"
                class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors"
                title="Duplicate (⌘D)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2"/>
                </svg>
            </button>

            <div class="w-px h-5 bg-gray-700 mx-1"></div>

            <!-- Bring Forward -->
            <button
                wire:click="bringToFront"
                class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors"
                title="Bring to Front"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11l7-7 7 7M5 19l7-7 7 7"/>
                </svg>
            </button>

            <!-- Send Backward -->
            <button
                wire:click="sendToBack"
                class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors"
                title="Send to Back"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 13l-7 7-7-7m14-8l-7 7-7-7"/>
                </svg>
            </button>

            <div class="w-px h-5 bg-gray-700 mx-1"></div>

            <!-- Lock/Unlock -->
            <button
                wire:click="toggleElementLock('{{ $selectedElement['id'] }}')"
                class="p-2 text-gray-400 hover:text-white hover:bg-gray-800 rounded-lg transition-colors"
                title="{{ ($selectedElement['config']['locked'] ?? false) ? 'Unlock' : 'Lock' }}"
            >
                @if($selectedElement['config']['locked'] ?? false)
                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                @endif
            </button>

            <div class="w-px h-5 bg-gray-700 mx-1"></div>

            <!-- Delete -->
            <button
                wire:click="deleteElement('{{ $selectedElement['id'] }}')"
                class="p-2 text-gray-400 hover:text-red-400 hover:bg-gray-800 rounded-lg transition-colors"
                title="Delete (Del)"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>
        </div>
    </div>
    @endif

    <!-- 6.7 Onboarding Tooltips (First-Time Users) -->
    <div
        x-data="{
            step: 0,
            maxSteps: 4,
            show: false,
            init() {
                // Check if user has seen onboarding
                if (!localStorage.getItem('pulse_report_builder_onboarded')) {
                    setTimeout(() => {
                        this.show = true;
                    }, 1000);
                }
            },
            next() {
                if (this.step < this.maxSteps - 1) {
                    this.step++;
                } else {
                    this.complete();
                }
            },
            skip() {
                this.complete();
            },
            complete() {
                this.show = false;
                localStorage.setItem('pulse_report_builder_onboarded', 'true');
            },
            getStepContent() {
                const steps = [
                    {
                        icon: '👋',
                        title: 'Welcome to Report Builder!',
                        description: 'Create beautiful, data-rich reports in minutes. Start by choosing a template or building from scratch.',
                        position: 'center'
                    },
                    {
                        icon: '🎨',
                        title: 'Drag & Drop Elements',
                        description: 'Use the sidebar to drag elements onto your canvas. Try text, charts, metrics, and more.',
                        position: 'left'
                    },
                    {
                        icon: '✨',
                        title: 'Smart Blocks',
                        description: 'Pre-built sections that auto-populate with real student data. Perfect for quick reports!',
                        position: 'left'
                    },
                    {
                        icon: '⌨️',
                        title: 'Keyboard Shortcuts',
                        description: 'Press ? anytime to see all shortcuts. Use ⌘C, ⌘V, arrows, and more to work faster.',
                        position: 'center'
                    }
                ];
                return steps[this.step];
            }
        }"
        x-show="show"
        x-cloak
        class="fixed inset-0 z-[110] pointer-events-none"
    >
        <!-- Backdrop with spotlight effect -->
        <div class="absolute inset-0 bg-black/40 pointer-events-auto" @click="skip()"></div>

        <!-- Tooltip Card -->
        <div
            class="absolute pointer-events-auto"
            :class="{
                'top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2': getStepContent().position === 'center',
                'top-1/2 left-80 -translate-y-1/2': getStepContent().position === 'left'
            }"
        >
            <div class="bg-white rounded-2xl shadow-2xl p-6 max-w-sm animate-modal-in">
                <!-- Icon -->
                <div class="text-4xl mb-4" x-text="getStepContent().icon"></div>

                <!-- Content -->
                <h3 class="text-lg font-semibold text-gray-900 mb-2" x-text="getStepContent().title"></h3>
                <p class="text-gray-600 text-sm mb-6" x-text="getStepContent().description"></p>

                <!-- Progress dots -->
                <div class="flex items-center justify-between">
                    <div class="flex gap-1.5">
                        <template x-for="i in maxSteps">
                            <div
                                class="w-2 h-2 rounded-full transition-colors"
                                :class="i - 1 === step ? 'bg-pulse-orange-500' : 'bg-gray-200'"
                            ></div>
                        </template>
                    </div>

                    <div class="flex gap-2">
                        <button
                            @click="skip()"
                            class="px-3 py-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors"
                        >
                            Skip
                        </button>
                        <button
                            @click="next()"
                            class="px-4 py-1.5 text-sm font-medium text-white bg-pulse-orange-500 hover:bg-pulse-orange-600 rounded-lg transition-colors"
                        >
                            <span x-text="step === maxSteps - 1 ? 'Get Started' : 'Next'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6.8 Canvas Minimap (Bottom Right) -->
    @if(count($elements) > 3)
    <div
        x-data="{
            expanded: false,
            toggle() { this.expanded = !this.expanded; }
        }"
        class="fixed bottom-6 right-6 z-40"
    >
        <!-- Collapsed state - just a button -->
        <button
            x-show="!expanded"
            @click="toggle()"
            class="bg-white rounded-xl shadow-lg p-3 hover:shadow-xl transition-shadow border border-gray-200"
            title="Show minimap"
        >
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
            </svg>
        </button>

        <!-- Expanded minimap -->
        <div
            x-show="expanded"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            class="bg-white rounded-xl shadow-2xl border border-gray-200 overflow-hidden"
        >
            <div class="px-3 py-2 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                <span class="text-xs font-medium text-gray-600">Minimap</span>
                <button @click="toggle()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="p-2">
                <div class="w-40 h-28 bg-gray-100 rounded relative">
                    @foreach($elements as $index => $element)
                        @php
                            // Scale down: 800px canvas -> 160px minimap = 0.2 scale
                            $scale = 0.2;
                            $x = ($element['position']['x'] ?? 0) * $scale;
                            $y = ($element['position']['y'] ?? 0) * $scale;
                            $w = max(4, ($element['size']['width'] ?? 100) * $scale);
                            $h = max(2, ($element['size']['height'] ?? 50) * $scale);
                        @endphp
                        <div
                            class="absolute rounded-sm {{ $selectedElementId === $element['id'] ? 'bg-blue-400' : 'bg-gray-400' }}"
                            style="left: {{ $x }}px; top: {{ $y }}px; width: {{ $w }}px; height: {{ $h }}px; opacity: 0.6;"
                        ></div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
