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
                    <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $title ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                        @if($title)
                            <x-icon name="check" class="w-4 h-4" />
                        @else
                            <span class="text-sm font-medium">1</span>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Basics</h3>
                        <p class="text-sm text-gray-500">
                            @if($title)
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
                            placeholder="Brief description of this distribution..."
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
                                    <x-icon name="bolt" class="w-5 h-5 mr-2 {{ $distributionType === 'one_time' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                    <span class="text-sm font-medium {{ $distributionType === 'one_time' ? 'text-pulse-orange-700' : 'text-gray-700' }}">One-time</span>
                                </label>
                                <label class="flex-1 flex items-center justify-center p-3 border rounded-lg cursor-pointer transition-colors {{ $distributionType === 'recurring' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" wire:model.live="distributionType" value="recurring" class="sr-only" />
                                    <x-icon name="arrow-path" class="w-5 h-5 mr-2 {{ $distributionType === 'recurring' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                    <span class="text-sm font-medium {{ $distributionType === 'recurring' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Recurring</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Content -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <button
                @click="toggle('content')"
                class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    @php
                        $contentComplete = ($contentType === 'report' && $reportId) || ($contentType === 'custom' && $messageBody && ($channel !== 'email' || $subject));
                    @endphp
                    <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $contentComplete ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                        @if($contentComplete)
                            <x-icon name="check" class="w-4 h-4" />
                        @else
                            <span class="text-sm font-medium">2</span>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Content</h3>
                        <p class="text-sm text-gray-500">
                            @if($contentComplete)
                                {{ $contentType === 'report' ? 'Report link' : 'Custom message' }}
                            @else
                                Report link or custom message
                            @endif
                        </p>
                    </div>
                </div>
                <x-icon
                    name="chevron-down"
                    class="w-5 h-5 text-gray-400 transition-transform"
                    x-bind:class="openSection === 'content' ? 'rotate-180' : ''"
                />
            </button>

            <div x-show="openSection === 'content'" x-collapse>
                <div class="px-6 pb-6 pt-2 border-t border-gray-100 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Content Type</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="p-4 border rounded-lg cursor-pointer transition-colors {{ $contentType === 'report' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="contentType" value="report" class="sr-only" />
                                <div class="flex items-center mb-2">
                                    <x-icon name="chart-bar" class="w-5 h-5 mr-2 {{ $contentType === 'report' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                    <span class="font-medium {{ $contentType === 'report' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Link Report</span>
                                </div>
                                <p class="text-xs text-gray-500">Attach or link to an existing report</p>
                            </label>
                            <label class="p-4 border rounded-lg cursor-pointer transition-colors {{ $contentType === 'custom' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="contentType" value="custom" class="sr-only" />
                                <div class="flex items-center mb-2">
                                    <x-icon name="pencil-square" class="w-5 h-5 mr-2 {{ $contentType === 'custom' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                    <span class="font-medium {{ $contentType === 'custom' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Custom Message</span>
                                </div>
                                <p class="text-xs text-gray-500">Compose your own message</p>
                            </label>
                        </div>
                    </div>

                    @if($contentType === 'report')
                        <div>
                            <label for="reportId" class="block text-sm font-medium text-gray-700 mb-1">Select Report <span class="text-red-500">*</span></label>
                            <select
                                id="reportId"
                                wire:model.live="reportId"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            >
                                <option value="">Select a report...</option>
                                @foreach($reports as $report)
                                    <option value="{{ $report->id }}">{{ $report->title }}</option>
                                @endforeach
                            </select>
                            @error('reportId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Report Mode</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ $reportMode === 'live' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" wire:model="reportMode" value="live" class="sr-only" />
                                    <x-icon name="link" class="w-5 h-5 mr-2 {{ $reportMode === 'live' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                    <div>
                                        <span class="text-sm font-medium {{ $reportMode === 'live' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Live Link</span>
                                        <p class="text-xs text-gray-500">Real-time data</p>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ $reportMode === 'static' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" wire:model="reportMode" value="static" class="sr-only" />
                                    <x-icon name="document" class="w-5 h-5 mr-2 {{ $reportMode === 'static' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                    <div>
                                        <span class="text-sm font-medium {{ $reportMode === 'static' ? 'text-pulse-orange-700' : 'text-gray-700' }}">PDF Snapshot</span>
                                        <p class="text-xs text-gray-500">Static attachment</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    @else
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
                            @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        @endif

                        <div x-data="{ messageBody: @entangle('messageBody') }">
                            <label for="messageBody" class="block text-sm font-medium text-gray-700 mb-1">Message <span class="text-red-500">*</span></label>
                            <textarea
                                id="messageBody"
                                x-model="messageBody"
                                rows="6"
                                placeholder="Type your message here. Click merge fields below to insert them..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 font-mono text-sm"
                            ></textarea>
                            @error('messageBody') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                            <div class="mt-3">
                                <p class="text-xs text-gray-500 mb-2">Click to insert merge fields:</p>
                                <div class="flex flex-wrap gap-1.5">
                                    @php
                                        $mergeFields = [
                                            '@{{first_name}}',
                                            '@{{last_name}}',
                                            '@{{full_name}}',
                                            '@{{email}}',
                                            '@{{organization_name}}'
                                        ];
                                    @endphp
                                    @foreach($mergeFields as $field)
                                        @php $displayField = str_replace('@', '', $field); @endphp
                                        <button
                                            type="button"
                                            x-on:click="messageBody = (messageBody || '') + '{{ $displayField }} '"
                                            class="px-2.5 py-1 text-xs bg-pulse-orange-100 hover:bg-pulse-orange-200 text-pulse-orange-700 rounded-md font-mono border border-pulse-orange-200 transition-colors"
                                        >
                                            {{ $displayField }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Section 3: Recipients -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <button
                @click="toggle('recipients')"
                class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    @php
                        $recipientsComplete = ($recipientType === 'contact_list' && $contactListId) || ($recipientType === 'individual' && count($recipientIds) > 0);
                    @endphp
                    <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $recipientsComplete ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                        @if($recipientsComplete)
                            <x-icon name="check" class="w-4 h-4" />
                        @else
                            <span class="text-sm font-medium">3</span>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-gray-900">Recipients</h3>
                        <p class="text-sm text-gray-500">
                            @if($recipientsComplete)
                                @if($recipientType === 'contact_list' && $contactListId)
                                    {{ $contactLists->firstWhere('id', $contactListId)?->name ?? 'Selected list' }}
                                @else
                                    {{ count($recipientIds) }} contacts selected
                                @endif
                            @else
                                Who receives this distribution
                            @endif
                        </p>
                    </div>
                </div>
                <x-icon
                    name="chevron-down"
                    class="w-5 h-5 text-gray-400 transition-transform"
                    x-bind:class="openSection === 'recipients' ? 'rotate-180' : ''"
                />
            </button>

            <div x-show="openSection === 'recipients'" x-collapse>
                <div class="px-6 pb-6 pt-2 border-t border-gray-100 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Recipients</label>
                        <div class="space-y-3">
                            <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors {{ $recipientType === 'contact_list' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="recipientType" value="contact_list" class="mt-0.5 mr-3 text-pulse-orange-500 focus:ring-pulse-orange-500" />
                                <div>
                                    <span class="font-medium {{ $recipientType === 'contact_list' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Contact List</span>
                                    <p class="text-xs text-gray-500">Send to an existing contact list</p>
                                </div>
                            </label>
                            <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors {{ $recipientType === 'individual' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="recipientType" value="individual" class="mt-0.5 mr-3 text-pulse-orange-500 focus:ring-pulse-orange-500" />
                                <div>
                                    <span class="font-medium {{ $recipientType === 'individual' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Individual Contacts</span>
                                    <p class="text-xs text-gray-500">Select specific contacts</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    @if($recipientType === 'contact_list')
                        <div>
                            <label for="contactListId" class="block text-sm font-medium text-gray-700 mb-1">Select Contact List <span class="text-red-500">*</span></label>
                            <select
                                id="contactListId"
                                wire:model.live="contactListId"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            >
                                <option value="">Select a list...</option>
                                @foreach($contactLists as $list)
                                    <option value="{{ $list->id }}">{{ $list->name }} ({{ $list->members_count ?? 0 }} contacts)</option>
                                @endforeach
                            </select>
                            @if($contactLists->isEmpty())
                                <p class="mt-2 text-sm text-gray-500">
                                    No contact lists available.
                                    <a href="{{ route('contacts.lists') }}" class="text-pulse-orange-600 hover:underline">Create one</a>
                                </p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Section 4: Schedule -->
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <button
                @click="toggle('schedule')"
                class="w-full flex items-center justify-between px-6 py-4 text-left hover:bg-gray-50 transition-colors"
            >
                <div class="flex items-center gap-3">
                    @php
                        $scheduleComplete = $distributionType === 'one_time'
                            ? ($sendImmediately || $scheduledFor)
                            : ($sendTime && ($scheduleType === 'interval' || count($customDays) > 0));
                    @endphp
                    <div class="flex items-center justify-center w-8 h-8 rounded-full {{ $scheduleComplete ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-400' }}">
                        @if($scheduleComplete)
                            <x-icon name="check" class="w-4 h-4" />
                        @else
                            <span class="text-sm font-medium">4</span>
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
                                @if($scheduleType === 'interval')
                                    Every {{ $intervalValue }} {{ Str::plural($intervalType, $intervalValue) }} at {{ $sendTime }}
                                @elseif(count($customDays) > 0)
                                    {{ implode(', ', array_map('ucfirst', array_map(fn($d) => substr($d, 0, 3), $customDays))) }} at {{ $sendTime }}
                                @else
                                    Configure recurring schedule
                                @endif
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
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">When to send</label>
                            <div class="space-y-3">
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ $sendImmediately ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" wire:model.live="sendImmediately" value="1" class="mr-3 text-pulse-orange-500 focus:ring-pulse-orange-500" />
                                    <span class="font-medium {{ $sendImmediately ? 'text-pulse-orange-700' : 'text-gray-700' }}">Send immediately when activated</span>
                                </label>
                                <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ !$sendImmediately ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                    <input type="radio" wire:model.live="sendImmediately" value="0" class="mr-3 text-pulse-orange-500 focus:ring-pulse-orange-500" />
                                    <span class="font-medium {{ !$sendImmediately ? 'text-pulse-orange-700' : 'text-gray-700' }}">Schedule for a specific date/time</span>
                                </label>
                            </div>
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
                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Schedule Type</label>
                                <select
                                    wire:model.live="scheduleType"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                >
                                    <option value="interval">Interval (Daily/Weekly/Monthly)</option>
                                    <option value="custom">Custom Days</option>
                                </select>
                            </div>

                            @if($scheduleType === 'interval')
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                                    <div class="flex gap-2">
                                        <span class="py-2 text-sm text-gray-500">Every</span>
                                        <input
                                            type="number"
                                            wire:model="intervalValue"
                                            min="1"
                                            max="30"
                                            class="w-16 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                        />
                                        <select
                                            wire:model="intervalType"
                                            class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                        >
                                            <option value="daily">day(s)</option>
                                            <option value="weekly">week(s)</option>
                                            <option value="monthly">month(s)</option>
                                        </select>
                                    </div>
                                </div>
                            @else
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Days of Week</label>
                                    <div class="flex flex-wrap gap-2">
                                        @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                                            <label class="flex items-center px-3 py-1.5 border rounded-lg cursor-pointer transition-colors {{ in_array($day, $customDays) ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                                <input
                                                    type="checkbox"
                                                    wire:model.live="customDays"
                                                    value="{{ $day }}"
                                                    class="sr-only"
                                                />
                                                <span class="text-sm font-medium {{ in_array($day, $customDays) ? 'text-pulse-orange-700' : 'text-gray-600' }}">{{ ucfirst(substr($day, 0, 3)) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <div>
                                <label for="sendTime" class="block text-sm font-medium text-gray-700 mb-1">Send Time</label>
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
                                    <option value="America/New_York">Eastern Time</option>
                                    <option value="America/Chicago">Central Time</option>
                                    <option value="America/Denver">Mountain Time</option>
                                    <option value="America/Los_Angeles">Pacific Time</option>
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
                    $isValid = $title && (($contentType === 'report' && $reportId) || ($contentType === 'custom' && $messageBody && ($channel !== 'email' || $subject)))
                        && (($recipientType === 'contact_list' && $contactListId) || ($recipientType === 'individual' && count($recipientIds) > 0));
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
