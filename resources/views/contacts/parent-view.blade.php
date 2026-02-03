<x-layouts.dashboard :title="$parent->name">
    <x-slot name="actions">
        <x-button variant="secondary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
            Send Message
        </x-button>
        <x-button variant="primary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>
            Add Note
        </x-button>
    </x-slot>

    <!-- Contact Header -->
    <livewire:contact-header :contact="$parent" />

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Left Column -->
        <div class="space-y-8">
            <!-- Linked Learners -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Linked Learners</h3>
                @if($linkedLearners && $linkedLearners->count() > 0)
                <div class="space-y-4">
                    @foreach($linkedLearners as $learner)
                    <a href="{{ route('contacts.show', $learner) }}" class="block p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-blue-600 font-semibold">
                                    {{ substr($learner->first_name ?? 'S', 0, 1) }}{{ substr($learner->last_name ?? '', 0, 1) }}
                                </span>
                            </div>
                            <div class="flex-1">
                                <div class="font-medium text-gray-900">{{ $learner->first_name }} {{ $learner->last_name }}</div>
                                <div class="text-sm text-gray-500">Grade {{ $learner->grade_level }} - Learner #{{ $learner->learner_number }}</div>
                            </div>
                            @if($learner->risk_level)
                            @php
                                $riskColor = match($learner->risk_level) {
                                    'good' => 'green',
                                    'low' => 'yellow',
                                    'high' => 'red',
                                    default => 'gray',
                                };
                            @endphp
                            <x-badge :color="$riskColor">{{ ucfirst($learner->risk_level) }}</x-badge>
                            @endif
                        </div>

                        @if($learner->metrics && $learner->metrics->count() > 0)
                        <div class="mt-3 pt-3 border-t border-gray-100">
                            <div class="text-xs text-gray-500 mb-2">Recent Metrics</div>
                            <div class="flex flex-wrap gap-2">
                                @foreach($learner->metrics->take(3) as $metric)
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
                <p class="text-gray-500 text-sm">No linked learners found.</p>
                @endif
            </x-card>

            <!-- Communication Notes -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Communication History</h3>
                <livewire:contact-notes
                    contact-type="user"
                    :contact-id="$parent->id"
                />
            </x-card>
        </div>

        <!-- Right Column -->
        <div class="space-y-8">
            <!-- Engagement Metrics -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Engagement Overview</h3>
                <livewire:contact-overview-charts
                    :contact-type="\App\Models\User::class"
                    :contact-id="$parent->id"
                />
            </x-card>

            <!-- Parent Information -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Email</dt>
                        <dd class="text-sm text-gray-900">{{ $parent->email }}</dd>
                    </div>
                    @if($parent->phone)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Phone</dt>
                        <dd class="text-sm text-gray-900">{{ $parent->phone }}</dd>
                    </div>
                    @endif
                    @if($parent->address)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Address</dt>
                        <dd class="text-sm text-gray-900">{{ $parent->address }}</dd>
                    </div>
                    @endif
                    @if($parent->preferred_contact_method)
                    <div class="flex justify-between">
                        <dt class="text-sm text-gray-500">Preferred Contact</dt>
                        <dd class="text-sm text-gray-900 capitalize">{{ $parent->preferred_contact_method }}</dd>
                    </div>
                    @endif
                </dl>
            </x-card>

            <!-- Engagement Stats -->
            @if($engagementMetrics && $engagementMetrics->count() > 0)
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Engagement Statistics</h3>
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
