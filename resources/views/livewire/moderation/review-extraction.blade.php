<div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6">
    <div class="bg-white rounded-lg shadow p-4 border-t-4" style="border-color: {{ $settings->primary_color }}">
        <h3 class="font-bold text-lg mb-4">Original Narrative</h3>

        @if($extraction->audio_path)
            <div class="mb-4 bg-gray-50 p-3 rounded border">
                <audio controls src="{{ Storage::disk(config('filesystems.default'))->url($extraction->audio_path) }}" class="w-full"></audio>
            </div>
        @endif

        <div class="prose max-w-none text-gray-800 bg-gray-100 p-4 rounded italic">
            "{{ $extraction->raw_transcript ?? 'Transcript pending...' }}"
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-4 border-t-4" style="border-color: {{ $settings->primary_color }}">
        <h3 class="font-bold text-lg mb-4">Proposed Update: {{ $settings->contact_label_singular }}</h3>

        <form wire:submit.prevent="apply">
            @foreach($editableData as $key => $value)
                <div class="mb-4">
                    <label class="block text-sm font-semibold text-gray-700 capitalize">
                        {{ str_replace('_', ' ', $key) }}
                    </label>
                    <input
                        type="text"
                        wire:model="editableData.{{ $key }}"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500
                            {{ $extraction->confidence_score < 70 ? 'border-yellow-400' : '' }}"
                    />
                </div>
            @endforeach

            <div class="mt-6 flex justify-between items-center">
                <button type="button" wire:click="reject" class="text-red-600 font-medium">Reject</button>
                <button type="submit" class="px-6 py-2 rounded text-white font-bold" style="background-color: {{ $settings->primary_color }}">
                    Apply to {{ $settings->contact_label_singular }} Record
                </button>
            </div>
        </form>

        <div class="mt-8">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Suggested Resources</h4>
            <div class="space-y-2">
                @forelse($suggestions as $resource)
                    <div class="flex items-center justify-between border rounded-lg p-3">
                        <div>
                            <div class="text-sm font-medium text-gray-900">{{ $resource->title }}</div>
                            <div class="text-xs text-gray-500">{{ $resource->category ?? 'Resource' }}</div>
                        </div>
                        <button type="button" wire:click="addResourceToPlan({{ $resource->id }})" class="px-3 py-1.5 text-xs font-semibold rounded text-white" style="background-color: {{ $settings->primary_color }}">
                            Add to {{ $settings->plan_label }}
                        </button>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">No suggestions yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
