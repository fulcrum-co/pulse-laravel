<div>
    @php($terminology = app(\App\Services\TerminologyService::class))
    <!-- Tab Navigation -->
    <div class="flex gap-1 mb-6 border-b border-gray-200 overflow-x-auto">
        @foreach([
            'assigned' => $terminology->get('assigned_label'),
            'courses' => $terminology->get('course_plural'),
            'suggestions' => $terminology->get('suggestions_label'),
            'providers' => $terminology->get('provider_plural'),
            'programs' => $terminology->get('program_plural')
        ] as $tab => $label)
        <button
            wire:click="setActiveTab('{{ $tab }}')"
            class="px-4 py-2 text-sm font-medium whitespace-nowrap border-b-2 transition-colors {{ $activeTab === $tab ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
        >
            {{ $label }}
            @if($tab === 'courses' && $enrollments->count() > 0)
            <span class="ml-1 px-1.5 py-0.5 text-xs bg-pulse-orange-100 text-pulse-orange-700 rounded-full">{{ $enrollments->total() }}</span>
            @elseif($tab === 'suggestions' && $courseSuggestions->count() > 0)
            <span class="ml-1 px-1.5 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">{{ $courseSuggestions->count() }}</span>
            @endif
        </button>
        @endforeach
    </div>

    <!-- ==================== ASSIGNED TAB ==================== -->
    @if($activeTab === 'assigned')
    <div>
        <!-- Action Header -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex gap-2 overflow-x-auto pb-2">
                @foreach(['all' => $terminology->get('all_label'), 'pending' => $terminology->get('pending_label'), 'in_progress' => $terminology->get('in_progress_label'), 'completed' => $terminology->get('completed_label')] as $status => $label)
                <button
                    wire:click="setFilterStatus('{{ $status }}')"
                    class="px-3 py-1 text-sm font-medium rounded-lg whitespace-nowrap transition-colors {{ $filterStatus === $status ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                >
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <button
                wire:click="openAssignModal"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                @term('assign_resource_label')
            </button>
        </div>

        <!-- Resource Assignments List -->
        <div class="space-y-3">
            @forelse($assignments as $assignment)
            <div class="border border-gray-200 rounded-lg overflow-hidden {{ $expandedAssignmentId === $assignment->id ? 'ring-2 ring-pulse-orange-200' : '' }}">
                <!-- Header Row (Clickable) -->
                <button
                    wire:click="toggleExpand({{ $assignment->id }})"
                    class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 transition-colors text-left"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center
                            {{ $assignment->status === 'completed' ? 'bg-green-100' : ($assignment->status === 'in_progress' ? 'bg-yellow-100' : 'bg-gray-100') }}">
                            @if($assignment->status === 'completed')
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            @elseif($assignment->status === 'in_progress')
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @else
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            @endif
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $assignment->resource->title ?? $terminology->get('unknown_resource_label') }}</div>
                            <div class="text-xs text-gray-500">
                                @term('assigned_label') {{ $assignment->assigned_at?->format('M d, Y') ?? $terminology->get('unknown_label') }}
                                @if($assignment->assigner)
                                <span class="text-gray-400">@term('by_label') {{ $assignment->assigner->name ?? $assignment->assigner->first_name }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- Progress Bar -->
                        <div class="w-24 hidden sm:block">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-pulse-orange-500 transition-all" style="width: {{ $assignment->progress_percent ?? 0 }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600 w-8">{{ $assignment->progress_percent ?? 0 }}%</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 text-xs rounded-full
                            {{ $assignment->status === 'completed' ? 'bg-green-100 text-green-700' : ($assignment->status === 'in_progress' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                            {{ $terminology->get(($assignment->status ?? 'pending').'_label') }}
                        </span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform {{ $expandedAssignmentId === $assignment->id ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>

                <!-- Expanded Content -->
                @if($expandedAssignmentId === $assignment->id)
                <div class="border-t border-gray-200 p-4 bg-white">
                    <!-- Resource Details -->
                    <div class="mb-4">
                        @if($assignment->resource)
                        <div class="flex items-start gap-4">
                            @if($assignment->resource->thumbnail_url)
                            <img src="{{ $assignment->resource->thumbnail_url }}" alt="" class="w-16 h-16 rounded-lg object-cover">
                            @endif
                            <div class="flex-1">
                                <p class="text-sm text-gray-600 mb-2">{{ $assignment->resource->description ?? $terminology->get('no_description_available_label') }}</p>
                                <div class="flex flex-wrap gap-2 text-xs">
                                @if($assignment->resource->resource_type)
                                    <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded">{{ $terminology->get($assignment->resource->resource_type.'_label') }}</span>
                                    @endif
                                    @if($assignment->resource->category)
                                    <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded">{{ $terminology->get('category_' . $assignment->resource->category . '_label') }}</span>
                                    @endif
                                    @if($assignment->resource->estimated_duration_minutes)
                                    <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded">{{ $assignment->resource->estimated_duration_minutes }} @term('minutes_label')</span>
                                    @endif
                                </div>
                                @if($assignment->resource->url)
                                <a href="{{ $assignment->resource->url }}" target="_blank" class="inline-flex items-center gap-1 mt-2 text-sm text-pulse-orange-600 hover:text-pulse-orange-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                    @term('open_resource_label')
                                </a>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                        <h4 class="text-sm font-medium text-gray-700">@term('progress_status_label')</h4>
                        @if($editingAssignmentId !== $assignment->id)
                        <div class="flex gap-2">
                            <button
                                wire:click="startEdit({{ $assignment->id }})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-pulse-orange-600 hover:bg-pulse-orange-50 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                                @term('update_progress_label')
                            </button>
                            <button
                                wire:click="removeAssignment({{ $assignment->id }})"
                                wire:confirm="@term('remove_resource_assignment_confirm_label')"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                                @term('remove_label')
                            </button>
                        </div>
                        @else
                        <div class="flex gap-2">
                            <button
                                wire:click="saveChanges"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                @term('save_label')
                            </button>
                            <button
                                wire:click="cancelEdit"
                                class="px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                @term('cancel_label')
                            </button>
                        </div>
                        @endif
                    </div>

                    <!-- Edit Form -->
                    @if($editingAssignmentId === $assignment->id)
                    <div class="mt-4 p-4 bg-gray-50 rounded-lg space-y-4">
                        <!-- Progress Slider -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">@term('progress_label')</label>
                            <div class="flex items-center gap-4">
                                <input
                                    type="range"
                                    min="0"
                                    max="100"
                                    step="5"
                                    wire:model.live="editingProgress"
                                    class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                                >
                                <span class="text-sm font-medium text-gray-900 w-12">{{ $editingProgress }}%</span>
                            </div>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">@term('status_label')</label>
                            <div class="flex gap-2">
                                @foreach(['pending' => $terminology->get('pending_label'), 'in_progress' => $terminology->get('in_progress_label'), 'completed' => $terminology->get('completed_label')] as $status => $label)
                                <button
                                    type="button"
                                    wire:click="$set('editingStatus', '{{ $status }}')"
                                    class="px-3 py-1.5 text-sm rounded-lg transition-colors {{ $editingStatus === $status ? 'bg-pulse-orange-500 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:border-gray-400' }}"
                                >
                                    {{ $label }}
                                </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">@term('notes_label')</label>
                            <textarea
                                wire:model="editingNotes"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                                placeholder="@term('progress_notes_placeholder')"
                            ></textarea>
                        </div>
                    </div>
                    @else
                    <!-- View Only -->
                    @if($assignment->notes)
                    <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                        <p class="text-xs font-medium text-gray-500 mb-1">@term('notes_label')</p>
                        <p class="text-sm text-gray-700">{{ $assignment->notes }}</p>
                    </div>
                    @endif

                    <!-- Timestamps -->
                    <div class="mt-4 flex gap-4 text-xs text-gray-500">
                        @if($assignment->started_at)
                        <span>@term('started_label'): {{ $assignment->started_at->format('M d, Y') }}</span>
                        @endif
                        @if($assignment->completed_at)
                        <span>@term('completed_label'): {{ $assignment->completed_at->format('M d, Y') }}</span>
                        @endif
                    </div>
                    @endif
                </div>
                @endif
            </div>
            @empty
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <p>@term('no_resources_assigned_yet_label')</p>
                <button
                    wire:click="openAssignModal"
                    class="inline-flex items-center gap-1 mt-2 text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    @term('assign_resource_label')
                </button>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($assignments->hasPages())
        <div class="mt-4">
            {{ $assignments->links() }}
        </div>
        @endif
    </div>
    @endif

    <!-- ==================== COURSES TAB ==================== -->
    @if($activeTab === 'courses')
    <div>
        <!-- Action Header -->
        <div class="flex items-center justify-between mb-4">
            <div class="flex gap-2 overflow-x-auto pb-2">
                @foreach(['all' => $terminology->get('all_label'), 'enrolled' => $terminology->get('enrolled_label'), 'in_progress' => $terminology->get('in_progress_label'), 'completed' => $terminology->get('completed_label'), 'paused' => $terminology->get('paused_label')] as $status => $label)
                <button
                    wire:click="setEnrollmentFilterStatus('{{ $status }}')"
                    class="px-3 py-1 text-sm font-medium rounded-lg whitespace-nowrap transition-colors {{ $enrollmentFilterStatus === $status ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                >
                    {{ $label }}
                </button>
                @endforeach
            </div>
            <button
                wire:click="openEnrollModal"
                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                @term('enroll_in_course_label')
            </button>
        </div>

        <!-- Course Enrollments List -->
        <div class="space-y-3">
            @forelse($enrollments as $enrollment)
            <div class="border border-gray-200 rounded-lg overflow-hidden {{ $expandedEnrollmentId === $enrollment->id ? 'ring-2 ring-purple-200' : '' }}">
                <!-- Header Row -->
                <button
                    wire:click="toggleEnrollmentExpand({{ $enrollment->id }})"
                    class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 transition-colors text-left"
                >
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center
                            {{ $enrollment->status === 'completed' ? 'bg-green-100' : ($enrollment->status === 'in_progress' ? 'bg-purple-100' : ($enrollment->status === 'paused' ? 'bg-yellow-100' : 'bg-gray-100')) }}">
                            @if($enrollment->status === 'completed')
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            @elseif($enrollment->status === 'in_progress')
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            @elseif($enrollment->status === 'paused')
                            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @else
                            <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            @endif
                        </div>
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $enrollment->miniCourse->title ?? $terminology->get('unknown_course_label') }}</div>
                            <div class="text-xs text-gray-500">
                                @if($enrollment->currentStep)
                                @term('step_label') {{ $enrollment->currentStep->sort_order }} @term('of_label') {{ $enrollment->miniCourse->steps_count ?? $enrollment->miniCourse->steps()->count() }}
                                @else
                                @term('not_started_label')
                                @endif
                                @if($enrollment->started_at)
                                <span class="text-gray-400">@term('started_label') {{ $enrollment->started_at->format('M d') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <!-- Progress Bar -->
                        <div class="w-24 hidden sm:block">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-purple-500 transition-all" style="width: {{ $enrollment->progress_percent ?? 0 }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600 w-8">{{ $enrollment->progress_percent ?? 0 }}%</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 text-xs rounded-full
                            {{ $enrollment->status === 'completed' ? 'bg-green-100 text-green-700' : ($enrollment->status === 'in_progress' ? 'bg-purple-100 text-purple-700' : ($enrollment->status === 'paused' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600')) }}">
                            {{ $terminology->get(($enrollment->status ?? 'enrolled').'_label') }}
                        </span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform {{ $expandedEnrollmentId === $enrollment->id ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </div>
                </button>

                <!-- Expanded Content -->
                @if($expandedEnrollmentId === $enrollment->id)
                <div class="border-t border-gray-200 p-4 bg-white">
                    @if($enrollment->miniCourse)
                    <p class="text-sm text-gray-600 mb-4">{{ Str::limit($enrollment->miniCourse->description, 200) }}</p>

                    <!-- Step Progress -->
                    <div class="mb-4">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">@term('step_progress_label')</h4>
                        <div class="flex gap-1">
                            @foreach($enrollment->miniCourse->steps as $step)
                            @php
                                $stepProgress = $enrollment->stepProgress->firstWhere('step_id', $step->id);
                                $stepStatus = $stepProgress?->status ?? 'not_started';
                            @endphp
                            <div
                                class="flex-1 h-2 rounded {{ $stepStatus === 'completed' ? 'bg-green-500' : ($stepStatus === 'in_progress' ? 'bg-purple-500' : ($stepStatus === 'skipped' ? 'bg-gray-300' : 'bg-gray-200')) }}"
                                title="{{ $terminology->get('step_label') }} {{ $step->sort_order }}: {{ $step->title }}"
                            ></div>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Actions -->
                    <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                        <a
                            href="{{ route('resources.courses.show', $enrollment->miniCourse) }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors"
                        >
                            @if($enrollment->status === 'in_progress')
                            @term('continue_course_label')
                            @else
                            @term('view_course_label')
                            @endif
                        </a>
                        <div class="flex gap-2">
                            @if($enrollment->status === 'paused')
                            <button
                                wire:click="resumeEnrollment({{ $enrollment->id }})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                @term('resume_label')
                            </button>
                            @elseif(in_array($enrollment->status, ['enrolled', 'in_progress']))
                            <button
                                wire:click="pauseEnrollment({{ $enrollment->id }})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-yellow-600 hover:bg-yellow-50 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                @term('pause_label')
                            </button>
                            @endif
                            @if($enrollment->status !== 'withdrawn' && $enrollment->status !== 'completed')
                            <button
                                wire:click="withdrawEnrollment({{ $enrollment->id }})"
                                wire:confirm="@term('withdraw_participant_confirm_label')"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                @term('withdraw_label')
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <p>@term('not_enrolled_courses_yet_label')</p>
                <button
                    wire:click="openEnrollModal"
                    class="inline-flex items-center gap-1 mt-2 text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    @term('enroll_in_course_label')
                </button>
            </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($enrollments->hasPages())
        <div class="mt-4">
            {{ $enrollments->links() }}
        </div>
        @endif
    </div>
    @endif

    <!-- ==================== SUGGESTIONS TAB ==================== -->
    @if($activeTab === 'suggestions')
    <div>
        <div class="mb-4">
            <p class="text-sm text-gray-600">@term('ai_course_suggestions_help_label')</p>
        </div>

        <div class="space-y-3">
            @forelse($courseSuggestions as $suggestion)
            <div class="border border-blue-200 rounded-lg overflow-hidden bg-blue-50/50">
                <div class="p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                </svg>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h4 class="text-sm font-medium text-gray-900">{{ $suggestion->miniCourse->title ?? $terminology->get('course_suggestion_label') }}</h4>
                                    <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">{{ $suggestion->relevance_score }}% @term('match_label')</span>
                                </div>
                                @if($suggestion->ai_rationale)
                    <p class="text-sm text-gray-600 mt-1">{{ $suggestion->ai_rationale }}</p>
                                @endif
                                @if($suggestion->trigger_signals)
                                <div class="mt-2 flex flex-wrap gap-1">
                                @foreach(array_slice($suggestion->trigger_signals ?? [], 0, 3) as $signal)
                                    <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $signal }}</span>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="flex gap-2 flex-shrink-0">
                            <button
                                wire:click="acceptSuggestion({{ $suggestion->id }})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                @term('accept_label')
                            </button>
                            <button
                                wire:click="declineSuggestion({{ $suggestion->id }})"
                                class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                @term('decline_label')
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
                <p>@term('no_pending_suggestions_label')</p>
                <p class="text-xs mt-1">@term('suggestions_help_label')</p>
            </div>
            @endforelse
        </div>
    </div>
    @endif

    <!-- ==================== PROVIDERS TAB ==================== -->
    @if($activeTab === 'providers')
    <div>
        <div class="mb-4">
            <p class="text-sm text-gray-600">@term('provider_recommendations_help_label')</p>
        </div>

        <div class="space-y-3">
            @forelse($providerRecommendations as $recommendation)
            <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                <div class="p-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0 text-purple-700 font-bold">
                            {{ substr($recommendation['provider']->name ?? '?', 0, 1) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-medium text-gray-900">{{ $recommendation['provider']->name }}</h4>
                                @if($recommendation['provider']->verified_at)
                                <svg class="w-4 h-4 text-blue-500" fill="currentColor" viewBox="0 0 20 20" title="@term('verified_label')">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                @endif
                                <span class="px-2 py-0.5 text-xs bg-purple-100 text-purple-700 rounded-full">{{ $recommendation['score'] }}% @term('match_label')</span>
                            </div>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $terminology->get(($recommendation['provider']->provider_type ?? 'provider').'_label') }}</p>
                            @if($recommendation['recommendation_reason'])
                            <p class="text-sm text-gray-600 mt-2">{{ $recommendation['recommendation_reason'] }}</p>
                            @endif
                            @if(!empty($recommendation['matching_factors']))
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach(array_slice($recommendation['matching_factors'], 0, 3) as $factor)
                                <span class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded">{{ $factor }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <a
                            href="{{ route('resources.providers.show', $recommendation['provider']) }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-purple-600 hover:bg-purple-50 rounded-lg transition-colors flex-shrink-0"
                        >
                            @term('view_profile_label')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
                <p>@term('no_provider_recommendations_label')</p>
                <a href="{{ route('resources.index') }}?activeTab=providers" class="inline-flex items-center gap-1 mt-2 text-sm text-pulse-orange-600 hover:text-pulse-orange-700">
                    @term('browse_all_providers_label')
                </a>
            </div>
            @endforelse
        </div>
    </div>
    @endif

    <!-- ==================== PROGRAMS TAB ==================== -->
    @if($activeTab === 'programs')
    <div>
        <div class="mb-4">
            <p class="text-sm text-gray-600">@term('program_recommendations_help_label')</p>
        </div>

        <div class="space-y-3">
            @forelse($programRecommendations as $recommendation)
            <div class="border border-gray-200 rounded-lg overflow-hidden bg-white">
                <div class="p-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h4 class="text-sm font-medium text-gray-900">{{ $recommendation['program']->name }}</h4>
                                <span class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full">{{ $recommendation['score'] }}% @term('match_label')</span>
                                @if($recommendation['program']->cost_structure === 'free')
                                <span class="px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">@term('free_label')</span>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                {{ $terminology->get(($recommendation['program']->program_type ?? 'program').'_label') }}
                                @if($recommendation['program']->provider_org_name)
                                @term('by_label') {{ $recommendation['program']->provider_org_name }}
                                @endif
                            </p>
                            @if($recommendation['recommendation_reason'])
                            <p class="text-sm text-gray-600 mt-2">{{ $recommendation['recommendation_reason'] }}</p>
                            @endif
                            @if(!empty($recommendation['matching_factors']))
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach(array_slice($recommendation['matching_factors'], 0, 3) as $factor)
                                <span class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded">{{ $factor }}</span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        <a
                            href="{{ route('resources.programs.show', $recommendation['program']) }}"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-green-600 hover:bg-green-50 rounded-lg transition-colors flex-shrink-0"
                        >
                            @term('view_details_label')
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                <p>@term('no_program_recommendations_label')</p>
                <a href="{{ route('resources.index') }}?activeTab=programs" class="inline-flex items-center gap-1 mt-2 text-sm text-pulse-orange-600 hover:text-pulse-orange-700">
                    @term('browse_all_programs_label')
                </a>
            </div>
            @endforelse
        </div>
    </div>
    @endif

    <!-- ==================== ASSIGN RESOURCE MODAL ==================== -->
    @if($showAssignModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeAssignModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">@term('assign_resource_label')</h3>

                    <!-- Search -->
                    <div class="mb-4">
                        <input
                            type="text"
                            wire:model.live="searchResources"
                            placeholder="@term('search_resources_placeholder')"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                        >
                    </div>

                    <!-- Resource List -->
                    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-200">
                        @forelse($availableResources as $resource)
                        <button
                            wire:click="$set('selectedResourceId', {{ $resource->id }})"
                            class="w-full flex items-center gap-3 p-3 text-left hover:bg-gray-50 transition-colors {{ $selectedResourceId === $resource->id ? 'bg-pulse-orange-50 border-l-4 border-pulse-orange-500' : '' }}"
                        >
                            <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                @if($resource->thumbnail_url)
                                <img src="{{ $resource->thumbnail_url }}" alt="" class="w-full h-full rounded object-cover">
                                @else
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $resource->title }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Str::limit($resource->description, 50) }}</p>
                            </div>
                            @if($selectedResourceId === $resource->id)
                            <svg class="w-5 h-5 text-pulse-orange-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            @endif
                        </button>
                        @empty
                        <div class="p-4 text-center text-gray-500">
                            <p class="text-sm">@term('no_resources_available_label')</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Notes -->
                    @if($selectedResourceId)
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('assignment_notes_optional_label')</label>
                        <textarea
                            wire:model="assignmentNotes"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                            placeholder="@term('assignment_notes_placeholder')"
                        ></textarea>
                    </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="mt-5 sm:mt-6 flex gap-3">
                    <button
                        wire:click="assignResource"
                        @if(!$selectedResourceId) disabled @endif
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        @term('assign_resource_label')
                    </button>
                    <button
                        wire:click="closeAssignModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        @term('cancel_label')
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ==================== ENROLL IN COURSE MODAL ==================== -->
    @if($showEnrollModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeEnrollModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">@term('enroll_in_course_label')</h3>

                    <!-- Search -->
                    <div class="mb-4">
                        <input
                            type="text"
                            wire:model.live="searchCourses"
                            placeholder="@term('search_courses_placeholder')"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                        >
                    </div>

                    <!-- Course List -->
                    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-200">
                        @forelse($availableCourses as $course)
                        <button
                            wire:click="$set('selectedCourseId', {{ $course->id }})"
                            class="w-full flex items-center gap-3 p-3 text-left hover:bg-gray-50 transition-colors {{ $selectedCourseId === $course->id ? 'bg-purple-50 border-l-4 border-purple-500' : '' }}"
                        >
                            <div class="w-10 h-10 bg-purple-100 rounded flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $course->title }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $course->steps_count ?? $course->steps()->count() }} @term('steps_label')
                                    @if($course->estimated_duration_minutes)
                                    / {{ $course->estimated_duration_minutes }} @term('minutes_label')
                                    @endif
                                </p>
                            </div>
                            @if($selectedCourseId === $course->id)
                            <svg class="w-5 h-5 text-purple-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            @endif
                        </button>
                        @empty
                        <div class="p-4 text-center text-gray-500">
                            <p class="text-sm">@term('no_courses_available_label')</p>
                        </div>
                        @endforelse
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-5 sm:mt-6 flex gap-3">
                    <button
                        wire:click="enrollInCourse"
                        @if(!$selectedCourseId) disabled @endif
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-purple-500 rounded-lg hover:bg-purple-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        @term('enroll_participant_label')
                    </button>
                    <button
                        wire:click="closeEnrollModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        @term('cancel_label')
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
