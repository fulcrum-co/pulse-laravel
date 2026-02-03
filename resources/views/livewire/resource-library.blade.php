{{-- Updated: Resource Library with Sidebar Layout --}}
<div class="flex gap-6">
    {{-- Left Sidebar --}}
    <div class="w-64 flex-shrink-0">
        {{-- Search --}}
        <div class="relative mb-6">
            <x-icon name="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="@term('search_resources_placeholder')"
                class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
        </div>

        {{-- Type Filter (contextual based on tab) --}}
        @if($activeTab === 'content')
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">@term('type_label')</h3>
                    @if($filterType)
                        <button wire:click="$set('filterType', '')" class="text-xs text-pulse-orange-600 hover:text-pulse-orange-700">@term('clear_action')</button>
                    @endif
                </div>
                <div class="space-y-1">
                    @foreach($this->resourceTypes as $value => $label)
                        <label class="flex items-center gap-2 py-1 cursor-pointer">
                            <input type="radio" wire:model.live="filterType" value="{{ $value }}" class="text-pulse-orange-500 focus:ring-pulse-orange-500">
                            <x-icon name="document-text" class="w-4 h-4 text-gray-400" />
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">@term('category_label')</h3>
                    @if($filterCategory)
                        <button wire:click="$set('filterCategory', '')" class="text-xs text-pulse-orange-600 hover:text-pulse-orange-700">@term('clear_action')</button>
                    @endif
                </div>
                <div class="space-y-1">
                    @foreach($this->categories as $category)
                        <label class="flex items-center gap-2 py-1 cursor-pointer">
                            <input type="radio" wire:model.live="filterCategory" value="{{ $category }}" class="text-pulse-orange-500 focus:ring-pulse-orange-500">
                            <x-icon name="folder" class="w-4 h-4 text-gray-400" />
                            <span class="text-sm text-gray-700">{{ ucfirst($category) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if($activeTab === 'providers')
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">@term('provider_type_label')</h3>
                    @if($filterType)
                        <button wire:click="$set('filterType', '')" class="text-xs text-pulse-orange-600 hover:text-pulse-orange-700">@term('clear_action')</button>
                    @endif
                </div>
                <div class="space-y-1">
                    @foreach($this->providerTypes as $value => $label)
                        <label class="flex items-center gap-2 py-1 cursor-pointer">
                            <input type="radio" wire:model.live="filterType" value="{{ $value }}" class="text-pulse-orange-500 focus:ring-pulse-orange-500">
                            <x-icon name="user" class="w-4 h-4 text-gray-400" />
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if($activeTab === 'programs')
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">@term('program_type_label')</h3>
                    @if($filterType)
                        <button wire:click="$set('filterType', '')" class="text-xs text-pulse-orange-600 hover:text-pulse-orange-700">@term('clear_action')</button>
                    @endif
                </div>
                <div class="space-y-1">
                    @foreach($this->programTypes as $value => $label)
                        <label class="flex items-center gap-2 py-1 cursor-pointer">
                            <input type="radio" wire:model.live="filterType" value="{{ $value }}" class="text-pulse-orange-500 focus:ring-pulse-orange-500">
                            <x-icon name="building-office" class="w-4 h-4 text-gray-400" />
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        @if($activeTab === 'courses')
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">@term('course_type_label')</h3>
                    @if($filterType)
                        <button wire:click="$set('filterType', '')" class="text-xs text-pulse-orange-600 hover:text-pulse-orange-700">@term('clear_action')</button>
                    @endif
                </div>
                <div class="space-y-1">
                    @foreach($this->courseTypes as $value => $label)
                        <label class="flex items-center gap-2 py-1 cursor-pointer">
                            <input type="radio" wire:model.live="filterType" value="{{ $value }}" class="text-pulse-orange-500 focus:ring-pulse-orange-500">
                            <x-icon name="academic-cap" class="w-4 h-4 text-gray-400" />
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Moderation Access (for moderators) --}}
        @if($canModerate)
            <div class="pt-4 border-t border-gray-200">
                <h3 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">@term('moderation_label')</h3>
                <a
                    href="{{ route('admin.moderation') }}"
                    class="flex items-center gap-2 py-2 px-3 text-sm font-medium text-pulse-orange-600 bg-pulse-orange-50 hover:bg-pulse-orange-100 rounded-lg transition-colors"
                >
                    <x-icon name="shield-check" class="w-4 h-4" />
                    <span>@term('review_queue_label')</span>
                    @if($moderationCount > 0)
                        <span class="ml-auto inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-pulse-orange-500 rounded-full">{{ $moderationCount > 99 ? '99+' : $moderationCount }}</span>
                    @endif
                </a>
            </div>
        @endif
    </div>

    {{-- Main Content --}}
    <div class="flex-1 min-w-0">
        {{-- Tabs --}}
        <div class="border-b border-gray-200 mb-4">
            <nav class="-mb-px flex gap-6 overflow-x-auto" aria-label="Tabs">
                @foreach([
                    'all' => ['label' => app(\App\Services\TerminologyService::class)->get('all_label'), 'count' => $counts['resources'] + $counts['providers'] + $counts['programs'] + $counts['courses']],
                    'content' => ['label' => app(\App\Services\TerminologyService::class)->get('content_singular'), 'count' => $counts['resources']],
                    'providers' => ['label' => app(\App\Services\TerminologyService::class)->get('provider_plural'), 'count' => $counts['providers']],
                    'programs' => ['label' => app(\App\Services\TerminologyService::class)->get('program_plural'), 'count' => $counts['programs']],
                    'courses' => ['label' => app(\App\Services\TerminologyService::class)->get('course_plural'), 'count' => $counts['courses']],
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

        {{-- View Toggle --}}
        <div class="flex items-center justify-end mb-4">
            <div class="flex items-center border border-gray-200 rounded-lg overflow-hidden">
                <button
                    wire:click="setViewMode('grid')"
                    class="p-2 {{ $viewMode === 'grid' ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}"
                    title="@term('grid_view_label')"
                >
                    <x-icon name="squares-2x2" class="w-4 h-4" />
                </button>
                <button
                    wire:click="setViewMode('list')"
                    class="p-2 border-l border-gray-200 {{ $viewMode === 'list' ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}"
                    title="@term('list_view_label')"
                >
                    <x-icon name="list-bullet" class="w-4 h-4" />
                </button>
                <button
                    wire:click="setViewMode('table')"
                    class="p-2 border-l border-gray-200 {{ $viewMode === 'table' ? 'bg-pulse-orange-50 text-pulse-orange-600' : 'text-gray-400 hover:text-gray-600 hover:bg-gray-50' }}"
                    title="@term('table_view_label')"
                >
                    <x-icon name="table-cells" class="w-4 h-4" />
                </button>
            </div>
        </div>

        {{-- Content Area --}}
        <div>
            {{-- All Tab --}}
            @if($activeTab === 'all')
                @if($allItems->isEmpty())
                    @include('livewire.resource-library.empty-state', ['message' => app(\App\Services\TerminologyService::class)->get('no_resources_found_help_label')])
                @elseif($viewMode === 'grid')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                    @include('livewire.resource-library.empty-state', ['message' => app(\App\Services\TerminologyService::class)->get('no_content_resources_found_label')])
                @elseif($viewMode === 'grid')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                    @include('livewire.resource-library.empty-state', ['message' => app(\App\Services\TerminologyService::class)->get('no_providers_found_label')])
                @elseif($viewMode === 'grid')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                    @include('livewire.resource-library.empty-state', ['message' => app(\App\Services\TerminologyService::class)->get('no_programs_found_label')])
                @elseif($viewMode === 'grid')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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
                    @include('livewire.resource-library.empty-state', ['message' => app(\App\Services\TerminologyService::class)->get('no_mini_courses_found_label')])
                @elseif($viewMode === 'grid')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
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

    {{-- Add Resource Modal --}}
    @if($showAddModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="closeAddModal"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">@term('add_resource_label')</h3>
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
                            <span class="text-sm font-medium {{ $addResourceType === 'resource' ? 'text-pulse-orange-600' : 'text-gray-700' }}">@term('content_singular')</span>
                        </button>
                        <button
                            type="button"
                            wire:click="setAddResourceType('provider')"
                            class="flex-1 p-3 rounded-lg border-2 text-center transition-all
                                {{ $addResourceType === 'provider' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                        >
                            <x-icon name="user" class="w-5 h-5 mx-auto mb-1 {{ $addResourceType === 'provider' ? 'text-pulse-orange-600' : 'text-gray-400' }}" />
                            <span class="text-sm font-medium {{ $addResourceType === 'provider' ? 'text-pulse-orange-600' : 'text-gray-700' }}">@term('provider_singular')</span>
                        </button>
                        <button
                            type="button"
                            wire:click="setAddResourceType('program')"
                            class="flex-1 p-3 rounded-lg border-2 text-center transition-all
                                {{ $addResourceType === 'program' ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                        >
                            <x-icon name="building-office" class="w-5 h-5 mx-auto mb-1 {{ $addResourceType === 'program' ? 'text-pulse-orange-600' : 'text-gray-400' }}" />
                            <span class="text-sm font-medium {{ $addResourceType === 'program' ? 'text-pulse-orange-600' : 'text-gray-700' }}">@term('program_singular')</span>
                        </button>
                        <a
                            href="{{ route('resources.courses.create') }}"
                            class="flex-1 p-3 rounded-lg border-2 border-gray-200 hover:border-gray-300 text-center"
                        >
                            <x-icon name="academic-cap" class="w-5 h-5 mx-auto mb-1 text-gray-400" />
                            <span class="text-sm font-medium text-gray-700">@term('course_singular')</span>
                        </a>
                    </div>
                </div>

                <!-- Resource Form -->
                @if($addResourceType === 'resource')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('title_label')</label>
                        <input
                            type="text"
                            wire:model="resourceTitle"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="@term('resource_title_placeholder')"
                        />
                        @error('resourceTitle') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('type_label')</label>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('description_label')</label>
                        <textarea
                            wire:model="resourceDescription"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="@term('brief_description_placeholder')"
                        ></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('category_label')</label>
                            <input
                                type="text"
                                wire:model="resourceCategory"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="@term('category_placeholder')"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('duration_minutes_label')</label>
                            <input
                                type="number"
                                wire:model="resourceDuration"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="@term('duration_minutes_placeholder')"
                            />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('url_optional_label')</label>
                        <input
                            type="url"
                            wire:model="resourceUrl"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="https://..."
                        />
                    </div>
                    <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('file_upload_optional_label')</label>
                        <div class="mt-1">
                            <input
                                type="file"
                                wire:model="resourceFile"
                                class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-lg file:border-0
                                    file:text-sm file:font-medium
                                    file:bg-pulse-orange-50 file:text-pulse-orange-600
                                    hover:file:bg-pulse-orange-100
                                    cursor-pointer border border-gray-300 rounded-lg"
                            />
                            <div wire:loading wire:target="resourceFile" class="mt-2 text-sm text-pulse-orange-600">
                                <x-icon name="arrow-path" class="w-4 h-4 inline animate-spin" /> @term('uploading_label')
                            </div>
                            @if($resourceFile)
                            <p class="mt-2 text-sm text-green-600">
                                <x-icon name="check-circle" class="w-4 h-4 inline" />
                                {{ $resourceFile->getClientOriginalName() }}
                            </p>
                            @endif
                            @error('resourceFile') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            @term('file_upload_help_label')
                        </p>
                    </div>
                </div>
                @endif

                <!-- Provider Form -->
                @if($addResourceType === 'provider')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('name_label')</label>
                        <input
                            type="text"
                            wire:model="providerName"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="@term('provider_name_placeholder')"
                        />
                        @error('providerName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('type_label')</label>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('bio_label')</label>
                        <textarea
                            wire:model="providerBio"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="@term('brief_bio_placeholder')"
                        ></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('email_label')</label>
                            <input
                                type="email"
                                wire:model="providerEmail"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="@term('email_placeholder')"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('phone_label')</label>
                            <input
                                type="tel"
                                wire:model="providerPhone"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="@term('phone_placeholder')"
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
                            <span class="text-sm text-gray-700">@term('serves_remote_clients_label')</span>
                        </label>
                    </div>
                </div>
                @endif

                <!-- Program Form -->
                @if($addResourceType === 'program')
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('name_label')</label>
                        <input
                            type="text"
                            wire:model="programName"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="@term('program_name_placeholder')"
                        />
                        @error('programName') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('type_label')</label>
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">@term('description_label')</label>
                        <textarea
                            wire:model="programDescription"
                            rows="2"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                            placeholder="@term('brief_description_placeholder')"
                        ></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('duration_weeks_label')</label>
                            <input
                                type="number"
                                wire:model="programDurationWeeks"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="@term('duration_weeks_placeholder')"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">@term('capacity_label')</label>
                            <input
                                type="number"
                                wire:model="programCapacity"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                placeholder="@term('capacity_placeholder')"
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
                        @term('cancel_action')
                    </button>
                    <button
                        type="button"
                        wire:click="saveResource"
                        class="px-4 py-2 text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                    >
                        @term('add_action') {{ ucfirst($addResourceType) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
