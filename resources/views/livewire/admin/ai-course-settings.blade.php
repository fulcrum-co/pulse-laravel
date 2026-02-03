<div class="space-y-6">
    @php($terminology = app(\App\Services\TerminologyService::class))
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">@term('ai_course_settings_label')</h1>
            <p class="text-gray-600 mt-1">@term('ai_course_settings_description')</p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-100">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-2xl font-bold text-yellow-900">{{ $approvalStats['pending'] }}</p>
                    <p class="text-xs text-yellow-600">@term('course_approval_pending_review_label')</p>
                </div>
            </div>
        </div>

        <div class="bg-green-50 rounded-lg p-4 border border-green-100">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-2xl font-bold text-green-900">{{ $approvalStats['approved_this_week'] }}</p>
                    <p class="text-xs text-green-600">@term('approved_this_week_label')</p>
                </div>
            </div>
        </div>

        <div class="bg-red-50 rounded-lg p-4 border border-red-100">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-2xl font-bold text-red-900">{{ $approvalStats['rejected_this_week'] }}</p>
                    <p class="text-xs text-red-600">@term('rejected_this_week_label')</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 rounded-lg p-4 border border-purple-100">
            <div class="flex items-center">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-2xl font-bold text-purple-900">{{ $approvalStats['auto_generated_total'] }}</p>
                    <p class="text-xs text-purple-600">@term('ai_generated_total_label')</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Settings Panel -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">@term('generation_settings_label')</h2>
            </div>
            <div class="p-4 space-y-6">
                <!-- Approval Mode -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">@term('approval_workflow_mode_label')</label>
                    <select
                        wire:model="approvalMode"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    >
                        @foreach($approvalModes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">
                        @if($approvalMode === 'auto_activate')
                        @term('approval_mode_auto_activate_help')
                        @elseif($approvalMode === 'create_approve')
                        @term('approval_mode_create_approve_help')
                        @else
                        @term('approval_mode_manual_help')
                        @endif
                    </p>
                </div>

                <!-- Auto-Generate Toggle -->
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">@term('enable_auto_generation_label')</label>
                        <p class="text-xs text-gray-500">@term('enable_auto_generation_help')</p>
                    </div>
                    <button
                        wire:click="$toggle('autoGenerateEnabled')"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 {{ $autoGenerateEnabled ? 'bg-purple-600' : 'bg-gray-200' }}"
                    >
                        <span class="sr-only">@term('enable_auto_generation_label')</span>
                        <span class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $autoGenerateEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                <!-- Generation Triggers -->
                @if($autoGenerateEnabled)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">@term('generation_triggers_label')</label>
                    <div class="space-y-2">
                        @foreach($triggerOptions as $value => $label)
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                wire:click="toggleTrigger('{{ $value }}')"
                                {{ in_array($value, $generationTriggers) ? 'checked' : '' }}
                                class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                            >
                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Max Courses Per Day -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">@term('max_auto_generated_courses_per_day_label')</label>
                    <input
                        type="number"
                        wire:model="maxAutoCoursesPerDay"
                        min="1"
                        max="100"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                    >
                </div>

                <!-- Notification Recipients -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">@term('notification_recipients_label')</label>
                    <div class="space-y-2">
                        @foreach(['admin' => $terminology->get('administrators_label'), 'support_person' => $terminology->get('support_person_plural'), 'instructor' => $terminology->get('instructor_plural')] as $value => $label)
                        <label class="flex items-center">
                            <input
                                type="checkbox"
                                wire:click="toggleRecipient('{{ $value }}')"
                                {{ in_array($value, $notificationRecipients) ? 'checked' : '' }}
                                class="h-4 w-4 text-purple-600 focus:ring-purple-500 border-gray-300 rounded"
                            >
                            <span class="ml-2 text-sm text-gray-700">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Require Review Toggle -->
                <div class="flex items-center justify-between">
                    <div>
                        <label class="text-sm font-medium text-gray-700">@term('require_review_for_ai_generated_label')</label>
                        <p class="text-xs text-gray-500">@term('require_review_for_ai_generated_help')</p>
                    </div>
                    <button
                        wire:click="$toggle('requireReviewForAiGenerated')"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 {{ $requireReviewForAiGenerated ? 'bg-purple-600' : 'bg-gray-200' }}"
                    >
                        <span class="sr-only">@term('require_review_for_ai_generated_label')</span>
                        <span class="pointer-events-none relative inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $requireReviewForAiGenerated ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                <!-- Save Button -->
                <div class="pt-4 border-t border-gray-200">
                    <button
                        wire:click="saveSettings"
                        class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700"
                    >
                        @term('save_settings_label')
                    </button>
                </div>
            </div>
        </div>

        <!-- Pending Approvals Panel -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">@term('pending_approvals_label')</h2>
            </div>
            <div class="p-4">
                @if($pendingApprovals->count() > 0)
                <div class="space-y-3">
                    @foreach($pendingApprovals as $workflow)
                    <div class="flex items-center justify-between p-3 bg-yellow-50 rounded-lg border border-yellow-100">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $workflow->course->title ?? $terminology->get('untitled_course_label') }}</p>
                            <p class="text-xs text-gray-500 mt-1">
                                @if($workflow->course->target_entity_type === 'participant')
                                    <span class="text-blue-600">@term('for_participant_label')</span>
                                @elseif($workflow->course->target_entity_type === 'instructor')
                                    <span class="text-green-600">@term('for_instructor_label')</span>
                                @else
                                    <span class="text-purple-600">{{ ucfirst($workflow->course->target_entity_type ?? $terminology->get('general_label')) }}</span>
                                @endif
                                - @term('submitted_label') {{ $workflow->submitted_at?->diffForHumans() ?? $terminology->get('recently_label') }}
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <button
                                wire:click="quickApprove({{ $workflow->id }})"
                                class="p-1.5 text-green-600 hover:bg-green-100 rounded"
                                title="@term('quick_approve_label')"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </button>
                            <button
                                wire:click="openApprovalModal({{ $workflow->id }})"
                                class="p-1.5 text-gray-600 hover:bg-gray-100 rounded"
                                title="@term('review_details_label')"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <p class="text-sm text-gray-600">@term('no_pending_approvals_label')</p>
                    <p class="text-xs text-gray-500 mt-1">@term('all_ai_courses_reviewed_label')</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    @if($showApprovalModal && $selectedWorkflowId)
    @php
        $selectedWorkflow = $pendingApprovals->firstWhere('id', $selectedWorkflowId);
    @endphp
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeApprovalModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">@term('review_course_label')</h3>

                    @if($selectedWorkflow && $selectedWorkflow->course)
                    <div class="mb-4">
                        <h4 class="font-medium text-gray-900">{{ $selectedWorkflow->course->title }}</h4>
                        <p class="text-sm text-gray-600 mt-1">{{ $selectedWorkflow->course->description }}</p>

                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ ucfirst(str_replace('_', ' ', $selectedWorkflow->course->course_type ?? $terminology->get('general_label'))) }}
                            </span>
                            @if($selectedWorkflow->course->estimated_duration_minutes)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                {{ $selectedWorkflow->course->estimated_duration_minutes }} @term('minutes_label')
                            </span>
                            @endif
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $selectedWorkflow->course->steps_count ?? $selectedWorkflow->course->steps()->count() }} @term('steps_label')
                            </span>
                        </div>

                        <div class="mt-4">
                            <a
                                href="{{ route('resources.courses.edit', $selectedWorkflow->course) }}"
                                target="_blank"
                                class="text-sm text-purple-600 hover:underline"
                            >
                                @term('open_in_editor_label') &rarr;
                            </a>
                        </div>
                    </div>
                    @endif

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('review_notes_optional_label')</label>
                            <textarea
                                wire:model="reviewNotes"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="@term('review_notes_placeholder')"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('rejection_reason_required_label')</label>
                            <textarea
                                wire:model="rejectionReason"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="@term('rejection_reason_placeholder')"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button
                        wire:click="approveCourse"
                        class="w-full inline-flex justify-center px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 sm:w-auto"
                    >
                        @term('approve_action')
                    </button>
                    <button
                        wire:click="requestRevision"
                        class="mt-2 w-full inline-flex justify-center px-4 py-2 bg-yellow-500 text-white rounded-lg text-sm font-medium hover:bg-yellow-600 sm:mt-0 sm:w-auto"
                    >
                        @term('request_revision_action')
                    </button>
                    <button
                        wire:click="rejectCourse"
                        class="mt-2 w-full inline-flex justify-center px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-medium hover:bg-red-700 sm:mt-0 sm:w-auto"
                    >
                        @term('reject_action')
                    </button>
                    <button
                        wire:click="closeApprovalModal"
                        class="mt-2 w-full inline-flex justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 sm:mt-0 sm:w-auto"
                    >
                        @term('cancel_action')
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
