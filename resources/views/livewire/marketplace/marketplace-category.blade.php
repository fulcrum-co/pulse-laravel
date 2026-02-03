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
                        <span class="text-gray-900">{{ $categoryLabel }}</span>
                    </nav>
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $categoryLabel }}</h1>
                </div>
            </div>
            @if($this->hasSellerProfile ?? false)
                <a
                    href="{{ route('marketplace.seller.items.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                >
                    <x-icon name="plus" class="w-4 h-4" />
                    @term('list_action') {{ Str::singular($categoryLabel) }}
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
                        placeholder="{{ $terminology->get('search_label') }} {{ strtolower($categoryLabel) }}..."
                        class="block w-full pl-9 pr-3 py-2 border border-gray-300 rounded-lg bg-white text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    >
                </div>
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
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">@term('free_label')</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer group">
                        <input
                            type="radio"
                            wire:model.live="priceFilter"
                            value="paid"
                            class="w-4 h-4 border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                        >
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">@term('paid_label')</span>
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

            <!-- Level Level Filter -->
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">@term('level_label')</h3>
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

            <!-- Type Filter (category-specific) -->
            @if(isset($filterTypes) && count($filterTypes) > 0)
                <div class="mb-6">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">{{ $filterTypeLabel ?? $terminology->get('type_label') }}</h3>
                    <div class="space-y-2">
                        @foreach($filterTypes as $value => $label)
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
            @endif

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
                    @term('showing_label') <span class="font-medium">{{ $items->count() }}</span> @term('of_label') <span class="font-medium">{{ $items->total() }}</span> {{ strtolower($categoryLabel) }}
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
                        <button
                            wire:click="$set('viewMode', 'table')"
                            class="p-2 border-l border-gray-300 {{ $viewMode === 'table' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }}"
                            title="{{ $terminology->get('table_view_label') }}"
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
                            <option value="popular">@term('most_popular_label')</option>
                            <option value="newest">@term('newest_label')</option>
                            <option value="rating">@term('highest_rated_label')</option>
                            <option value="price_low">@term('price_low_high_label')</option>
                            <option value="price_high">@term('price_high_low_label')</option>
                        </select>
                    </div>
                </div>
            </div>

            @if($items->count() > 0)
                @if($viewMode === 'grid')
                    <!-- Grid View -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($items as $item)
                            @include('livewire.marketplace.partials.item-card', ['item' => $item])
                        @endforeach
                    </div>
                @elseif($viewMode === 'list')
                    <!-- List View -->
                    <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                        @foreach($items as $item)
                            <a href="{{ route('marketplace.item', $item->uuid) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors group">
                                @if($item->thumbnail_url)
                                    <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="w-16 h-16 rounded-lg object-cover flex-shrink-0">
                                @else
                                    <div class="w-16 h-16 rounded-lg bg-{{ $categoryColor }}-100 flex items-center justify-center flex-shrink-0">
                                        <x-icon name="{{ $categoryIcon }}" class="w-8 h-8 text-{{ $categoryColor }}-600" />
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">{{ $item->title }}</h3>
                                        @if($item->is_verified)
                                            <x-icon name="check-badge" class="w-4 h-4 text-blue-500" />
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-500 mt-0.5">@term('by_label') {{ $item->seller->display_name }}</p>
                                    @if($item->short_description)
                                        <p class="text-sm text-gray-600 truncate mt-1">{{ $item->short_description }}</p>
                                    @endif
                                </div>
                                <div class="flex items-center gap-4 flex-shrink-0">
                                    @if($item->ratings_count > 0)
                                        <div class="flex items-center gap-1">
                                            <x-icon name="star" class="w-4 h-4 text-amber-400" solid />
                                            <span class="text-sm font-medium text-gray-900">{{ number_format($item->ratings_average, 1) }}</span>
                                            <span class="text-sm text-gray-400">({{ $item->ratings_count }})</span>
                                        </div>
                                    @endif
                                    <span class="text-sm font-semibold {{ $item->isFree() ? 'text-green-600' : 'text-gray-900' }}">
                                        @if($item->isFree())
                                            @term('free_label')
                                        @elseif($item->pricing_type === 'recurring')
                                            ${{ number_format($item->price ?? 0, 2) }}/@term('month_short_label')
                                        @else
                                            ${{ number_format($item->price ?? 0, 2) }}
                                        @endif
                                    </span>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('seller_label')</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('rating_label')</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('price_label')</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('sales_label')</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($items as $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                @if($item->thumbnail_url)
                                                    <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                                                @else
                                                    <div class="w-10 h-10 rounded-lg bg-{{ $categoryColor }}-100 flex items-center justify-center flex-shrink-0">
                                                        <x-icon name="{{ $categoryIcon }}" class="w-5 h-5 text-{{ $categoryColor }}-600" />
                                                    </div>
                                                @endif
                                                <div class="flex items-center gap-2">
                                                    <span class="text-sm font-medium text-gray-900">{{ Str::limit($item->title, 40) }}</span>
                                                    @if($item->is_verified)
                                                        <x-icon name="check-badge" class="w-4 h-4 text-blue-500" />
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $item->seller->display_name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($item->ratings_count > 0)
                                                <div class="flex items-center gap-1">
                                                    <x-icon name="star" class="w-4 h-4 text-amber-400" solid />
                                                    <span class="text-sm font-medium text-gray-900">{{ number_format($item->ratings_average, 1) }}</span>
                                                    <span class="text-xs text-gray-400">({{ $item->ratings_count }})</span>
                                                </div>
                                            @else
                                                <span class="text-sm text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-semibold {{ $item->isFree() ? 'text-green-600' : 'text-gray-900' }}">
                                                @if($item->isFree())
                                                    @term('free_label')
                                                @elseif($item->pricing_type === 'recurring')
                                                    ${{ number_format($item->price ?? 0, 2) }}/@term('month_short_label')
                                                @else
                                                    ${{ number_format($item->price ?? 0, 2) }}
                                                @endif
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ number_format($item->purchase_count + $item->download_count) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right">
                                            <a href="{{ route('marketplace.item', $item->uuid) }}" class="text-pulse-orange-600 hover:text-pulse-orange-700 text-sm font-medium">
                                                @term('view_action')
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
                    {{ $items->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-16">
                    <div class="w-16 h-16 rounded-full bg-{{ $categoryColor }}-100 flex items-center justify-center mx-auto mb-4">
                        <x-icon name="{{ $categoryIcon }}" class="w-8 h-8 text-{{ $categoryColor }}-600" />
                    </div>
                    @if($this->hasActiveFilters)
                        <h3 class="text-lg font-medium text-gray-900 mb-1">@term('no_items_match_filters_label') {{ strtolower($categoryLabel) }}</h3>
                        <p class="text-gray-500 mb-4">@term('adjust_filter_criteria_label')</p>
                        <button
                            wire:click="clearFilters"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            @term('clear_filters_action_label')
                        </button>
                    @else
                        <h3 class="text-lg font-medium text-gray-900 mb-1">@term('no_label') {{ strtolower($categoryLabel) }} @term('available_label') @term('yet_label')</h3>
                        <p class="text-gray-500 mb-4">@term('be_first_share_label') {{ strtolower(Str::singular($categoryLabel)) }} @term('with_community_label')</p>
                        <a
                            href="{{ route('marketplace.seller.create') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                        >
                            <x-icon name="plus" class="w-4 h-4" />
                            @term('become_seller_label')
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
