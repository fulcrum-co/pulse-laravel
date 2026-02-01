<div>
    {{-- Success notification --}}
    <div
        x-data="{ show: false }"
        x-on:preferences-saved.window="show = true; setTimeout(() => show = false, 2000)"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50"
        style="display: none;"
    >
        Preferences saved
    </div>

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Notification Preferences</h2>
            <p class="text-sm text-gray-500 mt-1">Choose how you want to receive notifications for different types of activity.</p>
        </div>
        <button
            wire:click="resetToDefaults"
            wire:confirm="Reset all notification preferences to defaults?"
            class="text-sm text-gray-500 hover:text-gray-700 underline"
        >
            Reset to defaults
        </button>
    </div>

    {{-- ========================================== --}}
    {{-- DELIVERY CHANNELS BY PRIORITY --}}
    {{-- ========================================== --}}
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Delivery Channels by Priority</h3>
        <p class="text-sm text-gray-500 mb-4">Control which channels are used based on notification priority. In-app notifications are always enabled.</p>

        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Priority Level
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center justify-center gap-1">
                                <x-icon name="bell" class="w-4 h-4" />
                                <span>In-App</span>
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center justify-center gap-1">
                                <x-icon name="envelope" class="w-4 h-4" />
                                <span>Email</span>
                            </div>
                        </th>
                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                            <div class="flex items-center justify-center gap-1">
                                <x-icon name="device-phone-mobile" class="w-4 h-4" />
                                <span>SMS</span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($priorityLabels as $priority => $label)
                        <tr class="{{ $priority === 'urgent' ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    @if($priority === 'urgent')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                            {{ $label }}
                                        </span>
                                        <span class="text-xs text-gray-500">(cannot be disabled)</span>
                                    @elseif($priority === 'high')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800">
                                            {{ $label }}
                                        </span>
                                    @elseif($priority === 'normal')
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $label }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ $label }}
                                        </span>
                                    @endif
                                </div>
                            </td>
                            {{-- In-App (always on) --}}
                            <td class="px-4 py-3 text-center">
                                <input
                                    type="checkbox"
                                    checked
                                    disabled
                                    class="w-5 h-5 text-pulse-orange-500 border-gray-300 rounded cursor-not-allowed opacity-60"
                                />
                            </td>
                            {{-- Email --}}
                            <td class="px-4 py-3 text-center">
                                @if($priority === 'urgent')
                                    <input
                                        type="checkbox"
                                        checked
                                        disabled
                                        class="w-5 h-5 text-pulse-orange-500 border-gray-300 rounded cursor-not-allowed opacity-60"
                                    />
                                @else
                                    <input
                                        type="checkbox"
                                        wire:click="togglePriorityChannel('{{ $priority }}', 'email')"
                                        @checked($channelsByPriority[$priority]['email'] ?? false)
                                        class="w-5 h-5 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500 cursor-pointer"
                                    />
                                @endif
                            </td>
                            {{-- SMS --}}
                            <td class="px-4 py-3 text-center">
                                @if($priority === 'urgent')
                                    <input
                                        type="checkbox"
                                        checked
                                        disabled
                                        class="w-5 h-5 text-pulse-orange-500 border-gray-300 rounded cursor-not-allowed opacity-60"
                                    />
                                @else
                                    <input
                                        type="checkbox"
                                        wire:click="togglePriorityChannel('{{ $priority }}', 'sms')"
                                        @checked($channelsByPriority[$priority]['sms'] ?? false)
                                        class="w-5 h-5 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500 cursor-pointer"
                                    />
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- NOTIFICATION CATEGORIES --}}
    {{-- ========================================== --}}
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-2">Notification Categories</h3>
        <p class="text-sm text-gray-500 mb-4">Enable or disable notifications by category. Expand each category to control individual notification types.</p>

        <div class="space-y-3">
            @foreach($categoryLabels as $category => $label)
                <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                    {{-- Category header --}}
                    <div class="flex items-start justify-between p-4">
                        <div class="flex items-start gap-3">
                            {{-- Expand/collapse button --}}
                            @if(isset($typesByCategory[$category]) && count($typesByCategory[$category]) > 0)
                                <button
                                    wire:click="toggleCategoryExpansion('{{ $category }}')"
                                    class="mt-0.5 text-gray-400 hover:text-gray-600"
                                >
                                    <x-icon
                                        name="{{ in_array($category, $expandedCategories) ? 'chevron-down' : 'chevron-right' }}"
                                        class="w-5 h-5"
                                    />
                                </button>
                            @else
                                <div class="w-5"></div>
                            @endif
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ $label }}</h4>
                                <p class="text-sm text-gray-500 mt-0.5">{{ $categoryDescriptions[$category] }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 ml-6">
                            {{-- In-App (always on) --}}
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs text-gray-500 font-medium">In-App</span>
                                <input
                                    type="checkbox"
                                    checked
                                    disabled
                                    class="w-5 h-5 text-pulse-orange-500 border-gray-300 rounded cursor-not-allowed opacity-60"
                                />
                            </div>

                            {{-- Email --}}
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs text-gray-500 font-medium">Email</span>
                                <input
                                    type="checkbox"
                                    wire:click="togglePreference('{{ $category }}', 'email')"
                                    @checked($preferences[$category]['email'] ?? false)
                                    class="w-5 h-5 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500 cursor-pointer"
                                />
                            </div>

                            {{-- SMS --}}
                            <div class="flex flex-col items-center gap-1">
                                <span class="text-xs text-gray-500 font-medium">SMS</span>
                                <input
                                    type="checkbox"
                                    wire:click="togglePreference('{{ $category }}', 'sms')"
                                    @checked($preferences[$category]['sms'] ?? false)
                                    class="w-5 h-5 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500 cursor-pointer"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Type overrides (expanded) --}}
                    @if(in_array($category, $expandedCategories) && isset($typesByCategory[$category]))
                        <div class="border-t border-gray-100 bg-gray-50 px-4 py-3">
                            <p class="text-xs text-gray-500 mb-3">Toggle individual notification types on/off:</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($typesByCategory[$category] as $type => $typeLabel)
                                    @php
                                        $isDisabled = isset($typeOverrides[$type]) && $typeOverrides[$type] === false;
                                    @endphp
                                    <label class="flex items-center gap-2 cursor-pointer group">
                                        <input
                                            type="checkbox"
                                            wire:click="toggleTypeOverride('{{ $type }}')"
                                            @checked(!$isDisabled)
                                            class="w-4 h-4 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500 cursor-pointer"
                                        />
                                        <span class="text-sm text-gray-700 group-hover:text-gray-900 {{ $isDisabled ? 'line-through text-gray-400' : '' }}">
                                            {{ $typeLabel }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- QUIET HOURS --}}
    {{-- ========================================== --}}
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Quiet Hours</h3>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <button
                            wire:click="toggleQuietHours"
                            type="button"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pulse-orange-500 focus:ring-offset-2 {{ $quietHoursEnabled ? 'bg-pulse-orange-500' : 'bg-gray-200' }}"
                            role="switch"
                            aria-checked="{{ $quietHoursEnabled ? 'true' : 'false' }}"
                        >
                            <span
                                aria-hidden="true"
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $quietHoursEnabled ? 'translate-x-5' : 'translate-x-0' }}"
                            ></span>
                        </button>
                        <div>
                            <span class="font-medium text-gray-900">Enable Do Not Disturb</span>
                            <p class="text-sm text-gray-500">Pause email and SMS notifications during specified hours</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($quietHoursEnabled)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div class="flex flex-wrap items-end gap-4">
                        <div>
                            <label for="quietHoursStart" class="block text-sm font-medium text-gray-700 mb-1">Start time</label>
                            <input
                                type="time"
                                id="quietHoursStart"
                                wire:model.live.debounce.500ms="quietHoursStart"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500 sm:text-sm"
                            />
                        </div>
                        <div class="pb-2">
                            <span class="text-gray-400">to</span>
                        </div>
                        <div>
                            <label for="quietHoursEnd" class="block text-sm font-medium text-gray-700 mb-1">End time</label>
                            <input
                                type="time"
                                id="quietHoursEnd"
                                wire:model.live.debounce.500ms="quietHoursEnd"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500 sm:text-sm"
                            />
                        </div>
                        <div>
                            <label for="quietHoursTimezone" class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                            <select
                                id="quietHoursTimezone"
                                wire:model.live="quietHoursTimezone"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500 sm:text-sm"
                            >
                                <option value="">Auto (browser)</option>
                                @foreach($timezones as $tz => $tzLabel)
                                    <option value="{{ $tz }}">{{ $tzLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <p class="mt-3 text-xs text-gray-500">
                        In-app notifications will still appear, but email and SMS will be held until quiet hours end.
                    </p>
                </div>
            @endif
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- EMAIL DIGESTS --}}
    {{-- ========================================== --}}
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Email Digests</h3>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <button
                            wire:click="toggleDigest"
                            type="button"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pulse-orange-500 focus:ring-offset-2 {{ $digestEnabled ? 'bg-pulse-orange-500' : 'bg-gray-200' }}"
                            role="switch"
                            aria-checked="{{ $digestEnabled ? 'true' : 'false' }}"
                        >
                            <span
                                aria-hidden="true"
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $digestEnabled ? 'translate-x-5' : 'translate-x-0' }}"
                            ></span>
                        </button>
                        <div>
                            <span class="font-medium text-gray-900">Enable Digest Emails</span>
                            <p class="text-sm text-gray-500">Receive a summary of your unread notifications</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($digestEnabled)
                <div class="mt-4 pt-4 border-t border-gray-100 space-y-4">
                    {{-- Frequency --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="radio"
                                    name="digestFrequency"
                                    value="daily"
                                    wire:model.live="digestFrequency"
                                    class="w-4 h-4 text-pulse-orange-500 border-gray-300 focus:ring-pulse-orange-500"
                                />
                                <span class="text-sm text-gray-700">Daily</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="radio"
                                    name="digestFrequency"
                                    value="weekly"
                                    wire:model.live="digestFrequency"
                                    class="w-4 h-4 text-pulse-orange-500 border-gray-300 focus:ring-pulse-orange-500"
                                />
                                <span class="text-sm text-gray-700">Weekly</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input
                                    type="radio"
                                    name="digestFrequency"
                                    value="both"
                                    wire:model.live="digestFrequency"
                                    class="w-4 h-4 text-pulse-orange-500 border-gray-300 focus:ring-pulse-orange-500"
                                />
                                <span class="text-sm text-gray-700">Both</span>
                            </label>
                        </div>
                    </div>

                    {{-- Day and Time --}}
                    <div class="flex flex-wrap items-end gap-4">
                        @if(in_array($digestFrequency, ['weekly', 'both']))
                            <div>
                                <label for="digestDay" class="block text-sm font-medium text-gray-700 mb-1">Weekly digest day</label>
                                <select
                                    id="digestDay"
                                    wire:model.live="digestDay"
                                    class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500 sm:text-sm"
                                >
                                    <option value="monday">Monday</option>
                                    <option value="tuesday">Tuesday</option>
                                    <option value="wednesday">Wednesday</option>
                                    <option value="thursday">Thursday</option>
                                    <option value="friday">Friday</option>
                                    <option value="saturday">Saturday</option>
                                    <option value="sunday">Sunday</option>
                                </select>
                            </div>
                        @endif
                        <div>
                            <label for="digestTime" class="block text-sm font-medium text-gray-700 mb-1">Delivery time</label>
                            <input
                                type="time"
                                id="digestTime"
                                wire:model.live.debounce.500ms="digestTime"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500 sm:text-sm"
                            />
                        </div>
                    </div>

                    {{-- Suppress individual emails --}}
                    <div class="pt-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:click="toggleDigestSuppressIndividual"
                                @checked($digestSuppressIndividual)
                                class="w-4 h-4 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500 cursor-pointer"
                            />
                            <span class="text-sm text-gray-700">Suppress individual emails when digest is active</span>
                        </label>
                        <p class="text-xs text-gray-500 mt-1 ml-6">When enabled, you'll only receive digest summaries instead of individual notification emails.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- POPUP NOTIFICATIONS --}}
    {{-- ========================================== --}}
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Popup Notifications</h3>
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <button
                            wire:click="toggleToast"
                            type="button"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pulse-orange-500 focus:ring-offset-2 {{ $toastEnabled ? 'bg-pulse-orange-500' : 'bg-gray-200' }}"
                            role="switch"
                            aria-checked="{{ $toastEnabled ? 'true' : 'false' }}"
                        >
                            <span
                                aria-hidden="true"
                                class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $toastEnabled ? 'translate-x-5' : 'translate-x-0' }}"
                            ></span>
                        </button>
                        <div>
                            <span class="font-medium text-gray-900">Show Popup Notifications</span>
                            <p class="text-sm text-gray-500">Display real-time notifications in the corner of your screen as they arrive</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($toastEnabled)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <div>
                        <label for="toastPriorityThreshold" class="block text-sm font-medium text-gray-700 mb-1">Minimum priority</label>
                        <select
                            id="toastPriorityThreshold"
                            wire:model.live="toastPriorityThreshold"
                            class="block w-48 rounded-md border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500 sm:text-sm"
                        >
                            <option value="low">All notifications</option>
                            <option value="normal">Normal and above</option>
                            <option value="high">High and above</option>
                            <option value="urgent">Critical only</option>
                        </select>
                        <p class="mt-2 text-xs text-gray-500">Only show popups for notifications at or above this priority level.</p>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- ========================================== --}}
    {{-- PHONE NUMBER REMINDER --}}
    {{-- ========================================== --}}
    @if(!auth()->user()->phone)
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <x-icon name="exclamation-triangle" class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" />
                <div>
                    <p class="text-sm font-medium text-amber-800">Phone number not set</p>
                    <p class="text-sm text-amber-700 mt-1">
                        To receive SMS notifications, please add your phone number to your account.
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
