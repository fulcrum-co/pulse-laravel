<x-layouts.dashboard title="Execution Details">
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <a href="{{ route('alerts.history', $workflow) }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-2">
                <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
                Back to History
            </a>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Execution Details</h1>
                    <p class="text-gray-500 mt-1">{{ $workflow->name }} - {{ substr($execution->_id, -8) }}</p>
                </div>
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
                <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-medium bg-{{ $statusColor }}-100 text-{{ $statusColor }}-800">
                    {{ ucfirst($execution->status) }}
                </span>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-card>
                <div class="text-sm text-gray-500">Triggered By</div>
                <div class="font-medium text-gray-900 mt-1">{{ $execution->triggered_by ?? 'Unknown' }}</div>
            </x-card>
            <x-card>
                <div class="text-sm text-gray-500">Started</div>
                <div class="font-medium text-gray-900 mt-1">
                    {{ $execution->started_at ? $execution->started_at->format('M j, Y g:i:s A') : '-' }}
                </div>
            </x-card>
            <x-card>
                <div class="text-sm text-gray-500">Completed</div>
                <div class="font-medium text-gray-900 mt-1">
                    {{ $execution->completed_at ? $execution->completed_at->format('M j, Y g:i:s A') : '-' }}
                </div>
            </x-card>
            <x-card>
                <div class="text-sm text-gray-500">Duration</div>
                <div class="font-medium text-gray-900 mt-1">
                    {{ $execution->duration ? $execution->duration . ' seconds' : '-' }}
                </div>
            </x-card>
        </div>

        @if($execution->error_message)
            <x-card class="bg-red-50 border-red-200">
                <div class="flex items-start gap-3">
                    <x-icon name="exclamation-circle" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" />
                    <div>
                        <div class="font-medium text-red-800">Error Message</div>
                        <div class="text-red-700 mt-1">{{ $execution->error_message }}</div>
                    </div>
                </div>
            </x-card>
        @endif

        <!-- Node Results -->
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Node Execution Results</h2>

            @if(empty($execution->node_results))
                <p class="text-gray-500 text-center py-8">No node results available</p>
            @else
                <div class="space-y-3">
                    @foreach($execution->node_results as $nodeId => $result)
                        @php
                            $node = collect($workflow->nodes)->firstWhere('id', $nodeId);
                            $nodeType = $node['type'] ?? 'unknown';
                            $resultStatus = $result['status'] ?? 'unknown';
                            $resultColor = match($resultStatus) {
                                'success' => 'green',
                                'failed' => 'red',
                                'skipped' => 'gray',
                                default => 'gray',
                            };
                        @endphp
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 capitalize">{{ $nodeType }}</span>
                                    <span class="text-gray-400">&bull;</span>
                                    <span class="font-mono text-xs text-gray-500">{{ substr($nodeId, -8) }}</span>
                                </div>
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $resultColor }}-100 text-{{ $resultColor }}-800">
                                    {{ ucfirst($resultStatus) }}
                                </span>
                            </div>

                            @if(!empty($result['output']))
                                <div class="mt-2 text-sm">
                                    <pre class="bg-white p-3 rounded border border-gray-200 overflow-x-auto text-xs">{{ json_encode($result['output'], JSON_PRETTY_PRINT) }}</pre>
                                </div>
                            @endif

                            @if(!empty($result['error']))
                                <div class="mt-2 text-sm text-red-600">
                                    Error: {{ $result['error'] }}
                                </div>
                            @endif

                            <div class="mt-2 text-xs text-gray-400">
                                Executed: {{ $result['executed_at'] ?? '-' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </x-card>

        <!-- Trigger Data -->
        <x-card>
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Trigger Data</h2>
            <pre class="bg-gray-50 p-4 rounded-lg overflow-x-auto text-sm">{{ json_encode($execution->trigger_data, JSON_PRETTY_PRINT) }}</pre>
        </x-card>

        <!-- Context -->
        @if(!empty($execution->context))
            <x-card>
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Execution Context</h2>
                <pre class="bg-gray-50 p-4 rounded-lg overflow-x-auto text-sm">{{ json_encode($execution->context, JSON_PRETTY_PRINT) }}</pre>
            </x-card>
        @endif
    </div>
</x-layouts.dashboard>
