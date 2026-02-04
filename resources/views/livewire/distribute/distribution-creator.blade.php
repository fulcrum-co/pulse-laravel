<div
    x-data="{
        openSection: 'basics',
        toggle(section) {
            this.openSection = this.openSection === section ? null : section;
        },
        linkReports: @entangle('linkReports')
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
                                    &bull; {{ count($selectedReportIds) }} report(s) linked
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
                <div class="px-6 pb-6 pt-2 border-t border-gray-100 space-y-5">

                    <!-- To: Recipients Box -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">To <span class="text-red-500">*</span></label>
                        <div class="border border-gray-300 rounded-lg p-3 min-h-[80px] focus-within:ring-2 focus-within:ring-pulse-orange-500 focus-within:border-pulse-orange-500">
                            <!-- Selected items as tags -->
                            <div class="flex flex-wrap gap-2 mb-2">
                                @foreach($selectedContactListIds as $listId)
                                    @php $list = $contactLists->firstWhere('id', $listId); @endphp
                                    @if($list)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-pulse-orange-100 text-pulse-orange-800 rounded-full text-sm">
                                            <x-icon name="user-group" class="w-3.5 h-3.5" />
                                            {{ $list->name }} ({{ $list->members_count }})
                                            <button type="button" wire:click="toggleContactList({{ $listId }})" class="ml-0.5 hover:text-pulse-orange-900">
                                                <x-icon name="x-mark" class="w-3.5 h-3.5" />
                                            </button>
                                        </span>
                                    @endif
                                @endforeach
                                @foreach($selectedContactIds as $contactId)
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">
                                        <x-icon name="user" class="w-3.5 h-3.5" />
                                        Contact #{{ $contactId }}
                                        <button type="button" wire:click="toggleContact({{ $contactId }})" class="ml-0.5 hover:text-blue-900">
                                            <x-icon name="x-mark" class="w-3.5 h-3.5" />
                                        </button>
                                    </span>
                                @endforeach
                            </div>

                            <!-- Search/Add input -->
                            <div class="relative" x-data="{ showDropdown: false }">
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="contactSearch"
                                    @focus="showDropdown = true"
                                    @click.away="showDropdown = false"
                                    placeholder="Search contacts or select a list..."
                                    class="w-full border-0 p-0 text-sm focus:ring-0 placeholder-gray-400"
                                />

                                <!-- Dropdown for lists and contacts -->
                                <div
                                    x-show="showDropdown"
                                    x-transition
                                    class="absolute z-20 left-0 right-0 mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-64 overflow-y-auto"
                                >
                                    <!-- Contact Lists Section -->
                                    @if($contactLists->isNotEmpty())
                                        <div class="p-2 border-b border-gray-100">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide px-2 py-1">Contact Lists</p>
                                            @foreach($contactLists as $list)
                                                <button
                                                    type="button"
                                                    wire:click="toggleContactList({{ $list->id }})"
                                                    class="w-full flex items-center justify-between px-2 py-2 rounded-md hover:bg-gray-50 {{ in_array($list->id, $selectedContactListIds) ? 'bg-pulse-orange-50' : '' }}"
                                                >
                                                    <span class="flex items-center gap-2">
                                                        <x-icon name="user-group" class="w-4 h-4 text-gray-400" />
                                                        <span class="text-sm text-gray-900">{{ $list->name }}</span>
                                                        <span class="text-xs text-gray-500">({{ $list->members_count }})</span>
                                                    </span>
                                                    @if(in_array($list->id, $selectedContactListIds))
                                                        <x-icon name="check" class="w-4 h-4 text-pulse-orange-500" />
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    @endif

                                    <!-- Individual Contacts Section (from search) -->
                                    @if($contacts->isNotEmpty())
                                        <div class="p-2">
                                            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide px-2 py-1">Contacts</p>
                                            @foreach($contacts as $contact)
                                                <button
                                                    type="button"
                                                    wire:click="toggleContact({{ $contact->id }})"
                                                    class="w-full flex items-center justify-between px-2 py-2 rounded-md hover:bg-gray-50 {{ in_array($contact->id, $selectedContactIds) ? 'bg-blue-50' : '' }}"
                                                >
                                                    <span class="flex items-center gap-2">
                                                        <x-icon name="user" class="w-4 h-4 text-gray-400" />
                                                        <span class="text-sm text-gray-900">{{ $contact->user->first_name }} {{ $contact->user->last_name }}</span>
                                                        <span class="text-xs text-gray-500">{{ $contact->user->email }}</span>
                                                    </span>
                                                    @if(in_array($contact->id, $selectedContactIds))
                                                        <x-icon name="check" class="w-4 h-4 text-blue-500" />
                                                    @endif
                                                </button>
                                            @endforeach
                                        </div>
                                    @elseif($contactSearch && $contacts->isEmpty())
                                        <div class="p-4 text-center text-sm text-gray-500">
                                            No contacts found for "{{ $contactSearch }}"
                                        </div>
                                    @endif

                                    @if($contactLists->isEmpty() && $contacts->isEmpty() && !$contactSearch)
                                        <div class="p-4 text-center text-sm text-gray-500">
                                            No contact lists available.
                                            <a href="{{ route('contacts.lists') }}" class="text-pulse-orange-600 hover:underline">Create one</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @if($this->selectedRecipientsCount > 0)
                            <p class="mt-1.5 text-xs text-gray-500">{{ $this->selectedRecipientsCount }} total recipient(s)</p>
                        @endif
                    </div>

                    <!-- Subject Line (email only) -->
                    @if($channel === 'email')
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject Line <span class="text-red-500">*</span></label>
                            <input
                                type="text"
                                id="subject"
                                wire:model.blur="subject"
                                placeholder="e.g., Your Weekly Progress Report"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            />
                        </div>
                    @endif

                    <!-- Link Reports Toggle -->
                    <div>
                        <label class="flex items-center justify-between cursor-pointer">
                            <span class="text-sm font-medium text-gray-700">Link Report(s)</span>
                            <button
                                type="button"
                                wire:click="$toggle('linkReports')"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-pulse-orange-500 focus:ring-offset-2 {{ $linkReports ? 'bg-pulse-orange-500' : 'bg-gray-200' }}"
                            >
                                <span class="sr-only">Link reports</span>
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $linkReports ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </label>

                        <!-- Reports selection box (shown when toggle is on) -->
                        @if($linkReports)
                            <div class="mt-3 border border-gray-200 rounded-lg p-4 bg-gray-50">
                                @if($reports->isNotEmpty())
                                    <div class="space-y-2 mb-4">
                                        @foreach($reports as $report)
                                            <label class="flex items-center p-3 bg-white border rounded-lg cursor-pointer transition-colors {{ in_array($report->id, $selectedReportIds) ? 'border-pulse-orange-500 ring-1 ring-pulse-orange-500' : 'border-gray-200 hover:border-gray-300' }}">
                                                <input
                                                    type="checkbox"
                                                    wire:click="toggleReport({{ $report->id }})"
                                                    @checked(in_array($report->id, $selectedReportIds))
                                                    class="w-4 h-4 text-pulse-orange-500 border-gray-300 rounded focus:ring-pulse-orange-500"
                                                />
                                                <div class="ml-3 flex-1">
                                                    <span class="text-sm font-medium text-gray-900">{{ $report->report_name }}</span>
                                                    @if($report->report_description)
                                                        <p class="text-xs text-gray-500 truncate">{{ Str::limit($report->report_description, 60) }}</p>
                                                    @endif
                                                </div>
                                                <x-icon name="chart-bar" class="w-5 h-5 text-gray-400" />
                                            </label>
                                        @endforeach
                                    </div>

                                    <!-- Report Mode -->
                                    @if(count($selectedReportIds) > 0)
                                        <div class="pt-3 border-t border-gray-200">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Mode</label>
                                            <div class="flex gap-3">
                                                <label class="flex-1 flex items-center p-2.5 bg-white border rounded-lg cursor-pointer transition-colors {{ $reportMode === 'live' ? 'border-pulse-orange-500 ring-1 ring-pulse-orange-500' : 'border-gray-200 hover:border-gray-300' }}">
                                                    <input type="radio" wire:model.live="reportMode" value="live" class="sr-only" />
                                                    <x-icon name="link" class="w-4 h-4 mr-2 {{ $reportMode === 'live' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                                    <div>
                                                        <span class="text-sm font-medium {{ $reportMode === 'live' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Live Link</span>
                                                        <p class="text-xs text-gray-500">Real-time data</p>
                                                    </div>
                                                </label>
                                                <label class="flex-1 flex items-center p-2.5 bg-white border rounded-lg cursor-pointer transition-colors {{ $reportMode === 'static' ? 'border-pulse-orange-500 ring-1 ring-pulse-orange-500' : 'border-gray-200 hover:border-gray-300' }}">
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
                                    <p class="text-sm text-gray-500 text-center py-2">
                                        No reports available.
                                        <a href="{{ route('reports.index') }}" class="text-pulse-orange-600 hover:underline">Create one</a>
                                    </p>
                                @endif
                            </div>
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
                                    Send immediately when activated
                                @elseif($scheduledFor)
                                    Scheduled for {{ \Carbon\Carbon::parse($scheduledFor)->format('M j, Y g:i A') }}
                                @else
                                    When to send
                                @endif
                            @else
                                Every {{ $intervalValue }} {{ Str::plural(str_replace('ly', '', $intervalType), $intervalValue) }} at {{ $sendTime }}
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
