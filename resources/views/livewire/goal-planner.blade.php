<div class="space-y-3">
    {{-- Legend + Add Button --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-gray-500">
            <span class="flex items-center gap-1">
                <span class="w-2.5 h-2.5 rounded bg-green-500"></span> Focus Area
            </span>
            <span class="text-gray-300">→</span>
            <span class="flex items-center gap-1">
                <span class="w-2.5 h-2.5 rounded bg-purple-500"></span> Key Activity
            </span>
        </div>

        @if($showAddGoal)
            <div class="flex items-center gap-2">
                <input type="text" wire:model="newGoalTitle" wire:keydown.enter="addGoal" wire:keydown.escape="cancelAddGoal"
                    class="px-2.5 py-1.5 border border-gray-300 rounded focus:ring-1 focus:ring-green-500 focus:border-green-500 text-xs w-56"
                    placeholder="Focus area title..." autofocus>
                <button wire:click="addGoal" class="px-2.5 py-1.5 bg-green-500 text-white rounded text-xs font-medium hover:bg-green-600">Add</button>
                <button wire:click="cancelAddGoal" class="px-2.5 py-1.5 text-gray-500 hover:text-gray-700 text-xs">Cancel</button>
            </div>
        @else
            <button wire:click="showAddGoalForm" class="flex items-center gap-1.5 px-2.5 py-1.5 bg-green-50 text-green-600 hover:bg-green-100 rounded text-xs font-medium transition-colors">
                <x-icon name="plus" class="w-3.5 h-3.5" />
                Add Focus Area
            </button>
        @endif
    </div>

    {{-- Focus Areas List --}}
    @forelse($goals as $goal)
        <div class="bg-white rounded-lg border border-green-200 overflow-hidden shadow-sm">
            {{-- Focus Area Header --}}
            <div class="bg-green-50 border-b border-green-100">
                <div class="flex items-center gap-2.5 px-3 py-2">
                    <button wire:click="toggleGoal({{ $goal->id }})" class="text-green-400 hover:text-green-600 transition-colors">
                        <x-icon name="{{ in_array($goal->id, $expandedGoals) ? 'chevron-down' : 'chevron-right' }}" class="w-3.5 h-3.5" />
                    </button>

                    <div class="w-2.5 h-2.5 rounded bg-green-500 flex-shrink-0"></div>

                    @if($editingGoalId === $goal->id)
                        <div class="flex-1 space-y-2">
                            <input type="text" wire:model="editData.title"
                                class="w-full px-2 py-1 border border-green-300 rounded text-xs font-medium" autofocus>
                            <textarea wire:model="editData.description" rows="2"
                                class="w-full px-2 py-1 border border-green-300 rounded text-xs"
                                placeholder="Description (optional)"></textarea>
                            <div class="flex items-center gap-2">
                                <input type="date" wire:model="editData.due_date"
                                    class="px-2 py-1 border border-green-300 rounded text-xs">
                                <button wire:click="saveGoal" class="text-green-600 hover:text-green-700 text-xs font-medium">Save</button>
                                <button wire:click="cancelEditGoal" class="text-gray-500 hover:text-gray-700 text-xs">Cancel</button>
                            </div>
                        </div>
                    @else
                        <div class="flex-1 min-w-0">
                            <span wire:click="startEditGoal({{ $goal->id }})"
                                class="text-xs font-medium text-green-900 cursor-pointer hover:text-green-600 transition-colors">
                                {{ $goal->title }}
                            </span>
                            @if($goal->description)
                                <p class="text-[10px] text-green-700 mt-0.5">{{ Str::limit($goal->description, 100) }}</p>
                            @endif
                            <div class="flex items-center gap-2 mt-1 text-[10px] text-green-600">
                                @if($goal->due_date)
                                    <span class="flex items-center gap-0.5">
                                        <x-icon name="calendar" class="w-3 h-3" />
                                        {{ $goal->due_date->format('M j, Y') }}
                                    </span>
                                @endif
                                <span>{{ $goal->keyResults->count() }} activities</span>
                            </div>
                        </div>

                        <span class="text-[10px] text-green-500 font-medium uppercase tracking-wide">Focus Area</span>
                    @endif

                    {{-- Progress --}}
                    @php $progress = $goal->calculateProgress(); @endphp
                    <div class="text-right min-w-[56px]">
                        <div class="text-sm font-semibold text-gray-900">{{ number_format($progress, 0) }}%</div>
                        <div class="w-14 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full transition-all {{ $progress >= 100 ? 'bg-green-500' : ($progress >= 50 ? 'bg-blue-500' : 'bg-yellow-500') }}"
                                style="width: {{ min($progress, 100) }}%"></div>
                        </div>
                    </div>

                    {{-- Status Badge --}}
                    @php
                        $statusConfig = [
                            'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Done'],
                            'in_progress' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Active'],
                            'at_risk' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'At Risk'],
                            'not_started' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => 'Not Started'],
                        ];
                        $config = $statusConfig[$goal->status] ?? $statusConfig['not_started'];
                    @endphp
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $config['bg'] }} {{ $config['text'] }} hover:opacity-80">
                            {{ $config['label'] }}
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-28 bg-white border border-gray-200 rounded shadow-lg z-20">
                            @foreach($statusConfig as $status => $cfg)
                                <button wire:click="updateGoalStatus({{ $goal->id }}, '{{ $status }}')" @click="open = false"
                                    class="block w-full text-left px-2 py-1 text-[10px] hover:bg-gray-50 {{ $goal->status === $status ? 'bg-gray-50 font-medium' : '' }}">
                                    {{ $cfg['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Actions Menu --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="p-1 text-green-400 hover:text-green-600 hover:bg-green-100 rounded transition-colors">
                            <x-icon name="dots-vertical" class="w-3.5 h-3.5" />
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-28 bg-white border border-gray-200 rounded shadow-lg z-20">
                            <button wire:click="startEditGoal({{ $goal->id }})" @click="open = false"
                                class="flex items-center gap-1.5 w-full text-left px-2 py-1.5 text-[10px] text-gray-700 hover:bg-gray-50">
                                <x-icon name="pencil" class="w-3 h-3 text-gray-400" />
                                Edit
                            </button>
                            <button wire:click="deleteGoal({{ $goal->id }})" @click="open = false"
                                wire:confirm="Delete this focus area and all its activities?"
                                class="flex items-center gap-1.5 w-full text-left px-2 py-1.5 text-[10px] text-red-600 hover:bg-red-50">
                                <x-icon name="trash" class="w-3 h-3" />
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Key Activities --}}
            @if(in_array($goal->id, $expandedGoals))
                <div class="p-3 space-y-2">
                    @forelse($goal->keyResults as $kr)
                        <div class="ml-3 border-l-2 border-purple-200">
                            <div class="bg-purple-50/50 rounded-r border border-l-0 border-purple-100 -ml-0.5">
                                <div class="flex items-center gap-2 px-2.5 py-1.5">
                                    <div class="w-2 h-2 rounded bg-purple-500 flex-shrink-0"></div>

                                    @if($editingKrId === $kr->id)
                                        <div class="flex-1 space-y-1.5">
                                            <input type="text" wire:model="editData.title"
                                                class="w-full px-2 py-1 border border-purple-300 rounded text-xs" autofocus>
                                            <div class="grid grid-cols-3 gap-2">
                                                <div>
                                                    <label class="block text-[10px] text-purple-500 mb-0.5">Current</label>
                                                    <input type="number" wire:model="editData.current_value" step="0.01"
                                                        class="w-full px-1.5 py-0.5 text-xs border border-purple-300 rounded">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] text-purple-500 mb-0.5">Target</label>
                                                    <input type="number" wire:model="editData.target_value" step="0.01"
                                                        class="w-full px-1.5 py-0.5 text-xs border border-purple-300 rounded">
                                                </div>
                                                <div>
                                                    <label class="block text-[10px] text-purple-500 mb-0.5">Unit</label>
                                                    <input type="text" wire:model="editData.unit"
                                                        class="w-full px-1.5 py-0.5 text-xs border border-purple-300 rounded">
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button wire:click="saveKr" class="text-purple-600 hover:text-purple-700 text-xs font-medium">Save</button>
                                                <button wire:click="cancelEditKr" class="text-gray-500 hover:text-gray-700 text-xs">Cancel</button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="flex-1 min-w-0">
                                            <span wire:click="startEditKr({{ $kr->id }})"
                                                class="text-xs font-medium text-purple-900 cursor-pointer hover:text-purple-600 transition-colors">
                                                {{ $kr->title }}
                                            </span>
                                            <div class="flex items-center gap-2 mt-0.5 text-[10px] text-purple-600">
                                                <span>{{ $kr->formatted_value }} / {{ $kr->formatted_target }}</span>
                                                @if($kr->due_date)
                                                    <span>Due {{ $kr->due_date->format('M j') }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <span class="text-[10px] text-purple-400 font-medium uppercase tracking-wide">Activity</span>
                                    @endif

                                    {{-- Progress Bar --}}
                                    @php $krProgress = $kr->calculateProgress(); @endphp
                                    <div class="text-right min-w-[48px]">
                                        <div class="text-xs font-medium text-gray-700">{{ number_format($krProgress, 0) }}%</div>
                                        <div class="w-12 h-1 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full transition-all {{ $krProgress >= 100 ? 'bg-green-500' : ($krProgress >= 50 ? 'bg-purple-500' : 'bg-yellow-500') }}"
                                                style="width: {{ min($krProgress, 100) }}%"></div>
                                        </div>
                                    </div>

                                    {{-- Status Badge --}}
                                    @php
                                        $krStatusConfig = [
                                            'completed' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'Done'],
                                            'on_track' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'On Track'],
                                            'in_progress' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Active'],
                                            'at_risk' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'At Risk'],
                                            'not_started' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => 'Pending'],
                                        ];
                                        $krConfig = $krStatusConfig[$kr->status] ?? $krStatusConfig['not_started'];
                                    @endphp
                                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-medium {{ $krConfig['bg'] }} {{ $krConfig['text'] }}">
                                        {{ $krConfig['label'] }}
                                    </span>

                                    <button wire:click="deleteKr({{ $kr->id }})"
                                        wire:confirm="Delete this activity?"
                                        class="p-0.5 text-purple-300 hover:text-red-500 transition-colors">
                                        <x-icon name="x" class="w-3.5 h-3.5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="ml-3 py-2 text-center text-xs text-gray-400">
                            No activities yet
                        </div>
                    @endforelse

                    {{-- Add Key Activity --}}
                    @if($addingKrToGoalId === $goal->id)
                        <div class="ml-3 p-2.5 bg-purple-50 rounded border border-dashed border-purple-300">
                            <div class="flex items-center gap-1.5 mb-2">
                                <div class="w-2 h-2 rounded bg-purple-400"></div>
                                <span class="text-xs font-medium text-purple-900">New Key Activity</span>
                            </div>
                            <div class="space-y-2">
                                <input type="text" wire:model="newKrTitle"
                                    class="w-full px-2 py-1 text-xs border border-purple-300 rounded bg-white"
                                    placeholder="Activity title..." autofocus>
                                <div class="grid grid-cols-4 gap-2">
                                    <div>
                                        <label class="block text-[10px] text-purple-600 mb-0.5">Type</label>
                                        <select wire:model="newKrMetricType"
                                            class="w-full px-1.5 py-1 text-xs border border-purple-300 rounded bg-white">
                                            <option value="percentage">%</option>
                                            <option value="number">#</option>
                                            <option value="currency">$</option>
                                            <option value="boolean">Y/N</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-purple-600 mb-0.5">Start</label>
                                        <input type="number" wire:model="newKrStartingValue" step="0.01"
                                            class="w-full px-1.5 py-1 text-xs border border-purple-300 rounded bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-purple-600 mb-0.5">Target</label>
                                        <input type="number" wire:model="newKrTargetValue" step="0.01"
                                            class="w-full px-1.5 py-1 text-xs border border-purple-300 rounded bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] text-purple-600 mb-0.5">Unit</label>
                                        <input type="text" wire:model="newKrUnit"
                                            class="w-full px-1.5 py-1 text-xs border border-purple-300 rounded bg-white"
                                            placeholder="%">
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button wire:click="addKeyResult"
                                        class="px-2 py-1 bg-purple-500 text-white rounded text-xs font-medium hover:bg-purple-600">
                                        Add
                                    </button>
                                    <button wire:click="cancelAddKr"
                                        class="px-2 py-1 text-gray-500 hover:text-gray-700 text-xs">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    @else
                        <button wire:click="showAddKrForm({{ $goal->id }})"
                            class="ml-3 flex items-center gap-1.5 px-2 py-1 text-xs text-purple-600 hover:text-purple-700 hover:bg-purple-50 rounded transition-colors">
                            <x-icon name="plus" class="w-3.5 h-3.5" />
                            Add Activity
                        </button>
                    @endif
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-8 bg-gray-50 rounded-lg border-2 border-dashed border-gray-200">
            <div class="w-10 h-10 mx-auto mb-2 rounded-full bg-green-100 flex items-center justify-center">
                <x-icon name="flag" class="w-5 h-5 text-green-500" />
            </div>
            <h3 class="text-xs font-medium text-gray-900 mb-1">No focus areas yet</h3>
            <p class="text-[10px] text-gray-500 mb-3">Start by adding a focus area</p>
            <button wire:click="showAddGoalForm" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-green-500 text-white rounded text-xs font-medium hover:bg-green-600 transition-colors">
                <x-icon name="plus" class="w-3.5 h-3.5" />
                Add Focus Area
            </button>
        </div>
    @endforelse

    {{-- Progress Info --}}
    <div class="p-2.5 bg-gray-50 rounded border border-gray-200">
        <div class="flex items-start gap-2">
            <x-icon name="information-circle" class="w-3.5 h-3.5 text-gray-400 mt-0.5" />
            <div class="text-[10px] text-gray-500">
                <span class="font-medium text-gray-600">Progress:</span>
                Activity progress = (Current - Start) / (Target - Start). Focus Area progress = average of activities.
            </div>
        </div>
    </div>
</div>
