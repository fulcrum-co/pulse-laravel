<div class="space-y-4">
    <!-- Search, Filters & View Toggle -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 flex-1">
            <div class="relative w-full sm:w-64">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search collections..."
                    class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                />
            </div>

            <select
                wire:model.live="statusFilter"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="">All Statuses</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select
                wire:model.live="typeFilter"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="">All Types</option>
                @foreach($collectionTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            @if($search || $statusFilter || $typeFilter)
            <button
                wire:click="clearFilters"
                class="text-sm text-gray-500 hover:text-gray-700"
            >
                Clear
            </button>
            @endif
        </div>

        <!-- View Toggle -->
        <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
            <button
                wire:click="setViewMode('grid')"
                class="p-1.5 rounded {{ $viewMode === 'grid' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                title="Grid view"
            >
                <x-icon name="squares-2x2" class="w-4 h-4" />
            </button>
            <button
                wire:click="setViewMode('list')"
                class="p-1.5 rounded {{ $viewMode === 'list' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                title="List view"
            >
                <x-icon name="list-bullet" class="w-4 h-4" />
            </button>
            <button
                wire:click="setViewMode('table')"
                class="p-1.5 rounded {{ $viewMode === 'table' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                title="Table view"
            >
                <x-icon name="table-cells" class="w-4 h-4" />
            </button>
        </div>
    </div>

    <!-- Empty State -->
    @if($collections->isEmpty())
        <x-card>
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gradient-to-br from-pulse-orange-100 to-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <x-icon name="arrow-path" class="w-8 h-8 text-pulse-orange-500" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">Create your first data collection</h3>
                <p class="text-gray-500 mb-4 max-w-sm mx-auto text-sm">
                    Set up recurring data collection to systematically gather progress monitoring data, check-ins, and insights from students, staff, or parents.
                </p>
                <a href="{{ route('collect.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                    <x-icon name="plus" class="w-4 h-4 mr-1" />
                    Create Data Collection
                </a>
            </div>
        </x-card>

    <!-- Grid View -->
    @elseif($viewMode === 'grid')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($collections as $collection)
                @php
                    $statusColor = match($collection->status) {
                        'active' => 'green',
                        'paused' => 'yellow',
                        'draft' => 'gray',
                        'archived' => 'red',
                        default => 'gray',
                    };
                    $typeColor = match($collection->collection_type) {
                        'recurring' => 'blue',
                        'one_time' => 'purple',
                        'event_triggered' => 'orange',
                        default => 'gray',
                    };
                    $formatIcon = match($collection->format_mode) {
                        'conversational' => 'chat-bubble-left-right',
                        'form' => 'clipboard-document-list',
                        'grid' => 'table-cells',
                        default => 'document',
                    };
                    $activeSchedule = $collection->schedules->first();
                @endphp
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <h3 class="font-medium text-gray-900 text-sm truncate flex-1">{{ $collection->title }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 flex-shrink-0">
                                {{ ucfirst($collection->status) }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 text-xs mb-3">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $typeColor }}-100 text-{{ $typeColor }}-700">
                                {{ str_replace('_', ' ', ucfirst($collection->collection_type)) }}
                            </span>
                            <span class="inline-flex items-center text-gray-500">
                                <x-icon name="{{ $formatIcon }}" class="w-3 h-3 mr-0.5" />
                                {{ ucfirst($collection->format_mode) }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-xs mb-3">
                            <div>
                                <span class="font-semibold text-gray-900">{{ number_format($collection->sessions_count ?? 0) }}</span>
                                <span class="text-gray-500 ml-1">sessions</span>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-900">{{ number_format($collection->entries_count ?? 0) }}</span>
                                <span class="text-gray-500 ml-1">entries</span>
                            </div>
                        </div>

                        @if($activeSchedule && $activeSchedule->next_scheduled_at)
                            <div class="flex items-center text-xs text-gray-500">
                                <x-icon name="clock" class="w-3 h-3 mr-1" />
                                Next: {{ $activeSchedule->next_scheduled_at->format('M j, g:i A') }}
                            </div>
                        @elseif($collection->data_source === 'survey' && $collection->survey_id)
                            <div class="flex items-center text-xs text-indigo-600">
                                <x-icon name="link" class="w-3 h-3 mr-1" />
                                Linked to survey
                            </div>
                        @else
                            <div class="text-xs text-gray-400">
                                Updated {{ $collection->updated_at->diffForHumans(null, true) }} ago
                            </div>
                        @endif
                    </div>

                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-1">
                            <div class="relative group">
                                <button wire:click="toggleStatus({{ $collection->id }})" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                    <x-icon name="{{ $collection->status === 'active' ? 'pause' : 'play' }}" class="w-3.5 h-3.5" />
                                </button>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">{{ $collection->status === 'active' ? 'Pause' : 'Activate' }}</span>
                            </div>
                            <div class="relative group">
                                <button wire:click="duplicate({{ $collection->id }})" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                    <x-icon name="document-duplicate" class="w-3.5 h-3.5" />
                                </button>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Duplicate</span>
                            </div>
                            <div class="relative group">
                                <button wire:click="confirmDelete({{ $collection->id }})" class="p-1.5 text-gray-400 hover:text-red-500 rounded">
                                    <x-icon name="trash" class="w-3.5 h-3.5" />
                                </button>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Delete</span>
                            </div>
                        </div>
                        <a href="{{ route('collect.show', $collection) }}" class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                            View
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

    <!-- List View -->
    @elseif($viewMode === 'list')
        <div class="space-y-2">
            @foreach($collections as $collection)
                @php
                    $statusColor = match($collection->status) {
                        'active' => 'green',
                        'paused' => 'yellow',
                        'draft' => 'gray',
                        'archived' => 'red',
                        default => 'gray',
                    };
                    $typeColor = match($collection->collection_type) {
                        'recurring' => 'blue',
                        'one_time' => 'purple',
                        'event_triggered' => 'orange',
                        default => 'gray',
                    };
                    $activeSchedule = $collection->schedules->first();
                @endphp
                <div class="bg-white rounded-lg border border-gray-200 p-3 hover:shadow-sm transition-shadow flex items-center gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="font-medium text-gray-900 text-sm truncate">{{ $collection->title }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                {{ ucfirst($collection->status) }}
                            </span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $typeColor }}-100 text-{{ $typeColor }}-700">
                                {{ str_replace('_', ' ', ucfirst($collection->collection_type)) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                            <span>{{ $collection->sessions_count ?? 0 }} sessions</span>
                            <span>{{ $collection->entries_count ?? 0 }} entries</span>
                            @if($activeSchedule && $activeSchedule->next_scheduled_at)
                                <span class="flex items-center">
                                    <x-icon name="clock" class="w-3 h-3 mr-1" />
                                    Next: {{ $activeSchedule->next_scheduled_at->format('M j') }}
                                </span>
                            @else
                                <span>Updated {{ $collection->updated_at->diffForHumans() }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-1">
                        <div class="relative group">
                            <button wire:click="toggleStatus({{ $collection->id }})" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="{{ $collection->status === 'active' ? 'pause' : 'play' }}" class="w-4 h-4" />
                            </button>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">{{ $collection->status === 'active' ? 'Pause' : 'Activate' }}</span>
                        </div>
                        <div class="relative group">
                            <button wire:click="duplicate({{ $collection->id }})" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="document-duplicate" class="w-4 h-4" />
                            </button>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Duplicate</span>
                        </div>
                        <div class="relative group">
                            <button wire:click="confirmDelete({{ $collection->id }})" class="p-1.5 text-gray-400 hover:text-red-500 rounded">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Delete</span>
                        </div>
                        <a href="{{ route('collect.show', $collection) }}" class="ml-2 px-3 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
                            View
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

    <!-- Table View -->
    @else
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Collection</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Format</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sessions</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Entries</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Next Run</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($collections as $collection)
                        @php
                            $statusColor = match($collection->status) {
                                'active' => 'green',
                                'paused' => 'yellow',
                                'draft' => 'gray',
                                'archived' => 'red',
                                default => 'gray',
                            };
                            $typeColor = match($collection->collection_type) {
                                'recurring' => 'blue',
                                'one_time' => 'purple',
                                'event_triggered' => 'orange',
                                default => 'gray',
                            };
                            $activeSchedule = $collection->schedules->first();
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900">{{ $collection->title }}</span>
                                    @if($collection->data_source === 'survey')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">Survey</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $typeColor }}-100 text-{{ $typeColor }}-700">
                                    {{ str_replace('_', ' ', ucfirst($collection->collection_type)) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                    {{ ucfirst($collection->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600">
                                {{ ucfirst($collection->format_mode) }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($collection->sessions_count ?? 0) }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($collection->entries_count ?? 0) }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                @if($activeSchedule && $activeSchedule->next_scheduled_at)
                                    {{ $activeSchedule->next_scheduled_at->format('M d, g:i A') }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <div class="relative group">
                                        <button wire:click="toggleStatus({{ $collection->id }})" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                            <x-icon name="{{ $collection->status === 'active' ? 'pause' : 'play' }}" class="w-4 h-4" />
                                        </button>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">{{ $collection->status === 'active' ? 'Pause' : 'Activate' }}</span>
                                    </div>
                                    <div class="relative group">
                                        <button wire:click="duplicate({{ $collection->id }})" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                            <x-icon name="document-duplicate" class="w-4 h-4" />
                                        </button>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">Duplicate</span>
                                    </div>
                                    <div class="relative group">
                                        <button wire:click="confirmDelete({{ $collection->id }})" class="p-1 text-gray-400 hover:text-red-500 rounded">
                                            <x-icon name="trash" class="w-4 h-4" />
                                        </button>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">Delete</span>
                                    </div>
                                    <a href="{{ route('collect.show', $collection) }}" class="ml-1 px-2 py-1 text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Pagination -->
    @if($collections->hasPages())
        <div class="mt-4">
            {{ $collections->links() }}
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelDelete"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100 sm:mx-0">
                        <x-icon name="exclamation-triangle" class="h-5 w-5 text-red-600" />
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-base font-medium text-gray-900">Delete Collection</h3>
                        <p class="mt-1 text-sm text-gray-500">Are you sure? This will also delete all sessions and entries. This cannot be undone.</p>
                    </div>
                </div>
                <div class="mt-4 sm:flex sm:flex-row-reverse gap-2">
                    <button wire:click="deleteCollection" class="w-full sm:w-auto px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Delete
                    </button>
                    <button wire:click="cancelDelete" class="mt-2 sm:mt-0 w-full sm:w-auto px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
