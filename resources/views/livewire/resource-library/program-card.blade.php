@php
    $typeColors = [
        'therapy' => 'bg-purple-100 text-purple-700',
        'tutoring' => 'bg-blue-100 text-blue-700',
        'mentorship' => 'bg-yellow-100 text-yellow-700',
        'enrichment' => 'bg-green-100 text-green-700',
        'intervention' => 'bg-red-100 text-red-700',
        'support_group' => 'bg-pink-100 text-pink-700',
        'external_service' => 'bg-gray-100 text-gray-700',
    ];
    $costLabels = [
        'free' => ['label' => 'Free', 'class' => 'text-green-600'],
        'sliding_scale' => ['label' => 'Sliding Scale', 'class' => 'text-blue-600'],
        'fixed' => ['label' => 'Paid', 'class' => 'text-gray-600'],
        'insurance' => ['label' => 'Insurance', 'class' => 'text-purple-600'],
    ];
    $locationIcons = [
        'in_person' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
        'virtual' => 'M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z',
        'hybrid' => 'M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    ];
@endphp

@if($viewMode === 'grid')
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg hover:border-gray-300 transition-all group cursor-pointer">
    <div class="p-4">
        <!-- Header -->
        <div class="flex items-start justify-between mb-3">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-100 to-green-50 flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $typeColors[$program->program_type] ?? 'bg-gray-100 text-gray-700' }}">
                {{ ucfirst(str_replace('_', ' ', $program->program_type)) }}
            </span>
        </div>

        <!-- Name -->
        <h3 class="font-semibold text-gray-900 mb-1 line-clamp-1 group-hover:text-pulse-orange-600 transition-colors">
            {{ $program->name }}
        </h3>

        <!-- Provider -->
        @if($program->provider_org_name)
        <p class="text-sm text-gray-500 mb-2">{{ $program->provider_org_name }}</p>
        @endif

        <!-- Description -->
        <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ Str::limit($program->description, 100) }}</p>

        <!-- Target Needs -->
        @if($program->target_needs && count($program->target_needs) > 0)
        <div class="flex flex-wrap gap-1 mb-3">
            @foreach(array_slice($program->target_needs, 0, 3) as $need)
            <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $need }}</span>
            @endforeach
        </div>
        @endif

        <!-- Footer -->
        <div class="flex items-center justify-between text-xs text-gray-500 pt-3 border-t border-gray-100">
            <div class="flex items-center gap-3">
                <!-- Location Type -->
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $locationIcons[$program->location_type] ?? $locationIcons['in_person'] }}"></path>
                    </svg>
                    {{ ucfirst(str_replace('_', '-', $program->location_type)) }}
                </span>

                <!-- Duration -->
                @if($program->duration_weeks)
                <span>{{ $program->duration_weeks }}w</span>
                @endif
            </div>

            <!-- Cost -->
            @if($program->cost_structure)
            <span class="{{ $costLabels[$program->cost_structure]['class'] ?? 'text-gray-600' }} font-medium">
                {{ $costLabels[$program->cost_structure]['label'] ?? ucfirst($program->cost_structure) }}
            </span>
            @endif
        </div>
    </div>
</div>
@else
<div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-gray-300 transition-all flex items-center gap-4 cursor-pointer">
    <!-- Icon -->
    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-green-100 to-green-50 flex items-center justify-center flex-shrink-0">
        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
        </svg>
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
            <h3 class="font-medium text-gray-900 truncate">{{ $program->name }}</h3>
            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $typeColors[$program->program_type] ?? 'bg-gray-100 text-gray-700' }}">
                {{ ucfirst(str_replace('_', ' ', $program->program_type)) }}
            </span>
        </div>
        <p class="text-sm text-gray-500 truncate">{{ $program->provider_org_name }}</p>
    </div>

    <!-- Meta -->
    <div class="hidden sm:flex items-center gap-4 text-sm text-gray-500">
        @if($program->duration_weeks)
        <span>{{ $program->duration_weeks }} weeks</span>
        @endif
        @if($program->cost_structure)
        <span class="{{ $costLabels[$program->cost_structure]['class'] ?? 'text-gray-600' }} font-medium">
            {{ $costLabels[$program->cost_structure]['label'] ?? ucfirst($program->cost_structure) }}
        </span>
        @endif
    </div>

    <!-- Arrow -->
    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
    </svg>
</div>
@endif
