@use('App\Services\RolePermissions')
<x-layouts.dashboard title="Settings">
    <div x-data="{ activeTab: 'notifications' }" class="space-y-6">
        {{-- Settings Navigation --}}
        <div class="border-b border-gray-200">
            <div class="flex items-center justify-between">
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
                    <button
                        @click="activeTab = 'terminology'"
                        :class="activeTab === 'terminology' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                    >
                        <x-icon name="tag" class="w-5 h-5 inline-block mr-2 -mt-0.5" />
                        Terminology
                    </button>
                    @if(auth()->user()?->isAdmin())
                    <button
                        @click="activeTab = 'admin'"
                        :class="activeTab === 'admin' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                    >
                        <x-icon name="cog-6-tooth" class="w-5 h-5 inline-block mr-2 -mt-0.5" />
                        Admin
                    </button>
                    @endif
                </nav>

                {{-- Save Button CTA --}}
                <button
                    @click="$dispatch('save-settings')"
                    class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors shadow-sm"
                >
                    <x-icon name="check" class="w-4 h-4" />
                    Save
                </button>
            </div>
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

        <div x-show="activeTab === 'terminology'" x-cloak>
            <livewire:admin.terminology-settings />
        </div>

        @if(auth()->user()?->isAdmin())
        <div x-show="activeTab === 'admin'" x-cloak>
            <x-card>
                <x-slot:header>
                    <h3 class="text-lg font-medium text-gray-900">Admin Settings</h3>
                    <p class="text-sm text-gray-500">System-wide configuration options for administrators.</p>
                </x-slot:header>

                <div class="divide-y divide-gray-200">
                    {{-- AI Course Settings --}}
                    <a href="{{ route('admin.settings.ai-courses') }}" class="flex items-center justify-between py-4 hover:bg-gray-50 -mx-6 px-6 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                                <x-icon name="sparkles" class="w-5 h-5 text-purple-600" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">AI Course Settings</p>
                                <p class="text-sm text-gray-500">Configure AI-generated course approvals and workflows</p>
                            </div>
                        </div>
                        <x-icon name="chevron-right" class="w-5 h-5 text-gray-400" />
                    </a>

                    {{-- Content Moderation --}}
                    <a href="{{ route('admin.moderation') }}" class="flex items-center justify-between py-4 hover:bg-gray-50 -mx-6 px-6 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                <x-icon name="shield-check" class="w-5 h-5 text-blue-600" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Content Moderation</p>
                                <p class="text-sm text-gray-500">Review and approve user-submitted content</p>
                            </div>
                        </div>
                        <x-icon name="chevron-right" class="w-5 h-5 text-gray-400" />
                    </a>

                    {{-- Moderation Dashboard --}}
                    <a href="{{ route('admin.moderation.dashboard') }}" class="flex items-center justify-between py-4 hover:bg-gray-50 -mx-6 px-6 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                                <x-icon name="chart-bar" class="w-5 h-5 text-green-600" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">Moderation Dashboard</p>
                                <p class="text-sm text-gray-500">Analytics and performance metrics for moderation</p>
                            </div>
                        </div>
                        <x-icon name="chevron-right" class="w-5 h-5 text-gray-400" />
                    </a>
                </div>
            </x-card>
        </div>
        @endif
    </div>
</x-layouts.dashboard>
