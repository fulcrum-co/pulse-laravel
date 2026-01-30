@php
    $typeColors = [
        'intervention' => 'bg-red-100 text-red-700',
        'enrichment' => 'bg-green-100 text-green-700',
        'skill_building' => 'bg-blue-100 text-blue-700',
        'wellness' => 'bg-purple-100 text-purple-700',
        'academic' => 'bg-yellow-100 text-yellow-700',
        'behavioral' => 'bg-orange-100 text-orange-700',
    ];
    $sourceIcons = [
        'ai_generated' => ['icon' => 'sparkles', 'label' => 'AI Generated', 'class' => 'text-purple-500'],
        'human_created' => ['icon' => 'user', 'label' => 'Human Created', 'class' => 'text-blue-500'],
        'hybrid' => ['icon' => 'refresh', 'label' => 'Hybrid', 'class' => 'text-green-500'],
        'template' => ['icon' => 'template', 'label' => 'Template', 'class' => 'text-gray-500'],
    ];
@endphp

@if($viewMode === 'grid')
<a href="{{ route('resources.courses.show', $course) ?? '#' }}" class="block bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg hover:border-gray-300 transition-all group">
    <!-- Header with gradient -->
    <div class="h-24 bg-gradient-to-br from-pulse-orange-400 to-pulse-orange-600 relative p-4">
        <!-- Course Type Badge -->
        <span class="absolute top-3 right-3 px-2 py-1 text-xs font-medium rounded-full bg-white/90 backdrop-blur-sm {{ str_replace(['bg-', 'text-'], ['', 'text-'], $typeColors[$course->course_type] ?? '') }}">
            {{ ucfirst(str_replace('_', ' ', $course->course_type)) }}
        </span>

        <!-- AI Badge -->
        @if($course->creation_source === 'ai_generated')
        <div class="absolute bottom-3 left-3 flex items-center gap-1 text-white/90 text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
            </svg>
            AI Generated
        </div>
        @endif

        <!-- Template Badge -->
        @if($course->is_template)
        <div class="absolute bottom-3 right-3 flex items-center gap-1 text-white/90 text-xs">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
            </svg>
            Template
        </div>
        @endif
    </div>

    <div class="p-4">
        <!-- Title -->
        <h3 class="font-semibold text-gray-900 mb-1 line-clamp-1 group-hover:text-pulse-orange-600 transition-colors">
            {{ $course->title }}
        </h3>

        <!-- Description -->
        <p class="text-sm text-gray-600 line-clamp-2 mb-3">{{ Str::limit($course->description, 100) }}</p>

        <!-- Objectives Preview -->
        @if($course->objectives && count($course->objectives) > 0)
        <div class="mb-3">
            <p class="text-xs text-gray-500 mb-1">Objectives:</p>
            <ul class="text-xs text-gray-600 space-y-0.5">
                @foreach(array_slice($course->objectives, 0, 2) as $objective)
                <li class="flex items-start gap-1">
                    <svg class="w-3 h-3 text-green-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="line-clamp-1">{{ $objective }}</span>
                </li>
                @endforeach
                @if(count($course->objectives) > 2)
                <li class="text-gray-400">+{{ count($course->objectives) - 2 }} more</li>
                @endif
            </ul>
        </div>
        @endif

        <!-- Footer -->
        <div class="flex items-center justify-between text-xs text-gray-500 pt-3 border-t border-gray-100">
            <div class="flex items-center gap-3">
                <!-- Steps Count -->
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    {{ $course->steps_count }} steps
                </span>

                <!-- Duration -->
                @if($course->estimated_duration_minutes)
                <span class="flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $course->estimated_duration_minutes }}m
                </span>
                @endif
            </div>

            <!-- Enrollments -->
            @if($course->enrollments_count > 0)
            <span class="flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
                {{ $course->enrollments_count }}
            </span>
            @endif
        </div>
    </div>
</a>
@else
<a href="{{ route('resources.courses.show', $course) ?? '#' }}" class="block bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md hover:border-gray-300 transition-all flex items-center gap-4">
    <!-- Icon -->
    <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-pulse-orange-100 to-pulse-orange-50 flex items-center justify-center flex-shrink-0">
        <svg class="w-6 h-6 text-pulse-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
        </svg>
    </div>

    <!-- Content -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 mb-1">
            <h3 class="font-medium text-gray-900 truncate">{{ $course->title }}</h3>
            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $typeColors[$course->course_type] ?? 'bg-gray-100 text-gray-700' }}">
                {{ ucfirst(str_replace('_', ' ', $course->course_type)) }}
            </span>
            @if($course->creation_source === 'ai_generated')
            <svg class="w-4 h-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="AI Generated">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
            </svg>
            @endif
            @if($course->is_template)
            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Template">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"></path>
            </svg>
            @endif
        </div>
        <p class="text-sm text-gray-600 truncate">{{ $course->description }}</p>
    </div>

    <!-- Meta -->
    <div class="hidden sm:flex items-center gap-4 text-sm text-gray-500">
        <span>{{ $course->steps_count }} steps</span>
        @if($course->estimated_duration_minutes)
        <span>{{ $course->estimated_duration_minutes }}m</span>
        @endif
        @if($course->enrollments_count > 0)
        <span>{{ $course->enrollments_count }} enrolled</span>
        @endif
    </div>

    <!-- Arrow -->
    <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
    </svg>
</a>
@endif
