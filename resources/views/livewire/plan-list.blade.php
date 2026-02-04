<div>
    {{-- Tabs --}}
    <div class="border-b border-gray-200 mb-4">
        <nav class="flex -mb-px">
            <button
                wire:click="setActiveTab('plans')"
                class="px-4 py-2 text-sm font-medium {{ $activeTab === 'plans' ? 'border-b-2 border-pulse-orange-500 text-pulse-orange-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Plans
            </button>
            <button
                wire:click="setActiveTab('alignment')"
                class="px-4 py-2 text-sm font-medium {{ $activeTab === 'alignment' ? 'border-b-2 border-pulse-orange-500 text-pulse-orange-600' : 'text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Alignment
            </button>
        </nav>
    </div>

    @if($activeTab === 'plans')
        {{-- Search & Filters --}}
        <div class="mb-4 flex items-center gap-2" data-help="plan-filters">
            <div class="relative flex-1 max-w-xs" data-help="search-plans">
                <x-icon name="search" class="w-3.5 h-3.5 text-gray-400 absolute left-2.5 top-1/2 -translate-y-1/2" />
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="w-full pl-8 pr-3 py-1.5 text-xs border border-gray-200 rounded focus:ring-1 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                    placeholder="Search plans...">
            </div>

            <select wire:model.live="typeFilter"
                class="px-2 py-1.5 text-xs border border-gray-200 rounded focus:ring-1 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
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
                class="px-2 py-1.5 text-xs border border-gray-200 rounded focus:ring-1 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="draft">Draft</option>
                <option value="completed">Completed</option>
            </select>

            @if($search || $statusFilter || $typeFilter !== 'all')
                <button wire:click="clearFilters" class="text-xs text-gray-400 hover:text-gray-600">
                    Clear
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
                <p class="text-sm text-gray-500 mb-1">No plans found</p>
                <p class="text-xs text-gray-400 mb-3">Create your first plan to get started</p>
                <a href="{{ route('plans.create') }}" class="inline-flex items-center px-3 py-1.5 bg-pulse-orange-500 text-white rounded text-xs font-medium hover:bg-pulse-orange-600">
                    <x-icon name="plus" class="w-3.5 h-3.5 mr-1" />
                    New Plan
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
                            } }}">{{ ucfirst($plan->status) }}</span>
                        </div>

                        @if($plan->description)
                            <p class="text-xs text-gray-500 mb-2 line-clamp-2">{{ $plan->description }}</p>
                        @endif

                        <div class="flex items-center justify-between text-[10px] text-gray-400">
                            <span>{{ $plan->start_date->format('M j') }} - {{ $plan->end_date->format('M j, Y') }}</span>
                            <span class="capitalize">{{ str_replace('_', ' ', $plan->plan_type) }}</span>
                        </div>

                        @if($plan->isOkrStyle() && $plan->goals->count() > 0)
                            <div class="mt-2 pt-2 border-t border-gray-100">
                                <div class="flex items-center justify-between text-[10px] mb-1">
                                    <span class="text-gray-500">{{ $plan->goals->count() }} goals</span>
                                    <span class="font-medium text-gray-600">{{ number_format($plan->progress, 0) }}%</span>
                                </div>
                                <div class="h-1 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-pulse-orange-500 rounded-full" style="width: {{ $plan->progress }}%"></div>
                                </div>
                            </div>
                        @elseif($plan->focusAreas->count() > 0)
                            <div class="mt-2 pt-2 border-t border-gray-100">
                                <span class="text-[10px] text-gray-400">{{ $plan->focusAreas->count() }} focus areas</span>
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
                                } }}">{{ ucfirst($plan->status) }}</span>
                            </div>
                            <div class="flex items-center gap-3 text-[10px] text-gray-400">
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
                            <div class="w-20 mr-3">
                                <div class="flex items-center justify-between text-[10px] mb-0.5">
                                    <span class="text-gray-400">Progress</span>
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

    @else
        {{-- Alignment Tab Content --}}
        <div class="space-y-4">
            {{-- Alignment Filters --}}
            <div class="flex items-center justify-between">
                <p class="text-sm text-gray-500">Monitor alignment between activities and plans</p>
                <select
                    wire:model.live="alignmentTimeRange"
                    class="text-sm border-gray-300 rounded-lg focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                >
                    <option value="7">Last 7 days</option>
                    <option value="30">Last 30 days</option>
                    <option value="90">Last 90 days</option>
                </select>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Average Alignment --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Avg Alignment</span>
                        @php
                            $avgScore = $this->alignmentSummary['average_alignment'];
                            $avgColor = match(true) {
                                $avgScore === null => 'gray',
                                $avgScore >= 0.85 => 'green',
                                $avgScore >= 0.65 => 'yellow',
                                default => 'red',
                            };
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $avgColor }}-100 text-{{ $avgColor }}-800">
                            {{ $avgScore !== null ? number_format($avgScore * 100, 0) . '%' : 'N/A' }}
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-gray-900">
                            {{ $avgScore !== null ? number_format($avgScore * 100, 0) : '—' }}
                        </span>
                        @if($avgScore !== null)
                            <span class="text-lg text-gray-500">%</span>
                        @endif
                    </div>
                </div>

                {{-- On Track --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">On Track</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            On Track
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-green-600">{{ $this->alignmentSummary['strong_count'] }}</span>
                        <span class="text-sm text-gray-500">narratives</span>
                    </div>
                </div>

                {{-- Drifting --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Drifting</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Drifting
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-yellow-600">{{ $this->alignmentSummary['moderate_count'] }}</span>
                        <span class="text-sm text-gray-500">narratives</span>
                    </div>
                </div>

                {{-- Off Track --}}
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-500">Off Track</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            Off Track
                        </span>
                    </div>
                    <div class="mt-2 flex items-baseline gap-2">
                        <span class="text-3xl font-bold text-red-600">{{ $this->alignmentSummary['weak_count'] }}</span>
                        <span class="text-sm text-gray-500">narratives</span>
                    </div>
                </div>
            </div>

            {{-- Trend Indicator --}}
            @if($this->alignmentSummary['trend'] !== 'insufficient_data')
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <div class="flex items-center gap-3">
                        @if($this->alignmentSummary['trend'] === 'improving')
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                <x-icon name="arrow-trending-up" class="w-5 h-5 text-green-600" />
                            </div>
                            <div>
                                <p class="font-medium text-green-600">Alignment Improving</p>
                                <p class="text-sm text-gray-500">Activities are becoming more aligned with plans</p>
                            </div>
                        @elseif($this->alignmentSummary['trend'] === 'declining')
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                <x-icon name="arrow-trending-down" class="w-5 h-5 text-red-600" />
                            </div>
                            <div>
                                <p class="font-medium text-red-600">Alignment Declining</p>
                                <p class="text-sm text-gray-500">Activities are drifting from plans</p>
                            </div>
                        @else
                            <div class="flex-shrink-0 w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center">
                                <x-icon name="minus" class="w-5 h-5 text-gray-600" />
                            </div>
                            <div>
                                <p class="font-medium text-gray-600">Alignment Stable</p>
                                <p class="text-sm text-gray-500">Activities are maintaining consistent alignment levels</p>
                            </div>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Filter Tabs --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button
                            wire:click="setAlignmentFilterLevel('all')"
                            class="px-6 py-3 text-sm font-medium {{ $alignmentFilterLevel === 'all' ? 'border-b-2 border-pulse-orange-500 text-pulse-orange-600' : 'text-gray-500 hover:text-gray-700' }}"
                        >
                            All ({{ $this->alignmentSummary['total_count'] }})
                        </button>
                        <button
                            wire:click="setAlignmentFilterLevel('weak')"
                            class="px-6 py-3 text-sm font-medium {{ $alignmentFilterLevel === 'weak' ? 'border-b-2 border-red-500 text-red-600' : 'text-gray-500 hover:text-gray-700' }}"
                        >
                            Off Track ({{ $this->alignmentSummary['weak_count'] }})
                        </button>
                        <button
                            wire:click="setAlignmentFilterLevel('moderate')"
                            class="px-6 py-3 text-sm font-medium {{ $alignmentFilterLevel === 'moderate' ? 'border-b-2 border-yellow-500 text-yellow-600' : 'text-gray-500 hover:text-gray-700' }}"
                        >
                            Drifting ({{ $this->alignmentSummary['moderate_count'] }})
                        </button>
                        <button
                            wire:click="setAlignmentFilterLevel('strong')"
                            class="px-6 py-3 text-sm font-medium {{ $alignmentFilterLevel === 'strong' ? 'border-b-2 border-green-500 text-green-600' : 'text-gray-500 hover:text-gray-700' }}"
                        >
                            On Track ({{ $this->alignmentSummary['strong_count'] }})
                        </button>
                    </nav>
                </div>

                {{-- Alignment Scores Table --}}
                <div class="divide-y divide-gray-200">
                    @forelse($this->alignmentScores as $score)
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    {{-- Contact Info --}}
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-medium text-gray-900">
                                            {{ $score->contactNote?->contact?->name ?? 'Unknown Contact' }}
                                        </span>
                                        <span class="text-gray-400">•</span>
                                        <span class="text-sm text-gray-500">
                                            by {{ $score->contactNote?->author?->name ?? 'Unknown' }}
                                        </span>
                                    </div>

                                    {{-- Note Preview --}}
                                    <p class="text-sm text-gray-600 line-clamp-2">
                                        {{ Str::limit($score->contactNote?->content ?? 'No content', 200) }}
                                    </p>

                                    {{-- Matched Context Tags --}}
                                    @if(!empty($score->matched_context))
                                        <div class="mt-2 flex flex-wrap gap-1">
                                            @foreach(array_slice($score->matched_context, 0, 3) as $match)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">
                                                    {{ $match['type'] }}: {{ Str::limit($match['title'], 25) }}
                                                    <span class="ml-1 text-gray-400">({{ number_format($match['similarity'] * 100, 0) }}%)</span>
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif

                                    {{-- Timestamp --}}
                                    <p class="mt-2 text-xs text-gray-400">
                                        Scored {{ $score->scored_at?->diffForHumans() ?? 'unknown' }}
                                    </p>
                                </div>

                                {{-- Alignment Badge --}}
                                <div class="flex-shrink-0 text-right">
                                    <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold
                                        {{ $score->alignment_level === 'strong' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $score->alignment_level === 'moderate' ? 'bg-yellow-100 text-yellow-800' : '' }}
                                        {{ $score->alignment_level === 'weak' ? 'bg-red-100 text-red-800' : '' }}
                                    ">
                                        {{ number_format($score->alignment_score * 100, 0) }}%
                                    </span>
                                    <p class="mt-1 text-xs text-gray-500">
                                        {{ match($score->alignment_level) {
                                            'strong' => 'On Track',
                                            'moderate' => 'Drifting',
                                            'weak' => 'Off Track',
                                            default => ucfirst($score->alignment_level)
                                        } }}
                                    </p>
                                    @if($score->drift_direction)
                                        <p class="text-xs {{ $score->drift_direction === 'improving' ? 'text-green-600' : ($score->drift_direction === 'declining' ? 'text-red-600' : 'text-gray-500') }}">
                                            @if($score->drift_direction === 'improving')
                                                ↑ Improving
                                            @elseif($score->drift_direction === 'declining')
                                                ↓ Declining
                                            @else
                                                → Stable
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center">
                            <x-icon name="chart-bar" class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No alignment scores yet</h3>
                            @if($this->alignmentSummary['total_count'] === 0)
                                <p class="text-sm text-gray-500 max-w-md mx-auto mb-4">
                                    Alignment monitors how well your activities match your plans.
                                </p>
                                <div class="text-left max-w-sm mx-auto text-sm text-gray-600 space-y-2">
                                    <p class="font-medium text-gray-700">To get started:</p>
                                    <ol class="list-decimal list-inside space-y-1">
                                        <li>Create a Plan with goals</li>
                                        <li>Add contact notes via <a href="/collect" class="text-pulse-orange-600 hover:underline">Collect</a></li>
                                        <li>Scores auto-calculate daily at 6am</li>
                                    </ol>
                                </div>
                            @else
                                <p class="text-sm text-gray-500">
                                    No scores match the current filter. Try selecting "All" to see all scores.
                                </p>
                            @endif
                        </div>
                    @endforelse
                </div>

                {{-- Pagination --}}
                @if($this->alignmentScores->hasPages())
                    <div class="px-4 py-3 border-t border-gray-200">
                        {{ $this->alignmentScores->links() }}
                    </div>
                @endif
            </div>

            {{-- Top Drift Areas --}}
            @if(!empty($this->alignmentSummary['top_drift_areas']))
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Areas with Most Drift</h2>
                    <p class="text-sm text-gray-500 mb-4">Plan elements that frequently appear in low-alignment narratives:</p>
                    <div class="space-y-3">
                        @foreach($this->alignmentSummary['top_drift_areas'] as $area)
                            <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                                <div class="flex items-center gap-3">
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-red-100 text-red-800">
                                        {{ $area['type'] }}
                                    </span>
                                    <span class="font-medium text-gray-900">{{ Str::limit($area['title'], 50) }}</span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-medium text-red-600">{{ $area['count'] }} instances</span>
                                    <p class="text-xs text-gray-500">avg similarity: {{ number_format($area['avg_similarity'] * 100, 0) }}%</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
