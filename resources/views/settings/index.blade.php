<x-layouts.dashboard title="Settings">
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
                    Notifications
                </button>
                <button
                    @click="activeTab = 'profile'"
                    :class="activeTab === 'profile' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    <x-icon name="user" class="w-5 h-5 inline-block mr-2 -mt-0.5" />
                    Profile
                </button>
                <button
                    @click="activeTab = 'security'"
                    :class="activeTab === 'security' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                >
                    <x-icon name="shield-check" class="w-5 h-5 inline-block mr-2 -mt-0.5" />
                    Security
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
                    <p class="text-gray-500">Profile settings coming soon.</p>
                    <p class="text-gray-400 text-sm mt-1">Profile management features are coming soon.</p>
                </div>
            </x-card>
        </div>

        <div x-show="activeTab === 'security'" x-cloak>
            <x-card>
                <div class="text-center py-12">
                    <x-icon name="shield-check" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <p class="text-gray-500">Security settings coming soon.</p>
                    <p class="text-gray-400 text-sm mt-1">Password and two-factor authentication options will be available here.</p>
                </div>
            </x-card>
        </div>
    </div>
</x-layouts.dashboard>
