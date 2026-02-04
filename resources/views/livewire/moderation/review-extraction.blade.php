@php
    $confidenceScore = $extraction->confidence_score ?? 0;
    $confidenceLevel = match(true) {
        $confidenceScore >= 80 => 'high',
        $confidenceScore >= 60 => 'medium',
        default => 'low',
    };
    $confidenceClasses = match($confidenceLevel) {
        'high' => 'bg-green-100 text-green-800 border-green-200',
        'medium' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'low' => 'bg-red-100 text-red-800 border-red-200',
    };
    $confidenceIcon = match($confidenceLevel) {
        'high' => 'check-circle',
        'medium' => 'exclamation-triangle',
        'low' => 'exclamation-circle',
    };
@endphp

<div class="p-6 space-y-6">
    {{-- Confidence Score Header --}}
    <div class="flex items-center justify-between bg-white rounded-lg shadow p-4">
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 px-4 py-2 rounded-full border {{ $confidenceClasses }}">
                <x-icon :name="$confidenceIcon" class="w-5 h-5" />
                <span class="font-semibold">{{ $confidenceScore }}% Confidence</span>
            </div>
            <span class="text-sm text-gray-500">
                @if($confidenceLevel === 'high')
                    High confidence - extraction looks accurate
                @elseif($confidenceLevel === 'medium')
                    Medium confidence - please verify key fields
                @else
                    Low confidence - careful review recommended
                @endif
            </span>
        </div>
        <div class="text-sm text-gray-400">
            Extracted {{ $extraction->created_at->diffForHumans() }}
        </div>
    </div>

    {{-- Confidence Legend --}}
    @if($confidenceLevel !== 'high')
    <div class="flex items-start gap-3 p-4 rounded-lg {{ $confidenceLevel === 'low' ? 'bg-red-50 border border-red-200' : 'bg-yellow-50 border border-yellow-200' }}">
        <x-icon name="information-circle" class="w-5 h-5 mt-0.5 {{ $confidenceLevel === 'low' ? 'text-red-500' : 'text-yellow-500' }}" />
        <div class="text-sm {{ $confidenceLevel === 'low' ? 'text-red-700' : 'text-yellow-700' }}">
            <p class="font-medium mb-1">
                @if($confidenceLevel === 'low')
                    This extraction needs careful review
                @else
                    Some fields may need verification
                @endif
            </p>
            <p class="text-{{ $confidenceLevel === 'low' ? 'red' : 'yellow' }}-600">
                Fields highlighted in yellow indicate lower confidence. Please verify these values against the original recording before applying.
            </p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Left Column: Original Narrative --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center gap-2" style="background-color: {{ $settings->primary_color }}10">
                <x-icon name="microphone" class="w-5 h-5" style="color: {{ $settings->primary_color }}" />
                <h3 class="font-semibold text-gray-900">Original Narrative</h3>
            </div>

            <div class="p-4 space-y-4">
                @if($extraction->audio_path)
                    <div class="bg-gray-50 p-3 rounded-lg border border-gray-200">
                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Audio Recording</label>
                        <audio controls src="{{ Storage::disk(config('filesystems.default'))->url($extraction->audio_path) }}" class="w-full"></audio>
                    </div>
                @endif

                <div>
                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wide mb-2">Transcript</label>
                    <div class="prose max-w-none text-gray-700 bg-gray-50 p-4 rounded-lg border border-gray-200 italic leading-relaxed">
                        "{{ $extraction->raw_transcript ?? 'Transcript pending...' }}"
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Proposed Update --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex items-center gap-2" style="background-color: {{ $settings->primary_color }}10">
                <x-icon name="pencil-square" class="w-5 h-5" style="color: {{ $settings->primary_color }}" />
                <h3 class="font-semibold text-gray-900">Proposed Update: {{ $settings->contact_label_singular }}</h3>
            </div>

            <div class="p-4">
                <form wire:submit.prevent="apply" class="space-y-4">
                    @foreach($editableData as $key => $value)
                        @php
                            // Determine if this field needs review (low overall confidence affects all fields)
                            $needsReview = $confidenceScore < 70;
                            $fieldClasses = $needsReview
                                ? 'border-yellow-400 bg-yellow-50 focus:border-yellow-500 focus:ring-yellow-500'
                                : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500';
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <label class="block text-sm font-medium text-gray-700 capitalize">
                                    {{ str_replace('_', ' ', $key) }}
                                </label>
                                @if($needsReview)
                                    <span class="inline-flex items-center gap-1 text-xs text-yellow-600">
                                        <x-icon name="exclamation-triangle" class="w-3.5 h-3.5" />
                                        Verify
                                    </span>
                                @endif
                            </div>
                            <input
                                type="text"
                                wire:model.live="editableData.{{ $key }}"
                                class="block w-full px-3 py-2 border rounded-md shadow-sm text-sm {{ $fieldClasses }}"
                            />
                        </div>
                    @endforeach

                    <div class="pt-4 flex items-center justify-between border-t border-gray-200">
                        <button
                            type="button"
                            wire:click="reject"
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors"
                        >
                            <x-icon name="x-mark" class="w-4 h-4" />
                            Reject Extraction
                        </button>
                        <button
                            type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold text-white rounded-lg shadow-sm hover:opacity-90 transition-opacity"
                            style="background-color: {{ $settings->primary_color }}"
                        >
                            <x-icon name="check" class="w-4 h-4" />
                            Apply to {{ $settings->contact_label_singular }} Record
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Suggested Resources Section --}}
    @if($suggestions->count() > 0)
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center gap-2 bg-gray-50">
            <x-icon name="light-bulb" class="w-5 h-5 text-amber-500" />
            <h3 class="font-semibold text-gray-900">Suggested Resources</h3>
            <span class="ml-auto text-sm text-gray-500">Based on narrative content</span>
        </div>
        <div class="p-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                @foreach($suggestions as $resource)
                    <div class="flex items-center justify-between border border-gray-200 rounded-lg p-3 hover:border-gray-300 hover:shadow-sm transition-all">
                        <div class="min-w-0 flex-1 mr-3">
                            <div class="text-sm font-medium text-gray-900 truncate">{{ $resource->title }}</div>
                            <div class="text-xs text-gray-500">{{ $resource->category ?? 'Resource' }}</div>
                        </div>
                        <button
                            type="button"
                            wire:click="addResourceToPlan({{ $resource->id }})"
                            class="flex-shrink-0 inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-md text-white transition-opacity hover:opacity-90"
                            style="background-color: {{ $settings->primary_color }}"
                        >
                            <x-icon name="plus" class="w-3.5 h-3.5" />
                            Add
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
