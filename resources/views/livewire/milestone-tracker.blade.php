<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-semibold text-gray-900">Milestones</h3>
            <p class="text-xs text-gray-500">Track key checkpoints and deadlines</p>
        </div>
        <button wire:click="showForm"
            class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
            <x-icon name="plus" class="w-3.5 h-3.5 mr-1" />
            Add Milestone
        </button>
    </div>

    {{-- Add Milestone Form --}}
    @if($showAddForm)
        <div class="mb-4 bg-white rounded border border-gray-200 p-3">
            <h4 class="text-xs font-medium text-gray-900 mb-2">New Milestone</h4>
            <div class="space-y-2">
                <div>
                    <input type="text" wire:model="newMilestoneTitle"
                        class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-pulse-orange-500"
                        placeholder="Milestone title...">
                    @error('newMilestoneTitle') <p class="text-red-500 text-[10px] mt-0.5">{{ $message }}</p> @enderror
                </div>
                <div>
                    <textarea wire:model="newMilestoneDescription" rows="2"
                        class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-pulse-orange-500"
                        placeholder="Description (optional)"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Due Date *</label>
                        <input type="date" wire:model="newMilestoneDueDate"
                            class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-pulse-orange-500">
                        @error('newMilestoneDueDate') <p class="text-red-500 text-[10px] mt-0.5">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Link to Focus Area</label>
                        <select wire:model="newMilestoneGoalId"
                            class="w-full px-2.5 py-1.5 text-xs border border-gray-300 rounded focus:ring-1 focus:ring-pulse-orange-500">
                            <option value="">No specific area</option>
                            @foreach($goals as $goal)
                                <option value="{{ $goal->id }}">{{ $goal->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="addMilestone"
                        class="px-2.5 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
                        Add
                    </button>
                    <button wire:click="cancelForm"
                        class="px-2.5 py-1 text-xs font-medium text-gray-600 bg-white border border-gray-300 rounded hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Empty State --}}
    @if($milestones->isEmpty())
        <div class="text-center py-8 bg-white rounded border border-gray-200">
            <x-icon name="flag" class="w-8 h-8 text-gray-300 mx-auto mb-2" />
            <p class="text-xs text-gray-500">No milestones yet.</p>
            <p class="text-[10px] text-gray-400 mt-0.5">Add milestones to track key checkpoints.</p>
        </div>
    @else
        {{-- Overdue Section --}}
        @if($overdue->isNotEmpty())
            <div class="mb-4">
                <h4 class="text-xs font-medium text-red-700 mb-2 flex items-center gap-1">
                    <x-icon name="exclamation-circle" class="w-3.5 h-3.5" />
                    Overdue ({{ $overdue->count() }})
                </h4>
                <div class="space-y-1.5">
                    @foreach($overdue as $milestone)
                        @include('livewire.partials.milestone-card', ['milestone' => $milestone, 'isOverdue' => true])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Upcoming Section --}}
        @if($upcoming->isNotEmpty())
            <div class="mb-4">
                <h4 class="text-xs font-medium text-gray-700 mb-2 flex items-center gap-1">
                    <x-icon name="clock" class="w-3.5 h-3.5" />
                    Upcoming ({{ $upcoming->count() }})
                </h4>
                <div class="space-y-1.5">
                    @foreach($upcoming as $milestone)
                        @include('livewire.partials.milestone-card', ['milestone' => $milestone, 'isOverdue' => false])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Completed Section --}}
        @if($completed->isNotEmpty())
            <div>
                <h4 class="text-xs font-medium text-green-700 mb-2 flex items-center gap-1">
                    <x-icon name="check-circle" class="w-3.5 h-3.5" />
                    Completed ({{ $completed->count() }})
                </h4>
                <div class="space-y-1.5">
                    @foreach($completed as $milestone)
                        @include('livewire.partials.milestone-card', ['milestone' => $milestone, 'isOverdue' => false])
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
