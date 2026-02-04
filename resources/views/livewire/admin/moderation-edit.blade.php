<div class="max-w-3xl mx-auto">
    {{-- Back Link --}}
    <a href="{{ route('admin.moderation') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 mb-6" title="Return to moderation queue">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Queue
    </a>

    {{-- Assignment Context Card --}}
    @if($result->assigned_to || $result->assignment_notes || $result->due_at)
        <div class="bg-pulse-orange-50 border border-pulse-orange-200 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 rounded-full bg-pulse-orange-100 flex items-center justify-center flex-shrink-0">
                    <x-icon name="clipboard-document-check" class="w-5 h-5 text-pulse-orange-600" />
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-semibold text-pulse-orange-900 mb-1">Assignment Details</h3>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-sm">
                        @if($result->assigner)
                            <div class="text-gray-600">
                                <span class="text-gray-500">Assigned by:</span>
                                <span class="font-medium">{{ $result->assigner->full_name }}</span>
                            </div>
                        @endif
                        @if($result->assigned_at)
                            <div class="text-gray-600">
                                <span class="text-gray-500">Assigned:</span>
                                <span>{{ $result->assigned_at->diffForHumans() }}</span>
                            </div>
                        @endif
                        @if($result->due_at)
                            <div class="text-gray-600">
                                <span class="text-gray-500">Due:</span>
                                <span class="{{ $result->isOverdue() ? 'text-red-600 font-medium' : ($result->isDueSoon() ? 'text-yellow-600' : '') }}">
                                    {{ $result->due_at->format('M j, Y g:i A') }}
                                    @if($result->isOverdue())
                                        (Overdue)
                                    @elseif($result->isDueSoon())
                                        (Due soon)
                                    @endif
                                </span>
                            </div>
                        @endif
                        @if($result->assignment_priority && $result->assignment_priority !== 'normal')
                            <div class="text-gray-600">
                                <span class="text-gray-500">Priority:</span>
                                @php
                                    $priorityColors = [
                                        'urgent' => 'bg-red-100 text-red-700',
                                        'high' => 'bg-orange-100 text-orange-700',
                                        'low' => 'bg-gray-100 text-gray-700',
                                    ];
                                @endphp
                                <span class="px-1.5 py-0.5 text-xs font-medium rounded {{ $priorityColors[$result->assignment_priority] ?? '' }}">
                                    {{ ucfirst($result->assignment_priority) }}
                                </span>
                            </div>
                        @endif
                    </div>
                    @if($result->assignment_notes)
                        <div class="mt-2 p-2 bg-white/50 rounded text-sm text-gray-700 italic">
                            "{{ $result->assignment_notes }}"
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Content Author & Notification Card --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="flex items-start gap-4">
            {{-- Author Avatar --}}
            @php
                $contentOwner = $moderatable && isset($moderatable->created_by)
                    ? \App\Models\User::find($moderatable->created_by)
                    : null;
            @endphp
            <div class="w-12 h-12 rounded-full bg-pulse-blue-100 flex items-center justify-center flex-shrink-0">
                @if($contentOwner)
                    <span class="text-lg font-semibold text-pulse-blue-600">{{ substr($contentOwner->first_name ?? 'U', 0, 1) }}</span>
                @else
                    <x-icon name="user" class="w-6 h-6 text-pulse-blue-600" />
                @endif
            </div>

            {{-- Author Details --}}
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Content Author</h3>
                        @if($contentOwner)
                            <p class="text-base font-medium text-gray-800 dark:text-gray-200">{{ $contentOwner->full_name }}</p>
                            <p class="text-sm text-gray-500">{{ $contentOwner->email }}</p>
                        @else
                            <p class="text-sm text-gray-500">Unknown author</p>
                        @endif
                    </div>
                    <div class="text-right text-sm">
                        <div class="text-gray-500">Submitted</div>
                        <div class="text-gray-700 dark:text-gray-300">{{ $moderatable?->created_at?->format('M j, Y') ?? '—' }}</div>
                    </div>
                </div>

                {{-- What Happens Next --}}
                <div class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <div class="flex items-start gap-2">
                        <x-icon name="arrow-right-circle" class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" />
                        <div class="text-sm">
                            <span class="font-medium text-gray-700 dark:text-gray-300">When you complete this review:</span>
                            <ul class="mt-1 text-gray-600 dark:text-gray-400 space-y-0.5">
                                @if($contentOwner)
                                    <li>• <strong>{{ $contentOwner->first_name }}</strong> will receive a notification with your decision</li>
                                @endif
                                <li>• Your review notes will be included in the notification</li>
                                <li>• The content status will be updated based on your action</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        {{-- Review Notes --}}
        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                Review Notes
                <span class="font-normal text-gray-500">(visible to content owner)</span>
            </label>
            <textarea
                wire:model="reviewNotes"
                rows="3"
                placeholder="Add feedback, suggestions, or explanation for your decision..."
                class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white"
            ></textarea>
            <p class="mt-1 text-xs text-gray-500">
                These notes will be included in the notification sent to the content owner.
            </p>
        </div>

        {{-- Actions --}}
        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-gray-700">
            {{-- Action Explanations --}}
            <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg text-xs text-gray-600 dark:text-gray-400 space-y-1">
                <p><strong>Save & Re-moderate:</strong> Update content and run AI moderation again</p>
                <p><strong>Request Revision:</strong> Send back to owner with your notes for changes</p>
                <p><strong>Reject:</strong> Permanently reject this content</p>
                <p><strong>Approve:</strong> Approve content (owner can then publish)</p>
                @if($contentType === 'MiniCourse')
                    <p><strong>Publish:</strong> Approve and make live immediately</p>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <button
                    wire:click="cancel"
                    class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg order-last sm:order-first"
                    title="Discard changes and return to queue"
                >
                    Cancel
                </button>

                <div class="flex flex-wrap items-center justify-end gap-2">
                    <button
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 rounded-lg"
                        title="Save changes and run AI moderation again"
                    >
                        <span wire:loading.remove wire:target="save">Save & Re-moderate</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>

                    <button
                        wire:click="requestRevision"
                        wire:loading.attr="disabled"
                        class="px-3 py-2 text-sm font-medium text-yellow-700 bg-yellow-100 hover:bg-yellow-200 rounded-lg"
                        title="Send back to owner for revisions"
                    >
                        <span wire:loading.remove wire:target="requestRevision">Request Revision</span>
                        <span wire:loading wire:target="requestRevision">Sending...</span>
                    </button>

                    <button
                        wire:click="reject"
                        wire:loading.attr="disabled"
                        class="px-3 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg"
                        title="Reject this content"
                    >
                        <span wire:loading.remove wire:target="reject">Reject</span>
                        <span wire:loading wire:target="reject">Rejecting...</span>
                    </button>

                    <button
                        wire:click="saveAndApprove"
                        wire:loading.attr="disabled"
                        class="px-3 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg"
                        title="Save changes and approve"
                    >
                        <span wire:loading.remove wire:target="saveAndApprove">Approve</span>
                        <span wire:loading wire:target="saveAndApprove">Approving...</span>
                    </button>

                    @if($contentType === 'MiniCourse')
                        <button
                            wire:click="saveAndPublish"
                            wire:loading.attr="disabled"
                            class="px-3 py-2 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg"
                            title="Approve and publish immediately"
                        >
                            <span wire:loading.remove wire:target="saveAndPublish">Publish</span>
                            <span wire:loading wire:target="saveAndPublish">Publishing...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
