<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Alerts</h1>
            <p class="text-gray-500 mt-1">Create automated workflows to notify staff when conditions are met</p>
        </div>
        <a href="{{ route('alerts.create') }}">
            <x-button variant="primary">
                <x-icon name="plus" class="w-4 h-4 mr-2" />
                Create Alert
            </x-button>
        </a>
    </div>

    <!-- Search & Filters -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <div class="flex-1 w-full sm:w-auto">
            <div class="relative">
                <x-icon name="search" class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search alerts..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                />
            </div>
        </div>

        <div class="flex items-center gap-3">
            <select
                wire:model.live="statusFilter"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="">All Statuses</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select
                wire:model.live="triggerTypeFilter"
                class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="">All Trigger Types</option>
                @foreach($triggerTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            @if($search || $statusFilter || $triggerTypeFilter)
            <button
                wire:click="clearFilters"
                class="text-sm text-gray-500 hover:text-gray-700"
            >
                Clear filters
            </button>
            @endif
        </div>
    </div>

    <!-- Workflows Grid -->
    @if($workflows->isEmpty())
        <x-card>
            <div class="text-center py-16">
                <div class="w-20 h-20 bg-gradient-to-br from-pulse-orange-100 to-pulse-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <x-icon name="bell-alert" class="w-10 h-10 text-pulse-orange-500" />
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">Create your first alert</h3>
                <p class="text-gray-500 mb-6 max-w-md mx-auto">
                    Set up automated alerts to notify staff when student metrics change or specific conditions are met.
                </p>
                <a href="{{ route('alerts.create') }}">
                    <x-button variant="primary" size="lg">
                        <x-icon name="plus" class="w-5 h-5 mr-2" />
                        Create Alert
                    </x-button>
                </a>
            </div>
        </x-card>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($workflows as $workflow)
                <div class="bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow group">
                    <!-- Card Header -->
                    <div class="p-5 border-b border-gray-100">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-gray-900 truncate">{{ $workflow->name }}</h3>
                                @if($workflow->description)
                                    <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $workflow->description }}</p>
                                @endif
                            </div>

                            <!-- Status Badge -->
                            @php
                                $statusColor = match($workflow->status) {
                                    'active' => 'green',
                                    'paused' => 'yellow',
                                    'draft' => 'gray',
                                    'archived' => 'red',
                                    default => 'gray',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 flex-shrink-0">
                                {{ ucfirst($workflow->status) }}
                            </span>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-5 space-y-4">
                        <!-- Trigger Type -->
                        <div class="flex items-center gap-2 text-sm">
                            <x-icon name="bolt" class="w-4 h-4 text-gray-400" />
                            <span class="text-gray-600">{{ $triggerTypes[$workflow->trigger_type] ?? 'Unknown' }}</span>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <div class="text-2xl font-bold text-gray-900">{{ number_format($workflow->execution_count) }}</div>
                                <div class="text-xs text-gray-500">Executions</div>
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">
                                    {{ $workflow->last_triggered_at ? $workflow->last_triggered_at->diffForHumans() : 'Never' }}
                                </div>
                                <div class="text-xs text-gray-500">Last Triggered</div>
                            </div>
                        </div>

                        <!-- Mode Badge -->
                        <div class="flex items-center gap-2">
                            @if($workflow->mode === 'advanced')
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">
                                    <x-icon name="squares-plus" class="w-3 h-3 mr-1" />
                                    Visual Canvas
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-700">
                                    <x-icon name="list-bullet" class="w-3 h-3 mr-1" />
                                    Simple Wizard
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Card Footer -->
                    <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <!-- Toggle Active/Pause -->
                            <button
                                wire:click="toggleStatus('{{ $workflow->_id }}')"
                                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                                title="{{ $workflow->status === 'active' ? 'Pause' : 'Activate' }}"
                            >
                                @if($workflow->status === 'active')
                                    <x-icon name="pause" class="w-4 h-4" />
                                @else
                                    <x-icon name="play" class="w-4 h-4" />
                                @endif
                            </button>

                            <!-- Test Trigger -->
                            <button
                                wire:click="testTrigger('{{ $workflow->_id }}')"
                                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                                title="Test Trigger"
                            >
                                <x-icon name="beaker" class="w-4 h-4" />
                            </button>

                            <!-- Duplicate -->
                            <button
                                wire:click="duplicate('{{ $workflow->_id }}')"
                                class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                                title="Duplicate"
                            >
                                <x-icon name="document-duplicate" class="w-4 h-4" />
                            </button>

                            <!-- Delete -->
                            <button
                                wire:click="confirmDelete('{{ $workflow->_id }}')"
                                class="p-2 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                title="Delete"
                            >
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        </div>

                        <div class="flex items-center gap-2">
                            <a
                                href="{{ route('alerts.history', $workflow) }}"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                History
                            </a>
                            <a
                                href="{{ route('alerts.edit', $workflow) }}"
                                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors"
                            >
                                Edit
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $workflows->links() }}
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelDelete"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>

            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                        <x-icon name="exclamation-triangle" class="h-6 w-6 text-red-600" />
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">Delete Alert</h3>
                        <div class="mt-2">
                            <p class="text-sm text-gray-500">
                                Are you sure you want to delete this alert? This action cannot be undone and all execution history will be lost.
                            </p>
                        </div>
                    </div>
                </div>
                <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse gap-3">
                    <button
                        wire:click="deleteWorkflow"
                        type="button"
                        class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm"
                    >
                        Delete
                    </button>
                    <button
                        wire:click="cancelDelete"
                        type="button"
                        class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pulse-orange-500 sm:mt-0 sm:w-auto sm:text-sm"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
