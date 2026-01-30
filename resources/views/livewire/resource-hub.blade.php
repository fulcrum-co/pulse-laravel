<div class="min-h-screen bg-gray-50">
    <!-- Header Banner -->
    <div class="bg-white border-b border-gray-200">
        <div class="px-6 py-4">
            <h1 class="text-2xl font-semibold text-gray-900">Resources</h1>
        </div>
    </div>

    <div class="px-6 py-6 max-w-6xl mx-auto">
        <!-- Unified Search -->
        <div class="mb-8">
            <div class="relative max-w-2xl mx-auto">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <x-icon name="magnifying-glass" class="h-5 w-5 text-gray-400" />
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search all resources..."
                    class="block w-full pl-11 pr-10 py-3 border border-gray-300 rounded-xl bg-white shadow-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500 text-gray-900 placeholder-gray-500"
                >
                @if($search)
                    <button
                        wire:click="clearSearch"
                        class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600"
                    >
                        <x-icon name="x-mark" class="h-5 w-5" />
                    </button>
                @endif
            </div>
        </div>

        @if($isSearching && count($searchResults) > 0)
            <!-- Search Results -->
            <div class="space-y-8">
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
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
            <!-- Section Cards (when not searching) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <!-- Content Card -->
                <a href="{{ route('resources.content.index') }}" class="group bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg hover:border-pulse-orange-300 transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-xl bg-blue-100 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                            <x-icon name="document-text" class="w-7 h-7 text-blue-600" />
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">Content</h2>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($counts['content']) }}</p>
                            <p class="text-sm text-gray-500 mt-1">Articles, videos, worksheets, activities, and more</p>
                        </div>
                        <x-icon name="chevron-right" class="w-5 h-5 text-gray-400 group-hover:text-pulse-orange-500 group-hover:translate-x-1 transition-all" />
                    </div>
                </a>

                <!-- Providers Card -->
                <a href="{{ route('resources.providers.index') }}" class="group bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg hover:border-pulse-orange-300 transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-xl bg-purple-100 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                            <x-icon name="users" class="w-7 h-7 text-purple-600" />
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">Providers</h2>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($counts['providers']) }}</p>
                            <p class="text-sm text-gray-500 mt-1">Therapists, tutors, coaches, and specialists</p>
                        </div>
                        <x-icon name="chevron-right" class="w-5 h-5 text-gray-400 group-hover:text-pulse-orange-500 group-hover:translate-x-1 transition-all" />
                    </div>
                </a>

                <!-- Programs Card -->
                <a href="{{ route('resources.programs.index') }}" class="group bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg hover:border-pulse-orange-300 transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-xl bg-green-100 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                            <x-icon name="building-office" class="w-7 h-7 text-green-600" />
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">Programs</h2>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($counts['programs']) }}</p>
                            <p class="text-sm text-gray-500 mt-1">Interventions, support groups, and services</p>
                        </div>
                        <x-icon name="chevron-right" class="w-5 h-5 text-gray-400 group-hover:text-pulse-orange-500 group-hover:translate-x-1 transition-all" />
                    </div>
                </a>

                <!-- Courses Card -->
                <a href="{{ route('resources.courses.index') }}" class="group bg-white rounded-xl border border-gray-200 p-6 hover:shadow-lg hover:border-pulse-orange-300 transition-all">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-xl bg-orange-100 flex items-center justify-center flex-shrink-0 group-hover:scale-105 transition-transform">
                            <x-icon name="academic-cap" class="w-7 h-7 text-orange-600" />
                        </div>
                        <div class="flex-1">
                            <h2 class="text-lg font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">Courses</h2>
                            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($counts['courses']) }}</p>
                            <p class="text-sm text-gray-500 mt-1">Mini-courses and learning paths</p>
                        </div>
                        <x-icon name="chevron-right" class="w-5 h-5 text-gray-400 group-hover:text-pulse-orange-500 group-hover:translate-x-1 transition-all" />
                    </div>
                </a>
            </div>
        @endif
    </div>
</div>
