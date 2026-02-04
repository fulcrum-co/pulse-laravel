<div
    x-data="{
        openSection: 'basics',
        toggle(section) {
            this.openSection = this.openSection === section ? null : section;
        },
        showListDropdown: false
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
                    @php $basicsComplete = $title && $channel; @endphp
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
                <x-icon name="chevron-down" class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="openSection === 'basics' ? 'rotate-180' : ''" />
            </button>

            <div x-show="openSection === 'basics'" x-collapse>
                <div class="px-6 pb-6 pt-2 border-t border-gray-100 space-y-5">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                        <input type="text" id="title" wire:model.blur="title" placeholder="e.g., Weekly Progress Report" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500" />
                        @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea id="description" wire:model.blur="description" rows="2" placeholder="Brief description..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"></textarea>
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
                    @php $composeComplete = (count($selectedContactListIds) > 0 || count($selectedContactIds) > 0) && ($channel !== 'email' || $subject); @endphp
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
                                @if(count($selectedReportIds) > 0) &bull; {{ count($selectedReportIds) }} report(s) @endif
                            @else
                                Recipients, subject, and message
                            @endif
                        </p>
                    </div>
                </div>
                <x-icon name="chevron-down" class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="openSection === 'compose' ? 'rotate-180' : ''" />
            </button>

            <div x-show="openSection === 'compose'" x-collapse>
                <div class="px-6 pb-6 pt-2 border-t border-gray-100 space-y-5">

                    <!-- To: Recipients (HubSpot style) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To <span class="text-red-500">*</span></label>

                        <!-- Input box with tags -->
                        <div class="border border-gray-300 rounded-lg focus-within:ring-2 focus-within:ring-pulse-orange-500 focus-within:border-pulse-orange-500">
                            <div class="flex flex-wrap gap-1.5 p-2 min-h-[42px]">
                                <!-- Selected contact list tags -->
                                @foreach($selectedContactListIds as $listId)
                                    @php $list = $contactLists->firstWhere('id', $listId); @endphp
                                    @if($list)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">
                                            <x-icon name="user-group" class="w-3.5 h-3.5" />
                                            {{ $list->name }}
                                            <button type="button" wire:click="toggleContactList({{ $listId }})" class="hover:text-blue-900 ml-0.5">
                                                <x-icon name="x-mark" class="w-3.5 h-3.5" />
                                            </button>
                                        </span>
                                    @endif
                                @endforeach

                                <!-- Selected individual contact tags -->
                                @foreach($selectedContactIds as $contactId)
                                    @php $contact = $selectedContacts->get($contactId); @endphp
                                    @if($contact)
                                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-100 text-gray-800 rounded text-sm">
                                            <x-icon name="user" class="w-3.5 h-3.5" />
                                            {{ $contact->user->first_name }} {{ $contact->user->last_name }}
                                            <button type="button" wire:click="toggleContact({{ $contactId }})" class="hover:text-gray-900 ml-0.5">
                                                <x-icon name="x-mark" class="w-3.5 h-3.5" />
                                            </button>
                                        </span>
                                    @endif
                                @endforeach

                                <!-- Search input -->
                                <input
                                    type="text"
                                    wire:model.live.debounce.300ms="contactSearch"
                                    placeholder="{{ count($selectedContactListIds) + count($selectedContactIds) > 0 ? 'Add more...' : 'Search contacts by name or email...' }}"
                                    class="flex-1 min-w-[200px] border-0 p-1 text-sm focus:ring-0 placeholder-gray-400"
                                />
                            </div>

                            <!-- Search results dropdown -->
                            @if($contactSearch && $contacts->isNotEmpty())
                                <div class="border-t border-gray-200 max-h-48 overflow-y-auto">
                                    @foreach($contacts as $contact)
                                        <button
                                            type="button"
                                            wire:click="toggleContact({{ $contact->id }})"
                                            class="w-full flex items-center gap-3 px-3 py-2 hover:bg-gray-50 text-left {{ in_array($contact->id, $selectedContactIds) ? 'bg-gray-50' : '' }}"
                                        >
                                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 text-xs font-medium">
                                                {{ substr($contact->user->first_name ?? '', 0, 1) }}{{ substr($contact->user->last_name ?? '', 0, 1) }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-gray-900">{{ $contact->user->first_name }} {{ $contact->user->last_name }}</p>
                                                <p class="text-xs text-gray-500 truncate">{{ $contact->user->email }}</p>
                                            </div>
                                            @if(in_array($contact->id, $selectedContactIds))
                                                <x-icon name="check" class="w-4 h-4 text-pulse-orange-500" />
                                            @endif
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        <!-- Contact Lists dropdown -->
                        <div class="mt-3 relative" x-data="{ open: false }">
                            <button
                                type="button"
                                @click="open = !open"
                                class="inline-flex items-center gap-2 px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                            >
                                <x-icon name="user-group" class="w-4 h-4" />
                                Add from list
                                <x-icon name="chevron-down" class="w-4 h-4" x-bind:class="open ? 'rotate-180' : ''" />
                            </button>

                            <!-- Lists dropdown panel -->
                            <div
                                x-show="open"
                                @click.away="open = false"
                                x-transition
                                class="absolute z-20 mt-1 w-72 bg-white border border-gray-200 rounded-lg shadow-lg"
                            >
                                <div class="p-2">
                                    <p class="px-2 py-1 text-xs font-medium text-gray-500 uppercase">Contact Lists</p>
                                    @forelse($contactLists as $list)
                                        <button
                                            type="button"
                                            wire:click="toggleContactList({{ $list->id }})"
                                            class="w-full flex items-center justify-between px-2 py-2 rounded hover:bg-gray-50 text-left"
                                        >
                                            <div class="flex items-center gap-2">
                                                <div class="w-8 h-8 rounded bg-blue-100 flex items-center justify-center">
                                                    <x-icon name="user-group" class="w-4 h-4 text-blue-600" />
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $list->name }}</p>
                                                    <p class="text-xs text-gray-500">{{ $list->members_count }} contacts</p>
                                                </div>
                                            </div>
                                            @if(in_array($list->id, $selectedContactListIds))
                                                <x-icon name="check-circle" class="w-5 h-5 text-pulse-orange-500" />
                                            @endif
                                        </button>
                                    @empty
                                        <p class="px-2 py-3 text-sm text-gray-500 text-center">
                                            No lists available.
                                            <a href="{{ route('contacts.lists') }}" class="text-pulse-orange-600 hover:underline">Create one</a>
                                        </p>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        @if($this->selectedRecipientsCount > 0)
                            <p class="mt-2 text-xs text-gray-500">{{ $this->selectedRecipientsCount }} total recipient(s)</p>
                        @endif
                    </div>

                    <!-- Subject Line (email only) -->
                    @if($channel === 'email')
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject <span class="text-red-500">*</span></label>
                            <input type="text" id="subject" wire:model.blur="subject" placeholder="e.g., Your Weekly Progress Report" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500" />
                        </div>
                    @endif

                    <!-- Attach Reports -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-sm font-medium text-gray-700">Attach Reports</label>
                            <button
                                type="button"
                                wire:click="$toggle('linkReports')"
                                class="relative inline-flex h-5 w-9 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 {{ $linkReports ? 'bg-pulse-orange-500' : 'bg-gray-200' }}"
                            >
                                <span class="pointer-events-none inline-block h-4 w-4 transform rounded-full bg-white shadow transition duration-200 {{ $linkReports ? 'translate-x-4' : 'translate-x-0' }}"></span>
                            </button>
                        </div>

                        @if($linkReports)
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <!-- Selected reports as tags + search input -->
                                <div class="flex flex-wrap gap-1.5 p-2 min-h-[42px] border-b border-gray-200">
                                    @foreach($selectedReportIds as $reportId)
                                        @php $report = $selectedReports->get($reportId); @endphp
                                        @if($report)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-pulse-orange-100 text-pulse-orange-800 rounded text-sm">
                                                <x-icon name="document-chart-bar" class="w-3.5 h-3.5" />
                                                {{ $report->report_name }}
                                                <button type="button" wire:click="toggleReport({{ $reportId }})" class="hover:text-pulse-orange-900 ml-0.5">
                                                    <x-icon name="x-mark" class="w-3.5 h-3.5" />
                                                </button>
                                            </span>
                                        @endif
                                    @endforeach
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="reportSearch"
                                        placeholder="{{ count($selectedReportIds) > 0 ? 'Add more reports...' : 'Search reports...' }}"
                                        class="flex-1 min-w-[150px] border-0 p-1 text-sm focus:ring-0 placeholder-gray-400"
                                    />
                                </div>

                                <!-- Report list (filtered by search) -->
                                <div class="max-h-48 overflow-y-auto">
                                    @forelse($reports as $report)
                                        @if(!in_array($report->id, $selectedReportIds))
                                            <button
                                                type="button"
                                                wire:click="toggleReport({{ $report->id }})"
                                                class="w-full flex items-center gap-3 px-3 py-2.5 text-left border-b border-gray-100 last:border-b-0 hover:bg-gray-50"
                                            >
                                                <div class="w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0 border-gray-300">
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-900">{{ $report->report_name }}</p>
                                                </div>
                                            </button>
                                        @endif
                                    @empty
                                        <p class="px-3 py-4 text-sm text-gray-500 text-center">
                                            @if($reportSearch)
                                                No reports match "{{ $reportSearch }}"
                                            @else
                                                No reports available. <a href="{{ route('reports.index') }}" class="text-pulse-orange-600 hover:underline">Create one</a>
                                            @endif
                                        </p>
                                    @endforelse
                                </div>

                                @if(count($selectedReportIds) > 0)
                                    <div class="px-3 py-2 bg-gray-50 border-t border-gray-200 flex items-center gap-4">
                                        <span class="text-xs text-gray-500">Deliver as:</span>
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" wire:model.live="reportMode" value="live" class="w-3.5 h-3.5 text-pulse-orange-500 focus:ring-pulse-orange-500" />
                                            <span class="text-xs text-gray-700">Live Link</span>
                                        </label>
                                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                            <input type="radio" wire:model.live="reportMode" value="static" class="w-3.5 h-3.5 text-pulse-orange-500 focus:ring-pulse-orange-500" />
                                            <span class="text-xs text-gray-700">PDF</span>
                                        </label>
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>

                    <!-- Message Body -->
                    <div x-data="{ messageBody: @entangle('messageBody') }">
                        <label for="messageBody" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea id="messageBody" x-model="messageBody" rows="4" placeholder="Type your message here..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"></textarea>
                        <div class="mt-1.5 flex items-center gap-1.5 flex-wrap">
                            <span class="text-xs text-gray-400">Insert:</span>
                            @foreach(['{{first_name}}', '{{last_name}}', '{{email}}'] as $field)
                                <button type="button" x-on:click="messageBody = (messageBody || '') + '{{ $field }} '" class="px-1.5 py-0.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-500 rounded font-mono">{{ $field }}</button>
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
                    @php $scheduleComplete = $distributionType === 'one_time' ? ($sendImmediately || $scheduledFor) : ($sendTime); @endphp
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
                                @if($sendImmediately) Send immediately @elseif($scheduledFor) {{ \Carbon\Carbon::parse($scheduledFor)->format('M j, Y \a\t g:i A') }} @else When to send @endif
                            @else
                                Every {{ $intervalValue }} {{ Str::plural(str_replace('ly', '', $intervalType), $intervalValue) }} at {{ \Carbon\Carbon::parse($sendTime)->format('g:i A') }}
                            @endif
                        </p>
                    </div>
                </div>
                <x-icon name="chevron-down" class="w-5 h-5 text-gray-400 transition-transform" x-bind:class="openSection === 'schedule' ? 'rotate-180' : ''" />
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
                                    <input type="datetime-local" id="scheduledFor" wire:model.live="scheduledFor" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500" />
                                </div>
                                <div>
                                    <label for="timezone" class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                                    <select id="timezone" wire:model="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
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
                                    <input type="number" wire:model="intervalValue" min="1" max="30" class="w-16 px-2 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-center" />
                                    <select wire:model="intervalType" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                                        <option value="daily">day(s)</option>
                                        <option value="weekly">week(s)</option>
                                        <option value="monthly">month(s)</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label for="sendTime" class="block text-sm font-medium text-gray-700 mb-1">At</label>
                                <input type="time" id="sendTime" wire:model="sendTime" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500" />
                            </div>
                            <div>
                                <label for="timezone-recurring" class="block text-sm font-medium text-gray-700 mb-1">Timezone</label>
                                <select id="timezone-recurring" wire:model="timezone" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
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
            <a href="{{ route('distribute.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">Cancel</a>
            <div class="flex items-center gap-3">
                <button wire:click="save" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Save Draft</button>
                @php $isValid = $title && (count($selectedContactListIds) > 0 || count($selectedContactIds) > 0); @endphp
                <button wire:click="save" @if(!$isValid) disabled @endif class="px-5 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 disabled:opacity-50 disabled:cursor-not-allowed">
                    @if($distributionType === 'one_time' && $sendImmediately) Send Now @elseif($distributionType === 'one_time') Schedule @else Activate @endif
                </button>
            </div>
        </div>
    </div>
</div>
