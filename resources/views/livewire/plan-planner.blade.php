<div class="space-y-2">
    {{-- Add Focus Area Button --}}
    <div class="mb-4">
        @if($addingTo === 'strategy')
            <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                <x-icon name="globe" class="w-5 h-5 text-gray-400" />
                <input type="text" wire:model="newFocusAreaTitle" wire:keydown.enter="saveFocusArea" wire:keydown.escape="cancelAdd"
                    class="flex-1 px-3 py-1.5 border border-gray-300 rounded focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-sm"
                    placeholder="Enter focus area title..." autofocus>
                <button wire:click="saveFocusArea" class="px-3 py-1.5 bg-pulse-orange-500 text-white rounded text-sm font-medium hover:bg-pulse-orange-600">Save</button>
                <button wire:click="cancelAdd" class="px-3 py-1.5 text-gray-600 hover:text-gray-900 text-sm">Cancel</button>
            </div>
        @else
            <button wire:click="startAddFocusArea" class="flex items-center gap-2 text-pulse-orange-500 hover:text-pulse-orange-600 text-sm font-medium">
                <x-icon name="plus" class="w-4 h-4" />
                Add Focus Area
            </button>
        @endif
    </div>

    {{-- Focus Areas --}}
    @foreach($focusAreas as $focusArea)
        <div class="border border-gray-200 rounded-lg overflow-hidden">
            {{-- Focus Area Header --}}
            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border-b border-gray-200">
                <button wire:click="toggleFocusArea({{ $focusArea->id }})" class="text-gray-400 hover:text-gray-600">
                    <x-icon name="{{ ($expandedFocusAreas[$focusArea->id] ?? true) ? 'chevron-down' : 'chevron-right' }}" class="w-5 h-5" />
                </button>
                <x-icon name="globe" class="w-5 h-5 text-blue-500" />

                @if($editingType === 'focus_area' && $editingId === $focusArea->id)
                    <input type="text" wire:model="editingTitle" wire:keydown.enter="saveEdit" wire:keydown.escape="cancelEdit"
                        class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm" autofocus>
                    <button wire:click="saveEdit" class="text-pulse-orange-500 hover:text-pulse-orange-600 text-sm">Save</button>
                    <button wire:click="cancelEdit" class="text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                @else
                    <span wire:click="startEdit('focus_area', {{ $focusArea->id }}, '{{ addslashes($focusArea->title) }}')"
                        class="flex-1 font-medium text-gray-900 cursor-pointer hover:text-pulse-orange-600">
                        {{ $focusArea->title }}
                    </span>
                @endif

                <button wire:click="$dispatch('openSurveyAssignment', { type: 'focus_area', id: {{ $focusArea->id }} })"
                    class="text-sm text-gray-500 hover:text-gray-700">
                    Assigned Survey(ies) +
                </button>

                {{-- Status Badge --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-1 text-sm">
                        @php
                            $statusColors = [
                                'on_track' => 'text-green-600',
                                'at_risk' => 'text-yellow-600',
                                'off_track' => 'text-red-600',
                                'not_started' => 'text-gray-400',
                            ];
                            $statusLabels = [
                                'on_track' => 'On Track',
                                'at_risk' => 'At Risk',
                                'off_track' => 'Off Track',
                                'not_started' => 'Not Started',
                            ];
                        @endphp
                        <x-icon name="trending-up" class="w-4 h-4 {{ $statusColors[$focusArea->status] ?? 'text-gray-400' }}" />
                        <span class="{{ $statusColors[$focusArea->status] ?? 'text-gray-400' }}">{{ $statusLabels[$focusArea->status] ?? 'Unknown' }}</span>
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-32 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                        @foreach(['on_track' => 'On Track', 'at_risk' => 'At Risk', 'off_track' => 'Off Track', 'not_started' => 'Not Started'] as $status => $label)
                            <button wire:click="updateStatus('focus_area', {{ $focusArea->id }}, '{{ $status }}')" @click="open = false"
                                class="block w-full text-left px-3 py-2 text-sm hover:bg-gray-50 {{ $focusArea->status === $status ? 'bg-gray-50' : '' }}">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Actions Menu --}}
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="p-1 text-gray-400 hover:text-gray-600">
                        <x-icon name="dots-vertical" class="w-5 h-5" />
                    </button>
                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-32 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                        <button wire:click="deleteFocusArea({{ $focusArea->id }})" @click="open = false"
                            wire:confirm="Are you sure you want to delete this focus area?"
                            class="block w-full text-left px-3 py-2 text-sm text-red-600 hover:bg-red-50">
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            {{-- Objectives --}}
            @if($expandedFocusAreas[$focusArea->id] ?? true)
                <div class="bg-white">
                    @foreach($focusArea->objectives as $objective)
                        <div class="border-b border-gray-100 last:border-b-0">
                            {{-- Objective Row --}}
                            <div class="flex items-center gap-3 px-4 py-2 pl-12">
                                <button wire:click="toggleObjective({{ $objective->id }})" class="text-gray-400 hover:text-gray-600">
                                    <x-icon name="{{ ($expandedObjectives[$objective->id] ?? true) ? 'chevron-down' : 'chevron-right' }}" class="w-4 h-4" />
                                </button>
                                <div class="w-4 h-4 rounded-full border-2 border-orange-400"></div>

                                @if($editingType === 'objective' && $editingId === $objective->id)
                                    <input type="text" wire:model="editingTitle" wire:keydown.enter="saveEdit" wire:keydown.escape="cancelEdit"
                                        class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm" autofocus>
                                    <button wire:click="saveEdit" class="text-pulse-orange-500 hover:text-pulse-orange-600 text-sm">Save</button>
                                    <button wire:click="cancelEdit" class="text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                                @else
                                    <span wire:click="startEdit('objective', {{ $objective->id }}, '{{ addslashes($objective->title) }}')"
                                        class="flex-1 text-sm text-gray-900 cursor-pointer hover:text-pulse-orange-600">
                                        {{ $objective->title }}
                                    </span>
                                @endif

                                <span class="text-xs text-gray-400">
                                    @if($objective->start_date && $objective->end_date)
                                        {{ $objective->start_date->format('n/j/Y') }} - {{ $objective->end_date->format('n/j/Y') }}
                                    @endif
                                </span>

                                {{-- Status --}}
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" class="flex items-center gap-1 text-xs">
                                        <x-icon name="trending-up" class="w-3 h-3 {{ $statusColors[$objective->status] ?? 'text-gray-400' }}" />
                                        <span class="{{ $statusColors[$objective->status] ?? 'text-gray-400' }}">{{ $statusLabels[$objective->status] ?? 'Unknown' }}</span>
                                    </button>
                                    <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-32 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                                        @foreach(['on_track' => 'On Track', 'at_risk' => 'At Risk', 'off_track' => 'Off Track', 'not_started' => 'Not Started'] as $status => $label)
                                            <button wire:click="updateStatus('objective', {{ $objective->id }}, '{{ $status }}')" @click="open = false"
                                                class="block w-full text-left px-3 py-2 text-xs hover:bg-gray-50">
                                                {{ $label }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>

                                <button wire:click="deleteObjective({{ $objective->id }})"
                                    wire:confirm="Are you sure you want to delete this objective?"
                                    class="p-1 text-gray-400 hover:text-red-500">
                                    <x-icon name="x" class="w-4 h-4" />
                                </button>
                            </div>

                            {{-- Activities --}}
                            @if($expandedObjectives[$objective->id] ?? true)
                                @foreach($objective->activities as $activity)
                                    <div class="flex items-center gap-3 px-4 py-2 pl-20 bg-gray-50/50">
                                        <div class="w-3 h-3 rounded border border-gray-300"></div>

                                        @if($editingType === 'activity' && $editingId === $activity->id)
                                            <input type="text" wire:model="editingTitle" wire:keydown.enter="saveEdit" wire:keydown.escape="cancelEdit"
                                                class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm" autofocus>
                                            <button wire:click="saveEdit" class="text-pulse-orange-500 hover:text-pulse-orange-600 text-sm">Save</button>
                                            <button wire:click="cancelEdit" class="text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                                        @else
                                            <span wire:click="startEdit('activity', {{ $activity->id }}, '{{ addslashes($activity->title) }}')"
                                                class="flex-1 text-sm text-gray-700 cursor-pointer hover:text-pulse-orange-600">
                                                {{ $activity->title }}
                                            </span>
                                        @endif

                                        <span class="text-xs text-gray-400">
                                            @if($activity->start_date && $activity->end_date)
                                                {{ $activity->start_date->format('n/j/Y') }} - {{ $activity->end_date->format('n/j/Y') }}
                                            @endif
                                        </span>

                                        {{-- Status --}}
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="flex items-center gap-1 text-xs">
                                                <x-icon name="arrow-right" class="w-3 h-3 {{ $statusColors[$activity->status] ?? 'text-gray-400' }}" />
                                                <span class="{{ $statusColors[$activity->status] ?? 'text-gray-400' }}">{{ $statusLabels[$activity->status] ?? 'Unknown' }}</span>
                                            </button>
                                            <div x-show="open" @click.away="open = false" class="absolute right-0 mt-1 w-32 bg-white border border-gray-200 rounded-lg shadow-lg z-10">
                                                @foreach(['on_track' => 'On Track', 'at_risk' => 'At Risk', 'off_track' => 'Off Track', 'not_started' => 'Not Started'] as $status => $label)
                                                    <button wire:click="updateStatus('activity', {{ $activity->id }}, '{{ $status }}')" @click="open = false"
                                                        class="block w-full text-left px-3 py-2 text-xs hover:bg-gray-50">
                                                        {{ $label }}
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>

                                        <button wire:click="deleteActivity({{ $activity->id }})"
                                            wire:confirm="Are you sure you want to delete this activity?"
                                            class="p-1 text-gray-400 hover:text-red-500">
                                            <x-icon name="x" class="w-3 h-3" />
                                        </button>
                                    </div>
                                @endforeach

                                {{-- Add Activity --}}
                                <div class="pl-20 py-2">
                                    @if($addingTo === 'objective_' . $objective->id)
                                        <div class="flex items-center gap-2 pr-4">
                                            <input type="text" wire:model="newActivityTitle" wire:keydown.enter="saveActivity({{ $objective->id }})" wire:keydown.escape="cancelAdd"
                                                class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Activity title..." autofocus>
                                            <button wire:click="saveActivity({{ $objective->id }})" class="text-pulse-orange-500 hover:text-pulse-orange-600 text-sm">Save</button>
                                            <button wire:click="cancelAdd" class="text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                                        </div>
                                    @else
                                        <button wire:click="startAddActivity({{ $objective->id }})" class="text-xs text-pulse-orange-500 hover:text-pulse-orange-600 flex items-center gap-1">
                                            <x-icon name="plus" class="w-3 h-3" />
                                            Add Activity
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
                    @endforeach

                    {{-- Add Objective --}}
                    <div class="px-4 py-2 pl-12">
                        @if($addingTo === 'focus_area_' . $focusArea->id)
                            <div class="flex items-center gap-2">
                                <input type="text" wire:model="newObjectiveTitle" wire:keydown.enter="saveObjective({{ $focusArea->id }})" wire:keydown.escape="cancelAdd"
                                    class="flex-1 px-2 py-1 border border-gray-300 rounded text-sm" placeholder="Objective title..." autofocus>
                                <button wire:click="saveObjective({{ $focusArea->id }})" class="text-pulse-orange-500 hover:text-pulse-orange-600 text-sm">Save</button>
                                <button wire:click="cancelAdd" class="text-gray-500 hover:text-gray-700 text-sm">Cancel</button>
                            </div>
                        @else
                            <button wire:click="startAddObjective({{ $focusArea->id }})" class="text-sm text-pulse-orange-500 hover:text-pulse-orange-600 flex items-center gap-1">
                                <x-icon name="plus" class="w-4 h-4" />
                                Add Objective
                            </button>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endforeach

    @if($focusAreas->isEmpty())
        <div class="text-center py-12 text-gray-500">
            <x-icon name="clipboard-list" class="w-12 h-12 mx-auto mb-3 text-gray-300" />
            <p>No focus areas yet.</p>
            <p class="text-sm">Click "Add Focus Area" to get started.</p>
        </div>
    @endif
</div>
