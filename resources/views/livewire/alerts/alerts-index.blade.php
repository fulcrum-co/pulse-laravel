<div class="space-y-4">
    <!-- Tab Toggle -->
    <div class="flex items-center justify-between border-b border-gray-200">
        <nav class="flex gap-6">
            <button
                wire:click="setActiveTab('notifications')"
                class="relative py-3 text-sm font-medium border-b-2 -mb-px transition-colors {{ $activeTab === 'notifications' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
            >
                Notifications
                @if($notificationCount > 0)
                    <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ $activeTab === 'notifications' ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-gray-100 text-gray-600' }}">
                        {{ $notificationCount }}
                    </span>
                @endif
            </button>
            <button
                wire:click="setActiveTab('workflows')"
                class="py-3 text-sm font-medium border-b-2 -mb-px transition-colors {{ $activeTab === 'workflows' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700' }}"
            >
                Alert Workflows
            </button>
        </nav>

        <!-- Create Button (only on workflows tab) -->
        @if($activeTab === 'workflows')
            <a href="{{ route('alerts.create') }}" class="inline-flex items-center px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors">
                <x-icon name="plus" class="w-4 h-4 mr-1.5" />
                Create Alert
            </a>
        @endif
    </div>

    <!-- Search, Filters & View Toggle -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 flex-1">
            <!-- Search -->
            <div class="relative w-full sm:w-64">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ $activeTab === 'notifications' ? 'Search by workflow name...' : 'Search alerts...' }}"
                    class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                />
            </div>

            <!-- Tab-specific Filters -->
            @if($activeTab === 'notifications')
                <select
                    wire:model.live="notificationStatusFilter"
                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                >
                    <option value="">All Statuses</option>
                    @foreach($executionStatuses as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                @if($search || $notificationStatusFilter)
                    <button wire:click="clearFilters" class="text-sm text-gray-500 hover:text-gray-700">
                        Clear
                    </button>
                @endif
            @else
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
                    wire:model.live="triggerTypeFilter"
                    class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                >
                    <option value="">All Triggers</option>
                    @foreach($triggerTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                @if($search || $statusFilter || $triggerTypeFilter)
                    <button wire:click="clearFilters" class="text-sm text-gray-500 hover:text-gray-700">
                        Clear
                    </button>
                @endif
            @endif
        </div>

        <!-- View Toggle & Bulk Actions -->
        <div class="flex items-center gap-3">
            @if($activeTab === 'workflows' && count($selected) > 0)
                <div class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-1.5">
                    <span class="text-sm text-red-700">{{ count($selected) }} selected</span>
                    <button wire:click="deselectAll" class="text-xs text-red-600 hover:text-red-800 underline">Clear</button>
                    <button wire:click="confirmBulkDelete" class="ml-2 px-2 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700">
                        Delete Selected
                    </button>
                </div>
            @elseif($activeTab === 'workflows')
                <button wire:click="selectAll" class="text-xs text-gray-500 hover:text-gray-700">Select All</button>
            @endif

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
    </div>

    <!-- Tab Content -->
    @if($activeTab === 'notifications')
        @include('livewire.alerts.partials.notifications-content')
    @else
        @include('livewire.alerts.partials.workflows-content')
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
                        <h3 class="text-base font-medium text-gray-900">Delete Alert</h3>
                        <p class="mt-1 text-sm text-gray-500">Are you sure? This cannot be undone.</p>
                    </div>
                </div>
                <div class="mt-4 sm:flex sm:flex-row-reverse gap-2">
                    <button wire:click="deleteWorkflow" class="w-full sm:w-auto px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
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

    <!-- Bulk Delete Confirmation Modal -->
    @if($showBulkDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelBulkDelete"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100 sm:mx-0">
                        <x-icon name="exclamation-triangle" class="h-5 w-5 text-red-600" />
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-base font-medium text-gray-900">Delete {{ count($selected) }} Alert(s)</h3>
                        <p class="mt-1 text-sm text-gray-500">Are you sure you want to delete all selected alerts? This cannot be undone.</p>
                    </div>
                </div>
                <div class="mt-4 sm:flex sm:flex-row-reverse gap-2">
                    <button wire:click="deleteSelected" class="w-full sm:w-auto px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                        Delete All
                    </button>
                    <button wire:click="cancelBulkDelete" class="mt-2 sm:mt-0 w-full sm:w-auto px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
