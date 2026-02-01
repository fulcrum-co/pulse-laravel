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
        'resource' => route('resources.show', $item['id']),
    };
@endphp

@if($viewMode === 'grid')
<a href="{{ $itemUrl }}" class="block bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md hover:border-gray-300 transition-all h-full flex flex-col group">
    <div class="p-4 flex-1">
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
        <h3 class="font-medium text-gray-900 text-sm truncate mb-1 group-hover:text-pulse-orange-600 transition-colors">{{ $item['title'] }}</h3>

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
        <p class="text-xs text-gray-500 line-clamp-2">{{ Str::limit($item['description'], 80) }}</p>
    </div>

    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-end mt-auto">
        <span class="text-xs font-medium text-pulse-orange-600 group-hover:text-pulse-orange-700">
            View
        </span>
    </div>
</a>
@else
<a href="{{ $itemUrl }}" class="block bg-white rounded-lg border border-gray-200 p-3 hover:shadow-sm hover:border-gray-300 transition-all flex items-center gap-4 group">
    <!-- Icon -->
    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
        @include('livewire.resource-library.icons.' . $item['icon'])
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <h3 class="font-medium text-gray-900 text-sm truncate group-hover:text-pulse-orange-600 transition-colors">{{ $item['title'] }}</h3>
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
    <span class="ml-2 px-3 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded group-hover:bg-pulse-orange-600 transition-colors">
        View
    </span>
</a>
@endif
