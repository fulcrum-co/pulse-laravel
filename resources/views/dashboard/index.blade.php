<x-layouts.dashboard title="{{ app(\App\Services\TerminologyService::class)->get('dashboard_singular') }}">
    <x-slot name="actions">
        <x-button variant="primary">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            @term('add_action') @term('entry_singular')
        </x-button>
    </x-slot>

    <!-- Chart Section -->
    <div class="mb-8">
        <x-card>
            <div class="flex items-start justify-between mb-6">
                <div>
                    <div class="text-sm text-gray-600 mb-1">@term('learner_singular') @term('overview_label')</div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-semibold text-gray-900">{{ $learnerMetrics['total'] }}</span>
                        <span class="text-sm text-green-600 font-medium">@term('total_label') @term('learner_plural')</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-button variant="secondary" size="small">
                        {{ now()->startOfWeek()->format('M d') }} - {{ now()->endOfWeek()->format('M d, Y') }}
                    </x-button>
                    <x-button variant="secondary" size="small">@term('week_label')</x-button>
                </div>
            </div>

            <!-- Chart -->
            <livewire:dashboard-chart />
        </x-card>
    </div>

    <!-- Participant Metrics -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('learner_singular') @term('metrics_label')</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-metric-card
                label="{{ app(\App\Services\TerminologyService::class)->get('learner_plural') }} in {{ app(\App\Services\TerminologyService::class)->get('good_standing_label') }}"
                :value="$learnerMetrics['good']"
                color="green"
            />

            <x-metric-card
                label="{{ app(\App\Services\TerminologyService::class)->get('learner_plural') }} at {{ app(\App\Services\TerminologyService::class)->get('low_risk_label') }}"
                :value="$learnerMetrics['low']"
                color="yellow"
            />

            <x-metric-card
                label="{{ app(\App\Services\TerminologyService::class)->get('learner_plural') }} at {{ app(\App\Services\TerminologyService::class)->get('high_risk_label') }}"
                :value="$learnerMetrics['high']"
                color="red"
            />
        </div>
    </div>

    <!-- Survey Metrics -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('survey_singular') @term('activity_label')</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-card>
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-600 mb-1">@term('active_label') @term('survey_plural')</div>
                        <div class="text-sm text-gray-500">@term('currently_running_label') @term('survey_plural')</div>
                    </div>
                    <span class="text-4xl font-semibold text-gray-900">{{ $surveyMetrics['active'] }}</span>
                </div>
            </x-card>

            <x-card>
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-600 mb-1">@term('completed_this_week_label')</div>
                        <div class="text-sm text-gray-500">@term('survey_singular') @term('responses_received_label')</div>
                    </div>
                    <span class="text-4xl font-semibold text-gray-900">{{ $surveyMetrics['completed_this_week'] }}</span>
                </div>
            </x-card>
        </div>
    </div>

    <!-- Suggested Resources -->
    <x-card>
        <div class="flex items-start justify-between">
            <div>
                <div class="text-sm font-medium text-gray-900 mb-1">@term('suggested_label') @term('resource_plural')</div>
                <div class="text-sm text-gray-600">
                    <span class="text-green-600 font-medium">{{ $suggestedResourcesCount }}</span> @term('resource_plural') @term('available_label') for @term('assignment_singular')
                </div>
            </div>
            <span class="text-5xl font-semibold text-gray-900">{{ $suggestedResourcesCount }}</span>
        </div>
    </x-card>
</x-layouts.dashboard>
