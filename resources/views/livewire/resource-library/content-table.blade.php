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
@endphp

<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resource</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($resources as $resource)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                @include('livewire.resource-library.icons.' . ($typeIcons[$resource->resource_type] ?? 'document'))
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $resource->title }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($resource->description, 50) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $typeColors[$resource->resource_type] ?? 'gray' }}-100 text-{{ $typeColors[$resource->resource_type] ?? 'gray' }}-700">
                            {{ ucfirst($resource->resource_type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $resource->category ? ucfirst($resource->category) : '-' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $resource->estimated_duration_minutes ? $resource->estimated_duration_minutes . ' min' : '-' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($resource->target_risk_levels && count($resource->target_risk_levels) > 0)
                            <div class="flex gap-1">
                                @foreach($resource->target_risk_levels as $level)
                                    <span class="w-2 h-2 rounded-full {{ $level === 'high' ? 'bg-red-400' : ($level === 'low' ? 'bg-yellow-400' : 'bg-green-400') }}" title="{{ ucfirst($level) }} risk"></span>
                                @endforeach
                            </div>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        <a href="{{ $resource->url ?? '#' }}" target="{{ $resource->url ? '_blank' : '_self' }}" class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                            @if($resource->url)
                                Open
                            @else
                                View
                            @endif
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
