<div class="min-h-screen bg-gray-50">
    @php($terminology = app(\App\Services\TerminologyService::class))
    <!-- Header Banner -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('resources.index') }}" class="text-gray-400 hover:text-gray-600">
                    <x-icon name="chevron-left" class="w-5 h-5" />
                </a>
                <div>
                    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                        <a href="{{ route('resources.index') }}" class="hover:text-gray-700">@term('resources_label')</a>
                        <span>@term('breadcrumb_separator_label')</span>
                        <span class="text-gray-900">@term('content_label')</span>
                    </nav>
                    <h1 class="text-2xl font-semibold text-gray-900">@term('content_library_label')</h1>
                </div>
            </div>
            <button
                wire:click="$dispatch('openAddResourceModal')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
            >
                <x-icon name="plus" class="w-4 h-4" />
                @term('add_content_label')
            </button>
        </div>
    </div>

    <div class="flex">
        <!-- Left Filter Sidebar -->
        <div class="w-64 bg-white border-r border-gray-200 min-h-[calc(100vh-73px)] p-4 flex-shrink-0">
            <!-- Search -->
            <div class="mb-6">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-icon name="magnifying-glass" class="h-4 w-4 text-gray-400" />
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="@term('search_action')"
                        class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    >
                </div>
            </div>

            <!-- Type Filter -->
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">@term('type_label')</h3>
                <div class="space-y-2">
                    @foreach($this->types as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input
                                type="checkbox"
                                wire:click="toggleType('{{ $value }}')"
                                @checked(in_array($value, $selectedTypes))
                                class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                            >
                            <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Level Filter -->
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">@term('levels_label')</h3>
                <div class="space-y-2">
                    @foreach($this->levels as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input
                                type="checkbox"
                                wire:click="toggleGrade('{{ $value }}')"
                                @checked(in_array($value, $selectedGrades))
                                class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                            >
                            <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Category Filter -->
            @if(count($this->categories) > 0)
                <div class="mb-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">@term('category_singular')</h3>
                    <div class="space-y-2 max-h-48 overflow-y-auto">
                        @foreach($this->categories as $category)
                            <label class="flex items-center gap-2 cursor-pointer group">
                                <input
                                    type="checkbox"
                                    wire:click="toggleCategory('{{ $category }}')"
                                    @checked(in_array($category, $selectedCategories))
                                    class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                                >
                                <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ ucfirst($category) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Risk Level Filter -->
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">@term('target_risk_levels_label')</h3>
                <div class="space-y-2">
                    @foreach($this->riskLevels as $value => $label)
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input
                                type="checkbox"
                                wire:click="toggleRiskLevel('{{ $value }}')"
                                @checked(in_array($value, $selectedRiskLevels))
                                class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                            >
                            <span class="text-sm text-gray-700 group-hover:text-gray-900">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Clear Filters -->
            @if($this->hasActiveFilters)
                <button
                    wire:click="clearFilters"
                    class="w-full text-sm text-pulse-orange-600 hover:text-pulse-orange-700 font-medium"
                >
                    @term('clear_filters_label')
                </button>
            @endif
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 p-6">
            <!-- Sort & Count Bar -->
            <div class="flex items-center justify-between mb-6">
                <p class="text-sm text-gray-600">
                    @term('showing_label') <span class="font-medium">{{ $resources->count() }}</span> @term('of_label') <span class="font-medium">{{ $resources->total() }}</span> @term('resources_label')
                </p>
                <div class="flex items-center gap-4">
                    <!-- View Toggle -->
                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                        <button
                            wire:click="$set('viewMode', 'grid')"
                            class="p-2 {{ $viewMode === 'grid' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }}"
                            title="@term('grid_view_label')"
                        >
                            <x-icon name="squares-2x2" class="w-4 h-4" />
                        </button>
                        <button
                            wire:click="$set('viewMode', 'list')"
                            class="p-2 border-l border-gray-300 {{ $viewMode === 'list' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }}"
                            title="@term('list_view_label')"
                        >
                            <x-icon name="bars-3" class="w-4 h-4" />
                        </button>
                        <button
                            wire:click="$set('viewMode', 'table')"
                            class="p-2 border-l border-gray-300 {{ $viewMode === 'table' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }}"
                            title="@term('table_view_label')"
                        >
                            <x-icon name="table-cells" class="w-4 h-4" />
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500">@term('sort_by_label'):</span>
                        <select
                            wire:model.live="sortBy"
                            class="border-gray-300 rounded-lg text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                            <option value="recent">@term('recently_added_label')</option>
                            <option value="oldest">@term('oldest_first_label')</option>
                            <option value="title">@term('title_az_label')</option>
                        </select>
                    </div>
                </div>
            </div>

            @php
                $typeColors = [
                    'article' => 'blue',
                    'video' => 'red',
                    'worksheet' => 'green',
                    'activity' => 'purple',
                    'link' => 'gray',
                    'document' => 'yellow',
                ];
                $typeIcons = [
                    'article' => 'document-text',
                    'video' => 'play-circle',
                    'worksheet' => 'clipboard-document-list',
                    'activity' => 'puzzle-piece',
                    'link' => 'link',
                    'document' => 'document',
                ];
            @endphp

            @if($resources->count() > 0)
                @if($viewMode === 'grid')
                    <!-- Grid View -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($resources as $resource)
                            @php
                                $color = $typeColors[$resource->resource_type] ?? 'gray';
                                $icon = $typeIcons[$resource->resource_type] ?? 'document';
                            @endphp
                            <a
                                href="{{ route('resources.show', $resource) }}"
                                class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-pulse-orange-300 transition-all"
                            >
                                <div class="p-5">
                                    <div class="flex items-start gap-4">
                                        <div class="w-12 h-12 rounded-xl bg-{{ $color }}-100 flex items-center justify-center flex-shrink-0">
                                            <x-icon name="{{ $icon }}" class="w-6 h-6 text-{{ $color }}-600" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-base font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors truncate">
                                                {{ $resource->title }}
                                            </h3>
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700 mt-1">
                                                {{ $terminology->get('resource_type_'.$resource->resource_type.'_label') }}
                                            </span>
                                        </div>
                                    </div>
                                    @if($resource->description)
                                        <p class="mt-3 text-sm text-gray-600 line-clamp-2">{{ $resource->description }}</p>
                                    @endif
                                </div>
                                <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        @if($resource->estimated_duration_minutes)
                                            <span class="text-xs text-gray-500 flex items-center gap-1">
                                                <x-icon name="clock" class="w-3.5 h-3.5" />
                                                {{ $resource->estimated_duration_minutes }} @term('minutes_label')
                                            </span>
                                        @endif
                                        @if($resource->category)
                                            <span class="text-xs text-gray-500">{{ ucfirst($resource->category) }}</span>
                                        @endif
                                    </div>
                                    <span class="text-xs font-medium text-pulse-orange-600 group-hover:text-pulse-orange-700">@term('view_action') &rarr;</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @elseif($viewMode === 'list')
                    <!-- List View -->
                    <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                        @foreach($resources as $resource)
                            @php
                                $color = $typeColors[$resource->resource_type] ?? 'gray';
                                $icon = $typeIcons[$resource->resource_type] ?? 'document';
                            @endphp
                            <a href="{{ route('resources.show', $resource) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors group">
                                <div class="w-10 h-10 rounded-lg bg-{{ $color }}-100 flex items-center justify-center flex-shrink-0">
                                    <x-icon name="{{ $icon }}" class="w-5 h-5 text-{{ $color }}-600" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-sm font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">{{ $resource->title }}</h3>
                                    @if($resource->description)
                                        <p class="text-sm text-gray-500 truncate mt-0.5">{{ Str::limit($resource->description, 100) }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-4 flex-shrink-0">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700">
                                        {{ $terminology->get('resource_type_'.$resource->resource_type.'_label') }}
                                    </span>
                                    @if($resource->estimated_duration_minutes)
                                        <span class="text-xs text-gray-500">{{ $resource->estimated_duration_minutes }} @term('minutes_label')</span>
                                    @endif
                                    @if($resource->category)
                                        <span class="text-xs text-gray-500">{{ ucfirst($resource->category) }}</span>
                                    @endif
                                    <x-icon name="chevron-right" class="w-5 h-5 text-gray-400 group-hover:text-pulse-orange-500" />
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <!-- Table View -->
                    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('title_label')</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('type_label')</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('category_singular')</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('duration_label')</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('added_label')</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($resources as $resource)
                                    @php
                                        $color = $typeColors[$resource->resource_type] ?? 'gray';
                                        $icon = $typeIcons[$resource->resource_type] ?? 'document';
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-lg bg-{{ $color }}-100 flex items-center justify-center flex-shrink-0">
                                                    <x-icon name="{{ $icon }}" class="w-4 h-4 text-{{ $color }}-600" />
                                                </div>
                                                <span class="text-sm font-medium text-gray-900">{{ Str::limit($resource->title, 40) }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700">
                                                {{ $terminology->get('resource_type_'.$resource->resource_type.'_label') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $resource->category ? ucfirst($resource->category) : '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $resource->estimated_duration_minutes ? $resource->estimated_duration_minutes . ' ' . $terminology->get('minutes_label') : $terminology->get('empty_dash_label') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $resource->created_at->format('M j, Y') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <a href="{{ route('resources.show', $resource) }}" class="text-pulse-orange-600 hover:text-pulse-orange-700 text-sm font-medium">
                                                @term('view_action') &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $resources->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
                        <x-icon name="document-text" class="w-8 h-8 text-gray-400" />
                    </div>
                    @if($this->hasActiveFilters)
                        <h3 class="text-lg font-medium text-gray-900 mb-1">@term('no_resources_match_filters_label')</h3>
                        <p class="text-gray-500 mb-4">@term('adjust_filters_or_clear_label')</p>
                        <button
                            wire:click="clearFilters"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            @term('clear_filters_label')
                        </button>
                    @else
                        <h3 class="text-lg font-medium text-gray-900 mb-1">@term('no_content_resources_yet_label')</h3>
                        <p class="text-gray-500 mb-4">@term('content_library_empty_help_label')</p>
                        <button
                            wire:click="$dispatch('openAddResourceModal')"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                        >
                            <x-icon name="plus" class="w-4 h-4" />
                            @term('add_content_label')
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
