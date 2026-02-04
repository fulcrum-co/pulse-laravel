<div class="min-h-screen pb-24">
    <div class="max-w-3xl mx-auto space-y-6">

        <!-- Section 1: Basics -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Basics</h3>

            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="title"
                        wire:model.blur="title"
                        placeholder="e.g., Weekly Progress Report"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    />
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea
                        id="description"
                        wire:model.blur="description"
                        rows="2"
                        placeholder="Brief description..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Channel</label>
                        <div class="flex gap-2">
                            <label class="flex-1 flex items-center justify-center p-3 border rounded-lg cursor-pointer transition-colors {{ $channel === 'email' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="channel" value="email" class="sr-only" />
                                <x-icon name="envelope" class="w-5 h-5 mr-2 {{ $channel === 'email' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                <span class="text-sm font-medium {{ $channel === 'email' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Email</span>
                            </label>
                            <label class="flex-1 flex items-center justify-center p-3 border rounded-lg cursor-pointer transition-colors {{ $channel === 'sms' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="channel" value="sms" class="sr-only" />
                                <x-icon name="device-phone-mobile" class="w-5 h-5 mr-2 {{ $channel === 'sms' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                <span class="text-sm font-medium {{ $channel === 'sms' ? 'text-pulse-orange-700' : 'text-gray-700' }}">SMS</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                        <div class="flex gap-2">
                            <label class="flex-1 flex items-center justify-center p-3 border rounded-lg cursor-pointer transition-colors {{ $distributionType === 'one_time' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="distributionType" value="one_time" class="sr-only" />
                                <span class="text-sm font-medium {{ $distributionType === 'one_time' ? 'text-pulse-orange-700' : 'text-gray-700' }}">One-time</span>
                            </label>
                            <label class="flex-1 flex items-center justify-center p-3 border rounded-lg cursor-pointer transition-colors {{ $distributionType === 'recurring' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="distributionType" value="recurring" class="sr-only" />
                                <span class="text-sm font-medium {{ $distributionType === 'recurring' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Recurring</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Compose -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Compose</h3>

            <div class="space-y-5">
                <!-- To: Recipients -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">To <span class="text-red-500">*</span></label>

                    <!-- Contact Lists -->
                    @if($contactLists->isNotEmpty())
                        <div class="mb-3">
                            <p class="text-xs text-gray-500 mb-2">Select contact list(s):</p>
                            <div class="flex flex-wrap gap-2">
                                @foreach($contactLists as $list)
                                    <button
                                        type="button"
                                        wire:click="toggleContactList({{ $list->id }})"
                                        class="inline-flex items-center px-3 py-1.5 rounded-full text-sm transition-colors {{ in_array($list->id, $selectedContactListIds) ? 'bg-pulse-orange-500 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                    >
                                        @if(in_array($list->id, $selectedContactListIds))
                                            <x-icon name="check" class="w-4 h-4 mr-1" />
                                        @endif
                                        {{ $list->name }}
                                        <span class="ml-1 text-xs opacity-75">({{ $list->members_count }})</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 mb-3">
                            No contact lists available.
                            <a href="{{ route('contacts.lists') }}" class="text-pulse-orange-600 hover:underline">Create one</a>
                        </p>
                    @endif

                    <!-- Individual Contact Search -->
                    <div class="relative">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="contactSearch"
                            placeholder="Search for individual contacts..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        />
                        @if($contactSearch && $contacts->isNotEmpty())
                            <div class="absolute z-10 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-48 overflow-y-auto">
                                @foreach($contacts as $contact)
                                    <button
                                        type="button"
                                        wire:click="toggleContact({{ $contact->id }})"
                                        class="w-full px-3 py-2 text-left hover:bg-gray-50 flex items-center justify-between {{ in_array($contact->id, $selectedContactIds) ? 'bg-pulse-orange-50' : '' }}"
                                    >
                                        <span>{{ $contact->user->first_name }} {{ $contact->user->last_name }}</span>
                                        @if(in_array($contact->id, $selectedContactIds))
                                            <x-icon name="check" class="w-4 h-4 text-pulse-orange-500" />
                                        @endif
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Selected Count -->
                    @if($this->selectedRecipientsCount > 0)
                        <p class="mt-2 text-sm text-gray-600">
                            <x-icon name="users" class="w-4 h-4 inline mr-1" />
                            {{ $this->selectedRecipientsCount }} recipient(s) selected
                        </p>
                    @endif
                </div>

                <!-- Subject Line (email only) -->
                @if($channel === 'email')
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject Line</label>
                        <input
                            type="text"
                            id="subject"
                            wire:model.blur="subject"
                            placeholder="e.g., Your Weekly Progress Report"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        />
                    </div>
                @endif

                <!-- Link Reports -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Link Report(s)</label>
                    @if($reports->isNotEmpty())
                        <div class="space-y-2">
                            @foreach($reports as $report)
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ in_array($report->id, $selectedReportIds) ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input
                                        type="checkbox"
                                        wire:click="toggleReport({{ $report->id }})"
                                        @checked(in_array($report->id, $selectedReportIds))
                                        class="w-4 h-4 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500"
                                    />
                                    <div class="ml-3 flex-1">
                                        <span class="text-sm font-medium text-gray-900">{{ $report->report_name }}</span>
                                        @if($report->report_description)
                                            <p class="text-xs text-gray-500 truncate">{{ $report->report_description }}</p>
                                        @endif
                                    </div>
                                    <x-icon name="chart-bar" class="w-5 h-5 text-gray-400" />
                                </label>
                            @endforeach
                        </div>

                        <!-- Report Mode (only show if reports selected) -->
                        @if(!empty($selectedReportIds))
                            <div class="mt-3 p-3 bg-gray-50 rounded-lg">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Report Delivery Mode</label>
                                <div class="flex gap-3">
                                    <label class="flex-1 flex items-center p-2 border rounded-lg cursor-pointer transition-colors {{ $reportMode === 'live' ? 'border-pulse-orange-500 bg-white' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                                        <input type="radio" wire:model.live="reportMode" value="live" class="sr-only" />
                                        <x-icon name="link" class="w-4 h-4 mr-2 {{ $reportMode === 'live' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                        <div>
                                            <span class="text-sm font-medium {{ $reportMode === 'live' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Live Link</span>
                                            <p class="text-xs text-gray-500">Real-time data</p>
                                        </div>
                                    </label>
                                    <label class="flex-1 flex items-center p-2 border rounded-lg cursor-pointer transition-colors {{ $reportMode === 'static' ? 'border-pulse-orange-500 bg-white' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                                        <input type="radio" wire:model.live="reportMode" value="static" class="sr-only" />
                                        <x-icon name="document" class="w-4 h-4 mr-2 {{ $reportMode === 'static' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                        <div>
                                            <span class="text-sm font-medium {{ $reportMode === 'static' ? 'text-pulse-orange-700' : 'text-gray-700' }}">PDF Snapshot</span>
                                            <p class="text-xs text-gray-500">Static attachment</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        @endif
                    @else
                        <p class="text-sm text-gray-500">
                            No reports available.
                            <a href="{{ route('reports.index') }}" class="text-pulse-orange-600 hover:underline">Create one</a>
                        </p>
                    @endif
                </div>

                <!-- Message Body -->
                <div x-data="{ messageBody: @entangle('messageBody') }">
                    <label for="messageBody" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea
                        id="messageBody"
                        x-model="messageBody"
                        rows="5"
                        placeholder="Type your message here..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    ></textarea>

                    <div class="mt-2">
                        <p class="text-xs text-gray-500 mb-1.5">Insert merge fields:</p>
                        <div class="flex flex-wrap gap-1">
                            @php
                                $mergeFields = ['{{first_name}}', '{{last_name}}', '{{full_name}}', '{{email}}', '{{organization_name}}'];
                            @endphp
                            @foreach($mergeFields as $field)
                                <button
                                    type="button"
                                    x-on:click="messageBody = (messageBody || '') + '{{ $field }} '"
                                    class="px-2 py-0.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded font-mono transition-colors"
                                >
                                    {{ $field }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Schedule -->
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Schedule</h3>

            <div class="space-y-4">
                @if($distributionType === 'one_time')
                    <div class="flex gap-3">
                        <label class="flex-1 flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ $sendImmediately ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" wire:model.live="sendImmediately" value="1" class="sr-only" />
                            <x-icon name="bolt" class="w-5 h-5 mr-2 {{ $sendImmediately ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                            <span class="text-sm font-medium {{ $sendImmediately ? 'text-pulse-orange-700' : 'text-gray-700' }}">Send immediately</span>
                        </label>
                        <label class="flex-1 flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ !$sendImmediately ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" wire:model.live="sendImmediately" value="0" class="sr-only" />
                            <x-icon name="clock" class="w-5 h-5 mr-2 {{ !$sendImmediately ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                            <span class="text-sm font-medium {{ !$sendImmediately ? 'text-pulse-orange-700' : 'text-gray-700' }}">Schedule for later</span>
                        </label>
                    </div>

                    @if(!$sendImmediately)
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="scheduledFor" class="block text-sm font-medium text-gray-700 mb-1">Date & Time</label>
                                <input
                                    type="datetime-local"
                                    id="scheduledFor"
                                    wire:model.live="scheduledFor"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                />
                            </div>
                            <div>
                                <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                                <select
                                    id="timezone"
                                    wire:model="timezone"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                >
                                    <option value="America/New_York">Eastern Time</option>
                                    <option value="America/Chicago">Central Time</option>
                                    <option value="America/Denver">Mountain Time</option>
                                    <option value="America/Los_Angeles">Pacific Time</option>
                                </select>
                            </div>
                        </div>
                    @endif
                @else
                    <!-- Recurring Schedule -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Repeat</label>
                            <div class="flex items-center gap-2">
                                <span class="text-sm text-gray-500">Every</span>
                                <input
                                    type="number"
                                    wire:model="intervalValue"
                                    min="1"
                                    max="30"
                                    class="w-16 px-2 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-center"
                                />
                                <select
                                    wire:model="intervalType"
                                    class="px-3 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                >
                                    <option value="daily">day(s)</option>
                                    <option value="weekly">week(s)</option>
                                    <option value="monthly">month(s)</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="sendTime" class="block text-sm font-medium text-gray-700 mb-1">Send Time</label>
                            <input
                                type="time"
                                id="sendTime"
                                wire:model="sendTime"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            />
                        </div>
                    </div>

                    <div>
                        <label for="timezone-recurring" class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                        <select
                            id="timezone-recurring"
                            wire:model="timezone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                            <option value="America/New_York">Eastern Time</option>
                            <option value="America/Chicago">Central Time</option>
                            <option value="America/Denver">Mountain Time</option>
                            <option value="America/Los_Angeles">Pacific Time</option>
                        </select>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Sticky Footer -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-8 py-4 z-40">
        <div class="max-w-3xl mx-auto flex items-center justify-between">
            <a
                href="{{ route('distribute.index') }}"
                class="text-sm font-medium text-gray-600 hover:text-gray-900"
            >
                Cancel
            </a>

            <div class="flex items-center gap-3">
                <button
                    wire:click="save"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Save Draft
                </button>

                @php
                    $isValid = $title && (count($selectedContactListIds) > 0 || count($selectedContactIds) > 0);
                @endphp

                <button
                    wire:click="save"
                    @if(!$isValid) disabled @endif
                    class="inline-flex items-center px-5 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <x-icon name="paper-airplane" class="w-4 h-4 mr-2 transform -rotate-45" />
                    @if($distributionType === 'one_time' && $sendImmediately)
                        Send Now
                    @elseif($distributionType === 'one_time' && !$sendImmediately)
                        Schedule
                    @else
                        Activate
                    @endif
                </button>
            </div>
        </div>
    </div>
</div>
