<div class="min-h-screen bg-gray-50" x-data="{
    shortcuts: true,
    handleKeydown(e) {
        if (!this.shortcuts || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT') return;

        const actions = {
            'a': () => $wire.approve(),
            'r': () => $wire.reject(),
            'c': () => $wire.requestChanges(),
            'e': () => $wire.escalate(),
            's': () => $wire.skipItem(),
            'Escape': () => $wire.exitReview(),
        };

        if (actions[e.key]) {
            e.preventDefault();
            actions[e.key]();
        }
    }
}" @keydown.window="handleKeydown($event)">

    {{-- Progress Header --}}
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                {{-- Progress Stats --}}
                <div class="flex items-center space-x-6">
                    <div>
                        <span class="text-2xl font-bold text-gray-900">{{ $this->todayProgress['completed'] }}</span>
                        <span class="text-gray-500 text-sm ml-1">/ {{ $this->todayProgress['target'] }} today</span>
                    </div>
                    <div class="w-48 bg-gray-200 rounded-full h-2">
                        <div class="bg-pulse-orange-500 h-2 rounded-full transition-all duration-300"
                             style="width: {{ $this->todayProgress['percentage'] }}%"></div>
                    </div>
                </div>

                {{-- Navigation --}}
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.moderation') }}"
                       class="text-gray-600 hover:text-gray-900 flex items-center text-sm"
                       title="View moderation queue">
                        <x-icon name="queue-list" class="w-5 h-5 mr-2" />
                        Queue
                    </a>
                    <a href="{{ route('admin.moderation.dashboard') }}"
                       class="text-gray-600 hover:text-gray-900 flex items-center text-sm"
                       title="View dashboard and analytics">
                        <x-icon name="chart-bar" class="w-5 h-5 mr-2" />
                        Dashboard
                    </a>
                    @if($viewMode === 'reviewing')
                        <button wire:click="exitReview"
                                class="text-gray-600 hover:text-gray-900 flex items-center text-sm"
                                title="Exit review mode (Esc)">
                            <x-icon name="x-mark" class="w-5 h-5 mr-2" />
                            Exit Review
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if($viewMode === 'queue')
            {{-- Queue View --}}
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900">My Moderation Queue</h1>
                <p class="text-gray-500 mt-1">{{ $this->queueStats['total'] }} items pending review</p>
            </div>

            {{-- Start Review Button --}}
            <div class="mb-8 flex justify-center">
                <button wire:click="startNextItem"
                        class="px-8 py-4 bg-pulse-orange-500 text-white rounded-xl font-semibold text-lg hover:bg-pulse-orange-600 transition-colors shadow-lg flex items-center"
                        title="Begin reviewing the next item in your queue">
                    <x-icon name="play" class="w-6 h-6 mr-3" />
                    Start Reviewing
                </button>
            </div>

            {{-- Queue List --}}
            @if($this->myQueue->isNotEmpty())
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="divide-y divide-gray-200">
                        @foreach($this->myQueue as $queueItem)
                            <div class="p-4 hover:bg-gray-50 flex items-center justify-between cursor-pointer"
                                 wire:click="claimItem({{ $queueItem->id }})">
                                <div class="flex items-center space-x-4">
                                    {{-- Priority Badge --}}
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ match($queueItem->priority) {
                                            'urgent' => 'bg-red-100 text-red-700',
                                            'high' => 'bg-orange-100 text-orange-700',
                                            'normal' => 'bg-blue-100 text-blue-700',
                                            'low' => 'bg-gray-100 text-gray-700',
                                            default => 'bg-gray-100 text-gray-700'
                                        } }}">
                                        {{ ucfirst($queueItem->priority) }}
                                    </span>

                                    <div>
                                        <h3 class="font-medium text-gray-900">
                                            {{ $queueItem->moderationResult?->moderatable?->title ?? 'Untitled' }}
                                        </h3>
                                        <p class="text-sm text-gray-500">
                                            {{ class_basename($queueItem->moderationResult?->moderatable_type ?? 'Content') }}
                                            · Score: {{ number_format($queueItem->moderationResult?->overall_score ?? 0, 2) }}
                                        </p>
                                    </div>
                                </div>

                                <div class="flex items-center space-x-4 text-sm">
                                    @if($queueItem->sla_status === 'warning')
                                        <span class="text-yellow-600 flex items-center" title="SLA deadline approaching">
                                            <x-icon name="clock" class="w-4 h-4 mr-1" />
                                            Due soon
                                        </span>
                                    @elseif($queueItem->sla_status === 'breached')
                                        <span class="text-red-600 flex items-center" title="SLA deadline exceeded">
                                            <x-icon name="exclamation-triangle" class="w-4 h-4 mr-1" />
                                            Overdue
                                        </span>
                                    @else
                                        <span class="text-gray-500">
                                            Due {{ $queueItem->due_at?->diffForHumans() ?? 'N/A' }}
                                        </span>
                                    @endif

                                    <x-icon name="chevron-right" class="w-5 h-5 text-gray-400" title="Click to review" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="text-center py-12 bg-white rounded-xl border border-gray-200">
                    <x-icon name="check-circle" class="w-16 h-16 text-green-500 mx-auto mb-4" title="All caught up" />
                    <h3 class="text-lg font-medium text-gray-900">All caught up!</h3>
                    <p class="text-gray-500 mt-1">No items assigned to you. Check the unassigned queue.</p>
                </div>
            @endif

        @elseif($viewMode === 'reviewing' && $currentItem)
            {{-- Review Mode --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Content Preview (2/3) --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        {{-- Content Header --}}
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <span class="px-2 py-1 text-xs font-medium rounded-full mr-2
                                    {{ match($currentItem->priority) {
                                        'urgent' => 'bg-red-100 text-red-700',
                                        'high' => 'bg-orange-100 text-orange-700',
                                        'normal' => 'bg-blue-100 text-blue-700',
                                        'low' => 'bg-gray-100 text-gray-700',
                                        default => 'bg-gray-100 text-gray-700'
                                    } }}">
                                    {{ ucfirst($currentItem->priority) }} Priority
                                </span>
                                <span class="text-gray-500 text-sm">
                                    {{ class_basename($currentItem->moderationResult?->moderatable_type ?? 'Content') }}
                                </span>
                            </div>
                            <button wire:click="openEditModal"
                                    class="text-pulse-blue-500 hover:text-pulse-blue-700 text-sm font-medium flex items-center"
                                    title="Edit this content before approving">
                                <x-icon name="pencil" class="w-4 h-4 mr-1" />
                                Edit Content
                            </button>
                        </div>

                        {{-- Content Body --}}
                        <div class="p-6">
                            @php $moderatable = $currentItem->moderationResult?->moderatable; @endphp

                            <h1 class="text-2xl font-bold text-gray-900 mb-4">
                                {{ $moderatable?->title ?? 'Untitled' }}
                            </h1>

                            @if($moderatable?->description)
                                <div class="prose prose-gray max-w-none mb-6">
                                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Description</h3>
                                    <p>{{ $moderatable->description }}</p>
                                </div>
                            @endif

                            @if($moderatable?->rationale)
                                <div class="prose prose-gray max-w-none mb-6">
                                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Rationale</h3>
                                    <p>{{ $moderatable->rationale }}</p>
                                </div>
                            @endif

                            @if(is_array($moderatable?->objectives) && count($moderatable->objectives) > 0)
                                <div class="mb-6">
                                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Objectives</h3>
                                    <ul class="list-disc list-inside space-y-1 text-gray-700">
                                        @foreach($moderatable->objectives as $objective)
                                            <li>{{ $objective }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>

                        {{-- AI Moderation Scores --}}
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            <h3 class="text-sm font-medium text-gray-700 mb-4">AI Moderation Scores</h3>

                            @php $result = $currentItem->moderationResult; @endphp

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                {{-- Overall Score --}}
                                <div class="text-center">
                                    <div class="text-2xl font-bold {{ ($result?->overall_score ?? 0) >= 0.7 ? 'text-green-600' : (($result?->overall_score ?? 0) >= 0.4 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ number_format(($result?->overall_score ?? 0) * 100, 0) }}%
                                    </div>
                                    <div class="text-xs text-gray-500">Overall</div>
                                </div>

                                {{-- Age Appropriateness --}}
                                <div class="text-center">
                                    <div class="text-xl font-semibold text-gray-700">
                                        {{ number_format(($result?->age_appropriateness_score ?? 0) * 100, 0) }}%
                                    </div>
                                    <div class="text-xs text-gray-500">Age Appropriate</div>
                                </div>

                                {{-- Clinical Safety --}}
                                <div class="text-center">
                                    <div class="text-xl font-semibold text-gray-700">
                                        {{ number_format(($result?->clinical_safety_score ?? 0) * 100, 0) }}%
                                    </div>
                                    <div class="text-xs text-gray-500">Clinical Safety</div>
                                </div>

                                {{-- Cultural Sensitivity --}}
                                <div class="text-center">
                                    <div class="text-xl font-semibold text-gray-700">
                                        {{ number_format(($result?->cultural_sensitivity_score ?? 0) * 100, 0) }}%
                                    </div>
                                    <div class="text-xs text-gray-500">Cultural Sensitivity</div>
                                </div>
                            </div>

                            {{-- Flags --}}
                            @if(!empty($result?->flags))
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <h4 class="text-sm font-medium text-red-600 mb-2 flex items-center">
                                        <x-icon name="flag" class="w-4 h-4 mr-1" title="Flagged Concerns" />
                                        Flagged Concerns
                                    </h4>
                                    <div class="space-y-2">
                                        @foreach($result->flags as $category => $description)
                                            <div class="bg-red-50 border border-red-200 rounded-lg px-3 py-2">
                                                <span class="font-medium text-red-700">{{ ucwords(str_replace('_', ' ', $category)) }}:</span>
                                                <span class="text-red-600">{{ $description }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Decision Panel (1/3) --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-24">
                        <div class="p-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Make Decision</h2>

                            {{-- Timer --}}
                            <div class="text-center mb-6 py-3 bg-gray-50 rounded-lg">
                                <span class="text-3xl font-mono text-gray-700" wire:poll.1s="$refresh">
                                    {{ $this->timeSpent }}
                                </span>
                                <p class="text-xs text-gray-500 mt-1">Time on this review</p>
                            </div>

                            {{-- Notes --}}
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Notes (optional)
                                </label>
                                <textarea wire:model="notes"
                                          rows="4"
                                          class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-pulse-blue-500 focus:border-pulse-blue-500"
                                          placeholder="Add any notes about your decision..."></textarea>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="space-y-3">
                                <button wire:click="approve"
                                        class="w-full px-4 py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition-colors flex items-center justify-center"
                                        title="Approve this content (Keyboard: A)">
                                    <x-icon name="check" class="w-5 h-5 mr-2" />
                                    Approve
                                    <kbd class="ml-auto bg-green-700 px-2 py-0.5 rounded text-xs">A</kbd>
                                </button>

                                <button wire:click="requestChanges"
                                        class="w-full px-4 py-3 bg-yellow-500 text-white rounded-lg font-medium hover:bg-yellow-600 transition-colors flex items-center justify-center"
                                        title="Request changes from the creator (Keyboard: C)">
                                    <x-icon name="pencil-square" class="w-5 h-5 mr-2" />
                                    Request Changes
                                    <kbd class="ml-auto bg-yellow-600 px-2 py-0.5 rounded text-xs">C</kbd>
                                </button>

                                <button wire:click="reject"
                                        class="w-full px-4 py-3 bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition-colors flex items-center justify-center"
                                        title="Reject this content (Keyboard: R)">
                                    <x-icon name="x-mark" class="w-5 h-5 mr-2" />
                                    Reject
                                    <kbd class="ml-auto bg-red-700 px-2 py-0.5 rounded text-xs">R</kbd>
                                </button>

                                <button wire:click="escalate"
                                        class="w-full px-4 py-3 bg-orange-500 text-white rounded-lg font-medium hover:bg-orange-600 transition-colors flex items-center justify-center"
                                        title="Escalate to a senior moderator (Keyboard: E)">
                                    <x-icon name="arrow-up-circle" class="w-5 h-5 mr-2" />
                                    Escalate
                                    <kbd class="ml-auto bg-orange-600 px-2 py-0.5 rounded text-xs">E</kbd>
                                </button>
                            </div>

                            <div class="mt-4 pt-4 border-t border-gray-200">
                                <button wire:click="skipItem"
                                        class="w-full px-4 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors flex items-center justify-center text-sm"
                                        title="Skip and review later (Keyboard: S)">
                                    <x-icon name="forward" class="w-4 h-4 mr-2" />
                                    Skip for now
                                    <kbd class="ml-auto bg-gray-200 px-2 py-0.5 rounded text-xs">S</kbd>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @elseif($viewMode === 'complete')
            {{-- All Done View --}}
            <div class="text-center py-16">
                <x-icon name="check-badge" class="w-24 h-24 text-green-500 mx-auto mb-6" title="All tasks completed" />
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Great work!</h1>
                <p class="text-gray-500 text-lg mb-8">You've completed all your assigned items.</p>

                <div class="flex justify-center space-x-4">
                    <a href="{{ route('admin.moderation') }}"
                       class="px-6 py-3 bg-white border border-gray-300 text-gray-700 rounded-lg font-medium hover:bg-gray-50 transition-colors">
                        View Queue
                    </a>
                    <a href="{{ route('admin.moderation.dashboard') }}"
                       class="px-6 py-3 bg-pulse-blue-500 text-white rounded-lg font-medium hover:bg-pulse-blue-600 transition-colors">
                        View Dashboard
                    </a>
                </div>
            </div>
        @endif
    </div>

    {{-- Edit Content Modal --}}
    @if($showEditModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
             x-data
             @keydown.escape.window="$wire.closeEditModal()">
            <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-900">Edit Content</h2>
                    <button wire:click="closeEditModal" class="text-gray-400 hover:text-gray-600" title="Close (Esc)">
                        <x-icon name="x-mark" class="w-6 h-6" />
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input type="text"
                               wire:model="editableContent.title"
                               class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-pulse-blue-500 focus:border-pulse-blue-500" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea wire:model="editableContent.description"
                                  rows="3"
                                  class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-pulse-blue-500 focus:border-pulse-blue-500"></textarea>
                    </div>

                    @if(isset($editableContent['rationale']))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rationale</label>
                            <textarea wire:model="editableContent.rationale"
                                      rows="3"
                                      class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-pulse-blue-500 focus:border-pulse-blue-500"></textarea>
                        </div>
                    @endif

                    @if(isset($editableContent['objectives']))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Objectives (one per line)</label>
                            <textarea wire:model="editableContent.objectives"
                                      rows="4"
                                      class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-pulse-blue-500 focus:border-pulse-blue-500"></textarea>
                        </div>
                    @endif

                    @if(isset($editableContent['content']))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Content</label>
                            <textarea wire:model="editableContent.content"
                                      rows="6"
                                      class="w-full border-gray-300 rounded-lg shadow-sm focus:ring-pulse-blue-500 focus:border-pulse-blue-500"></textarea>
                        </div>
                    @endif
                </div>

                <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                    <button wire:click="closeEditModal"
                            class="px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors">
                        Cancel
                    </button>
                    <button wire:click="saveEditedContent"
                            class="px-4 py-2 bg-pulse-blue-500 text-white rounded-lg hover:bg-pulse-blue-600 transition-colors">
                        Save Changes
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
