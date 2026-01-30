<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
        <a href="{{ route('alerts.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
            <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
            Back to Alerts
        </a>
        <h1 class="text-2xl font-bold text-gray-900">{{ $workflowId ? 'Edit Alert' : 'Create Alert' }}</h1>
        <p class="text-gray-500 mt-1">Set up an automated alert workflow in a few simple steps</p>
    </div>

    <!-- Progress Steps -->
    <div class="mb-8">
        <nav aria-label="Progress">
            <ol role="list" class="flex items-center">
                @foreach([
                    ['step' => 1, 'name' => 'Basic Info'],
                    ['step' => 2, 'name' => 'Trigger'],
                    ['step' => 3, 'name' => 'Delay'],
                    ['step' => 4, 'name' => 'Actions'],
                    ['step' => 5, 'name' => 'Settings'],
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
                    <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>
                    <p class="text-sm text-gray-500 mt-1">Give your alert a name and description</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700">Alert Name *</label>
                        <input
                            type="text"
                            id="name"
                            wire:model="name"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="e.g., Low GPA Alert"
                        />
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea
                            id="description"
                            wire:model="description"
                            rows="3"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="Describe what this alert does..."
                        ></textarea>
                    </div>
                </div>
            </div>
        @endif

        {{-- Step 2: Trigger Configuration --}}
        @if($currentStep === 2)
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Trigger Configuration</h2>
                    <p class="text-sm text-gray-500 mt-1">Define when this alert should fire</p>
                </div>

                <!-- Trigger Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Trigger Type</label>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        @foreach([
                            'metric_threshold' => ['label' => 'Metric Threshold', 'icon' => 'chart-bar'],
                            'survey_response' => ['label' => 'Survey Response', 'icon' => 'clipboard-document-list'],
                            'attendance' => ['label' => 'Attendance', 'icon' => 'calendar'],
                            'schedule' => ['label' => 'Schedule', 'icon' => 'clock'],
                        ] as $type => $info)
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
                        <label class="block text-sm font-medium text-gray-700">Conditions</label>
                        <button
                            wire:click="openConditionModal"
                            class="inline-flex items-center text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
                        >
                            <x-icon name="plus" class="w-4 h-4 mr-1" />
                            Add Condition
                        </button>
                    </div>

                    @if(count($conditions) > 1)
                        <div class="mb-4">
                            <label class="text-sm text-gray-600">Match:</label>
                            <select wire:model="conditionLogic" class="ml-2 text-sm border-gray-300 rounded-lg">
                                <option value="and">All conditions (AND)</option>
                                <option value="or">Any condition (OR)</option>
                            </select>
                        </div>
                    @endif

                    @if(empty($conditions))
                        <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                            <x-icon name="funnel" class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                            <p class="text-sm text-gray-500">No conditions added yet</p>
                            <button
                                wire:click="openConditionModal"
                                class="mt-2 text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
                            >
                                Add your first condition
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
                    <h2 class="text-lg font-semibold text-gray-900">Time Delay (Optional)</h2>
                    <p class="text-sm text-gray-500 mt-1">Add a delay before actions are executed</p>
                </div>

                <div class="flex items-center gap-3">
                    <input
                        type="checkbox"
                        id="hasDelay"
                        wire:model.live="hasDelay"
                        class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                    />
                    <label for="hasDelay" class="text-sm font-medium text-gray-700">
                        Add a delay before executing actions
                    </label>
                </div>

                @if($hasDelay)
                    <div class="ml-7 p-4 bg-gray-50 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Wait for:</label>
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
                                <option value="minutes">Minutes</option>
                                <option value="hours">Hours</option>
                                <option value="days">Days</option>
                            </select>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Actions will be executed {{ $delayDuration }} {{ $delayUnit }} after the trigger conditions are met.
                        </p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Step 4: Actions --}}
        @if($currentStep === 4)
            <div class="space-y-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Actions</h2>
                    <p class="text-sm text-gray-500 mt-1">Define what happens when the alert is triggered</p>
                </div>

                <div class="flex items-center justify-between mb-3">
                    <span class="text-sm text-gray-600">{{ count($actions) }} action(s) configured</span>
                    <button
                        wire:click="openActionModal"
                        class="inline-flex items-center text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
                    >
                        <x-icon name="plus" class="w-4 h-4 mr-1" />
                        Add Action
                    </button>
                </div>

                @if(empty($actions))
                    <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                        <x-icon name="bolt" class="w-8 h-8 text-gray-400 mx-auto mb-2" />
                        <p class="text-sm text-gray-500">No actions added yet</p>
                        <button
                            wire:click="openActionModal"
                            class="mt-2 text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
                        >
                            Add your first action
                        </button>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($actions as $index => $action)
                            @php $actionInfo = $this->actionTypes[$action['action_type']] ?? ['label' => 'Unknown', 'icon' => 'question-mark-circle']; @endphp
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
                                            {{ count($action['config']['recipients']) }} recipient(s)
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
                    <h2 class="text-lg font-semibold text-gray-900">Settings</h2>
                    <p class="text-sm text-gray-500 mt-1">Configure alert behavior and limits</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Cooldown Period</label>
                        <p class="text-xs text-gray-500 mb-2">Minimum time between repeat triggers for the same entity</p>
                        <div class="flex items-center gap-2">
                            <input
                                type="number"
                                wire:model="cooldownMinutes"
                                min="0"
                                max="10080"
                                class="w-24 rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            />
                            <span class="text-sm text-gray-500">minutes</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Daily Execution Limit</label>
                        <p class="text-xs text-gray-500 mb-2">Maximum number of times this alert can fire per day</p>
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
                    <h3 class="font-semibold text-gray-900 mb-3">Alert Summary</h3>
                    <dl class="space-y-2 text-sm">
                        <div class="flex">
                            <dt class="w-32 text-gray-500">Name:</dt>
                            <dd class="font-medium text-gray-900">{{ $name }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="w-32 text-gray-500">Trigger:</dt>
                            <dd class="font-medium text-gray-900">{{ Workflow::getTriggerTypes()[$triggerType] ?? $triggerType }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="w-32 text-gray-500">Conditions:</dt>
                            <dd class="font-medium text-gray-900">{{ count($conditions) }} condition(s)</dd>
                        </div>
                        <div class="flex">
                            <dt class="w-32 text-gray-500">Delay:</dt>
                            <dd class="font-medium text-gray-900">{{ $hasDelay ? "{$delayDuration} {$delayUnit}" : 'None' }}</dd>
                        </div>
                        <div class="flex">
                            <dt class="w-32 text-gray-500">Actions:</dt>
                            <dd class="font-medium text-gray-900">{{ count($actions) }} action(s)</dd>
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
                        Previous
                    </button>
                @endif
            </div>

            <div class="flex items-center gap-3">
                @if($currentStep < 5)
                    <button
                        wire:click="nextStep"
                        class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                    >
                        Next
                        <x-icon name="arrow-right" class="w-4 h-4 ml-2" />
                    </button>
                @else
                    <button
                        wire:click="save(false)"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Save as Draft
                    </button>
                    <button
                        wire:click="save(true)"
                        class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700"
                    >
                        <x-icon name="bolt" class="w-4 h-4 mr-2" />
                        Save & Activate
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
                    {{ $editingConditionIndex !== null ? 'Edit Condition' : 'Add Condition' }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Field</label>
                        <select
                            wire:model="conditionForm.field"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                        >
                            <option value="">Select a field...</option>
                            @foreach($this->availableFields as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('conditionForm.field') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Operator</label>
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
                        <label class="block text-sm font-medium text-gray-700">Value</label>
                        <input
                            type="text"
                            wire:model="conditionForm.value"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="Enter value..."
                        />
                        @error('conditionForm.value') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        wire:click="closeConditionModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="saveCondition"
                        class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                    >
                        {{ $editingConditionIndex !== null ? 'Update' : 'Add' }} Condition
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
                    {{ $editingActionIndex !== null ? 'Edit Action' : 'Add Action' }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Action Type</label>
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
                            <label class="block text-sm font-medium text-gray-700">Recipients</label>
                            <select
                                wire:model="actionForm.config.recipients"
                                multiple
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            >
                                @foreach($this->staffMembers as $staff)
                                    <option value="{{ $staff['id'] }}">{{ $staff['name'] }} ({{ $staff['role'] }})</option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Message Template</label>
                            <textarea
                                wire:model="actionForm.config.message"
                                rows="3"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                                placeholder="Alert: @{{student_name}} requires attention. @{{trigger_reason}}"
                            ></textarea>
                            <p class="mt-1 text-xs text-gray-500">Use @{{variable}} for dynamic values</p>
                        </div>
                    @endif

                    @if(($actionForm['action_type'] ?? '') === 'webhook')
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Webhook URL</label>
                            <input
                                type="url"
                                wire:model="actionForm.config.url"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                                placeholder="https://api.example.com/webhook"
                            />
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        wire:click="closeActionModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                    >
                        Cancel
                    </button>
                    <button
                        wire:click="saveAction"
                        class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                    >
                        {{ $editingActionIndex !== null ? 'Update' : 'Add' }} Action
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
