@php
    $typeColors = [
        'therapist' => 'purple',
        'tutor' => 'blue',
        'coach' => 'green',
        'mentor' => 'yellow',
        'counselor' => 'pink',
        'specialist' => 'indigo',
    ];
@endphp

<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Provider</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Specialties</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Availability</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rate</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rating</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($providers as $provider)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-100 to-purple-50 flex items-center justify-center text-purple-600 font-semibold text-sm flex-shrink-0">
                                {{ substr($provider->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="flex items-center gap-1">
                                    <span class="text-sm font-medium text-gray-900">{{ $provider->name }}</span>
                                    @if($provider->verified_at)
                                        <x-icon name="check-badge" class="w-4 h-4 text-blue-500" />
                                    @endif
                                </div>
                                @if($provider->credentials)
                                    <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($provider->credentials, 40) }}</div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $typeColors[$provider->provider_type] ?? 'gray' }}-100 text-{{ $typeColors[$provider->provider_type] ?? 'gray' }}-700">
                            {{ ucfirst($provider->provider_type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($provider->specialty_areas && count($provider->specialty_areas) > 0)
                            <div class="flex flex-wrap gap-1 max-w-xs">
                                @foreach(array_slice($provider->specialty_areas, 0, 2) as $specialty)
                                    <span class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $specialty }}</span>
                                @endforeach
                                @if(count($provider->specialty_areas) > 2)
                                    <span class="text-xs text-gray-400">+{{ count($provider->specialty_areas) - 2 }}</span>
                                @endif
                            </div>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm">
                        <div class="flex items-center gap-2">
                            @if($provider->serves_remote)
                                <span class="text-green-600 text-xs">Remote</span>
                            @endif
                            @if($provider->serves_in_person)
                                <span class="text-gray-600 text-xs">In-person</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                        {{ $provider->hourly_rate ? '$' . number_format($provider->hourly_rate) . '/hr' : '-' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($provider->ratings_average)
                            <div class="flex items-center text-yellow-500">
                                <x-icon name="star" class="w-4 h-4 fill-current" />
                                <span class="ml-1 text-sm text-gray-900">{{ number_format($provider->ratings_average, 1) }}</span>
                            </div>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        <a href="{{ route('resources.providers.show', $provider) }}" class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                            View
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
