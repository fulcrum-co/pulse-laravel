@php
    $terminology = app(\App\Services\TerminologyService::class);
    $triggerTypeOptions = [
        'metric_threshold' => ['label' => $terminology->get('trigger_metric_threshold_label'), 'icon' => 'chart-bar'],
        'survey_response' => ['label' => $terminology->get('trigger_survey_response_label'), 'icon' => 'clipboard-document-list'],
        'attendance' => ['label' => $terminology->get('trigger_attendance_label'), 'icon' => 'calendar'],
        'schedule' => ['label' => $terminology->get('trigger_schedule_label'), 'icon' => 'clock'],
    ];
@endphp

<div class="max-w-4xl mx-auto">
    {{-- Step 0: Mode Selection --}}
    @if($currentStep === 0)
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">@term('create_alert_label')</h1>
            <p class="text-gray-500 mt-1">@term('choose_alert_workflow_label')</p>
        </div>

        <div class="grid md:grid-cols-2 gap-6 max-w-3xl mx-auto">
            <!-- Step-by-Step Wizard -->
            <button
                wire:click="selectWizardMode"
                class="group relative bg-white rounded-xl border-2 border-gray-200 p-8 text-left hover:border-pulse-orange-400 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-pulse-orange-500 focus:ring-offset-2"
            >
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-pulse-orange-100 flex items-center justify-center group-hover:bg-pulse-orange-200 transition-colors">
                        <x-icon name="list-bullet" class="w-6 h-6 text-pulse-orange-600" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">@term('step_by_step_wizard_label')</h3>
                        <p class="text-sm text-gray-500 mt-1">@term('step_by_step_wizard_body')</p>
                        <ul class="mt-3 space-y-1 text-xs text-gray-500">
                            <li class="flex items-center gap-1.5">
                                <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                                @term('easy_to_use_label')
                            </li>
                            <li class="flex items-center gap-1.5">
                                <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                                @term('quick_setup_label')
                            </li>
                            <li class="flex items-center gap-1.5">
                                <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                                @term('linear_workflows_label')
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="absolute top-3 right-3 text-xs font-medium text-pulse-orange-600 bg-pulse-orange-50 px-2 py-1 rounded-full">
                    @term('recommended_label')
                </div>
            </button>

            <!-- Visual Canvas -->
            <button
                wire:click="selectCanvasMode"
                class="group relative bg-white rounded-xl border-2 border-gray-200 p-8 text-left hover:border-indigo-400 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
            >
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center group-hover:bg-indigo-200 transition-colors">
                        <x-icon name="squares-2x2" class="w-6 h-6 text-indigo-600" />
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">@term('visual_canvas_label')</h3>
                        <p class="text-sm text-gray-500 mt-1">@term('visual_canvas_body')</p>
                        <ul class="mt-3 space-y-1 text-xs text-gray-500">
                            <li class="flex items-center gap-1.5">
                                <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                                @term('branching_logic_label')
                            </li>
                            <li class="flex items-center gap-1.5">
                                <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                                @term('parallel_actions_label')
                            </li>
                            <li class="flex items-center gap-1.5">
                                <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                                @term('visual_overview_label')
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="absolute top-3 right-3 text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded-full">
                    @term('advanced_label')
                </div>
            </button>
        </div>

        <div class="text-center mt-8">
            <a href="{{ route('alerts.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                @term('cancel_action')
            </a>
        </div>
    @else
    <!-- Header (shown for steps 1-5) -->
    <div class="mb-8">
        <a href="{{ route('alerts.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
            <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
            @term('back_to_alerts_label')
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $workflowId ? $terminology->get('edit_alert_label') : $terminology->get('create_alert_label') }}</h1>
        <p class="text-gray-500 mt-1">@term('alert_setup_steps_label')</p>
    </div>

    <!-- Progress Steps -->
    <div class="mb-8">
        <nav aria-label="Progress">
            <ol role="list" class="flex items-center">
                @foreach([
                    ['step' => 1, 'name' => $terminology->get('alert_step_basic_info_label')],
                    ['step' => 2, 'name' => $terminology->get('alert_step_trigger_label')],
                    ['step' => 3, 'name' => $terminology->get('alert_step_delay_label')],
                    ['step' => 4, 'name' => $terminology->get('alert_step_actions_label')],
                    ['step' => 5, 'name' => $terminology->get('alert_step_settings_label')],
                ] as $index => $stepInfo)
                    <li class="relative {{ $index < 4 ? 'pr-8 sm:pr-20 flex-1' : '' }}">
                        @if($stepInfo['step'] < $currentStep)
                            <!-- Completed -->
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="h-0.5 w-full bg-pulse-orange-500"></div>
                            </div>
                            <button
                                wire:click="goToStep({{ $stepInfo['step'] }})"
                                class="relative flex h-8 w-8 items-center justify-center rounded-full bg-pulse-orange-500 hover:bg-pulse-orange-600"
                            >
                                <x-icon name="check" class="h-5 w-5 text-white" />
                            </button>
                        @elseif($stepInfo['step'] === $currentStep)
                            <!-- Current -->
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="h-0.5 w-full bg-gray-200"></div>
                            </div>
                            <div class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-pulse-orange-500 bg-white">
                                <span class="text-pulse-orange-500 font-medium text-sm">{{ $stepInfo['step'] }}</span>
                            </div>
                        @else
                            <!-- Upcoming -->
                            <div class="absolute inset-0 flex items-center" aria-hidden="true">
                                <div class="h-0.5 w-full bg-gray-200"></div>
                            </div>
                            <div class="relative flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white">
                                <span class="text-gray-500 text-sm">{{ $stepInfo['step'] }}</span>
                            </div>
                        @endif
                        <span class="absolute -bottom-6 left-1/2 -translate-x-1/2 text-xs font-medium {{ $stepInfo['step'] === $currentStep ? 'text-pulse-orange-600' : 'text-gray-500' }} whitespace-nowrap">
                            {{ $stepInfo['name'] }}
                        </span>
                    </li>
                @endforeach
            </ol>
        </nav>
    </div>

    <!-- Step Content -->
    <x-card class="mt-12">
        {{-- Step 1: Basic Info --}}
        @if($currentStep === 1)
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">@term('basic_information_label')</h2>
                    <p class="text-sm text-gray-500 mt-1">@term('alert_name_description_help_label')</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">@term('alert_name_label') *</label>
                        <input
                            type="text"
                            id="name"
                            wire:model="name"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="{{ $terminology->get('alert_name_placeholder') }}"
                        />
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">@term('description_label')</label>
                        <textarea
                            id="description"
                            wire:model="description"
                            rows="3"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="{{ $terminology->get('alert_description_placeholder') }}"
                        ></textarea>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 2: Trigger Configuration --}}
        @if($currentStep === 2)
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">@term('trigger_configuration_label')</h2>
                    <p class="text-sm text-gray-500 mt-1">@term('trigger_configuration_body')</p>
                </div>

                <!-- Trigger Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">@term('trigger_type_label')</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach($triggerTypeOptions as $type => $info)
                            <button
                                wire:click="$set('triggerType', '{{ $type }}')"
                                class="p-4 rounded-lg border-2 text-center transition-all {{ $triggerType === $type ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                            >
                                <x-icon name="{{ $info['icon'] }}" class="w-6 h-6 mx-auto mb-2 {{ $triggerType === $type ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                <span class="text-sm font-medium {{ $triggerType === $type ? 'text-pulse-orange-700' : 'text-gray-700' }}">{{ $info['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <!-- Conditions -->
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <label class="block text-sm font-medium text-gray-700">@term('conditions_label')</label>
                        <button
                            wire:click="openConditionModal"
                            class="inline-flex items-center text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
                        >
                            <x-icon name="plus" class="w-4 h-4 mr-1" />
                            @term('add_condition_label')
                        </button>
                    </div>

                    @if(count($conditions) > 1)
                        <div class="mb-4">
                            <label class="text-sm text-gray-600">@term('match_label')</label>
                            <select wire:model="conditionLogic" class="ml-2 text-sm border-gray-300 rounded-lg">
                                <option value="and">@term('all_conditions_label')</option>
                                <option value="or">@term('any_condition_label')</option>
                            </select>
                        </div>
                    @endif

                    @if(empty($conditions))
                        <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                            <x-icon name="funnel" class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                            <p class="text-sm text-gray-500">@term('no_conditions_yet_label')</p>
                            <button
                                wire:click="openConditionModal"
                                class="mt-2 text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
                            >
                                @term('add_first_condition_label')
                            </button>
                        </div>
                    @else
                        <div class="space-y-2">
                            @foreach($conditions as $index => $condition)
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-1">
                                        <span class="font-medium text-gray-900">{{ $this->availableFields[$condition['field']] ?? $condition['field'] }}</span>
                                        <span class="text-gray-500 mx-2">{{ $this->operators[$condition['operator']] ?? $condition['operator'] }}</span>
                                        <span class="text-pulse-orange-600 font-medium">{{ $condition['value'] }}</span>
                                    </div>
                                    <button wire:click="openConditionModal({{ $index }})" class="p-1 text-gray-400 hover:text-gray-600">
                                        <x-icon name="pencil" class="w-4 h-4" />
                                    </button>
                                    <button wire:click="removeCondition({{ $index }})" class="p-1 text-gray-400 hover:text-red-500">
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </button>
                                </div>
                                @if(!$loop->last)
                                    <div class="text-center text-xs text-gray-400 uppercase">{{ $conditionLogic }}</div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    @error('conditions') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        @endif

        {{-- Step 3: Delay --}}
        @if($currentStep === 3)
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">@term('time_delay_optional_label')</h2>
                    <p class="text-sm text-gray-500 mt-1">@term('delay_before_actions_label')</p>
                </div>

                <div class="flex items-center gap-3">
                    <input
                        type="checkbox"
                        id="hasDelay"
                        wire:model.live="hasDelay"
                        class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                    />
                    <label for="hasDelay" class="text-sm font-medium text-gray-700">
                        @term('add_delay_label')
                    </label>
                </div>

                @if($hasDelay)
                    <div class="ml-7 p-4 bg-gray-50 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2">@term('wait_for_label')</label>
                        <div class="flex items-center gap-3">
                            <input
                                type="number"
                                wire:model="delayDuration"
                                min="1"
                                max="999"
                                class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            />
                            <select
                                wire:model="delayUnit"
                                class="rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            >
                                <option value="minutes">@term('minutes_label')</option>
                                <option value="hours">@term('hours_label')</option>
                                <option value="days">@term('days_label')</option>
                            </select>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            @term('delay_execution_help_label') {{ $delayDuration }} {{ $delayUnit }}.
                        </p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Step 4: Actions --}}
        @if($currentStep === 4)
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">@term('actions_label')</h2>
                    <p class="text-sm text-gray-500 mt-1">@term('actions_when_triggered_label')</p>
                </div>

                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-600">{{ count($actions) }} @term('actions_configured_label')</span>
                    <button
                        wire:click="openActionModal"
                        class="inline-flex items-center text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
                    >
                        <x-icon name="plus" class="w-4 h-4 mr-1" />
                        @term('add_action_label')
                    </button>
                </div>

                @if(empty($actions))
                    <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <x-icon name="bolt" class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                        <p class="text-sm text-gray-500">@term('no_actions_yet_label')</p>
                        <button
                            wire:click="openActionModal"
                            class="mt-2 text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
                        >
                            @term('add_first_action_label')
                        </button>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($actions as $index => $action)
                            @php $actionInfo = $this->actionTypes[$action['action_type']] ?? ['label' => $terminology->get('unknown_label'), 'icon' => 'question-mark-circle']; @endphp
                            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0 w-10 h-10 bg-pulse-orange-100 rounded-lg flex items-center justify-center">
                                    <x-icon name="{{ $actionInfo['icon'] }}" class="w-5 h-5 text-pulse-orange-600" />
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">{{ $actionInfo['label'] }}</div>
                                    <div class="text-sm text-gray-500">
                                        @if(!empty($action['config']['message']))
                                            {{ Str::limit($action['config']['message'], 50) }}
                                        @elseif(!empty($action['config']['recipients']))
                                            {{ count($action['config']['recipients']) }} @term('recipients_label')
                                        @endif
                                    </div>
                                </div>
                                <button wire:click="openActionModal({{ $index }})" class="p-2 text-gray-400 hover:text-gray-600">
                                    <x-icon name="pencil" class="w-4 h-4" />
                                </button>
                                <button wire:click="removeAction({{ $index }})" class="p-2 text-gray-400 hover:text-red-500">
                                    <x-icon name="trash" class="w-4 h-4" />
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
                @error('actions') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        @endif

        {{-- Step 5: Settings --}}
        @if($currentStep === 5)
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">@term('settings_label')</h2>
                    <p class="text-sm text-gray-500 mt-1">@term('alert_settings_body_label')</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">@term('cooldown_period_label')</label>
                        <p class="text-xs text-gray-500 mb-2">@term('cooldown_period_help_label')</p>
                        <div class="flex items-center gap-2">
                            <input
                                type="number"
                                wire:model="cooldownMinutes"
                                min="0"
                                max="10080"
                                class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            />
                            <span class="text-sm text-gray-500">@term('minutes_label')</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">@term('daily_execution_limit_label')</label>
                        <p class="text-xs text-gray-500 mb-2">@term('daily_execution_limit_help_label')</p>
                        <input
                            type="number"
                            wire:model="maxExecutionsPerDay"
                            min="1"
                            max="10000"
                            class="w-32 rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                        />
                    </div>
                </div>

                <!-- Summary -->
                <div class="mt-8 p-4 bg-pulse-orange-50 rounded-lg">
                    <h3 class="font-semibold text-gray-900 mb-3">@term('alert_summary_label')</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex">
                            <dt class="w-32 text-gray-500">@term('name_label'):</dt>
                            <dd class="font-medium text-gray-900">{{ $name }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="w-32 text-gray-500">@term('trigger_label'):</dt>
                            <dd class="font-medium text-gray-900">{{ \App\Models\Workflow::getTriggerTypes()[$triggerType] ?? $triggerType }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="w-32 text-gray-500">@term('conditions_label'):</dt>
                            <dd class="font-medium text-gray-900">{{ count($conditions) }} @term('conditions_label')</dd>
                        </div>
                        <div class="flex">
                            <dt class="w-32 text-gray-500">@term('delay_label'):</dt>
                            <dd class="font-medium text-gray-900">{{ $hasDelay ? "{$delayDuration} {$delayUnit}" : $terminology->get('none_label') }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="w-32 text-gray-500">@term('actions_label'):</dt>
                            <dd class="font-medium text-gray-900">{{ count($actions) }} @term('actions_label')</dd>
                        </div>
                    </dl>
                </div>
            </div>
        @endif

        <!-- Navigation Buttons -->
        <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
            <div>
                @if($currentStep > 1)
                    <button
                        wire:click="previousStep"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                    >
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        @term('previous_label')
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-3">
                @if($currentStep < 5)
                    <button
                        wire:click="nextStep"
                        class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                    >
                        @term('next_label')
                        <x-icon name="arrow-right" class="w-4 h-4 ml-2" />
                    </button>
                @else
                    <button
                        wire:click="save(false)"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        @term('save_draft_label')
                    </button>
                    <button
                        wire:click="save(true)"
                        class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700"
                    >
                        <x-icon name="bolt" class="w-4 h-4 mr-2" />
                        @term('save_activate_label')
                    </button>
                @endif
            </div>
        </div>
    </x-card>

    <!-- Condition Modal -->
    @if($showConditionModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeConditionModal"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ $editingConditionIndex !== null ? $terminology->get('edit_condition_label') : $terminology->get('add_condition_label') }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">@term('field_label')</label>
                        <select
                            wire:model="conditionForm.field"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                        >
                            <option value="">{{ $terminology->get('select_field_placeholder') }}</option>
                            @foreach($this->availableFields as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('conditionForm.field') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">@term('operator_label')</label>
                        <select
                            wire:model="conditionForm.operator"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                        >
                            @foreach($this->operators as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">@term('value_label')</label>
                        <input
                            type="text"
                            wire:model="conditionForm.value"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="{{ $terminology->get('enter_value_placeholder') }}"
                        />
                        @error('conditionForm.value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        wire:click="closeConditionModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                    >
                        @term('cancel_action')
                    </button>
                    <button
                        wire:click="saveCondition"
                        class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                    >
                        {{ $editingConditionIndex !== null ? $terminology->get('update_action') : $terminology->get('add_action') }} @term('condition_label')
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Action Modal -->
    @if($showActionModal)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeActionModal"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ $editingActionIndex !== null ? $terminology->get('edit_action_label') : $terminology->get('add_action_label') }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">@term('action_type_label')</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($this->actionTypes as $type => $info)
                                <button
                                    wire:click="$set('actionForm.action_type', '{{ $type }}')"
                                    class="flex items-center gap-2 p-3 rounded-lg border-2 text-left transition-all {{ ($actionForm['action_type'] ?? '') === $type ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                                >
                                    <x-icon name="{{ $info['icon'] }}" class="w-5 h-5 {{ ($actionForm['action_type'] ?? '') === $type ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                    <span class="text-sm font-medium">{{ $info['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    @if(in_array($actionForm['action_type'] ?? '', ['send_sms', 'send_email', 'send_whatsapp', 'make_call']))
                        <div>
                            <label class="block text-sm font-medium text-gray-700">@term('recipients_label')</label>
                            <select
                                wire:model="actionForm.config.recipients"
                                multiple
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            >
                                @foreach($this->staffMembers as $staff)
                                    <option value="{{ $staff['id'] }}">{{ $staff['name'] }} ({{ $staff['role'] }})</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">@term('multi_select_help_label')</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">@term('message_template_label')</label>
                            <textarea
                                wire:model="actionForm.config.message"
                                rows="3"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                                placeholder="{{ $terminology->get('alert_message_template_placeholder') }}"
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">@term('message_template_help_label')</p>
                        </div>
                    @endif

                    @if(($actionForm['action_type'] ?? '') === 'webhook')
                        <div>
                            <label class="block text-sm font-medium text-gray-700">@term('webhook_url_label')</label>
                            <input
                                type="url"
                                wire:model="actionForm.config.url"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                                placeholder="{{ $terminology->get('webhook_url_placeholder') }}"
                            />
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        wire:click="closeActionModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                    >
                        @term('cancel_action')
                    </button>
                    <button
                        wire:click="saveAction"
                        class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                    >
                        {{ $editingActionIndex !== null ? $terminology->get('update_action') : $terminology->get('add_action') }} @term('action_label')
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif {{-- End of step 0 else --}}
</div>
