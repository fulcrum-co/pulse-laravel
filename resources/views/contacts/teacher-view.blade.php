<x-layouts.dashboard :title="$teacher->name">
    <x-slot name="actions">
        <x-button variant="secondary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
            </svg>
            Share
        </x-button>
        <x-button variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Entry
        </x-button>
    </x-slot>

    <!-- Contact Header -->
    <livewire:contact-header :contact="$teacher" />

    <!-- Overview Chart -->
    <x-card class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Overview</h3>
        <livewire:contact-overview-charts
            :contact-type="\App\Models\User::class"
            :contact-id="$teacher->id"
        />
    </x-card>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Left Column -->
        <div class="space-y-8">
            <!-- Classroom Performance -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Classroom Performance</h3>
                @if($classroomMetrics && $classroomMetrics->count() > 0)
                <div class="space-y-4">
                    @foreach($classroomMetrics as $metric)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <div>
                            <div class="font-medium text-gray-900">{{ $metric->metric_label ?? ucfirst(str_replace('_', ' ', $metric->metric_key)) }}</div>
                            <div class="text-sm text-gray-500">{{ $metric->recorded_at->format('M d, Y') }}</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-lg font-semibold text-gray-900">{{ $metric->numeric_value ?? $metric->text_value }}</span>
                            @if($metric->status)
                            @php
                                $statusColor = match($metric->status) {
                                    'on_track' => 'green',
                                    'at_risk' => 'yellow',
                                    'off_track' => 'red',
                                    default => 'gray',
                                };
                            @endphp
                            <x-badge :color="$statusColor">{{ ucfirst(str_replace('_', ' ', $metric->status)) }}</x-badge>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-sm">No classroom performance data available.</p>
                @endif
            </x-card>

            <!-- Notes -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes & Observations</h3>
                <livewire:contact-notes
                    contact-type="user"
                    :contact-id="$teacher->id"
                />
            </x-card>
        </div>

        <!-- Right Column -->
        <div class="space-y-8">
            <!-- Professional Development -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Professional Development</h3>
                @if($pdMetrics && $pdMetrics->count() > 0)
                <div class="space-y-4">
                    @foreach($pdMetrics as $metric)
                    <div class="p-3 border border-gray-200 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-medium text-gray-900">{{ $metric->metric_label ?? ucfirst(str_replace('_', ' ', $metric->metric_key)) }}</span>
                            @if($metric->numeric_value)
                            <span class="text-sm text-gray-600">{{ $metric->numeric_value }}%</span>
                            @endif
                        </div>
                        @if($metric->numeric_value)
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ min($metric->numeric_value, 100) }}%"></div>
                        </div>
                        @endif
                        <div class="text-sm text-gray-500 mt-1">{{ $metric->recorded_at->format('M d, Y') }}</div>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-sm">No professional development data available.</p>
                @endif
            </x-card>

            <!-- Teacher Information -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Teacher Information</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Email</dt>
                        <dd class="text-sm text-gray-900">{{ $teacher->email }}</dd>
                    </div>
                    @if($teacher->phone)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Phone</dt>
                        <dd class="text-sm text-gray-900">{{ $teacher->phone }}</dd>
                    </div>
                    @endif
                    @if($teacher->department)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Department</dt>
                        <dd class="text-sm text-gray-900">{{ $teacher->department }}</dd>
                    </div>
                    @endif
                    @if($teacher->hire_date)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Hire Date</dt>
                        <dd class="text-sm text-gray-900">{{ $teacher->hire_date->format('M d, Y') }}</dd>
                    </div>
                    @endif
                </dl>
            </x-card>
        </div>
    </div>
</x-layouts.dashboard>
