<div class="space-y-6">
    <x-card>
        <x-slot:header>
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900">Terminology Settings</h3>
                    <p class="text-sm text-gray-500">Customize the labels used throughout the application to match your organization's language.</p>
                </div>
                @if($hasChanges)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    Unsaved changes
                </span>
                @endif
            </div>
        </x-slot:header>

        <div class="space-y-8">
            @foreach($categories as $categoryName => $keys)
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-4 pb-2 border-b border-gray-200">{{ $categoryName }}</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
            >
                Reset All to Defaults
            </button>

            <button
                wire:click="save"
                type="button"
                class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 border border-transparent rounded-lg hover:bg-pulse-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                @if(!$hasChanges) disabled @endif
            >
                Save Changes
            </button>
        </div>
    </x-card>

    <x-card>
        <x-slot:header>
            <h3 class="text-lg font-medium text-gray-900">Preview</h3>
            <p class="text-sm text-gray-500">See how your custom terminology will appear in the application.</p>
        </x-slot:header>

        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
            <p class="text-sm text-gray-700">
                <span class="font-medium">Example sentence:</span>
                "View all <span class="text-pulse-orange-600 font-medium">{{ $terminology['students'] ?? 'Students' }}</span>
                in <span class="text-pulse-orange-600 font-medium">{{ $terminology['grade'] ?? 'Grade' }}</span> 9
                assigned to this <span class="text-pulse-orange-600 font-medium">{{ $terminology['teacher'] ?? 'Teacher' }}</span>."
            </p>
            <p class="text-sm text-gray-700">
                <span class="font-medium">Risk labels:</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 mx-1">{{ $terminology['good_standing'] ?? 'Good Standing' }}</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 mx-1">{{ $terminology['low_risk'] ?? 'Low Risk' }}</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 mx-1">{{ $terminology['high_risk'] ?? 'High Risk' }}</span>
            </p>
        </div>
    </x-card>
</div>
