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
    $color = $typeColors[$course->course_type] ?? 'gray';
@endphp

@if($viewMode === 'grid')
<div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
    <div class="p-4">
        <div class="flex items-start justify-between gap-2 mb-3">
            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-pulse-orange-100 to-pulse-orange-50 flex items-center justify-center flex-shrink-0">
                <x-icon name="academic-cap" class="w-5 h-5 text-pulse-orange-600" />
            </div>
            <div class="flex items-center gap-1">
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColors[$course->status] ?? 'gray' }}-100 text-{{ $statusColors[$course->status] ?? 'gray' }}-800">
                    {{ ucfirst($course->status) }}
                </span>
            </div>
        </div>

        <div class="flex items-center gap-2 mb-1">
            <h3 class="font-medium text-gray-900 text-sm truncate">{{ $course->title }}</h3>
            @if($course->creation_source === 'ai_generated')
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">AI</span>
            @endif
        </div>

        <div class="flex items-center gap-2 text-xs mb-2">
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700">
                {{ ucfirst(str_replace('_', ' ', $course->course_type)) }}
            </span>
            <span class="text-gray-500">{{ $course->steps_count }} steps</span>
        </div>

        <p class="text-xs text-gray-500 line-clamp-2 mb-3">{{ Str::limit($course->description, 80) }}</p>

        <div class="flex items-center justify-between text-xs mb-3">
            <div class="flex items-center gap-3 text-gray-500">
                @if($course->estimated_duration_minutes)
                <span>{{ $course->estimated_duration_minutes }} min</span>
                @endif
                @if($course->enrollments_count > 0)
                <span>{{ number_format($course->enrollments_count) }} enrolled</span>
                @endif
            </div>
            @if($course->is_template)
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Template</span>
            @endif
        </div>
    </div>

    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-1">
            @if($course->status === 'draft')
            <a href="{{ route('resources.courses.edit', $course) }}" class="p-1 text-gray-400 hover:text-gray-600 rounded" title="Edit">
                <x-icon name="pencil" class="w-3.5 h-3.5" />
            </a>
            @endif
        </div>
        <a href="{{ route('resources.courses.show', $course) }}" class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
            View
        </a>
    </div>
</div>
@else
<div class="bg-white rounded-lg border border-gray-200 p-3 hover:shadow-sm transition-shadow flex items-center gap-4">
    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-pulse-orange-100 to-pulse-orange-50 flex items-center justify-center flex-shrink-0">
        <x-icon name="academic-cap" class="w-5 h-5 text-pulse-orange-600" />
    </div>

    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <h3 class="font-medium text-gray-900 text-sm truncate">{{ $course->title }}</h3>
            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColors[$course->status] ?? 'gray' }}-100 text-{{ $statusColors[$course->status] ?? 'gray' }}-800">
                {{ ucfirst($course->status) }}
            </span>
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700">
                {{ ucfirst(str_replace('_', ' ', $course->course_type)) }}
            </span>
            @if($course->creation_source === 'ai_generated')
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">AI</span>
            @endif
            @if($course->is_template)
            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">Template</span>
            @endif
        </div>
        <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
            <span>{{ $course->steps_count }} steps</span>
            @if($course->estimated_duration_minutes)
            <span>{{ $course->estimated_duration_minutes }} min</span>
            @endif
            @if($course->enrollments_count > 0)
            <span>{{ number_format($course->enrollments_count) }} enrolled</span>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-1">
        @if($course->status === 'draft')
        <a href="{{ route('resources.courses.edit', $course) }}" class="p-1.5 text-gray-400 hover:text-gray-600 rounded" title="Edit">
            <x-icon name="pencil" class="w-4 h-4" />
        </a>
        @endif
        <a href="{{ route('resources.courses.show', $course) }}" class="ml-2 px-3 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
            View
        </a>
    </div>
</div>
@endif
