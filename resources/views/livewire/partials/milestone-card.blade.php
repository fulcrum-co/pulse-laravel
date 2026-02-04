@php
    $isEditing = $editingMilestoneId === $milestone->id;
@endphp

<div class="bg-white rounded border {{ $isOverdue ? 'border-red-200' : 'border-gray-200' }} p-2.5">
    @if($isEditing)
        {{-- Edit Mode --}}
        <div class="space-y-2">
            <input type="text" wire:model="editData.title"
                class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-pulse-orange-500">
            <textarea wire:model="editData.description" rows="2"
                class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-pulse-orange-500"
                placeholder="Description"></textarea>
            <div class="grid grid-cols-2 gap-2">
                <input type="date" wire:model="editData.due_date"
                    class="px-2 py-1.5 text-xs border border-gray-300 rounded">
                <select wire:model="editData.goal_id"
                    class="px-2 py-1.5 text-xs border border-gray-300 rounded">
                    <option value="">No specific area</option>
                    @foreach($goals as $goal)
                        <option value="{{ $goal->id }}">{{ $goal->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button wire:click="saveMilestone"
                    class="px-2 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
                    Save
                </button>
                <button wire:click="cancelEdit"
                    class="px-2 py-1 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50">
                    Cancel
                </button>
            </div>
        </div>
    @else
        {{-- View Mode --}}
        <div class="flex items-start gap-2.5">
            {{-- Checkbox --}}
            <div class="pt-0.5">
                @if($milestone->status === 'completed')
                    <div class="w-4 h-4 rounded-full bg-green-500 flex items-center justify-center">
                        <x-icon name="check" class="w-2.5 h-2.5 text-white" />
                    </div>
                @else
                    <button wire:click="markComplete({{ $milestone->id }})"
                        class="w-4 h-4 rounded-full border-2 {{ $isOverdue ? 'border-red-400' : 'border-gray-300' }} hover:border-green-500 hover:bg-green-50 transition-colors">
                    </button>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between">
                    <div>
                        <h5 class="text-xs font-medium text-gray-900 {{ $milestone->status === 'completed' ? 'line-through text-gray-400' : '' }}">
                            {{ $milestone->title }}
                        </h5>
                        @if($milestone->description)
                            <p class="text-[10px] text-gray-500 mt-0.5">{{ Str::limit($milestone->description, 80) }}</p>
                        @endif
                        <div class="flex items-center gap-2 mt-1 text-[10px]">
                            {{-- Due Date --}}
                            <span class="{{ $isOverdue ? 'text-red-600 font-medium' : 'text-gray-500' }} flex items-center gap-0.5">
                                <x-icon name="calendar" class="w-3 h-3" />
                                @if($isOverdue)
                                    {{ now()->diffInDays($milestone->due_date) }}d overdue
                                @elseif($milestone->status === 'completed')
                                    Done {{ $milestone->completed_at->format('M j') }}
                                @elseif($milestone->isDueSoon())
                                    {{ $milestone->due_date->diffInDays(now()) }}d left
                                @else
                                    {{ $milestone->due_date->format('M j') }}
                                @endif
                            </span>

                            {{-- Linked Goal --}}
                            @if($milestone->goal)
                                <span class="text-gray-400 flex items-center gap-0.5">
                                    <x-icon name="link" class="w-3 h-3" />
                                    {{ Str::limit($milestone->goal->title, 15) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Status & Actions --}}
                    <div class="flex items-center gap-1.5">
                        @if($milestone->status !== 'completed')
                            <span class="px-1.5 py-0.5 text-[10px] font-medium rounded-full
                                {{ match($milestone->status) {
                                    'in_progress' => 'bg-blue-100 text-blue-700',
                                    'missed' => 'bg-red-100 text-red-700',
                                    default => $isOverdue ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600'
                                } }}">
                                @if($isOverdue && $milestone->status !== 'missed')
                                    Overdue
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $milestone->status)) }}
                                @endif
                            </span>
                        @endif

                        {{-- Actions Dropdown --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="p-0.5 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="ellipsis-vertical" class="w-3.5 h-3.5" />
                            </button>
                            <div x-show="open" @click.away="open = false"
                                class="absolute right-0 mt-1 w-24 bg-white rounded shadow-lg border border-gray-200 py-0.5 z-10">
                                <button wire:click="startEdit({{ $milestone->id }})" @click="open = false"
                                    class="w-full px-2 py-1 text-left text-[10px] hover:bg-gray-50 flex items-center gap-1.5">
                                    <x-icon name="pencil" class="w-3 h-3" />
                                    Edit
                                </button>
                                @if($milestone->status !== 'completed')
                                    <button wire:click="markComplete({{ $milestone->id }})" @click="open = false"
                                        class="w-full px-2 py-1 text-left text-[10px] text-green-600 hover:bg-green-50 flex items-center gap-1.5">
                                        <x-icon name="check" class="w-3 h-3" />
                                        Done
                                    </button>
                                @endif
                                <button wire:click="deleteMilestone({{ $milestone->id }})"
                                    wire:confirm="Delete this milestone?"
                                    @click="open = false"
                                    class="w-full px-2 py-1 text-left text-[10px] text-red-600 hover:bg-red-50 flex items-center gap-1.5">
                                    <x-icon name="trash" class="w-3 h-3" />
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
