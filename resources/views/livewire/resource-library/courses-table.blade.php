@php
    $typeColors = [
        'intervention' => 'red',
        'enrichment' => 'green',
        'skill_building' => 'blue',
        'wellness' => 'purple',
        'academic' => 'yellow',
        'behavioral' => 'orange',
    ];
    $statusColors = [
        'draft' => 'gray',
        'active' => 'green',
        'archived' => 'red',
    ];
@endphp

<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Course</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Steps</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enrollments</th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @foreach($courses as $course)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-pulse-orange-100 to-pulse-orange-50 flex items-center justify-center flex-shrink-0">
                                <x-icon name="academic-cap" class="w-4 h-4 text-pulse-orange-600" />
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900">{{ $course->title }}</span>
                                    @if($course->creation_source === 'ai_generated')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">AI</span>
                                    @endif
                                    @if($course->is_template)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Template</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($course->description, 50) }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $typeColors[$course->course_type] ?? 'gray' }}-100 text-{{ $typeColors[$course->course_type] ?? 'gray' }}-700">
                            {{ ucfirst(str_replace('_', ' ', $course->course_type)) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColors[$course->status] ?? 'gray' }}-100 text-{{ $statusColors[$course->status] ?? 'gray' }}-800">
                            {{ ucfirst($course->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                        {{ $course->steps_count }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                        {{ $course->estimated_duration_minutes ? $course->estimated_duration_minutes . ' min' : '-' }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                        {{ number_format($course->enrollments_count ?? 0) }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-1">
                            @if($course->status === 'draft')
                            <a href="{{ route('resources.courses.edit', $course) }}" class="p-1 text-gray-400 hover:text-gray-600 rounded" title="Edit">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </a>
                            @endif
                            <a href="{{ route('resources.courses.show', $course) }}" class="ml-1 text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                View
                            </a>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
