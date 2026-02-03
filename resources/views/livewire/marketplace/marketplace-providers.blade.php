@php
    $terminology = app(\App\Services\TerminologyService::class);
@endphp

<div class="min-h-screen bg-gray-50">
    <!-- Header Banner -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('marketplace.index') }}" class="text-gray-400 hover:text-gray-600">
                    <x-icon name="chevron-left" class="w-5 h-5" />
                </a>
                <div>
                    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                        <a href="{{ route('marketplace.index') }}" class="hover:text-gray-700">@term('marketplace_label')</a>
                        <span>/</span>
                        <span class="text-gray-900">@term('providers_label')</span>
                    </nav>
                    <h1 class="text-2xl font-semibold text-gray-900">@term('providers_label')</h1>
                </div>
            </div>
            @if($this->hasSellerProfile ?? false)
                <a
                    href="{{ route('marketplace.seller.items.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                >
                    <x-icon name="plus" class="w-4 h-4" />
                    @term('list_your_services_label')
                </a>
            @endif
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
                        placeholder="{{ $terminology->get('search_providers_placeholder') }}"
                        class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    >
                </div>
            </div>

            <!-- Verified Only -->
            <div class="mb-6">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input
                        type="checkbox"
                        wire:click="toggleVerified"
                        @checked($verifiedOnly)
                        class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                    >
                    <span class="text-sm text-gray-700 group-hover:text-gray-900 flex items-center gap-1">
                        <x-icon name="check-badge" class="w-4 h-4 text-blue-500" />
                        @term('verified_only_label')
                    </span>
                </label>
            </div>

            <!-- Price Filter -->
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">@term('price_label')</h3>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input
                            type="radio"
                            wire:model.live="priceFilter"
                            value=""
                            class="w-4 h-4 border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                        >
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">@term('all_prices_label')</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input
                            type="radio"
                            wire:model.live="priceFilter"
                            value="free"
                            class="w-4 h-4 border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                        >
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">@term('free_consultation_label')</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input
                            type="radio"
                            wire:model.live="priceFilter"
                            value="paid"
                            class="w-4 h-4 border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                        >
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">@term('paid_services_label')</span>
                    </label>
                </div>
            </div>

            <!-- Rating Filter -->
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">@term('rating_label')</h3>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input
                            type="checkbox"
                            wire:click="$set('ratingFilter', $wire.ratingFilter === '4plus' ? '' : '4plus')"
                            @checked($ratingFilter === '4plus')
                            class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                        >
                        <span class="text-sm text-gray-700 group-hover:text-gray-900 flex items-center gap-1">
                            <x-icon name="star" class="w-4 h-4 text-amber-400" solid />
                            @term('rating_4_plus_label')
                        </span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input
                            type="checkbox"
                            wire:click="$set('ratingFilter', $wire.ratingFilter === '3plus' ? '' : '3plus')"
                            @checked($ratingFilter === '3plus')
                            class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                        >
                        <span class="text-sm text-gray-700 group-hover:text-gray-900 flex items-center gap-1">
                            <x-icon name="star" class="w-4 h-4 text-amber-400" solid />
                            @term('rating_3_plus_label')
                        </span>
                    </label>
                </div>
            </div>

            <!-- Provider Type Filter -->
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">@term('provider_type_label')</h3>
                <div class="space-y-2">
                    @foreach($this->providerTypes as $value => $label)
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

            <!-- Clear Filters -->
            @if($this->hasActiveFilters)
                <button
                    wire:click="clearFilters"
                    class="w-full text-sm text-pulse-orange-600 hover:text-pulse-orange-700 font-medium"
                >
                    @term('clear_all_filters_label')
                </button>
            @endif
        </div>

        <!-- Main Content Area -->
        <div class="flex-1 p-6">
            <!-- Sort & Count Bar -->
            <div class="flex items-center justify-between mb-6">
                <p class="text-sm text-gray-600">
                    @term('showing_label') <span class="font-medium">{{ $items->count() }}</span> @term('of_label') <span class="font-medium">{{ $items->total() }}</span> @term('providers_label')
                </p>
                <div class="flex items-center gap-4">
                    <!-- View Toggle -->
                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                        <button
                            wire:click="$set('viewMode', 'grid')"
                            class="p-2 {{ $viewMode === 'grid' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }}"
                            title="{{ $terminology->get('grid_view_label') }}"
                        >
                            <x-icon name="squares-2x2" class="w-4 h-4" />
                        </button>
                        <button
                            wire:click="$set('viewMode', 'list')"
                            class="p-2 border-l border-gray-300 {{ $viewMode === 'list' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }}"
                            title="{{ $terminology->get('list_view_label') }}"
                        >
                            <x-icon name="bars-3" class="w-4 h-4" />
                        </button>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm text-gray-500">@term('sort_by_label'):</span>
                        <select
                            wire:model.live="sortBy"
                            class="border-gray-300 rounded-lg text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                            <option value="popular">@term('most_popular_label')</option>
                            <option value="newest">@term('newest_label')</option>
                            <option value="rating">@term('highest_rated_label')</option>
                        </select>
                    </div>
                </div>
            </div>

            @if($items->count() > 0)
                @if($viewMode === 'grid')
                    <!-- Grid View -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($items as $item)
                            <a href="{{ route('marketplace.item', $item->uuid) }}" class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-pulse-orange-300 transition-all">
                                <!-- Provider Photo -->
                                <div class="aspect-[4/3] bg-gray-100 relative overflow-hidden">
                                    @if($item->thumbnail_url)
                                        <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center bg-purple-100">
                                            <x-icon name="user" class="w-16 h-16 text-purple-400" />
                                        </div>
                                    @endif
                                    @if($item->is_verified)
                                        <div class="absolute top-3 right-3">
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-blue-500 text-white text-xs font-medium">
                                                <x-icon name="check-badge" class="w-3 h-3" />
                                                @term('verified_label')
                                            </span>
                                        </div>
                                    @endif
                                </div>
                                <!-- Info -->
                                <div class="p-5">
                                    <h3 class="font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">{{ $item->title }}</h3>
                                    <p class="text-sm text-gray-500 mt-1">{{ $item->seller->display_name }}</p>
                                    @if($item->short_description)
                                        <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ $item->short_description }}</p>
                                    @endif
                                    <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                                        @if($item->ratings_count > 0)
                                            <div class="flex items-center gap-1">
                                                <x-icon name="star" class="w-4 h-4 text-amber-400" solid />
                                                <span class="text-sm font-medium text-gray-900">{{ number_format($item->ratings_average, 1) }}</span>
                                                <span class="text-sm text-gray-400">({{ $item->ratings_count }})</span>
                                            </div>
                                        @else
                                            <span class="text-sm text-gray-400">@term('no_reviews_yet_label')</span>
                                        @endif
                                        <span class="text-sm font-semibold {{ $item->isFree() ? 'text-green-600' : 'text-gray-900' }}">
                                            {{ $item->isFree() ? $terminology->get('free_consultation_label') : $terminology->get('contact_for_rates_label') }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <!-- List View (Default for providers) -->
                    <div class="space-y-4">
                        @foreach($items as $item)
                            <a href="{{ route('marketplace.item', $item->uuid) }}" class="block bg-white rounded-xl border border-gray-200 p-5 hover:shadow-lg hover:border-pulse-orange-300 transition-all group">
                                <div class="flex items-start gap-5">
                                    <!-- Photo -->
                                    @if($item->thumbnail_url)
                                        <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="w-20 h-20 rounded-xl object-cover flex-shrink-0">
                                    @else
                                        <div class="w-20 h-20 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0">
                                            <x-icon name="user" class="w-10 h-10 text-purple-400" />
                                        </div>
                                    @endif

                                    <!-- Content -->
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">{{ $item->title }}</h3>
                                                    @if($item->is_verified)
                                                        <x-icon name="check-badge" class="w-5 h-5 text-blue-500" />
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-500 mt-0.5">{{ $item->seller->display_name }}</p>
                                            </div>
                                            <div class="text-right flex-shrink-0">
                                                @if($item->ratings_count > 0)
                                                    <div class="flex items-center gap-1">
                                                        <x-icon name="star" class="w-4 h-4 text-amber-400" solid />
                                                        <span class="font-semibold text-gray-900">{{ number_format($item->ratings_average, 1) }}</span>
                                                    </div>
                                                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->ratings_count }} {{ Str::plural($terminology->get('review_label'), $item->ratings_count) }}</p>
                                                @else
                                                    <span class="text-sm text-gray-400">@term('no_reviews_label')</span>
                                                @endif
                                            </div>
                                        </div>

                                        @if($item->short_description)
                                            <p class="text-sm text-gray-600 mt-3 line-clamp-2">{{ $item->short_description }}</p>
                                        @endif

                                        <!-- Tags / Specialties -->
                                        @if($item->subcategories && count($item->subcategories) > 0)
                                            <div class="flex flex-wrap gap-2 mt-3">
                                                @foreach(array_slice($item->subcategories, 0, 4) as $specialty)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-600 text-xs">
                                                        {{ ucfirst(str_replace('_', ' ', $specialty)) }}
                                                    </span>
                                                @endforeach
                                                @if(count($item->subcategories) > 4)
                                                    <span class="text-xs text-gray-400">+{{ count($item->subcategories) - 4 }} {{ $terminology->get('more_label') }}</span>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="flex items-center gap-4 mt-4">
                                            <span class="text-sm font-semibold {{ $item->isFree() ? 'text-green-600' : 'text-gray-900' }}">
                                                {{ $item->isFree() ? $terminology->get('free_consultation_label') : $terminology->get('contact_for_rates_label') }}
                                            </span>
                                            <span class="text-sm text-pulse-orange-600 group-hover:text-pulse-orange-700 font-medium">{{ $terminology->get('view_profile_label') }} &rarr;</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $items->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-16 h-16 rounded-full bg-purple-100 flex items-center justify-center mx-auto mb-4">
                        <x-icon name="users" class="w-8 h-8 text-purple-600" />
                    </div>
                    @if($this->hasActiveFilters)
                        <h3 class="text-lg font-medium text-gray-900 mb-1">@term('no_providers_match_filters_label')</h3>
                        <p class="text-gray-500 mb-4">@term('adjust_filter_criteria_label')</p>
                        <button
                            wire:click="clearFilters"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            @term('clear_filters_label')
                        </button>
                    @else
                        <h3 class="text-lg font-medium text-gray-900 mb-1">@term('no_providers_available_yet_label')</h3>
                        <p class="text-gray-500 mb-4">@term('be_first_offer_services_label')</p>
                        <a
                            href="{{ route('marketplace.seller.create') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                        >
                            <x-icon name="plus" class="w-4 h-4" />
                            @term('list_your_services_label')
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
