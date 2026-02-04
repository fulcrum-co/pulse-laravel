<div class="bg-white rounded-lg border border-gray-200 p-4">
    <h3 class="text-sm font-semibold text-gray-900 mb-3">Send Collection Link</h3>

    @if(empty($events))
        <p class="text-sm text-gray-500">No collection events available.</p>
    @else
        <div class="space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Collection Event</label>
                <select wire:model="eventId" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                    @foreach($events as $event)
                        <option value="{{ $event['id'] }}">{{ $event['title'] }}</option>
                    @endforeach
                </select>
            </div>

            <button
                type="button"
                wire:click="send"
                class="w-full inline-flex items-center justify-center px-3 py-2 rounded-lg text-sm font-semibold text-white bg-pulse-orange-500 hover:bg-pulse-orange-600 disabled:opacity-50"
                @if(!($contact->user?->phone)) disabled @endif
            >
                Send SMS Link
            </button>
            @if(!($contact->user?->phone))
                <p class="text-xs text-gray-500">Add a phone number to send SMS.</p>
            @endif
        </div>
    @endif
</div>
