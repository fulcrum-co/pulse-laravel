@php
    $typeColors = [
        'therapy' => 'purple',
        'tutoring' => 'blue',
        'mentorship' => 'yellow',
        'enrichment' => 'green',
        'intervention' => 'red',
        'support_group' => 'pink',
        'external_service' => 'gray',
    ];
    $costLabels = [
        'free' => ['label' => 'Free', 'class' => 'text-green-600'],
        'sliding_scale' => ['label' => 'Sliding Scale', 'class' => 'text-blue-600'],
        'fixed' => ['label' => 'Paid', 'class' => 'text-gray-600'],
        'insurance' => ['label' => 'Insurance', 'class' => 'text-purple-600'],
    ];
    $color = $typeColors[$program->program_type] ?? 'gray';
@endphp

@if($viewMode === 'grid')
<a href="{{ route('resources.programs.show', $program) }}" class="block bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md hover:border-gray-300 transition-all h-full flex flex-col group">
    <div class="p-4 flex-1">
        <div class="flex items-start justify-between gap-2 mb-3">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-100 to-green-50 flex items-center justify-center flex-shrink-0">
                <x-icon name="building-office" class="w-5 h-5 text-green-600" />
            </div>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800 flex-shrink-0">
                {{ ucfirst(str_replace('_', ' ', $program->program_type)) }}
            </span>
        </div>

        <h3 class="font-medium text-gray-900 text-sm truncate mb-1 group-hover:text-pulse-orange-600 transition-colors">{{ $program->name }}</h3>

        @if($program->provider_org_name)
        <p class="text-xs text-gray-500 mb-2">{{ $program->provider_org_name }}</p>
        @endif

        <div class="flex items-center gap-2 text-xs mb-2">
            <span class="text-gray-500">{{ ucfirst(str_replace('_', '-', $program->location_type)) }}</span>
            @if($program->duration_weeks)
            <span class="text-gray-400">|</span>
            <span class="text-gray-500">{{ $program->duration_weeks }}w</span>
            @endif
        </div>

        @if($program->target_needs && count($program->target_needs) > 0)
        <div class="flex flex-wrap gap-1">
            @foreach(array_slice($program->target_needs, 0, 2) as $need)
            <span class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $need }}</span>
            @endforeach
            @if(count($program->target_needs) > 2)
            <span class="text-xs text-gray-400">+{{ count($program->target_needs) - 2 }}</span>
            @endif
        </div>
        @endif
    </div>

    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between mt-auto">
        @if($program->cost_structure)
        <span class="{{ $costLabels[$program->cost_structure]['class'] ?? 'text-gray-600' }} text-xs font-medium">
            {{ $costLabels[$program->cost_structure]['label'] ?? ucfirst($program->cost_structure) }}
        </span>
        @else
        <span></span>
        @endif
        <span class="text-xs font-medium text-pulse-orange-600 group-hover:text-pulse-orange-700">
            View Details
        </span>
    </div>
</a>
@else
<a href="{{ route('resources.programs.show', $program) }}" class="block bg-white rounded-lg border border-gray-200 p-3 hover:shadow-sm hover:border-gray-300 transition-all flex items-center gap-4 group">
    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-green-100 to-green-50 flex items-center justify-center flex-shrink-0">
        <x-icon name="building-office" class="w-5 h-5 text-green-600" />
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <h3 class="font-medium text-gray-900 text-sm truncate group-hover:text-pulse-orange-600 transition-colors">{{ $program->name }}</h3>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                {{ ucfirst(str_replace('_', ' ', $program->program_type)) }}
            </span>
        </div>
        <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
            @if($program->provider_org_name)
            <span>{{ $program->provider_org_name }}</span>
            @endif
            @if($program->duration_weeks)
            <span>{{ $program->duration_weeks }} weeks</span>
            @endif
        </div>
    </div>

    <div class="hidden sm:flex items-center gap-4 text-sm text-gray-500">
        <span>{{ ucfirst(str_replace('_', '-', $program->location_type)) }}</span>
        @if($program->cost_structure)
        <span class="{{ $costLabels[$program->cost_structure]['class'] ?? 'text-gray-600' }} font-medium">
            {{ $costLabels[$program->cost_structure]['label'] ?? ucfirst($program->cost_structure) }}
        </span>
        @endif
    </div>

    <span class="ml-2 px-3 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded group-hover:bg-pulse-orange-600 transition-colors">
        View
    </span>
</a>
@endif
