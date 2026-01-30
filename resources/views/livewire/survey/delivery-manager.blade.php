<div class="space-y-6">
    {{-- Delivery Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        @foreach([
            ['label' => 'Total Sent', 'value' => $this->deliveryStats['total'], 'color' => 'gray'],
            ['label' => 'Pending', 'value' => $this->deliveryStats['pending'], 'color' => 'yellow'],
            ['label' => 'Delivered', 'value' => $this->deliveryStats['delivered'], 'color' => 'blue'],
            ['label' => 'Completed', 'value' => $this->deliveryStats['completed'], 'color' => 'green'],
            ['label' => 'Failed', 'value' => $this->deliveryStats['failed'], 'color' => 'red'],
        ] as $stat)
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <div class="text-2xl font-bold text-{{ $stat['color'] }}-600">{{ $stat['value'] }}</div>
                <div class="text-sm text-gray-500">{{ $stat['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Delivery Form --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Channel Selection --}}
            <x-card>
                <h3 class="font-semibold text-gray-900 mb-4">Delivery Channel</h3>

                <div class="grid md:grid-cols-2 gap-3">
                    @foreach($this->channelOptions as $key => $option)
                        @if(in_array($key, $survey->delivery_channels ?? ['web']))
                            <button
                                wire:click="$set('channel', '{{ $key }}')"
                                class="flex items-start gap-3 p-4 rounded-lg border-2 text-left transition-all {{ $channel === $key ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                            >
                                <div class="flex-shrink-0 w-10 h-10 rounded-lg {{ $channel === $key ? 'bg-pulse-orange-100' : 'bg-gray-100' }} flex items-center justify-center">
                                    <x-icon name="{{ $option['icon'] }}" class="w-5 h-5 {{ $channel === $key ? 'text-pulse-orange-600' : 'text-gray-500' }}" />
                                </div>
                                <div>
                                    <div class="font-medium {{ $channel === $key ? 'text-pulse-orange-700' : 'text-gray-900' }}">{{ $option['label'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $option['description'] }}</div>
                                </div>
                            </button>
                        @endif
                    @endforeach
                </div>
            </x-card>

            {{-- Recipient Selection --}}
            <x-card>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900">Select Recipients</h3>
                    <div class="flex gap-2">
                        <button wire:click="selectAll" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700">
                            Select All
                        </button>
                        <span class="text-gray-300">|</span>
                        <button wire:click="clearSelection" class="text-sm text-gray-500 hover:text-gray-700">
                            Clear
                        </button>
                    </div>
                </div>

                {{-- Recipient Type Tabs --}}
                <div class="flex gap-2 mb-4">
                    <button
                        wire:click="$set('recipientType', 'student')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $recipientType === 'student' ? 'bg-pulse-orange-100 text-pulse-orange-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                    >
                        Students
                    </button>
                    <button
                        wire:click="$set('recipientType', 'user')"
                        class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $recipientType === 'user' ? 'bg-pulse-orange-100 text-pulse-orange-700' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}"
                    >
                        Staff
                    </button>
                </div>

                {{-- Search --}}
                <div class="mb-4">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search by name or email..."
                        class="w-full rounded-lg border-gray-300 focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                    />
                </div>

                {{-- Manual Phone Entry (for SMS/Voice) --}}
                @if(in_array($channel, ['sms', 'voice_call', 'whatsapp']))
                    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Add Phone Number Manually</label>
                        <div class="flex gap-2">
                            <input
                                type="tel"
                                wire:model="phoneNumber"
                                placeholder="+1 (555) 123-4567"
                                class="flex-1 rounded-lg border-gray-300 focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            />
                            <button
                                wire:click="addManualRecipient"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300"
                            >
                                Add
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">For demo: Add your phone number here to receive a live survey</p>
                    </div>
                @endif

                {{-- Recipient List --}}
                <div class="max-h-64 overflow-y-auto border border-gray-200 rounded-lg divide-y divide-gray-200">
                    @forelse($recipients as $recipient)
                        @php $key = "{$recipient['type']}_{$recipient['id']}"; @endphp
                        <label class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:click="selectRecipient({{ $recipient['id'] }}, '{{ $recipient['type'] }}', '{{ $recipient['phone'] ?? '' }}')"
                                @checked(isset($selectedRecipients[$key]))
                                class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                            />
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-gray-900 truncate">{{ $recipient['name'] }}</div>
                                <div class="text-sm text-gray-500 truncate">
                                    {{ $recipient['email'] }}
                                    @if($recipient['phone'] ?? null)
                                        <span class="mx-1">·</span>
                                        {{ $recipient['phone'] }}
                                    @endif
                                </div>
                            </div>
                            @if($recipient['grade'] ?? null)
                                <x-badge color="gray">Grade {{ $recipient['grade'] }}</x-badge>
                            @elseif($recipient['role'] ?? null)
                                <x-badge color="blue">{{ ucfirst($recipient['role']) }}</x-badge>
                            @endif
                        </label>
                    @empty
                        <div class="p-8 text-center text-gray-500">
                            No recipients found
                        </div>
                    @endforelse
                </div>

                {{-- Selected Count --}}
                @if(count($selectedRecipients) > 0)
                    <div class="mt-4 flex items-center justify-between p-3 bg-pulse-orange-50 rounded-lg">
                        <span class="text-sm text-pulse-orange-700">
                            <strong>{{ count($selectedRecipients) }}</strong> recipient(s) selected
                        </span>
                        <button wire:click="clearSelection" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700">
                            Clear all
                        </button>
                    </div>
                @endif
            </x-card>

            {{-- Schedule Option --}}
            <x-card>
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900">Schedule Delivery</h3>
                        <p class="text-sm text-gray-500">Send at a specific date and time</p>
                    </div>
                    <button
                        wire:click="$toggle('scheduleDelivery')"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 {{ $scheduleDelivery ? 'bg-pulse-orange-500' : 'bg-gray-200' }}"
                    >
                        <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 {{ $scheduleDelivery ? 'translate-x-5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                @if($scheduleDelivery)
                    <div class="mt-4">
                        <input
                            type="datetime-local"
                            wire:model="scheduledFor"
                            class="w-full rounded-lg border-gray-300 focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                        />
                    </div>
                @endif
            </x-card>

            {{-- Send Button --}}
            <button
                wire:click="deliver"
                @disabled(count($selectedRecipients) === 0 || $isDelivering)
                class="w-full py-3 px-6 bg-pulse-orange-500 text-white font-medium rounded-lg hover:bg-pulse-orange-600 disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2"
            >
                @if($isDelivering)
                    <div class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                    Sending...
                @else
                    <x-icon name="paper-airplane" class="w-5 h-5" />
                    Send Survey to {{ count($selectedRecipients) }} Recipient(s)
                @endif
            </button>
        </div>

        {{-- Recent Deliveries --}}
        <div class="space-y-6">
            <x-card>
                <h3 class="font-semibold text-gray-900 mb-4">Recent Deliveries</h3>

                @if($this->recentDeliveries->count() > 0)
                    <div class="space-y-3">
                        @foreach($this->recentDeliveries as $delivery)
                            <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                <div class="flex-shrink-0">
                                    <x-icon name="{{ match($delivery->channel) {
                                        'web' => 'globe-alt',
                                        'sms' => 'chat-bubble-left',
                                        'voice_call' => 'phone',
                                        'whatsapp' => 'chat-bubble-oval-left',
                                        default => 'device-phone-mobile',
                                    } }}" class="w-5 h-5 text-gray-400" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <x-badge :color="match($delivery->status) {
                                            'completed' => 'green',
                                            'delivered' => 'blue',
                                            'pending' => 'yellow',
                                            'failed' => 'red',
                                            default => 'gray',
                                        }">{{ ucfirst($delivery->status) }}</x-badge>
                                    </div>
                                    <div class="text-sm text-gray-500 mt-1">
                                        {{ $delivery->phone_number ?? 'Web' }}
                                    </div>
                                    <div class="text-xs text-gray-400">
                                        {{ $delivery->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-icon name="paper-airplane" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                        <p class="text-gray-500">No deliveries yet</p>
                    </div>
                @endif
            </x-card>

            {{-- Quick Tips --}}
            <x-card class="bg-blue-50 border-blue-200">
                <h4 class="font-medium text-blue-900 mb-2">Tips for Demo</h4>
                <ul class="text-sm text-blue-800 space-y-2">
                    <li class="flex items-start gap-2">
                        <x-icon name="light-bulb" class="w-4 h-4 mt-0.5 flex-shrink-0" />
                        <span>Add your phone number to receive a live survey via SMS</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="light-bulb" class="w-4 h-4 mt-0.5 flex-shrink-0" />
                        <span>Voice calls use TTS to read questions aloud</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <x-icon name="light-bulb" class="w-4 h-4 mt-0.5 flex-shrink-0" />
                        <span>Responses trigger workflows automatically</span>
                    </li>
                </ul>
            </x-card>
        </div>
    </div>

    {{-- Delivery Result Modal --}}
    @if($deliveryResult)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('deliveryResult', null)"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="text-center mb-6">
                    @if($deliveryResult['failed'] === 0)
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <x-icon name="check-circle" class="w-10 h-10 text-green-600" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Delivery Successful!</h3>
                    @else
                        <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <x-icon name="exclamation-triangle" class="w-10 h-10 text-yellow-600" />
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">Delivery Complete</h3>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="text-center p-3 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ $deliveryResult['success'] }}</div>
                        <div class="text-sm text-green-700">Successful</div>
                    </div>
                    <div class="text-center p-3 bg-red-50 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">{{ $deliveryResult['failed'] }}</div>
                        <div class="text-sm text-red-700">Failed</div>
                    </div>
                </div>

                <button
                    wire:click="$set('deliveryResult', null)"
                    class="w-full py-2 px-4 bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600"
                >
                    Done
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
