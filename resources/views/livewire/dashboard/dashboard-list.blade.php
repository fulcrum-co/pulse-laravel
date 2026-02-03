@php
    $terminology = app(\App\Services\TerminologyService::class);
@endphp

<div class="space-y-4">
    <!-- Search, Filters & View Toggle -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 flex-1">
            <div class="relative w-full sm:w-64">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ $terminology->get('search_dashboards_placeholder') }}"
                    class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                />
            </div>

            <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                <button
                    wire:click="setFilter('all')"
                    class="px-3 py-1 text-sm rounded {{ $filter === 'all' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    @term('all_label')
                </button>
                <button
                    wire:click="setFilter('mine')"
                    class="px-3 py-1 text-sm rounded {{ $filter === 'mine' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    @term('my_dashboards_label')
                </button>
                <button
                    wire:click="setFilter('shared')"
                    class="px-3 py-1 text-sm rounded {{ $filter === 'shared' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                >
                    @term('shared_label')
                </button>
            </div>
        </div>

        <!-- View Toggle -->
        <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
            <button
                wire:click="setViewMode('grid')"
                class="p-1.5 rounded {{ $viewMode === 'grid' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                title="{{ $terminology->get('grid_view_label') }}"
            >
                <x-icon name="squares-2x2" class="w-4 h-4" />
            </button>
            <button
                wire:click="setViewMode('list')"
                class="p-1.5 rounded {{ $viewMode === 'list' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                title="{{ $terminology->get('list_view_label') }}"
            >
                <x-icon name="list-bullet" class="w-4 h-4" />
            </button>
            <button
                wire:click="setViewMode('table')"
                class="p-1.5 rounded {{ $viewMode === 'table' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                title="{{ $terminology->get('table_view_label') }}"
            >
                <x-icon name="table-cells" class="w-4 h-4" />
            </button>
        </div>
    </div>

    <!-- Empty State -->
    @if($dashboards->isEmpty())
        <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
            <div class="w-16 h-16 bg-gradient-to-br from-pulse-orange-100 to-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                <x-icon name="squares-2x2" class="w-8 h-8 text-pulse-orange-500" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">@term('no_dashboards_yet_label')</h3>
            <p class="text-sm text-gray-500 mb-4 max-w-sm mx-auto">
                @term('create_first_dashboard_label')
            </p>
            <a href="/dashboard" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                <x-icon name="plus" class="w-4 h-4 mr-1" />
                @term('create_dashboard_label')
            </a>
        </div>

    <!-- Grid View -->
    @elseif($viewMode === 'grid')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($dashboards as $dashboard)
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    <!-- Dashboard Preview -->
                    <a href="/dashboard?id={{ $dashboard->id }}" class="block p-4">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <h3 class="font-medium text-gray-900 text-sm truncate">{{ $dashboard->name }}</h3>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                @if($dashboard->is_default)
                                    <span class="text-xs bg-pulse-orange-100 text-pulse-orange-600 px-1.5 py-0.5 rounded">@term('default_label')</span>
                                @endif
                                @if($dashboard->is_shared)
                                    <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">@term('shared_label')</span>
                                @endif
                            </div>
                        </div>

                        @if($dashboard->description)
                            <p class="text-xs text-gray-500 mb-3 line-clamp-2">{{ $dashboard->description }}</p>
                        @endif

                        <!-- Widget Preview -->
                        <div class="grid grid-cols-4 gap-1 h-16 mb-3">
                            @foreach($dashboard->widgets->take(8) as $widget)
                                @php
                                    $widgetColor = match($widget->widget_type) {
                                        'metric_card' => 'bg-blue-100',
                                        'bar_chart' => 'bg-green-100',
                                        'line_chart' => 'bg-purple-100',
                                        'learner_list' => 'bg-yellow-100',
                                        'survey_summary' => 'bg-pink-100',
                                        'alert_feed' => 'bg-red-100',
                                        default => 'bg-gray-100',
                                    };
                                @endphp
                                <div class="rounded {{ $widgetColor }}"></div>
                            @endforeach
                            @for($i = $dashboard->widgets->count(); $i < 8; $i++)
                                <div class="rounded bg-gray-50 border border-dashed border-gray-200"></div>
                            @endfor
                        </div>

                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-500">{{ $dashboard->widgets->count() }} @term('widgets_label')</span>
                            @if($dashboard->user_id !== auth()->id())
                                <span class="text-gray-400">{{ $terminology->get('by_label') }} {{ $dashboard->user->first_name ?? $terminology->get('unknown_label') }}</span>
                            @else
                                <span class="text-gray-400">{{ $dashboard->updated_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </a>

                    <!-- Actions -->
                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-1">
                            <div class="relative group">
                                <button wire:click="duplicateDashboard({{ $dashboard->id }})" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                    <x-icon name="document-duplicate" class="w-3.5 h-3.5" />
                                </button>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">@term('duplicate_label')</span>
                            </div>
                            @if($dashboard->user_id === auth()->id())
                                <div class="relative group">
                                    <button
                                        wire:click="deleteDashboard({{ $dashboard->id }})"
                                        wire:confirm="{{ $terminology->get('delete_dashboard_confirm_label') }}"
                                        class="p-1.5 text-gray-400 hover:text-red-500 rounded"
                                    >
                                        <x-icon name="trash" class="w-3.5 h-3.5" />
                                    </button>
                                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">@term('delete_label')</span>
                                </div>
                            @endif
                        </div>
                        <a href="/dashboard?id={{ $dashboard->id }}" class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                            @term('open_label')
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

    <!-- List View -->
    @elseif($viewMode === 'list')
        <div class="space-y-2">
            @foreach($dashboards as $dashboard)
                <div class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-sm transition-shadow">
                    <div class="flex items-center gap-4">
                        <!-- Widget Preview Mini -->
                        <div class="w-16 h-12 grid grid-cols-4 gap-0.5 flex-shrink-0">
                            @foreach($dashboard->widgets->take(8) as $widget)
                                @php
                                    $widgetColor = match($widget->widget_type) {
                                        'metric_card' => 'bg-blue-100',
                                        'bar_chart' => 'bg-green-100',
                                        'line_chart' => 'bg-purple-100',
                                        'learner_list' => 'bg-yellow-100',
                                        'survey_summary' => 'bg-pink-100',
                                        'alert_feed' => 'bg-red-100',
                                        default => 'bg-gray-100',
                                    };
                                @endphp
                                <div class="rounded-sm {{ $widgetColor }}"></div>
                            @endforeach
                            @for($i = $dashboard->widgets->count(); $i < 8; $i++)
                                <div class="rounded-sm bg-gray-50"></div>
                            @endfor
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-medium text-gray-900 text-sm truncate">{{ $dashboard->name }}</h3>
                                @if($dashboard->is_default)
                                    <span class="text-xs bg-pulse-orange-100 text-pulse-orange-600 px-1.5 py-0.5 rounded">@term('default_label')</span>
                                @endif
                                @if($dashboard->is_shared)
                                    <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">@term('shared_label')</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                                <span>{{ $dashboard->widgets->count() }} @term('widgets_label')</span>
                                @if($dashboard->user_id !== auth()->id())
                                    <span>{{ $terminology->get('by_label') }} {{ $dashboard->user->first_name ?? $terminology->get('unknown_label') }}</span>
                                @else
                                    <span>@term('updated_label') {{ $dashboard->updated_at->diffForHumans() }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <button wire:click="duplicateDashboard({{ $dashboard->id }})" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="document-duplicate" class="w-4 h-4" />
                            </button>
                            @if($dashboard->user_id === auth()->id())
                                <button
                                    wire:click="deleteDashboard({{ $dashboard->id }})"
                                    wire:confirm="{{ $terminology->get('delete_dashboard_confirm_label') }}"
                                    class="p-1.5 text-gray-400 hover:text-red-500 rounded"
                                >
                                    <x-icon name="trash" class="w-4 h-4" />
                                </button>
                            @endif
                            <a href="/dashboard?id={{ $dashboard->id }}" class="ml-2 px-3 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
                                @term('open_label')
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
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('dashboard_label')</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('widgets_label')</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('status_label')</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('owner_label')</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('updated_label')</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">@term('actions_label')</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($dashboards as $dashboard)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <!-- Widget Preview Mini -->
                                    <div class="w-10 h-8 grid grid-cols-4 gap-0.5 flex-shrink-0">
                                        @foreach($dashboard->widgets->take(8) as $widget)
                                            @php
                                                $widgetColor = match($widget->widget_type) {
                                                    'metric_card' => 'bg-blue-100',
                                                    'bar_chart' => 'bg-green-100',
                                                    'line_chart' => 'bg-purple-100',
                                                    'learner_list' => 'bg-yellow-100',
                                                    'survey_summary' => 'bg-pink-100',
                                                    'alert_feed' => 'bg-red-100',
                                                    default => 'bg-gray-100',
                                                };
                                            @endphp
                                            <div class="rounded-sm {{ $widgetColor }}"></div>
                                        @endforeach
                                        @for($i = $dashboard->widgets->count(); $i < 8; $i++)
                                            <div class="rounded-sm bg-gray-50"></div>
                                        @endfor
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $dashboard->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $dashboard->widgets->count() }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-1">
                                    @if($dashboard->is_default)
                                        <span class="text-xs bg-pulse-orange-100 text-pulse-orange-600 px-1.5 py-0.5 rounded">@term('default_label')</span>
                                    @endif
                                    @if($dashboard->is_shared)
                                        <span class="text-xs bg-blue-100 text-blue-600 px-1.5 py-0.5 rounded">@term('shared_label')</span>
                                    @endif
                                    @if(!$dashboard->is_default && !$dashboard->is_shared)
                                        <span class="text-xs text-gray-400">@term('private_label')</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                @if($dashboard->user_id === auth()->id())
                                    @term('you_label')
                                @else
                                    {{ $dashboard->user->first_name ?? $terminology->get('unknown_label') }}
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $dashboard->updated_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="duplicateDashboard({{ $dashboard->id }})" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                        <x-icon name="document-duplicate" class="w-4 h-4" />
                                    </button>
                                    @if($dashboard->user_id === auth()->id())
                                        <button
                                            wire:click="deleteDashboard({{ $dashboard->id }})"
                                            wire:confirm="{{ $terminology->get('delete_dashboard_confirm_label') }}"
                                            class="p-1 text-gray-400 hover:text-red-500 rounded"
                                        >
                                            <x-icon name="trash" class="w-4 h-4" />
                                        </button>
                                    @endif
                                    <a href="/dashboard?id={{ $dashboard->id }}" class="ml-1 px-2 py-1 text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                        @term('open_label')
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
