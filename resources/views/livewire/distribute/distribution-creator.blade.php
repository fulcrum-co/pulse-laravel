<div
    x-data="{
        openSection: 'basics',
        toggle(section) {
            this.openSection = this.openSection === section ? null : section;
        }
    }"
    class="min-h-screen pb-24"
>
    <div class="max-w-3xl mx-auto space-y-4">

        <!-- Section 1: Basics -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <button
                @click="toggle('basics')"
                class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    @php
                        $basicsComplete = $title && $channel;
                    @endphp
                    <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $basicsComplete ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                        @if($basicsComplete)
                            <x-icon name="check" class="w-4 h-4" />
                        @else
                            <span class="text-sm font-medium">1</span>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Basics</h3>
                        <p class="text-sm text-gray-500">
                            @if($basicsComplete)
                                {{ $title }} &bull; {{ ucfirst($channel) }} &bull; {{ $distributionType === 'recurring' ? 'Recurring' : 'One-time' }}
                            @else
                                Title, channel, and frequency
                            @endif
                        </p>
                    </div>
                </div>
                <x-icon
                    name="chevron-down"
                    class="w-5 h-5 text-gray-400 transition-transform"
                    x-bind:class="openSection === 'basics' ? 'rotate-180' : ''"
                />
            </button>

            <div x-show="openSection === 'basics'" x-collapse>
                <div class="px-6 pb-6 pt-2 border-t border-gray-100 space-y-5">
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

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Channel</label>
                            <div class="flex gap-3">
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
                            <div class="flex gap-3">
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
        </div>

        <!-- Section 2: Compose -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <button
                @click="toggle('compose')"
                class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    @php
                        $composeComplete = (count($selectedContactListIds) > 0 || count($selectedContactIds) > 0) && ($channel !== 'email' || $subject);
                    @endphp
                    <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $composeComplete ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                        @if($composeComplete)
                            <x-icon name="check" class="w-4 h-4" />
                        @else
                            <span class="text-sm font-medium">2</span>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Compose</h3>
                        <p class="text-sm text-gray-500">
                            @if($composeComplete)
                                {{ $this->selectedRecipientsCount }} recipients
                                @if(count($selectedReportIds) > 0)
                                    &bull; {{ count($selectedReportIds) }} report(s)
                                @endif
                            @else
                                Recipients, subject, and message
                            @endif
                        </p>
                    </div>
                </div>
                <x-icon
                    name="chevron-down"
                    class="w-5 h-5 text-gray-400 transition-transform"
                    x-bind:class="openSection === 'compose' ? 'rotate-180' : ''"
                />
            </button>

            <div x-show="openSection === 'compose'" x-collapse>
                <div class="px-6 pb-6 pt-2 border-t border-gray-100 space-y-6">

                    <!-- To: Recipients -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">To <span class="text-red-500">*</span></label>

                        <!-- Contact Lists as clickable chips -->
                        @if($contactLists->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach($contactLists as $list)
                                    <button
                                        type="button"
                                        wire:click="toggleContactList({{ $list->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-all {{ in_array($list->id, $selectedContactListIds) ? 'bg-pulse-orange-500 text-white shadow-sm' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}"
                                    >
                                        @if(in_array($list->id, $selectedContactListIds))
                                            <x-icon name="check-circle" class="w-4 h-4" />
                                        @else
                                            <x-icon name="user-group" class="w-4 h-4 opacity-60" />
                                        @endif
                                        {{ $list->name }}
                                        <span class="opacity-75">({{ $list->members_count }})</span>
                                    </button>
                                @endforeach
                            </div>
                        @else
                            <div class="text-sm text-gray-500 bg-gray-50 rounded-lg p-4 text-center">
                                No contact lists available.
                                <a href="{{ route('contacts.lists') }}" class="text-pulse-orange-600 hover:underline font-medium">Create one</a>
                            </div>
                        @endif

                        @if($this->selectedRecipientsCount > 0)
                            <p class="mt-3 text-sm text-green-600 font-medium">
                                <x-icon name="check-circle" class="w-4 h-4 inline -mt-0.5" />
                                {{ $this->selectedRecipientsCount }} recipient(s) selected
                            </p>
                        @endif
                    </div>

                    <!-- Subject Line (email only) -->
                    @if($channel === 'email')
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
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
                    <div class="border-t border-gray-100 pt-5">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-sm font-medium text-gray-700">Attach Reports</label>
                            <button
                                type="button"
                                wire:click="$toggle('linkReports')"
                                class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $linkReports ? 'bg-pulse-orange-500' : 'bg-gray-200' }}"
                            >
                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $linkReports ? 'translate-x-4' : 'translate-x-0' }}"></span>
                            </button>
                        </div>

                        @if($linkReports)
                            @if($reports->isNotEmpty())
                                <!-- Simple report selection -->
                                <div class="space-y-2 mb-4">
                                    @foreach($reports as $report)
                                        <button
                                            type="button"
                                            wire:click="toggleReport({{ $report->id }})"
                                            class="w-full flex items-center gap-3 p-3 rounded-lg border text-left transition-all {{ in_array($report->id, $selectedReportIds) ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300 bg-white' }}"
                                        >
                                            <div class="flex-shrink-0 w-5 h-5 rounded border-2 flex items-center justify-center {{ in_array($report->id, $selectedReportIds) ? 'border-pulse-orange-500 bg-pulse-orange-500' : 'border-gray-300' }}">
                                                @if(in_array($report->id, $selectedReportIds))
                                                    <x-icon name="check" class="w-3 h-3 text-white" />
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900">{{ $report->report_name }}</p>
                                                @if($report->report_description)
                                                    <p class="text-xs text-gray-500 truncate">{{ $report->report_description }}</p>
                                                @endif
                                            </div>
                                            <x-icon name="document-chart-bar" class="w-5 h-5 text-gray-400 flex-shrink-0" />
                                        </button>
                                    @endforeach
                                </div>

                                <!-- Delivery mode (compact) -->
                                @if(count($selectedReportIds) > 0)
                                    <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                                        <span class="text-sm text-gray-600">Deliver as:</span>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" wire:model.live="reportMode" value="live" class="text-pulse-orange-500 focus:ring-pulse-orange-500" />
                                            <span class="text-sm {{ $reportMode === 'live' ? 'text-gray-900 font-medium' : 'text-gray-600' }}">Live Link</span>
                                        </label>
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="radio" wire:model.live="reportMode" value="static" class="text-pulse-orange-500 focus:ring-pulse-orange-500" />
                                            <span class="text-sm {{ $reportMode === 'static' ? 'text-gray-900 font-medium' : 'text-gray-600' }}">PDF Attachment</span>
                                        </label>
                                    </div>
                                @endif
                            @else
                                <div class="text-sm text-gray-500 bg-gray-50 rounded-lg p-4 text-center">
                                    No reports available.
                                    <a href="{{ route('reports.index') }}" class="text-pulse-orange-600 hover:underline font-medium">Create one</a>
                                </div>
                            @endif
                        @endif
                    </div>

                    <!-- Message Body -->
                    <div x-data="{ messageBody: @entangle('messageBody') }">
                        <label for="messageBody" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea
                            id="messageBody"
                            x-model="messageBody"
                            rows="4"
                            placeholder="Type your message here..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        ></textarea>

                        <div class="mt-2 flex items-center gap-2 flex-wrap">
                            <span class="text-xs text-gray-500">Merge:</span>
                            @foreach(['{{first_name}}', '{{last_name}}', '{{email}}'] as $field)
                                <button
                                    type="button"
                                    x-on:click="messageBody = (messageBody || '') + '{{ $field }} '"
                                    class="px-1.5 py-0.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-600 rounded font-mono transition-colors"
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
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <button
                @click="toggle('schedule')"
                class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    @php
                        $scheduleComplete = $distributionType === 'one_time'
                            ? ($sendImmediately || $scheduledFor)
                            : ($sendTime);
                    @endphp
                    <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $scheduleComplete ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                        @if($scheduleComplete)
                            <x-icon name="check" class="w-4 h-4" />
                        @else
                            <span class="text-sm font-medium">3</span>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Schedule</h3>
                        <p class="text-sm text-gray-500">
                            @if($distributionType === 'one_time')
                                @if($sendImmediately)
                                    Send immediately
                                @elseif($scheduledFor)
                                    {{ \Carbon\Carbon::parse($scheduledFor)->format('M j, Y \a\t g:i A') }}
                                @else
                                    When to send
                                @endif
                            @else
                                Every {{ $intervalValue }} {{ Str::plural(str_replace('ly', '', $intervalType), $intervalValue) }} at {{ \Carbon\Carbon::parse($sendTime)->format('g:i A') }}
                            @endif
                        </p>
                    </div>
                </div>
                <x-icon
                    name="chevron-down"
                    class="w-5 h-5 text-gray-400 transition-transform"
                    x-bind:class="openSection === 'schedule' ? 'rotate-180' : ''"
                />
            </button>

            <div x-show="openSection === 'schedule'" x-collapse>
                <div class="px-6 pb-6 pt-2 border-t border-gray-100 space-y-5">
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
                                <span class="text-sm font-medium {{ !$sendImmediately ? 'text-pulse-orange-700' : 'text-gray-700' }}">Schedule</span>
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
                                        <option value="America/New_York">Eastern</option>
                                        <option value="America/Chicago">Central</option>
                                        <option value="America/Denver">Mountain</option>
                                        <option value="America/Los_Angeles">Pacific</option>
                                    </select>
                                </div>
                            </div>
                        @endif
                    @else
                        <div class="grid grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Every</label>
                                <div class="flex items-center gap-2">
                                    <input
                                        type="number"
                                        wire:model="intervalValue"
                                        min="1"
                                        max="30"
                                        class="w-16 px-2 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-center"
                                    />
                                    <select
                                        wire:model="intervalType"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                    >
                                        <option value="daily">day(s)</option>
                                        <option value="weekly">week(s)</option>
                                        <option value="monthly">month(s)</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="sendTime" class="block text-sm font-medium text-gray-700 mb-1">At</label>
                                <input
                                    type="time"
                                    id="sendTime"
                                    wire:model="sendTime"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                />
                            </div>

                            <div>
                                <label for="timezone-recurring" class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                                <select
                                    id="timezone-recurring"
                                    wire:model="timezone"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                >
                                    <option value="America/New_York">Eastern</option>
                                    <option value="America/Chicago">Central</option>
                                    <option value="America/Denver">Mountain</option>
                                    <option value="America/Los_Angeles">Pacific</option>
                                </select>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sticky Footer -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-8 py-4 z-40">
        <div class="max-w-3xl mx-auto flex items-center justify-between">
            <a href="{{ route('distribute.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                Cancel
            </a>

            <div class="flex items-center gap-3">
                <button
                    wire:click="save"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                >
                    Save Draft
                </button>

                @php
                    $isValid = $title && (count($selectedContactListIds) > 0 || count($selectedContactIds) > 0);
                @endphp

                <button
                    wire:click="save"
                    @if(!$isValid) disabled @endif
                    class="px-5 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    @if($distributionType === 'one_time' && $sendImmediately)
                        Send Now
                    @elseif($distributionType === 'one_time')
                        Schedule
                    @else
                        Activate
                    @endif
                </button>
            </div>
        </div>
    </div>
</div>
