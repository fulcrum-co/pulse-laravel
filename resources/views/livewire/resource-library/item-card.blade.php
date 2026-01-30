@php
    $typeColors = [
        'resource' => 'blue',
        'provider' => 'purple',
        'program' => 'green',
        'course' => 'orange',
    ];
    $typeLabels = [
        'resource' => 'Content',
        'provider' => 'Provider',
        'program' => 'Program',
        'course' => 'Course',
    ];
    $itemUrl = match($item['type']) {
        'provider' => route('resources.providers.show', $item['id']),
        'program' => route('resources.programs.show', $item['id']),
        'course' => route('resources.courses.show', $item['id']),
        'resource' => $item['model']->url ?? '#',
    };
@endphp

@if($viewMode === 'grid')
<div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
    <div class="p-4">
        <!-- Type Badge & Icon -->
        <div class="flex items-start justify-between gap-2 mb-3">
            <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                @include('livewire.resource-library.icons.' . $item['icon'])
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $typeColors[$item['type']] }}-100 text-{{ $typeColors[$item['type']] }}-800 flex-shrink-0">
                {{ $typeLabels[$item['type']] }}
            </span>
        </div>

        <!-- Title -->
        <h3 class="font-medium text-gray-900 text-sm truncate mb-1">{{ $item['title'] }}</h3>

        <!-- Subtitle -->
        <div class="flex items-center gap-2 text-xs mb-2">
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                {{ $item['subtitle'] }}
            </span>
            @if($item['meta'])
            <span class="text-gray-500">{{ $item['meta'] }}</span>
            @endif
        </div>

        <!-- Description -->
        <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ Str::limit($item['description'], 80) }}</p>
    </div>

    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-end">
        <a href="{{ $itemUrl }}" @if($item['type'] === 'resource' && $item['model']->url) target="_blank" @endif class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
            View
        </a>
    </div>
</div>
@else
<div class="bg-white rounded-lg border border-gray-200 p-3 hover:shadow-sm transition-shadow flex items-center gap-4">
    <!-- Icon -->
    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
        @include('livewire.resource-library.icons.' . $item['icon'])
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <h3 class="font-medium text-gray-900 text-sm truncate">{{ $item['title'] }}</h3>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $typeColors[$item['type']] }}-100 text-{{ $typeColors[$item['type']] }}-800">
                {{ $typeLabels[$item['type']] }}
            </span>
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                {{ $item['subtitle'] }}
            </span>
        </div>
        <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
            @if($item['meta'])
            <span>{{ $item['meta'] }}</span>
            @endif
            <span class="truncate max-w-xs">{{ Str::limit($item['description'], 60) }}</span>
        </div>
    </div>

    <!-- Action -->
    <a href="{{ $itemUrl }}" @if($item['type'] === 'resource' && $item['model']->url) target="_blank" @endif class="ml-2 px-3 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
        View
    </a>
</div>
@endif
