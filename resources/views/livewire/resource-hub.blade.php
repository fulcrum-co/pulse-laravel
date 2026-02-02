<div class="flex gap-6">
    {{-- Left Sidebar --}}
    <div class="w-64 flex-shrink-0" data-help="resource-filters">
        {{-- Search --}}
        <div class="relative mb-6" data-help="search-resources">
            <x-icon name="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search resources..."
                class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
            @if($search)
                <button wire:click="clearSearch" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    <x-icon name="x-mark" class="w-4 h-4" />
                </button>
            @endif
        </div>

        {{-- Category Filter --}}
        <div class="mb-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</h3>
                @if(count($selectedCategories) > 0)
                    <button wire:click="clearCategories" class="text-xs text-pulse-orange-600 hover:text-pulse-orange-700">Clear</button>
                @endif
            </div>
            <div class="space-y-1">
                <label class="flex items-center gap-2 py-1 cursor-pointer">
                    <input type="checkbox" wire:click="toggleCategory('content')" @checked(in_array('content', $selectedCategories)) class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500">
                    <x-icon name="document-text" class="w-4 h-4 text-blue-500" />
                    <span class="text-sm text-gray-700">Content</span>
                </label>
                <label class="flex items-center gap-2 py-1 cursor-pointer">
                    <input type="checkbox" wire:click="toggleCategory('provider')" @checked(in_array('provider', $selectedCategories)) class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500">
                    <x-icon name="users" class="w-4 h-4 text-purple-500" />
                    <span class="text-sm text-gray-700">Providers</span>
                </label>
                <label class="flex items-center gap-2 py-1 cursor-pointer">
                    <input type="checkbox" wire:click="toggleCategory('program')" @checked(in_array('program', $selectedCategories)) class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500">
                    <x-icon name="building-office" class="w-4 h-4 text-green-500" />
                    <span class="text-sm text-gray-700">Programs</span>
                </label>
                <label class="flex items-center gap-2 py-1 cursor-pointer">
                    <input type="checkbox" wire:click="toggleCategory('course')" @checked(in_array('course', $selectedCategories)) class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500">
                    <x-icon name="academic-cap" class="w-4 h-4 text-orange-500" />
                    <span class="text-sm text-gray-700">Courses</span>
                </label>
            </div>
        </div>

        {{-- Content Type Filter (shown only when Content is selected) --}}
        @if(in_array('content', $selectedCategories))
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Content Type</h3>
                    @if(count($selectedContentTypes) > 0)
                        <button wire:click="clearContentTypes" class="text-xs text-pulse-orange-600 hover:text-pulse-orange-700">Clear</button>
                    @endif
                </div>
                <div class="space-y-1">
                    @foreach($contentTypes as $value => $label)
                        <label class="flex items-center gap-2 py-1 cursor-pointer">
                            <input type="checkbox" wire:click="toggleContentType('{{ $value }}')" @checked(in_array($value, $selectedContentTypes)) class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Sort By --}}
        <div>
            <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Sort By</h3>
            <select wire:model.live="sortBy" class="w-full text-sm border border-gray-300 rounded-lg py-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                <option value="recent">Recently Added</option>
                <option value="alphabetical">Alphabetical</option>
            </select>
        </div>
    </div>

    {{-- Main Content --}}
    <div class="flex-1 min-w-0">
        @if($isSearching && count($searchResults) > 0)
            <!-- Search Results -->
            <div class="space-y-10">
                <!-- Content Results -->
                @if($searchResults['content']['total'] > 0)
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">
                                Content
                                <span class="text-sm font-normal text-gray-500">({{ $searchResults['content']['total'] }} results)</span>
                            </h2>
                            <a href="{{ route('resources.content.index', ['q' => $search]) }}" class="text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                View all content &rarr;
                            </a>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach($searchResults['content']['items'] as $item)
                                <a href="{{ $item['url'] }}" class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md hover:border-pulse-orange-300 transition-all">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                            <x-icon name="{{ $item['icon'] }}" class="w-5 h-5 text-blue-600" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-sm font-medium text-gray-900 truncate">{{ $item['title'] }}</h3>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $item['subtitle'] }}</p>
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Provider Results (List Layout) -->
                @if($searchResults['providers']['total'] > 0)
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">
                                Providers
                                <span class="text-sm font-normal text-gray-500">({{ $searchResults['providers']['total'] }} results)</span>
                            </h2>
                            <a href="{{ route('resources.providers.index', ['q' => $search]) }}" class="text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                View all providers &rarr;
                            </a>
                        </div>
                        <div class="bg-white rounded-lg border border-gray-200 divide-y divide-gray-100">
                            @foreach($searchResults['providers']['items'] as $item)
                                <a href="{{ $item['url'] }}" class="flex items-center gap-4 p-4 hover:bg-gray-50 transition-colors">
                                    <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center flex-shrink-0">
                                        <x-icon name="user" class="w-5 h-5 text-purple-600" />
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <h3 class="text-sm font-medium text-gray-900">{{ $item['title'] }}</h3>
                                            <span class="text-xs text-gray-500">&bull; {{ $item['subtitle'] }}</span>
                                        </div>
                                        @if($item['description'])
                                            <p class="text-sm text-gray-600 truncate mt-0.5">{{ Str::limit($item['description'], 80) }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        @if($item['serves_remote'])
                                            <span class="text-xs text-gray-500">Remote available</span>
                                        @endif
                                        <x-icon name="chevron-right" class="w-5 h-5 text-gray-400" />
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Program Results -->
                @if($searchResults['programs']['total'] > 0)
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">
                                Programs
                                <span class="text-sm font-normal text-gray-500">({{ $searchResults['programs']['total'] }} results)</span>
                            </h2>
                            <a href="{{ route('resources.programs.index', ['q' => $search]) }}" class="text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                View all programs &rarr;
                            </a>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach($searchResults['programs']['items'] as $item)
                                <a href="{{ $item['url'] }}" class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md hover:border-pulse-orange-300 transition-all">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                                            <x-icon name="building-office" class="w-5 h-5 text-green-600" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-sm font-medium text-gray-900 truncate">{{ $item['title'] }}</h3>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $item['subtitle'] }}</p>
                                            @if($item['meta'])
                                                <p class="text-xs text-gray-400 mt-1">{{ $item['meta'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Course Results -->
                @if($searchResults['courses']['total'] > 0)
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-gray-900">
                                Courses
                                <span class="text-sm font-normal text-gray-500">({{ $searchResults['courses']['total'] }} results)</span>
                            </h2>
                            <a href="{{ route('resources.courses.index', ['q' => $search]) }}" class="text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                View all courses &rarr;
                            </a>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach($searchResults['courses']['items'] as $item)
                                <a href="{{ $item['url'] }}" class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md hover:border-pulse-orange-300 transition-all">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                                            <x-icon name="academic-cap" class="w-5 h-5 text-orange-600" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-sm font-medium text-gray-900 truncate">{{ $item['title'] }}</h3>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $item['subtitle'] }}</p>
                                            @if($item['meta'])
                                                <p class="text-xs text-gray-400 mt-1">{{ $item['meta'] }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- No Results -->
                @if($searchResults['content']['total'] === 0 && $searchResults['providers']['total'] === 0 && $searchResults['programs']['total'] === 0 && $searchResults['courses']['total'] === 0)
                    <div class="text-center py-12">
                        <x-icon name="magnifying-glass" class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                        <h3 class="text-lg font-medium text-gray-900 mb-1">No results found</h3>
                        <p class="text-gray-500">Try adjusting your search terms or browse by category below.</p>
                        <button wire:click="clearSearch" class="mt-4 text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                            Clear search
                        </button>
                    </div>
                @endif
            </div>
        @else
            <!-- Category Cards (3-column layout to fit with sidebar) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8" data-help="resource-categories">
                <!-- Content Card -->
                <a href="{{ route('resources.content.index') }}" class="group bg-white rounded-2xl border border-gray-200 p-4 hover:shadow-xl hover:border-pulse-orange-300 transition-all">
                    <div class="flex flex-col items-center text-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <x-icon name="document-text" class="w-5 h-5 text-blue-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-sm font-semibold text-pulse-orange-600 group-hover:text-pulse-orange-600 transition-colors">Content</h2>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($counts['content']) }}</p>
                        </div>
                    </div>
                </a>

                <!-- Providers Card -->
                <a href="{{ route('resources.providers.index') }}" class="group bg-white rounded-2xl border border-gray-200 p-4 hover:shadow-xl hover:border-pulse-orange-300 transition-all">
                    <div class="flex flex-col items-center text-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <x-icon name="users" class="w-5 h-5 text-purple-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-sm font-semibold text-pulse-orange-600 group-hover:text-pulse-orange-600 transition-colors">Providers</h2>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($counts['providers']) }}</p>
                        </div>
                    </div>
                </a>

                <!-- Programs Card -->
                <a href="{{ route('resources.programs.index') }}" class="group bg-white rounded-2xl border border-gray-200 p-4 hover:shadow-xl hover:border-pulse-orange-300 transition-all">
                    <div class="flex flex-col items-center text-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <x-icon name="building-office" class="w-5 h-5 text-green-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-sm font-semibold text-pulse-orange-600 group-hover:text-pulse-orange-600 transition-colors">Programs</h2>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($counts['programs']) }}</p>
                        </div>
                    </div>
                </a>

                <!-- Courses Card -->
                <a href="{{ route('resources.courses.index') }}" class="group bg-white rounded-2xl border border-gray-200 p-4 hover:shadow-xl hover:border-pulse-orange-300 transition-all">
                    <div class="flex flex-col items-center text-center gap-2">
                        <div class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <x-icon name="academic-cap" class="w-5 h-5 text-orange-600" />
                        </div>
                        <div class="flex-1 min-w-0">
                            <h2 class="text-sm font-semibold text-pulse-orange-600 group-hover:text-pulse-orange-600 transition-colors">Courses</h2>
                            <p class="text-xl font-bold text-gray-900">{{ number_format($counts['courses']) }}</p>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Recently Added -->
            @if($recentItems->count() > 0)
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                            <x-icon name="clock" class="w-5 h-5 text-gray-400" />
                            Recently Added
                            <span class="text-sm font-normal text-gray-500">({{ $recentItems->count() }} items)</span>
                        </h2>
                        {{-- View Toggle --}}
                        <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                            <button
                                wire:click="$set('viewMode', 'list')"
                                class="p-2 {{ $viewMode === 'list' ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}"
                                title="List view"
                            >
                                <x-icon name="list-bullet" class="w-4 h-4" />
                            </button>
                            <button
                                wire:click="$set('viewMode', 'grid')"
                                class="p-2 border-l border-gray-200 {{ $viewMode === 'grid' ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}"
                                title="Grid view"
                            >
                                <x-icon name="squares-2x2" class="w-4 h-4" />
                            </button>
                            <button
                                wire:click="$set('viewMode', 'table')"
                                class="p-2 border-l border-gray-200 {{ $viewMode === 'table' ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}"
                                title="Table view"
                            >
                                <x-icon name="table-cells" class="w-4 h-4" />
                            </button>
                        </div>
                    </div>

                    {{-- GRID VIEW --}}
                    @if($viewMode === 'grid')
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                            @foreach($recentItems as $item)
                                @php
                                    $bgColor = match($item['icon_bg']) {
                                        'blue' => 'bg-blue-100',
                                        'purple' => 'bg-purple-100',
                                        'green' => 'bg-green-100',
                                        'orange' => 'bg-orange-100',
                                        default => 'bg-gray-100',
                                    };
                                    $textColor = match($item['icon_bg']) {
                                        'blue' => 'text-blue-600',
                                        'purple' => 'text-purple-600',
                                        'green' => 'text-green-600',
                                        'orange' => 'text-orange-600',
                                        default => 'text-gray-600',
                                    };
                                @endphp
                                <a href="{{ $item['url'] }}" class="bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md hover:border-pulse-orange-300 transition-all">
                                    <div class="flex items-start gap-3">
                                        <div class="w-10 h-10 rounded-lg {{ $bgColor }} flex items-center justify-center flex-shrink-0">
                                            <x-icon name="{{ $item['icon'] }}" class="w-5 h-5 {{ $textColor }}" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="text-sm font-medium text-gray-900 truncate">{{ $item['title'] }}</h3>
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $item['subtitle'] }}</p>
                                            @if(isset($item['description']) && $item['description'])
                                                <p class="text-xs text-gray-400 mt-1 line-clamp-2">{{ Str::limit($item['description'], 60) }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- LIST VIEW --}}
                    @if($viewMode === 'list')
                        <div class="space-y-3">
                            @foreach($recentItems as $item)
                                @php
                                    $bgColor = match($item['icon_bg']) {
                                        'blue' => 'bg-blue-100',
                                        'purple' => 'bg-purple-100',
                                        'green' => 'bg-green-100',
                                        'orange' => 'bg-orange-100',
                                        default => 'bg-gray-100',
                                    };
                                    $textColor = match($item['icon_bg']) {
                                        'blue' => 'text-blue-600',
                                        'purple' => 'text-purple-600',
                                        'green' => 'text-green-600',
                                        'orange' => 'text-orange-600',
                                        default => 'text-gray-600',
                                    };
                                @endphp
                                <a href="{{ $item['url'] }}" class="flex items-center gap-4 bg-white rounded-lg border border-gray-200 p-4 hover:shadow-md hover:border-pulse-orange-300 transition-all">
                                    <div class="w-10 h-10 rounded-lg {{ $bgColor }} flex items-center justify-center flex-shrink-0">
                                        <x-icon name="{{ $item['icon'] }}" class="w-5 h-5 {{ $textColor }}" />
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-sm font-medium text-gray-900">{{ $item['title'] }}</h3>
                                        <p class="text-xs text-gray-500">{{ $item['subtitle'] }}</p>
                                    </div>
                                    @if(isset($item['description']) && $item['description'])
                                        <p class="hidden md:block text-sm text-gray-500 max-w-md truncate">{{ Str::limit($item['description'], 80) }}</p>
                                    @endif
                                    <x-icon name="chevron-right" class="w-5 h-5 text-gray-400 flex-shrink-0" />
                                </a>
                            @endforeach
                        </div>
                    @endif

                    {{-- TABLE VIEW --}}
                    @if($viewMode === 'table')
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Description</th>
                                        <th scope="col" class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($recentItems as $item)
                                        @php
                                            $bgColor = match($item['icon_bg']) {
                                                'blue' => 'bg-blue-100',
                                                'purple' => 'bg-purple-100',
                                                'green' => 'bg-green-100',
                                                'orange' => 'bg-orange-100',
                                                default => 'bg-gray-100',
                                            };
                                            $textColor = match($item['icon_bg']) {
                                                'blue' => 'text-blue-600',
                                                'purple' => 'text-purple-600',
                                                'green' => 'text-green-600',
                                                'orange' => 'text-orange-600',
                                                default => 'text-gray-600',
                                            };
                                        @endphp
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 rounded-lg {{ $bgColor }} flex items-center justify-center flex-shrink-0">
                                                        <x-icon name="{{ $item['icon'] }}" class="w-4 h-4 {{ $textColor }}" />
                                                    </div>
                                                    <span class="text-sm font-medium text-gray-900">{{ $item['title'] }}</span>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="text-sm text-gray-500">{{ $item['subtitle'] }}</span>
                                            </td>
                                            <td class="px-4 py-3 hidden md:table-cell">
                                                <span class="text-sm text-gray-500 truncate max-w-xs block">{{ Str::limit($item['description'] ?? '', 50) }}</span>
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <a href="{{ $item['url'] }}" class="text-sm font-medium text-pulse-orange-600 hover:text-pulse-orange-700">View</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Empty State -->
            @if($counts['content'] === 0 && $counts['providers'] === 0 && $counts['programs'] === 0 && $counts['courses'] === 0)
                <div class="text-center py-16">
                    <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-6">
                        <x-icon name="folder-open" class="w-10 h-10 text-gray-400" />
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-2">No resources yet</h3>
                    <p class="text-gray-500 mb-6 max-w-md mx-auto">Start building your resource library by adding content, providers, programs, or courses.</p>
                    <a href="{{ route('resources.content.index') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-pulse-orange-500 text-white font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors">
                        <x-icon name="plus" class="w-5 h-5" />
                        Browse Resources
                    </a>
                </div>
            @endif
        @endif
    </div>
</div>
