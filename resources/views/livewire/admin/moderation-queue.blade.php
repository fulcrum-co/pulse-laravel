<div>
    {{-- Header with Stats --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Content Moderation Queue</h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            Review AI-generated content before publication
        </p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pending_review'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Needs Review</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-2xl font-bold text-green-600">{{ $stats['passed'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Passed</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-2xl font-bold text-yellow-600">{{ $stats['flagged'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Flagged</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-2xl font-bold text-red-600">{{ $stats['rejected'] ?? 0 }}</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Rejected</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 shadow-sm">
            <div class="text-2xl font-bold text-blue-600">{{ number_format(($stats['average_score'] ?? 0) * 100) }}%</div>
            <div class="text-sm text-gray-500 dark:text-gray-400">Avg Score</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-4 mb-6">
        <select wire:model.live="statusFilter" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            <option value="needs_review">Needs Review</option>
            <option value="flagged">Flagged</option>
            <option value="rejected">Rejected</option>
            <option value="passed">Passed</option>
            <option value="pending">Pending</option>
            <option value="">All</option>
        </select>

        <select wire:model.live="contentTypeFilter" class="rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm">
            <option value="">All Content Types</option>
            @foreach($contentTypes as $class => $label)
                <option value="{{ $class }}">{{ $label }}</option>
            @endforeach
        </select>
    </div>

    {{-- Results Table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-700">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('created_at')">
                        Date
                        @if($sortBy === 'created_at')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Content
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Type
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider cursor-pointer" wire:click="sortBy('overall_score')">
                        Score
                        @if($sortBy === 'overall_score')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '↑' : '↓' }}</span>
                        @endif
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Flags
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($results as $result)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $result->created_at->format('M j, Y H:i') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                            <div class="max-w-xs truncate">
                                {{ $result->moderatable?->title ?? 'Unknown' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ class_basename($result->moderatable_type) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $scorePercent = ($result->overall_score ?? 0) * 100;
                                $scoreColor = $scorePercent >= 85 ? 'green' : ($scorePercent >= 70 ? 'yellow' : 'red');
                            @endphp
                            <div class="flex items-center">
                                <div class="w-16 bg-gray-200 dark:bg-gray-600 rounded-full h-2 mr-2">
                                    <div class="bg-{{ $scoreColor }}-500 h-2 rounded-full" style="width: {{ $scorePercent }}%"></div>
                                </div>
                                <span class="text-sm font-medium text-{{ $scoreColor }}-600 dark:text-{{ $scoreColor }}-400">
                                    {{ number_format($scorePercent) }}%
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusColors = [
                                    'passed' => 'green',
                                    'flagged' => 'yellow',
                                    'rejected' => 'red',
                                    'approved_override' => 'blue',
                                    'pending' => 'gray',
                                ];
                                $color = $statusColors[$result->status] ?? 'gray';
                            @endphp
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $color }}-100 text-{{ $color }}-800 dark:bg-{{ $color }}-900 dark:text-{{ $color }}-200">
                                {{ ucfirst(str_replace('_', ' ', $result->status)) }}
                            </span>
                            @if($result->human_reviewed)
                                <span class="ml-1 text-xs text-gray-400" title="Reviewed by {{ $result->reviewer?->name ?? 'Unknown' }}">
                                    ✓
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($result->flags && count($result->flags) > 0)
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ count($result->flags) }} flag(s)
                                </span>
                            @else
                                <span class="text-sm text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <button
                                wire:click="openReviewModal({{ $result->id }})"
                                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
                            >
                                Review
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                            No items in the moderation queue
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $results->links() }}
        </div>
    </div>

    {{-- Review Modal --}}
    @if($showReviewModal && $selectedResult)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-75 transition-opacity" wire:click="closeReviewModal"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

                <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="flex justify-between items-start mb-4">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white">
                                Review: {{ $selectedResult->moderatable?->title ?? 'Content' }}
                            </h3>
                            <button wire:click="closeReviewModal" class="text-gray-400 hover:text-gray-500">
                                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            {{-- Scores --}}
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Dimension Scores</h4>
                                <div class="space-y-3">
                                    @php
                                        $dimensions = [
                                            'Age Appropriateness' => $selectedResult->age_appropriateness_score,
                                            'Clinical Safety' => $selectedResult->clinical_safety_score,
                                            'Cultural Sensitivity' => $selectedResult->cultural_sensitivity_score,
                                            'Accuracy' => $selectedResult->accuracy_score,
                                        ];
                                    @endphp
                                    @foreach($dimensions as $label => $score)
                                        <div>
                                            <div class="flex justify-between text-sm mb-1">
                                                <span class="text-gray-600 dark:text-gray-400">{{ $label }}</span>
                                                <span class="font-medium {{ $score >= 0.85 ? 'text-green-600' : ($score >= 0.7 ? 'text-yellow-600' : 'text-red-600') }}">
                                                    {{ $score !== null ? number_format($score * 100) . '%' : 'N/A' }}
                                                </span>
                                            </div>
                                            <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2">
                                                <div class="h-2 rounded-full {{ $score >= 0.85 ? 'bg-green-500' : ($score >= 0.7 ? 'bg-yellow-500' : 'bg-red-500') }}" style="width: {{ ($score ?? 0) * 100 }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Overall Score</span>
                                        <span class="text-2xl font-bold {{ ($selectedResult->overall_score ?? 0) >= 0.85 ? 'text-green-600' : (($selectedResult->overall_score ?? 0) >= 0.7 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ number_format(($selectedResult->overall_score ?? 0) * 100) }}%
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Flags & Recommendations --}}
                            <div>
                                @if($selectedResult->flags && count($selectedResult->flags) > 0)
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Concerns Identified</h4>
                                    <ul class="list-disc list-inside text-sm text-red-600 dark:text-red-400 space-y-1 mb-4">
                                        @foreach($selectedResult->flags as $flag)
                                            <li>{{ $flag }}</li>
                                        @endforeach
                                    </ul>
                                @endif

                                @if($selectedResult->recommendations && count($selectedResult->recommendations) > 0)
                                    <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Recommendations</h4>
                                    <ul class="list-disc list-inside text-sm text-gray-600 dark:text-gray-400 space-y-1">
                                        @foreach($selectedResult->recommendations as $rec)
                                            <li>{{ $rec }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        </div>

                        {{-- Review Notes --}}
                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Review Notes
                            </label>
                            <textarea
                                wire:model="reviewNotes"
                                rows="3"
                                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm"
                                placeholder="Add notes about your review decision..."
                            ></textarea>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-3">
                        <button
                            wire:click="approveContent"
                            class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm"
                        >
                            Approve
                        </button>
                        <button
                            wire:click="requestRevision"
                            class="w-full sm:w-auto inline-flex justify-center rounded-md border border-yellow-300 shadow-sm px-4 py-2 bg-yellow-50 text-base font-medium text-yellow-700 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 sm:text-sm dark:bg-yellow-900 dark:text-yellow-200 dark:border-yellow-700"
                        >
                            Request Revision
                        </button>
                        <button
                            wire:click="rejectContent"
                            class="w-full sm:w-auto inline-flex justify-center rounded-md border border-red-300 shadow-sm px-4 py-2 bg-red-50 text-base font-medium text-red-700 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:text-sm dark:bg-red-900 dark:text-red-200 dark:border-red-700"
                        >
                            Reject
                        </button>
                        <button
                            wire:click="closeReviewModal"
                            class="w-full sm:w-auto inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:text-sm dark:bg-gray-600 dark:text-gray-200 dark:border-gray-500"
                        >
                            Cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
