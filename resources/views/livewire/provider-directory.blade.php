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
                        <a href="{{ route('resources.index') }}" class="hover:text-gray-700">Resources</a>
                        <span>/</span>
                        <span class="text-gray-900">Providers</span>
                    </nav>
                    <h1 class="text-2xl font-semibold text-gray-900">Provider Directory</h1>
                </div>
            </div>
            <button
                wire:click="$dispatch('openAddResourceModal')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
            >
                <x-icon name="plus" class="w-4 h-4" />
                Add Provider
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
                            placeholder="Search providers by name, specialty, or bio..."
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
                        <option value="">All Types</option>
                        @foreach($this->providerTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select
                        wire:model.live="filterAvailability"
                        class="border-gray-300 rounded-lg text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    >
                        <option value="">Any Availability</option>
                        @foreach($this->availabilityOptions as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    <select
                        wire:model.live="filterLocation"
                        class="border-gray-300 rounded-lg text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    >
                        <option value="">Any Location</option>
                        <option value="remote">Remote Available</option>
                        <option value="in_person">In-Person Only</option>
                    </select>

                    @if($this->hasActiveFilters)
                        <button
                            wire:click="clearFilters"
                            class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900"
                        >
                            Clear
                        </button>
                    @endif

                    <!-- Divider -->
                    <div class="hidden lg:block w-px h-8 bg-gray-200"></div>

                    <!-- View Toggle -->
                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                        <button
                            wire:click="$set('viewMode', 'grid')"
                            class="p-2 {{ $viewMode === 'grid' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }} transition-colors"
                            title="Grid view"
                        >
                            <x-icon name="squares-2x2" class="w-4 h-4" />
                        </button>
                        <button
                            wire:click="$set('viewMode', 'list')"
                            class="p-2 border-l border-gray-300 {{ $viewMode === 'list' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }} transition-colors"
                            title="List view"
                        >
                            <x-icon name="bars-3" class="w-4 h-4" />
                        </button>
                        <button
                            wire:click="$set('viewMode', 'table')"
                            class="p-2 border-l border-gray-300 {{ $viewMode === 'table' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }} transition-colors"
                            title="Table view"
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
                Showing <span class="font-medium">{{ $providers->count() }}</span> of <span class="font-medium">{{ $providers->total() }}</span> providers
            </p>
        </div>

        @if($providers->count() > 0)
            @if($viewMode === 'grid')
                <!-- Grid View - Hunhu Style Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($providers as $provider)
                        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-pulse-orange-300 transition-all group">
                            <!-- Photo Area -->
                            <div class="aspect-square bg-gradient-to-br from-purple-100 to-purple-50 flex items-center justify-center relative overflow-hidden">
                                @if($provider->thumbnail_url)
                                    <img
                                        src="{{ $provider->thumbnail_url }}"
                                        alt="{{ $provider->name }}"
                                        class="w-full h-full object-cover"
                                    >
                                @else
                                    <div class="w-24 h-24 rounded-full bg-purple-200 flex items-center justify-center">
                                        <x-icon name="user" class="w-12 h-12 text-purple-500" />
                                    </div>
                                @endif

                                <!-- Verified Badge -->
                                @if($provider->isVerified())
                                    <div class="absolute top-3 right-3">
                                        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700 shadow-sm">
                                            <x-icon name="check-badge" class="w-3.5 h-3.5" />
                                            Verified
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Card Content -->
                            <div class="p-5">
                                <!-- Name & Credentials -->
                                <h3 class="text-lg font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">
                                    {{ $provider->name }}
                                </h3>
                                @if($provider->credentials)
                                    <p class="text-sm text-gray-500">{{ $provider->credentials }}</p>
                                @endif

                                <!-- Provider Type -->
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                        {{ ucfirst($provider->provider_type) }}
                                    </span>
                                </div>

                                <!-- Specialties -->
                                @if($provider->specialty_areas && count($provider->specialty_areas) > 0)
                                    <p class="text-sm text-gray-600 mt-3">
                                        {{ implode(', ', array_slice($provider->specialty_areas, 0, 3)) }}
                                        @if(count($provider->specialty_areas) > 3)
                                            <span class="text-gray-400">+{{ count($provider->specialty_areas) - 3 }} more</span>
                                        @endif
                                    </p>
                                @endif

                                <!-- Bio Preview -->
                                @if($provider->bio)
                                    <p class="text-sm text-gray-500 mt-2 line-clamp-2">{{ $provider->bio }}</p>
                                @endif

                                <!-- Location & Rating Row -->
                                <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                    <div class="flex items-center gap-2 text-xs text-gray-500">
                                        @if($provider->serves_remote)
                                            <span class="inline-flex items-center gap-1">
                                                <x-icon name="globe-alt" class="w-3.5 h-3.5" />
                                                Remote
                                            </span>
                                        @endif
                                        @if($provider->serves_in_person)
                                            <span class="inline-flex items-center gap-1">
                                                <x-icon name="map-pin" class="w-3.5 h-3.5" />
                                                In-Person
                                            </span>
                                        @endif
                                    </div>
                                    @if($provider->ratings_count > 0)
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-yellow-700">
                                            <x-icon name="star" class="w-3.5 h-3.5 text-yellow-500" />
                                            {{ number_format($provider->ratings_average, 1) }}
                                        </span>
                                    @endif
                                </div>

                                <!-- View Profile Button -->
                                <a
                                    href="{{ route('resources.providers.show', $provider) }}"
                                    class="mt-4 w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-900 text-white text-sm font-medium rounded-lg hover:bg-gray-800 transition-colors"
                                >
                                    View Profile
                                </a>
                            </div>
                        </div>
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
                                            Verified
                                        </span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-600">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                        {{ ucfirst($provider->provider_type) }}
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
                                            Remote
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
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialties</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
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
                                                {{ ucfirst($provider->provider_type) }}
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
                                                <span class="text-sm text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <div class="flex items-center gap-2">
                                                @if($provider->serves_remote)
                                                    <span class="inline-flex items-center gap-1 text-xs">
                                                        <x-icon name="globe-alt" class="w-3.5 h-3.5" />
                                                        Remote
                                                    </span>
                                                @endif
                                                @if($provider->serves_in_person)
                                                    <span class="inline-flex items-center gap-1 text-xs">
                                                        <x-icon name="map-pin" class="w-3.5 h-3.5" />
                                                        In-Person
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($provider->isVerified())
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                                    <x-icon name="check-badge" class="w-3.5 h-3.5" />
                                                    Verified
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                                    Unverified
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
                                                <span class="text-sm text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('resources.providers.show', $provider) }}" class="text-pulse-orange-600 hover:text-pulse-orange-700">
                                                View
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
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No providers match your filters</h3>
                    <p class="text-gray-500 mb-4">Try adjusting your search or filter criteria.</p>
                    <button
                        wire:click="clearFilters"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Clear filters
                    </button>
                @else
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No providers yet</h3>
                    <p class="text-gray-500 mb-4">Add therapists, tutors, coaches, and other service providers to your directory.</p>
                    <button
                        wire:click="$dispatch('openAddResourceModal')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                    >
                        <x-icon name="plus" class="w-4 h-4" />
                        Add Provider
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
