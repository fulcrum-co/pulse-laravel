<div class="space-y-6">
    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Terminology Settings</h3>
                    <p class="text-sm text-gray-500">Customize the labels used throughout the application to match your organization's language.</p>
                </div>
                <div class="flex items-center gap-3">
                    @if($hasChanges)
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        Unsaved changes
                    </span>
                    @endif
                    <button
                        wire:click="save"
                        type="button"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 border border-transparent rounded-lg hover:bg-pulse-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
                        @if(!$hasChanges) disabled @endif
                    >
                        <x-icon name="check" class="w-4 h-4" />
                        Save Changes
                    </button>
                </div>
            </div>
        </x-slot:header>

        <div class="space-y-8">
            @foreach($categories as $categoryName => $keys)
            <div>
                <h4 class="text-sm font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200 flex items-center gap-2">
                    @switch($categoryName)
                        @case('Contacts & People')
                            <x-icon name="users" class="w-4 h-4 text-gray-400" />
                            @break
                        @case('Organization & Structure')
                            <x-icon name="building-office" class="w-4 h-4 text-gray-400" />
                            @break
                        @case('Risk & Status')
                            <x-icon name="shield-exclamation" class="w-4 h-4 text-gray-400" />
                            @break
                        @case('Data Collection')
                            <x-icon name="clipboard-document-list" class="w-4 h-4 text-gray-400" />
                            @break
                        @case('Resources & Learning')
                            <x-icon name="academic-cap" class="w-4 h-4 text-gray-400" />
                            @break
                        @case('Communication')
                            <x-icon name="chat-bubble-left-right" class="w-4 h-4 text-gray-400" />
                            @break
                        @case('Reports & Analytics')
                            <x-icon name="chart-bar" class="w-4 h-4 text-gray-400" />
                            @break
                    @endswitch
                    {{ $categoryName }}
                </h4>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($keys as $key)
                    @if(isset($terminology[$key]))
                    <div class="relative">
                        <label for="term-{{ $key }}" class="block text-sm font-medium text-gray-700 mb-1">
                            {{ ucwords(str_replace('_', ' ', $key)) }}
                        </label>
                        <div class="flex items-center gap-2">
                            <input
                                type="text"
                                id="term-{{ $key }}"
                                wire:model.live="terminology.{{ $key }}"
                                class="flex-1 px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="{{ \App\Models\OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? '' }}"
                            />
                            @if(($terminology[$key] ?? '') !== (\App\Models\OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? ''))
                            <button
                                wire:click="resetField('{{ $key }}')"
                                type="button"
                                class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
                                title="Reset to default"
                            >
                                <x-icon name="arrow-uturn-left" class="w-4 h-4" />
                            </button>
                            @endif
                        </div>
                        <p class="text-xs text-gray-400 mt-1">
                            Default: {{ \App\Models\OrganizationSettings::DEFAULT_TERMINOLOGY[$key] ?? ucwords(str_replace('_', ' ', $key)) }}
                        </p>
                    </div>
                    @endif
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-between">
            <button
                wire:click="resetToDefaults"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
                <x-icon name="arrow-path" class="w-4 h-4" />
                Reset All to Defaults
            </button>

            <button
                wire:click="save"
                type="button"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 border border-transparent rounded-lg hover:bg-pulse-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                @if(!$hasChanges) disabled @endif
            >
                <x-icon name="check" class="w-4 h-4" />
                Save Changes
            </button>
        </div>
    </x-card>

    <x-card>
        <x-slot:header>
            <h3 class="text-lg font-medium text-gray-900">Live Preview</h3>
            <p class="text-sm text-gray-500">See how your custom terminology will appear across the application.</p>
        </x-slot:header>

        <div class="space-y-4">
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Contacts Section</p>
                <p class="text-sm text-gray-700">
                    "View all <span class="text-pulse-orange-600 font-medium">{{ $terminology['contacts'] ?? 'Contacts' }}</span>
                    assigned to this <span class="text-pulse-orange-600 font-medium">{{ $terminology['counselor'] ?? 'Counselor' }}</span>.
                    Filter by <span class="text-pulse-orange-600 font-medium">{{ $terminology['grade_level'] ?? 'Grade Level' }}</span>
                    or <span class="text-pulse-orange-600 font-medium">{{ $terminology['cohort'] ?? 'Cohort' }}</span>."
                </p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Risk Status Labels</p>
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        {{ $terminology['good_standing'] ?? 'Good Standing' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                        {{ $terminology['low_risk'] ?? 'Low Risk' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        {{ $terminology['high_risk'] ?? 'High Risk' }}
                    </span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ $terminology['needs_support'] ?? 'Needs Support' }}
                    </span>
                </div>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Data Collection</p>
                <p class="text-sm text-gray-700">
                    "Send a <span class="text-pulse-orange-600 font-medium">{{ $terminology['survey'] ?? 'Survey' }}</span>
                    to all <span class="text-pulse-orange-600 font-medium">{{ $terminology['students'] ?? 'Students' }}</span>
                    in <span class="text-pulse-orange-600 font-medium">{{ $terminology['grade'] ?? 'Grade' }}</span> 9.
                    Review <span class="text-pulse-orange-600 font-medium">{{ $terminology['responses'] ?? 'Responses' }}</span>
                    from the <span class="text-pulse-orange-600 font-medium">{{ $terminology['check_in'] ?? 'Check-In' }}</span>."
                </p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">Resources</p>
                <p class="text-sm text-gray-700">
                    "Assign a <span class="text-pulse-orange-600 font-medium">{{ $terminology['course'] ?? 'Course' }}</span>
                    with 5 <span class="text-pulse-orange-600 font-medium">{{ $terminology['modules'] ?? 'Modules' }}</span>
                    to this <span class="text-pulse-orange-600 font-medium">{{ $terminology['student'] ?? 'Student' }}</span>.
                    Track <span class="text-pulse-orange-600 font-medium">{{ $terminology['assignment'] ?? 'Assignment' }}</span> completion."
                </p>
            </div>
        </div>
    </x-card>
</div>
