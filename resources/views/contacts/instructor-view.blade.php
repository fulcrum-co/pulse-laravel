<x-layouts.dashboard :title="$instructor->name">
    <x-slot name="actions">
        <x-button variant="secondary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
            </svg>
            @term('share_label')
        </x-button>
        <x-button variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            @term('add_action') @term('entry_singular')
        </x-button>
    </x-slot>

    <!-- Contact Header -->
    <livewire:contact-header :contact="$instructor" />

    <!-- Overview Chart -->
    <x-card class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('performance_overview_label')</h3>
        <livewire:contact-overview-charts
            :contact-type="\App\Models\User::class"
            :contact-id="$instructor->id"
        />
    </x-card>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Left Column -->
        <div class="space-y-8">
            <!-- LearningGroup Performance -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('learning_group_performance_label')</h3>
                @if($learningGroupMetrics && $learningGroupMetrics->count() > 0)
                <div class="space-y-4">
                    @foreach($learningGroupMetrics as $metric)
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
                <p class="text-gray-500 text-sm">@term('no_learning_group_performance_label')</p>
                @endif
            </x-card>

            <!-- Notes -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('notes_observations_label')</h3>
                <livewire:contact-notes
                    contact-type="user"
                    :contact-id="$instructor->id"
                />
            </x-card>
        </div>

        <!-- Right Column -->
        <div class="space-y-8">
            <!-- Professional Development -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('professional_development_label')</h3>
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
                <p class="text-gray-500 text-sm">@term('no_professional_development_data_label')</p>
                @endif
            </x-card>

            <!-- Instructor Information -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('instructor_information_label')</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">@term('email_label')</dt>
                        <dd class="text-sm text-gray-900">{{ $instructor->email }}</dd>
                    </div>
                    @if($instructor->phone)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">@term('phone_label')</dt>
                        <dd class="text-sm text-gray-900">{{ $instructor->phone }}</dd>
                    </div>
                    @endif
                    @if($instructor->department)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">@term('department_label')</dt>
                        <dd class="text-sm text-gray-900">{{ $instructor->department }}</dd>
                    </div>
                    @endif
                    @if($instructor->hire_date)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">@term('hire_date_label')</dt>
                        <dd class="text-sm text-gray-900">{{ $instructor->hire_date->format('M d, Y') }}</dd>
                    </div>
                    @endif
                </dl>
            </x-card>
        </div>
    </div>
</x-layouts.dashboard>
