@php
    $isEditing = $editingMilestoneId === $milestone->id;
@endphp

<div class="bg-white rounded-lg border {{ $isOverdue ? 'border-red-200' : 'border-gray-200' }} p-4">
    @if($isEditing)
        {{-- Edit Mode --}}
        <div class="space-y-3">
            <input type="text" wire:model="editData.title"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500">
            <textarea wire:model="editData.description" rows="2"
                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500"
                placeholder="Description"></textarea>
            <div class="grid grid-cols-2 gap-3">
                <input type="date" wire:model="editData.due_date"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
                <select wire:model="editData.goal_id"
                    class="px-3 py-2 text-sm border border-gray-300 rounded-lg">
                    <option value="">No specific goal</option>
                    @foreach($goals as $goal)
                        <option value="{{ $goal->id }}">{{ $goal->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="saveMilestone"
                    class="px-3 py-1.5 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                    Save
                </button>
                <button wire:click="cancelEdit"
                    class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </button>
            </div>
        </div>
    @else
        {{-- View Mode --}}
        <div class="flex items-start gap-4">
            {{-- Checkbox --}}
            <div class="pt-0.5">
                @if($milestone->status === 'completed')
                    <div class="w-5 h-5 rounded-full bg-green-500 flex items-center justify-center">
                        <x-icon name="check" class="w-3 h-3 text-white" />
                    </div>
                @else
                    <button wire:click="markComplete({{ $milestone->id }})"
                        class="w-5 h-5 rounded-full border-2 {{ $isOverdue ? 'border-red-400' : 'border-gray-300' }} hover:border-green-500 hover:bg-green-50 transition-colors">
                    </button>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                    <div>
                        <h5 class="font-medium text-gray-900 {{ $milestone->status === 'completed' ? 'line-through text-gray-400' : '' }}">
                            {{ $milestone->title }}
                        </h5>
                        @if($milestone->description)
                            <p class="text-sm text-gray-500 mt-0.5">{{ $milestone->description }}</p>
                        @endif
                        <div class="flex items-center gap-3 mt-2 text-xs">
                            {{-- Due Date --}}
                            <span class="{{ $isOverdue ? 'text-red-600 font-medium' : 'text-gray-500' }} flex items-center gap-1">
                                <x-icon name="calendar" class="w-3.5 h-3.5" />
                                @if($isOverdue)
                                    {{ now()->diffInDays($milestone->due_date) }} days overdue
                                @elseif($milestone->status === 'completed')
                                    Completed {{ $milestone->completed_at->format('M j') }}
                                @elseif($milestone->isDueSoon())
                                    Due in {{ $milestone->due_date->diffInDays(now()) }} days
                                @else
                                    {{ $milestone->due_date->format('M j, Y') }}
                                @endif
                            </span>

                            {{-- Linked Goal --}}
                            @if($milestone->goal)
                                <span class="text-gray-400 flex items-center gap-1">
                                    <x-icon name="link" class="w-3.5 h-3.5" />
                                    {{ Str::limit($milestone->goal->title, 20) }}
                                </span>
                            @endif

                            {{-- Completed By --}}
                            @if($milestone->completedByUser)
                                <span class="text-green-600 flex items-center gap-1">
                                    <x-icon name="user" class="w-3.5 h-3.5" />
                                    {{ $milestone->completedByUser->first_name }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Status & Actions --}}
                    <div class="flex items-center gap-2">
                        @if($milestone->status !== 'completed')
                            {{-- Status Badge --}}
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                {{ match($milestone->status) {
                                    'in_progress' => 'bg-blue-100 text-blue-700',
                                    'missed' => 'bg-red-100 text-red-700',
                                    default => $isOverdue ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700'
                                } }}">
                                @if($isOverdue && $milestone->status !== 'missed')
                                    Overdue
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                                @endif
                            </span>

                            {{-- Quick Action Button --}}
                            @if($milestone->status === 'pending')
                                <button wire:click="markInProgress({{ $milestone->id }})"
                                    class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                    Start
                                </button>
                            @endif
                        @endif

                        {{-- Actions Dropdown --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="ellipsis-vertical" class="w-4 h-4" />
                            </button>
                            <div x-show="open" @click.away="open = false"
                                class="absolute right-0 mt-1 w-32 bg-white rounded-lg shadow-lg border border-gray-200 py-1 z-10">
                                <button wire:click="startEdit({{ $milestone->id }})" @click="open = false"
                                    class="w-full px-3 py-1.5 text-left text-sm hover:bg-gray-50 flex items-center gap-2">
                                    <x-icon name="pencil" class="w-3.5 h-3.5" />
                                    Edit
                                </button>
                                @if($milestone->status !== 'completed')
                                    <button wire:click="markComplete({{ $milestone->id }})" @click="open = false"
                                        class="w-full px-3 py-1.5 text-left text-sm text-green-600 hover:bg-green-50 flex items-center gap-2">
                                        <x-icon name="check" class="w-3.5 h-3.5" />
                                        Complete
                                    </button>
                                @endif
                                <button wire:click="deleteMilestone({{ $milestone->id }})"
                                    wire:confirm="Delete this milestone?"
                                    @click="open = false"
                                    class="w-full px-3 py-1.5 text-left text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                    <x-icon name="trash" class="w-3.5 h-3.5" />
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
