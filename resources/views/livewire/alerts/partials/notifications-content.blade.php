{{-- Notifications Tab Content --}}

@php
    $terminology = app(\App\Services\TerminologyService::class);
    // Helper to get status config
    $getStatusConfig = function($status) use ($terminology) {
        return match($status) {
            'pending' => ['color' => 'gray', 'icon' => 'clock', 'label' => $terminology->get('pending_label')],
            'running' => ['color' => 'blue', 'icon' => 'arrow-path', 'label' => $terminology->get('execution_status_running_label')],
            'waiting' => ['color' => 'yellow', 'icon' => 'pause', 'label' => $terminology->get('execution_status_waiting_label')],
            'completed' => ['color' => 'green', 'icon' => 'check-circle', 'label' => $terminology->get('completed_label')],
            'failed' => ['color' => 'red', 'icon' => 'exclamation-circle', 'label' => $terminology->get('failed_label')],
            'cancelled' => ['color' => 'gray', 'icon' => 'x-circle', 'label' => $terminology->get('execution_status_cancelled_label')],
            default => ['color' => 'gray', 'icon' => 'question-mark-circle', 'label' => $terminology->get('unknown_label')],
        };
    };
@endphp

<!-- Empty State -->
@if($notifications->isEmpty())
    <x-card>
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-gradient-to-br from-green-100 to-emerald-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                <x-icon name="check-circle" class="w-8 h-8 text-green-500" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-1">@term('all_caught_up_label')</h3>
            <p class="text-gray-500 text-sm max-w-sm mx-auto">
                @term('no_workflow_executions_label')
            </p>
        </div>
    </x-card>

<!-- Grid View -->
@elseif($viewMode === 'grid')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($notifications as $notification)
            @php
                $statusConfig = $getStatusConfig($notification->status);
                $actionSummary = $this->getActionSummary($notification->node_results ?? []);
            @endphp
            <div class="bg-white rounded-lg border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
                <div class="p-4">
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-full bg-{{ $statusConfig['color'] }}-100 flex items-center justify-center flex-shrink-0">
                                <x-icon name="{{ $statusConfig['icon'] }}" class="w-4 h-4 text-{{ $statusConfig['color'] }}-600" />
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-medium text-gray-900 text-sm truncate">
                                    {{ $notification->workflow->name ?? $terminology->get('unknown_workflow_label') }}
                                </h3>
                                <p class="text-xs text-gray-500">
                                    {{ $notification->created_at->diffForHumans() }}
                                </p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}-800 flex-shrink-0">
                            {{ $statusConfig['label'] }}
                        </span>
                    </div>

                    <!-- Action Summary -->
                    @if(count($actionSummary) > 0)
                        <div class="text-xs text-gray-600 bg-gray-50 rounded p-2 mb-3">
                            <div class="font-medium text-gray-700 mb-1">@term('actions_taken_label'):</div>
                            <ul class="space-y-0.5">
                                @foreach($actionSummary as $action)
                                    <li class="flex items-center gap-1">
                                        <x-icon name="check" class="w-3 h-3 text-green-500" />
                                        {{ $action }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Error Message -->
                    @if($notification->error_message)
                        <p class="text-xs text-red-600 bg-red-50 rounded p-2 mb-3 line-clamp-2">
                            {{ $notification->error_message }}
                        </p>
                    @endif

                    <div class="text-xs text-gray-500">
                        <span>@term('triggered_by_label'): {{ ucfirst(str_replace('_', ' ', $notification->triggered_by ?? $terminology->get('unknown_label'))) }}</span>
                    </div>
                </div>

                <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between">
                    <div class="flex items-center gap-1">
                        @if($notification->status === 'failed')
                            <div class="relative group">
                                <button wire:click="retryExecution('{{ $notification->id }}')" class="p-1.5 text-gray-400 hover:text-blue-600 rounded" title="@term('retry_label')">
                                    <x-icon name="arrow-path" class="w-4 h-4" />
                                </button>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 px-2 py-1 text-xs text-white bg-gray-900 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">@term('retry_label')</span>
                            </div>
                        @endif
                    </div>
                    <a href="{{ route('alerts.execution', [$notification->workflow_id, $notification->id]) }}" class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                        @term('view_details_label')
                    </a>
                </div>
            </div>
        @endforeach
    </div>

<!-- List View -->
@elseif($viewMode === 'list')
    <div class="space-y-2">
        @foreach($notifications as $notification)
            @php
                $statusConfig = $getStatusConfig($notification->status);
                $actionSummary = $this->getActionSummary($notification->node_results ?? []);
            @endphp
            <div class="bg-white rounded-lg border border-gray-200 p-3 hover:shadow-sm transition-shadow flex items-center gap-4">
                <div class="w-8 h-8 rounded-full bg-{{ $statusConfig['color'] }}-100 flex items-center justify-center flex-shrink-0">
                    <x-icon name="{{ $statusConfig['icon'] }}" class="w-4 h-4 text-{{ $statusConfig['color'] }}-600" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-medium text-gray-900 text-sm truncate">{{ $notification->workflow->name ?? $terminology->get('unknown_workflow_label') }}</h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}-800">
                            {{ $statusConfig['label'] }}
                        </span>
                    </div>
                    <div class="flex items-center gap-4 mt-1 text-xs text-gray-500">
                        <span>{{ $notification->created_at->diffForHumans() }}</span>
                        @if(count($actionSummary) > 0)
                            <span class="text-gray-700">{{ implode(', ', array_slice($actionSummary, 0, 2)) }}{{ count($actionSummary) > 2 ? '...' : '' }}</span>
                        @endif
                        @if($notification->error_message)
                            <span class="text-red-500 truncate max-w-xs">{{ Str::limit($notification->error_message, 50) }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if($notification->status === 'failed')
                        <button wire:click="retryExecution('{{ $notification->id }}')" class="p-1.5 text-gray-400 hover:text-blue-600 rounded" title="@term('retry_label')">
                            <x-icon name="arrow-path" class="w-4 h-4" />
                        </button>
                    @endif
                    <a href="{{ route('alerts.execution', [$notification->workflow_id, $notification->id]) }}" class="px-3 py-1 text-xs font-medium text-white bg-pulse-orange-500 rounded hover:bg-pulse-orange-600">
                        @term('details_label')
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
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('workflow_label')</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('status_label')</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('actions_taken_label')</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('triggered_by_label')</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">@term('time_label')</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">@term('details_label')</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($notifications as $notification)
                    @php
                        $statusConfig = $getStatusConfig($notification->status);
                        $actionSummary = $this->getActionSummary($notification->node_results ?? []);
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-{{ $statusConfig['color'] }}-100 flex items-center justify-center flex-shrink-0">
                                    <x-icon name="{{ $statusConfig['icon'] }}" class="w-3 h-3 text-{{ $statusConfig['color'] }}-600" />
                                </div>
                                <span class="text-sm font-medium text-gray-900">{{ $notification->workflow->name ?? $terminology->get('unknown_label') }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $statusConfig['color'] }}-100 text-{{ $statusConfig['color'] }}-800">
                                {{ $statusConfig['label'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if(count($actionSummary) > 0)
                                <div class="text-sm text-gray-600">
                                    {{ implode(', ', $actionSummary) }}
                                </div>
                            @elseif($notification->error_message)
                                <div class="text-sm text-red-500 truncate max-w-xs" title="{{ $notification->error_message }}">
                                    {{ Str::limit($notification->error_message, 40) }}
                                </div>
                            @else
                                <span class="text-sm text-gray-400">@term('empty_dash_label')</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ ucfirst(str_replace('_', ' ', $notification->triggered_by ?? $terminology->get('unknown_label'))) }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                            {{ $notification->created_at->diffForHumans() }}
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            <div class="flex items-center justify-end gap-2">
                                @if($notification->status === 'failed')
                                    <button wire:click="retryExecution('{{ $notification->id }}')" class="p-1 text-gray-400 hover:text-blue-600 rounded" title="@term('retry_label')">
                                        <x-icon name="arrow-path" class="w-4 h-4" />
                                    </button>
                                @endif
                                <a href="{{ route('alerts.execution', [$notification->workflow_id, $notification->id]) }}" class="text-xs font-medium text-pulse-orange-600 hover:text-pulse-orange-700">
                                    @term('view_action')
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
@if($notifications->hasPages())
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
@endif
