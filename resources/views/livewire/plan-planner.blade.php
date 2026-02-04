<div class="space-y-4">
    {{-- Add Focus Area Button --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <span class="flex items-center gap-1">
                <span class="w-3 h-3 rounded bg-blue-500"></span> Focus Area
            </span>
            <span class="text-gray-300">→</span>
            <span class="flex items-center gap-1">
                <span class="w-3 h-3 rounded bg-orange-400"></span> Objective
            </span>
            <span class="text-gray-300">→</span>
            <span class="flex items-center gap-1">
                <span class="w-3 h-3 rounded bg-gray-400"></span> Activity
            </span>
        </div>

        @if($addingTo === 'plan')
            <div class="flex items-center gap-2">
                <input type="text" wire:model="newFocusAreaTitle" wire:keydown.enter="saveFocusArea" wire:keydown.escape="cancelAdd"
                    class="px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm w-64"
                    placeholder="Focus area title..." autofocus>
                <button wire:click="saveFocusArea" class="px-3 py-1.5 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600">Add</button>
                <button wire:click="cancelAdd" class="px-3 py-1.5 text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
            </div>
        @else
            <button wire:click="startAddFocusArea" class="flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-lg text-sm font-medium transition-colors">
                <x-icon name="plus" class="w-4 h-4" />
                Add Focus Area
            </button>
        @endif
    </div>

    {{-- Focus Areas List --}}
    @forelse($focusAreas as $focusArea)
        <div class="bg-white rounded-xl border-2 border-blue-200 overflow-hidden shadow-sm">
            {{-- Focus Area Header --}}
            <div class="bg-blue-50 border-b border-blue-200">
                <div class="flex items-center gap-3 px-4 py-3">
                    <button wire:click="toggleFocusArea({{ $focusArea->id }})" class="text-blue-400 hover:text-blue-600 transition-colors">
                        <x-icon name="{{ ($expandedFocusAreas[$focusArea->id] ?? true) ? 'chevron-down' : 'chevron-right' }}" class="w-5 h-5" />
                    </button>

                    <div class="w-4 h-4 rounded bg-blue-500 flex-shrink-0"></div>

                    @if($editingType === 'focus_area' && $editingId === $focusArea->id)
                        <input type="text" wire:model="editingTitle" wire:keydown.enter="saveEdit" wire:keydown.escape="cancelEdit"
                            class="flex-1 px-2 py-1 border border-blue-300 rounded text-sm font-medium" autofocus>
                        <button wire:click="saveEdit" class="text-blue-600 hover:text-blue-700 text-sm font-medium">Save</button>
                        <button wire:click="cancelEdit" class="text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                    @else
                        <span wire:click="startEdit('focus_area', {{ $focusArea->id }}, '{{ addslashes($focusArea->title) }}')"
                            class="flex-1 font-semibold text-blue-900 cursor-pointer hover:text-blue-600 transition-colors">
                            {{ $focusArea->title }}
                        </span>

                        <span class="text-xs text-blue-500 font-medium">FOCUS AREA</span>
                    @endif

                    {{-- Status Badge --}}
                    @php
                        $statusConfig = [
                            'on_track' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'label' => 'On Track'],
                            'at_risk' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'At Risk'],
                            'off_track' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Off Track'],
                            'not_started' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600', 'label' => 'Not Started'],
                        ];
                        $config = $statusConfig[$focusArea->status] ?? $statusConfig['not_started'];
                    @endphp
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="px-2.5 py-1 rounded-full text-xs font-medium {{ $config['bg'] }} {{ $config['text'] }} hover:opacity-80 transition-opacity">
                            {{ $config['label'] }}
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-32 bg-white border border-gray-200 rounded-lg shadow-lg z-20">
                            @foreach($statusConfig as $status => $cfg)
                                <button wire:click="updateStatus('focus_area', {{ $focusArea->id }}, '{{ $status }}')" @click="open = false"
                                    class="block w-full text-left px-3 py-2 text-xs hover:bg-gray-50 {{ $focusArea->status === $status ? 'bg-gray-50 font-medium' : '' }}">
                                    {{ $cfg['label'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- Actions Menu --}}
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="p-1.5 text-blue-400 hover:text-blue-600 hover:bg-blue-100 rounded transition-colors">
                            <x-icon name="dots-vertical" class="w-4 h-4" />
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-40 bg-white border border-gray-200 rounded-lg shadow-lg z-20">
                            <button wire:click="$dispatch('openSurveyAssignment', { type: 'focus_area', id: {{ $focusArea->id }} })" @click="open = false"
                                class="flex items-center gap-2 w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                <x-icon name="clipboard-list" class="w-4 h-4 text-gray-400" />
                                Assign Survey
                            </button>
                            <button wire:click="deleteFocusArea({{ $focusArea->id }})" @click="open = false"
                                wire:confirm="Delete this focus area and all its objectives and activities?"
                                class="flex items-center gap-2 w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                                <x-icon name="trash" class="w-4 h-4" />
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Objectives --}}
            @if($expandedFocusAreas[$focusArea->id] ?? true)
                <div class="p-4 space-y-3">
                    @forelse($focusArea->objectives as $objective)
                        <div class="ml-4 border-l-2 border-orange-200">
                            {{-- Objective Header --}}
                            <div class="bg-orange-50 rounded-r-lg border border-l-0 border-orange-200 -ml-0.5">
                                <div class="flex items-center gap-3 px-4 py-2.5">
                                    <button wire:click="toggleObjective({{ $objective->id }})" class="text-orange-400 hover:text-orange-600 transition-colors">
                                        <x-icon name="{{ ($expandedObjectives[$objective->id] ?? true) ? 'chevron-down' : 'chevron-right' }}" class="w-4 h-4" />
                                    </button>

                                    <div class="w-3 h-3 rounded bg-orange-400 flex-shrink-0"></div>

                                    @if($editingType === 'objective' && $editingId === $objective->id)
                                        <input type="text" wire:model="editingTitle" wire:keydown.enter="saveEdit" wire:keydown.escape="cancelEdit"
                                            class="flex-1 px-2 py-1 border border-orange-300 rounded text-sm" autofocus>
                                        <button wire:click="saveEdit" class="text-orange-600 hover:text-orange-700 text-sm font-medium">Save</button>
                                        <button wire:click="cancelEdit" class="text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                                    @else
                                        <span wire:click="startEdit('objective', {{ $objective->id }}, '{{ addslashes($objective->title) }}')"
                                            class="flex-1 font-medium text-orange-900 cursor-pointer hover:text-orange-600 transition-colors">
                                            {{ $objective->title }}
                                        </span>

                                        <span class="text-xs text-orange-500 font-medium">OBJECTIVE</span>
                                    @endif

                                    @if($objective->start_date && $objective->end_date)
                                        <span class="text-xs text-orange-400">
                                            {{ $objective->start_date->format('M j') }} - {{ $objective->end_date->format('M j') }}
                                        </span>
                                    @endif

                                    {{-- Status --}}
                                    @php $objConfig = $statusConfig[$objective->status] ?? $statusConfig['not_started']; @endphp
                                    <div class="relative" x-data="{ open: false }">
                                        <button @click="open = !open" class="px-2 py-0.5 rounded-full text-xs font-medium {{ $objConfig['bg'] }} {{ $objConfig['text'] }} hover:opacity-80">
                                            {{ $objConfig['label'] }}
                                        </button>
                                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-32 bg-white border border-gray-200 rounded-lg shadow-lg z-20">
                                            @foreach($statusConfig as $status => $cfg)
                                                <button wire:click="updateStatus('objective', {{ $objective->id }}, '{{ $status }}')" @click="open = false"
                                                    class="block w-full text-left px-3 py-2 text-xs hover:bg-gray-50">
                                                    {{ $cfg['label'] }}
                                                </button>
                                            @endforeach
                                        </div>
                                    </div>

                                    <button wire:click="deleteObjective({{ $objective->id }})"
                                        wire:confirm="Delete this objective and all its activities?"
                                        class="p-1 text-orange-300 hover:text-red-500 transition-colors">
                                        <x-icon name="x" class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>

                            {{-- Activities --}}
                            @if($expandedObjectives[$objective->id] ?? true)
                                <div class="ml-6 mt-2 space-y-1.5">
                                    @foreach($objective->activities as $activity)
                                        <div class="flex items-center gap-3 px-3 py-2 bg-gray-50 rounded-lg border border-gray-200 hover:border-gray-300 transition-colors group">
                                            <div class="w-2.5 h-2.5 rounded bg-gray-400 flex-shrink-0"></div>

                                            @if($editingType === 'activity' && $editingId === $activity->id)
                                                <input type="text" wire:model="editingTitle" wire:keydown.enter="saveEdit" wire:keydown.escape="cancelEdit"
                                                    class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm" autofocus>
                                                <button wire:click="saveEdit" class="text-gray-600 hover:text-gray-700 text-sm font-medium">Save</button>
                                                <button wire:click="cancelEdit" class="text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                                            @else
                                                <span wire:click="startEdit('activity', {{ $activity->id }}, '{{ addslashes($activity->title) }}')"
                                                    class="flex-1 text-sm text-gray-700 cursor-pointer hover:text-gray-900 transition-colors">
                                                    {{ $activity->title }}
                                                </span>

                                                <span class="text-xs text-gray-400 font-medium opacity-0 group-hover:opacity-100 transition-opacity">ACTIVITY</span>
                                            @endif

                                            @if($activity->start_date && $activity->end_date)
                                                <span class="text-xs text-gray-400">
                                                    {{ $activity->start_date->format('M j') }} - {{ $activity->end_date->format('M j') }}
                                                </span>
                                            @endif

                                            {{-- Status --}}
                                            @php $actConfig = $statusConfig[$activity->status] ?? $statusConfig['not_started']; @endphp
                                            <div class="relative" x-data="{ open: false }">
                                                <button @click="open = !open" class="px-2 py-0.5 rounded-full text-xs font-medium {{ $actConfig['bg'] }} {{ $actConfig['text'] }} hover:opacity-80">
                                                    {{ $actConfig['label'] }}
                                                </button>
                                                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-1 w-32 bg-white border border-gray-200 rounded-lg shadow-lg z-20">
                                                    @foreach($statusConfig as $status => $cfg)
                                                        <button wire:click="updateStatus('activity', {{ $activity->id }}, '{{ $status }}')" @click="open = false"
                                                            class="block w-full text-left px-3 py-2 text-xs hover:bg-gray-50">
                                                            {{ $cfg['label'] }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <button wire:click="deleteActivity({{ $activity->id }})"
                                                wire:confirm="Delete this activity?"
                                                class="p-1 text-gray-300 hover:text-red-500 opacity-0 group-hover:opacity-100 transition-all">
                                                <x-icon name="x" class="w-3.5 h-3.5" />
                                            </button>
                                        </div>
                                    @endforeach

                                    {{-- Add Activity --}}
                                    @if($addingTo === 'objective_' . $objective->id)
                                        <div class="flex items-center gap-2 px-3 py-2 bg-gray-100 rounded-lg border border-dashed border-gray-300">
                                            <div class="w-2.5 h-2.5 rounded bg-gray-300 flex-shrink-0"></div>
                                            <input type="text" wire:model="newActivityTitle" wire:keydown.enter="saveActivity({{ $objective->id }})" wire:keydown.escape="cancelAdd"
                                                class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm bg-white" placeholder="Activity title..." autofocus>
                                            <button wire:click="saveActivity({{ $objective->id }})" class="px-2 py-1 bg-gray-600 text-white rounded text-xs font-medium hover:bg-gray-700">Add</button>
                                            <button wire:click="cancelAdd" class="text-gray-500 hover:text-gray-700 text-xs">Cancel</button>
                                        </div>
                                    @else
                                        <button wire:click="startAddActivity({{ $objective->id }})"
                                            class="flex items-center gap-2 px-3 py-1.5 text-xs text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg transition-colors w-full">
                                            <x-icon name="plus" class="w-3.5 h-3.5" />
                                            Add Activity
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="ml-4 py-4 text-center text-sm text-gray-400">
                            No objectives yet. Add one below.
                        </div>
                    @endforelse

                    {{-- Add Objective --}}
                    @if($addingTo === 'focus_area_' . $focusArea->id)
                        <div class="ml-4 flex items-center gap-2 p-3 bg-orange-50 rounded-lg border border-dashed border-orange-300">
                            <div class="w-3 h-3 rounded bg-orange-300 flex-shrink-0"></div>
                            <input type="text" wire:model="newObjectiveTitle" wire:keydown.enter="saveObjective({{ $focusArea->id }})" wire:keydown.escape="cancelAdd"
                                class="flex-1 px-2 py-1 border border-orange-300 rounded text-sm bg-white" placeholder="Objective title..." autofocus>
                            <button wire:click="saveObjective({{ $focusArea->id }})" class="px-2 py-1 bg-orange-500 text-white rounded text-xs font-medium hover:bg-orange-600">Add</button>
                            <button wire:click="cancelAdd" class="text-gray-500 hover:text-gray-700 text-xs">Cancel</button>
                        </div>
                    @else
                        <button wire:click="startAddObjective({{ $focusArea->id }})"
                            class="ml-4 flex items-center gap-2 px-3 py-2 text-sm text-orange-600 hover:text-orange-700 hover:bg-orange-50 rounded-lg transition-colors">
                            <x-icon name="plus" class="w-4 h-4" />
                            Add Objective
                        </button>
                    @endif
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-16 bg-gray-50 rounded-xl border-2 border-dashed border-gray-200">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-blue-100 flex items-center justify-center">
                <x-icon name="clipboard-document-list" class="w-8 h-8 text-blue-500" />
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-1">No focus areas yet</h3>
            <p class="text-sm text-gray-500 mb-4">Start building your plan by adding a focus area</p>
            <button wire:click="startAddFocusArea" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-500 text-white rounded-lg text-sm font-medium hover:bg-blue-600 transition-colors">
                <x-icon name="plus" class="w-4 h-4" />
                Add Focus Area
            </button>
        </div>
    @endforelse
</div>
