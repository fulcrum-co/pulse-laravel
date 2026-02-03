<x-layouts.dashboard :title="$learner->user->first_name . ' ' . $learner->user->last_name">
    <x-slot name="actions">
        <x-button variant="secondary">Message</x-button>
        <x-button variant="primary">Assign Resource</x-button>
    </x-slot>

    <!-- Hero Section -->
    <x-card class="mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <div class="w-24 h-24 bg-pulse-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-pulse-orange-600 font-bold text-3xl">
                    {{ substr($learner->user->first_name ?? 'U', 0, 1) }}{{ substr($learner->user->last_name ?? '', 0, 1) }}
                </span>
            </div>

            <div class="flex-1">
                <h1 class="text-3xl font-semibold text-gray-900 mb-2">
                    {{ $learner->user->first_name }} {{ $learner->user->last_name }}
                </h1>
                <div class="space-y-1 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <span>{{ $learner->user->email }}</span>
                    </div>
                    @if($learner->user->phone)
                    <div class="flex items-center gap-2">
                        <span>{{ $learner->user->phone }}</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-4 mt-2">
                        <span class="text-gray-900 font-medium">Grade {{ $learner->grade_level }}</span>
                        <span>Learner #{{ $learner->learner_number }}</span>
                        @php
                            $riskColor = match($learner->risk_level) {
                                'good' => 'green',
                                'low' => 'yellow',
                                'high' => 'red',
                                default => 'gray',
                            };
                            $riskLabel = match($learner->risk_level) {
                                'good' => 'Good Standing',
                                'low' => 'Low Risk',
                                'high' => 'High Risk',
                                default => 'Unknown',
                            };
                        @endphp
                        <x-badge :color="$riskColor">{{ $riskLabel }}</x-badge>
                    </div>
                </div>
            </div>
        </div>
    </x-card>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Learner Info -->
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Learner Information</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Date of Birth</dt>
                    <dd class="text-sm text-gray-900">{{ $learner->date_of_birth?->format('M d, Y') ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Gender</dt>
                    <dd class="text-sm text-gray-900 capitalize">{{ $learner->gender ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Enrollment Date</dt>
                    <dd class="text-sm text-gray-900">{{ $learner->enrollment_date?->format('M d, Y') ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">@term('iep_status_label')</dt>
                    <dd class="text-sm text-gray-900">{{ $learner->iep_status ? app(\App\Services\TerminologyService::class)->get('yes_label') : app(\App\Services\TerminologyService::class)->get('no_label') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">@term('ell_status_label')</dt>
                    <dd class="text-sm text-gray-900">{{ $learner->ell_status ? app(\App\Services\TerminologyService::class)->get('yes_label') : app(\App\Services\TerminologyService::class)->get('no_label') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">@term('risk_label') @term('score_label')</dt>
                    <dd class="text-sm text-gray-900">{{ $learner->risk_score ?? 'N/A' }}</dd>
                </div>
            </dl>

            @if($learner->tags && count($learner->tags) > 0)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <dt class="text-sm text-gray-500 mb-2">@term('tag_plural')</dt>
                <div class="flex flex-wrap gap-2">
                    @foreach($learner->tags as $tag)
                    <x-badge color="gray">{{ $tag }}</x-badge>
                    @endforeach
                </div>
            </div>
            @endif
        </x-card>

        <!-- Suggested Resources -->
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('suggested_label') @term('resource_plural')</h3>
            @if($suggestedResources->count() > 0)
            <div class="space-y-3">
                @foreach($suggestedResources as $resource)
                <div class="flex items-start justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                    <div>
                        <div class="font-medium text-gray-900">{{ $resource->title }}</div>
                        <div class="text-sm text-gray-500 capitalize">{{ $resource->resource_type }} - {{ $resource->category }}</div>
                    </div>
                    <x-button variant="ghost" size="small">Assign</x-button>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">@term('no_label') @term('suggested_label') @term('resource_plural') @term('available_label').</p>
            @endif
        </x-card>
    </div>

    <!-- Survey History -->
    <x-card class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('survey_singular') @term('history_label')</h3>
        @if($learner->surveyAttempts->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">@term('survey_singular')</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">@term('status_label')</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">@term('score_label')</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">@term('complete_action')</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($learner->surveyAttempts->take(5) as $attempt)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $attempt->survey->title ?? (app(\App\Services\TerminologyService::class)->get('unknown_label') . ' ' . app(\App\Services\TerminologyService::class)->get('survey_singular')) }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$attempt->status === 'completed' ? 'green' : 'yellow'">
                                {{ ucfirst($attempt->status) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $attempt->overall_score ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $attempt->completed_at?->format('M d, Y') ?? 'In Progress' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <p class="text-gray-500 text-sm">@term('no_label') @term('survey_singular') @term('history_label') @term('available_label').</p>
        @endif
    </x-card>

    <!-- Resource Assignments -->
    <x-card>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('assigned_label') @term('resource_plural')</h3>
        @if($learner->resourceAssignments->count() > 0)
        <div class="space-y-3">
            @foreach($learner->resourceAssignments as $assignment)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <div class="font-medium text-gray-900">{{ $assignment->resource->title }}</div>
                    <div class="text-sm text-gray-500">@term('assigned_label') {{ $assignment->assigned_at->format('M d, Y') }}</div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-600">{{ $assignment->progress_percent }}% complete</div>
                    <x-badge :color="$assignment->status === 'completed' ? 'green' : ($assignment->status === 'in_progress' ? 'yellow' : 'gray')">
                        {{ ucfirst($assignment->status) }}
                    </x-badge>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-gray-500 text-sm">@term('no_label') @term('resource_plural') @term('assigned_label') yet.</p>
        @endif
    </x-card>
</x-layouts.dashboard>
