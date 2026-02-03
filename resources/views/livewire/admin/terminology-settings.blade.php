<div class="space-y-6">
    @php($terminologyService = app(\App\Services\TerminologyService::class))
    @php($terminology = $terminologyService)
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">@term('terminology_settings_label')</h1>
            <p class="text-gray-600 mt-1">@term('terminology_settings_description')</p>
        </div>
        <div class="flex items-center gap-3">
            @if($hasChanges)
            <span class="text-sm text-amber-600 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                @term('unsaved_changes_label')
            </span>
            @endif
            <button
                wire:click="resetToDefaults"
                wire:confirm="@term('reset_terminology_confirm_label')"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
                @term('reset_to_defaults_label')
            </button>
            <button
                wire:click="save"
                @if(!$hasChanges) disabled @endif
                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
                @term('save_changes_label')
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
                <h3 class="text-sm font-medium text-blue-800">@term('customize_platform_label')</h3>
                <p class="mt-1 text-sm text-blue-700">
                    @term('customize_platform_help_label')
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
                        {{ \App\Models\OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? ucwords(str_replace('_', ' ', $key)) }}
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
                        <button
                            type="button"
                            class="p-2 text-gray-400 hover:text-gray-600"
                            title="@term('example_label'): {{ \App\Models\OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? $terminology->get('not_available_label') }}"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </button>
                        @if(($terminology[$key] ?? '') !== (\App\Models\OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? ''))
                        <button
                            wire:click="resetField('{{ $key }}')"
                            class="p-2 text-gray-400 hover:text-gray-600 opacity-0 group-hover:opacity-100 transition-opacity"
                            title="@term('reset_to_default_label')"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                        @endif
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        @term('default_label'): {{ \App\Models\OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? $terminology->get('not_available_label') }}
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
            <h2 class="text-lg font-semibold text-gray-900">@term('preview_label')</h2>
            <p class="text-sm text-gray-600">@term('preview_help_label')</p>
        </div>
        <div class="p-4">
            <div class="bg-gray-50 rounded-lg p-4 space-y-3 text-sm">
                <p class="text-gray-700">
                    <span class="font-medium">@term('example_navigation_label'):</span>
                    {{ $terminology['course_plural'] ?? $terminologyService->get('course_plural') }} / {{ $terminology['cohort_plural'] ?? $terminologyService->get('cohort_plural') }} / {{ $terminology['learner_plural'] ?? $terminologyService->get('learner_plural') }}
                </p>
                <p class="text-gray-700">
                    <span class="font-medium">@term('example_message_label'):</span>
                    "Welcome to the {{ $terminology['period_singular'] ?? $terminologyService->get('period_singular') }}! Your {{ $terminology['instructor_singular'] ?? $terminologyService->get('instructor_singular') }} has enrolled you in a new {{ $terminology['course_singular'] ?? $terminologyService->get('course_singular') }}."
                </p>
                <p class="text-gray-700">
                    <span class="font-medium">@term('example_completion_label'):</span>
                    "Congratulations! You've earned a {{ $terminology['certificate_singular'] ?? $terminologyService->get('certificate_singular') }} and {{ $terminology['badge_singular'] ?? $terminologyService->get('badge_singular') }} for completing this {{ $terminology['module_singular'] ?? $terminologyService->get('module_singular') }}."
                </p>
            </div>
        </div>
    </div>
</div>
