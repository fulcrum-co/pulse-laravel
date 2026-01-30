<div class="space-y-4">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Resource Library</h1>
            <p class="text-sm text-gray-500 mt-1">Browse content, providers, programs, and mini-courses</p>
        </div>
        <a href="#" wire:click.prevent="$dispatch('notify', {type: 'info', message: 'Resource creation coming soon'})" class="inline-flex items-center gap-2 px-4 py-2 bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors text-sm font-medium">
            <x-icon name="plus" class="w-4 h-4" />
            Add Resource
        </a>
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

    <!-- Search, Filters & View Toggle -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 flex-1">
            <div class="relative w-full sm:w-64">
                <x-icon name="magnifying-glass" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search..."
                    class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                />
            </div>

            <!-- Type Filter (contextual based on tab) -->
            @if($activeTab === 'content')
            <select wire:model.live="filterType" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                <option value="">All Types</option>
                @foreach($this->resourceTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterCategory" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                <option value="">All Categories</option>
                @foreach($this->categories as $category)
                <option value="{{ $category }}">{{ ucfirst($category) }}</option>
                @endforeach
            </select>
            @endif

            @if($activeTab === 'providers')
            <select wire:model.live="filterType" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                <option value="">All Types</option>
                @foreach($this->providerTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @endif

            @if($activeTab === 'programs')
            <select wire:model.live="filterType" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                <option value="">All Types</option>
                @foreach($this->programTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @endif

            @if($activeTab === 'courses')
            <select wire:model.live="filterType" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                <option value="">All Types</option>
                @foreach($this->courseTypes as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @endif

            @if($search || $filterType || $filterCategory)
            <button wire:click="resetFilters" class="text-sm text-gray-500 hover:text-gray-700">
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

    <!-- Content Area -->
    <div>
        {{-- All Tab --}}
        @if($activeTab === 'all')
            @if($allItems->isEmpty())
                @include('livewire.resource-library.empty-state', ['message' => 'No resources found. Start by adding content, providers, or programs.'])
            @elseif($viewMode === 'grid')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($allItems as $item)
                        @include('livewire.resource-library.item-card', ['item' => $item, 'viewMode' => 'grid'])
                    @endforeach
                </div>
            @elseif($viewMode === 'list')
                <div class="space-y-2">
                    @foreach($allItems as $item)
                        @include('livewire.resource-library.item-card', ['item' => $item, 'viewMode' => 'list'])
                    @endforeach
                </div>
            @else
                @include('livewire.resource-library.all-table', ['items' => $allItems])
            @endif
        @endif

        {{-- Content Tab --}}
        @if($activeTab === 'content')
            @if($contentResources->isEmpty())
                @include('livewire.resource-library.empty-state', ['message' => 'No content resources found.'])
            @elseif($viewMode === 'grid')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($contentResources as $resource)
                        @include('livewire.resource-library.resource-card', ['resource' => $resource, 'viewMode' => 'grid'])
                    @endforeach
                </div>
            @elseif($viewMode === 'list')
                <div class="space-y-2">
                    @foreach($contentResources as $resource)
                        @include('livewire.resource-library.resource-card', ['resource' => $resource, 'viewMode' => 'list'])
                    @endforeach
                </div>
            @else
                @include('livewire.resource-library.content-table', ['resources' => $contentResources])
            @endif

            @if($contentResources->hasPages())
            <div class="mt-4">
                {{ $contentResources->links() }}
            </div>
            @endif
        @endif

        {{-- Providers Tab --}}
        @if($activeTab === 'providers')
            @if($providers->isEmpty())
                @include('livewire.resource-library.empty-state', ['message' => 'No providers found.'])
            @elseif($viewMode === 'grid')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($providers as $provider)
                        @include('livewire.resource-library.provider-card', ['provider' => $provider, 'viewMode' => 'grid'])
                    @endforeach
                </div>
            @elseif($viewMode === 'list')
                <div class="space-y-2">
                    @foreach($providers as $provider)
                        @include('livewire.resource-library.provider-card', ['provider' => $provider, 'viewMode' => 'list'])
                    @endforeach
                </div>
            @else
                @include('livewire.resource-library.providers-table', ['providers' => $providers])
            @endif

            @if($providers->hasPages())
            <div class="mt-4">
                {{ $providers->links() }}
            </div>
            @endif
        @endif

        {{-- Programs Tab --}}
        @if($activeTab === 'programs')
            @if($programs->isEmpty())
                @include('livewire.resource-library.empty-state', ['message' => 'No programs found.'])
            @elseif($viewMode === 'grid')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($programs as $program)
                        @include('livewire.resource-library.program-card', ['program' => $program, 'viewMode' => 'grid'])
                    @endforeach
                </div>
            @elseif($viewMode === 'list')
                <div class="space-y-2">
                    @foreach($programs as $program)
                        @include('livewire.resource-library.program-card', ['program' => $program, 'viewMode' => 'list'])
                    @endforeach
                </div>
            @else
                @include('livewire.resource-library.programs-table', ['programs' => $programs])
            @endif

            @if($programs->hasPages())
            <div class="mt-4">
                {{ $programs->links() }}
            </div>
            @endif
        @endif

        {{-- Courses Tab --}}
        @if($activeTab === 'courses')
            @if($miniCourses->isEmpty())
                @include('livewire.resource-library.empty-state', ['message' => 'No mini-courses found.'])
            @elseif($viewMode === 'grid')
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                    @foreach($miniCourses as $course)
                        @include('livewire.resource-library.course-card', ['course' => $course, 'viewMode' => 'grid'])
                    @endforeach
                </div>
            @elseif($viewMode === 'list')
                <div class="space-y-2">
                    @foreach($miniCourses as $course)
                        @include('livewire.resource-library.course-card', ['course' => $course, 'viewMode' => 'list'])
                    @endforeach
                </div>
            @else
                @include('livewire.resource-library.courses-table', ['courses' => $miniCourses])
            @endif

            @if($miniCourses->hasPages())
            <div class="mt-4">
                {{ $miniCourses->links() }}
            </div>
            @endif
        @endif
    </div>
</div>
