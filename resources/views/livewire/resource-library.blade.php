<div class="space-y-4">
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

    <!-- Add Resource Modal -->
    @if($showAddModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeAddModal"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Add Resource</h3>
                    <button wire:click="closeAddModal" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                        <x-icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>

                <!-- Resource Type Selector -->
                <div class="mb-6">
                    <div class="flex gap-2">
                        <button
                            type="button"
                            wire:click="setAddResourceType('resource')"
                            class="flex-1 p-3 rounded-lg border-2 text-center transition-all
                                {{ $addResourceType === 'resource' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                        >
                            <x-icon name="document-text" class="w-5 h-5 mx-auto mb-1 {{ $addResourceType === 'resource' ? 'text-pulse-orange-600' : 'text-gray-400' }}" />
                            <span class="text-sm font-medium {{ $addResourceType === 'resource' ? 'text-pulse-orange-600' : 'text-gray-700' }}">Content</span>
                        </button>
                        <button
                            type="button"
                            wire:click="setAddResourceType('provider')"
                            class="flex-1 p-3 rounded-lg border-2 text-center transition-all
                                {{ $addResourceType === 'provider' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                        >
                            <x-icon name="user" class="w-5 h-5 mx-auto mb-1 {{ $addResourceType === 'provider' ? 'text-pulse-orange-600' : 'text-gray-400' }}" />
                            <span class="text-sm font-medium {{ $addResourceType === 'provider' ? 'text-pulse-orange-600' : 'text-gray-700' }}">Provider</span>
                        </button>
                        <button
                            type="button"
                            wire:click="setAddResourceType('program')"
                            class="flex-1 p-3 rounded-lg border-2 text-center transition-all
                                {{ $addResourceType === 'program' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                        >
                            <x-icon name="building-office" class="w-5 h-5 mx-auto mb-1 {{ $addResourceType === 'program' ? 'text-pulse-orange-600' : 'text-gray-400' }}" />
                            <span class="text-sm font-medium {{ $addResourceType === 'program' ? 'text-pulse-orange-600' : 'text-gray-700' }}">Program</span>
                        </button>
                        <a
                            href="{{ route('resources.courses.create') }}"
                            class="flex-1 p-3 rounded-lg border-2 border-gray-200 hover:border-gray-300 text-center"
                        >
                            <x-icon name="academic-cap" class="w-5 h-5 mx-auto mb-1 text-gray-400" />
                            <span class="text-sm font-medium text-gray-700">Course</span>
                        </a>
                    </div>
                </div>

                <!-- Resource Form -->
                @if($addResourceType === 'resource')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                        <input
                            type="text"
                            wire:model="resourceTitle"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="Resource title"
                        />
                        @error('resourceTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select
                            wire:model="resourceTypeField"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                            @foreach($this->resourceTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            wire:model="resourceDescription"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="Brief description..."
                        ></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                            <input
                                type="text"
                                wire:model="resourceCategory"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="e.g., Wellness"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Duration (min)</label>
                            <input
                                type="number"
                                wire:model="resourceDuration"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="e.g., 15"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">URL (optional)</label>
                        <input
                            type="url"
                            wire:model="resourceUrl"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="https://..."
                        />
                    </div>
                </div>
                @endif

                <!-- Provider Form -->
                @if($addResourceType === 'provider')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input
                            type="text"
                            wire:model="providerName"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="Provider name"
                        />
                        @error('providerName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select
                            wire:model="providerTypeField"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                            @foreach($this->providerTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bio</label>
                        <textarea
                            wire:model="providerBio"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="Brief bio..."
                        ></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input
                                type="email"
                                wire:model="providerEmail"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="email@example.com"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input
                                type="tel"
                                wire:model="providerPhone"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="(555) 123-4567"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                wire:model="providerServesRemote"
                                class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                            />
                            <span class="text-sm text-gray-700">Serves remote/virtual clients</span>
                        </label>
                    </div>
                </div>
                @endif

                <!-- Program Form -->
                @if($addResourceType === 'program')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                        <input
                            type="text"
                            wire:model="programName"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="Program name"
                        />
                        @error('programName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                        <select
                            wire:model="programTypeField"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                        >
                            @foreach($this->programTypes as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                        <textarea
                            wire:model="programDescription"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="Brief description..."
                        ></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Duration (weeks)</label>
                            <input
                                type="number"
                                wire:model="programDurationWeeks"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="e.g., 8"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Capacity</label>
                            <input
                                type="number"
                                wire:model="programCapacity"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="Max participants"
                            />
                        </div>
                    </div>
                </div>
                @endif

                <div class="mt-6 flex justify-end gap-3">
                    <button
                        type="button"
                        wire:click="closeAddModal"
                        class="px-4 py-2 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="saveResource"
                        class="px-4 py-2 text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                    >
                        Add {{ ucfirst($addResourceType) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
