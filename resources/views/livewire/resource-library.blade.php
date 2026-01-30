<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Resource Library</h1>
            <p class="text-sm text-gray-500 mt-1">Browse content, providers, programs, and mini-courses</p>
        </div>
        <div class="flex gap-2">
            <a href="#" wire:click.prevent="$dispatch('notify', {type: 'info', message: 'Resource creation coming soon'})" class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Add Resource
            </a>
        </div>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex gap-6 overflow-x-auto" aria-label="Tabs">
            @foreach([
                'all' => ['label' => 'All', 'count' => $counts['resources'] + $counts['providers'] + $counts['programs'] + $counts['courses']],
                'content' => ['label' => 'Content', 'count' => $counts['resources']],
                'providers' => ['label' => 'Providers', 'count' => $counts['providers']],
                'programs' => ['label' => 'Programs', 'count' => $counts['programs']],
                'courses' => ['label' => 'Mini-Courses', 'count' => $counts['courses']],
            ] as $tab => $data)
            <button
                wire:click="setActiveTab('{{ $tab }}')"
                class="whitespace-nowrap py-3 px-1 border-b-2 text-sm font-medium transition-colors {{ $activeTab === $tab ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                {{ $data['label'] }}
                <span class="ml-2 px-2 py-0.5 text-xs rounded-full {{ $activeTab === $tab ? 'bg-pulse-orange-100 text-pulse-orange-600' : 'bg-gray-100 text-gray-600' }}">
                    {{ $data['count'] }}
                </span>
            </button>
            @endforeach
        </nav>
    </div>

    <!-- Filters Bar -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
        <div class="flex flex-wrap gap-3 items-center">
            <!-- Search -->
            <div class="relative">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search..."
                    class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent w-64"
                >
            </div>

            <!-- Type Filter (contextual based on tab) -->
            @if($activeTab === 'content')
            <select wire:model.live="filterType" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent">
                <option value="">All Types</option>
                @foreach($this->resourceTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterCategory" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent">
                <option value="">All Categories</option>
                @foreach($this->categories as $category)
                <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                @endforeach
            </select>
            @endif

            @if($activeTab === 'providers')
            <select wire:model.live="filterType" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent">
                <option value="">All Types</option>
                @foreach($this->providerTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @endif

            @if($activeTab === 'programs')
            <select wire:model.live="filterType" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent">
                <option value="">All Types</option>
                @foreach($this->programTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @endif

            @if($activeTab === 'courses')
            <select wire:model.live="filterType" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent">
                <option value="">All Types</option>
                @foreach($this->courseTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @endif

            @if($search || $filterType || $filterCategory)
            <button wire:click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700">
                Clear filters
            </button>
            @endif
        </div>

        <!-- View Toggle -->
        <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
            <button
                wire:click="setViewMode('grid')"
                class="p-2 rounded {{ $viewMode === 'grid' ? 'bg-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}"
                title="Grid view"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path>
                </svg>
            </button>
            <button
                wire:click="setViewMode('list')"
                class="p-2 rounded {{ $viewMode === 'list' ? 'bg-white shadow-sm' : 'text-gray-500 hover:text-gray-700' }}"
                title="List view"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
        </div>
    </div>

    <!-- Content Area -->
    <div>
        {{-- All Tab --}}
        @if($activeTab === 'all')
            @if($allItems->isEmpty())
                @include('livewire.resource-library.empty-state', ['message' => 'No resources found. Start by adding content, providers, or programs.'])
            @else
                <div class="{{ $viewMode === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4' : 'space-y-3' }}">
                    @foreach($allItems as $item)
                        @include('livewire.resource-library.item-card', ['item' => $item, 'viewMode' => $viewMode])
                    @endforeach
                </div>
            @endif
        @endif

        {{-- Content Tab --}}
        @if($activeTab === 'content')
            @if($contentResources->isEmpty())
                @include('livewire.resource-library.empty-state', ['message' => 'No content resources found.'])
            @else
                <div class="{{ $viewMode === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4' : 'space-y-3' }}">
                    @foreach($contentResources as $resource)
                        @include('livewire.resource-library.resource-card', ['resource' => $resource, 'viewMode' => $viewMode])
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $contentResources->links() }}
                </div>
            @endif
        @endif

        {{-- Providers Tab --}}
        @if($activeTab === 'providers')
            @if($providers->isEmpty())
                @include('livewire.resource-library.empty-state', ['message' => 'No providers found.'])
            @else
                <div class="{{ $viewMode === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4' : 'space-y-3' }}">
                    @foreach($providers as $provider)
                        @include('livewire.resource-library.provider-card', ['provider' => $provider, 'viewMode' => $viewMode])
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $providers->links() }}
                </div>
            @endif
        @endif

        {{-- Programs Tab --}}
        @if($activeTab === 'programs')
            @if($programs->isEmpty())
                @include('livewire.resource-library.empty-state', ['message' => 'No programs found.'])
            @else
                <div class="{{ $viewMode === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4' : 'space-y-3' }}">
                    @foreach($programs as $program)
                        @include('livewire.resource-library.program-card', ['program' => $program, 'viewMode' => $viewMode])
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $programs->links() }}
                </div>
            @endif
        @endif

        {{-- Courses Tab --}}
        @if($activeTab === 'courses')
            @if($miniCourses->isEmpty())
                @include('livewire.resource-library.empty-state', ['message' => 'No mini-courses found.'])
            @else
                <div class="{{ $viewMode === 'grid' ? 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4' : 'space-y-3' }}">
                    @foreach($miniCourses as $course)
                        @include('livewire.resource-library.course-card', ['course' => $course, 'viewMode' => $viewMode])
                    @endforeach
                </div>
                <div class="mt-6">
                    {{ $miniCourses->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
