<div class="max-w-4xl mx-auto">
    <!-- Step Indicator -->
    <nav class="mb-8">
        <ol class="flex items-center justify-between">
            @foreach($this->steps as $step => $info)
                <li class="flex items-center {{ $step < count($this->steps) ? 'flex-1' : '' }}">
                    <button
                        wire:click="goToStep({{ $step }})"
                        class="flex flex-col items-center group"
                    >
                        <span class="flex items-center justify-center w-10 h-10 rounded-full border-2 transition-colors
                            {{ $currentStep === $step ? 'bg-pulse-orange-500 border-pulse-orange-500 text-white' :
                               ($currentStep > $step ? 'bg-green-500 border-green-500 text-white' : 'bg-white border-gray-300 text-gray-500') }}">
                            @if($currentStep > $step)
                                <x-icon name="check" class="w-5 h-5" />
                            @else
                                <x-icon name="{{ $info['icon'] }}" class="w-5 h-5" />
                            @endif
                        </span>
                        <span class="mt-2 text-xs font-medium {{ $currentStep === $step ? 'text-pulse-orange-600' : 'text-gray-500' }}">
                            {{ $info['label'] }}
                        </span>
                    </button>
                    @if($step < count($this->steps))
                        <div class="flex-1 h-0.5 mx-2 {{ $currentStep > $step ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>

    <!-- Step Content -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        {{-- Step 1: Basic Info --}}
        @if($currentStep === 1)
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-1">@term('basic_information_label')</h2>
                <p class="text-gray-500 text-sm mb-6">@term('give_collection_name_label')</p>

                <div class="space-y-6">
                    <!-- Title -->
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">@term('title_label')</label>
                        <input
                            type="text"
                            id="title"
                            wire:model="title"
                            placeholder="{{ app(\App\Services\TerminologyService::class)->get('survey_title_example_label') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        />
                        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">@term('description_optional_label')</label>
                        <textarea
                            id="description"
                            wire:model="description"
                            rows="3"
                            placeholder="{{ app(\App\Services\TerminologyService::class)->get('what_collection_for_label') }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        ></textarea>
                    </div>

                    <!-- Collection Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">@term('collection_type_label')</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($this->collectionTypes as $type => $info)
                                <button
                                    type="button"
                                    wire:click="$set('collectionType', '{{ $type }}')"
                                    class="p-4 rounded-lg border-2 text-left transition-all
                                        {{ $collectionType === $type ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                                >
                                    <div class="flex items-center gap-3 mb-2">
                                        <div class="w-10 h-10 rounded-lg {{ $collectionType === $type ? 'bg-pulse-orange-100' : 'bg-gray-100' }} flex items-center justify-center">
                                            <x-icon name="{{ $info['icon'] }}" class="w-5 h-5 {{ $collectionType === $type ? 'text-pulse-orange-600' : 'text-gray-500' }}" />
                                        </div>
                                        <span class="font-medium {{ $collectionType === $type ? 'text-pulse-orange-600' : 'text-gray-900' }}">{{ $info['label'] }}</span>
                                    </div>
                                    <p class="text-sm text-gray-500">{{ $info['description'] }}</p>
                                </button>
                            @endforeach
                        </div>
                        @error('collectionType') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

        {{-- Step 2: Data Source --}}
        @elseif($currentStep === 2)
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-1">@term('data_source_label')</h2>
                <p class="text-gray-500 text-sm mb-6">@term('data_source_body')</p>

                <div class="space-y-6">
                    <!-- Data Source Type -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        @foreach($this->dataSources as $source => $info)
                            <button
                                type="button"
                                wire:click="$set('dataSource', '{{ $source }}')"
                                class="p-4 rounded-lg border-2 text-left transition-all
                                    {{ $dataSource === $source ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                            >
                                <div class="flex items-center gap-3 mb-2">
                                    <x-icon name="{{ $info['icon'] }}" class="w-5 h-5 {{ $dataSource === $source ? 'text-pulse-orange-600' : 'text-gray-500' }}" />
                                    <span class="font-medium {{ $dataSource === $source ? 'text-pulse-orange-600' : 'text-gray-900' }}">{{ $info['label'] }}</span>
                                </div>
                                <p class="text-sm text-gray-500">{{ $info['description'] }}</p>
                            </button>
                        @endforeach
                    </div>

                    <!-- Survey Selection (if survey or hybrid) -->
                    @if(in_array($dataSource, ['survey', 'hybrid']))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">@term('select_survey_label')</label>
                            @if(count($availableSurveys) > 0)
                                <div class="space-y-2 max-h-64 overflow-y-auto border border-gray-200 rounded-lg p-2">
                                    @foreach($availableSurveys as $survey)
                                        <button
                                            type="button"
                                            wire:click="$set('surveyId', {{ $survey['id'] }})"
                                            class="w-full p-3 rounded-lg text-left transition-all
                                                {{ $surveyId === $survey['id'] ? 'bg-pulse-orange-50 border border-pulse-orange-300' : 'bg-gray-50 hover:bg-gray-100' }}"
                                        >
                                            <div class="font-medium text-gray-900">{{ $survey['title'] }}</div>
                                            <div class="text-sm text-gray-500 mt-1">
                                                {{ count($survey['questions'] ?? []) }} @term('questions_label')
                                                <span class="mx-1">&middot;</span>
                                                {{ ucfirst($survey['survey_type']) }}
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 bg-gray-50 rounded-lg">
                                    <x-icon name="clipboard-document-list" class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                                    <p class="text-gray-500">@term('no_active_surveys_label')</p>
                                    <a href="{{ route('surveys.create') }}" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700">@term('create_survey_first_label')</a>
                                </div>
                            @endif
                            @error('surveyId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <!-- Inline Questions (if inline or hybrid) -->
                    @if(in_array($dataSource, ['inline', 'hybrid']))
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-sm font-medium text-gray-700">
                                    {{ $dataSource === 'hybrid' ? app(\App\Services\TerminologyService::class)->get('additional_questions_label') : app(\App\Services\TerminologyService::class)->get('questions_label') }}
                                </label>
                                <button
                                    type="button"
                                    wire:click="openQuestionEditor"
                                    class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700 font-medium"
                                >
                                    + @term('add_question_label')
                                </button>
                            </div>

                            @if(count($inlineQuestions) > 0)
                                <div class="space-y-2">
                                    @foreach($inlineQuestions as $index => $question)
                                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                            <span class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs font-medium text-gray-600">
                                                {{ $index + 1 }}
                                            </span>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-gray-900 truncate">{{ $question['question'] }}</p>
                                                <p class="text-xs text-gray-500">{{ $this->questionTypes[$question['type']]['label'] ?? $question['type'] }}</p>
                                            </div>
                                            <button wire:click="openQuestionEditor({{ $index }})" class="p-1 text-gray-400 hover:text-gray-600">
                                                <x-icon name="pencil" class="w-4 h-4" />
                                            </button>
                                            <button wire:click="removeQuestion({{ $index }})" class="p-1 text-gray-400 hover:text-red-500">
                                                <x-icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
                                    <x-icon name="plus-circle" class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                                    <p class="text-gray-500">@term('no_questions_added_label')</p>
                                    <button wire:click="openQuestionEditor" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700 mt-1">@term('add_first_question_label')</button>
                                </div>
                            @endif
                            @error('inlineQuestions') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>
            </div>

        {{-- Step 3: Format Mode --}}
        @elseif($currentStep === 3)
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-1">@term('format_mode_label')</h2>
                <p class="text-gray-500 text-sm mb-6">@term('format_collect_body')</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($this->formatModes as $mode => $info)
                        <button
                            type="button"
                            wire:click="$set('formatMode', '{{ $mode }}')"
                            class="p-6 rounded-lg border-2 text-left transition-all
                                {{ $formatMode === $mode ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                        >
                            <div class="w-12 h-12 rounded-xl {{ $formatMode === $mode ? 'bg-pulse-orange-100' : 'bg-gray-100' }} flex items-center justify-center mb-4">
                                <x-icon name="{{ $info['icon'] }}" class="w-6 h-6 {{ $formatMode === $mode ? 'text-pulse-orange-600' : 'text-gray-500' }}" />
                            </div>
                            <h3 class="font-semibold {{ $formatMode === $mode ? 'text-pulse-orange-600' : 'text-gray-900' }}">{{ $info['label'] }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $info['description'] }}</p>
                        </button>
                    @endforeach
                </div>
            </div>

        {{-- Step 4: Schedule Configuration --}}
        @elseif($currentStep === 4)
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-1">@term('schedule_configuration_label')</h2>
                <p class="text-gray-500 text-sm mb-6">
                    @if($collectionType === 'one_time')
                        @term('schedule_one_time_label')
                    @else
                        @term('schedule_configure_label')
                    @endif
                </p>

                @if($collectionType !== 'one_time')
                    <!-- Schedule Type -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">@term('schedule_type_label')</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @foreach($this->scheduleTypes as $type => $info)
                                <button
                                    type="button"
                                    wire:click="$set('scheduleType', '{{ $type }}')"
                                    class="p-4 rounded-lg border-2 text-left transition-all
                                        {{ $scheduleType === $type ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                                >
                                    <span class="font-medium {{ $scheduleType === $type ? 'text-pulse-orange-600' : 'text-gray-900' }}">{{ $info['label'] }}</span>
                                    <p class="text-sm text-gray-500 mt-1">{{ $info['description'] }}</p>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Interval Configuration -->
                    @if($scheduleType === 'interval')
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@term('every_label')</label>
                                <input
                                    type="number"
                                    wire:model="intervalValue"
                                    min="1"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">@term('period_label')</label>
                                <select
                                    wire:model="intervalType"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                >
                                    <option value="daily">@term('day_plural_label')</option>
                                    <option value="weekly">@term('week_plural_label')</option>
                                    <option value="monthly">@term('month_plural_label')</option>
                                </select>
                            </div>
                        </div>
                    @endif

                    <!-- Custom Days -->
                    @if($scheduleType === 'custom')
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">@term('select_days_label')</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($this->daysOfWeek as $day)
                                    <button
                                        type="button"
                                        wire:click="toggleDay('{{ $day }}')"
                                        class="px-4 py-2 rounded-lg border transition-all
                                            {{ in_array($day, $customDays) ? 'bg-pulse-orange-500 border-pulse-orange-500 text-white' : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400' }}"
                                    >
                                        {{ ucfirst(substr($day, 0, 3)) }}
                                    </button>
                                @endforeach
                            </div>
                            @error('customDays') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <!-- Event Trigger -->
                    @if($scheduleType === 'event')
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('trigger_event_label')</label>
                            <select
                                wire:model="eventTrigger"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            >
                                <option value="">@term('select_event_label')</option>
                                <option value="survey_completed">@term('survey_completed_label')</option>
                                <option value="metric_threshold">@term('metric_threshold_label')</option>
                                <option value="flag_raised">@term('flag_raised_label')</option>
                            </select>
                        </div>
                    @endif

                    <!-- Times -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">@term('collection_times_label')</label>
                            <button type="button" wire:click="addTime" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700">+ @term('add_time_label')</button>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            @foreach($customTimes as $index => $time)
                                <div class="flex items-center gap-2">
                                    <input
                                        type="time"
                                        wire:model="customTimes.{{ $index }}"
                                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                    />
                                    @if(count($customTimes) > 1)
                                        <button type="button" wire:click="removeTime({{ $index }})" class="p-1 text-gray-400 hover:text-red-500">
                                            <x-icon name="x-mark" class="w-4 h-4" />
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('start_date_label')</label>
                        <input
                            type="date"
                            wire:model="startDate"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        />
                        @error('startDate') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    @if($collectionType !== 'one_time')
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('end_date_optional_label')</label>
                            <input
                                type="date"
                                wire:model="endDate"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            />
                        </div>
                    @endif
                </div>
            </div>

        {{-- Step 5: Contact Scope --}}
        @elseif($currentStep === 5)
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-1">@term('contact_scope_label')</h2>
                <p class="text-gray-500 text-sm mb-6">@term('contact_scope_body')</p>

                <!-- Target Type -->
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-3">@term('target_type_label')</label>
                    <div class="flex gap-4">
                        <button
                            type="button"
                            wire:click="$set('targetType', 'learners')"
                            class="flex-1 p-4 rounded-lg border-2 text-center transition-all
                                {{ $targetType === 'learners' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                        >
                            <x-icon name="academic-cap" class="w-8 h-8 mx-auto mb-2 {{ $targetType === 'learners' ? 'text-pulse-orange-600' : 'text-gray-400' }}" />
                            <span class="font-medium {{ $targetType === 'learners' ? 'text-pulse-orange-600' : 'text-gray-900' }}">@term('learner_plural')</span>
                        </button>
                        <button
                            type="button"
                            wire:click="$set('targetType', 'users')"
                            class="flex-1 p-4 rounded-lg border-2 text-center transition-all
                                {{ $targetType === 'users' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                        >
                            <x-icon name="users" class="w-8 h-8 mx-auto mb-2 {{ $targetType === 'users' ? 'text-pulse-orange-600' : 'text-gray-400' }}" />
                            <span class="font-medium {{ $targetType === 'users' ? 'text-pulse-orange-600' : 'text-gray-900' }}">@term('staff_parents_label')</span>
                        </button>
                    </div>
                </div>

                @if($targetType === 'learners')
                    <!-- Grades Filter -->
                    @if(count($availableGrades) > 0)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">@term('filter_by_grade_label')</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($availableGrades as $grade)
                                    <button
                                        type="button"
                                        wire:click="toggleGrade('{{ $grade }}')"
                                        class="px-3 py-1.5 rounded-lg text-sm border transition-all
                                            {{ in_array($grade, $selectedGrades) ? 'bg-pulse-orange-500 border-pulse-orange-500 text-white' : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400' }}"
                                    >
                                        {{ $grade }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Classrooms Filter -->
                    @if(count($availableClassrooms) > 0)
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">@term('filter_by_classroom_label')</label>
                            <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg p-2 space-y-1">
                                @foreach($availableClassrooms as $classroom)
                                    <button
                                        type="button"
                                        wire:click="toggleClassroom({{ $classroom['id'] }})"
                                        class="w-full px-3 py-2 rounded-lg text-left text-sm transition-all
                                            {{ in_array($classroom['id'], $selectedClassrooms) ? 'bg-pulse-orange-50 text-pulse-orange-700' : 'hover:bg-gray-50' }}"
                                    >
                                        <div class="flex items-center justify-between">
                                            <span>{{ $classroom['name'] }}</span>
                                            @if(in_array($classroom['id'], $selectedClassrooms))
                                                <x-icon name="check" class="w-4 h-4 text-pulse-orange-600" />
                                            @endif
                                        </div>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Role Filter -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-2">@term('filter_by_role_label')</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach(['teacher', 'counselor', 'admin', 'parent'] as $role)
                                <button
                                    type="button"
                                    wire:click="$toggle('selectedRoles.{{ $role }}')"
                                    class="px-3 py-1.5 rounded-lg text-sm border transition-all
                                        {{ in_array($role, $selectedRoles) ? 'bg-pulse-orange-500 border-pulse-orange-500 text-white' : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400' }}"
                                >
                                    {{ ucfirst($role) }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Estimated Count -->
                <div class="p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center gap-2">
                        <x-icon name="information-circle" class="w-5 h-5 text-blue-600" />
                        <span class="text-sm text-blue-800">
                            <strong>{{ $this->estimatedContactCount }}</strong>
                            {{ $targetType === 'learners' ? app(\App\Services\TerminologyService::class)->get('learner_plural') : app(\App\Services\TerminologyService::class)->get('user_plural') }} @term('match_filters_label')
                            @if(empty($selectedGrades) && empty($selectedClassrooms))
                                <span class="text-blue-600">(@term('all_label') {{ $targetType }})</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

        {{-- Step 6: Reminder Settings --}}
        @elseif($currentStep === 6)
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-1">@term('reminder_settings_label')</h2>
                <p class="text-gray-500 text-sm mb-6">@term('reminder_settings_body')</p>

                <!-- Enable Reminders Toggle -->
                <div class="mb-6">
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            wire:model.live="enableReminders"
                            class="w-5 h-5 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                        />
                        <span class="font-medium text-gray-900">@term('enable_reminders_label')</span>
                    </label>
                </div>

                @if($enableReminders)
                    <!-- Reminder Channels -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3">@term('reminder_channels_label')</label>
                        <div class="flex gap-4">
                            @foreach(['email' => 'Email', 'sms' => 'SMS', 'whatsapp' => 'WhatsApp'] as $channel => $label)
                                <button
                                    type="button"
                                    wire:click="toggleReminderChannel('{{ $channel }}')"
                                    class="flex items-center gap-2 px-4 py-2 rounded-lg border transition-all
                                        {{ in_array($channel, $reminderChannels) ? 'bg-pulse-orange-500 border-pulse-orange-500 text-white' : 'bg-white border-gray-300 text-gray-700 hover:border-gray-400' }}"
                                >
                                    <x-icon name="{{ $channel === 'email' ? 'envelope' : ($channel === 'sms' ? 'device-phone-mobile' : 'chat-bubble-left-ellipsis') }}" class="w-4 h-4" />
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        @error('reminderChannels') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <!-- Lead Time -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('send_reminder_before_label')</label>
                        <select
                            wire:model="reminderLeadTime"
                            class="w-full md:w-64 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                            <option value="15">15 minutes</option>
                            <option value="30">30 minutes</option>
                            <option value="60">1 hour</option>
                            <option value="120">2 hours</option>
                            <option value="1440">1 day</option>
                        </select>
                    </div>

                    <!-- Follow-up -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <label class="flex items-center gap-3 cursor-pointer mb-3">
                            <input
                                type="checkbox"
                                wire:model.live="enableFollowUp"
                                class="w-5 h-5 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                            />
                            <span class="font-medium text-gray-900">@term('follow_up_label')</span>
                        </label>

                        @if($enableFollowUp)
                            <div class="ml-8">
                                <label class="block text-sm text-gray-600 mb-1">@term('follow_up_after_label')</label>
                                <select
                                    wire:model="followUpDelay"
                                    class="w-full md:w-64 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                >
                                    <option value="1">1 hour</option>
                                    <option value="4">4 hours</option>
                                    <option value="24">24 hours</option>
                                    <option value="48">48 hours</option>
                                </select>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8 bg-gray-50 rounded-lg">
                        <x-icon name="bell-slash" class="w-12 h-12 text-gray-300 mx-auto mb-2" />
                        <p class="text-gray-500">@term('reminders_disabled_label')</p>
                    </div>
                @endif
            </div>

        {{-- Step 7: Review --}}
        @elseif($currentStep === 7)
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-1">@term('review_create_label')</h2>
                <p class="text-gray-500 text-sm mb-6">@term('review_settings_label')</p>

                <div class="space-y-4">
                    <!-- Basic Info -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-2">@term('basic_info_label')</h3>
                        <dl class="grid grid-cols-2 gap-2 text-sm">
                            <dt class="text-gray-500">@term('title_label')</dt>
                            <dd class="text-gray-900">{{ $title }}</dd>
                            <dt class="text-gray-500">@term('type_label')</dt>
                            <dd class="text-gray-900">{{ $this->collectionTypes[$collectionType]['label'] }}</dd>
                        </dl>
                    </div>

                    <!-- Data Source -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-2">@term('data_source_label')</h3>
                        <dl class="grid grid-cols-2 gap-2 text-sm">
                            <dt class="text-gray-500">@term('source_label')</dt>
                            <dd class="text-gray-900">{{ $this->dataSources[$dataSource]['label'] }}</dd>
                            <dt class="text-gray-500">@term('questions_label')</dt>
                            <dd class="text-gray-900">
                                @if($dataSource === 'survey' && $surveyId)
                                    {{ collect($availableSurveys)->firstWhere('id', $surveyId)['title'] ?? app(\App\Services\TerminologyService::class)->get('survey_singular') }}
                                @else
                                    {{ count($inlineQuestions) }} @term('inline_questions_label')
                                @endif
                            </dd>
                        </dl>
                    </div>

                    <!-- Format -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-2">@term('format_label')</h3>
                        <p class="text-sm text-gray-900">{{ $this->formatModes[$formatMode]['label'] }}</p>
                    </div>

                    <!-- Schedule -->
                    @if($collectionType !== 'one_time')
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-medium text-gray-900 mb-2">@term('schedule_label')</h3>
                            <dl class="grid grid-cols-2 gap-2 text-sm">
                                <dt class="text-gray-500">@term('type_label')</dt>
                                <dd class="text-gray-900">{{ $this->scheduleTypes[$scheduleType]['label'] }}</dd>
                                @if($scheduleType === 'interval')
                                    <dt class="text-gray-500">@term('every_label')</dt>
                                    <dd class="text-gray-900">{{ $intervalValue }} {{ $intervalType }}</dd>
                                @elseif($scheduleType === 'custom')
                                    <dt class="text-gray-500">@term('select_days_label')</dt>
                                    <dd class="text-gray-900">{{ implode(', ', array_map('ucfirst', $customDays)) }}</dd>
                                @endif
                                <dt class="text-gray-500">@term('collection_times_label')</dt>
                                <dd class="text-gray-900">{{ implode(', ', $customTimes) }}</dd>
                            </dl>
                        </div>
                    @endif

                    <!-- Contacts -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-2">@term('contacts_label')</h3>
                        <dl class="grid grid-cols-2 gap-2 text-sm">
                            <dt class="text-gray-500">@term('target_type_label')</dt>
                            <dd class="text-gray-900">{{ ucfirst($targetType) }}</dd>
                            <dt class="text-gray-500">@term('estimated_count_label')</dt>
                            <dd class="text-gray-900">{{ $this->estimatedContactCount }} {{ $targetType }}</dd>
                        </dl>
                    </div>

                    <!-- Reminders -->
                    <div class="p-4 bg-gray-50 rounded-lg">
                        <h3 class="font-medium text-gray-900 mb-2">@term('reminders_label')</h3>
                        <p class="text-sm text-gray-900">
                            @if($enableReminders)
                                @term('enabled_via_label') {{ implode(', ', array_map('ucfirst', $reminderChannels)) }}
                            @else
                                @term('disabled_label')
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Navigation Buttons -->
    <div class="mt-6 flex items-center justify-between">
        <div>
            @if($currentStep > 1)
                <button
                    type="button"
                    wire:click="previousStep"
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    @term('back_label')
                </button>
            @else
                <a href="{{ route('collect.index') }}" class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 inline-block">
                    @term('cancel_action')
                </a>
            @endif
        </div>

        <div class="flex gap-3">
            @if($currentStep === 7)
                <button
                    type="button"
                    wire:click="save(false)"
                    class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    @term('save_draft_label')
                </button>
                <button
                    type="button"
                    wire:click="save(true)"
                    class="px-4 py-2 text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                >
                    @term('create_activate_label')
                </button>
            @else
                <button
                    type="button"
                    wire:click="nextStep"
                    class="px-4 py-2 text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                >
                    @term('continue_action')
                </button>
            @endif
        </div>
    </div>

    <!-- Question Editor Modal -->
    @if($showQuestionEditor)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeQuestionEditor"></div>

                <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ $editingQuestionIndex !== null ? app(\App\Services\TerminologyService::class)->get('edit_question_title_label') : app(\App\Services\TerminologyService::class)->get('add_question_title_label') }}
                    </h3>

                    <div class="space-y-4">
                        <!-- Question Text -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('question_singular')</label>
                            <textarea
                                wire:model="questionForm.question"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="{{ app(\App\Services\TerminologyService::class)->get('enter_question_label') }}"
                            ></textarea>
                            @error('questionForm.question') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <!-- Question Type -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('type_label')</label>
                            <select
                                wire:model.live="questionForm.type"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            >
                                @foreach($this->questionTypes as $type => $info)
                                    <option value="{{ $type }}">{{ $info['label'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Multiple Choice Options -->
                        @if(($questionForm['type'] ?? 'scale') === 'multiple_choice')
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="block text-sm font-medium text-gray-700">@term('options_label')</label>
                                    <button type="button" wire:click="addOption" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700">+ @term('add_option_label')</button>
                                </div>
                                <div class="space-y-2">
                                    @foreach($questionForm['options'] ?? [] as $index => $option)
                                        <div class="flex items-center gap-2">
                                            <input
                                                type="text"
                                                wire:model="questionForm.options.{{ $index }}"
                                                class="flex-1 px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                                placeholder="{{ app(\App\Services\TerminologyService::class)->get('option_singular') }} {{ $index + 1 }}"
                                            />
                                            @if(count($questionForm['options'] ?? []) > 2)
                                                <button type="button" wire:click="removeOption({{ $index }})" class="p-1 text-gray-400 hover:text-red-500">
                                                    <x-icon name="x-mark" class="w-4 h-4" />
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Required Toggle -->
                        <div>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="checkbox"
                                    wire:model="questionForm.required"
                                    class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                                />
                                <span class="text-sm text-gray-700">@term('required_question_label')</span>
                            </label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end gap-3">
                        <button
                            type="button"
                            wire:click="closeQuestionEditor"
                            class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            @term('cancel_action')
                        </button>
                        <button
                            type="button"
                            wire:click="saveQuestion"
                            class="px-4 py-2 text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                        >
                            @term('save_action') @term('question_singular')
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
