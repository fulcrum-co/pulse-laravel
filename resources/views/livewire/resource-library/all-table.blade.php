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
@endphp

<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($items as $item)
                @php
                    $itemUrl = match($item['type']) {
                        'provider' => route('resources.providers.show', $item['id']),
                        'program' => route('resources.programs.show', $item['id']),
                        'course' => route('resources.courses.show', $item['id']),
                        'resource' => $item['model']->url ?? '#',
                    };
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                @include('livewire.resource-library.icons.' . $item['icon'])
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $item['title'] }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($item['description'], 50) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $typeColors[$item['type']] }}-100 text-{{ $typeColors[$item['type']] }}-700">
                            {{ $typeLabels[$item['type']] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $item['subtitle'] }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $item['meta'] ?? '-' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        <a href="{{ $itemUrl }}" @if($item['type'] === 'resource' && $item['model']->url) target="_blank" @endif class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                            View
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
