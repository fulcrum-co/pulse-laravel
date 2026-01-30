<div>
    <!-- Action Header -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex gap-2 overflow-x-auto pb-2">
            @foreach(['all' => 'All', 'pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed'] as $status => $label)
            <button
                wire:click="setFilterStatus('{{ $status }}')"
                class="px-3 py-1 text-sm font-medium rounded-lg whitespace-nowrap transition-colors {{ $filterStatus === $status ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
            >
                {{ $label }}
            </button>
            @endforeach
        </div>
        <button
            wire:click="openAssignModal"
            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Assign Resource
        </button>
    </div>

    <!-- Resource Assignments List -->
    <div class="space-y-3">
        @forelse($assignments as $assignment)
        <div class="border border-gray-200 rounded-lg overflow-hidden {{ $expandedAssignmentId === $assignment->id ? 'ring-2 ring-pulse-orange-200' : '' }}">
            <!-- Header Row (Clickable) -->
            <button
                wire:click="toggleExpand({{ $assignment->id }})"
                class="w-full flex items-center justify-between p-4 bg-gray-50 hover:bg-gray-100 transition-colors text-left"
            >
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center
                        {{ $assignment->status === 'completed' ? 'bg-green-100' : ($assignment->status === 'in_progress' ? 'bg-yellow-100' : 'bg-gray-100') }}">
                        @if($assignment->status === 'completed')
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        @elseif($assignment->status === 'in_progress')
                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        @else
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        @endif
                    </div>
                    <div>
                        <div class="text-sm font-medium text-gray-900">{{ $assignment->resource->title ?? 'Unknown Resource' }}</div>
                        <div class="text-xs text-gray-500">
                            Assigned {{ $assignment->assigned_at?->format('M d, Y') ?? 'Unknown' }}
                            @if($assignment->assigner)
                            <span class="text-gray-400">by {{ $assignment->assigner->name ?? $assignment->assigner->first_name }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Progress Bar -->
                    <div class="w-24 hidden sm:block">
                        <div class="flex items-center gap-2">
                            <div class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-pulse-orange-500 transition-all" style="width: {{ $assignment->progress_percent ?? 0 }}%"></div>
                            </div>
                            <span class="text-xs text-gray-600 w-8">{{ $assignment->progress_percent ?? 0 }}%</span>
                        </div>
                    </div>
                    <span class="px-2 py-0.5 text-xs rounded-full
                        {{ $assignment->status === 'completed' ? 'bg-green-100 text-green-700' : ($assignment->status === 'in_progress' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                        {{ ucfirst(str_replace('_', ' ', $assignment->status ?? 'pending')) }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform {{ $expandedAssignmentId === $assignment->id ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </div>
            </button>

            <!-- Expanded Content -->
            @if($expandedAssignmentId === $assignment->id)
            <div class="border-t border-gray-200 p-4 bg-white">
                <!-- Resource Details -->
                <div class="mb-4">
                    @if($assignment->resource)
                    <div class="flex items-start gap-4">
                        @if($assignment->resource->thumbnail_url)
                        <img src="{{ $assignment->resource->thumbnail_url }}" alt="" class="w-16 h-16 rounded-lg object-cover">
                        @endif
                        <div class="flex-1">
                            <p class="text-sm text-gray-600 mb-2">{{ $assignment->resource->description ?? 'No description available.' }}</p>
                            <div class="flex flex-wrap gap-2 text-xs">
                                @if($assignment->resource->resource_type)
                                <span class="px-2 py-0.5 bg-blue-100 text-blue-700 rounded">{{ ucfirst($assignment->resource->resource_type) }}</span>
                                @endif
                                @if($assignment->resource->category)
                                <span class="px-2 py-0.5 bg-purple-100 text-purple-700 rounded">{{ $assignment->resource->category }}</span>
                                @endif
                                @if($assignment->resource->estimated_duration_minutes)
                                <span class="px-2 py-0.5 bg-gray-100 text-gray-600 rounded">{{ $assignment->resource->estimated_duration_minutes }} min</span>
                                @endif
                            </div>
                            @if($assignment->resource->url)
                            <a href="{{ $assignment->resource->url }}" target="_blank" class="inline-flex items-center gap-1 mt-2 text-sm text-pulse-orange-600 hover:text-pulse-orange-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                </svg>
                                Open Resource
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between border-t border-gray-100 pt-4">
                    <h4 class="text-sm font-medium text-gray-700">Progress & Status</h4>
                    @if($editingAssignmentId !== $assignment->id)
                    <div class="flex gap-2">
                        <button
                            wire:click="startEdit({{ $assignment->id }})"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-pulse-orange-600 hover:bg-pulse-orange-50 rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Update Progress
                        </button>
                        <button
                            wire:click="removeAssignment({{ $assignment->id }})"
                            wire:confirm="Are you sure you want to remove this resource assignment?"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Remove
                        </button>
                    </div>
                    @else
                    <div class="flex gap-2">
                        <button
                            wire:click="saveChanges"
                            class="inline-flex items-center gap-1 px-3 py-1.5 text-sm bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Save
                        </button>
                        <button
                            wire:click="cancelEdit"
                            class="px-3 py-1.5 text-sm text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                        >
                            Cancel
                        </button>
                    </div>
                    @endif
                </div>

                <!-- Edit Form -->
                @if($editingAssignmentId === $assignment->id)
                <div class="mt-4 p-4 bg-gray-50 rounded-lg space-y-4">
                    <!-- Progress Slider -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Progress</label>
                        <div class="flex items-center gap-4">
                            <input
                                type="range"
                                min="0"
                                max="100"
                                step="5"
                                wire:model.live="editingProgress"
                                class="flex-1 h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer"
                            >
                            <span class="text-sm font-medium text-gray-900 w-12">{{ $editingProgress }}%</span>
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <div class="flex gap-2">
                            @foreach(['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed'] as $status => $label)
                            <button
                                type="button"
                                wire:click="$set('editingStatus', '{{ $status }}')"
                                class="px-3 py-1.5 text-sm rounded-lg transition-colors {{ $editingStatus === $status ? 'bg-pulse-orange-500 text-white' : 'bg-white border border-gray-300 text-gray-600 hover:border-gray-400' }}"
                            >
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
                        <textarea
                            wire:model="editingNotes"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                            placeholder="Add notes about progress..."
                        ></textarea>
                    </div>
                </div>
                @else
                <!-- View Only -->
                @if($assignment->notes)
                <div class="mt-4 p-3 bg-gray-50 rounded-lg">
                    <p class="text-xs font-medium text-gray-500 mb-1">Notes</p>
                    <p class="text-sm text-gray-700">{{ $assignment->notes }}</p>
                </div>
                @endif

                <!-- Timestamps -->
                <div class="mt-4 flex gap-4 text-xs text-gray-500">
                    @if($assignment->started_at)
                    <span>Started: {{ $assignment->started_at->format('M d, Y') }}</span>
                    @endif
                    @if($assignment->completed_at)
                    <span>Completed: {{ $assignment->completed_at->format('M d, Y') }}</span>
                    @endif
                </div>
                @endif
            </div>
            @endif
        </div>
        @empty
        <div class="text-center py-8 text-gray-500">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <p>No resources assigned yet.</p>
            <button
                wire:click="openAssignModal"
                class="inline-flex items-center gap-1 mt-2 text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Assign a Resource
            </button>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($assignments->hasPages())
    <div class="mt-4">
        {{ $assignments->links() }}
    </div>
    @endif

    <!-- Assign Resource Modal -->
    @if($showAssignModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeAssignModal"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <!-- Modal panel -->
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Resource</h3>

                    <!-- Search -->
                    <div class="mb-4">
                        <input
                            type="text"
                            wire:model.live="searchResources"
                            placeholder="Search resources..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                        >
                    </div>

                    <!-- Resource List -->
                    <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-200">
                        @forelse($availableResources as $resource)
                        <button
                            wire:click="$set('selectedResourceId', {{ $resource->id }})"
                            class="w-full flex items-center gap-3 p-3 text-left hover:bg-gray-50 transition-colors {{ $selectedResourceId === $resource->id ? 'bg-pulse-orange-50 border-l-4 border-pulse-orange-500' : '' }}"
                        >
                            <div class="w-10 h-10 bg-gray-100 rounded flex items-center justify-center flex-shrink-0">
                                @if($resource->thumbnail_url)
                                <img src="{{ $resource->thumbnail_url }}" alt="" class="w-full h-full rounded object-cover">
                                @else
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $resource->title }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ Str::limit($resource->description, 50) }}</p>
                            </div>
                            @if($selectedResourceId === $resource->id)
                            <svg class="w-5 h-5 text-pulse-orange-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            @endif
                        </button>
                        @empty
                        <div class="p-4 text-center text-gray-500">
                            <p class="text-sm">No resources available.</p>
                        </div>
                        @endforelse
                    </div>

                    <!-- Notes -->
                    @if($selectedResourceId)
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Assignment Notes (optional)</label>
                        <textarea
                            wire:model="assignmentNotes"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                            placeholder="Add notes about why this resource was assigned..."
                        ></textarea>
                    </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="mt-5 sm:mt-6 flex gap-3">
                    <button
                        wire:click="assignResource"
                        @if(!$selectedResourceId) disabled @endif
                        class="flex-1 px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        Assign Resource
                    </button>
                    <button
                        wire:click="closeAssignModal"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
