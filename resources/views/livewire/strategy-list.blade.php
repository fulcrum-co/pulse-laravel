<div>
    {{-- Type Tabs --}}
    <div class="mb-6 flex flex-wrap gap-2">
        <button wire:click="setTypeFilter('all')"
            class="px-4 py-2 rounded-lg text-sm font-medium {{ $typeFilter === 'all' ? 'bg-pulse-orange-500 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
            All ({{ $counts['all'] }})
        </button>
        <button wire:click="setTypeFilter('organizational')"
            class="px-4 py-2 rounded-lg text-sm font-medium {{ $typeFilter === 'organizational' ? 'bg-pulse-orange-500 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
            Organizational ({{ $counts['organizational'] }})
        </button>
        <button wire:click="setTypeFilter('teacher')"
            class="px-4 py-2 rounded-lg text-sm font-medium {{ $typeFilter === 'teacher' ? 'bg-pulse-orange-500 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
            Teacher ({{ $counts['teacher'] }})
        </button>
        <button wire:click="setTypeFilter('student')"
            class="px-4 py-2 rounded-lg text-sm font-medium {{ $typeFilter === 'student' ? 'bg-pulse-orange-500 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
            Student ({{ $counts['student'] }})
        </button>
        <button wire:click="setTypeFilter('department')"
            class="px-4 py-2 rounded-lg text-sm font-medium {{ $typeFilter === 'department' ? 'bg-pulse-orange-500 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
            Department ({{ $counts['department'] }})
        </button>
        <button wire:click="setTypeFilter('grade')"
            class="px-4 py-2 rounded-lg text-sm font-medium {{ $typeFilter === 'grade' ? 'bg-pulse-orange-500 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
            Grade ({{ $counts['grade'] }})
        </button>
    </div>

    {{-- Search, Filters & View Toggle --}}
    <div class="mb-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 flex-1">
            <div class="relative w-full sm:w-64">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    placeholder="Search strategies...">
            </div>
            <select wire:model.live="statusFilter"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                <option value="">All Statuses</option>
                <option value="draft">Draft</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
                <option value="archived">Archived</option>
            </select>
            @if($search || $statusFilter || $typeFilter !== 'all')
                <button wire:click="clearFilters" class="text-sm text-gray-500 hover:text-gray-700">
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

    {{-- Empty State --}}
    @if($strategies->isEmpty())
        <x-card>
            <div class="text-center py-12">
                <x-icon name="clipboard-list" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <p class="text-gray-500">No strategies found.</p>
                <p class="text-gray-400 text-sm mt-1">Create your first strategy to get started.</p>
                <a href="{{ route('strategies.create') }}" class="inline-flex items-center mt-4 px-4 py-2 bg-pulse-orange-500 text-white rounded-lg font-medium hover:bg-pulse-orange-600 transition-colors">
                    <x-icon name="plus" class="w-4 h-4 mr-2" />
                    Create Strategy
                </a>
            </div>
        </x-card>

    {{-- Grid View --}}
    @elseif($viewMode === 'grid')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($strategies as $strategy)
                <a href="{{ route('strategies.show', $strategy) }}" class="block">
                    <x-card class="h-full hover:shadow-md transition-shadow">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $strategy->title }}</h3>
                                <p class="text-sm text-gray-500 capitalize">{{ str_replace('_', ' ', $strategy->plan_type) }} Plan</p>
                            </div>
                            <x-badge :color="match($strategy->status) {
                                'active' => 'green',
                                'draft' => 'gray',
                                'completed' => 'blue',
                                'archived' => 'gray',
                                default => 'gray'
                            }">
                                {{ ucfirst($strategy->status) }}
                            </x-badge>
                        </div>

                        @if($strategy->description)
                            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ $strategy->description }}</p>
                        @endif

                        <div class="flex items-center gap-4 text-sm text-gray-500 mb-3">
                            <span>
                                <x-icon name="calendar" class="w-4 h-4 inline mr-1" />
                                {{ $strategy->start_date->format('M j, Y') }} - {{ $strategy->end_date->format('M j, Y') }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between text-sm">
                            <span class="text-gray-500">
                                {{ $strategy->focusAreas->count() }} focus area{{ $strategy->focusAreas->count() !== 1 ? 's' : '' }}
                            </span>

                            @if($strategy->collaborators->count() > 0)
                                <div class="flex -space-x-2">
                                    @foreach($strategy->collaborators->take(3) as $collab)
                                        <div class="w-6 h-6 rounded-full bg-pulse-orange-100 border-2 border-white flex items-center justify-center text-xs font-medium text-pulse-orange-600">
                                            {{ substr($collab->user->first_name ?? 'U', 0, 1) }}
                                        </div>
                                    @endforeach
                                    @if($strategy->collaborators->count() > 3)
                                        <div class="w-6 h-6 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center text-xs font-medium text-gray-600">
                                            +{{ $strategy->collaborators->count() - 3 }}
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </x-card>
                </a>
            @endforeach
        </div>

    {{-- List View --}}
    @elseif($viewMode === 'list')
        <div class="space-y-2">
            @foreach($strategies as $strategy)
                <a href="{{ route('strategies.show', $strategy) }}" class="block bg-white rounded-lg border border-gray-200 p-4 hover:shadow-sm transition-shadow">
                    <div class="flex items-center gap-4">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="font-medium text-gray-900 text-sm truncate">{{ $strategy->title }}</h3>
                                <x-badge :color="match($strategy->status) {
                                    'active' => 'green',
                                    'draft' => 'gray',
                                    'completed' => 'blue',
                                    'archived' => 'gray',
                                    default => 'gray'
                                }">
                                    {{ ucfirst($strategy->status) }}
                                </x-badge>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 capitalize">
                                    {{ str_replace('_', ' ', $strategy->plan_type) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                                <span>{{ $strategy->focusAreas->count() }} focus areas</span>
                                <span>{{ $strategy->start_date->format('M j, Y') }} - {{ $strategy->end_date->format('M j, Y') }}</span>
                                @if($strategy->collaborators->count() > 0)
                                    <span>{{ $strategy->collaborators->count() }} collaborators</span>
                                @endif
                            </div>
                        </div>
                        @if($strategy->collaborators->count() > 0)
                            <div class="flex -space-x-2">
                                @foreach($strategy->collaborators->take(3) as $collab)
                                    <div class="w-6 h-6 rounded-full bg-pulse-orange-100 border-2 border-white flex items-center justify-center text-xs font-medium text-pulse-orange-600">
                                        {{ substr($collab->user->first_name ?? 'U', 0, 1) }}
                                    </div>
                                @endforeach
                                @if($strategy->collaborators->count() > 3)
                                    <div class="w-6 h-6 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center text-xs font-medium text-gray-600">
                                        +{{ $strategy->collaborators->count() - 3 }}
                                    </div>
                                @endif
                            </div>
                        @endif
                        <span class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                            View
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

    {{-- Table View --}}
    @else
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Strategy</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Range</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Focus Areas</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Collaborators</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($strategies as $strategy)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $strategy->title }}</div>
                                @if($strategy->description)
                                    <div class="text-xs text-gray-500 truncate max-w-xs">{{ Str::limit($strategy->description, 50) }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 capitalize">
                                    {{ str_replace('_', ' ', $strategy->plan_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                <x-badge :color="match($strategy->status) {
                                    'active' => 'green',
                                    'draft' => 'gray',
                                    'completed' => 'blue',
                                    'archived' => 'gray',
                                    default => 'gray'
                                }">
                                    {{ ucfirst($strategy->status) }}
                                </x-badge>
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                {{ $strategy->start_date->format('M j, Y') }} - {{ $strategy->end_date->format('M j, Y') }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                                {{ $strategy->focusAreas->count() }}
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($strategy->collaborators->count() > 0)
                                    <div class="flex -space-x-2">
                                        @foreach($strategy->collaborators->take(3) as $collab)
                                            <div class="w-6 h-6 rounded-full bg-pulse-orange-100 border-2 border-white flex items-center justify-center text-xs font-medium text-pulse-orange-600">
                                                {{ substr($collab->user->first_name ?? 'U', 0, 1) }}
                                            </div>
                                        @endforeach
                                        @if($strategy->collaborators->count() > 3)
                                            <div class="w-6 h-6 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center text-xs font-medium text-gray-600">
                                                +{{ $strategy->collaborators->count() - 3 }}
                                            </div>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 whitespace-nowrap text-right">
                                <a href="{{ route('strategies.show', $strategy) }}" class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Pagination --}}
    @if($strategies->hasPages())
        <div class="mt-6">
            {{ $strategies->links() }}
        </div>
    @endif
</div>
