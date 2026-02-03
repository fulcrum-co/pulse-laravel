<div class="space-y-4">
    <!-- Search, Filters & View Toggle -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4" data-help="report-filters">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 flex-1">
            <div class="relative w-full sm:w-64" data-help="search-reports">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ app(\App\Services\TerminologyService::class)->get('search_action') }} {{ strtolower(app(\App\Services\TerminologyService::class)->get('report_plural')) }}..."
                    class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                />
            </div>

            <select
                wire:model.live="statusFilter"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="">{{ app(\App\Services\TerminologyService::class)->get('all_label') }} {{ app(\App\Services\TerminologyService::class)->get('status_label') }}es</option>
                <option value="draft">@term('draft_label')</option>
                <option value="published">@term('published_label')</option>
            </select>

            @if($search || $statusFilter)
            <button
                wire:click="clearFilters"
                class="text-sm text-gray-500 hover:text-gray-700"
            >
                @term('clear_label')
            </button>
            @endif
        </div>

        <!-- View Toggle -->
        <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
            <button
                wire:click="setViewMode('grid')"
                class="p-1.5 rounded {{ $viewMode === 'grid' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                    title="{{ app(\App\Services\TerminologyService::class)->get('grid_view_label') }}"
                >
                <x-icon name="squares-2x2" class="w-4 h-4" />
            </button>
            <button
                wire:click="setViewMode('list')"
                class="p-1.5 rounded {{ $viewMode === 'list' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                    title="{{ app(\App\Services\TerminologyService::class)->get('list_view_label') }}"
                >
                <x-icon name="list-bullet" class="w-4 h-4" />
            </button>
            <button
                wire:click="setViewMode('table')"
                class="p-1.5 rounded {{ $viewMode === 'table' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                    title="{{ app(\App\Services\TerminologyService::class)->get('table_view_label') }}"
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
                <h3 class="text-lg font-semibold text-gray-900 mb-1">@term('report_empty_title')</h3>
                <p class="text-gray-500 mb-4 max-w-sm mx-auto text-sm">
                    @term('report_empty_body')
                </p>
                <a href="{{ route('reports.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                    <x-icon name="plus" class="w-4 h-4 mr-1" />
                    @term('create_action') @term('report_singular')
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
                            <h3 class="font-medium text-gray-900 text-sm truncate flex-1">{{ $report->report_name ?? app(\App\Services\TerminologyService::class)->get('untitled_report_label') }}</h3>
                            @if($report->isPublished())
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-100 text-green-700 flex-shrink-0">
                                    @term('published_label')
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-100 text-gray-600 flex-shrink-0">
                                    @term('draft_label')
                                </span>
                            @endif
                        </div>

                        <!-- Preview placeholder (like dashboard widget grid) -->
                        <div class="grid grid-cols-4 gap-1 mb-2" style="height: 48px;">
                            <div class="rounded flex items-center justify-center h-full" style="background: linear-gradient(to bottom right, #eff6ff, #dbeafe);">
                                <svg class="w-3 h-3" style="color: #60a5fa;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="rounded flex items-center justify-center h-full" style="background: linear-gradient(to bottom right, #f0fdf4, #dcfce7);">
                                <svg class="w-3 h-3" style="color: #4ade80;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <div class="rounded flex items-center justify-center h-full" style="background: linear-gradient(to bottom right, #faf5ff, #f3e8ff);">
                                <svg class="w-3 h-3" style="color: #c084fc;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="rounded flex items-center justify-center h-full" style="background: linear-gradient(to bottom right, #fff7ed, #ffedd5);">
                                <svg class="w-3 h-3" style="color: #fb923c;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>
                                </svg>
                            </div>
                        </div>

                        <p class="text-[11px] text-gray-500">
                            @term('updated_label') {{ $report->updated_at?->diffForHumans() ?? app(\App\Services\TerminologyService::class)->get('unknown_label') }}
                        </p>
                    </div>

                    <!-- Actions bar -->
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between" onclick="event.preventDefault(); event.stopPropagation();">
                        <div class="flex items-center gap-1">
                            <button wire:click.prevent="duplicate({{ $report->id }})" class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded transition-colors" title="{{ app(\App\Services\TerminologyService::class)->get('duplicate_action') }}">
                                <x-icon name="document-duplicate" class="w-3.5 h-3.5" />
                            </button>
                            @if($canPush)
                            <div class="relative group/push">
                                <button wire:click.prevent="openPushModal({{ $report->id }})" class="p-1 text-gray-400 hover:text-pulse-orange-500 hover:bg-pulse-orange-50 rounded transition-colors" title="{{ app(\App\Services\TerminologyService::class)->get('push_label') }} to {{ app(\App\Services\TerminologyService::class)->get('organization_plural') }}">
                                    <x-icon name="arrow-up-on-square" class="w-3.5 h-3.5" />
                                </button>
                            </div>
                            @endif
                            @if($report->isPublished())
                            <a href="{{ $report->getPublicUrl() }}" target="_blank" onclick="event.stopPropagation();" class="p-1 text-gray-400 hover:text-gray-600 hover:bg-gray-200 rounded transition-colors" title="{{ app(\App\Services\TerminologyService::class)->get('view_action') }} {{ app(\App\Services\TerminologyService::class)->get('published_label') }}">
                                <x-icon name="arrow-top-right-on-square" class="w-3.5 h-3.5" />
                            </a>
                            @endif
                        </div>
                        <button
                            wire:click.prevent="delete({{ $report->id }})"
                            wire:confirm="{{ app(\App\Services\TerminologyService::class)->get('confirm_label') }}: {{ app(\App\Services\TerminologyService::class)->get('delete_action') }} {{ app(\App\Services\TerminologyService::class)->get('report_singular') }}?"
                            class="p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded transition-colors"
                            title="{{ app(\App\Services\TerminologyService::class)->get('delete_action') }}"
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
                                <img src="{{ $report->thumbnail_path }}" alt="{{ $report->report_name ?? app(\App\Services\TerminologyService::class)->get('report_thumbnail_label') }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <x-icon name="document-chart-bar" class="w-6 h-6 text-gray-300" />
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-medium text-gray-900 text-sm truncate">{{ $report->report_name ?? app(\App\Services\TerminologyService::class)->get('untitled_report_label') }}</h3>
                            @if($report->isPublished())
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    @term('published_label')
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    @term('draft_label')
                                </span>
                            @endif
                        </div>
                        <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                                <span>@term('updated_label') {{ $report->updated_at?->diffForHumans() ?? app(\App\Services\TerminologyService::class)->get('unknown_label') }}</span>
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
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none">@term('push_label') to @term('organization_plural')</span>
                            </div>
                            @endif
                            @if($report->isPublished())
                                <a href="{{ $report->getPublicUrl() }}" target="_blank" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                    <x-icon name="arrow-top-right-on-square" class="w-4 h-4" />
                                </a>
                            @endif
                            <button
                                wire:click="delete({{ $report->id }})"
                                wire:confirm="{{ app(\App\Services\TerminologyService::class)->get('confirm_label') }}: {{ app(\App\Services\TerminologyService::class)->get('delete_action') }} {{ app(\App\Services\TerminologyService::class)->get('report_singular') }}?"
                                class="p-1.5 text-gray-400 hover:text-red-500 rounded"
                            >
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                            <a href="{{ route('reports.edit', $report) }}" class="ml-2 px-3 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
                                @term('edit_action')
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
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('report_singular')</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('type_label')</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('status_label')</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('updated_label')</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">@term('actions_label')</th>
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
                                    <span class="text-sm font-medium text-gray-900">{{ $report->report_name ?? app(\App\Services\TerminologyService::class)->get('untitled_report_label') }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($report->report_type)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 capitalize">
                                        {{ str_replace('_', ' ', $report->report_type) }}
                                    </span>
                                @else
                                    <span class="text-sm text-gray-400">@term('empty_value_placeholder')</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($report->isPublished())
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        @term('published_label')
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        @term('draft_label')
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $report->updated_at?->format('M d, Y') ?? '-' }}
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
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-800 rounded opacity-0 group-hover:opacity-100 whitespace-nowrap pointer-events-none z-10">@term('push_label') to @term('organization_plural')</span>
                                    </div>
                                    @endif
                                    @if($report->isPublished())
                                        <a href="{{ $report->getPublicUrl() }}" target="_blank" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                            <x-icon name="arrow-top-right-on-square" class="w-4 h-4" />
                                        </a>
                                    @endif
                                    <button
                                        wire:click="delete({{ $report->id }})"
                                        wire:confirm="{{ app(\App\Services\TerminologyService::class)->get('confirm_label') }}: {{ app(\App\Services\TerminologyService::class)->get('delete_action') }} {{ app(\App\Services\TerminologyService::class)->get('report_singular') }}?"
                                        class="p-1 text-gray-400 hover:text-red-500 rounded"
                                    >
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </button>
                                    <a href="{{ route('reports.edit', $report) }}" class="ml-1 px-2 py-1 text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                        @term('edit_action')
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
