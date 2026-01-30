@php
    $typeColors = [
        'therapist' => 'bg-purple-100 text-purple-700',
        'tutor' => 'bg-blue-100 text-blue-700',
        'coach' => 'bg-green-100 text-green-700',
        'mentor' => 'bg-yellow-100 text-yellow-700',
        'counselor' => 'bg-pink-100 text-pink-700',
        'specialist' => 'bg-indigo-100 text-indigo-700',
    ];
@endphp

@if($viewMode === 'grid')
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg hover:border-gray-300 transition-all group cursor-pointer">
    <div class="p-4">
        <!-- Avatar & Type -->
        <div class="flex items-start justify-between mb-3">
            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-purple-100 to-purple-50 flex items-center justify-center text-purple-600 font-semibold text-lg">
                {{ substr($provider->name, 0, 1) }}
            </div>
            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $typeColors[$provider->provider_type] ?? 'bg-gray-100 text-gray-700' }}">
                {{ ucfirst($provider->provider_type) }}
            </span>
        </div>

        <!-- Name -->
        <h3 class="font-semibold text-gray-900 mb-1 group-hover:text-pulse-orange-600 transition-colors">
            {{ $provider->name }}
        </h3>

        <!-- Credentials -->
        @if($provider->credentials)
        <p class="text-xs text-gray-500 mb-2">{{ Str::limit($provider->credentials, 50) }}</p>
        @endif

        <!-- Specialties -->
        @if($provider->specialty_areas && count($provider->specialty_areas) > 0)
        <div class="flex flex-wrap gap-1 mb-3">
            @foreach(array_slice($provider->specialty_areas, 0, 3) as $specialty)
            <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $specialty }}</span>
            @endforeach
            @if(count($provider->specialty_areas) > 3)
            <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">+{{ count($provider->specialty_areas) - 3 }}</span>
            @endif
        </div>
        @endif

        <!-- Footer -->
        <div class="flex items-center justify-between text-xs text-gray-500 pt-3 border-t border-gray-100">
            <div class="flex items-center gap-2">
                @if($provider->serves_remote)
                <span class="flex items-center text-green-600">
                    <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                    </svg>
                    Remote
                </span>
                @endif
                @if($provider->serves_in_person)
                <span class="flex items-center">
                    <svg class="w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    In-person
                </span>
                @endif
            </div>

            @if($provider->ratings_average)
            <div class="flex items-center text-yellow-500">
                <svg class="w-4 h-4 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                </svg>
                {{ number_format($provider->ratings_average, 1) }}
            </div>
            @endif
        </div>
    </div>
</div>
@else
<div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-gray-300 transition-all flex items-center gap-4 cursor-pointer">
    <!-- Avatar -->
    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-purple-100 to-purple-50 flex items-center justify-center text-purple-600 font-semibold flex-shrink-0">
        {{ substr($provider->name, 0, 1) }}
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
            <h3 class="font-medium text-gray-900 truncate">{{ $provider->name }}</h3>
            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $typeColors[$provider->provider_type] ?? 'bg-gray-100 text-gray-700' }}">
                {{ ucfirst($provider->provider_type) }}
            </span>
            @if($provider->verified_at)
            <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
            </svg>
            @endif
        </div>
        <p class="text-sm text-gray-500 truncate">{{ $provider->credentials }}</p>
    </div>

    <!-- Meta -->
    <div class="hidden sm:flex items-center gap-4 text-sm text-gray-500">
        @if($provider->ratings_average)
        <div class="flex items-center text-yellow-500">
            <svg class="w-4 h-4 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
            </svg>
            {{ number_format($provider->ratings_average, 1) }}
        </div>
        @endif
        @if($provider->hourly_rate)
        <span>${{ number_format($provider->hourly_rate) }}/hr</span>
        @endif
    </div>

    <!-- Arrow -->
    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
    </svg>
</div>
@endif
