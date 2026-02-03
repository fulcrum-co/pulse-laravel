@php
    $terminology = app(\App\Services\TerminologyService::class);
@endphp

<div class="min-h-screen bg-gray-50">
    <!-- Header Banner -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-4">
            <x-breadcrumbs :items="[
                ['label' => $terminology->get('marketplace_label'), 'url' => route('marketplace.index')],
                ['label' => $this->categoryLabel, 'url' => route($this->categoryRoute)],
                ['label' => $item->title],
            ]" />
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Preview Image / Carousel -->
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="aspect-[16/9] bg-gray-100">
                        @if($item->thumbnail_url)
                            <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-{{ $this->categoryColor }}-100">
                                <x-icon name="{{ $this->categoryIcon }}" class="w-24 h-24 text-{{ $this->categoryColor }}-300" />
                            </div>
                        @endif
                    </div>
                    @if($item->preview_images && count($item->preview_images) > 0)
                        <div class="p-4 border-t border-gray-100">
                            <div class="flex gap-2 overflow-x-auto">
                                @foreach($item->preview_images as $image)
                                    <img src="{{ $image }}" alt="{{ $terminology->get('preview_label') }}" class="w-20 h-20 rounded-lg object-cover flex-shrink-0 cursor-pointer hover:ring-2 hover:ring-pulse-orange-500">
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Title & Meta -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-{{ $this->categoryColor }}-100 text-{{ $this->categoryColor }}-700 text-xs font-medium">
                                    <x-icon name="{{ $this->categoryIcon }}" class="w-3 h-3" />
                                    {{ $terminology->get('category_' . $item->category . '_label') }}
                                </span>
                                @if($item->is_verified)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-blue-100 text-blue-700 text-xs font-medium">
                                        <x-icon name="check-badge" class="w-3 h-3" />
                                        @term('verified_label')
                                    </span>
                                @endif
                                @if($item->is_featured)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-amber-100 text-amber-700 text-xs font-medium">
                                        <x-icon name="star" class="w-3 h-3" solid />
                                        @term('featured_label')
                                    </span>
                                @endif
                            </div>
                            <h1 class="text-2xl font-bold text-gray-900">{{ $item->title }}</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-4 pt-4 border-t border-gray-100">
                        @if($item->ratings_count > 0)
                            <div class="flex items-center gap-1">
                                <x-icon name="star" class="w-5 h-5 text-amber-400" solid />
                                <span class="font-semibold text-gray-900">{{ number_format($item->ratings_average, 1) }}</span>
                                <span class="text-gray-500">({{ $item->ratings_count }} {{ Str::plural($terminology->get('review_label'), $item->ratings_count) }})</span>
                            </div>
                        @else
                            <span class="text-gray-500">@term('no_reviews_yet_label')</span>
                        @endif
                        <span class="text-gray-300">|</span>
                        <span class="text-gray-500">{{ number_format($item->purchase_count + $item->download_count) }} {{ ($item->purchase_count + $item->download_count) === 1 ? $terminology->get('user_label') : $terminology->get('users_label') }}</span>
                    </div>

                    <!-- Tags -->
                    @if($item->tags && count($item->tags) > 0)
                        <div class="flex flex-wrap gap-2 mt-4">
                            @foreach($item->tags as $tag)
                                <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-600 text-xs">
                                    {{ $tag }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Tabs -->
                <div class="bg-white rounded-xl border border-gray-200">
                    <div class="border-b border-gray-200">
                        <nav class="flex gap-6 px-6">
                            <button
                                wire:click="$set('activeTab', 'description')"
                                class="py-4 text-sm font-medium border-b-2 -mb-px transition-colors {{ $activeTab === 'description' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                            >
                                @term('description_label')
                            </button>
                            @if($item->preview_content)
                                <button
                                    wire:click="$set('activeTab', 'preview')"
                                    class="py-4 text-sm font-medium border-b-2 -mb-px transition-colors {{ $activeTab === 'preview' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                                >
                                    @term('preview_label')
                                </button>
                            @endif
                            <button
                                wire:click="$set('activeTab', 'reviews')"
                                class="py-4 text-sm font-medium border-b-2 -mb-px transition-colors {{ $activeTab === 'reviews' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
                            >
                                {{ $terminology->get('reviews_label') }} ({{ $item->ratings_count }})
                            </button>
                        </nav>
                    </div>

                    <div class="p-6">
                        @if($activeTab === 'description')
                            <div class="prose max-w-none">
                                {!! nl2br(e($item->description)) !!}
                            </div>

                            <!-- Target Info -->
                            <div class="mt-6 pt-6 border-t border-gray-100 grid grid-cols-2 md:grid-cols-3 gap-4">
                                @if($item->target_levels && count($item->target_levels) > 0)
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">@term('levels_label')</p>
                                        <p class="text-sm text-gray-900">{{ implode(', ', $item->target_levels) }}</p>
                                    </div>
                                @endif
                                @if($item->target_subjects && count($item->target_subjects) > 0)
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">@term('subjects_label')</p>
                                        <p class="text-sm text-gray-900">{{ implode(', ', array_map('ucfirst', $item->target_subjects)) }}</p>
                                    </div>
                                @endif
                                @if($item->target_needs && count($item->target_needs) > 0)
                                    <div>
                                        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">@term('target_needs_label')</p>
                                        <p class="text-sm text-gray-900">{{ implode(', ', array_map('ucfirst', $item->target_needs)) }}</p>
                                    </div>
                                @endif
                            </div>
                        @elseif($activeTab === 'preview')
                            @if($item->preview_content)
                                <div class="prose max-w-none">
                                    @if(is_array($item->preview_content))
                                        @foreach($item->preview_content as $content)
                                            <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                                                {!! nl2br(e(is_array($content) ? json_encode($content) : $content)) !!}
                                            </div>
                                        @endforeach
                                    @else
                                        {!! nl2br(e($item->preview_content)) !!}
                                    @endif
                                </div>
                            @else
                                <p class="text-gray-500">@term('no_preview_available_label')</p>
                            @endif
                        @else
                            <!-- Reviews -->
                            @if($reviews->count() > 0)
                                <div class="space-y-6">
                                    @foreach($reviews as $review)
                                        <div class="pb-6 border-b border-gray-100 last:border-0 last:pb-0">
                                            <div class="flex items-start justify-between gap-4">
                                                <div class="flex items-center gap-3">
                                                    @if($review->user->avatar_url)
                                                        <img src="{{ $review->user->avatar_url }}" alt="" class="w-10 h-10 rounded-full object-cover">
                                                    @else
                                                        <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                                            <span class="text-sm font-medium text-gray-500">{{ substr($review->user->first_name ?? 'U', 0, 1) }}</span>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="flex items-center gap-2">
                                                            <span class="font-medium text-gray-900">{{ $review->user->first_name }} {{ substr($review->user->last_name ?? '', 0, 1) }}.</span>
                                                            @if($review->is_verified_purchase)
                                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-green-100 text-green-700 text-xs">
                                                                    <x-icon name="check" class="w-3 h-3" />
                                                                    @term('verified_purchase_label')
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="flex items-center gap-2 mt-0.5">
                                                            <div class="flex items-center">
                                                                @for($i = 1; $i <= 5; $i++)
                                                                    <x-icon name="star" class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}" solid />
                                                                @endfor
                                                            </div>
                                                            <span class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @if($review->review_text)
                                                <p class="mt-3 text-gray-600">{{ $review->review_text }}</p>
                                            @endif
                                            @if($review->seller_response)
                                                <div class="mt-4 ml-6 p-4 bg-gray-50 rounded-lg">
                                                    <p class="text-xs font-semibold text-gray-500 mb-1">@term('seller_response_label')</p>
                                                    <p class="text-sm text-gray-600">{{ $review->seller_response }}</p>
                                                </div>
                                            @endif
                                            <div class="mt-3 flex items-center gap-4">
                                                <button class="text-xs text-gray-500 hover:text-gray-700 flex items-center gap-1">
                                                    <x-icon name="hand-thumb-up" class="w-4 h-4" />
                                                    {{ $terminology->get('helpful_label') }} ({{ $review->helpful_count }})
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="mt-6">
                                    {{ $reviews->links() }}
                                </div>
                            @else
                                <div class="text-center py-8">
                                    <x-icon name="chat-bubble-left-right" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                                    <p class="text-gray-500">@term('no_reviews_prompt_label')</p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Purchase Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-6 sticky top-6">
                    <div class="text-center mb-6">
                        @if($item->isFree())
                            <p class="text-3xl font-bold text-green-600">@term('free_label')</p>
                        @elseif($item->primaryPricing)
                            @if($item->primaryPricing->hasDiscount())
                                <p class="text-sm text-gray-500 line-through">${{ number_format($item->primaryPricing->original_price, 2) }}</p>
                            @endif
                            <p class="text-3xl font-bold text-gray-900">
                                ${{ number_format($item->primaryPricing->price ?? $item->primaryPricing->recurring_price ?? 0, 2) }}
                                @if($item->pricing_type === 'recurring')
                                    <span class="text-lg font-normal text-gray-500">/{{ $terminology->get('billing_interval_' . $item->primaryPricing->billing_interval . '_label') }}</span>
                                @endif
                            </p>
                            @if($item->primaryPricing->license_type !== 'single')
                                <p class="text-sm text-gray-500 mt-1">{{ $terminology->get('license_type_' . $item->primaryPricing->license_type . '_label') }} {{ $terminology->get('license_label') }}</p>
                            @endif
                        @else
                            <p class="text-xl font-semibold text-gray-900">@term('contact_for_pricing_label')</p>
                        @endif
                    </div>

                    @if($hasAccess)
                        <div class="space-y-3">
                            <div class="flex items-center justify-center gap-2 text-green-600 mb-4">
                                <x-icon name="check-circle" class="w-5 h-5" />
                                <span class="font-medium">@term('you_own_this_label')</span>
                            </div>
                            <a href="#" class="block w-full py-3 px-4 bg-pulse-orange-500 text-white text-center font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors">
                                @term('access_content_label')
                            </a>
                        </div>
                    @else
                        <div class="space-y-3">
                            <button class="w-full py-3 px-4 bg-pulse-orange-500 text-white font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors">
                                {{ $item->isFree() ? $terminology->get('get_for_free_label') : $terminology->get('buy_now_label') }}
                            </button>
                            @if(!$item->isFree())
                                <button class="w-full py-3 px-4 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                    @term('add_to_library_label')
                                </button>
                            @endif
                        </div>
                    @endif

                    <!-- License Info -->
                    @if($item->primaryPricing && $item->primaryPricing->license_terms)
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">@term('includes_label')</p>
                            <ul class="space-y-2 text-sm text-gray-600">
                                @foreach($item->primaryPricing->license_terms as $term)
                                    <li class="flex items-center gap-2">
                                        <x-icon name="check" class="w-4 h-4 text-green-500" />
                                        {{ $term }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>

                <!-- Seller Card -->
                <div class="bg-white rounded-xl border border-gray-200 p-6">
                    <div class="flex items-center gap-4 mb-4">
                        @if($item->seller->avatar_url)
                            <img src="{{ $item->seller->avatar_url }}" alt="{{ $item->seller->display_name }}" class="w-14 h-14 rounded-full object-cover">
                        @else
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-pulse-orange-400 to-pulse-orange-600 flex items-center justify-center">
                                <span class="text-lg font-medium text-white">{{ substr($item->seller->display_name, 0, 2) }}</span>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-semibold text-gray-900 truncate">{{ $item->seller->display_name }}</h3>
                                @if($item->seller->is_verified)
                                    <x-icon name="check-badge" class="w-5 h-5 text-blue-500 flex-shrink-0" />
                                @endif
                            </div>
                        @if($item->seller->ratings_count > 0)
                            <div class="flex items-center gap-1 mt-0.5">
                                <x-icon name="star" class="w-4 h-4 text-amber-400" solid />
                                <span class="text-sm font-medium text-gray-900">{{ number_format($item->seller->ratings_average, 1) }}</span>
                                <span class="text-sm text-gray-500">({{ $item->seller->ratings_count }} {{ Str::plural($terminology->get('review_label'), $item->seller->ratings_count) }})</span>
                            </div>
                    @endif
                        </div>
                    </div>
                    @if($item->seller->bio)
                        <p class="text-sm text-gray-600 mb-4">{{ Str::limit($item->seller->bio, 150) }}</p>
                    @endif
                    <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                        <span>{{ $item->seller->total_items }} {{ Str::plural($terminology->get('item_label'), $item->seller->total_items) }}</span>
                        <span>{{ number_format($item->seller->total_sales) }} @term('sales_label')</span>
                    </div>
                    <div class="space-y-2">
                        <!-- Primary CTA: Message Seller -->
                        <a href="{{ route('messages.index') }}" class="flex items-center justify-center gap-2 w-full py-2.5 px-4 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            @term('message_seller_label')
                        </a>
                        <a href="{{ route('marketplace.sellers.show', $item->seller->slug) }}" class="block w-full py-2 px-4 bg-gray-100 text-gray-700 text-center text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                            @term('view_profile_label')
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Items -->
        @if($relatedItems->count() > 0)
            <div class="mt-12">
                <h2 class="text-xl font-bold text-gray-900 mb-6">{{ $terminology->get('more_label') }} {{ $this->categoryLabel }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($relatedItems as $related)
                        @include('livewire.marketplace.partials.item-card', ['item' => $related])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</div>
