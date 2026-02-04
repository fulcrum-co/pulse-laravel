<div class="max-w-xl mx-auto px-4 py-8">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-white" style="background-color: {{ $settings->primary_color }}">
                <x-icon name="microphone" class="w-5 h-5" />
            </div>
            <div>
                <h1 class="text-lg font-semibold text-gray-900">Submit Narrative</h1>
                <p class="text-sm text-gray-500">Share a brief update about this {{ $settings->contact_label_singular }}.</p>
            </div>
        </div>

        @if($submitted)
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-700">
                Thanks! Your update has been received.
            </div>
        @else
            <form wire:submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Audio Recording</label>
                    <input type="file" wire:model="audioFile" accept="audio/*" class="block w-full text-sm text-gray-700" />
                    @error('audioFile') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="w-full py-2 rounded-lg text-white font-medium" style="background-color: {{ $settings->primary_color }}">
                    Submit
                </button>
            </form>
        @endif
    </div>
</div>
