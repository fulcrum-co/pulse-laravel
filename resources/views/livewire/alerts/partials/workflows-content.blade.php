{{-- Workflows Tab Content --}}

<!-- Empty State -->
@if($workflows->isEmpty())
    <x-card>
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-gradient-to-br from-pulse-orange-100 to-pulse-purple-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                <x-icon name="bell-alert" class="w-8 h-8 text-pulse-orange-500" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">No alerts yet</h3>
            <p class="text-gray-500 max-w-sm mx-auto text-sm">
                Set up automated alerts to notify staff when conditions are met.
            </p>
        </div>
    </x-card>

<!-- Grid View -->
@elseif($viewMode === 'grid')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($workflows as $workflow)
            @php
                $statusColor = match($workflow->status) {
                    'active' => 'green',
                    'paused' => 'yellow',
                    'draft' => 'gray',
                    'archived' => 'red',
                    default => 'gray',
                };
            @endphp
            <div class="bg-white rounded-lg border {{ in_array((string)$workflow->id, $selected) ? 'border-pulse-orange-300 ring-2 ring-pulse-orange-100' : 'border-gray-200' }} overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <input
                                type="checkbox"
                                wire:click="toggleSelect('{{ $workflow->id }}')"
                                {{ in_array((string)$workflow->id, $selected) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                            />
                            <h3 class="font-medium text-gray-900 text-sm truncate">{{ $workflow->name }}</h3>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800 flex-shrink-0">
                            {{ ucfirst($workflow->status) }}
                        </span>
                    </div>

                    <div class="flex items-center gap-1.5 text-xs text-gray-500 mb-3">
                        <x-icon name="bolt" class="w-3.5 h-3.5" />
                        <span>{{ $triggerTypes[$workflow->trigger_type] ?? 'Unknown' }}</span>
                    </div>

                    <div class="flex items-center justify-between text-xs mb-3">
                        <div>
                            <span class="font-semibold text-gray-900">{{ number_format($workflow->execution_count) }}</span>
                            <span class="text-gray-500 ml-1">runs</span>
                        </div>
                        <div class="text-gray-500">
                            {{ $workflow->last_triggered_at ? $workflow->last_triggered_at->diffForHumans(null, true) : 'Never' }}
                        </div>
                    </div>

                    @if($workflow->mode === 'advanced')
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">
                            <x-icon name="squares-2x2" class="w-3 h-3 mr-0.5" />
                            Canvas
                        </span>
                    @endif
                </div>

                <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        <div class="relative group">
                            <button wire:click="toggleStatus('{{ $workflow->id }}')" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="{{ $workflow->status === 'active' ? 'pause' : 'play' }}" class="w-3.5 h-3.5" />
                            </button>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">{{ $workflow->status === 'active' ? 'Pause' : 'Activate' }}</span>
                        </div>
                        <div class="relative group">
                            <button wire:click="testTrigger('{{ $workflow->id }}')" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="beaker" class="w-3.5 h-3.5" />
                            </button>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Test</span>
                        </div>
                        <div class="relative group">
                            <button wire:click="duplicate('{{ $workflow->id }}')" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                <x-icon name="document-duplicate" class="w-3.5 h-3.5" />
                            </button>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Duplicate</span>
                        </div>
                        <div class="relative group">
                            <button wire:click="confirmDelete('{{ $workflow->id }}')" class="p-1.5 text-gray-400 hover:text-red-500 rounded">
                                <x-icon name="trash" class="w-3.5 h-3.5" />
                            </button>
                            <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Delete</span>
                        </div>
                    </div>
                    <a href="{{ route('alerts.edit', $workflow) }}" class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                        Edit
                    </a>
                </div>
            </div>
        @endforeach
    </div>

<!-- List View -->
@elseif($viewMode === 'list')
    <div class="space-y-2">
        @foreach($workflows as $workflow)
            @php
                $statusColor = match($workflow->status) {
                    'active' => 'green',
                    'paused' => 'yellow',
                    'draft' => 'gray',
                    'archived' => 'red',
                    default => 'gray',
                };
            @endphp
            <div class="bg-white rounded-lg border {{ in_array((string)$workflow->id, $selected) ? 'border-pulse-orange-300 ring-2 ring-pulse-orange-100' : 'border-gray-200' }} p-3 hover:shadow-sm transition-shadow flex items-center gap-4">
                <input
                    type="checkbox"
                    wire:click="toggleSelect('{{ $workflow->id }}')"
                    {{ in_array((string)$workflow->id, $selected) ? 'checked' : '' }}
                    class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500 flex-shrink-0"
                />
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-medium text-gray-900 text-sm truncate">{{ $workflow->name }}</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                            {{ ucfirst($workflow->status) }}
                        </span>
                        @if($workflow->mode === 'advanced')
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">Canvas</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                        <span class="flex items-center gap-1">
                            <x-icon name="bolt" class="w-3 h-3" />
                            {{ $triggerTypes[$workflow->trigger_type] ?? 'Unknown' }}
                        </span>
                        <span>{{ number_format($workflow->execution_count) }} runs</span>
                        <span>{{ $workflow->last_triggered_at ? 'Last: ' . $workflow->last_triggered_at->diffForHumans() : 'Never triggered' }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-1">
                    <div class="relative group">
                        <button wire:click="toggleStatus('{{ $workflow->id }}')" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                            <x-icon name="{{ $workflow->status === 'active' ? 'pause' : 'play' }}" class="w-4 h-4" />
                        </button>
                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">{{ $workflow->status === 'active' ? 'Pause' : 'Activate' }}</span>
                    </div>
                    <div class="relative group">
                        <button wire:click="testTrigger('{{ $workflow->id }}')" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                            <x-icon name="beaker" class="w-4 h-4" />
                        </button>
                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Test</span>
                    </div>
                    <div class="relative group">
                        <button wire:click="duplicate('{{ $workflow->id }}')" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                            <x-icon name="document-duplicate" class="w-4 h-4" />
                        </button>
                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Duplicate</span>
                    </div>
                    <div class="relative group">
                        <button wire:click="confirmDelete('{{ $workflow->id }}')" class="p-1.5 text-gray-400 hover:text-red-500 rounded">
                            <x-icon name="trash" class="w-4 h-4" />
                        </button>
                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">Delete</span>
                    </div>
                    <a href="{{ route('alerts.edit', $workflow) }}" class="ml-2 px-3 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
                        Edit
                    </a>
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
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Trigger</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Runs</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last Run</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($workflows as $workflow)
                    @php
                        $statusColor = match($workflow->status) {
                            'active' => 'green',
                            'paused' => 'yellow',
                            'draft' => 'gray',
                            'archived' => 'red',
                            default => 'gray',
                        };
                    @endphp
                    <tr class="hover:bg-gray-50 {{ in_array((string)$workflow->id, $selected) ? 'bg-pulse-orange-50' : '' }}">
                        <td class="px-4 py-2">
                            <input
                                type="checkbox"
                                wire:click="toggleSelect('{{ $workflow->id }}')"
                                {{ in_array((string)$workflow->id, $selected) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                            />
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium text-gray-900">{{ $workflow->name }}</span>
                                @if($workflow->mode === 'advanced')
                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">Canvas</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                            {{ $triggerTypes[$workflow->trigger_type] ?? 'Unknown' }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                {{ ucfirst($workflow->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                            {{ number_format($workflow->execution_count) }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-500">
                            {{ $workflow->last_triggered_at ? $workflow->last_triggered_at->diffForHumans() : 'Never' }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-1">
                                <div class="relative group">
                                    <button wire:click="toggleStatus('{{ $workflow->id }}')" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                        <x-icon name="{{ $workflow->status === 'active' ? 'pause' : 'play' }}" class="w-4 h-4" />
                                    </button>
                                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">{{ $workflow->status === 'active' ? 'Pause' : 'Activate' }}</span>
                                </div>
                                <div class="relative group">
                                    <button wire:click="testTrigger('{{ $workflow->id }}')" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                        <x-icon name="beaker" class="w-4 h-4" />
                                    </button>
                                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">Test</span>
                                </div>
                                <div class="relative group">
                                    <button wire:click="duplicate('{{ $workflow->id }}')" class="p-1 text-gray-400 hover:text-gray-600 rounded">
                                        <x-icon name="document-duplicate" class="w-4 h-4" />
                                    </button>
                                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">Duplicate</span>
                                </div>
                                <div class="relative group">
                                    <button wire:click="confirmDelete('{{ $workflow->id }}')" class="p-1 text-gray-400 hover:text-red-500 rounded">
                                        <x-icon name="trash" class="w-4 h-4" />
                                    </button>
                                    <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none z-10">Delete</span>
                                </div>
                                <a href="{{ route('alerts.edit', $workflow) }}" class="ml-1 px-2 py-1 text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                    Edit
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
@if($workflows->hasPages())
    <div class="mt-4">
        {{ $workflows->links() }}
    </div>
@endif
