<x-layouts.dashboard title="Dashboard">
    <x-slot name="actions">
        <x-button variant="primary">
            <x-icon name="plus" class="w-4 h-4 mr-2" />
            Add Entry
        </x-button>
    </x-slot>

    <!-- Chart Section -->
    <div class="mb-8">
        <x-card>
            <div class="flex items-start justify-between mb-6">
                <div>
                    <div class="text-sm text-gray-600 mb-1">Student Overview</div>
                    <div class="flex items-baseline gap-2">
                        <span class="text-4xl font-semibold text-gray-900">{{ $studentMetrics['total'] }}</span>
                        <span class="text-sm text-green-600 font-medium">Total Students</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-button variant="secondary" size="small">
                        {{ now()->startOfWeek()->format('M d') }} - {{ now()->endOfWeek()->format('M d, Y') }}
                    </x-button>
                    <x-button variant="secondary" size="small">Week</x-button>
                </div>
            </div>

            <!-- Chart -->
            <livewire:dashboard-chart />
        </x-card>
    </div>

    <!-- Student Metrics -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Student Metrics</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <x-metric-card
                label="Students in Good Standing"
                :value="$studentMetrics['good']"
                color="green"
            />

            <x-metric-card
                label="Students at Low Risk"
                :value="$studentMetrics['low']"
                color="yellow"
            />

            <x-metric-card
                label="Students at High Risk"
                :value="$studentMetrics['high']"
                color="red"
            />
        </div>
    </div>

    <!-- Survey Metrics -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Survey Activity</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <x-card>
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-600 mb-1">Active Surveys</div>
                        <div class="text-sm text-gray-500">Currently running surveys</div>
                    </div>
                    <span class="text-4xl font-semibold text-gray-900">{{ $surveyMetrics['active'] }}</span>
                </div>
            </x-card>

            <x-card>
                <div class="flex items-start justify-between">
                    <div>
                        <div class="text-sm font-medium text-gray-600 mb-1">Completed This Week</div>
                        <div class="text-sm text-gray-500">Survey responses received</div>
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
                <div class="text-sm font-medium text-gray-900 mb-1">Suggested Resources</div>
                <div class="text-sm text-gray-600">
                    <span class="text-green-600 font-medium">{{ $suggestedResourcesCount }}</span> resources available for assignment
                </div>
            </div>
            <span class="text-5xl font-semibold text-gray-900">{{ $suggestedResourcesCount }}</span>
        </div>
    </x-card>
</x-layouts.dashboard>
