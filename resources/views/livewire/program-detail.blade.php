<div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
    @php($terminology = app(\App\Services\TerminologyService::class))
    <!-- Breadcrumbs -->
    <x-breadcrumbs :items="[
        ['label' => app(\App\Services\TerminologyService::class)->get('resource_plural'), 'url' => route('resources.index')],
        ['label' => app(\App\Services\TerminologyService::class)->get('program_plural'), 'url' => route('resources.index') . '?activeTab=programs'],
        ['label' => $program->name],
    ]" />

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <!-- Header -->
        <div class="bg-gradient-to-br from-green-500 to-green-600 px-6 py-8">
            <div class="flex items-start justify-between">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-3 py-1 bg-white/20 text-white text-sm rounded-full">
                            {{ $terminology->get('program_type_'.$program->program_type.'_label') }}
                        </span>
                        @if($program->cost_structure === 'free')
                        <span class="px-3 py-1 bg-green-400/30 text-white text-sm rounded-full">@term('free_label')</span>
                        @endif
                    </div>
                    <h1 class="text-2xl font-bold text-white mb-2">{{ $program->name }}</h1>
                    @if($program->provider_org_name)
                    <p class="text-green-100">@term('by_label') {{ $program->provider_org_name }}</p>
                    @endif
                </div>
                <div class="text-right text-white">
                    @if($program->duration_weeks)
                    <div class="text-2xl font-bold">{{ $program->duration_weeks }}</div>
                    <div class="text-green-200 text-sm">@term('weeks_label')</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Content -->
        <div class="p-6 space-y-6">
            <!-- Description -->
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">@term('about_program_label')</h2>
                <p class="text-gray-600">{{ $program->description }}</p>
            </div>

            <!-- Key Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Location Type -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-gray-500 text-sm mb-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        @term('location_label')
                    </div>
                    <div class="font-medium text-gray-900">{{ ucfirst(str_replace('_', '-', $program->location_type)) }}</div>
                </div>

                <!-- Cost -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-gray-500 text-sm mb-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        @term('cost_label')
                    </div>
                    <div class="font-medium text-gray-900">
                        @switch($program->cost_structure)
                            @case('free')
                                <span class="text-green-600">@term('free_label')</span>
                                @break
                            @case('sliding_scale')
                                @term('sliding_scale_label')
                                @break
                            @case('insurance')
                                @term('insurance_accepted_label')
                                @break
                            @default
                                @term('paid_program_label')
                        @endswitch
                    </div>
                </div>

                <!-- Duration -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center gap-2 text-gray-500 text-sm mb-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        @term('duration_label')
                    </div>
                    <div class="font-medium text-gray-900">
                        @if($program->duration_weeks)
                            {{ $program->duration_weeks }} @term('weeks_label')
                        @else
                            @term('ongoing_label')
                        @endif
                    </div>
                </div>
            </div>

            <!-- Target Needs -->
            @if($program->target_needs && count($program->target_needs) > 0)
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">@term('areas_of_focus_label')</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach($program->target_needs as $need)
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">{{ $need }}</span>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Eligibility -->
            @if($program->eligibility_criteria && count($program->eligibility_criteria) > 0)
            <div>
                <h2 class="text-lg font-semibold text-gray-900 mb-2">@term('eligibility_requirements_label')</h2>
                <ul class="list-disc list-inside text-gray-600 space-y-1">
                    @foreach($program->eligibility_criteria as $criterion)
                    <li>{{ $criterion }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <!-- Actions -->
            <div class="border-t border-gray-200 pt-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('actions_label')</h2>
                <div class="flex flex-wrap gap-4">
                    <!-- Push to Organizations -->
                    @if($canPush)
                    <button
                        wire:click="openPushModal"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        title="@term('push_to_organizations_label')"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                        </svg>
                        @term('push_to_organizations_label')
                    </button>
                    @endif

                    <!-- Enroll Participant -->
                    <button
                        wire:click="openEnrollModal"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        title="@term('enroll_participant_label')"
                    >
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                        </svg>
                        @term('enroll_participant_label')
                        @if($enrollmentCount > 0)
                        <span class="ml-2 px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded-full">{{ $enrollmentCount }}</span>
                        @endif
                    </button>

                    <!-- Enrollment Link -->
                    @if($program->enrollment_url)
                    <a href="{{ $program->enrollment_url }}" target="_blank" class="inline-flex items-center px-6 py-3 bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors font-medium shadow-sm">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                        </svg>
                        @term('learn_more_enroll_label')
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Enroll Modal -->
    @if($showEnrollModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeEnrollModal"></div>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                @term('enroll_in_program_label')
                            </h3>
                            <p class="text-sm text-gray-500 mt-1">@term('enroll_participant_or_list_label') {{ $program->name }}.</p>

                            <div class="mt-4 space-y-4">
                                <!-- Enrollment Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">@term('enroll_label')</label>
                                    <div class="flex gap-4">
                                        <label class="flex items-center">
                                            <input type="radio" wire:model.live="enrollType" value="participant" class="mr-2">
                                            @term('individual_participant_label')
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" wire:model.live="enrollType" value="list" class="mr-2">
                                            @term('contact_list_label')
                                        </label>
                                    </div>
                                </div>

                                <!-- Participant Select -->
                                @if($enrollType === 'participant')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">@term('select_participant_label')</label>
                                    <select wire:model="selectedLearnerId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                        <option value="">@term('choose_participant_placeholder')</option>
                                        @foreach($participants as $participant)
                                        <option value="{{ $participant->id }}">{{ $participant->user?->name ?? $terminology->get('participant_label').' #'.$participant->id }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <!-- List Select -->
                                @if($enrollType === 'list')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">@term('select_contact_list_label')</label>
                                    <select wire:model="selectedListId" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                                        <option value="">@term('choose_list_placeholder')</option>
                                        @foreach($contactLists as $list)
                                        <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->learners_count ?? $list->participants->count() }} {{ $terminology->get('participants_label') }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif

                                <!-- Note -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">@term('note_optional_label')</label>
                                    <textarea wire:model="enrollNote" rows="2" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500" placeholder="@term('enrollment_note_placeholder')"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button
                        wire:click="enrollLearner"
                        type="button"
                        class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        @term('enroll_participant_label')
                    </button>
                    <button
                        wire:click="closeEnrollModal"
                        type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
                    >
                        @term('cancel_action')
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
