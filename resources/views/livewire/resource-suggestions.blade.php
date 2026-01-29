<div>
    <!-- Add Suggestion Button -->
    <div class="mb-4">
        <button
            wire:click="toggleAddForm"
            class="flex items-center gap-2 px-3 py-2 text-sm font-medium text-blue-600 hover:text-blue-700 hover:bg-blue-50 rounded-lg transition-colors"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Suggestion
        </button>
    </div>

    <!-- Add Form -->
    @if($showAddForm)
    <div class="mb-4 p-4 bg-gray-50 rounded-lg">
        <div class="mb-3">
            <label class="block text-sm font-medium text-gray-700 mb-1">Select Resource</label>
            <select wire:model="selectedResourceId" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                <option value="">Choose a resource...</option>
                @foreach($availableResources as $resource)
                <option value="{{ $resource->id }}">{{ $resource->title }} ({{ $resource->category }})</option>
                @endforeach
            </select>
            @error('selectedResourceId') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div class="flex gap-2">
            <x-button wire:click="addManualSuggestion" variant="primary" size="small">Add</x-button>
            <x-button wire:click="toggleAddForm" variant="secondary" size="small">Cancel</x-button>
        </div>
    </div>
    @endif

    <!-- Suggestions List -->
    <div class="space-y-3">
        @forelse($suggestions as $suggestion)
        <div class="flex items-start justify-between p-3 {{ $suggestion->status === 'pending' ? 'bg-yellow-50 border border-yellow-200' : 'bg-gray-50' }} rounded-lg">
            <div class="flex-1">
                <div class="font-medium text-gray-900">{{ $suggestion->resource->title ?? 'Unknown Resource' }}</div>
                <div class="text-sm text-gray-500">
                    {{ $suggestion->resource->category ?? '' }}
                    @if($suggestion->suggestion_source === 'ai_recommendation')
                    <span class="ml-2 px-2 py-0.5 text-xs bg-purple-100 text-purple-700 rounded-full">AI Suggested</span>
                    @elseif($suggestion->suggestion_source === 'rule_based')
                    <span class="ml-2 px-2 py-0.5 text-xs bg-blue-100 text-blue-700 rounded-full">Auto-matched</span>
                    @endif
                </div>
                @if($suggestion->ai_rationale)
                <div class="mt-1 text-xs text-gray-600 italic">{{ $suggestion->ai_rationale }}</div>
                @endif
                @if($suggestion->relevance_score)
                <div class="mt-1 text-xs text-gray-500">Relevance: {{ number_format($suggestion->relevance_score, 0) }}%</div>
                @endif
            </div>

            <div class="flex items-center gap-2 ml-3">
                @if($suggestion->status === 'pending')
                <button
                    wire:click="openReview({{ $suggestion->id }})"
                    class="px-3 py-1 text-sm font-medium text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                >
                    Review
                </button>
                @elseif($suggestion->status === 'accepted')
                <x-badge color="green">Accepted</x-badge>
                @elseif($suggestion->status === 'declined')
                <x-badge color="gray">Declined</x-badge>
                @elseif($suggestion->status === 'assigned')
                <x-badge color="blue">Assigned</x-badge>
                @endif
            </div>
        </div>
        @empty
        <div class="text-center py-6 text-gray-500">
            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            <p>No resource suggestions yet.</p>
        </div>
        @endforelse
    </div>

    <!-- Review Modal -->
    @if($reviewingSuggestionId)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Review Suggestion</h3>
            </div>
            <div class="p-4">
                @php
                    $reviewSuggestion = $suggestions->firstWhere('id', $reviewingSuggestionId);
                @endphp
                @if($reviewSuggestion)
                <div class="mb-4">
                    <div class="font-medium text-gray-900">{{ $reviewSuggestion->resource->title ?? 'Unknown' }}</div>
                    <div class="text-sm text-gray-500">{{ $reviewSuggestion->resource->description ?? '' }}</div>
                </div>
                @endif

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                    <textarea
                        wire:model="reviewNotes"
                        rows="3"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500"
                        placeholder="Add any notes about this decision..."
                    ></textarea>
                </div>
            </div>
            <div class="p-4 border-t border-gray-200 flex justify-end gap-2">
                <x-button wire:click="closeReview" variant="secondary">Cancel</x-button>
                <x-button wire:click="declineSuggestion" variant="secondary" class="text-red-600 hover:bg-red-50">Decline</x-button>
                <x-button wire:click="acceptSuggestion" variant="primary">Accept & Assign</x-button>
            </div>
        </div>
    </div>
    @endif
</div>
