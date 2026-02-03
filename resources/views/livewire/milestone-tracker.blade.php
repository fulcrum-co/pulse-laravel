<div>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-semibold text-gray-900">@term('milestones_label')</h3>
            <p class="text-sm text-gray-500">@term('milestones_subtitle_label')</p>
        </div>
        <button wire:click="showForm"
            class="inline-flex items-center px-3 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
            <x-icon name="plus" class="w-4 h-4 mr-1.5" />
            @term('add_milestone_label')
        </button>
    </div>

    {{-- Add Milestone Form --}}
    @if($showAddForm)
        <div class="mb-6 bg-white rounded-lg border border-gray-200 p-4">
            <h4 class="font-medium text-gray-900 mb-3">@term('new_milestone_label')</h4>
            <div class="space-y-3">
                <div>
                    <input type="text" wire:model="newMilestoneTitle"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500"
                        placeholder="@term('milestone_title_placeholder')">
                    @error('newMilestoneTitle') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <textarea wire:model="newMilestoneDescription" rows="2"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500"
                        placeholder="@term('milestone_description_placeholder')"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">@term('due_date_label')</label>
                        <input type="date" wire:model="newMilestoneDueDate"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500">
                        @error('newMilestoneDueDate') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">@term('link_to_goal_label')</label>
                        <select wire:model="newMilestoneGoalId"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500">
                            <option value="">@term('no_specific_goal_label')</option>
                            @foreach($goals as $goal)
                                <option value="{{ $goal->id }}">{{ $goal->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="addMilestone"
                        class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                        @term('add_milestone_label')
                    </button>
                    <button wire:click="cancelForm"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        @term('cancel_action')
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Empty State --}}
    @if($milestones->isEmpty())
        <div class="text-center py-12 bg-white rounded-lg border border-gray-200">
            <x-icon name="flag" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
            <p class="text-gray-500">@term('no_milestones_yet_label')</p>
            <p class="text-gray-400 text-sm mt-1">@term('add_milestones_help_label')</p>
        </div>
    @else
        {{-- Overdue Section --}}
        @if($overdue->isNotEmpty())
            <div class="mb-6">
                <h4 class="text-sm font-medium text-red-700 mb-3 flex items-center gap-1.5">
                    <x-icon name="exclamation-circle" class="w-4 h-4" />
                    @term('overdue_label') ({{ $overdue->count() }})
                </h4>
                <div class="space-y-2">
                    @foreach($overdue as $milestone)
                        @include('livewire.partials.milestone-card', ['milestone' => $milestone, 'isOverdue' => true])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Upcoming Section --}}
        @if($upcoming->isNotEmpty())
            <div class="mb-6">
                <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center gap-1.5">
                    <x-icon name="clock" class="w-4 h-4" />
                    @term('upcoming_label') ({{ $upcoming->count() }})
                </h4>
                <div class="space-y-2">
                    @foreach($upcoming as $milestone)
                        @include('livewire.partials.milestone-card', ['milestone' => $milestone, 'isOverdue' => false])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Completed Section --}}
        @if($completed->isNotEmpty())
            <div>
                <h4 class="text-sm font-medium text-green-700 mb-3 flex items-center gap-1.5">
                    <x-icon name="check-circle" class="w-4 h-4" />
                    @term('completed_label') ({{ $completed->count() }})
                </h4>
                <div class="space-y-2">
                    @foreach($completed as $milestone)
                        @include('livewire.partials.milestone-card', ['milestone' => $milestone, 'isOverdue' => false])
                    @endforeach
                </div>
            </div>
        @endif
    @endif
</div>
