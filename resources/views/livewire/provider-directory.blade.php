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

    <div class="px-6 py-6 max-w-5xl mx-auto">
        <!-- Search & Filter Bar -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
            <div class="flex flex-col sm:flex-row gap-4">
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

                <!-- Filter Dropdowns -->
                <div class="flex gap-3">
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

                    @if($this->hasActiveFiltersProperty)
                        <button
                            wire:click="clearFilters"
                            class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900"
                        >
                            Clear
                        </button>
                    @endif
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
            <!-- Provider List (not cards) -->
            <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                @foreach($providers as $provider)
                    <a
                        href="{{ route('resources.providers.show', $provider) }}"
                        class="flex items-center gap-5 p-5 hover:bg-gray-50 transition-colors group"
                    >
                        <!-- Avatar -->
                        <div class="w-14 h-14 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                            @if($provider->photo_url)
                                <img src="{{ $provider->photo_url }}" alt="{{ $provider->name }}" class="w-14 h-14 rounded-full object-cover">
                            @else
                                <x-icon name="user" class="w-7 h-7 text-purple-600" />
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
                                <span class="font-medium">{{ ucfirst($provider->provider_type) }}</span>
                                @if($provider->specialty_areas && count($provider->specialty_areas) > 0)
                                    <span>&bull;</span>
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
                            <div class="text-right">
                                @if($provider->serves_remote)
                                    <span class="inline-flex items-center gap-1 text-xs text-gray-600">
                                        <x-icon name="globe-alt" class="w-3.5 h-3.5" />
                                        Remote
                                    </span>
                                @endif
                                @if($provider->location_address)
                                    <span class="inline-flex items-center gap-1 text-xs text-gray-600 {{ $provider->serves_remote ? 'ml-2' : '' }}">
                                        <x-icon name="map-pin" class="w-3.5 h-3.5" />
                                        {{ Str::limit($provider->location_address, 30) }}
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
                @if($this->hasActiveFiltersProperty)
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
