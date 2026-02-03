<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Terminology Settings</h1>
            <p class="text-gray-600 mt-1">Customize the labels and terms used throughout the platform for your organization</p>
        </div>
        <div class="flex items-center gap-3">
            @if($hasChanges)
            <span class="text-sm text-amber-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                Unsaved changes
            </span>
            @endif
            <button
                wire:click="resetToDefaults"
                wire:confirm="Are you sure you want to reset all terminology to defaults?"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
                Reset to Defaults
            </button>
            <button
                wire:click="save"
                @if(!$hasChanges) disabled @endif
                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                Save Changes
            </button>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Customize Your Platform</h3>
                <p class="mt-1 text-sm text-blue-700">
                    Change how terms appear across your platform. For example, rename "Semester" to "Quarter" for corporate training,
                    or "Learner" to "Learner" for education. Changes apply immediately after saving.
                </p>
            </div>
        </div>
    </div>

    <!-- Terminology Categories -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($categories as $categoryName => $keys)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                <h2 class="text-lg font-semibold text-gray-900">{{ $categoryName }}</h2>
            </div>
            <div class="p-4 space-y-4">
                @foreach($keys as $key)
                <div class="group">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ ucwords(str_replace('_', ' ', $key)) }}
                    </label>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            wire:model.live="terminology.{{ $key }}"
                            placeholder="{{ \App\Models\OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? '' }}"
                            class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm
                                @if(($terminology[$key] ?? '') !== (\App\Models\OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? ''))
                                    border-purple-300 bg-purple-50
                                @endif
                            "
                        >
                        @if(($terminology[$key] ?? '') !== (\App\Models\OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? ''))
                        <button
                            wire:click="resetField('{{ $key }}')"
                            class="p-2 text-gray-400 hover:text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity"
                            title="Reset to default"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Default: {{ \App\Models\OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? 'N/A' }}
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    <!-- Preview Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-4 py-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
            <h2 class="text-lg font-semibold text-gray-900">Preview</h2>
            <p class="text-sm text-gray-600">See how your terminology will appear in the platform</p>
        </div>
        <div class="p-4">
            <div class="bg-gray-50 rounded-lg p-4 space-y-3 text-sm">
                <p class="text-gray-700">
                    <span class="font-medium">Example navigation:</span>
                    {{ $terminology['course_plural'] ?? 'Courses' }} / {{ $terminology['cohort_plural'] ?? 'Cohorts' }} / {{ $terminology['learner_plural'] ?? 'Learners' }}
                </p>
                <p class="text-gray-700">
                    <span class="font-medium">Example message:</span>
                    "Welcome to the {{ $terminology['period_singular'] ?? 'Semester' }}! Your {{ $terminology['instructor_singular'] ?? 'Instructor' }} has enrolled you in a new {{ $terminology['course_singular'] ?? 'Course' }}."
                </p>
                <p class="text-gray-700">
                    <span class="font-medium">Example completion:</span>
                    "Congratulations! You've earned a {{ $terminology['certificate_singular'] ?? 'Certificate' }} and {{ $terminology['badge_singular'] ?? 'Badge' }} for completing this {{ $terminology['module_singular'] ?? 'Module' }}."
                </p>
            </div>
        </div>
    </div>
</div>
