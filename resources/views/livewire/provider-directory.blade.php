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
                        <span class="text-gray-900">@term('provider_plural')</span>
                    </nav>
                    <h1 class="text-2xl font-semibold text-gray-900">@term('provider_directory_label')</h1>
                </div>
            </div>
            <button
                wire:click="$dispatch('openAddResourceModal')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
            >
                <x-icon name="plus" class="w-4 h-4" />
                @term('add_provider_label')
            </button>
        </div>
    </div>

    <div class="px-6 py-6">
        <!-- Search & Filter Bar -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
            <div class="flex flex-col lg:flex-row gap-4">
                <!-- Search -->
                <div class="flex-1">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-icon name="magnifying-glass" class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="@term('search_providers_placeholder')"
                            class="block w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                    </div>
                </div>

                <!-- Filter Dropdowns & View Toggle -->
                <div class="flex flex-wrap gap-3 items-center">
                    <select
                        wire:model.live="filterType"
                        class="border-gray-300 rounded-lg text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    >
                        <option value="">@term('all_types_label')</option>
                        @foreach($this->providerTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select
                        wire:model.live="filterAvailability"
                        class="border-gray-300 rounded-lg text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    >
                        <option value="">@term('any_availability_label')</option>
                        @foreach($this->availabilityOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select
                        wire:model.live="filterLocation"
                        class="border-gray-300 rounded-lg text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    >
                        <option value="">@term('any_location_label')</option>
                        <option value="remote">@term('remote_available_label')</option>
                        <option value="in_person">@term('in_person_only_label')</option>
                    </select>

                    @if($this->hasActiveFilters)
                        <button
                            wire:click="clearFilters"
                            class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900"
                        >
                            @term('clear_action')
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
                @term('showing_label') <span class="font-medium">{{ $providers->count() }}</span> @term('of_label') <span class="font-medium">{{ $providers->total() }}</span> @term('provider_plural')
            </p>
        </div>

        @if($providers->count() > 0)
            @if($viewMode === 'grid')
                <!-- Grid View - Hunhu Style Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($providers as $provider)
                        <a href="{{ route('resources.providers.show', $provider) }}"
                           class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-pulse-orange-300 transition-all flex flex-col h-full">
                            <div class="p-5 flex-1">
                                <!-- Header Row: Avatar + Name/Type -->
                                <div class="flex items-start gap-4">
                                    <div class="w-14 h-14 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                        @if($provider->thumbnail_url)
                                            <img src="{{ $provider->thumbnail_url }}" alt="{{ $provider->name }}" class="w-14 h-14 object-cover">
                                        @else
                                            <x-icon name="user" class="w-7 h-7 text-purple-600" />
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h3 class="text-base font-semibold text-gray-900 truncate group-hover:text-pulse-orange-600 transition-colors">
                                                {{ $provider->name }}
                                            </h3>
                                            @if($provider->isVerified())
                                                <x-icon name="check-badge" class="w-4 h-4 text-green-600 flex-shrink-0" />
                                            @endif
                                        </div>
                                        @if($provider->credentials)
                                            <p class="text-sm text-gray-500 truncate">{{ $provider->credentials }}</p>
                                        @endif
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700 mt-1">
                                            {{ $terminology->get('provider_type_'.$provider->provider_type.'_label') }}
                                        </span>
                                    </div>
                                </div>

                                <!-- Specialties -->
                                @if($provider->specialty_areas && count($provider->specialty_areas) > 0)
                                    <p class="text-sm text-gray-600 mt-3 line-clamp-1">
                                        {{ implode(', ', array_slice($provider->specialty_areas, 0, 3)) }}
                                        @if(count($provider->specialty_areas) > 3)
                                            <span class="text-gray-400">+{{ count($provider->specialty_areas) - 3 }}</span>
                                        @endif
                                    </p>
                                @endif

                                <!-- Bio -->
                                @if($provider->bio)
                                    <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $provider->bio }}</p>
                                @endif
                            </div>

                            <!-- Card Footer -->
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between mt-auto">
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    @if($provider->serves_remote)
                                            <span class="inline-flex items-center gap-1">
                                                <x-icon name="globe-alt" class="w-3.5 h-3.5" />
                                                @term('remote_label')
                                            </span>
                                        @endif
                                    @if($provider->ratings_count > 0)
                                        <span class="inline-flex items-center gap-1 font-medium text-yellow-700">
                                            <x-icon name="star" class="w-3.5 h-3.5 text-yellow-500" />
                                            {{ number_format($provider->ratings_average, 1) }}
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs font-medium text-pulse-orange-600 group-hover:text-pulse-orange-700">
                                    @term('view_profile_label') &rarr;
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

            @elseif($viewMode === 'list')
                <!-- List View - Horizontal Rows -->
                <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                    @foreach($providers as $provider)
                        <a
                            href="{{ route('resources.providers.show', $provider) }}"
                            class="flex items-center gap-5 p-5 hover:bg-gray-50 transition-colors group"
                        >
                            <!-- Avatar -->
                            <div class="w-16 h-16 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                @if($provider->thumbnail_url)
                                    <img src="{{ $provider->thumbnail_url }}" alt="{{ $provider->name }}" class="w-16 h-16 object-cover">
                                @else
                                    <x-icon name="user" class="w-8 h-8 text-purple-600" />
                                @endif
                            </div>

                            <!-- Info -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">
                                        {{ $provider->name }}
                                    </h3>
                                    @if($provider->credentials)
                                        <span class="text-sm text-gray-500">{{ $provider->credentials }}</span>
                                    @endif
                                    @if($provider->isVerified())
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <x-icon name="check-badge" class="w-3.5 h-3.5" />
                                            @term('verified_label')
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                        {{ $terminology->get('provider_type_'.$provider->provider_type.'_label') }}
                                    </span>
                                    @if($provider->specialty_areas && count($provider->specialty_areas) > 0)
                                        <span class="text-gray-400">&bull;</span>
                                        <span>{{ implode(', ', array_slice($provider->specialty_areas, 0, 3)) }}</span>
                                    @endif
                                </div>
                                @if($provider->bio)
                                    <p class="text-sm text-gray-500 mt-1 line-clamp-1">{{ $provider->bio }}</p>
                                @endif
                            </div>

                            <!-- Status & Actions -->
                            <div class="flex items-center gap-4 flex-shrink-0">
                                <!-- Location -->
                                <div class="text-right space-y-1">
                                    @if($provider->serves_remote)
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-600">
                                            <x-icon name="globe-alt" class="w-3.5 h-3.5" />
                                            @term('remote_label')
                                        </span>
                                    @endif
                                    @if($provider->location_address)
                                        <span class="block text-xs text-gray-500">
                                            {{ Str::limit($provider->location_address, 25) }}
                                        </span>
                                    @endif
                                </div>

                                <!-- Rating Badge -->
                                @if($provider->ratings_count > 0)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-700">
                                        <x-icon name="star" class="w-3.5 h-3.5" />
                                        {{ number_format($provider->ratings_average, 1) }}
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('provider_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('type_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('specialties_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('location_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('status_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('rating_label')</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">@term('actions_label')</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($providers as $provider)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                                    @if($provider->thumbnail_url)
                                                        <img src="{{ $provider->thumbnail_url }}" alt="{{ $provider->name }}" class="w-10 h-10 object-cover">
                                                    @else
                                                        <x-icon name="user" class="w-5 h-5 text-purple-600" />
                                                    @endif
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $provider->name }}</div>
                                                    @if($provider->credentials)
                                                        <div class="text-xs text-gray-500">{{ $provider->credentials }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                                {{ $terminology->get('provider_type_'.$provider->provider_type.'_label') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($provider->specialty_areas && count($provider->specialty_areas) > 0)
                                                <div class="text-sm text-gray-600 max-w-xs truncate">
                                                    {{ implode(', ', array_slice($provider->specialty_areas, 0, 2)) }}
                                                    @if(count($provider->specialty_areas) > 2)
                                                        <span class="text-gray-400">+{{ count($provider->specialty_areas) - 2 }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-400">@term('empty_value_placeholder')</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <div class="flex items-center gap-2">
                                                @if($provider->serves_remote)
                                                    <span class="inline-flex items-center gap-1 text-xs">
                                                        <x-icon name="globe-alt" class="w-3.5 h-3.5" />
                                                        @term('remote_label')
                                                    </span>
                                                @endif
                                                @if($provider->serves_in_person)
                                                    <span class="inline-flex items-center gap-1 text-xs">
                                                        <x-icon name="map-pin" class="w-3.5 h-3.5" />
                                                        @term('in_person_label')
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($provider->isVerified())
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                    <x-icon name="check-badge" class="w-3.5 h-3.5" />
                                                    @term('verified_label')
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                    @term('unverified_label')
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($provider->ratings_count > 0)
                                                <span class="inline-flex items-center gap-1 text-sm font-medium text-yellow-700">
                                                    <x-icon name="star" class="w-4 h-4 text-yellow-500" />
                                                    {{ number_format($provider->ratings_average, 1) }}
                                                    <span class="text-gray-400 font-normal">({{ $provider->ratings_count }})</span>
                                                </span>
                                            @else
                                                <span class="text-sm text-gray-400">@term('empty_dash_label')</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('resources.providers.show', $provider) }}" class="text-pulse-orange-600 hover:text-pulse-orange-700">
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
                {{ $providers->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl border border-gray-200 text-center py-16">
                <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center mx-auto mb-4">
                    <x-icon name="users" class="w-8 h-8 text-purple-400" />
                </div>
                @if($this->hasActiveFilters)
                    <h3 class="text-lg font-medium text-gray-900 mb-1">@term('no_providers_match_filters_label')</h3>
                    <p class="text-gray-500 mb-4">@term('adjust_search_or_filters_label')</p>
                    <button
                        wire:click="clearFilters"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        @term('clear_filters_label')
                    </button>
                @else
                    <h3 class="text-lg font-medium text-gray-900 mb-1">@term('no_providers_yet_label')</h3>
                    <p class="text-gray-500 mb-4">@term('provider_directory_empty_help_label')</p>
                    <button
                        wire:click="$dispatch('openAddResourceModal')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                    >
                        <x-icon name="plus" class="w-4 h-4" />
                        @term('add_provider_label')
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
