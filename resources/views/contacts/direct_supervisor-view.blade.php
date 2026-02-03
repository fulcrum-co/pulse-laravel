<x-layouts.dashboard :title="$direct_supervisor->name">
    <x-slot name="actions">
        <x-button variant="secondary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            @term('send_message_label')
        </x-button>
        <x-button variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            @term('add_note_label')
        </x-button>
    </x-slot>

    <!-- Contact Header -->
    <livewire:contact-header :contact="$direct_supervisor" />

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Left Column -->
        <div class="space-y-8">
            <!-- Linked Participants -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('linked_participants_label')</h3>
                @if($linkedLearners && $linkedLearners->count() > 0)
                <div class="space-y-4">
                    @foreach($linkedLearners as $participant)
                    <a href="{{ route('contacts.show', $participant) }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-blue-600 font-semibold">
                                    {{ substr($participant->first_name ?? 'S', 0, 1) }}{{ substr($participant->last_name ?? '', 0, 1) }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $participant->first_name }} {{ $participant->last_name }}</div>
                                <div class="text-sm text-gray-500">@term('level_label') {{ $participant->level }} - @term('participant_number_label') {{ $participant->participant_number }}</div>
                            </div>
                            @if($participant->risk_level)
                            @php
                                $riskColor = match($participant->risk_level) {
                                    'good' => 'green',
                                    'low' => 'yellow',
                                    'high' => 'red',
                                    default => 'gray',
                                };
                            @endphp
                            <x-badge :color="$riskColor">{{ ucfirst($participant->risk_level) }}</x-badge>
                            @endif
                        </div>

                        @if($participant->metrics && $participant->metrics->count() > 0)
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <div class="text-xs text-gray-500 mb-2">@term('recent_metrics_label')</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($participant->metrics->take(3) as $metric)
                                <span class="text-xs px-2 py-1 bg-gray-100 rounded">
                                    {{ ucfirst(str_replace('_', ' ', $metric->metric_key)) }}: {{ $metric->numeric_value ?? $metric->text_value }}
                                </span>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </a>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-sm">@term('no_linked_participants_label')</p>
                @endif
            </x-card>

            <!-- Communication Notes -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('communication_history_label')</h3>
                <livewire:contact-notes
                    contact-type="user"
                    :contact-id="$direct_supervisor->id"
                />
            </x-card>
        </div>

        <!-- Right Column -->
        <div class="space-y-8">
            <!-- Engagement Metrics -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('engagement_overview_label')</h3>
                <livewire:contact-overview-charts
                    :contact-type="\App\Models\User::class"
                    :contact-id="$direct_supervisor->id"
                />
            </x-card>

            <!-- Direct Supervisor Information -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('contact_information_label')</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">@term('email_label')</dt>
                        <dd class="text-sm text-gray-900">{{ $direct_supervisor->email }}</dd>
                    </div>
                    @if($direct_supervisor->phone)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">@term('phone_label')</dt>
                        <dd class="text-sm text-gray-900">{{ $direct_supervisor->phone }}</dd>
                    </div>
                    @endif
                    @if($direct_supervisor->address)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">@term('address_label')</dt>
                        <dd class="text-sm text-gray-900">{{ $direct_supervisor->address }}</dd>
                    </div>
                    @endif
                    @if($direct_supervisor->preferred_contact_method)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">@term('preferred_contact_label')</dt>
                        <dd class="text-sm text-gray-900 capitalize">{{ $direct_supervisor->preferred_contact_method }}</dd>
                    </div>
                    @endif
                </dl>
            </x-card>

            <!-- Engagement Stats -->
            @if($engagementMetrics && $engagementMetrics->count() > 0)
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('engagement_statistics_label')</h3>
                <div class="space-y-4">
                    @foreach($engagementMetrics as $metric)
                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm font-medium text-gray-700">{{ $metric->metric_label ?? ucfirst(str_replace('_', ' ', $metric->metric_key)) }}</span>
                        <span class="text-lg font-semibold text-gray-900">{{ $metric->numeric_value ?? $metric->text_value }}</span>
                    </div>
                    @endforeach
                </div>
            </x-card>
            @endif
        </div>
    </div>
</x-layouts.dashboard>
