<div>
    @php($terminology = app(\App\Services\TerminologyService::class))
    {{-- Search & Filters --}}
    <div class="mb-4 flex items-center gap-2" data-help="plan-filters">
        <div class="relative flex-1 max-w-xs" data-help="search-plans">
            <x-icon name="search" class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
            <input type="text" wire:model.live.debounce.300ms="search"
                class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded focus:ring-1 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                placeholder="@term('search_plans_placeholder')">
        </div>

        <select wire:model.live="typeFilter"
            class="px-2 py-1.5 text-xs border border-gray-200 rounded focus:ring-1 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
            <option value="all">@term('all_types_label')</option>
            <option value="organizational">@term('plan_type_organizational_label')</option>
            <option value="instructor">@term('plan_type_instructor_label')</option>
            <option value="participant">@term('plan_type_participant_label')</option>
            <option value="department">@term('plan_type_department_label')</option>
            <option value="improvement">@term('plan_type_improvement_label')</option>
            <option value="growth">@term('plan_type_growth_label')</option>
            <option value="strategic">@term('plan_type_strategic_label')</option>
            <option value="action">@term('plan_type_action_label')</option>
        </select>

        <select wire:model.live="statusFilter"
            class="px-2 py-1.5 text-xs border border-gray-200 rounded focus:ring-1 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
            <option value="">@term('all_status_label')</option>
            <option value="active">@term('active_label')</option>
            <option value="draft">@term('draft_label')</option>
            <option value="completed">@term('completed_label')</option>
        </select>

        @if($search || $statusFilter || $typeFilter !== 'all')
            <button wire:click="clearFilters" class="text-xs text-gray-400 hover:text-gray-600">
                @term('clear_action')
            </button>
        @endif

        <div class="flex-1"></div>

        {{-- View Toggle --}}
        <div class="flex items-center border border-gray-200 rounded overflow-hidden">
            <button wire:click="setViewMode('grid')"
                class="p-1.5 {{ $viewMode === 'grid' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600' }}">
                <x-icon name="squares-2x2" class="w-3.5 h-3.5" />
            </button>
            <button wire:click="setViewMode('list')"
                class="p-1.5 {{ $viewMode === 'list' ? 'bg-gray-100 text-gray-900' : 'text-gray-400 hover:text-gray-600' }}">
                <x-icon name="list-bullet" class="w-3.5 h-3.5" />
            </button>
        </div>
    </div>

    {{-- Empty State --}}
    @if($plans->isEmpty())
        <div class="text-center py-12">
            <x-icon name="clipboard-document-list" class="w-10 h-10 text-gray-300 mx-auto mb-3" />
            <p class="text-sm text-gray-500 mb-1">@term('no_plans_found_label')</p>
            <p class="text-xs text-gray-400 mb-3">@term('create_first_plan_help_label')</p>
            <a href="{{ route('plans.create') }}" class="inline-flex items-center px-3 py-1.5 bg-pulse-orange-500 text-white rounded text-xs font-medium hover:bg-pulse-orange-600">
                <x-icon name="plus" class="w-3.5 h-3.5 mr-1" />
                @term('new_plan_label')
            </a>
        </div>

    {{-- Grid View --}}
    @elseif($viewMode === 'grid')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3" data-help="plan-list">
            @foreach($plans as $plan)
                <a href="{{ route('plans.show', $plan) }}" class="block bg-white rounded border border-gray-200 p-3 hover:border-pulse-orange-300 hover:shadow-sm transition-all group">
                    <div class="flex items-start justify-between mb-2">
                        <h3 class="text-sm font-medium text-gray-900 group-hover:text-pulse-orange-600 transition-colors truncate pr-2">{{ $plan->title }}</h3>
                        <span class="px-1.5 py-0.5 text-[10px] font-medium rounded shrink-0 {{ match($plan->status) {
                            'active' => 'bg-green-100 text-green-700',
                            'draft' => 'bg-gray-100 text-gray-600',
                            'completed' => 'bg-blue-100 text-blue-700',
                            default => 'bg-gray-100 text-gray-600'
                        } }}">{{ $terminology->get($plan->status.'_label') }}</span>
                    </div>

                    @if($plan->description)
                        <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ $plan->description }}</p>
                    @endif

                    <div class="flex items-center justify-between text-[10px] text-gray-400">
                        <span>{{ $plan->start_date->format('M j') }} - {{ $plan->end_date->format('M j, Y') }}</span>
                        <span class="capitalize">{{ $terminology->get('plan_type_'.$plan->plan_type.'_label') }}</span>
                    </div>

                    @if($plan->isOkrStyle() && $plan->goals->count() > 0)
                        <div class="mt-2 pt-2 border-t border-gray-100">
                            <div class="flex items-center justify-between text-[10px] mb-1">
                                <span class="text-gray-500">{{ $plan->goals->count() }} @term('goals_label')</span>
                                <span class="font-medium text-gray-600">{{ number_format($plan->progress, 0) }}%</span>
                            </div>
                            <div class="h-1 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-pulse-orange-500 rounded-full" style="width: {{ $plan->progress }}%"></div>
                            </div>
                        </div>
                    @elseif($plan->focusAreas->count() > 0)
                        <div class="mt-2 pt-2 border-t border-gray-100">
                            <span class="text-[10px] text-gray-400">{{ $plan->focusAreas->count() }} @term('focus_areas_label')</span>
                        </div>
                    @endif
                </a>
            @endforeach
        </div>

    {{-- List View --}}
    @else
        <div class="bg-white rounded border border-gray-200 divide-y divide-gray-100" data-help="plan-list">
            @foreach($plans as $plan)
                <a href="{{ route('plans.show', $plan) }}" class="flex items-center px-3 py-2.5 hover:bg-gray-50 transition-colors group">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <h3 class="text-sm font-medium text-gray-900 group-hover:text-pulse-orange-600 transition-colors truncate">{{ $plan->title }}</h3>
                            <span class="px-1.5 py-0.5 text-[10px] font-medium rounded shrink-0 {{ match($plan->status) {
                                'active' => 'bg-green-100 text-green-700',
                                'draft' => 'bg-gray-100 text-gray-600',
                                'completed' => 'bg-blue-100 text-blue-700',
                                default => 'bg-gray-100 text-gray-600'
                            } }}">{{ $terminology->get($plan->status.'_label') }}</span>
                        </div>
                        <div class="flex items-center gap-3 text-[10px] text-gray-400">
                            <span class="capitalize">{{ $terminology->get('plan_type_'.$plan->plan_type.'_label') }}</span>
                            <span>{{ $plan->start_date->format('M j') }} - {{ $plan->end_date->format('M j, Y') }}</span>
                            @if($plan->isOkrStyle())
                                <span>{{ $plan->goals->count() }} @term('goals_label')</span>
                            @else
                                <span>{{ $plan->focusAreas->count() }} @term('focus_areas_label')</span>
                            @endif
                        </div>
                    </div>

                    @if($plan->isOkrStyle())
                        <div class="w-20 mr-3">
                            <div class="flex items-center justify-between text-[10px] mb-0.5">
                                <span class="text-gray-400">@term('progress_label')</span>
                                <span class="font-medium text-gray-600">{{ number_format($plan->progress, 0) }}%</span>
                            </div>
                            <div class="h-1 bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-pulse-orange-500 rounded-full" style="width: {{ $plan->progress }}%"></div>
                            </div>
                        </div>
                    @endif

                    <x-icon name="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-gray-400" />
                </a>
            @endforeach
        </div>
    @endif

    {{-- Pagination --}}
    @if($plans->hasPages())
        <div class="mt-4">
            {{ $plans->links() }}
        </div>
    @endif
</div>
