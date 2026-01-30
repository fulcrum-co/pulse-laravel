<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <a href="{{ route('resources.index') }}?activeTab=courses" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-2">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Courses
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ $isNew ? 'Create Mini-Course' : 'Edit Course' }}</h1>
            </div>
            <div class="flex gap-3">
                @if(!$isNew && $course)
                <a href="{{ route('resources.courses.show', $course) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    Preview
                </a>
                @if($course->status === 'draft')
                <button wire:click="$set('showPublishConfirm', true)" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Publish
                </button>
                @endif
                @endif
                <button wire:click="saveCourse" class="inline-flex items-center px-4 py-2 bg-pulse-orange-500 text-white rounded-lg text-sm font-medium hover:bg-pulse-orange-600">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                    </svg>
                    Save
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Course Info Panel -->
            <div class="lg:col-span-1 space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Course Information</h2>

                    <!-- Title -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input
                            type="text"
                            wire:model="title"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                            placeholder="Course title"
                        >
                        @error('title') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            wire:model="description"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                            placeholder="Brief description of the course"
                        ></textarea>
                        @error('description') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Course Type -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Course Type</label>
                        <select wire:model="courseType" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent">
                            @foreach($courseTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Estimated Duration -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Duration (minutes)</label>
                        <input
                            type="number"
                            wire:model="estimatedDuration"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                            placeholder="45"
                            min="1"
                            max="480"
                        >
                    </div>

                    <!-- Options -->
                    <div class="space-y-2">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="isTemplate" class="rounded text-pulse-orange-500 focus:ring-pulse-orange-500">
                            <span class="text-sm text-gray-700">Make this a template</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="isPublic" class="rounded text-pulse-orange-500 focus:ring-pulse-orange-500">
                            <span class="text-sm text-gray-700">Make publicly visible</span>
                        </label>
                    </div>
                </div>

                <!-- Objectives -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Objectives</h2>
                    <ul class="space-y-2 mb-4">
                        @foreach($objectives as $index => $objective)
                        <li class="flex items-center gap-2 text-sm">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="flex-1">{{ $objective }}</span>
                            <button wire:click="removeObjective({{ $index }})" class="text-gray-400 hover:text-red-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </li>
                        @endforeach
                    </ul>
                    <div class="flex gap-2">
                        <input
                            type="text"
                            wire:model="newObjective"
                            wire:keydown.enter="addObjective"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                            placeholder="Add an objective..."
                        >
                        <button wire:click="addObjective" class="px-3 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Rationale -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Rationale & Experience</h2>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Why This Course Exists</label>
                        <textarea
                            wire:model="rationale"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                            placeholder="Explain the purpose and value of this course..."
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Expected Learner Experience</label>
                        <textarea
                            wire:model="expectedExperience"
                            rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                            placeholder="Describe what the learner will do and experience..."
                        ></textarea>
                    </div>
                </div>
            </div>

            <!-- Steps Panel -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Course Steps</h2>
                        <button wire:click="openStepModal" class="inline-flex items-center px-3 py-1.5 bg-pulse-orange-500 text-white rounded-lg text-sm hover:bg-pulse-orange-600">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Add Step
                        </button>
                    </div>

                    @if($course && $course->steps->count() > 0)
                    <div class="space-y-3">
                        @foreach($course->steps as $index => $step)
                        <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-lg border border-gray-200 group">
                            <!-- Step Number -->
                            <div class="w-8 h-8 rounded-full bg-pulse-orange-100 flex items-center justify-center text-sm font-medium text-pulse-orange-600 flex-shrink-0">
                                {{ $index + 1 }}
                            </div>

                            <!-- Step Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <h3 class="font-medium text-gray-900">{{ $step->title }}</h3>
                                    <span class="px-2 py-0.5 text-xs bg-gray-200 text-gray-600 rounded">{{ ucfirst($step->step_type) }}</span>
                                    @if($step->is_required)
                                    <span class="px-2 py-0.5 text-xs bg-red-100 text-red-600 rounded">Required</span>
                                    @endif
                                </div>
                                @if($step->description)
                                <p class="text-sm text-gray-500 truncate">{{ $step->description }}</p>
                                @endif
                            </div>

                            <!-- Duration -->
                            @if($step->estimated_duration_minutes)
                            <span class="text-sm text-gray-500">{{ $step->estimated_duration_minutes }}m</span>
                            @endif

                            <!-- Actions -->
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button wire:click="moveStepUp({{ $step->id }})" class="p-1 text-gray-400 hover:text-gray-600" @if($index === 0) disabled @endif>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                    </svg>
                                </button>
                                <button wire:click="moveStepDown({{ $step->id }})" class="p-1 text-gray-400 hover:text-gray-600" @if($index === $course->steps->count() - 1) disabled @endif>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <button wire:click="openStepModal({{ $step->id }})" class="p-1 text-gray-400 hover:text-pulse-orange-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button wire:click="deleteStep({{ $step->id }})" wire:confirm="Are you sure you want to delete this step?" class="p-1 text-gray-400 hover:text-red-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-12 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <p class="mb-2">No steps yet</p>
                        <p class="text-sm">Add steps to build your course content.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Step Modal -->
    @if($showStepModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeStepModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">{{ $editingStepId ? 'Edit Step' : 'Add Step' }}</h3>

                    <div class="space-y-4">
                        <!-- Step Type -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Step Type</label>
                                <select wire:model="stepForm.step_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent">
                                    @foreach($stepTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Content Type</label>
                                <select wire:model="stepForm.content_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent">
                                    @foreach($contentTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Title -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                            <input
                                type="text"
                                wire:model="stepForm.title"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                                placeholder="Step title"
                            >
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                            <textarea
                                wire:model="stepForm.description"
                                rows="2"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                                placeholder="Brief description"
                            ></textarea>
                        </div>

                        <!-- Instructions -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Instructions</label>
                            <textarea
                                wire:model="stepForm.instructions"
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                                placeholder="Instructions for the learner..."
                            ></textarea>
                        </div>

                        <!-- Duration & Required -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Duration (minutes)</label>
                                <input
                                    type="number"
                                    wire:model="stepForm.estimated_duration_minutes"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent"
                                    min="1"
                                >
                            </div>
                            <div class="flex items-end pb-2">
                                <label class="flex items-center gap-2">
                                    <input type="checkbox" wire:model="stepForm.is_required" class="rounded text-pulse-orange-500 focus:ring-pulse-orange-500">
                                    <span class="text-sm text-gray-700">Required step</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button wire:click="saveStep" class="w-full inline-flex justify-center px-4 py-2 bg-pulse-orange-500 text-white rounded-lg text-sm font-medium hover:bg-pulse-orange-600 sm:w-auto">
                        {{ $editingStepId ? 'Update Step' : 'Add Step' }}
                    </button>
                    <button wire:click="closeStepModal" class="mt-3 w-full inline-flex justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 sm:mt-0 sm:w-auto">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Publish Confirmation Modal -->
    @if($showPublishConfirm)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="$set('showPublishConfirm', false)"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Publish Course</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to publish this course? Once published, it will be available for enrollment and a new version will be created.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                    <button wire:click="publish" class="w-full inline-flex justify-center px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 sm:w-auto">
                        Publish Course
                    </button>
                    <button wire:click="$set('showPublishConfirm', false)" class="mt-3 w-full inline-flex justify-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 sm:mt-0 sm:w-auto">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
