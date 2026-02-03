<x-layouts.dashboard title="@term('settings_label')">
    <div x-data="{ activeTab: 'notifications' }" class="space-y-6">
        {{-- Settings Navigation --}}
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button
                    @click="activeTab = 'notifications'"
                    :class="activeTab === 'notifications' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    <x-icon name="bell" class="w-5 h-5 inline-block mr-2 -mt-0.5" />
                    @term('notifications_label')
                </button>
                <button
                    @click="activeTab = 'profile'"
                    :class="activeTab === 'profile' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    <x-icon name="user" class="w-5 h-5 inline-block mr-2 -mt-0.5" />
                    @term('profile_label')
                </button>
                <button
                    @click="activeTab = 'security'"
                    :class="activeTab === 'security' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    <x-icon name="shield-check" class="w-5 h-5 inline-block mr-2 -mt-0.5" />
                    @term('security_label')
                </button>
                <button
                    @click="activeTab = 'terminology'"
                    :class="activeTab === 'terminology' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    <x-icon name="tag" class="w-5 h-5 inline-block mr-2 -mt-0.5" />
                    @term('terminology_label')
                </button>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div x-show="activeTab === 'notifications'" x-cloak>
            <livewire:settings.notification-preferences />
        </div>

        <div x-show="activeTab === 'profile'" x-cloak>
            <x-card>
                <div class="text-center py-12">
                    <x-icon name="user" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <p class="text-gray-500">@term('profile_settings_coming_soon_label')</p>
                    <p class="text-gray-400 text-sm mt-1">@term('profile_settings_coming_soon_body')</p>
                </div>
            </x-card>
        </div>

        <div x-show="activeTab === 'security'" x-cloak>
            <x-card>
                <div class="text-center py-12">
                    <x-icon name="shield-check" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <p class="text-gray-500">@term('security_settings_coming_soon_label')</p>
                    <p class="text-gray-400 text-sm mt-1">@term('security_settings_coming_soon_body')</p>
                </div>
            </x-card>
        </div>

        <div x-show="activeTab === 'terminology'" x-cloak>
            <livewire:admin.terminology-settings />
        </div>
    </div>
</x-layouts.dashboard>
