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
@endphp

<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Program</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cost</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($programs as $program)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-green-100 to-green-50 flex items-center justify-center flex-shrink-0">
                                <x-icon name="building-office" class="w-4 h-4 text-green-600" />
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $program->name }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($program->description, 50) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $typeColors[$program->program_type] ?? 'gray' }}-100 text-{{ $typeColors[$program->program_type] ?? 'gray' }}-700">
                            {{ ucfirst(str_replace('_', ' ', $program->program_type)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $program->provider_org_name ?? '-' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ ucfirst(str_replace('_', '-', $program->location_type)) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $program->duration_weeks ? $program->duration_weeks . ' weeks' : 'Ongoing' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="{{ $costLabels[$program->cost_structure]['class'] ?? 'text-gray-600' }} text-sm font-medium">
                            {{ $costLabels[$program->cost_structure]['label'] ?? ucfirst($program->cost_structure) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        <a href="{{ route('resources.programs.show', $program) }}" class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                            View
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
