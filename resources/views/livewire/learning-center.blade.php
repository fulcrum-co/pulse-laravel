<div class="min-h-screen bg-gray-50">
    <!-- Header Banner -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('resources.index') }}" class="text-gray-400 hover:text-gray-600">
                    <x-icon name="chevron-left" class="w-5 h-5" />
                </a>
                <div>
                    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                        <a href="{{ route('resources.index') }}" class="hover:text-gray-700">Resources</a>
                        <span>/</span>
                        <span class="text-gray-900">Courses</span>
                    </nav>
                    <h1 class="text-2xl font-semibold text-gray-900">Learning Center</h1>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('resources.courses.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                >
                    <x-icon name="plus" class="w-4 h-4" />
                    Create Course
                </a>
            </div>
        </div>
    </div>

    <div class="px-6 py-6">
        <!-- Search & Category Tabs -->
        <div class="bg-white rounded-xl border border-gray-200 mb-6">
            <!-- Search -->
            <div class="p-4 border-b border-gray-100">
                <div class="relative max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-icon name="magnifying-glass" class="h-5 w-5 text-gray-400" />
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search courses..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    >
                </div>
            </div>

            <!-- Category Tabs -->
            <div class="px-4 py-2 flex items-center gap-1 overflow-x-auto">
                @foreach($this->categories as $key => $label)
                    @php
                        $count = $this->categoryCounts[$key] ?? 0;
                        $isActive = $activeCategory === $key;
                    @endphp
                    <button
                        wire:click="setCategory('{{ $key }}')"
                        class="px-4 py-2 text-sm font-medium rounded-lg transition-colors whitespace-nowrap {{ $isActive ? 'bg-pulse-orange-100 text-pulse-orange-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}"
                    >
                        {{ $label }}
                        <span class="ml-1 text-xs {{ $isActive ? 'text-pulse-orange-500' : 'text-gray-400' }}">
                            ({{ $count }})
                        </span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Results Count -->
        <div class="mb-4">
            <p class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $courses->count() }}</span> of <span class="font-medium">{{ $courses->total() }}</span> courses
            </p>
        </div>

        @if($courses->count() > 0)
            <!-- Course Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($courses as $course)
                    @php
                        $typeColors = [
                            'wellness' => 'green',
                            'academic' => 'blue',
                            'behavioral' => 'purple',
                            'skill_building' => 'yellow',
                            'intervention' => 'red',
                            'enrichment' => 'orange',
                        ];
                        $color = $typeColors[$course->course_type] ?? 'gray';
                    @endphp
                    <div class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-pulse-orange-300 transition-all">
                        <div class="p-5">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-{{ $color }}-100 flex items-center justify-center flex-shrink-0">
                                    <x-icon name="academic-cap" class="w-6 h-6 text-{{ $color }}-600" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-1">
                                        @if($course->is_ai_generated)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">
                                                <x-icon name="sparkles" class="w-3 h-3" />
                                                AI
                                            </span>
                                        @endif
                                        @if($course->is_template)
                                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                                Template
                                            </span>
                                        @endif
                                    </div>
                                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">
                                        {{ $course->title }}
                                    </h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700 mt-1">
                                        {{ ucfirst(str_replace('_', ' ', $course->course_type)) }}
                                    </span>
                                </div>
                            </div>

                            @if($course->description)
                                <p class="mt-3 text-sm text-gray-600 line-clamp-2">
                                    {{ $course->description }}
                                </p>
                            @endif

                            <!-- Course Stats -->
                            <div class="mt-4 flex items-center gap-4 text-xs text-gray-500">
                                <span class="flex items-center gap-1">
                                    <x-icon name="queue-list" class="w-3.5 h-3.5" />
                                    {{ $course->steps_count }} steps
                                </span>
                                @if($course->estimated_duration_minutes)
                                    <span class="flex items-center gap-1">
                                        <x-icon name="clock" class="w-3.5 h-3.5" />
                                        {{ $course->estimated_duration_minutes }} min
                                    </span>
                                @endif
                                <span class="flex items-center gap-1">
                                    <x-icon name="users" class="w-3.5 h-3.5" />
                                    {{ $course->enrollments_count }} enrolled
                                </span>
                            </div>
                        </div>

                        <!-- Card Footer -->
                        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                            <a
                                href="{{ route('resources.courses.show', $course) }}"
                                class="text-xs font-medium text-gray-600 hover:text-gray-900"
                            >
                                Preview
                            </a>
                            <a
                                href="{{ route('resources.courses.edit', $course) }}"
                                class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700"
                            >
                                Edit &rarr;
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $courses->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl border border-gray-200 text-center py-16">
                <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center mx-auto mb-4">
                    <x-icon name="academic-cap" class="w-8 h-8 text-orange-400" />
                </div>
                @if($search || $activeCategory !== 'all')
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No courses match your criteria</h3>
                    <p class="text-gray-500 mb-4">Try adjusting your search or selecting a different category.</p>
                    <button
                        wire:click="$set('search', ''); $set('activeCategory', 'all')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Clear filters
                    </button>
                @else
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No courses yet</h3>
                    <p class="text-gray-500 mb-4">Create personalized learning paths and mini-courses for students.</p>
                    <div class="flex items-center justify-center gap-3">
                        <a
                            href="{{ route('resources.courses.create') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                        >
                            <x-icon name="plus" class="w-4 h-4" />
                            Create Course
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
