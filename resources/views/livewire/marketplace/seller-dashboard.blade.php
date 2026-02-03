@php
    $terminology = app(\App\Services\TerminologyService::class);
@endphp

<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    @if($seller->avatar_url)
                        <img src="{{ $seller->avatar_url }}" alt="{{ $seller->display_name }}" class="w-14 h-14 rounded-full object-cover">
                    @else
                        <div class="w-14 h-14 rounded-full bg-pulse-orange-100 flex items-center justify-center">
                            <x-icon name="user" class="w-7 h-7 text-pulse-orange-600" />
                        </div>
                    @endif
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">{{ $seller->display_name }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            @if($seller->is_verified)
                                <span class="inline-flex items-center gap-1 text-sm text-blue-600">
                                    <x-icon name="check-badge" class="w-4 h-4" />
                                    @term('verified_label')
                                </span>
                            @endif
                            <span class="text-sm text-gray-500">{{ ucfirst(str_replace('_', ' ', $seller->seller_type)) }}</span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('marketplace.sellers.show', $seller->slug) }}" class="inline-flex items-center gap-2 px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50" target="_blank">
                        <x-icon name="eye" class="w-4 h-4" />
                        @term('view_public_profile_label')
                    </a>
                    <a href="{{ route('marketplace.seller.items.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors">
                        <x-icon name="plus" class="w-5 h-5" />
                        @term('list_new_item_label')
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg flex items-center gap-3">
                <x-icon name="check-circle" class="w-5 h-5 text-green-600" />
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="text-sm text-gray-500 mb-1">@term('total_items_label')</div>
                <div class="text-3xl font-bold text-gray-900">{{ $stats['total_items'] }}</div>
                <div class="text-xs text-gray-400 mt-1">{{ $stats['published_items'] }} @term('published_label')</div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="text-sm text-gray-500 mb-1">@term('total_sales_label')</div>
                <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_sales']) }}</div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="text-sm text-gray-500 mb-1">@term('total_revenue_label')</div>
                <div class="text-3xl font-bold text-gray-900">${{ number_format($stats['total_revenue'], 2) }}</div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="text-sm text-gray-500 mb-1">@term('rating_label')</div>
                <div class="flex items-center gap-2">
                    @if($stats['ratings_average'])
                        <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['ratings_average'], 1) }}</div>
                        <x-icon name="star" class="w-6 h-6 text-amber-400" solid />
                    @else
                        <div class="text-xl text-gray-400">@term('no_ratings_yet_label')</div>
                    @endif
                </div>
                @if($stats['ratings_count'] > 0)
                    <div class="text-xs text-gray-400 mt-1">{{ $stats['ratings_count'] }} {{ Str::plural($terminology->get('review_label'), $stats['ratings_count']) }}</div>
                @endif
            </div>
        </div>

        <!-- Quick Actions -->
        @if($stats['pending_items'] > 0)
            <div class="mb-8 p-4 bg-amber-50 border border-amber-200 rounded-lg flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <x-icon name="clock" class="w-5 h-5 text-amber-600" />
                    <span class="text-amber-800">{{ $terminology->get('you_have_label') }} {{ $stats['pending_items'] }} {{ Str::plural($terminology->get('item_label'), $stats['pending_items']) }} @term('pending_review_label').</span>
                </div>
                <a href="{{ route('marketplace.seller.items', ['status' => 'pending_review']) }}" class="text-sm font-medium text-amber-700 hover:text-amber-900">
                    {{ $terminology->get('view_items_label') }} &rarr;
                </a>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Recent Items -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">@term('your_items_label')</h2>
                    <a href="{{ route('marketplace.seller.items') }}" class="text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                        {{ $terminology->get('view_all_label') }} &rarr;
                    </a>
                </div>

                @if($recentItems->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($recentItems as $item)
                            <a href="{{ route('marketplace.seller.items.edit', $item) }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                                @if($item->thumbnail_url)
                                    <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="w-12 h-12 rounded-lg object-cover">
                                @else
                                    <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center">
                                        <x-icon name="document" class="w-6 h-6 text-gray-400" />
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium text-gray-900 truncate">{{ $item->title }}</h3>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-xs px-2 py-0.5 rounded-full
                                            @switch($item->status)
                                                @case('approved') bg-green-100 text-green-700 @break
                                                @case('pending_review') bg-amber-100 text-amber-700 @break
                                                @case('rejected') bg-red-100 text-red-700 @break
                                                @default bg-gray-100 text-gray-700
                                            @endswitch
                                        ">
                                            {{ ucfirst(str_replace('_', ' ', $item->status)) }}
                                        </span>
                                        <span class="text-sm text-gray-500">
                                            {{ $item->isFree() ? $terminology->get('free_label') : '$' . number_format($item->price ?? 0, 2) }}
                                        </span>
                                    </div>
                                </div>
                                <x-icon name="chevron-right" class="w-5 h-5 text-gray-400" />
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <x-icon name="shopping-bag" class="w-6 h-6 text-gray-400" />
                        </div>
                        <h3 class="font-medium text-gray-900 mb-1">@term('no_items_yet_label')</h3>
                        <p class="text-sm text-gray-500 mb-4">@term('start_selling_first_item_label')</p>
                        <a href="{{ route('marketplace.seller.items.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white font-medium rounded-lg hover:bg-pulse-orange-600 text-sm">
                            <x-icon name="plus" class="w-4 h-4" />
                            @term('list_new_item_label')
                        </a>
                    </div>
                @endif
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">@term('recent_sales_label')</h2>
                    <a href="{{ route('marketplace.seller.analytics') }}" class="text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                        {{ $terminology->get('view_analytics_label') }} &rarr;
                    </a>
                </div>

                @if($recentTransactions->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($recentTransactions as $transaction)
                            <div class="flex items-center justify-between p-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                        <x-icon name="currency-dollar" class="w-5 h-5 text-green-600" />
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $transaction->item->title }}</div>
                                        <div class="text-sm text-gray-500">{{ $transaction->buyer->name }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-medium text-green-600">+${{ number_format($transaction->seller_payout, 2) }}</div>
                                    <div class="text-xs text-gray-400">{{ $transaction->created_at->diffForHumans() }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center">
                        <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-3">
                            <x-icon name="banknotes" class="w-6 h-6 text-gray-400" />
                        </div>
                        <h3 class="font-medium text-gray-900 mb-1">@term('no_sales_yet_label')</h3>
                        <p class="text-sm text-gray-500">@term('sales_appear_after_first_sale_label')</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Seller Navigation -->
        <div class="mt-8 grid grid-cols-2 sm:grid-cols-4 gap-4">
            <a href="{{ route('marketplace.seller.items') }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-pulse-orange-300 hover:shadow-md transition-all group">
                <x-icon name="squares-2x2" class="w-8 h-8 text-gray-400 group-hover:text-pulse-orange-500 mb-3" />
                <h3 class="font-semibold text-gray-900">@term('my_items_label')</h3>
                <p class="text-sm text-gray-500 mt-1">@term('manage_listings_label')</p>
            </a>

            <a href="{{ route('marketplace.seller.analytics') }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-pulse-orange-300 hover:shadow-md transition-all group">
                <x-icon name="chart-bar" class="w-8 h-8 text-gray-400 group-hover:text-pulse-orange-500 mb-3" />
                <h3 class="font-semibold text-gray-900">@term('analytics_label')</h3>
                <p class="text-sm text-gray-500 mt-1">@term('view_performance_label')</p>
            </a>

            <a href="{{ route('marketplace.seller.reviews') }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-pulse-orange-300 hover:shadow-md transition-all group">
                <x-icon name="star" class="w-8 h-8 text-gray-400 group-hover:text-pulse-orange-500 mb-3" />
                <h3 class="font-semibold text-gray-900">@term('reviews_label')</h3>
                <p class="text-sm text-gray-500 mt-1">@term('read_respond_label')</p>
            </a>

            <a href="{{ route('marketplace.seller.payouts') }}" class="bg-white rounded-xl border border-gray-200 p-5 hover:border-pulse-orange-300 hover:shadow-md transition-all group">
                <x-icon name="banknotes" class="w-8 h-8 text-gray-400 group-hover:text-pulse-orange-500 mb-3" />
                <h3 class="font-semibold text-gray-900">@term('payouts_label')</h3>
                <p class="text-sm text-gray-500 mt-1">@term('manage_earnings_label')</p>
            </a>
        </div>
    </div>
</div>
