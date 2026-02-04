<div>
    @if($show)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="resource-picker-modal" role="dialog" aria-modal="true">
            <div class="flex items-start justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                {{-- Backdrop --}}
                <div wire:click="closeModal" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"></div>

                {{-- Modal Panel --}}
                <div class="relative bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 w-full max-w-4xl">
                    {{-- Header --}}
                    <div class="bg-gradient-to-r from-pulse-orange-500 to-pulse-orange-600 px-6 py-4 text-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <x-icon name="folder-open" class="w-6 h-6" />
                                <div>
                                    <h3 class="text-lg font-semibold">Select Resource</h3>
                                    <p class="text-sm opacity-90">Choose a resource from your library</p>
                                </div>
                            </div>
                            <button wire:click="closeModal" class="text-white/80 hover:text-white">
                                <x-icon name="x-mark" class="w-6 h-6" />
                            </button>
                        </div>
                    </div>

                    {{-- Filters Bar --}}
                    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                        <div class="flex flex-wrap items-center gap-4">
                            {{-- Search --}}
                            <div class="flex-1 min-w-[200px]">
                                <div class="relative">
                                    <x-icon name="magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
                                    <input
                                        type="text"
                                        wire:model.live.debounce.300ms="search"
                                        placeholder="Search resources..."
                                        class="w-full pl-10 pr-4 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                                    >
                                </div>
                            </div>

                            {{-- Type Filter --}}
                            <select wire:model.live="filterType" class="text-sm border border-gray-300 rounded-lg py-2 px-3 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                                <option value="">All Types</option>
                                @foreach($resourceTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>

                            {{-- Category Filter --}}
                            @if(count($categories) > 0)
                                <select wire:model.live="filterCategory" class="text-sm border border-gray-300 rounded-lg py-2 px-3 focus:ring-pulse-orange-500 focus:border-pulse-orange-500">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            @endif

                            {{-- Search Mode Toggle --}}
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input type="checkbox" wire:model.live="useSemanticSearch" class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500">
                                <x-icon name="sparkles" class="w-4 h-4 text-purple-500" />
                                Smart Search
                            </label>

                            {{-- Include Unapproved Toggle --}}
                            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                                <input type="checkbox" wire:model.live="includeUnapproved" class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500">
                                Include Unapproved
                            </label>

                            {{-- Clear Filters --}}
                            @if($search || $filterType || $filterCategory)
                                <button wire:click="resetFilters" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700">
                                    Clear filters
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Resources Grid --}}
                    <div class="px-6 py-4 max-h-[50vh] overflow-y-auto">
                        @if(method_exists($resources, 'count') && $resources->count() > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($resources as $resource)
                                    <div
                                        wire:click="selectResource({{ $resource->id }})"
                                        class="relative p-4 border rounded-lg cursor-pointer transition-all {{ $selectedResourceId === $resource->id ? 'border-pulse-orange-500 bg-pulse-orange-50 ring-2 ring-pulse-orange-500' : 'border-gray-200 hover:border-gray-300 hover:shadow-sm' }}"
                                    >
                                        {{-- Selection indicator --}}
                                        @if($selectedResourceId === $resource->id)
                                            <div class="absolute top-2 right-2">
                                                <div class="w-5 h-5 bg-pulse-orange-500 rounded-full flex items-center justify-center">
                                                    <x-icon name="check" class="w-3 h-3 text-white" />
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Resource Type Icon --}}
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                @php
                                                    $icon = match($resource->resource_type) {
                                                        'article' => 'document-text',
                                                        'video' => 'play-circle',
                                                        'worksheet' => 'clipboard-document-list',
                                                        'activity' => 'puzzle-piece',
                                                        'link' => 'link',
                                                        'document' => 'document',
                                                        'presentation' => 'presentation-chart-bar',
                                                        'audio' => 'speaker-wave',
                                                        default => 'document',
                                                    };
                                                @endphp
                                                <x-icon name="{{ $icon }}" class="w-5 h-5 text-blue-600" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="text-sm font-medium text-gray-900 truncate">{{ $resource->title }}</h4>
                                                <p class="text-xs text-gray-500 capitalize">{{ str_replace('_', ' ', $resource->resource_type) }}</p>
                                            </div>
                                        </div>

                                        @if($resource->description)
                                            <p class="mt-2 text-xs text-gray-600 line-clamp-2">{{ $resource->description }}</p>
                                        @endif

                                        <div class="mt-3 flex items-center justify-between">
                                            @if($resource->estimated_duration_minutes)
                                                <span class="text-xs text-gray-500">
                                                    <x-icon name="clock" class="w-3 h-3 inline-block mr-0.5" />
                                                    {{ $resource->estimated_duration_minutes }} min
                                                </span>
                                            @else
                                                <span></span>
                                            @endif

                                            @if(!$resource->active)
                                                <span class="px-1.5 py-0.5 text-xs font-medium bg-yellow-100 text-yellow-700 rounded">
                                                    Unapproved
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Pagination --}}
                            @if(method_exists($resources, 'hasPages') && $resources->hasPages())
                                <div class="mt-4">
                                    {{ $resources->links() }}
                                </div>
                            @endif
                        @else
                            <div class="text-center py-12">
                                <x-icon name="folder-open" class="w-12 h-12 mx-auto text-gray-300" />
                                <h3 class="mt-3 text-sm font-medium text-gray-900">No resources found</h3>
                                <p class="mt-1 text-sm text-gray-500">
                                    @if($search)
                                        Try adjusting your search terms or filters.
                                    @else
                                        Your library is empty. Add some resources first.
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <div class="text-sm text-gray-500">
                            @if($selectedResourceId)
                                @php $selected = $resources->firstWhere('id', $selectedResourceId); @endphp
                                @if($selected)
                                    Selected: <span class="font-medium text-gray-900">{{ $selected->title }}</span>
                                    @if(!$selected->active)
                                        <span class="ml-2 text-yellow-600">
                                            <x-icon name="exclamation-triangle" class="w-4 h-4 inline-block" />
                                            This resource is not yet approved
                                        </span>
                                    @endif
                                @endif
                            @else
                                Click a resource to select it
                            @endif
                        </div>
                        <div class="flex items-center gap-3">
                            <button wire:click="closeModal" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-900">
                                Cancel
                            </button>
                            <button
                                wire:click="confirmSelection"
                                @if(!$selectedResourceId) disabled @endif
                                class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 hover:bg-pulse-orange-600 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Select Resource
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
