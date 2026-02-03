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
                <!-- AI Assistant Toggle -->
                <button
                    wire:click="toggleAIPanel"
                    class="inline-flex items-center px-4 py-2 border border-purple-300 rounded-lg text-sm font-medium {{ $showAIPanel ? 'bg-purple-100 text-purple-700' : 'text-purple-600 bg-white hover:bg-purple-50' }}"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                    </svg>
                    AI Assistant
                </button>

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
                    <button
                        wire:click="generateStepContent"
                        wire:loading.attr="disabled"
                        wire:target="generateStepContent"
                        class="mt-3 w-full inline-flex justify-center items-center px-4 py-2 border border-purple-300 text-purple-600 rounded-lg text-sm font-medium bg-white hover:bg-purple-50 sm:mt-0 sm:w-auto disabled:opacity-50"
                    >
                        <span wire:loading.remove wire:target="generateStepContent">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                            </svg>
                            AI Generate
                        </span>
                        <span wire:loading wire:target="generateStepContent" class="flex items-center">
                            <svg class="animate-spin mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Generating...
                        </span>
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

    <!-- AI Assistant Slide-out Panel -->
    @if($showAIPanel)
    <div class="fixed inset-0 z-40 overflow-hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute inset-0 bg-gray-500 bg-opacity-50 transition-opacity" wire:click="toggleAIPanel"></div>
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10">
                <div class="pointer-events-auto w-screen max-w-md">
                    <div class="flex h-full flex-col overflow-y-scroll bg-white shadow-xl">
                        <!-- Header -->
                        <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-4 py-6 sm:px-6">
                            <div class="flex items-center justify-between">
                                <h2 class="text-lg font-medium text-white flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                    AI Course Assistant
                                </h2>
                                <button wire:click="toggleAIPanel" class="rounded-md text-purple-200 hover:text-white focus:outline-none">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1 text-sm text-purple-200">Let AI help you create engaging course content</p>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 px-4 py-6 sm:px-6 space-y-6">
                            <!-- Error Display -->
                            @if($aiError)
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                <div class="flex">
                                    <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <p class="ml-3 text-sm text-red-700">{{ $aiError }}</p>
                                </div>
                            </div>
                            @endif

                            <!-- Generate Full Course Section -->
                            <div class="bg-purple-50 rounded-lg p-4 border border-purple-100">
                                <h3 class="text-sm font-semibold text-purple-900 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                    </svg>
                                    Generate Complete Course
                                </h3>

                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Topic/Subject</label>
                                        <input
                                            type="text"
                                            wire:model="aiTopic"
                                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                            placeholder="e.g., Managing Test Anxiety"
                                        >
                                    </div>

                                    <div class="grid grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Audience</label>
                                            <select wire:model="aiAudience" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                                <option value="learners">Learners</option>
                                                <option value="teachers">Teachers</option>
                                                <option value="parents">Parents</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-gray-700 mb-1">Duration (min)</label>
                                            <input
                                                type="number"
                                                wire:model="aiDurationMinutes"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                                min="10"
                                                max="120"
                                            >
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Grade Level (optional)</label>
                                        <select wire:model="aiGradeLevel" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                            <option value="">Any grade</option>
                                            <option value="K-2">K-2</option>
                                            <option value="3-5">3-5</option>
                                            <option value="6-8">6-8</option>
                                            <option value="9-12">9-12</option>
                                        </select>
                                    </div>

                                    <button
                                        wire:click="generateFullCourse"
                                        wire:loading.attr="disabled"
                                        wire:target="generateFullCourse"
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <span wire:loading.remove wire:target="generateFullCourse">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                            </svg>
                                            Generate Course Draft
                                        </span>
                                        <span wire:loading wire:target="generateFullCourse" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Generating...
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <!-- Upload Document Section -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                    </svg>
                                    Create from Document
                                </h3>

                                <div class="space-y-3">
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-4 text-center">
                                        <input
                                            type="file"
                                            wire:model="uploadedDocument"
                                            class="hidden"
                                            id="document-upload"
                                            accept=".txt,.pdf,.doc,.docx"
                                        >
                                        <label for="document-upload" class="cursor-pointer">
                                            <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            <span class="mt-2 block text-xs text-gray-600">
                                                Click to upload TXT, PDF, or DOC
                                            </span>
                                        </label>
                                        @if($uploadedDocument)
                                        <p class="mt-2 text-xs text-green-600">{{ $uploadedDocument->getClientOriginalName() }}</p>
                                        @endif
                                        <div wire:loading wire:target="uploadedDocument" class="mt-2 text-xs text-purple-600">
                                            Uploading...
                                        </div>
                                    </div>

                                    <button
                                        wire:click="processDocument"
                                        wire:loading.attr="disabled"
                                        wire:target="processDocument"
                                        @if(!$uploadedDocument) disabled @endif
                                        class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-800 text-white rounded-lg text-sm font-medium hover:bg-gray-900 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <span wire:loading.remove wire:target="processDocument">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                            </svg>
                                            Extract Course from Document
                                        </span>
                                        <span wire:loading wire:target="processDocument" class="flex items-center">
                                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            Processing...
                                        </span>
                                    </button>
                                </div>
                            </div>

                            <!-- Generate Sections -->
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h3 class="text-sm font-semibold text-gray-900 mb-3 flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
                                    </svg>
                                    Generate Sections
                                </h3>

                                <div class="grid grid-cols-2 gap-2">
                                    <button
                                        wire:click="generateSection('introduction')"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        Introduction
                                    </button>
                                    <button
                                        wire:click="generateSection('content')"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Content
                                    </button>
                                    <button
                                        wire:click="generateSection('reflection')"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                        </svg>
                                        Reflection
                                    </button>
                                    <button
                                        wire:click="generateSection('assessment')"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                                        </svg>
                                        Assessment
                                    </button>
                                    <button
                                        wire:click="generateSection('action')"
                                        wire:loading.attr="disabled"
                                        class="inline-flex items-center justify-center px-3 py-2 border border-gray-300 rounded-lg text-xs font-medium text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 col-span-2"
                                    >
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        Action Plan
                                    </button>
                                </div>

                                <!-- Loading indicator -->
                                <div wire:loading wire:target="generateSection" class="mt-3 text-center">
                                    <div class="inline-flex items-center text-sm text-purple-600">
                                        <svg class="animate-spin mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Generating section...
                                    </div>
                                </div>
                            </div>

                            <!-- AI Suggestions Display -->
                            @if(!empty($aiSuggestions))
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="text-sm font-semibold text-green-900">AI Suggestions</h3>
                                    <button wire:click="clearAISuggestions" class="text-green-600 hover:text-green-800">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>

                                <div class="space-y-2 text-sm">
                                    @if(isset($aiSuggestions['title']))
                                    <div class="flex items-center justify-between p-2 bg-white rounded">
                                        <span class="text-gray-600">Title: {{ $aiSuggestions['title'] }}</span>
                                        <button wire:click="applySuggestion('title', '{{ addslashes($aiSuggestions['title']) }}')" class="text-xs text-green-600 hover:underline">Apply</button>
                                    </div>
                                    @endif

                                    @if(isset($aiSuggestions['suggestions']))
                                    @foreach($aiSuggestions['suggestions'] as $suggestion)
                                    <div class="p-2 bg-white rounded">
                                        <span class="text-xs font-medium text-gray-500 uppercase">{{ $suggestion['type'] ?? 'Suggestion' }}</span>
                                        <p class="text-gray-700">{{ $suggestion['text'] ?? $suggestion['description'] ?? '' }}</p>
                                    </div>
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Tips -->
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2">Tips</h4>
                                <ul class="text-xs text-gray-600 space-y-1">
                                    <li class="flex items-start">
                                        <span class="mr-2">•</span>
                                        <span>Add objectives first for better AI content</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">•</span>
                                        <span>Upload existing documents to convert to courses</span>
                                    </li>
                                    <li class="flex items-start">
                                        <span class="mr-2">•</span>
                                        <span>Review and customize AI-generated content</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
