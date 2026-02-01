<div class="flex">
    <!-- Left Filter Sidebar -->
    <div class="w-64 bg-white border-r border-gray-200 min-h-[calc(100vh-140px)] p-4 flex-shrink-0">
        <!-- Search -->
        <div class="mb-6">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <x-icon name="magnifying-glass" class="h-4 w-4 text-gray-400" />
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search marketplace..."
                    class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                >
                @if($search)
                    <button
                        wire:click="clearSearch"
                        class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600"
                    >
                        <x-icon name="x-mark" class="h-4 w-4" />
                    </button>
                @endif
            </div>
        </div>

        <!-- Category Filter -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</h3>
                <div class="flex items-center gap-2">
                    <button
                        wire:click="selectAllCategories"
                        class="text-xs text-pulse-orange-600 hover:text-pulse-orange-700"
                    >
                        All
                    </button>
                    <span class="text-gray-300">|</span>
                    <button
                        wire:click="clearCategories"
                        class="text-xs text-gray-500 hover:text-gray-700"
                    >
                        Clear
                    </button>
                </div>
            </div>
            <div class="space-y-2">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input
                        type="checkbox"
                        wire:click="toggleCategory('survey')"
                        @checked(in_array('survey', $selectedCategories))
                        class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                    >
                    <span class="text-sm text-gray-700 group-hover:text-gray-900 flex items-center gap-2">
                        <x-icon name="clipboard-document-list" class="w-4 h-4 text-blue-500" />
                        Surveys
                    </span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input
                        type="checkbox"
                        wire:click="toggleCategory('strategy')"
                        @checked(in_array('strategy', $selectedCategories))
                        class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                    >
                    <span class="text-sm text-gray-700 group-hover:text-gray-900 flex items-center gap-2">
                        <x-icon name="light-bulb" class="w-4 h-4 text-amber-500" />
                        Strategies
                    </span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input
                        type="checkbox"
                        wire:click="toggleCategory('content')"
                        @checked(in_array('content', $selectedCategories))
                        class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                    >
                    <span class="text-sm text-gray-700 group-hover:text-gray-900 flex items-center gap-2">
                        <x-icon name="document-text" class="w-4 h-4 text-emerald-500" />
                        Content
                    </span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input
                        type="checkbox"
                        wire:click="toggleCategory('provider')"
                        @checked(in_array('provider', $selectedCategories))
                        class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                    >
                    <span class="text-sm text-gray-700 group-hover:text-gray-900 flex items-center gap-2">
                        <x-icon name="users" class="w-4 h-4 text-purple-500" />
                        Providers
                    </span>
                </label>
            </div>
        </div>

        <!-- Price Filter -->
        <div class="mb-6">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Price</h3>
            <div class="space-y-2">
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input
                        type="radio"
                        wire:model.live="priceFilter"
                        value=""
                        class="w-4 h-4 border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                    >
                    <span class="text-sm text-gray-700 group-hover:text-gray-900">All prices</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input
                        type="radio"
                        wire:model.live="priceFilter"
                        value="free"
                        class="w-4 h-4 border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                    >
                    <span class="text-sm text-gray-700 group-hover:text-gray-900">Free</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input
                        type="radio"
                        wire:model.live="priceFilter"
                        value="paid"
                        class="w-4 h-4 border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                    >
                    <span class="text-sm text-gray-700 group-hover:text-gray-900">Paid</span>
                </label>
            </div>
        </div>

        <!-- Rating Filter -->
        <div class="mb-6">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Rating</h3>
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
                        4+ stars
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
                        3+ stars
                    </span>
                </label>
            </div>
        </div>

        <!-- Sort -->
        <div class="mb-6">
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Sort By</h3>
            <select
                wire:model.live="sortBy"
                class="w-full text-sm border-gray-300 rounded-lg focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="popular">Most Popular</option>
                <option value="newest">Newest</option>
                <option value="rating">Highest Rated</option>
            </select>
        </div>

        <!-- Clear All Filters -->
        @if($hasActiveFilters)
            <button
                wire:click="clearFilters"
                class="w-full text-sm text-pulse-orange-600 hover:text-pulse-orange-700 font-medium"
            >
                Clear all filters
            </button>
        @endif
    </div>

    <!-- Main Content Area -->
    <div class="flex-1 p-6">
        @if($isSearching && count($searchResults) > 0)
            <!-- Search Results -->
            <div class="space-y-10">
                @foreach(['surveys' => 'Surveys', 'strategies' => 'Strategies', 'content' => 'Content', 'providers' => 'Providers'] as $key => $label)
                    @if(isset($searchResults[$key]) && $searchResults[$key]['total'] > 0)
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-lg font-semibold text-gray-900">
                                    {{ $label }}
                                    <span class="text-sm font-normal text-gray-500">({{ $searchResults[$key]['total'] }} results)</span>
                                </h2>
                                <a href="{{ route('marketplace.' . $key, ['q' => $search]) }}" class="text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                    View all {{ strtolower($label) }} &rarr;
                                </a>
                            </div>

                            @if($key === 'providers')
                                <!-- Providers as list -->
                                <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-100">
                                    @foreach($searchResults[$key]['items'] as $item)
                                        <a href="{{ route('marketplace.item', $item->uuid) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                                            @if($item->thumbnail_url)
                                                <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="w-12 h-12 rounded-full object-cover flex-shrink-0">
                                            @else
                                                <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                                                    <x-icon name="user" class="w-6 h-6 text-purple-600" />
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-2">
                                                    <h3 class="text-sm font-medium text-gray-900">{{ $item->title }}</h3>
                                                    @if($item->is_verified)
                                                        <x-icon name="check-badge" class="w-4 h-4 text-blue-500" />
                                                    @endif
                                                </div>
                                                @if($item->short_description)
                                                    <p class="text-sm text-gray-600 truncate mt-0.5">{{ $item->short_description }}</p>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-3 flex-shrink-0">
                                                @if($item->ratings_count > 0)
                                                    <div class="flex items-center gap-1 text-sm text-gray-500">
                                                        <x-icon name="star" class="w-4 h-4 text-amber-400" solid />
                                                        {{ number_format($item->ratings_average, 1) }}
                                                    </div>
                                                @endif
                                                <span class="text-sm font-medium {{ $item->isFree() ? 'text-green-600' : 'text-gray-900' }}">
                                                    {{ $item->isFree() ? 'Free' : '$' . number_format($item->price ?? 0, 2) }}
                                                </span>
                                                <x-icon name="chevron-right" class="w-5 h-5 text-gray-400" />
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <!-- Other categories as cards -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                                    @foreach($searchResults[$key]['items'] as $item)
                                        @include('livewire.marketplace.partials.item-card', ['item' => $item])
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach

                <!-- No Results -->
                @php
                    $totalResults = collect($searchResults)->sum('total');
                @endphp
                @if($totalResults === 0)
                    <div class="text-center py-12">
                        <x-icon name="magnifying-glass" class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No results found</h3>
                        <p class="text-gray-500">Try adjusting your search terms or browse by category below.</p>
                        <button wire:click="clearSearch" class="mt-4 text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                            Clear search
                        </button>
                    </div>
                @endif
            </div>
        @else
            <!-- Category Cards (4-column layout) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <!-- Surveys Card -->
                <a href="{{ route('marketplace.surveys') }}" class="group bg-white rounded-2xl border border-gray-200 p-4 hover:shadow-xl hover:border-pulse-orange-300 transition-all">
                    <div class="flex flex-col items-center text-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <x-icon name="clipboard-document-list" class="w-5 h-5 text-blue-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-sm font-semibold text-pulse-orange-600 group-hover:text-pulse-orange-600 transition-colors">Surveys</h2>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($counts['surveys']) }}</p>
                        </div>
                    </div>
                </a>

                <!-- Strategies Card -->
                <a href="{{ route('marketplace.strategies') }}" class="group bg-white rounded-2xl border border-gray-200 p-4 hover:shadow-xl hover:border-pulse-orange-300 transition-all">
                    <div class="flex flex-col items-center text-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <x-icon name="light-bulb" class="w-5 h-5 text-amber-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-sm font-semibold text-pulse-orange-600 group-hover:text-pulse-orange-600 transition-colors">Strategies</h2>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($counts['strategies']) }}</p>
                        </div>
                    </div>
                </a>

                <!-- Content Card -->
                <a href="{{ route('marketplace.content') }}" class="group bg-white rounded-2xl border border-gray-200 p-4 hover:shadow-xl hover:border-pulse-orange-300 transition-all">
                    <div class="flex flex-col items-center text-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <x-icon name="document-text" class="w-5 h-5 text-emerald-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-sm font-semibold text-pulse-orange-600 group-hover:text-pulse-orange-600 transition-colors">Content</h2>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($counts['content']) }}</p>
                        </div>
                    </div>
                </a>

                <!-- Providers Card -->
                <a href="{{ route('marketplace.providers') }}" class="group bg-white rounded-2xl border border-gray-200 p-4 hover:shadow-xl hover:border-pulse-orange-300 transition-all">
                    <div class="flex flex-col items-center text-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <x-icon name="users" class="w-5 h-5 text-purple-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-sm font-semibold text-pulse-orange-600 group-hover:text-pulse-orange-600 transition-colors">Providers</h2>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($counts['providers']) }}</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Featured Items -->
            @if($featuredItems->count() > 0)
                <div class="mb-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <x-icon name="star" class="w-6 h-6 text-amber-400" solid />
                            Featured
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($featuredItems as $item)
                            @include('livewire.marketplace.partials.item-card', ['item' => $item, 'featured' => true])
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Recently Added -->
            @if($recentItems->count() > 0)
                <div>
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900 flex items-center gap-2">
                            <x-icon name="clock" class="w-6 h-6 text-gray-400" />
                            Recently Added
                        </h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($recentItems as $item)
                            @include('livewire.marketplace.partials.item-card', ['item' => $item])
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Empty State -->
            @if($counts['surveys'] === 0 && $counts['strategies'] === 0 && $counts['content'] === 0 && $counts['providers'] === 0)
                <div class="text-center py-16">
                    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-6">
                        <x-icon name="shopping-bag" class="w-10 h-10 text-gray-400" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">The marketplace is empty</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">Be the first to share your educational resources with the Pulse community.</p>
                    <a href="{{ route('marketplace.seller.create') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-pulse-orange-500 text-white font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors">
                        <x-icon name="plus" class="w-5 h-5" />
                        Start Selling
                    </a>
                </div>
            @endif
        @endif
    </div>
</div>
