<div class="max-w-4xl mx-auto">
    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            @foreach(['Basics', 'Content', 'Recipients', 'Schedule', 'Review'] as $index => $step)
                @php $stepNum = $index + 1; @endphp
                <div class="flex items-center {{ $stepNum < $totalSteps ? 'flex-1' : '' }}">
                    <button
                        wire:click="goToStep({{ $stepNum }})"
                        class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-medium transition-colors
                            {{ $currentStep === $stepNum ? 'bg-pulse-orange-500 text-white' : ($currentStep > $stepNum ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-600') }}
                            {{ $currentStep >= $stepNum ? 'cursor-pointer hover:opacity-80' : 'cursor-not-allowed' }}"
                        @if($currentStep < $stepNum) disabled @endif
                    >
                        @if($currentStep > $stepNum)
                            <x-icon name="check" class="w-4 h-4" />
                        @else
                            {{ $stepNum }}
                        @endif
                    </button>
                    <span class="ml-2 text-sm font-medium {{ $currentStep >= $stepNum ? 'text-gray-900' : 'text-gray-500' }}">{{ $step }}</span>
                    @if($stepNum < $totalSteps)
                        <div class="flex-1 h-0.5 mx-4 {{ $currentStep > $stepNum ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    <x-card>
        <!-- Step 1: Basics -->
        @if($currentStep === 1)
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-900">Basic Information</h2>

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                    <input
                        type="text"
                        id="title"
                        wire:model="title"
                        placeholder="e.g., Weekly Progress Report"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    />
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description (optional)</label>
                    <textarea
                        id="description"
                        wire:model="description"
                        rows="2"
                        placeholder="Brief description of this distribution..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    ></textarea>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Channel</label>
                        <div class="flex gap-3">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ $channel === 'email' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="channel" value="email" class="sr-only" />
                                <x-icon name="envelope" class="w-5 h-5 mr-2 {{ $channel === 'email' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                <span class="text-sm font-medium {{ $channel === 'email' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Email</span>
                            </label>
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ $channel === 'sms' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="channel" value="sms" class="sr-only" />
                                <x-icon name="device-phone-mobile" class="w-5 h-5 mr-2 {{ $channel === 'sms' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                <span class="text-sm font-medium {{ $channel === 'sms' ? 'text-pulse-orange-700' : 'text-gray-700' }}">SMS</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Frequency</label>
                        <div class="flex gap-3">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ $distributionType === 'one_time' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="distributionType" value="one_time" class="sr-only" />
                                <x-icon name="paper-airplane" class="w-5 h-5 mr-2 {{ $distributionType === 'one_time' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                <span class="text-sm font-medium {{ $distributionType === 'one_time' ? 'text-pulse-orange-700' : 'text-gray-700' }}">One-time</span>
                            </label>
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ $distributionType === 'recurring' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="distributionType" value="recurring" class="sr-only" />
                                <x-icon name="arrow-path" class="w-5 h-5 mr-2 {{ $distributionType === 'recurring' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                <span class="text-sm font-medium {{ $distributionType === 'recurring' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Recurring</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

        <!-- Step 2: Content -->
        @elseif($currentStep === 2)
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-900">Content</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Content Type</label>
                    <div class="flex gap-3">
                        <label class="flex-1 p-4 border rounded-lg cursor-pointer transition-colors {{ $contentType === 'report' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" wire:model.live="contentType" value="report" class="sr-only" />
                            <div class="flex items-center mb-2">
                                <x-icon name="chart-bar" class="w-5 h-5 mr-2 {{ $contentType === 'report' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                <span class="font-medium {{ $contentType === 'report' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Link Report</span>
                            </div>
                            <p class="text-xs text-gray-500">Attach or link to an existing report</p>
                        </label>
                        <label class="flex-1 p-4 border rounded-lg cursor-pointer transition-colors {{ $contentType === 'custom' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
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
                        <label for="reportId" class="block text-sm font-medium text-gray-700 mb-1">Select Report</label>
                        <select
                            id="reportId"
                            wire:model="reportId"
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
                        <div class="flex gap-3">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ $reportMode === 'live' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model="reportMode" value="live" class="sr-only" />
                                <x-icon name="link" class="w-5 h-5 mr-2 {{ $reportMode === 'live' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                <div>
                                    <span class="text-sm font-medium {{ $reportMode === 'live' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Live Link</span>
                                    <p class="text-xs text-gray-500">Recipients see real-time data</p>
                                </div>
                            </label>
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ $reportMode === 'static' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model="reportMode" value="static" class="sr-only" />
                                <x-icon name="document" class="w-5 h-5 mr-2 {{ $reportMode === 'static' ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                <div>
                                    <span class="text-sm font-medium {{ $reportMode === 'static' ? 'text-pulse-orange-700' : 'text-gray-700' }}">PDF Snapshot</span>
                                    <p class="text-xs text-gray-500">Static PDF attachment</p>
                                </div>
                            </label>
                        </div>
                    </div>
                @else
                    @if($channel === 'email')
                    <div>
                        <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject Line</label>
                        <input
                            type="text"
                            id="subject"
                            wire:model="subject"
                            placeholder="e.g., Your Weekly Progress Report"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        />
                        @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    @endif

                    <div>
                        <label for="messageBody" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                        <textarea
                            id="messageBody"
                            wire:model="messageBody"
                            rows="6"
                            placeholder="Type your message here. Use merge fields like @{{first_name}} for personalization..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 font-mono text-sm"
                        ></textarea>
                        @error('messageBody') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror

                        <div class="mt-2">
                            <p class="text-xs text-gray-500 mb-2">Available merge fields:</p>
                            <div class="flex flex-wrap gap-1">
                                @php
                                    $mergeFields = [
                                        '{{first_name}}',
                                        '{{last_name}}',
                                        '{{full_name}}',
                                        '{{email}}',
                                        '{{organization_name}}'
                                    ];
                                @endphp
                                @foreach($mergeFields as $field)
                                    <button
                                        type="button"
                                        onclick="navigator.clipboard.writeText('{{ $field }}')"
                                        class="px-2 py-0.5 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded font-mono"
                                    >
                                        {{ $field }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        <!-- Step 3: Recipients -->
        @elseif($currentStep === 3)
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-900">Recipients</h2>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Select Recipients</label>
                    <div class="space-y-3">
                        <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors {{ $recipientType === 'contact_list' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" wire:model.live="recipientType" value="contact_list" class="mt-0.5 mr-3" />
                            <div>
                                <span class="font-medium {{ $recipientType === 'contact_list' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Contact List</span>
                                <p class="text-xs text-gray-500">Send to an existing contact list</p>
                            </div>
                        </label>
                        <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors {{ $recipientType === 'individual' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                            <input type="radio" wire:model.live="recipientType" value="individual" class="mt-0.5 mr-3" />
                            <div>
                                <span class="font-medium {{ $recipientType === 'individual' ? 'text-pulse-orange-700' : 'text-gray-700' }}">Individual Contacts</span>
                                <p class="text-xs text-gray-500">Select specific contacts</p>
                            </div>
                        </label>
                    </div>
                </div>

                @if($recipientType === 'contact_list')
                    <div>
                        <label for="contactListId" class="block text-sm font-medium text-gray-700 mb-1">Select Contact List</label>
                        <select
                            id="contactListId"
                            wire:model="contactListId"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                            <option value="">Select a list...</option>
                            @foreach($contactLists as $list)
                                <option value="{{ $list->id }}">{{ $list->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-gray-500">
                            <a href="{{ route('contacts.lists') }}" class="text-pulse-orange-600 hover:underline">Manage contact lists</a>
                        </p>
                    </div>
                @endif
            </div>

        <!-- Step 4: Schedule -->
        @elseif($currentStep === 4)
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-900">Schedule</h2>

                @if($distributionType === 'one_time')
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">When to send</label>
                        <div class="space-y-3">
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ $sendImmediately ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="sendImmediately" value="1" class="mr-3" />
                                <span class="font-medium {{ $sendImmediately ? 'text-pulse-orange-700' : 'text-gray-700' }}">Send immediately when activated</span>
                            </label>
                            <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-colors {{ !$sendImmediately ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}">
                                <input type="radio" wire:model.live="sendImmediately" value="0" class="mr-3" />
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
                                    wire:model="scheduledFor"
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
                                        <label class="flex items-center">
                                            <input
                                                type="checkbox"
                                                wire:model="customDays"
                                                value="{{ $day }}"
                                                class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                                            />
                                            <span class="ml-1 text-sm text-gray-700">{{ ucfirst(substr($day, 0, 3)) }}</span>
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
            </div>

        <!-- Step 5: Review -->
        @else
            <div class="space-y-6">
                <h2 class="text-lg font-semibold text-gray-900">Review & Confirm</h2>

                <div class="bg-gray-50 rounded-lg p-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Title</p>
                            <p class="font-medium text-gray-900">{{ $title }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Channel</p>
                            <p class="font-medium text-gray-900">{{ ucfirst($channel) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Type</p>
                            <p class="font-medium text-gray-900">{{ $distributionType === 'recurring' ? 'Recurring' : 'One-time' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Content</p>
                            <p class="font-medium text-gray-900">{{ $contentType === 'report' ? 'Report Link' : 'Custom Message' }}</p>
                        </div>
                    </div>

                    @if($contentType === 'custom' && $messageBody)
                        <div class="pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-2">Message Preview</p>
                            <div class="bg-white border border-gray-200 rounded p-3 text-sm text-gray-700">
                                {!! nl2br(e(Str::limit($messageBody, 200))) !!}
                            </div>
                        </div>
                    @endif

                    @if($recipientType === 'contact_list' && $contactListId)
                        <div class="pt-4 border-t border-gray-200">
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Recipients</p>
                            <p class="font-medium text-gray-900">
                                {{ $contactLists->firstWhere('id', $contactListId)?->name ?? 'Selected List' }}
                            </p>
                        </div>
                    @endif
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <x-icon name="information-circle" class="w-5 h-5 text-yellow-600 mr-2 flex-shrink-0 mt-0.5" />
                        <div>
                            <p class="text-sm font-medium text-yellow-800">Ready to save</p>
                            <p class="text-sm text-yellow-700 mt-1">
                                Your distribution will be saved as a draft. You can review and activate it from the distribution detail page.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Navigation Buttons -->
        <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
            <div>
                @if($currentStep > 1)
                    <button
                        wire:click="previousStep"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        <x-icon name="arrow-left" class="w-4 h-4 mr-2" />
                        Back
                    </button>
                @endif
            </div>

            <div class="flex gap-3">
                <a href="{{ route('distribute.index') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancel
                </a>

                @if($currentStep < $totalSteps)
                    <button
                        wire:click="nextStep"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                    >
                        Continue
                        <x-icon name="arrow-right" class="w-4 h-4 ml-2" />
                    </button>
                @else
                    <button
                        wire:click="save"
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                    >
                        <x-icon name="check" class="w-4 h-4 mr-2" />
                        Save Distribution
                    </button>
                @endif
            </div>
        </div>
    </x-card>
</div>
