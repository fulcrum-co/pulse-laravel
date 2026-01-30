@php
    $typeColors = [
        'resource' => 'bg-blue-100 text-blue-700',
        'provider' => 'bg-purple-100 text-purple-700',
        'program' => 'bg-green-100 text-green-700',
        'course' => 'bg-orange-100 text-orange-700',
    ];
    $typeLabels = [
        'resource' => 'Content',
        'provider' => 'Provider',
        'program' => 'Program',
        'course' => 'Course',
    ];
@endphp

@if($viewMode === 'grid')
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg hover:border-gray-300 transition-all group">
    <div class="p-4">
        <!-- Type Badge & Icon -->
        <div class="flex items-start justify-between mb-3">
            <div class="w-12 h-12 rounded-xl bg-gray-100 flex items-center justify-center group-hover:bg-pulse-orange-50 transition-colors">
                @include('livewire.resource-library.icons.' . $item['icon'])
            </div>
            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $typeColors[$item['type']] }}">
                {{ $typeLabels[$item['type']] }}
            </span>
        </div>

        <!-- Title -->
        <h3 class="font-semibold text-gray-900 mb-1 line-clamp-1">{{ $item['title'] }}</h3>

        <!-- Subtitle -->
        <p class="text-sm text-gray-500 mb-2">{{ $item['subtitle'] }}</p>

        <!-- Description -->
        <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ Str::limit($item['description'], 80) }}</p>

        <!-- Meta -->
        @if($item['meta'])
        <div class="flex items-center text-xs text-gray-500">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            {{ $item['meta'] }}
        </div>
        @endif
    </div>
</div>
@else
<div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-gray-300 transition-all flex items-center gap-4">
    <!-- Icon -->
    <div class="w-10 h-10 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
        @include('livewire.resource-library.icons.' . $item['icon'])
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
            <h3 class="font-medium text-gray-900 truncate">{{ $item['title'] }}</h3>
            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $typeColors[$item['type']] }}">
                {{ $typeLabels[$item['type']] }}
            </span>
        </div>
        <p class="text-sm text-gray-500">{{ $item['subtitle'] }}</p>
    </div>

    <!-- Meta -->
    @if($item['meta'])
    <div class="text-sm text-gray-500 hidden sm:block">
        {{ $item['meta'] }}
    </div>
    @endif

    <!-- Arrow -->
    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
    </svg>
</div>
@endif
