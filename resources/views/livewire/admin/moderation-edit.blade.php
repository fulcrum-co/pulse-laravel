<div class="max-w-3xl mx-auto">
    {{-- Back Link --}}
    <a href="{{ route('admin.moderation') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mb-6" title="Return to moderation queue">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Queue
    </a>

    {{-- Header Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide mb-1">
                    {{ $contentType }}
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
            </div>
            <div class="flex items-center gap-2">
                @php
                    $scorePercent = ($result->overall_score ?? 0) * 100;
                    $scoreColor = $scorePercent >= 85 ? 'green' : ($scorePercent >= 70 ? 'yellow' : 'red');
                @endphp
                <span class="px-3 py-1.5 text-sm font-bold rounded-lg bg-{{ $scoreColor }}-100 text-{{ $scoreColor }}-700">
                    {{ number_format($scorePercent) }}% Score
                </span>
            </div>
        </div>

        {{-- Score Breakdown --}}
        <div class="grid grid-cols-4 gap-3 mt-4 pt-4 border-t border-gray-100 dark:border-gray-700">
            @php
                $dimensions = [
                    'Age' => $result->age_appropriateness_score,
                    'Safety' => $result->clinical_safety_score,
                    'Cultural' => $result->cultural_sensitivity_score,
                    'Accuracy' => $result->accuracy_score,
                ];
            @endphp
            @foreach($dimensions as $label => $score)
                @php $dimColor = $score >= 0.85 ? 'green' : ($score >= 0.7 ? 'yellow' : 'red'); @endphp
                <div class="text-center">
                    <div class="text-lg font-bold text-{{ $dimColor }}-600 dark:text-{{ $dimColor }}-400">
                        {{ $score !== null ? number_format($score * 100) : '—' }}%
                    </div>
                    <div class="text-xs text-gray-500">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Flags --}}
    @if($result->flags && count($result->flags) > 0)
        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4 mb-6">
            <h3 class="text-sm font-semibold text-red-800 dark:text-red-200 mb-2">Issues Identified</h3>
            <ul class="space-y-1">
                @foreach($result->flags as $flag)
                    <li class="text-sm text-red-700 dark:text-red-300 flex items-start">
                        <span class="mr-2">•</span>
                        <span>{{ $flag }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Recommendations --}}
    @if($result->recommendations && count($result->recommendations) > 0)
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4 mb-6">
            <h3 class="text-sm font-semibold text-blue-800 dark:text-blue-200 mb-2">Recommendations</h3>
            <ul class="space-y-1">
                @foreach($result->recommendations as $rec)
                    <li class="text-sm text-blue-700 dark:text-blue-300 flex items-start">
                        <span class="mr-2">•</span>
                        <span>{{ $rec }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Edit Form --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Edit Content</h2>

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                <input
                    type="text"
                    wire:model="title"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                >
                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea
                    wire:model="description"
                    rows="4"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                ></textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            @if($contentType === 'MiniCourse')
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rationale</label>
                    <textarea
                        wire:model="rationale"
                        rows="2"
                        placeholder="Why this course matters..."
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    ></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Expected Experience</label>
                    <textarea
                        wire:model="expectedExperience"
                        rows="2"
                        placeholder="What learners will experience..."
                        class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                    ></textarea>
                </div>
            @endif
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-between mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
            <button
                wire:click="cancel"
                class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg"
                title="Discard changes and return to queue"
            >
                Cancel
            </button>

            <div class="flex items-center gap-3">
                <button
                    wire:click="save"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg"
                    title="Save changes and run AI moderation again"
                >
                    <span wire:loading.remove wire:target="save">Save & Re-moderate</span>
                    <span wire:loading wire:target="save">Saving...</span>
                </button>

                <button
                    wire:click="saveAndApprove"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg"
                    title="Save changes and approve for publishing"
                >
                    <span wire:loading.remove wire:target="saveAndApprove">Save & Approve</span>
                    <span wire:loading wire:target="saveAndApprove">Approving...</span>
                </button>

                @if($contentType === 'MiniCourse')
                    <button
                        wire:click="saveAndPublish"
                        wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg"
                        title="Save changes and publish immediately"
                    >
                        <span wire:loading.remove wire:target="saveAndPublish">Publish</span>
                        <span wire:loading wire:target="saveAndPublish">Publishing...</span>
                    </button>
                @endif
            </div>
        </div>
    </div>
</div>
