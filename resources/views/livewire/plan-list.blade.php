<div>
    {{-- Search & Filters --}}
    <div class="mb-6 flex items-center gap-3">
        <div class="relative flex-1 max-w-xs">
            <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.live.debounce.300ms="search"
                class="w-full pl-9 pr-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                placeholder="Search plans...">
        </div>

        <select wire:model.live="typeFilter"
            class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
            <option value="all">All Types</option>
            <option value="organizational">Organizational</option>
            <option value="teacher">Teacher</option>
            <option value="student">Student</option>
            <option value="department">Department</option>
            <option value="improvement">PIP</option>
            <option value="growth">Growth</option>
            <option value="strategic">OKR</option>
            <option value="action">Action</option>
        </select>

        <select wire:model.live="statusFilter"
            class="px-3 py-2 text-sm border border-gray-200 rounded-lg focus:ring-1 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="draft">Draft</option>
            <option value="completed">Completed</option>
        </select>

        @if($search || $statusFilter || $typeFilter !== 'all')
            <button wire:click="clearFilters" class="text-sm text-gray-400 hover:text-gray-600">
                Clear
            </button>
        @endif

        <div class="flex-1"></div>

        {{-- View Toggle --}}
        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
            <button wire:click="setViewMode('grid')"
                class="p-2 {{ $viewMode === 'grid' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600' }}">
                <x-icon name="squares-2x2" class="w-4 h-4" />
            </button>
            <button wire:click="setViewMode('list')"
                class="p-2 {{ $viewMode === 'list' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600' }}">
                <x-icon name="list-bullet" class="w-4 h-4" />
            </button>
        </div>
    </div>

    {{-- Empty State --}}
    @if($plans->isEmpty())
        <div class="text-center py-16">
            <x-icon name="clipboard-document-list" class="w-12 h-12 text-gray-300 mx-auto mb-4" />
            <p class="text-gray-500 mb-1">No plans found</p>
            <p class="text-sm text-gray-400 mb-4">Create your first plan to get started</p>
            <a href="{{ route('plans.create') }}" class="inline-flex items-center px-4 py-2 bg-pulse-orange-500 text-white rounded-lg text-sm font-medium hover:bg-pulse-orange-600">
                <x-icon name="plus" class="w-4 h-4 mr-1.5" />
                New Plan
            </a>
        </div>

    {{-- Grid View --}}
    @elseif($viewMode === 'grid')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($plans as $plan)
                <a href="{{ route('plans.show', $plan) }}" class="block bg-white rounded-lg border border-gray-200 p-5 hover:border-pulse-orange-300 hover:shadow-sm transition-all group">
                    <div class="flex items-start justify-between mb-3">
                        <h3 class="font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">{{ $plan->title }}</h3>
                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ match($plan->status) {
                            'active' => 'bg-green-100 text-green-700',
                            'draft' => 'bg-gray-100 text-gray-600',
                            'completed' => 'bg-blue-100 text-blue-700',
                            default => 'bg-gray-100 text-gray-600'
                        } }}">{{ ucfirst($plan->status) }}</span>
                    </div>

                    @if($plan->description)
                        <p class="text-sm text-gray-500 mb-3 line-clamp-2">{{ $plan->description }}</p>
                    @endif

                    <div class="flex items-center justify-between text-xs text-gray-400">
                        <span>{{ $plan->start_date->format('M j') }} - {{ $plan->end_date->format('M j, Y') }}</span>
                        <span class="capitalize">{{ str_replace('_', ' ', $plan->plan_type) }}</span>
                    </div>

                    @if($plan->isOkrStyle() && $plan->goals->count() > 0)
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-gray-500">{{ $plan->goals->count() }} goals</span>
                                <span class="font-medium text-gray-700">{{ number_format($plan->progress, 0) }}%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-pulse-orange-500 rounded-full" style="width: {{ $plan->progress }}%"></div>
                            </div>
                        </div>
                    @elseif($plan->focusAreas->count() > 0)
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <span class="text-xs text-gray-500">{{ $plan->focusAreas->count() }} focus areas</span>
                        </div>
                    @endif
                </a>
            @endforeach
        </div>

    {{-- List View --}}
    @else
        <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-100">
            @foreach($plans as $plan)
                <a href="{{ route('plans.show', $plan) }}" class="flex items-center p-4 hover:bg-gray-50 transition-colors group">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-medium text-gray-900 group-hover:text-pulse-orange-600 transition-colors truncate">{{ $plan->title }}</h3>
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full shrink-0 {{ match($plan->status) {
                                'active' => 'bg-green-100 text-green-700',
                                'draft' => 'bg-gray-100 text-gray-600',
                                'completed' => 'bg-blue-100 text-blue-700',
                                default => 'bg-gray-100 text-gray-600'
                            } }}">{{ ucfirst($plan->status) }}</span>
                        </div>
                        <div class="flex items-center gap-4 text-xs text-gray-400">
                            <span class="capitalize">{{ str_replace('_', ' ', $plan->plan_type) }}</span>
                            <span>{{ $plan->start_date->format('M j') }} - {{ $plan->end_date->format('M j, Y') }}</span>
                            @if($plan->isOkrStyle())
                                <span>{{ $plan->goals->count() }} goals</span>
                            @else
                                <span>{{ $plan->focusAreas->count() }} focus areas</span>
                            @endif
                        </div>
                    </div>

                    @if($plan->isOkrStyle())
                        <div class="w-24 mr-4">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-gray-400">Progress</span>
                                <span class="font-medium text-gray-700">{{ number_format($plan->progress, 0) }}%</span>
                            </div>
                            <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-pulse-orange-500 rounded-full" style="width: {{ $plan->progress }}%"></div>
                            </div>
                        </div>
                    @endif

                    <x-icon name="chevron-right" class="w-5 h-5 text-gray-300 group-hover:text-gray-400" />
                </a>
            @endforeach
        </div>
    @endif

    {{-- Pagination --}}
    @if($plans->hasPages())
        <div class="mt-6">
            {{ $plans->links() }}
        </div>
    @endif
</div>
