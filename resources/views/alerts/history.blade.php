@php
    $terminology = app(\App\Services\TerminologyService::class);
    $executionStatusLabels = [
        'completed' => $terminology->get('execution_status_completed_label'),
        'running' => $terminology->get('execution_status_running_label'),
        'waiting' => $terminology->get('execution_status_waiting_label'),
        'failed' => $terminology->get('execution_status_failed_label'),
        'cancelled' => $terminology->get('execution_status_cancelled_label'),
    ];
@endphp

<x-layouts.dashboard title="@term('alert_history_label')">
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('alerts.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-2">
                    <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
                    @term('back_to_label') @term('alert_plural')
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ $workflow->name }}</h1>
                <p class="text-gray-500 mt-1">@term('execution_history_label')</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('alerts.edit', $workflow) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    <x-icon name="pencil" class="w-4 h-4 mr-2" />
                    @term('edit_alert_label')
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900">{{ number_format($workflow->execution_count) }}</div>
                    <div class="text-sm text-gray-500">@term('total_executions_label')</div>
                </div>
            </x-card>
            <x-card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-green-600">
                        {{ $executions->where('status', 'completed')->count() }}
                    </div>
                    <div class="text-sm text-gray-500">@term('successful_label')</div>
                </div>
            </x-card>
            <x-card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-red-600">
                        {{ $executions->where('status', 'failed')->count() }}
                    </div>
                    <div class="text-sm text-gray-500">@term('failed_label')</div>
                </div>
            </x-card>
            <x-card>
                <div class="text-center">
                    <div class="text-3xl font-bold text-gray-900">
                        {{ $workflow->last_triggered_at ? $workflow->last_triggered_at->diffForHumans() : app(\App\Services\TerminologyService::class)->get('never_label') }}
                    </div>
                    <div class="text-sm text-gray-500">@term('last_triggered_label')</div>
                </div>
            </x-card>
        </div>

        <!-- Executions Table -->
        <x-card :padding="false">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">@term('execution_id_label')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">@term('triggered_by_label')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">@term('status_label')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">@term('started_label')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">@term('duration_label')</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">@term('actions_label')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($executions as $execution)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <span class="font-mono text-sm text-gray-900">{{ substr($execution->_id, -8) }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600">{{ $execution->triggered_by ?? app(\App\Services\TerminologyService::class)->get('unknown_label') }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusColor = match($execution->status) {
                                            'completed' => 'green',
                                            'running' => 'blue',
                                            'waiting' => 'yellow',
                                            'failed' => 'red',
                                            'cancelled' => 'gray',
                                            default => 'gray',
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                                        {{ $executionStatusLabels[$execution->status] ?? ucfirst($execution->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600">
                                        {{ $execution->started_at ? $execution->started_at->format('M j, Y g:i A') : '-' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm text-gray-600">
                                        @if($execution->duration)
                                            {{ $execution->duration }} @term('seconds_label')
                                        @else
                                            -
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <a href="{{ route('alerts.execution', [$workflow, $execution]) }}" class="text-pulse-orange-600 hover:text-pulse-orange-700 text-sm font-medium">
                                        @term('view_details_label')
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <x-icon name="clock" class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                                    <p class="text-gray-500">@term('no_executions_yet_label')</p>
                                    <p class="text-sm text-gray-400 mt-1">@term('alert_not_triggered_yet_label')</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($executions->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $executions->links() }}
                </div>
            @endif
        </x-card>
    </div>
</x-layouts.dashboard>
