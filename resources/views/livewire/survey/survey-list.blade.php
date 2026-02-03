<div class="space-y-4">
    <!-- Search, Filters & View Toggle -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 flex-1">
            <div class="relative w-full sm:w-64">
                <x-icon name="search" class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input
                    type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ app(\App\Services\TerminologyService::class)->get('search_action') }} {{ strtolower(app(\App\Services\TerminologyService::class)->get('survey_plural')) }}..."
                    class="w-full pl-9 pr-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
                />
            </div>

            <select
                wire:model.live="statusFilter"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="">{{ app(\App\Services\TerminologyService::class)->get('all_label') }} {{ app(\App\Services\TerminologyService::class)->get('status_label') }}es</option>
                @foreach($statuses as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            <select
                wire:model.live="typeFilter"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="">{{ app(\App\Services\TerminologyService::class)->get('all_label') }} {{ app(\App\Services\TerminologyService::class)->get('type_label') }}s</option>
                @foreach($surveyTypes as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>

            @if($isAdmin && $accessibleOrgs->count() > 1)
            <select
                wire:model.live="orgFilter"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-pulse-orange-500 focus:border-pulse-orange-500"
            >
                <option value="">{{ app(\App\Services\TerminologyService::class)->get('all_label') }} {{ app(\App\Services\TerminologyService::class)->get('organization_plural') }}</option>
                @foreach($accessibleOrgs as $org)
                    <option value="{{ $org->id }}">{{ $org->org_name }}</option>
                @endforeach
            </select>
            @endif

            @if($search || $statusFilter || $typeFilter || $orgFilter)
            <button
                wire:click="clearFilters"
                class="text-sm text-gray-500 hover:text-gray-700"
            >
                @term('clear_label')
            </button>
            @endif
        </div>

        <!-- View Toggle -->
        <div class="flex items-center gap-3">
            @if(count($selected) > 0)
                <div class="flex items-center gap-2 bg-red-50 border border-red-200 rounded-lg px-3 py-1.5">
                    <span class="text-sm text-red-700">{{ count($selected) }} @term('selected_label')</span>
                    <button wire:click="deselectAll" class="text-xs text-red-600 hover:text-red-800 underline">@term('clear_label')</button>
                    <button wire:click="confirmBulkDelete" class="ml-2 px-2 py-1 text-xs font-medium text-white bg-red-600 rounded hover:bg-red-700">
                        @term('delete_action') @term('selected_label')
                    </button>
                </div>
            @else
                <button wire:click="selectAll" class="text-xs text-gray-500 hover:text-gray-700">@term('select_action') @term('all_label')</button>
            @endif
            <div class="flex items-center gap-1 bg-gray-100 rounded-lg p-1">
                <button
                    wire:click="setViewMode('grid')"
                    class="p-1.5 rounded {{ $viewMode === 'grid' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                    title="{{ app(\App\Services\TerminologyService::class)->get('grid_view_label') }}"
                >
                    <x-icon name="squares-2x2" class="w-4 h-4" />
                </button>
                <button
                    wire:click="setViewMode('list')"
                    class="p-1.5 rounded {{ $viewMode === 'list' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                    title="{{ app(\App\Services\TerminologyService::class)->get('list_view_label') }}"
                >
                    <x-icon name="list-bullet" class="w-4 h-4" />
                </button>
                <button
                    wire:click="setViewMode('table')"
                    class="p-1.5 rounded {{ $viewMode === 'table' ? 'bg-white shadow-sm text-gray-900' : 'text-gray-500 hover:text-gray-700' }}"
                    title="{{ app(\App\Services\TerminologyService::class)->get('table_view_label') }}"
                >
                    <x-icon name="table-cells" class="w-4 h-4" />
                </button>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    @if($surveys->isEmpty())
        <x-card>
            <div class="text-center py-12">
                <div class="w-16 h-16 bg-gradient-to-br from-pulse-orange-100 to-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                    <x-icon name="clipboard-document-list" class="w-8 h-8 text-pulse-orange-500" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mb-1">@term('survey_empty_title')</h3>
                <p class="text-gray-500 mb-4 max-w-sm mx-auto text-sm">
                    @term('survey_empty_body')
                </p>
                <a href="{{ route('surveys.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                    <x-icon name="plus" class="w-4 h-4 mr-1" />
                    @term('create_action') @term('survey_singular')
                </a>
            </div>
        </x-card>

    <!-- Grid View -->
    @elseif($viewMode === 'grid')
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach($surveys as $survey)
                @php
                    $statusColor = match($survey->status) {
                        'active' => 'green',
                        'paused' => 'yellow',
                        'draft' => 'gray',
                        'completed' => 'blue',
                        'archived' => 'red',
                        default => 'gray',
                    };
                    $typeColor = match($survey->survey_type) {
                        'wellness' => 'green',
                        'academic' => 'blue',
                        'behavioral' => 'orange',
                        default => 'purple',
                    };
                @endphp
                <div class="bg-white rounded-lg border {{ in_array((string)$survey->id, $selected) ? 'border-pulse-orange-300 ring-2 ring-pulse-orange-100' : 'border-gray-200' }} overflow-hidden hover:shadow-md transition-shadow relative group">
                    <!-- Clickable overlay for the card -->
                    <a href="{{ route('surveys.show', $survey) }}" class="absolute inset-0 z-0" aria-label="View {{ $survey->title }}"></a>

                    <div class="p-4 relative z-10 pointer-events-none">
                        <div class="flex items-start justify-between gap-2 mb-3">
                            <div class="flex items-center gap-2 min-w-0">
                                <input
                                    type="checkbox"
                                    wire:click="toggleSelect('{{ $survey->id }}')"
                                    {{ in_array((string)$survey->id, $selected) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500 flex-shrink-0 pointer-events-auto"
                                    onclick="event.stopPropagation()"
                                />
                                <h3 class="font-medium text-gray-900 text-sm truncate group-hover:text-pulse-orange-600 transition-colors">{{ $survey->title }}</h3>
                            </div>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 flex-shrink-0">
                                {{ ucfirst($survey->status) }}
                            </span>
                        </div>

                        <div class="flex items-center gap-2 text-xs mb-3">
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $typeColor }}-100 text-{{ $typeColor }}-700">
                                {{ ucfirst($survey->survey_type) }}
                            </span>
                            <span class="text-gray-500">{{ $survey->question_count }} @term('questions_label')</span>
                        </div>

                        <div class="flex items-center justify-between text-xs mb-3">
                            <div>
                                <span class="font-semibold text-gray-900">{{ number_format($survey->completed_attempts_count ?? 0) }}</span>
                                <span class="text-gray-500 ml-1">@term('responses_label')</span>
                            </div>
                            <div class="text-gray-500">
                                {{ $survey->created_at->diffForHumans(null, true) }}
                            </div>
                        </div>

                        @if($survey->creation_mode === 'ai_assisted' || $survey->creation_mode === 'chat')
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">
                                <x-icon name="sparkles" class="w-3 h-3 mr-0.5" />
                                @term('ai_created_label')
                            </span>
                        @endif
                    </div>

                    <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-1 pointer-events-auto">
                            <div class="relative group/btn">
                                <button wire:click="toggleStatus('{{ $survey->id }}')" class="p-1.5 text-gray-400 hover:text-gray-600 rounded" onclick="event.stopPropagation()">
                                    <x-icon name="{{ $survey->status === 'active' ? 'pause' : 'play' }}" class="w-3.5 h-3.5" />
                                </button>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover/btn:opacity-100 transition-opacity pointer-events-none">{{ $survey->status === 'active' ? app(\App\Services\TerminologyService::class)->get('pause_action') : app(\App\Services\TerminologyService::class)->get('activate_action') }}</span>
                            </div>
                            @if($survey->status === 'active')
                            <div class="relative group/btn">
                                <a href="{{ route('surveys.deliver.form', $survey) }}" class="p-1.5 text-gray-400 hover:text-gray-600 rounded inline-block" onclick="event.stopPropagation()">
                                    <x-icon name="paper-airplane" class="w-3.5 h-3.5" />
                                </a>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover/btn:opacity-100 transition-opacity pointer-events-none">@term('send_action')</span>
                            </div>
                            @endif
                            <div class="relative group/btn">
                                <button wire:click="duplicate('{{ $survey->id }}')" class="p-1.5 text-gray-400 hover:text-gray-600 rounded" onclick="event.stopPropagation()">
                                    <x-icon name="document-duplicate" class="w-3.5 h-3.5" />
                                </button>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover/btn:opacity-100 transition-opacity pointer-events-none">@term('duplicate_action')</span>
                            </div>
                            @if($canPush)
                            <div class="relative group/btn">
                                <button wire:click="openPushModal({{ $survey->id }})" class="p-1.5 text-gray-400 hover:text-pulse-orange-500 rounded" onclick="event.stopPropagation()">
                                    <x-icon name="arrow-up-on-square" class="w-3.5 h-3.5" />
                                </button>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover/btn:opacity-100 transition-opacity pointer-events-none">@term('push_label') to @term('organization_plural')</span>
                            </div>
                            @endif
                            <div class="relative group/btn">
                                <button wire:click="confirmDelete('{{ $survey->id }}')" class="p-1.5 text-gray-400 hover:text-red-500 rounded" onclick="event.stopPropagation()">
                                    <x-icon name="trash" class="w-3.5 h-3.5" />
                                </button>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover/btn:opacity-100 transition-opacity pointer-events-none">@term('delete_action')</span>
                            </div>
                        </div>
                        <span class="text-xs font-medium text-pulse-orange-600 pointer-events-none">
                            @term('view_action') →
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

    <!-- List View -->
    @elseif($viewMode === 'list')
        <div class="space-y-2">
            @foreach($surveys as $survey)
                @php
                    $statusColor = match($survey->status) {
                        'active' => 'green',
                        'paused' => 'yellow',
                        'draft' => 'gray',
                        'completed' => 'blue',
                        'archived' => 'red',
                        default => 'gray',
                    };
                    $typeColor = match($survey->survey_type) {
                        'wellness' => 'green',
                        'academic' => 'blue',
                        'behavioral' => 'orange',
                        default => 'purple',
                    };
                @endphp
                <div class="bg-white rounded-lg border {{ in_array((string)$survey->id, $selected) ? 'border-pulse-orange-300 ring-2 ring-pulse-orange-100' : 'border-gray-200' }} p-3 hover:shadow-sm transition-shadow flex items-center gap-4 relative group cursor-pointer"
                     onclick="window.location='{{ route('surveys.show', $survey) }}'">
                    <input
                        type="checkbox"
                        wire:click="toggleSelect('{{ $survey->id }}')"
                        {{ in_array((string)$survey->id, $selected) ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500 flex-shrink-0 relative z-10"
                        onclick="event.stopPropagation()"
                    />
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2">
                            <h3 class="font-medium text-gray-900 text-sm truncate group-hover:text-pulse-orange-600 transition-colors">{{ $survey->title }}</h3>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                {{ ucfirst($survey->status) }}
                            </span>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $typeColor }}-100 text-{{ $typeColor }}-700">
                                {{ ucfirst($survey->survey_type) }}
                            </span>
                            @if($survey->creation_mode === 'ai_assisted' || $survey->creation_mode === 'chat')
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">AI</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                            <span>{{ $survey->question_count }} questions</span>
                            <span>{{ number_format($survey->completed_attempts_count ?? 0) }} responses</span>
                            <span>Created {{ $survey->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 relative z-10" onclick="event.stopPropagation()">
                        <div class="relative group/btn">
                            <button wire:click="toggleStatus('{{ $survey->id }}')" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="{{ $survey->status === 'active' ? 'pause' : 'play' }}" class="w-4 h-4" />
                            </button>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover/btn:opacity-100 transition-opacity pointer-events-none">{{ $survey->status === 'active' ? 'Pause' : 'Activate' }}</span>
                        </div>
                        @if($survey->status === 'active')
                        <div class="relative group/btn">
                            <a href="{{ route('surveys.deliver.form', $survey) }}" class="p-1.5 text-gray-400 hover:text-gray-600 rounded inline-block">
                                <x-icon name="paper-airplane" class="w-4 h-4" />
                            </a>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover/btn:opacity-100 transition-opacity pointer-events-none">Send</span>
                        </div>
                        @endif
                        <div class="relative group/btn">
                            <button wire:click="duplicate('{{ $survey->id }}')" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="document-duplicate" class="w-4 h-4" />
                            </button>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover/btn:opacity-100 transition-opacity pointer-events-none">Duplicate</span>
                        </div>
                        @if($canPush)
                        <div class="relative group/btn">
                            <button wire:click="openPushModal({{ $survey->id }})" class="p-1.5 text-gray-400 hover:text-pulse-orange-500 rounded">
                                <x-icon name="arrow-up-on-square" class="w-4 h-4" />
                            </button>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover/btn:opacity-100 transition-opacity pointer-events-none">Push to Organizations</span>
                        </div>
                        @endif
                        <div class="relative group/btn">
                            <button wire:click="confirmDelete('{{ $survey->id }}')" class="p-1.5 text-gray-400 hover:text-red-500 rounded">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover/btn:opacity-100 transition-opacity pointer-events-none">@term('delete_action')</span>
                        </div>
                        <span class="ml-2 px-3 py-1 text-xs font-medium text-pulse-orange-600">
                            View →
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

    <!-- Table View -->
    @else
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 w-8">
                            <input
                                type="checkbox"
                                wire:click="{{ count($selected) > 0 ? 'deselectAll' : 'selectAll' }}"
                                {{ count($selected) > 0 ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                            />
                        </th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('survey_singular')</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Questions</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Responses</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($surveys as $survey)
                        @php
                            $statusColor = match($survey->status) {
                                'active' => 'green',
                                'paused' => 'yellow',
                                'draft' => 'gray',
                                'completed' => 'blue',
                                'archived' => 'red',
                                default => 'gray',
                            };
                            $typeColor = match($survey->survey_type) {
                                'wellness' => 'green',
                                'academic' => 'blue',
                                'behavioral' => 'orange',
                                default => 'purple',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 {{ in_array((string)$survey->id, $selected) ? 'bg-pulse-orange-50' : '' }}">
                            <td class="px-4 py-2">
                                <input
                                    type="checkbox"
                                    wire:click="toggleSelect('{{ $survey->id }}')"
                                    {{ in_array((string)$survey->id, $selected) ? 'checked' : '' }}
                                    class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                                />
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm font-medium text-gray-900">{{ $survey->title }}</span>
                                    @if($survey->creation_mode === 'ai_assisted' || $survey->creation_mode === 'chat')
                                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-700">AI</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-{{ $typeColor }}-100 text-{{ $typeColor }}-700">
                                    {{ ucfirst($survey->survey_type) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                    {{ ucfirst($survey->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                {{ $survey->question_count }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($survey->completed_attempts_count ?? 0) }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                                {{ $survey->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-2 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <div class="relative group">
                                        <button wire:click="toggleStatus('{{ $survey->id }}')" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                            <x-icon name="{{ $survey->status === 'active' ? 'pause' : 'play' }}" class="w-4 h-4" />
                                        </button>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">{{ $survey->status === 'active' ? 'Pause' : 'Activate' }}</span>
                                    </div>
                                    @if($survey->status === 'active')
                                    <div class="relative group">
                                        <a href="{{ route('surveys.deliver.form', $survey) }}" class="p-1 text-gray-400 hover:text-gray-600 rounded inline-block">
                                            <x-icon name="paper-airplane" class="w-4 h-4" />
                                        </a>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">Send</span>
                                    </div>
                                    @endif
                                    <div class="relative group">
                                        <button wire:click="duplicate('{{ $survey->id }}')" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                            <x-icon name="document-duplicate" class="w-4 h-4" />
                                        </button>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">Duplicate</span>
                                    </div>
                                    @if($canPush)
                                    <div class="relative group">
                                        <button wire:click="openPushModal({{ $survey->id }})" class="p-1 text-gray-400 hover:text-pulse-orange-500 rounded">
                                            <x-icon name="arrow-up-on-square" class="w-4 h-4" />
                                        </button>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">Push to Organizations</span>
                                    </div>
                                    @endif
                                    <div class="relative group">
                                        <button wire:click="confirmDelete('{{ $survey->id }}')" class="p-1 text-gray-400 hover:text-red-500 rounded">
                                            <x-icon name="trash" class="w-4 h-4" />
                                        </button>
                                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">@term('delete_action')</span>
                                    </div>
                                    <a href="{{ route('surveys.show', $survey) }}" class="ml-1 px-2 py-1 text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                        View
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <!-- Pagination -->
    @if($surveys->hasPages())
        <div class="mt-4">
            {{ $surveys->links() }}
        </div>
    @endif

    <!-- Delete Confirmation Modal -->
    @if($showDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelDelete"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100 sm:mx-0">
                        <x-icon name="exclamation-triangle" class="h-5 w-5 text-red-600" />
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-base font-medium text-gray-900">@term('delete_action') @term('survey_singular')</h3>
                        <p class="mt-1 text-sm text-gray-500">@term('delete_survey_confirm_label')</p>
                    </div>
                </div>
                <div class="mt-4 sm:flex sm:flex-row-reverse gap-2">
                    <button wire:click="deleteSurvey" class="w-full sm:w-auto px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                        @term('delete_action')
                    </button>
                    <button wire:click="cancelDelete" class="mt-2 sm:mt-0 w-full sm:w-auto px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        @term('cancel_action')
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Bulk Delete Confirmation Modal -->
    @if($showBulkDeleteModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" wire:click="cancelBulkDelete"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-sm sm:w-full sm:p-6">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100 sm:mx-0">
                        <x-icon name="exclamation-triangle" class="h-5 w-5 text-red-600" />
                    </div>
                    <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                        <h3 class="text-base font-medium text-gray-900">@term('delete_action') {{ count($selected) }} @term('survey_singular')</h3>
                        <p class="mt-1 text-sm text-gray-500">@term('delete_selected_surveys_confirm_label')</p>
                    </div>
                </div>
                <div class="mt-4 sm:flex sm:flex-row-reverse gap-2">
                    <button wire:click="deleteSelected" class="w-full sm:w-auto px-3 py-1.5 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                        @term('delete_action') @term('all_label')
                    </button>
                    <button wire:click="cancelBulkDelete" class="mt-2 sm:mt-0 w-full sm:w-auto px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        @term('cancel_action')
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Push Content Modal -->
    @if($canPush)
        <livewire:push-content-modal />
    @endif
</div>
