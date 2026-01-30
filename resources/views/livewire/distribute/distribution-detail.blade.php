<div class="space-y-6">
    <!-- Header Actions -->
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('distribute.index') }}" class="text-gray-500 hover:text-gray-700">
                <x-icon name="arrow-left" class="w-5 h-5" />
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-semibold text-gray-900">{{ $distribution->title }}</h1>
                    @php
                        $statusColor = match($distribution->status) {
                            'active' => 'green',
                            'scheduled' => 'blue',
                            'paused' => 'yellow',
                            'draft' => 'gray',
                            'completed' => 'purple',
                            default => 'gray',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                        {{ ucfirst($distribution->status) }}
                    </span>
                </div>
                @if($distribution->description)
                    <p class="text-sm text-gray-500 mt-1">{{ $distribution->description }}</p>
                @endif
            </div>
        </div>

        <div class="flex items-center gap-2">
            @if($distribution->isDraft())
                <button
                    wire:click="activate"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700"
                >
                    <x-icon name="play" class="w-4 h-4 mr-1" />
                    Activate
                </button>
            @elseif($distribution->isActive())
                <button
                    wire:click="openSendModal"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                >
                    <x-icon name="paper-airplane" class="w-4 h-4 mr-1 transform -rotate-45" />
                    Send Now
                </button>
                <button
                    wire:click="pause"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                >
                    <x-icon name="pause" class="w-4 h-4 mr-1" />
                    Pause
                </button>
            @elseif($distribution->isPaused())
                <button
                    wire:click="resume"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700"
                >
                    <x-icon name="play" class="w-4 h-4 mr-1" />
                    Resume
                </button>
            @endif

            <a
                href="{{ route('distribute.edit', $distribution) }}"
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
            >
                <x-icon name="pencil" class="w-4 h-4 mr-1" />
                Edit
            </a>

            <button
                wire:click="confirmDelete"
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-600 bg-white border border-gray-300 rounded-lg hover:bg-red-50"
            >
                <x-icon name="trash" class="w-4 h-4" />
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-4 gap-4">
        <x-card class="p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Total Deliveries</p>
            <p class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($stats['total_deliveries']) }}</p>
        </x-card>
        <x-card class="p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Messages Sent</p>
            <p class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($stats['total_sent']) }}</p>
        </x-card>
        <x-card class="p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Opens</p>
            <p class="text-2xl font-semibold text-gray-900 mt-1">{{ number_format($stats['total_opened']) }}</p>
        </x-card>
        <x-card class="p-4">
            <p class="text-xs text-gray-500 uppercase tracking-wide">Avg. Open Rate</p>
            <p class="text-2xl font-semibold text-gray-900 mt-1">{{ $stats['avg_open_rate'] }}%</p>
        </x-card>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <!-- Details Panel -->
        <div class="col-span-1">
            <x-card>
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-medium text-gray-900">Distribution Details</h3>
                </div>
                <div class="p-4 space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Channel</p>
                        <p class="mt-1 flex items-center text-sm text-gray-900">
                            <x-icon name="{{ $distribution->channel === 'email' ? 'envelope' : 'device-phone-mobile' }}" class="w-4 h-4 mr-1 text-gray-400" />
                            {{ ucfirst($distribution->channel) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Type</p>
                        <p class="mt-1 text-sm text-gray-900">{{ $distribution->isRecurring() ? 'Recurring' : 'One-time' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Content</p>
                        <p class="mt-1 text-sm text-gray-900">
                            @if($distribution->usesReport())
                                <span class="flex items-center">
                                    <x-icon name="chart-bar" class="w-4 h-4 mr-1 text-gray-400" />
                                    {{ $distribution->report?->title ?? 'Report' }}
                                    <span class="ml-1 text-xs text-gray-500">({{ $distribution->report_mode === 'live' ? 'Live' : 'PDF' }})</span>
                                </span>
                            @else
                                Custom Message
                            @endif
                        </p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Recipients</p>
                        <p class="mt-1 text-sm text-gray-900">
                            @if($distribution->contactList)
                                <a href="{{ route('contacts.lists') }}" class="text-pulse-orange-600 hover:underline">
                                    {{ $distribution->contactList->name }}
                                </a>
                            @elseif($distribution->recipient_ids)
                                {{ count($distribution->recipient_ids) }} individual contacts
                            @else
                                Not configured
                            @endif
                        </p>
                    </div>

                    @if($distribution->isRecurring() && $distribution->schedule)
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wide">Schedule</p>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($distribution->schedule->schedule_type === 'interval')
                                    Every {{ $distribution->schedule->interval_value }}
                                    {{ Str::plural($distribution->schedule->interval_type, $distribution->schedule->interval_value) }}
                                @else
                                    {{ implode(', ', array_map('ucfirst', $distribution->schedule->custom_days ?? [])) }}
                                @endif
                                at {{ \Carbon\Carbon::parse($distribution->schedule->send_time)->format('g:i A') }}
                            </p>
                        </div>

                        @if($distribution->schedule->next_scheduled_at)
                            <div>
                                <p class="text-xs text-gray-500 uppercase tracking-wide">Next Send</p>
                                <p class="mt-1 text-sm text-gray-900">
                                    {{ $distribution->schedule->next_scheduled_at->format('M j, Y \a\t g:i A') }}
                                </p>
                            </div>
                        @endif
                    @endif

                    <div class="pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Created</p>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $distribution->created_at->format('M j, Y') }}
                            by {{ $distribution->creator?->first_name }} {{ $distribution->creator?->last_name }}
                        </p>
                    </div>
                </div>
            </x-card>

            @if($distribution->usesCustomMessage())
                <x-card class="mt-4">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-medium text-gray-900">Message Preview</h3>
                    </div>
                    <div class="p-4">
                        @if($distribution->channel === 'email' && $distribution->subject)
                            <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Subject</p>
                            <p class="text-sm font-medium text-gray-900 mb-3">{{ $distribution->subject }}</p>
                        @endif
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Body</p>
                        <div class="text-sm text-gray-700 whitespace-pre-wrap bg-gray-50 rounded p-3">{{ $distribution->message_body }}</div>
                    </div>
                </x-card>
            @endif
        </div>

        <!-- Delivery History -->
        <div class="col-span-2">
            <x-card>
                <div class="p-4 border-b border-gray-200">
                    <h3 class="font-medium text-gray-900">Delivery History</h3>
                </div>

                @if($deliveries->isEmpty())
                    <div class="p-8 text-center">
                        <x-icon name="paper-airplane" class="w-12 h-12 text-gray-300 mx-auto mb-3 transform -rotate-45" />
                        <p class="text-sm text-gray-500">No deliveries yet</p>
                        <p class="text-xs text-gray-400 mt-1">Deliveries will appear here once the distribution is sent.</p>
                    </div>
                @else
                    <div class="divide-y divide-gray-200">
                        @foreach($deliveries as $delivery)
                            <div class="p-4">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $delivery->created_at->format('M j, Y \a\t g:i A') }}
                                        </p>
                                        @php
                                            $deliveryStatusColor = match($delivery->status) {
                                                'completed' => 'green',
                                                'sending' => 'blue',
                                                'pending' => 'yellow',
                                                'failed' => 'red',
                                                'partial' => 'orange',
                                                default => 'gray',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $deliveryStatusColor }}-100 text-{{ $deliveryStatusColor }}-800 mt-1">
                                            {{ ucfirst($delivery->status) }}
                                        </span>
                                    </div>
                                    <div class="text-right text-sm">
                                        <p class="text-gray-900">
                                            {{ number_format($delivery->sent_count) }}/{{ number_format($delivery->total_recipients) }} sent
                                        </p>
                                        @if($delivery->sent_count > 0)
                                            <p class="text-gray-500 text-xs">
                                                {{ $delivery->getOpenRate() }}% opened
                                                @if($delivery->clicked_count > 0)
                                                    &bull; {{ $delivery->getClickRate() }}% clicked
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if($deliveries->hasPages())
                        <div class="p-4 border-t border-gray-200">
                            {{ $deliveries->links() }}
                        </div>
                    @endif
                @endif
            </x-card>
        </div>
    </div>

    <!-- Send Now Modal -->
    @if($showSendModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeSendModal"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-pulse-orange-100 sm:mx-0">
                        <x-icon name="paper-airplane" class="h-5 w-5 text-pulse-orange-600 transform -rotate-45" />
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-base font-medium text-gray-900">Send Distribution</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Are you sure you want to send this distribution now? It will be sent to all recipients.
                        </p>
                    </div>
                </div>
                <div class="mt-4 sm:flex sm:flex-row-reverse gap-2">
                    <button wire:click="sendNow" class="w-full sm:w-auto px-3 py-1.5 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                        Send Now
                    </button>
                    <button wire:click="closeSendModal" class="mt-2 sm:mt-0 w-full sm:w-auto px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelDelete"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100 sm:mx-0">
                        <x-icon name="exclamation-triangle" class="h-5 w-5 text-red-600" />
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-base font-medium text-gray-900">Delete Distribution</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            Are you sure? This will also delete all delivery history. This cannot be undone.
                        </p>
                    </div>
                </div>
                <div class="mt-4 sm:flex sm:flex-row-reverse gap-2">
                    <button wire:click="delete" class="w-full sm:w-auto px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Delete
                    </button>
                    <button wire:click="cancelDelete" class="mt-2 sm:mt-0 w-full sm:w-auto px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
