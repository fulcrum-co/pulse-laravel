@php
    $featured = $featured ?? false;
    $categoryColors = [
        'survey' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600', 'badge' => 'bg-blue-50 text-blue-700'],
        'strategy' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600', 'badge' => 'bg-amber-50 text-amber-700'],
        'content' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600', 'badge' => 'bg-emerald-50 text-emerald-700'],
        'provider' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600', 'badge' => 'bg-purple-50 text-purple-700'],
    ];
    $categoryIcons = [
        'survey' => 'clipboard-document-list',
        'strategy' => 'light-bulb',
        'content' => 'document-text',
        'provider' => 'users',
    ];
    $colors = $categoryColors[$item->category] ?? $categoryColors['content'];
    $icon = $categoryIcons[$item->category] ?? 'document';
@endphp

<a href="{{ route('marketplace.item', $item->uuid) }}" class="group bg-white rounded-xl border {{ $featured ? 'border-amber-200 ring-1 ring-amber-100' : 'border-gray-200' }} overflow-hidden hover:shadow-lg hover:border-pulse-orange-300 transition-all">
    <!-- Thumbnail -->
    <div class="aspect-[16/10] bg-gray-100 relative overflow-hidden">
        @if($item->thumbnail_url)
            <img src="{{ $item->thumbnail_url }}" alt="{{ $item->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center {{ $colors['bg'] }}">
                <x-icon name="{{ $icon }}" class="w-12 h-12 {{ $colors['text'] }} opacity-50" />
            </div>
        @endif

        <!-- Featured badge -->
        @if($featured)
            <div class="absolute top-2 left-2">
                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full bg-amber-500 text-white text-xs font-medium">
                    <x-icon name="star" class="w-3 h-3" solid />
                    Featured
                </span>
            </div>
        @endif

        <!-- Price badge -->
        <div class="absolute top-2 right-2">
            <span class="inline-flex items-center px-2 py-1 rounded-full {{ $item->isFree() ? 'bg-green-500 text-white' : 'bg-white/90 text-gray-900' }} text-xs font-semibold shadow-sm">
                @if($item->isFree())
                    Free
                @elseif($item->pricing_type === 'recurring')
                    ${{ number_format($item->price ?? 0, 2) }}/mo
                @else
                    ${{ number_format($item->price ?? 0, 2) }}
                @endif
            </span>
        </div>

        <!-- Category badge -->
        <div class="absolute bottom-2 left-2">
            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full {{ $colors['badge'] }} text-xs font-medium">
                <x-icon name="{{ $icon }}" class="w-3 h-3" />
                {{ ucfirst($item->category) }}
            </span>
        </div>
    </div>

    <!-- Content -->
    <div class="p-4">
        <!-- Title -->
        <h3 class="font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors line-clamp-2 mb-1">
            {{ $item->title }}
        </h3>

        <!-- Seller -->
        <div class="flex items-center gap-1.5 text-sm text-gray-500 mb-2">
            <span>by {{ $item->seller->display_name }}</span>
            @if($item->seller->is_verified)
                <x-icon name="check-badge" class="w-4 h-4 text-blue-500" />
            @endif
        </div>

        <!-- Rating and stats -->
        <div class="flex items-center justify-between text-sm">
            <div class="flex items-center gap-3">
                @if($item->ratings_count > 0)
                    <div class="flex items-center gap-1">
                        <x-icon name="star" class="w-4 h-4 text-amber-400" solid />
                        <span class="font-medium text-gray-900">{{ number_format($item->ratings_average, 1) }}</span>
                        <span class="text-gray-400">({{ $item->ratings_count }})</span>
                    </div>
                @else
                    <span class="text-gray-400">No reviews yet</span>
                @endif
            </div>

            @if($item->purchase_count > 0 || $item->download_count > 0)
                <span class="text-gray-400">
                    {{ number_format($item->purchase_count + $item->download_count) }} {{ ($item->purchase_count + $item->download_count) === 1 ? 'user' : 'users' }}
                </span>
            @endif
        </div>

        <!-- Tags (optional, show first 2) -->
        @if($item->tags && count($item->tags) > 0)
            <div class="flex flex-wrap gap-1 mt-3">
                @foreach(array_slice($item->tags, 0, 2) as $tag)
                    <span class="inline-flex items-center px-2 py-0.5 rounded bg-gray-100 text-gray-600 text-xs">
                        {{ $tag }}
                    </span>
                @endforeach
                @if(count($item->tags) > 2)
                    <span class="text-xs text-gray-400">+{{ count($item->tags) - 2 }}</span>
                @endif
            </div>
        @endif
    </div>
</a>
