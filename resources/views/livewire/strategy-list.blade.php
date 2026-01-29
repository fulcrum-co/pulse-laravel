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

    {{-- Search and Filters --}}
    <div class="mb-6 flex gap-4">
        <div class="flex-1">
            <input type="text" wire:model.live.debounce.300ms="search"
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                placeholder="Search strategies...">
        </div>
        <select wire:model.live="statusFilter"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="active">Active</option>
            <option value="completed">Completed</option>
            <option value="archived">Archived</option>
        </select>
        @if($search || $statusFilter || $typeFilter !== 'all')
            <button wire:click="clearFilters" class="px-4 py-2 text-gray-600 hover:text-gray-900">
                Clear
            </button>
        @endif
    </div>

    {{-- Strategy Grid --}}
    @if($strategies->count() > 0)
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

        {{-- Pagination --}}
        <div class="mt-6">
            {{ $strategies->links() }}
        </div>
    @else
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
    @endif
</div>
