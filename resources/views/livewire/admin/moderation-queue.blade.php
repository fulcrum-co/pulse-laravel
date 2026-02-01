<div class="flex gap-6">
    {{-- Left Sidebar --}}
    <div class="w-64 flex-shrink-0">
        {{-- Search --}}
        <div class="relative mb-6">
            <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" title="Search" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search queue..."
                class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
        </div>

        {{-- Status Filter --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</h3>
                @if($statusFilter)
                    <button wire:click="$set('statusFilter', '')" class="text-xs text-pulse-orange-600 hover:text-pulse-orange-700">Clear</button>
                @endif
            </div>
            <div class="space-y-1">
                <label class="flex items-center gap-2 py-1 cursor-pointer">
                    <input type="radio" wire:model.live="statusFilter" value="needs_review" class="text-pulse-orange-500 focus:ring-pulse-orange-500">
                    <x-icon name="clock" class="w-4 h-4 text-yellow-500" title="Needs Review" />
                    <span class="text-sm text-gray-700">Needs Review</span>
                </label>
                <label class="flex items-center gap-2 py-1 cursor-pointer">
                    <input type="radio" wire:model.live="statusFilter" value="flagged" class="text-pulse-orange-500 focus:ring-pulse-orange-500">
                    <x-icon name="flag" class="w-4 h-4 text-yellow-500" title="Flagged" />
                    <span class="text-sm text-gray-700">Flagged</span>
                </label>
                <label class="flex items-center gap-2 py-1 cursor-pointer">
                    <input type="radio" wire:model.live="statusFilter" value="passed" class="text-pulse-orange-500 focus:ring-pulse-orange-500">
                    <x-icon name="check-circle" class="w-4 h-4 text-green-500" title="Passed" />
                    <span class="text-sm text-gray-700">Passed</span>
                </label>
                <label class="flex items-center gap-2 py-1 cursor-pointer">
                    <input type="radio" wire:model.live="statusFilter" value="rejected" class="text-pulse-orange-500 focus:ring-pulse-orange-500">
                    <x-icon name="x-circle" class="w-4 h-4 text-red-500" title="Rejected" />
                    <span class="text-sm text-gray-700">Rejected</span>
                </label>
            </div>
        </div>

        {{-- Content Type Filter --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Content Type</h3>
                @if($contentTypeFilter)
                    <button wire:click="$set('contentTypeFilter', '')" class="text-xs text-pulse-orange-600 hover:text-pulse-orange-700">Clear</button>
                @endif
            </div>
            <div class="space-y-1">
                @foreach($contentTypes as $class => $label)
                    <label class="flex items-center gap-2 py-1 cursor-pointer">
                        <input type="radio" wire:model.live="contentTypeFilter" value="{{ $class }}" class="text-pulse-orange-500 focus:ring-pulse-orange-500">
                        <x-icon name="{{ $label === 'MiniCourse' ? 'academic-cap' : 'document-text' }}" class="w-4 h-4 text-gray-400" title="{{ $label }}" />
                        <span class="text-sm text-gray-700">{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- Assignment Filter --}}
        @if($canViewAll)
            <div class="mb-6">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Assignment</h3>
                <select wire:model.live="assignmentFilter" class="w-full text-sm border border-gray-300 rounded-lg py-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                    <option value="all">All Items</option>
                    <option value="my_assignments">My Assignments ({{ $assignmentStats['my_assignments'] ?? 0 }})</option>
                    <option value="collaborating">Collaborating ({{ $assignmentStats['collaborating'] ?? 0 }})</option>
                    <option value="unassigned">Unassigned ({{ $assignmentStats['unassigned'] ?? 0 }})</option>
                </select>
            </div>
        @endif

        {{-- Sort By --}}
        <div>
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Sort By</h3>
            <select wire:model.live="sortBy" class="w-full text-sm border border-gray-300 rounded-lg py-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="score_low">Lowest Score</option>
                <option value="score_high">Highest Score</option>
            </select>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="flex-1 min-w-0">
        {{-- Stats Cards (compact, equal width) --}}
        <div class="grid grid-cols-4 gap-2 mb-4">
            <button wire:click="$set('statusFilter', 'needs_review')" class="w-full bg-white border rounded-lg px-3 py-2 cursor-pointer hover:border-yellow-300 transition-colors {{ $statusFilter === 'needs_review' ? 'border-yellow-400 bg-yellow-50' : 'border-gray-200' }}">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded bg-yellow-100 flex items-center justify-center flex-shrink-0" title="Pending Review">
                        <x-icon name="clock" class="w-3.5 h-3.5 text-yellow-600" />
                    </div>
                    <div class="text-left flex-1">
                        <div class="text-xs text-yellow-600 leading-tight">Pending</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $stats['pending_review'] ?? 0 }}</div>
                    </div>
                </div>
            </button>
            <button wire:click="$set('statusFilter', 'flagged')" class="w-full bg-white border rounded-lg px-3 py-2 cursor-pointer hover:border-orange-300 transition-colors {{ $statusFilter === 'flagged' ? 'border-orange-400 bg-orange-50' : 'border-gray-200' }}">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded bg-orange-100 flex items-center justify-center flex-shrink-0" title="Flagged for Review">
                        <x-icon name="flag" class="w-3.5 h-3.5 text-orange-600" />
                    </div>
                    <div class="text-left flex-1">
                        <div class="text-xs text-orange-600 leading-tight">Flagged</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $stats['flagged'] ?? 0 }}</div>
                    </div>
                </div>
            </button>
            <button wire:click="$set('statusFilter', 'passed')" class="w-full bg-white border rounded-lg px-3 py-2 cursor-pointer hover:border-green-300 transition-colors {{ $statusFilter === 'passed' ? 'border-green-400 bg-green-50' : 'border-gray-200' }}">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded bg-green-100 flex items-center justify-center flex-shrink-0" title="Passed Review">
                        <x-icon name="check-circle" class="w-3.5 h-3.5 text-green-600" />
                    </div>
                    <div class="text-left flex-1">
                        <div class="text-xs text-green-600 leading-tight">Passed</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $stats['passed'] ?? 0 }}</div>
                    </div>
                </div>
            </button>
            <button wire:click="$set('statusFilter', 'rejected')" class="w-full bg-white border rounded-lg px-3 py-2 cursor-pointer hover:border-red-300 transition-colors {{ $statusFilter === 'rejected' ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                <div class="flex items-center gap-2">
                    <div class="w-6 h-6 rounded bg-red-100 flex items-center justify-center flex-shrink-0" title="Rejected">
                        <x-icon name="x-circle" class="w-3.5 h-3.5 text-red-600" />
                    </div>
                    <div class="text-left flex-1">
                        <div class="text-xs text-red-600 leading-tight">Rejected</div>
                        <div class="text-sm font-semibold text-gray-900">{{ $stats['rejected'] ?? 0 }}</div>
                    </div>
                </div>
            </button>
        </div>

        {{-- Queue Header --}}
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <x-icon name="clock" class="w-5 h-5 text-gray-400" title="Review Queue" />
                <h2 class="text-lg font-semibold text-gray-900">Review Queue</h2>
                <span class="text-sm text-gray-500">({{ $results->total() }} items)</span>
            </div>

            <div class="flex items-center gap-3">
                {{-- Dashboard Link --}}
                <a href="{{ route('admin.moderation.dashboard') }}"
                   class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors"
                   title="View moderation dashboard and analytics">
                    <x-icon name="chart-bar" class="w-4 h-4" />
                    Dashboard
                </a>

                {{-- Bulk Actions --}}
                @if($canAssign && count($selectedItems) > 0)
                    <div class="w-px h-6 bg-gray-200"></div>
                    <span class="text-sm text-gray-500 mr-2">{{ count($selectedItems) }} selected</span>
                    <button wire:click="bulkAssign" class="px-3 py-1.5 text-sm font-medium text-pulse-orange-600 bg-pulse-orange-50 hover:bg-pulse-orange-100 rounded-lg" title="Assign selected items to a moderator">
                        Assign
                    </button>
                    <button wire:click="$set('selectedItems', [])" class="p-1.5 text-gray-400 hover:text-gray-600" title="Clear selection">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                @endif

                <div class="w-px h-6 bg-gray-200"></div>

                {{-- View Toggle --}}
                <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                    <button
                        wire:click="$set('viewMode', 'list')"
                        class="p-2 {{ ($viewMode ?? 'list') === 'list' ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}"
                        title="List view"
                    >
                        <x-icon name="list-bullet" class="w-4 h-4" />
                    </button>
                    <button
                        wire:click="$set('viewMode', 'grid')"
                        class="p-2 border-l border-gray-200 {{ ($viewMode ?? 'list') === 'grid' ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}"
                        title="Grid view"
                    >
                        <x-icon name="squares-2x2" class="w-4 h-4" />
                    </button>
                    <button
                        wire:click="$set('viewMode', 'table')"
                        class="p-2 border-l border-gray-200 {{ ($viewMode ?? 'list') === 'table' ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}"
                        title="Table view"
                    >
                        <x-icon name="table-cells" class="w-4 h-4" />
                    </button>
                </div>

                {{-- Start Reviewing Button (far right) --}}
                <a href="{{ route('admin.moderation.task-flow') }}"
                   class="flex items-center gap-2 px-3 py-1.5 text-sm bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors"
                   title="Start reviewing items in task flow mode">
                    <x-icon name="play" class="w-4 h-4" />
                    Start Reviewing
                </a>
            </div>
        </div>

        {{-- LIST VIEW --}}
        @if(($viewMode ?? 'list') === 'list')
            <div class="space-y-3">
                @forelse($results as $result)
                    @php
                        $scorePercent = ($result->overall_score ?? 0) * 100;
                        $scoreColor = $scorePercent >= 85 ? 'green' : ($scorePercent >= 70 ? 'yellow' : 'red');
                        $statusColors = [
                            'flagged' => 'yellow',
                            'rejected' => 'red',
                            'passed' => 'green',
                            'approved_override' => 'blue',
                        ];
                        $statusColor = $statusColors[$result->status] ?? 'gray';
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-xl p-4 hover:border-gray-300 transition-colors">
                        <div class="flex items-start gap-3">
                            @if($canAssign)
                                <input type="checkbox" wire:model.live="selectedItems" value="{{ $result->id }}" class="mt-1 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500">
                            @endif

                            <div class="w-10 h-10 rounded-lg bg-{{ $statusColor }}-100 flex items-center justify-center flex-shrink-0">
                                <x-icon name="{{ $result->status === 'flagged' ? 'flag' : ($result->status === 'rejected' ? 'x-circle' : 'document-text') }}" class="w-5 h-5 text-{{ $statusColor }}-600" />
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-medium text-gray-900 truncate">{{ $result->moderatable?->title ?? 'Unknown Content' }}</h3>
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-{{ $scoreColor }}-100 text-{{ $scoreColor }}-700">
                                        {{ number_format($scorePercent) }}%
                                    </span>
                                </div>
                                <p class="text-sm text-gray-500 mb-2">{{ class_basename($result->moderatable_type) }}</p>
                                @php
                                    $displayFlags = null;
                                    if ($result->flags && is_array($result->flags) && count($result->flags) > 0) {
                                        $displayFlags = current($result->flags);
                                    } elseif ($result->flags && is_string($result->flags) && !in_array($result->flags, ['', '[]', 'null'])) {
                                        $displayFlags = $result->flags;
                                    }
                                @endphp
                                @if($displayFlags)
                                    <p class="text-sm text-red-600 line-clamp-1">{{ $displayFlags }}</p>
                                @endif
                            </div>

                            <div class="flex flex-col items-end gap-2">
                                <span class="text-xs text-gray-400">{{ $result->created_at->diffForHumans() }}</span>
                                @if($result->assignee)
                                    <span class="text-xs text-pulse-orange-600">{{ $result->assignee->first_name }}</span>
                                @endif
                                <div class="flex items-center gap-1">
                                    @if($canAssign)
                                        <button wire:click="openAssignModal({{ $result->id }})" class="p-1.5 text-gray-400 hover:text-pulse-orange-600 hover:bg-pulse-orange-50 rounded-lg" title="Assign">
                                            <x-icon name="user-plus" class="w-4 h-4" />
                                        </button>
                                    @endif
                                    <a href="{{ route('admin.moderation.edit', $result->id) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Review">
                                        <x-icon name="pencil-square" class="w-4 h-4" />
                                    </a>
                                    <button wire:click="quickApprove({{ $result->id }})" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg" title="Approve">
                                        <x-icon name="check-circle" class="w-4 h-4" />
                                    </button>
                                    <button wire:click="quickReject({{ $result->id }})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Reject">
                                        <x-icon name="x-circle" class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="bg-white border border-gray-200 rounded-xl p-12 text-center">
                        <x-icon name="check-circle" class="w-12 h-12 mx-auto text-green-200" title="All caught up" />
                        <h3 class="mt-3 text-lg font-medium text-gray-900">All caught up!</h3>
                        <p class="mt-1 text-sm text-gray-500">No items need review right now.</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- GRID VIEW --}}
        @if(($viewMode ?? 'list') === 'grid')
            <div class="grid grid-cols-2 xl:grid-cols-3 gap-4">
                @forelse($results as $result)
                    @php
                        $scorePercent = ($result->overall_score ?? 0) * 100;
                        $scoreColor = $scorePercent >= 85 ? 'green' : ($scorePercent >= 70 ? 'yellow' : 'red');
                        $statusColors = [
                            'flagged' => 'yellow',
                            'rejected' => 'red',
                            'passed' => 'green',
                            'approved_override' => 'blue',
                        ];
                        $statusColor = $statusColors[$result->status] ?? 'gray';
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-xl p-4 hover:border-gray-300 transition-colors">
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-center gap-2">
                                @if($canAssign)
                                    <input type="checkbox" wire:model.live="selectedItems" value="{{ $result->id }}" class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500">
                                @endif
                                <div class="w-8 h-8 rounded-lg bg-{{ $statusColor }}-100 flex items-center justify-center">
                                    <x-icon name="{{ $result->status === 'flagged' ? 'flag' : 'document-text' }}" class="w-4 h-4 text-{{ $statusColor }}-600" />
                                </div>
                            </div>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-{{ $scoreColor }}-100 text-{{ $scoreColor }}-700">
                                {{ number_format($scorePercent) }}%
                            </span>
                        </div>

                        <h3 class="font-medium text-gray-900 line-clamp-2 mb-1">{{ $result->moderatable?->title ?? 'Unknown Content' }}</h3>
                        <p class="text-xs text-gray-500 mb-2">{{ class_basename($result->moderatable_type) }} &middot; {{ $result->created_at->diffForHumans() }}</p>

                        @php
                            $gridDisplayFlags = null;
                            if ($result->flags && is_array($result->flags) && count($result->flags) > 0) {
                                $gridDisplayFlags = current($result->flags);
                            } elseif ($result->flags && is_string($result->flags) && !in_array($result->flags, ['', '[]', 'null'])) {
                                $gridDisplayFlags = $result->flags;
                            }
                        @endphp
                        @if($gridDisplayFlags)
                            <p class="text-xs text-red-600 line-clamp-1 mb-3">{{ $gridDisplayFlags }}</p>
                        @endif

                        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                            <span class="text-xs text-gray-400">{{ $result->assignee?->first_name ?? 'Unassigned' }}</span>
                            <div class="flex items-center gap-0.5">
                                @if($canAssign)
                                    <button wire:click="openAssignModal({{ $result->id }})" class="p-1 text-gray-400 hover:text-pulse-orange-600 rounded" title="Assign">
                                        <x-icon name="user-plus" class="w-4 h-4" />
                                    </button>
                                @endif
                                <a href="{{ route('admin.moderation.edit', $result->id) }}" class="p-1 text-gray-400 hover:text-blue-600 rounded" title="Review">
                                    <x-icon name="pencil-square" class="w-4 h-4" />
                                </a>
                                <button wire:click="quickApprove({{ $result->id }})" class="p-1 text-gray-400 hover:text-green-600 rounded" title="Approve">
                                    <x-icon name="check-circle" class="w-4 h-4" />
                                </button>
                                <button wire:click="quickReject({{ $result->id }})" class="p-1 text-gray-400 hover:text-red-600 rounded" title="Reject">
                                    <x-icon name="x-circle" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full bg-white border border-gray-200 rounded-xl p-12 text-center">
                        <x-icon name="check-circle" class="w-12 h-12 mx-auto text-green-200" title="All caught up" />
                        <h3 class="mt-3 text-lg font-medium text-gray-900">All caught up!</h3>
                        <p class="mt-1 text-sm text-gray-500">No items need review right now.</p>
                    </div>
                @endforelse
            </div>
        @endif

        {{-- TABLE VIEW --}}
        @if(($viewMode ?? 'list') === 'table')
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            @if($canAssign)
                                <th scope="col" class="w-10 px-3 py-3">
                                    <input type="checkbox" class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500">
                                </th>
                            @endif
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Content</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($results as $result)
                            @php
                                $scorePercent = ($result->overall_score ?? 0) * 100;
                                $scoreColor = $scorePercent >= 85 ? 'green' : ($scorePercent >= 70 ? 'yellow' : 'red');
                                $statusColors = [
                                    'flagged' => 'yellow',
                                    'rejected' => 'red',
                                    'passed' => 'green',
                                    'approved_override' => 'blue',
                                ];
                                $statusColor = $statusColors[$result->status] ?? 'gray';
                            @endphp
                            <tr class="hover:bg-gray-50">
                                @if($canAssign)
                                    <td class="px-3 py-3">
                                        <input type="checkbox" wire:model.live="selectedItems" value="{{ $result->id }}" class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500">
                                    </td>
                                @endif
                                <td class="px-4 py-3">
                                    <div class="text-sm font-medium text-gray-900 truncate max-w-xs">{{ $result->moderatable?->title ?? 'Unknown Content' }}</div>
                                    @php
                                        $tableDisplayFlags = null;
                                        if ($result->flags && is_array($result->flags) && count($result->flags) > 0) {
                                            $tableDisplayFlags = current($result->flags);
                                        } elseif ($result->flags && is_string($result->flags) && !in_array($result->flags, ['', '[]', 'null'])) {
                                            $tableDisplayFlags = $result->flags;
                                        }
                                    @endphp
                                    @if($tableDisplayFlags)
                                        <div class="text-xs text-red-500 truncate max-w-xs">{{ $tableDisplayFlags }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-500">{{ class_basename($result->moderatable_type) }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-{{ $scoreColor }}-100 text-{{ $scoreColor }}-700">
                                        {{ number_format($scorePercent) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-{{ $statusColor }}-100 text-{{ $statusColor }}-700 capitalize">
                                        {{ str_replace('_', ' ', $result->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-500">{{ $result->assignee?->first_name ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-gray-500">{{ $result->created_at->format('M j') }}</span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        @if($canAssign)
                                            <button wire:click="openAssignModal({{ $result->id }})" class="p-1.5 text-gray-400 hover:text-pulse-orange-600 hover:bg-pulse-orange-50 rounded" title="Assign">
                                                <x-icon name="user-plus" class="w-4 h-4" />
                                            </button>
                                        @endif
                                        <a href="{{ route('admin.moderation.edit', $result->id) }}" class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded" title="Review">
                                            <x-icon name="pencil-square" class="w-4 h-4" />
                                        </a>
                                        <button wire:click="quickApprove({{ $result->id }})" class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded" title="Approve">
                                            <x-icon name="check-circle" class="w-4 h-4" />
                                        </button>
                                        <button wire:click="quickReject({{ $result->id }})" class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded" title="Reject">
                                            <x-icon name="x-circle" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $canAssign ? 8 : 7 }}" class="px-4 py-12 text-center">
                                    <x-icon name="check-circle" class="w-12 h-12 mx-auto text-green-200" title="All caught up" />
                                    <h3 class="mt-3 text-lg font-medium text-gray-900">All caught up!</h3>
                                    <p class="mt-1 text-sm text-gray-500">No items need review right now.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Pagination --}}
        @if($results->hasPages())
            <div class="mt-6">
                {{ $results->links() }}
            </div>
        @endif
    </div>

    {{-- Assignment Modal --}}
    @if($showAssignModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="fixed inset-0 bg-black/50" wire:click="closeAssignModal"></div>

                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-md">
                    <div class="p-5">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            {{ count($selectedItems) > 1 ? 'Assign ' . count($selectedItems) . ' Items' : 'Assign for Review' }}
                        </h3>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Assign To</label>
                                <select wire:model="assignToUserId" class="w-full text-sm rounded-lg border-gray-300 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                                    <option value="">Select moderator...</option>
                                    @foreach($eligibleModerators as $moderator)
                                        <option value="{{ $moderator->id }}">{{ $moderator->first_name }} {{ $moderator->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                                    <select wire:model="assignmentPriority" class="w-full text-sm rounded-lg border-gray-300 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                                        @foreach($assignmentPriorities as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                                    <input type="date" wire:model="assignmentDueAt" class="w-full text-sm rounded-lg border-gray-300 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                                </div>
                            </div>

                            <details class="text-sm">
                                <summary class="cursor-pointer text-gray-600 hover:text-gray-900">Add collaborators</summary>
                                <div class="mt-2 space-y-1 max-h-24 overflow-y-auto">
                                    @foreach($eligibleModerators as $moderator)
                                        @if($moderator->id != $assignToUserId)
                                            <label class="flex items-center gap-2">
                                                <input type="checkbox" wire:model="selectedCollaborators" value="{{ $moderator->id }}" class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500">
                                                <span class="text-gray-700">{{ $moderator->first_name }} {{ $moderator->last_name }}</span>
                                            </label>
                                        @endif
                                    @endforeach
                                </div>
                            </details>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                <textarea wire:model="assignmentNotes" rows="2" placeholder="Optional instructions..." class="w-full text-sm rounded-lg border-gray-300 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"></textarea>
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-5 pt-4 border-t border-gray-100">
                            <button wire:click="closeAssignModal" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                                Cancel
                            </button>
                            <button wire:click="saveAssignment" class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 hover:bg-pulse-orange-600 rounded-lg">
                                Assign
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
