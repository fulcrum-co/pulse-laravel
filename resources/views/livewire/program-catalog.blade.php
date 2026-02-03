<div class="min-h-screen bg-gray-50">
    <!-- Header Banner -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('resources.index') }}" class="text-gray-400 hover:text-gray-600">
                    <x-icon name="chevron-left" class="w-5 h-5" />
                </a>
                <div>
                    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                        <a href="{{ route('resources.index') }}" class="hover:text-gray-700">@term('resource_plural')</a>
                        <span>@term('breadcrumb_separator_label')</span>
                        <span class="text-gray-900">@term('program_plural')</span>
                    </nav>
                    <h1 class="text-2xl font-semibold text-gray-900">@term('program_catalog_label')</h1>
                </div>
            </div>
            <button
                wire:click="$dispatch('openAddResourceModal')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
            >
                <x-icon name="plus" class="w-4 h-4" />
                @term('add_action') @term('program_singular')
            </button>
        </div>
    </div>

    <div class="px-6 py-6">
        <!-- Filter Bar -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[250px]">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-icon name="magnifying-glass" class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="@term('search_programs_placeholder')"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                    </div>
                </div>

                <!-- Filter Dropdowns & View Toggle -->
                <div class="flex flex-wrap items-center gap-3">
                    <select
                        wire:model.live="filterType"
                        class="border-gray-300 rounded-lg text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    >
                        <option value="">@term('all_types_label')</option>
                        @foreach($this->programTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select
                        wire:model.live="filterLocation"
                        class="border-gray-300 rounded-lg text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    >
                        <option value="">@term('any_location_label')</option>
                        <option value="virtual">@term('virtual_only_label')</option>
                        <option value="in_person">@term('in_person_only_label')</option>
                        <option value="hybrid">@term('hybrid_label')</option>
                    </select>

                    <select
                        wire:model.live="filterCost"
                        class="border-gray-300 rounded-lg text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    >
                        <option value="">@term('any_cost_label')</option>
                        <option value="free">@term('free_label')</option>
                        <option value="paid">@term('paid_label')</option>
                    </select>

                    <label class="flex items-center gap-2 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model.live="showActiveOnly"
                            class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                        >
                        <span class="text-sm text-gray-700">@term('active_only_label')</span>
                    </label>

                    @if($this->hasActiveFilters)
                        <button
                            wire:click="clearFilters"
                            class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900"
                        >
                            @term('clear_all_label')
                        </button>
                    @endif

                    <!-- Divider -->
                    <div class="hidden lg:block w-px h-8 bg-gray-200"></div>

                    <!-- View Toggle -->
                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                        <button
                            wire:click="$set('viewMode', 'grid')"
                            class="p-2 {{ $viewMode === 'grid' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }} transition-colors"
                            title="@term('grid_view_label')"
                        >
                            <x-icon name="squares-2x2" class="w-4 h-4" />
                        </button>
                        <button
                            wire:click="$set('viewMode', 'list')"
                            class="p-2 border-l border-gray-300 {{ $viewMode === 'list' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }} transition-colors"
                            title="@term('list_view_label')"
                        >
                            <x-icon name="bars-3" class="w-4 h-4" />
                        </button>
                        <button
                            wire:click="$set('viewMode', 'table')"
                            class="p-2 border-l border-gray-300 {{ $viewMode === 'table' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }} transition-colors"
                            title="@term('table_view_label')"
                        >
                            <x-icon name="table-cells" class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Count -->
        <div class="mb-4">
            <p class="text-sm text-gray-600">
                @term('showing_label') <span class="font-medium">{{ $programs->count() }}</span> @term('of_label') <span class="font-medium">{{ $programs->total() }}</span> @term('program_plural')
            </p>
        </div>

        @php
            $typeColors = [
                'therapy' => 'purple',
                'tutoring' => 'blue',
                'mentorship' => 'green',
                'enrichment' => 'orange',
                'intervention' => 'red',
                'support_group' => 'teal',
                'external_service' => 'gray',
            ];
            $typeIcons = [
                'therapy' => 'heart',
                'tutoring' => 'academic-cap',
                'mentorship' => 'users',
                'enrichment' => 'sparkles',
                'intervention' => 'shield-check',
                'support_group' => 'user-group',
                'external_service' => 'building-office',
            ];
        @endphp

        @if($programs->count() > 0)
            @if($viewMode === 'grid')
                <!-- Grid View -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($programs as $program)
                        @php
                            $color = $typeColors[$program->program_type] ?? 'gray';
                            $icon = $typeIcons[$program->program_type] ?? 'building-office';
                        @endphp
                        <a
                            href="{{ route('resources.programs.show', $program) }}"
                            class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-pulse-orange-300 transition-all"
                        >
                            <div class="p-5">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-{{ $color }}-100 flex items-center justify-center flex-shrink-0">
                                        <x-icon name="{{ $icon }}" class="w-6 h-6 text-{{ $color }}-600" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">
                                            {{ $program->name }}
                                        </h3>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700 mt-1">
                                            {{ $terminology->get('program_type_' . $program->program_type . '_label') }}
                                        </span>
                                    </div>
                                </div>

                                @if($program->description)
                                    <p class="mt-3 text-sm text-gray-600 line-clamp-2">
                                        {{ $program->description }}
                                    </p>
                                @endif

                                <!-- Program Details -->
                                <div class="mt-4 flex flex-wrap gap-3 text-xs text-gray-500">
                                    @if($program->duration_weeks)
                                        <span class="flex items-center gap-1">
                                            <x-icon name="calendar" class="w-3.5 h-3.5" />
                                            {{ $program->duration_weeks }} @term('weeks_label')
                                        </span>
                                    @endif
                                    @if($program->location_type)
                                        <span class="flex items-center gap-1">
                                            <x-icon name="{{ $program->location_type === 'virtual' ? 'globe-alt' : 'map-pin' }}" class="w-3.5 h-3.5" />
                                            {{ $terminology->get('location_type_' . $program->location_type . '_label') }}
                                        </span>
                                    @endif
                                    @if($program->cost_structure === 'free')
                                        <span class="flex items-center gap-1 text-green-600">
                                            <x-icon name="check-circle" class="w-3.5 h-3.5" />
                                            @term('free_label')
                                        </span>
                                    @elseif($program->cost_details)
                                        <span class="flex items-center gap-1">
                                            <x-icon name="currency-dollar" class="w-3.5 h-3.5" />
                                            {{ $program->cost_details }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Card Footer with Capacity -->
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                                @if($program->capacity)
                                    @php $spotsLeft = $program->spots_remaining ?? $program->capacity; @endphp
                                    <span class="text-xs {{ $spotsLeft > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        @if($spotsLeft > 0)
                                            {{ $spotsLeft }} @term('spots_available_label')
                                        @else
                                            @term('full_label')
                                        @endif
                                    </span>
                                @else
                                        <span class="text-xs text-gray-500">@term('open_enrollment_label')</span>
                                @endif
                                <span class="text-xs font-medium text-pulse-orange-600 group-hover:text-pulse-orange-700">
                                    @term('learn_more_label') &rarr;
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

            @elseif($viewMode === 'list')
                <!-- List View -->
                <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                    @foreach($programs as $program)
                        @php
                            $color = $typeColors[$program->program_type] ?? 'gray';
                            $icon = $typeIcons[$program->program_type] ?? 'building-office';
                        @endphp
                        <a
                            href="{{ route('resources.programs.show', $program) }}"
                            class="flex items-center gap-5 p-5 hover:bg-gray-50 transition-colors group"
                        >
                            <!-- Icon -->
                            <div class="w-12 h-12 rounded-xl bg-{{ $color }}-100 flex items-center justify-center flex-shrink-0">
                                <x-icon name="{{ $icon }}" class="w-6 h-6 text-{{ $color }}-600" />
                            </div>

                            <!-- Info -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">
                                        {{ $program->name }}
                                    </h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700">
                                        {{ $terminology->get('program_type_' . $program->program_type . '_label') }}
                                    </span>
                                </div>
                                @if($program->description)
                                    <p class="text-sm text-gray-500 line-clamp-1">{{ $program->description }}</p>
                                @endif
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                    @if($program->duration_weeks)
                                        <span class="flex items-center gap-1">
                                            <x-icon name="calendar" class="w-3.5 h-3.5" />
                                            {{ $program->duration_weeks }} @term('weeks_label')
                                        </span>
                                    @endif
                                    @if($program->location_type)
                                        <span class="flex items-center gap-1">
                                            <x-icon name="{{ $program->location_type === 'virtual' ? 'globe-alt' : 'map-pin' }}" class="w-3.5 h-3.5" />
                                            {{ $terminology->get('location_type_' . $program->location_type . '_label') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Cost & Availability -->
                            <div class="flex items-center gap-6 flex-shrink-0">
                                <!-- Cost -->
                                <div class="text-right">
                                    @if($program->cost_structure === 'free')
                                        <span class="inline-flex items-center gap-1 text-sm font-medium text-green-600">
                                            <x-icon name="check-circle" class="w-4 h-4" />
                                            @term('free_label')
                                        </span>
                                    @elseif($program->cost_details)
                                        <span class="text-sm text-gray-600">{{ $program->cost_details }}</span>
                                    @else
                                        <span class="text-sm text-gray-400">@term('empty_value_placeholder')</span>
                                    @endif
                                </div>

                                <!-- Availability -->
                                @if($program->capacity)
                                    @php $spotsLeft = $program->spots_remaining ?? $program->capacity; @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium {{ $spotsLeft > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                        @if($spotsLeft > 0)
                                            {{ $spotsLeft }} @term('spots_label')
                                        @else
                                            @term('full_label')
                                        @endif
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                        @term('open_label')
                                    </span>
                                @endif

                                <!-- Arrow -->
                                <x-icon name="chevron-right" class="w-5 h-5 text-gray-400 group-hover:text-pulse-orange-500 transition-colors" />
                            </div>
                        </a>
                    @endforeach
                </div>

            @else
                <!-- Table View -->
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('program_singular')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('type_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('duration_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('location_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('cost_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('availability_label')</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">@term('actions_label')</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($programs as $program)
                                    @php
                                        $color = $typeColors[$program->program_type] ?? 'gray';
                                        $icon = $typeIcons[$program->program_type] ?? 'building-office';
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-{{ $color }}-100 flex items-center justify-center flex-shrink-0">
                                                    <x-icon name="{{ $icon }}" class="w-5 h-5 text-{{ $color }}-600" />
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $program->name }}</div>
                                                    @if($program->provider_org_name)
                                                        <div class="text-xs text-gray-500">{{ $program->provider_org_name }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700">
                                                {{ $terminology->get('program_type_' . $program->program_type . '_label') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            @if($program->duration_weeks)
                                                {{ $program->duration_weeks }} @term('weeks_label')
                                                @if($program->frequency_per_week)
                                                    <span class="text-gray-400">({{ $program->frequency_per_week }}@term('times_per_week_label'))</span>
                                                @endif
                                            @else
                                                <span class="text-gray-400">@term('empty_value_placeholder')</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            @if($program->location_type)
                                                <span class="flex items-center gap-1">
                                                    <x-icon name="{{ $program->location_type === 'virtual' ? 'globe-alt' : 'map-pin' }}" class="w-4 h-4" />
                                                    {{ $terminology->get('location_type_' . $program->location_type . '_label') }}
                                                </span>
                                            @else
                                                <span class="text-gray-400">@term('empty_value_placeholder')</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @if($program->cost_structure === 'free')
                                                <span class="text-green-600 font-medium">@term('free_label')</span>
                                            @elseif($program->cost_details)
                                                <span class="text-gray-600">{{ $program->cost_details }}</span>
                                            @else
                                                <span class="text-gray-400">@term('empty_value_placeholder')</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($program->capacity)
                                                @php $spotsLeft = $program->spots_remaining ?? $program->capacity; @endphp
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $spotsLeft > 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                                    @if($spotsLeft > 0)
                                                        {{ $spotsLeft }} / {{ $program->capacity }}
                                                    @else
                                                        @term('full_label')
                                                    @endif
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                                    @term('open_label')
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('resources.programs.show', $program) }}" class="text-pulse-orange-600 hover:text-pulse-orange-700">
                                                @term('view_action')
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Pagination -->
            <div class="mt-6">
                {{ $programs->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl border border-gray-200 text-center py-16">
                <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                    <x-icon name="building-office" class="w-8 h-8 text-green-400" />
                </div>
                @if($this->hasActiveFilters)
                    <h3 class="text-lg font-medium text-gray-900 mb-1">@term('no_programs_match_filters_label')</h3>
                    <p class="text-gray-500 mb-4">@term('try_adjusting_filters_label')</p>
                    <button
                        wire:click="clearFilters"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        @term('clear_filters_label')
                    </button>
                @else
                    <h3 class="text-lg font-medium text-gray-900 mb-1">@term('no_programs_yet_label')</h3>
                    <p class="text-gray-500 mb-4">@term('program_catalog_empty_label')</p>
                    <button
                        wire:click="$dispatch('openAddResourceModal')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                    >
                        <x-icon name="plus" class="w-4 h-4" />
                        @term('add_action') @term('program_singular')
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
