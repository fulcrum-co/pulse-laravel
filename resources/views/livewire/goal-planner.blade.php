<div>
    @php($terminology = app(\App\Services\TerminologyService::class))
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">@term('goals_key_results_label')</h3>
            <p class="text-sm text-gray-500">@term('goals_key_results_help_label')</p>
        </div>
        <button wire:click="showAddGoalForm"
            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors">
            <x-icon name="plus" class="w-4 h-4 mr-1.5" />
            @term('add_goal_label')
        </button>
    </div>

    {{-- Add Goal Form --}}
    @if($showAddGoal)
        <div class="mb-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <h4 class="font-medium text-gray-900 mb-3">@term('new_goal_label')</h4>
            <div class="space-y-3">
                <div>
                    <input type="text" wire:model="newGoalTitle"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        placeholder="@term('goal_title_placeholder')">
                    @error('newGoalTitle') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <textarea wire:model="newGoalDescription" rows="2"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        placeholder="@term('description_optional_label')"></textarea>
                </div>
                <div class="w-48">
                    <label class="block text-xs font-medium text-gray-500 mb-1">@term('due_date_label')</label>
                    <input type="date" wire:model="newGoalDueDate"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="addGoal"
                        class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                        @term('add_goal_label')
                    </button>
                    <button wire:click="cancelAddGoal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        @term('cancel_label')
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Goals List --}}
    @if($goals->isEmpty())
        <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
            <x-icon name="flag" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-500">@term('no_goals_yet_label')</p>
            <p class="text-gray-400 text-sm mt-1">@term('add_first_goal_help_label')</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($goals as $goal)
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                    {{-- Goal Header --}}
                    <div class="p-4 {{ in_array($goal->id, $expandedGoals) ? 'border-b border-gray-100' : '' }}">
                        <div class="flex items-start gap-3">
                            {{-- Expand/Collapse Toggle --}}
                            <button wire:click="toggleGoal({{ $goal->id }})"
                                class="mt-1 p-1 text-gray-400 hover:text-gray-600 transition-transform {{ in_array($goal->id, $expandedGoals) ? 'rotate-90' : '' }}">
                                <x-icon name="chevron-right" class="w-4 h-4" />
                            </button>

                            {{-- Goal Content --}}
                            <div class="flex-1 min-w-0">
                                @if($editingGoalId === $goal->id)
                                    {{-- Edit Mode --}}
                                    <div class="space-y-3">
                                        <input type="text" wire:model="editData.title"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500">
                                            <textarea wire:model="editData.description" rows="2"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500"
                                                placeholder="@term('description_label')"></textarea>
                                        <div class="flex items-center gap-3">
                                            <input type="date" wire:model="editData.due_date"
                                                class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                            <select wire:model="editData.status"
                                                class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                                <option value="not_started">@term('not_started_label')</option>
                                                <option value="in_progress">@term('in_progress_label')</option>
                                                <option value="at_risk">@term('at_risk_label')</option>
                                                <option value="completed">@term('completed_label')</option>
                                            </select>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button wire:click="saveGoal"
                                                class="px-3 py-1.5 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                                                @term('save_label')
                                            </button>
                                            <button wire:click="cancelEditGoal"
                                                class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                                @term('cancel_label')
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    {{-- View Mode --}}
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ $goal->title }}</h4>
                                            @if($goal->description)
                                                <p class="text-sm text-gray-500 mt-0.5">{{ $goal->description }}</p>
                                            @endif
                                            <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                                                @if($goal->due_date)
                                                    <span class="flex items-center gap-1">
                                                        <x-icon name="calendar" class="w-3.5 h-3.5" />
                                                        {{ $goal->due_date->format('M j, Y') }}
                                                    </span>
                                                @endif
                                                <span>{{ $goal->keyResults->count() }} @term('key_results_label')</span>
                                                @if($goal->owner)
                                                    <span class="flex items-center gap-1">
                                                        <x-icon name="user" class="w-3.5 h-3.5" />
                                                        {{ $goal->owner->first_name }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>

                                        {{-- Goal Progress & Status --}}
                                        <div class="flex items-center gap-3">
                                            {{-- Progress --}}
                                            <div class="text-right">
                                                <div class="text-lg font-semibold text-gray-900">{{ number_format($goal->calculateProgress(), 0) }}%</div>
                                                <div class="w-24 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                    <div class="h-full bg-{{ $goal->status_color }}-500 transition-all"
                                                        style="width: {{ $goal->calculateProgress() }}%"></div>
                                                </div>
                                            </div>

                                            {{-- Status Dropdown --}}
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open"
                                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium
                                                        {{ match($goal->status) {
                                                            'completed' => 'bg-green-100 text-green-700',
                                                            'in_progress' => 'bg-blue-100 text-blue-700',
                                                            'at_risk' => 'bg-yellow-100 text-yellow-700',
                                                            default => 'bg-gray-100 text-gray-700'
                                                        } }}">
                                                    {{ $terminology->get($goal->status.'_label') }}
                                                    <x-icon name="chevron-down" class="w-3 h-3 ml-1" />
                                                </button>
                                                <div x-show="open" @click.away="open = false"
                                                    class="absolute right-0 mt-1 w-36 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10">
                                                    @foreach(['not_started', 'in_progress', 'at_risk', 'completed'] as $status)
                                                        <button wire:click="updateGoalStatus({{ $goal->id }}, '{{ $status }}')"
                                                            @click="open = false"
                                                            class="w-full px-3 py-1.5 text-left text-sm hover:bg-gray-50 {{ $goal->status === $status ? 'bg-gray-50 font-medium' : '' }}">
                                                            {{ $terminology->get($status.'_label') }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>

                                            {{-- Actions --}}
                                            <div x-data="{ open: false }" class="relative">
                                                <button @click="open = !open" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                                    <x-icon name="ellipsis-vertical" class="w-4 h-4" />
                                                </button>
                                                <div x-show="open" @click.away="open = false"
                                                    class="absolute right-0 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10">
                                                    <button wire:click="startEditGoal({{ $goal->id }})" @click="open = false"
                                                        class="w-full px-3 py-1.5 text-left text-sm hover:bg-gray-50 flex items-center gap-2">
                                                        <x-icon name="pencil" class="w-3.5 h-3.5" />
                                                        @term('edit_label')
                                                    </button>
                                                    <button wire:click="deleteGoal({{ $goal->id }})"
                                                        wire:confirm="@term('delete_goal_confirm_label')"
                                                        @click="open = false"
                                                        class="w-full px-3 py-1.5 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                                        <x-icon name="trash" class="w-3.5 h-3.5" />
                                                        @term('delete_label')
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Key Results (Expanded) --}}
                    @if(in_array($goal->id, $expandedGoals))
                        <div class="px-4 pb-4 pt-2 bg-gray-50">
                            {{-- Key Results List --}}
                            @if($goal->keyResults->isNotEmpty())
                                <div class="space-y-2 mb-3">
                                    @foreach($goal->keyResults as $kr)
                                        <div class="bg-white rounded-lg border border-gray-200 p-3">
                                            @if($editingKrId === $kr->id)
                                                {{-- Edit KR Mode --}}
                                                <div class="space-y-3">
                                                    <input type="text" wire:model="editData.title"
                                                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                                    <div class="grid grid-cols-3 gap-3">
                                                        <div>
                                                            <label class="block text-xs text-gray-500 mb-1">@term('current_label')</label>
                                                            <input type="number" wire:model="editData.current_value" step="0.01"
                                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-gray-500 mb-1">@term('target_label')</label>
                                                            <input type="number" wire:model="editData.target_value" step="0.01"
                                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                                        </div>
                                                        <div>
                                                            <label class="block text-xs text-gray-500 mb-1">@term('unit_label')</label>
                                                            <input type="text" wire:model="editData.unit"
                                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <button wire:click="saveKr"
                                                            class="px-3 py-1.5 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                                                            @term('save_label')
                                                        </button>
                                                        <button wire:click="cancelEditKr"
                                                            class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                                            @term('cancel_label')
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                {{-- View KR Mode --}}
                                                <div class="flex items-center gap-4">
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-gray-900">{{ $kr->title }}</p>
                                                        <div class="flex items-center gap-2 mt-1">
                                                            <span class="text-xs text-gray-500">
                                                                {{ $kr->formatted_value }} / {{ $kr->formatted_target }}
                                                            </span>
                                                            @if($kr->due_date)
                                                                <span class="text-xs text-gray-400">
                                                                    @term('due_label') {{ $kr->due_date->format('M j') }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>

                                                    {{-- Progress Bar --}}
                                                    <div class="w-32">
                                                        <div class="flex items-center justify-between text-xs text-gray-500 mb-1">
                                                            <span>{{ number_format($kr->calculateProgress(), 0) }}%</span>
                                                        </div>
                                                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                                            <div class="h-full bg-{{ $kr->status_color }}-500 transition-all"
                                                                style="width: {{ $kr->calculateProgress() }}%"></div>
                                                        </div>
                                                    </div>

                                                    {{-- Status Badge --}}
                                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                        {{ match($kr->status) {
                                                            'completed' => 'bg-green-100 text-green-700',
                                                            'on_track' => 'bg-green-100 text-green-700',
                                                            'in_progress' => 'bg-blue-100 text-blue-700',
                                                            'at_risk' => 'bg-yellow-100 text-yellow-700',
                                                            default => 'bg-gray-100 text-gray-700'
                                                        } }}">
                                                        {{ $terminology->get($kr->status.'_label') }}
                                                    </span>

                                                    {{-- KR Actions --}}
                                                    <div class="flex items-center gap-1">
                                                        <button wire:click="startEditKr({{ $kr->id }})"
                                                            class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                                            <x-icon name="pencil" class="w-3.5 h-3.5" />
                                                        </button>
                                                        <button wire:click="deleteKr({{ $kr->id }})"
                                                            wire:confirm="@term('delete_key_result_confirm_label')"
                                                            class="p-1 text-gray-400 hover:text-red-600 rounded">
                                                            <x-icon name="trash" class="w-3.5 h-3.5" />
                                                        </button>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            {{-- Add Key Result Form --}}
                            @if($addingKrToGoalId === $goal->id)
                                <div class="bg-white rounded-lg border border-gray-200 p-3 mb-3">
                                    <h5 class="text-sm font-medium text-gray-900 mb-3">@term('new_key_result_label')</h5>
                                    <div class="space-y-3">
                                        <input type="text" wire:model="newKrTitle"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"
                                            placeholder="@term('key_result_title_placeholder')">
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">@term('type_label')</label>
                                                <select wire:model="newKrMetricType"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                                    <option value="percentage">@term('percentage_label')</option>
                                                    <option value="number">@term('number_label')</option>
                                                    <option value="currency">@term('currency_label')</option>
                                                    <option value="boolean">@term('yes_no_label')</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">@term('start_label')</label>
                                                <input type="number" wire:model="newKrStartingValue" step="0.01"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">@term('target_label')</label>
                                                <input type="number" wire:model="newKrTargetValue" step="0.01"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg">
                                            </div>
                                            <div>
                                                <label class="block text-xs text-gray-500 mb-1">@term('unit_label')</label>
                                                <input type="text" wire:model="newKrUnit"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg"
                                                    placeholder="@term('unit_placeholder')">
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button wire:click="addKeyResult"
                                                class="px-3 py-1.5 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                                                @term('add_key_result_label')
                                            </button>
                                            <button wire:click="cancelAddKr"
                                                class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                                                @term('cancel_label')
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @else
                                {{-- Add Key Result Button --}}
                                <button wire:click="showAddKrForm({{ $goal->id }})"
                                    class="w-full py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-white rounded-lg border border-dashed border-gray-300 transition-colors flex items-center justify-center gap-1.5">
                                    <x-icon name="plus" class="w-4 h-4" />
                                    @term('add_key_result_label')
                                </button>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
