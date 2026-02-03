@php
    $typeColors = [
        'therapist' => 'purple',
        'tutor' => 'blue',
        'coach' => 'green',
        'mentor' => 'yellow',
        'support_person' => 'pink',
        'specialist' => 'indigo',
    ];
    $color = $typeColors[$provider->provider_type] ?? 'gray';
@endphp

@if($viewMode === 'grid')
<a href="{{ route('resources.providers.show', $provider) }}" class="block bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md hover:border-gray-300 transition-all h-full flex flex-col group">
    <div class="p-4 flex-1">
        <div class="flex items-start justify-between gap-2 mb-3">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-100 to-purple-50 flex items-center justify-center text-purple-600 font-semibold text-sm flex-shrink-0">
                {{ substr($provider->name, 0, 1) }}
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 flex-shrink-0">
                {{ ucfirst($provider->provider_type) }}
            </span>
        </div>

        <div class="flex items-center gap-1 mb-1">
            <h3 class="font-medium text-gray-900 text-sm truncate group-hover:text-pulse-orange-600 transition-colors">{{ $provider->name }}</h3>
            @if($provider->verified_at)
            <x-icon name="check-badge" class="w-4 h-4 text-blue-500 flex-shrink-0" />
            @endif
        </div>

        @if($provider->credentials)
        <p class="text-xs text-gray-500 mb-2 truncate">{{ Str::limit($provider->credentials, 40) }}</p>
        @endif

        @if($provider->specialty_areas && count($provider->specialty_areas) > 0)
        <div class="flex flex-wrap gap-1 mb-3">
            @foreach(array_slice($provider->specialty_areas, 0, 2) as $specialty)
            <span class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $specialty }}</span>
            @endforeach
            @if(count($provider->specialty_areas) > 2)
            <span class="text-xs text-gray-400">+{{ count($provider->specialty_areas) - 2 }}</span>
            @endif
        </div>
        @endif

        <div class="flex items-center justify-between text-xs">
            <div class="flex items-center gap-2">
                @if($provider->serves_remote)
                <span class="text-green-600">Remote</span>
                @endif
                @if($provider->serves_in_person)
                <span class="text-gray-600">In-person</span>
                @endif
            </div>
            @if($provider->ratings_average)
            <div class="flex items-center text-yellow-500">
                <x-icon name="star" class="w-3 h-3 fill-current" />
                <span class="ml-0.5 text-gray-900">{{ number_format($provider->ratings_average, 1) }}</span>
            </div>
            @endif
        </div>
    </div>

    <div class="px-4 py-3 bg-gray-50 border-t border-gray-100 mt-auto">
        @if($provider->hourly_rate)
        <p class="text-xs text-gray-500 mb-2">${{ number_format($provider->hourly_rate) }}/hr</p>
        @endif
        <span class="block w-full text-center px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg group-hover:bg-pulse-orange-600 transition-colors">
            View Profile
        </span>
    </div>
</a>
@else
<a href="{{ route('resources.providers.show', $provider) }}" class="block bg-white rounded-lg border border-gray-200 p-3 hover:shadow-sm hover:border-gray-300 transition-all flex items-center gap-4 group">
    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-100 to-purple-50 flex items-center justify-center text-purple-600 font-semibold text-sm flex-shrink-0">
        {{ substr($provider->name, 0, 1) }}
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <h3 class="font-medium text-gray-900 text-sm truncate group-hover:text-pulse-orange-600 transition-colors">{{ $provider->name }}</h3>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                {{ ucfirst($provider->provider_type) }}
            </span>
            @if($provider->verified_at)
            <x-icon name="check-badge" class="w-4 h-4 text-blue-500" />
            @endif
        </div>
        <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
            @if($provider->credentials)
            <span class="truncate">{{ Str::limit($provider->credentials, 40) }}</span>
            @endif
            @if($provider->serves_remote)
            <span class="text-green-600">Remote</span>
            @endif
        </div>
    </div>

    <div class="hidden sm:flex items-center gap-4 text-sm text-gray-500">
        @if($provider->ratings_average)
        <div class="flex items-center text-yellow-500">
            <x-icon name="star" class="w-4 h-4 fill-current" />
            <span class="ml-0.5 text-gray-900">{{ number_format($provider->ratings_average, 1) }}</span>
        </div>
        @endif
        @if($provider->hourly_rate)
        <span>${{ number_format($provider->hourly_rate) }}/hr</span>
        @endif
    </div>

    <span class="ml-2 px-3 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded group-hover:bg-pulse-orange-600 transition-colors">
        View Profile
    </span>
</a>
@endif
