@php
    $terminology = app(\App\Services\TerminologyService::class);
    $enrollmentStatusLabels = [
        'active' => $terminology->get('enrollment_status_active_label'),
        'completed' => $terminology->get('enrollment_status_completed_label'),
        'paused' => $terminology->get('enrollment_status_paused_label'),
    ];
    $approvalStatusLabels = [
        'approved' => $terminology->get('course_approval_approved_label'),
        'pending_review' => $terminology->get('course_approval_pending_review_label'),
        'rejected' => $terminology->get('course_approval_rejected_label'),
        'revision_requested' => $terminology->get('course_approval_revision_requested_label'),
        'draft' => $terminology->get('draft_label'),
    ];
@endphp

<div class="bg-white rounded-lg shadow-sm border border-gray-200">
    <div class="px-4 py-3 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                <svg class="w-4 h-4 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                @term('ai_course_suggestions_label')
            </h3>
            <button
                wire:click="generateCourse"
                wire:loading.attr="disabled"
                wire:target="generateCourse"
                class="inline-flex items-center px-3 py-1 text-xs font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="generateCourse">
                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    @term('generate_course_label')
                </span>
                <span wire:loading wire:target="generateCourse" class="flex items-center">
                    <svg class="animate-spin mr-1 h-3 w-3" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    @term('generating_label')
                </span>
            </button>
        </div>
    </div>

    <div class="p-4 space-y-4">
        <!-- Error Display -->
        @if($error)
        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
            <p class="text-sm text-red-700">{{ $error }}</p>
        </div>
        @endif

        <!-- Active Enrollments -->
        @if($activeEnrollments->count() > 0)
        <div>
            <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">@term('active_courses_label')</h4>
            <div class="space-y-2">
                @foreach($activeEnrollments as $enrollment)
                <div class="flex items-center justify-between p-2 bg-green-50 rounded-lg border border-green-100">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $enrollment->course->title ?? $terminology->get('course_singular') }}</p>
                            <p class="text-xs text-gray-500">{{ $enrollmentStatusLabels[$enrollment->status] ?? ucfirst($enrollment->status) }} - {{ $enrollment->progress_percentage ?? 0 }}% @term('complete_label')</p>
                        </div>
                    </div>
                    @if($enrollment->course)
                    <a href="{{ route('resources.courses.show', $enrollment->course) }}" class="text-xs text-green-600 hover:underline">
                        @term('view_action')
                    </a>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Pending Suggestions -->
        @if($suggestions->count() > 0)
        <div>
            <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">@term('pending_suggestions_label')</h4>
            <div class="space-y-2">
                @foreach($suggestions as $suggestion)
                <div class="p-3 bg-yellow-50 rounded-lg border border-yellow-100">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $suggestion->miniCourse->title ?? $terminology->get('suggested_course_label') }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $suggestion->reason ?? $terminology->get('ai_recommended_reason_label') }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2 mt-2">
                        <button
                            wire:click="acceptSuggestion({{ $suggestion->id }})"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-700 bg-green-100 rounded hover:bg-green-200"
                        >
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            @term('accept_action')
                        </button>
                        <button
                            wire:click="declineSuggestion({{ $suggestion->id }})"
                            class="inline-flex items-center px-2 py-1 text-xs font-medium text-red-700 bg-red-100 rounded hover:bg-red-200"
                        >
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            @term('decline_action')
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- AI-Generated Courses -->
        @if($existingCourses->count() > 0)
        <div>
            <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">@term('ai_generated_courses_label')</h4>
            <div class="space-y-2">
                @foreach($existingCourses as $course)
                <div class="flex items-center justify-between p-2 bg-gray-50 rounded-lg border border-gray-100">
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center mr-3 {{ $course->approval_status === 'approved' ? 'bg-green-100' : ($course->approval_status === 'pending_review' ? 'bg-yellow-100' : 'bg-gray-100') }}">
                            @if($course->approval_status === 'approved')
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            @elseif($course->approval_status === 'pending_review')
                            <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            @else
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $course->title }}</p>
                            <p class="text-xs text-gray-500">
                                <span class="text-gray-500">{{ $approvalStatusLabels[$course->approval_status] ?? ucfirst($course->approval_status) }}</span>
                                - {{ $course->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('resources.courses.edit', $course) }}" class="text-xs text-purple-600 hover:underline">
                        @term('edit_action')
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Empty State -->
        @if($existingCourses->count() === 0 && $suggestions->count() === 0 && $activeEnrollments->count() === 0)
        <div class="text-center py-6">
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                </svg>
            </div>
            <p class="text-sm text-gray-600 mb-2">@term('no_ai_courses_yet_label')</p>
            <p class="text-xs text-gray-500">@term('generate_course_empty_help_label')</p>
        </div>
        @endif
    </div>
</div>
