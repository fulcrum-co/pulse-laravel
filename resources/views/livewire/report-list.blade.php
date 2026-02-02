<div class="space-y-4">
    <!-- Search, Filters & View Toggle -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4" data-help="report-filters">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 flex-1">
            <div class="relative w-full sm:w-64" data-help="search-reports">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search reports..."
                    class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                />
            </div>

            <select
                wire:model.live="statusFilter"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="published">Published</option>
            </select>

            @if($search || $statusFilter)
            <button
                wire:click="clearFilters"
                class="text-sm text-gray-500 hover:text-gray-700"
            >
                Clear
            </button>
            @endif
        </div>

        <!-- View Toggle -->
        <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
            <button
                wire:click="setViewMode('grid')"
                class="p-1.5 rounded {{ $viewMode === 'grid' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                title="Grid view"
            >
                <x-icon name="squares-2x2" class="w-4 h-4" />
            </button>
            <button
                wire:click="setViewMode('list')"
                class="p-1.5 rounded {{ $viewMode === 'list' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                title="List view"
            >
                <x-icon name="list-bullet" class="w-4 h-4" />
            </button>
            <button
                wire:click="setViewMode('table')"
                class="p-1.5 rounded {{ $viewMode === 'table' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                title="Table view"
            >
                <x-icon name="table-cells" class="w-4 h-4" />
            </button>
        </div>
    </div>

    <!-- Empty State -->
    @if($reports->isEmpty())
        <x-card>
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gradient-to-br from-pulse-orange-100 to-pulse-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <x-icon name="chart-pie" class="w-8 h-8 text-pulse-orange-500" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Create your first report</h3>
                <p class="text-gray-500 mb-4 max-w-sm mx-auto text-sm">
                    Build beautiful, data-driven reports with our drag-and-drop editor.
                </p>
                <a href="{{ route('reports.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                    <x-icon name="plus" class="w-4 h-4 mr-1" />
                    Create Report
                </a>
            </div>
        </x-card>

    <!-- Grid View -->
    @elseif($viewMode === 'grid')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4" data-help="report-list">
            @foreach($reports as $report)
                <a href="{{ route('reports.edit', $report) }}" class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md hover:border-gray-300 transition-all group block">
                    <!-- Content -->
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <h3 class="font-medium text-gray-900 text-sm truncate flex-1">{{ $report->report_name }}</h3>
                            @if($report->isPublished())
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700 flex-shrink-0">
                                    Published
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 flex-shrink-0">
                                    Draft
                                </span>
                            @endif
                        </div>

                        <!-- Preview placeholder (like dashboard widget grid) -->
                        <div class="grid grid-cols-4 gap-1 h-12 mb-2">
                            <div class="bg-gradient-to-br from-gray-100 to-gray-50 rounded flex items-center justify-center">
                                <x-icon name="chart-bar" class="w-3 h-3 text-gray-300" />
                            </div>
                            <div class="bg-gradient-to-br from-gray-100 to-gray-50 rounded flex items-center justify-center">
                                <x-icon name="document-text" class="w-3 h-3 text-gray-300" />
                            </div>
                            <div class="bg-gradient-to-br from-gray-100 to-gray-50 rounded flex items-center justify-center">
                                <x-icon name="table-cells" class="w-3 h-3 text-gray-300" />
                            </div>
                            <div class="bg-gradient-to-br from-gray-100 to-gray-50 rounded flex items-center justify-center">
                                <x-icon name="chart-pie" class="w-3 h-3 text-gray-300" />
                            </div>
                        </div>

                        <p class="text-[11px] text-gray-500">
                            Updated {{ $report->updated_at->diffForHumans() }}
                        </p>
                    </div>

                    <!-- Actions bar -->
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between" onclick="event.preventDefault(); event.stopPropagation();">
                        <div class="flex items-center gap-1">
                            <button wire:click.prevent="duplicate({{ $report->id }})" class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded transition-colors" title="Duplicate">
                                <x-icon name="document-duplicate" class="w-3.5 h-3.5" />
                            </button>
                            @if($canPush)
                            <div class="relative group/push">
                                <button wire:click.prevent="openPushModal({{ $report->id }})" class="p-1 text-gray-400 hover:text-pulse-orange-500 hover:bg-pulse-orange-50 rounded transition-colors" title="Push to Schools">
                                    <x-icon name="arrow-up-on-square" class="w-3.5 h-3.5" />
                                </button>
                            </div>
                            @endif
                            @if($report->isPublished())
                            <a href="{{ $report->getPublicUrl() }}" target="_blank" onclick="event.stopPropagation();" class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded transition-colors" title="View Published">
                                <x-icon name="arrow-top-right-on-square" class="w-3.5 h-3.5" />
                            </a>
                            @endif
                        </div>
                        <button
                            wire:click.prevent="delete({{ $report->id }})"
                            wire:confirm="Are you sure you want to delete this report?"
                            class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors"
                            title="Delete"
                        >
                            <x-icon name="trash" class="w-3.5 h-3.5" />
                        </button>
                    </div>
                </a>
            @endforeach
        </div>

    <!-- List View -->
    @elseif($viewMode === 'list')
        <div class="space-y-2">
            @foreach($reports as $report)
                <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-sm transition-shadow">
                    <div class="flex items-center gap-4">
                        <!-- Thumbnail -->
                        <div class="w-20 h-14 bg-gradient-to-br from-gray-100 to-gray-50 rounded-lg flex-shrink-0 overflow-hidden">
                            @if($report->thumbnail_path)
                                <img src="{{ $report->thumbnail_path }}" alt="{{ $report->report_name }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <x-icon name="document-chart-bar" class="w-6 h-6 text-gray-300" />
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-medium text-gray-900 text-sm truncate">{{ $report->report_name }}</h3>
                                @if($report->isPublished())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Draft
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                                <span>Updated {{ $report->updated_at->diffForHumans() }}</span>
                                @if($report->report_type)
                                    <span class="capitalize">{{ str_replace('_', ' ', $report->report_type) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <a href="{{ route('reports.edit', $report) }}" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </a>
                            <button wire:click="duplicate({{ $report->id }})" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="document-duplicate" class="w-4 h-4" />
                            </button>
                            @if($canPush)
                            <div class="relative group">
                                <button wire:click="openPushModal({{ $report->id }})" class="p-1.5 text-gray-400 hover:text-pulse-orange-500 rounded">
                                    <x-icon name="arrow-up-on-square" class="w-4 h-4" />
                                </button>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none">Push to Schools</span>
                            </div>
                            @endif
                            @if($report->isPublished())
                                <a href="{{ $report->getPublicUrl() }}" target="_blank" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                    <x-icon name="arrow-top-right-on-square" class="w-4 h-4" />
                                </a>
                            @endif
                            <button
                                wire:click="delete({{ $report->id }})"
                                wire:confirm="Are you sure you want to delete this report?"
                                class="p-1.5 text-gray-400 hover:text-red-500 rounded"
                            >
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                            <a href="{{ route('reports.edit', $report) }}" class="ml-2 px-3 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

    <!-- Table View -->
    @else
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Report</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Updated</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($reports as $report)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-8 bg-gradient-to-br from-gray-100 to-gray-50 rounded flex-shrink-0 overflow-hidden">
                                        @if($report->thumbnail_path)
                                            <img src="{{ $report->thumbnail_path }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <x-icon name="document-chart-bar" class="w-4 h-4 text-gray-300" />
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $report->report_name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($report->report_type)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 capitalize">
                                        {{ str_replace('_', ' ', $report->report_type) }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($report->isPublished())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Published
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Draft
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $report->updated_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('reports.edit', $report) }}" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </a>
                                    <button wire:click="duplicate({{ $report->id }})" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                        <x-icon name="document-duplicate" class="w-4 h-4" />
                                    </button>
                                    @if($canPush)
                                    <div class="relative group">
                                        <button wire:click="openPushModal({{ $report->id }})" class="p-1 text-gray-400 hover:text-pulse-orange-500 rounded">
                                            <x-icon name="arrow-up-on-square" class="w-3.5 h-3.5" />
                                        </button>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none z-10">Push to Schools</span>
                                    </div>
                                    @endif
                                    @if($report->isPublished())
                                        <a href="{{ $report->getPublicUrl() }}" target="_blank" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                            <x-icon name="arrow-top-right-on-square" class="w-4 h-4" />
                                        </a>
                                    @endif
                                    <button
                                        wire:click="delete({{ $report->id }})"
                                        wire:confirm="Are you sure you want to delete this report?"
                                        class="p-1 text-gray-400 hover:text-red-500 rounded"
                                    >
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </button>
                                    <a href="{{ route('reports.edit', $report) }}" class="ml-1 px-2 py-1 text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                        Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Pagination -->
    @if($reports->hasPages())
        <div class="mt-6">
            {{ $reports->links() }}
        </div>
    @endif

    <!-- Push Content Modal -->
    @livewire('push-content-modal')
</div>
