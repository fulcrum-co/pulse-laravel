<div class="min-h-screen bg-gray-50">
    @php($terminology = app(\App\Services\TerminologyService::class))
    <!-- Header Banner -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('resources.index') }}" class="text-gray-400 hover:text-gray-600">
                    <x-icon name="chevron-left" class="w-5 h-5" />
                </a>
                <div>
                    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                        <a href="{{ route('resources.index') }}" class="hover:text-gray-700">@term('resources_label')</a>
                        <span>@term('breadcrumb_separator_label')</span>
                        <span class="text-gray-900">@term('course_plural')</span>
                    </nav>
                    <h1 class="text-2xl font-semibold text-gray-900">@term('learning_center_label')</h1>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a
                    href="{{ route('resources.courses.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                >
                    <x-icon name="plus" class="w-4 h-4" />
                    @term('create_course_label')
                </a>
            </div>
        </div>
    </div>

    <div class="px-6 py-6">
        <!-- Search & Category Tabs -->
        <div class="bg-white rounded-xl border border-gray-200 mb-6">
            <!-- Search + View Toggle -->
            <div class="p-4 border-b border-gray-100">
                <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                    <div class="relative flex-1 max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-icon name="magnifying-glass" class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="@term('search_courses_placeholder')"
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                    </div>

                    <!-- View Toggle -->
                    <div class="flex items-center border border-gray-300 rounded-lg overflow-hidden">
                        <button
                            wire:click="$set('viewMode', 'grid')"
                            class="p-2 {{ $viewMode === 'grid' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }} transition-colors"
                            title="@term('grid_view_label')"
                        >
                            <x-icon name="squares-2x2" class="w-4 h-4" />
                        </button>
                        <button
                            wire:click="$set('viewMode', 'list')"
                            class="p-2 border-l border-gray-300 {{ $viewMode === 'list' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }} transition-colors"
                            title="@term('list_view_label')"
                        >
                            <x-icon name="bars-3" class="w-4 h-4" />
                        </button>
                        <button
                            wire:click="$set('viewMode', 'table')"
                            class="p-2 border-l border-gray-300 {{ $viewMode === 'table' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-white text-gray-500 hover:bg-gray-50' }} transition-colors"
                            title="@term('table_view_label')"
                        >
                            <x-icon name="table-cells" class="w-4 h-4" />
                        </button>
                    </div>
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
                @term('showing_label') <span class="font-medium">{{ $courses->count() }}</span> @term('of_label') <span class="font-medium">{{ $courses->total() }}</span> @term('courses_label')
            </p>
        </div>

        @php
            $typeColors = [
                'wellness' => 'green',
                'academic' => 'blue',
                'behavioral' => 'purple',
                'skill_building' => 'yellow',
                'intervention' => 'red',
                'enrichment' => 'orange',
            ];
        @endphp

        @if($courses->count() > 0)
            @if($viewMode === 'grid')
                <!-- Grid View -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @foreach($courses as $course)
                        @php $color = $typeColors[$course->course_type] ?? 'gray'; @endphp
                        <a href="{{ route('resources.courses.show', $course) }}" class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-pulse-orange-300 transition-all block">
                            <div class="p-5">
                                <div class="flex items-start gap-4">
                                    <div class="w-12 h-12 rounded-xl bg-{{ $color }}-100 flex items-center justify-center flex-shrink-0">
                                        <x-icon name="academic-cap" class="w-6 h-6 text-{{ $color }}-600" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2 mb-1">
                                            @if($course->isAiGenerated())
                                                <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">
                                                    <x-icon name="sparkles" class="w-3 h-3" />
                                                    @term('ai_label')
                                                </span>
                                            @endif
                                            @if($course->is_template)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                                    @term('template_label')
                                                </span>
                                            @endif
                                        </div>
                                        <h3 class="text-base font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">
                                            {{ $course->title }}
                                        </h3>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700 mt-1">
                                            {{ $terminology->get('course_type_'.$course->course_type.'_label') }}
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
                                        {{ $course->steps_count }} @term('steps_label')
                                    </span>
                                    @if($course->estimated_duration_minutes)
                                        <span class="flex items-center gap-1">
                                            <x-icon name="clock" class="w-3.5 h-3.5" />
                                            {{ $course->estimated_duration_minutes }} @term('minutes_label')
                                        </span>
                                    @endif
                                    <span class="flex items-center gap-1">
                                        <x-icon name="users" class="w-3.5 h-3.5" />
                                        {{ $course->enrollments_count }} @term('enrolled_label')
                                    </span>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                                <span class="text-xs font-medium text-gray-600">
                                    @term('preview_label')
                                </span>
                                <span
                                    onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('resources.courses.edit', $course) }}';"
                                    class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700 cursor-pointer"
                                >
                                    @term('edit_label') &rarr;
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

            @elseif($viewMode === 'list')
                <!-- List View -->
                <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100">
                    @foreach($courses as $course)
                        @php $color = $typeColors[$course->course_type] ?? 'gray'; @endphp
                        <a href="{{ route('resources.courses.show', $course) }}" class="flex items-center gap-5 p-5 hover:bg-gray-50 transition-colors group">
                            <!-- Icon -->
                            <div class="w-12 h-12 rounded-xl bg-{{ $color }}-100 flex items-center justify-center flex-shrink-0">
                                <x-icon name="academic-cap" class="w-6 h-6 text-{{ $color }}-600" />
                            </div>

                            <!-- Info -->
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">
                                        {{ $course->title }}
                                    </h3>
                                    @if($course->isAiGenerated())
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">
                                            <x-icon name="sparkles" class="w-3 h-3" />
                                            @term('ai_label')
                                        </span>
                                    @endif
                                    @if($course->is_template)
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                            @term('template_label')
                                        </span>
                                    @endif
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700">
                                        {{ $terminology->get('course_type_'.$course->course_type.'_label') }}
                                    </span>
                                </div>
                                @if($course->description)
                                    <p class="text-sm text-gray-500 line-clamp-1">{{ $course->description }}</p>
                                @endif
                                <div class="flex items-center gap-4 mt-2 text-xs text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <x-icon name="queue-list" class="w-3.5 h-3.5" />
                                        {{ $course->steps_count }} @term('steps_label')
                                    </span>
                                    @if($course->estimated_duration_minutes)
                                        <span class="flex items-center gap-1">
                                            <x-icon name="clock" class="w-3.5 h-3.5" />
                                            {{ $course->estimated_duration_minutes }} @term('minutes_label')
                                        </span>
                                    @endif
                                    <span class="flex items-center gap-1">
                                        <x-icon name="users" class="w-3.5 h-3.5" />
                                        {{ $course->enrollments_count }} @term('enrolled_label')
                                    </span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center gap-4 flex-shrink-0">
                                <span class="text-sm text-gray-600">
                                    @term('preview_label')
                                </span>
                                <span
                                    onclick="event.preventDefault(); event.stopPropagation(); window.location.href='{{ route('resources.courses.edit', $course) }}';"
                                    class="text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700 cursor-pointer"
                                >
                                    @term('edit_label')
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>

            @else
                <!-- Table View -->
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('course_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('type_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('source_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('steps_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('duration_label')</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('enrolled_label')</th>
                                    <th scope="col" class="relative px-6 py-3">
                                        <span class="sr-only">Actions</span>
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($courses as $course)
                                    @php $color = $typeColors[$course->course_type] ?? 'gray'; @endphp
                                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location.href='{{ route('resources.courses.show', $course) }}';">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-lg bg-{{ $color }}-100 flex items-center justify-center flex-shrink-0">
                                                    <x-icon name="academic-cap" class="w-5 h-5 text-{{ $color }}-600" />
                                                </div>
                                                <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $course->title }}</div>
                                                    @if($course->description)
                                                        <div class="text-xs text-gray-500 max-w-xs truncate">{{ Str::limit($course->description, 50) }}</div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700">
                                                {{ $terminology->get('course_type_'.$course->course_type.'_label') }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($course->isAiGenerated())
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-700">
                                                    <x-icon name="sparkles" class="w-3 h-3" />
                                                    @term('ai_generated_label')
                                                </span>
                                            @elseif($course->is_template)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                                    @term('template_label')
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                                    @term('staff_created_label')
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ $course->steps_count }} @term('steps_label')
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            @if($course->estimated_duration_minutes)
                                                {{ $course->estimated_duration_minutes }} @term('minutes_label')
                                            @else
                                                <span class="text-gray-400">@term('empty_dash_label')</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1 text-sm text-gray-600">
                                                <x-icon name="users" class="w-4 h-4" />
                                                {{ $course->enrollments_count }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <span class="text-gray-600 mr-4">
                                                @term('preview_label')
                                            </span>
                                            <span
                                                onclick="event.stopPropagation(); window.location.href='{{ route('resources.courses.edit', $course) }}';"
                                                class="text-pulse-orange-600 hover:text-pulse-orange-700 cursor-pointer"
                                            >
                                                @term('edit_label')
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Pagination -->
            <div class="mt-6">
                {{ $courses->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl border border-gray-200 text-center py-16">
                <div class="w-16 h-16 rounded-full bg-orange-100 flex items-center justify-center mx-auto mb-4">
                    <x-icon name="academic-cap" class="w-8 h-8 text-orange-400" />
                </div>
                @if($search || $activeCategory !== 'all')
                    <h3 class="text-lg font-medium text-gray-900 mb-1">@term('no_courses_match_label')</h3>
                    <p class="text-gray-500 mb-4">@term('adjust_search_or_category_label')</p>
                    <button
                        wire:click="$set('search', ''); $set('activeCategory', 'all')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        @term('clear_filters_label')
                    </button>
                @else
                    <h3 class="text-lg font-medium text-gray-900 mb-1">@term('no_courses_yet_label')</h3>
                    <p class="text-gray-500 mb-4">@term('learning_center_empty_help_label')</p>
                    <div class="flex items-center justify-center gap-3">
                        <a
                            href="{{ route('resources.courses.create') }}"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                        >
                            <x-icon name="plus" class="w-4 h-4" />
                            @term('create_course_label')
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
</div>
