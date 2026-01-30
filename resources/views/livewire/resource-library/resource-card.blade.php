@php
    $typeColors = [
        'article' => 'blue',
        'video' => 'red',
        'worksheet' => 'green',
        'activity' => 'purple',
        'link' => 'gray',
        'document' => 'yellow',
    ];
    $typeIcons = [
        'article' => 'document-text',
        'video' => 'play-circle',
        'worksheet' => 'clipboard-list',
        'activity' => 'puzzle-piece',
        'link' => 'link',
        'document' => 'document',
    ];
    $icon = $typeIcons[$resource->resource_type] ?? 'document';
    $color = $typeColors[$resource->resource_type] ?? 'gray';
@endphp

@if($viewMode === 'grid')
<div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
    <div class="p-4">
        <div class="flex items-start justify-between gap-2 mb-3">
            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                @include('livewire.resource-library.icons.' . $icon)
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 flex-shrink-0">
                {{ ucfirst($resource->resource_type) }}
            </span>
        </div>

        <h3 class="font-medium text-gray-900 text-sm truncate mb-1">{{ $resource->title }}</h3>

        <div class="flex items-center gap-2 text-xs mb-2">
            @if($resource->category)
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                {{ ucfirst($resource->category) }}
            </span>
            @endif
            @if($resource->estimated_duration_minutes)
            <span class="text-gray-500">{{ $resource->estimated_duration_minutes }} min</span>
            @endif
        </div>

        <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ Str::limit($resource->description, 80) }}</p>

        @if($resource->target_risk_levels && count($resource->target_risk_levels) > 0)
        <div class="flex gap-1 mb-3">
            @foreach($resource->target_risk_levels as $level)
            <span class="w-2 h-2 rounded-full {{ $level === 'high' ? 'bg-red-400' : ($level === 'low' ? 'bg-yellow-400' : 'bg-green-400') }}" title="{{ ucfirst($level) }} risk"></span>
            @endforeach
        </div>
        @endif
    </div>

    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
        <a href="{{ $resource->url ?? '#' }}" target="{{ $resource->url ? '_blank' : '_self' }}" class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
            {{ $resource->url ? 'Open' : 'View' }}
        </a>
    </div>
</div>
@else
<div class="bg-white rounded-lg border border-gray-200 p-3 hover:shadow-sm transition-shadow flex items-center gap-4">
    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
        @include('livewire.resource-library.icons.' . $icon)
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <h3 class="font-medium text-gray-900 text-sm truncate">{{ $resource->title }}</h3>
            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700">
                {{ ucfirst($resource->resource_type) }}
            </span>
            @if($resource->category)
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                {{ ucfirst($resource->category) }}
            </span>
            @endif
        </div>
        <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
            @if($resource->estimated_duration_minutes)
            <span>{{ $resource->estimated_duration_minutes }} min</span>
            @endif
            <span class="truncate">{{ Str::limit($resource->description, 60) }}</span>
        </div>
    </div>

    <a href="{{ $resource->url ?? '#' }}" target="{{ $resource->url ? '_blank' : '_self' }}" class="ml-2 px-3 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
        {{ $resource->url ? 'Open' : 'View' }}
    </a>
</div>
@endif
