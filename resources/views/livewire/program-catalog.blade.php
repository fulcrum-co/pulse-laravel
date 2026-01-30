<div class="min-h-screen bg-gray-50">
    <!-- Header Banner -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('resources.index') }}" class="text-gray-400 hover:text-gray-600">
                    <x-icon name="chevron-left" class="w-5 h-5" />
                </a>
                <div>
                    <nav class="flex items-center gap-2 text-sm text-gray-500 mb-1">
                        <a href="{{ route('resources.index') }}" class="hover:text-gray-700">Resources</a>
                        <span>/</span>
                        <span class="text-gray-900">Programs</span>
                    </nav>
                    <h1 class="text-2xl font-semibold text-gray-900">Program Catalog</h1>
                </div>
            </div>
            <button
                wire:click="$dispatch('openAddResourceModal')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
            >
                <x-icon name="plus" class="w-4 h-4" />
                Add Program
            </button>
        </div>
    </div>

    <div class="px-6 py-6">
        <!-- Filter Bar -->
        <div class="bg-white rounded-xl border border-gray-200 p-4 mb-6">
            <div class="flex flex-wrap items-center gap-4">
                <!-- Search -->
                <div class="flex-1 min-w-[250px]">
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <x-icon name="magnifying-glass" class="h-5 w-5 text-gray-400" />
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search programs..."
                            class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                    </div>
                </div>

                <!-- Filter Dropdowns -->
                <select
                    wire:model.live="filterType"
                    class="border-gray-300 rounded-lg text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                >
                    <option value="">All Types</option>
                    @foreach($this->programTypes as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>

                <select
                    wire:model.live="filterLocation"
                    class="border-gray-300 rounded-lg text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                >
                    <option value="">Any Location</option>
                    <option value="virtual">Virtual Only</option>
                    <option value="in_person">In-Person Only</option>
                    <option value="hybrid">Hybrid</option>
                </select>

                <select
                    wire:model.live="filterCost"
                    class="border-gray-300 rounded-lg text-sm focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                >
                    <option value="">Any Cost</option>
                    <option value="free">Free</option>
                    <option value="paid">Paid</option>
                </select>

                <label class="flex items-center gap-2 cursor-pointer">
                    <input
                        type="checkbox"
                        wire:model.live="showActiveOnly"
                        class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                    >
                    <span class="text-sm text-gray-700">Active only</span>
                </label>

                @if($this->hasActiveFiltersProperty)
                    <button
                        wire:click="clearFilters"
                        class="px-3 py-2 text-sm text-gray-600 hover:text-gray-900"
                    >
                        Clear all
                    </button>
                @endif
            </div>
        </div>

        <!-- Results Count -->
        <div class="mb-4">
            <p class="text-sm text-gray-600">
                Showing <span class="font-medium">{{ $programs->count() }}</span> of <span class="font-medium">{{ $programs->total() }}</span> programs
            </p>
        </div>

        @if($programs->count() > 0)
            <!-- Program Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($programs as $program)
                    @php
                        $typeColors = [
                            'therapy' => 'purple',
                            'tutoring' => 'blue',
                            'mentorship' => 'green',
                            'enrichment' => 'orange',
                            'intervention' => 'red',
                            'support_group' => 'teal',
                            'external_service' => 'gray',
                        ];
                        $typeIcons = [
                            'therapy' => 'heart',
                            'tutoring' => 'academic-cap',
                            'mentorship' => 'users',
                            'enrichment' => 'sparkles',
                            'intervention' => 'shield-check',
                            'support_group' => 'user-group',
                            'external_service' => 'building-office',
                        ];
                        $color = $typeColors[$program->program_type] ?? 'gray';
                        $icon = $typeIcons[$program->program_type] ?? 'building-office';
                    @endphp
                    <a
                        href="{{ route('resources.programs.show', $program) }}"
                        class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg hover:border-pulse-orange-300 transition-all"
                    >
                        <div class="p-5">
                            <div class="flex items-start gap-4">
                                <div class="w-12 h-12 rounded-xl bg-{{ $color }}-100 flex items-center justify-center flex-shrink-0">
                                    <x-icon name="{{ $icon }}" class="w-6 h-6 text-{{ $color }}-600" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3 class="text-base font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">
                                        {{ $program->name }}
                                    </h3>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-700 mt-1">
                                        {{ ucfirst(str_replace('_', ' ', $program->program_type)) }}
                                    </span>
                                </div>
                            </div>

                            @if($program->description)
                                <p class="mt-3 text-sm text-gray-600 line-clamp-2">
                                    {{ $program->description }}
                                </p>
                            @endif

                            <!-- Program Details -->
                            <div class="mt-4 flex flex-wrap gap-3 text-xs text-gray-500">
                                @if($program->duration_weeks)
                                    <span class="flex items-center gap-1">
                                        <x-icon name="calendar" class="w-3.5 h-3.5" />
                                        {{ $program->duration_weeks }} weeks
                                    </span>
                                @endif
                                @if($program->location_type)
                                    <span class="flex items-center gap-1">
                                        <x-icon name="{{ $program->location_type === 'virtual' ? 'globe-alt' : 'map-pin' }}" class="w-3.5 h-3.5" />
                                        {{ ucfirst($program->location_type) }}
                                    </span>
                                @endif
                                @if($program->cost_amount && $program->cost_amount > 0)
                                    <span class="flex items-center gap-1">
                                        <x-icon name="currency-dollar" class="w-3.5 h-3.5" />
                                        ${{ number_format($program->cost_amount) }}{{ $program->cost_frequency ? '/' . $program->cost_frequency : '' }}
                                    </span>
                                @else
                                    <span class="flex items-center gap-1 text-green-600">
                                        <x-icon name="check-circle" class="w-3.5 h-3.5" />
                                        Free
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Card Footer with Capacity -->
                        <div class="px-5 py-3 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                            @if($program->capacity)
                                @php
                                    $enrolled = $program->enrollments_count ?? 0;
                                    $spotsLeft = max(0, $program->capacity - $enrolled);
                                @endphp
                                <span class="text-xs {{ $spotsLeft > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    @if($spotsLeft > 0)
                                        {{ $spotsLeft }} spots available
                                    @else
                                        Full
                                    @endif
                                </span>
                            @else
                                <span class="text-xs text-gray-500">Open enrollment</span>
                            @endif
                            <span class="text-xs font-medium text-pulse-orange-600 group-hover:text-pulse-orange-700">
                                Learn more &rarr;
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                {{ $programs->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-xl border border-gray-200 text-center py-16">
                <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mx-auto mb-4">
                    <x-icon name="building-office" class="w-8 h-8 text-green-400" />
                </div>
                @if($this->hasActiveFiltersProperty)
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No programs match your filters</h3>
                    <p class="text-gray-500 mb-4">Try adjusting your search or filter criteria.</p>
                    <button
                        wire:click="clearFilters"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors"
                    >
                        Clear filters
                    </button>
                @else
                    <h3 class="text-lg font-medium text-gray-900 mb-1">No programs yet</h3>
                    <p class="text-gray-500 mb-4">Add intervention programs, support groups, and other services for students.</p>
                    <button
                        wire:click="$dispatch('openAddResourceModal')"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white text-sm font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                    >
                        <x-icon name="plus" class="w-4 h-4" />
                        Add Program
                    </button>
                @endif
            </div>
        @endif
    </div>
</div>
