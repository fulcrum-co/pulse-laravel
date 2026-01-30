@php
    $typeIcons = [
        'article' => 'document-text',
        'video' => 'play-circle',
        'worksheet' => 'clipboard-list',
        'activity' => 'puzzle-piece',
        'link' => 'link',
        'document' => 'document',
    ];
    $icon = $typeIcons[$resource->resource_type] ?? 'document';
@endphp

@if($viewMode === 'grid')
<a href="{{ $resource->url ?? '#' }}" target="{{ $resource->url ? '_blank' : '_self' }}" class="block bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg hover:border-gray-300 transition-all group">
    <!-- Thumbnail or Icon -->
    <div class="aspect-video bg-gradient-to-br from-gray-100 to-gray-50 flex items-center justify-center relative">
        @if($resource->thumbnail_url)
        <img src="{{ $resource->thumbnail_url }}" alt="{{ $resource->title }}" class="w-full h-full object-cover">
        @else
        <div class="w-16 h-16 rounded-2xl bg-white shadow-sm flex items-center justify-center">
            @include('livewire.resource-library.icons.' . $icon)
        </div>
        @endif

        <!-- Type Badge -->
        <span class="absolute top-3 right-3 px-2 py-1 text-xs font-medium rounded-full bg-white/90 text-gray-700 backdrop-blur-sm">
            {{ ucfirst($resource->resource_type) }}
        </span>
    </div>

    <div class="p-4">
        <!-- Title -->
        <h3 class="font-semibold text-gray-900 mb-1 line-clamp-1 group-hover:text-pulse-orange-600 transition-colors">
            {{ $resource->title }}
        </h3>

        <!-- Category -->
        @if($resource->category)
        <p class="text-sm text-gray-500 mb-2">{{ ucfirst($resource->category) }}</p>
        @endif

        <!-- Description -->
        <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ Str::limit($resource->description, 100) }}</p>

        <!-- Footer -->
        <div class="flex items-center justify-between text-xs text-gray-500">
            @if($resource->estimated_duration_minutes)
            <div class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ $resource->estimated_duration_minutes }} min
            </div>
            @else
            <div></div>
            @endif

            @if($resource->target_risk_levels && count($resource->target_risk_levels) > 0)
            <div class="flex gap-1">
                @foreach($resource->target_risk_levels as $level)
                <span class="w-2 h-2 rounded-full {{ $level === 'high' ? 'bg-red-400' : ($level === 'low' ? 'bg-yellow-400' : 'bg-green-400') }}"></span>
                @endforeach
            </div>
            @endif
        </div>
    </div>
</a>
@else
<a href="{{ $resource->url ?? '#' }}" target="{{ $resource->url ? '_blank' : '_self' }}" class="block bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-gray-300 transition-all flex items-center gap-4">
    <!-- Icon -->
    <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
        @include('livewire.resource-library.icons.' . $icon)
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
            <h3 class="font-medium text-gray-900 truncate">{{ $resource->title }}</h3>
            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-blue-100 text-blue-700">
                {{ ucfirst($resource->resource_type) }}
            </span>
        </div>
        <p class="text-sm text-gray-600 truncate">{{ $resource->description }}</p>
    </div>

    <!-- Meta -->
    <div class="text-sm text-gray-500 hidden sm:flex items-center gap-4">
        @if($resource->category)
        <span class="px-2 py-0.5 bg-gray-100 rounded text-xs">{{ ucfirst($resource->category) }}</span>
        @endif
        @if($resource->estimated_duration_minutes)
        <span>{{ $resource->estimated_duration_minutes }} min</span>
        @endif
    </div>

    <!-- Arrow -->
    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
    </svg>
</a>
@endif
