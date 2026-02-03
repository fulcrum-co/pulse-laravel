<x-layouts.dashboard :title="$participant->user->first_name . ' ' . $participant->user->last_name">
    <x-slot name="actions">
        <x-button variant="secondary">@term('message_singular')</x-button>
        <x-button variant="primary">@term('assign_action') @term('resource_singular')</x-button>
    </x-slot>

    <!-- Hero Section -->
    <x-card class="mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <div class="w-24 h-24 bg-pulse-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-pulse-orange-600 font-bold text-3xl">
                    {{ substr($participant->user->first_name ?? 'U', 0, 1) }}{{ substr($participant->user->last_name ?? '', 0, 1) }}
                </span>
            </div>

            <div class="flex-1">
                <h1 class="text-3xl font-semibold text-gray-900 mb-2">
                    {{ $participant->user->first_name }} {{ $participant->user->last_name }}
                </h1>
                <div class="space-y-1 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <span>{{ $participant->user->email }}</span>
                    </div>
                    @if($participant->user->phone)
                    <div class="flex items-center gap-2">
                        <span>{{ $participant->user->phone }}</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-4 mt-2">
                        <span class="text-gray-900 font-medium">@term('level_label') {{ $participant->level }}</span>
                        <span>@term('participant_number_label') {{ $participant->participant_number }}</span>
                        @php
                            $riskColor = match($participant->risk_level) {
                                'good' => 'green',
                                'low' => 'yellow',
                                'high' => 'red',
                                default => 'gray',
                            };
                            $term = app(\App\Services\TerminologyService::class);
                            $riskLabel = match($participant->risk_level) {
                                'good' => $term->get('good_standing_label'),
                                'low' => $term->get('low_risk_label'),
                                'high' => $term->get('high_risk_label'),
                                default => $term->get('unknown_label'),
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
        <!-- Participant Info -->
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">@term('participant_information_label')</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">@term('date_of_birth_label')</dt>
                    <dd class="text-sm text-gray-900">{{ $participant->date_of_birth?->format('M d, Y') ?? app(\App\Services\TerminologyService::class)->get('not_available_label') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">@term('gender_label')</dt>
                    <dd class="text-sm text-gray-900 capitalize">{{ $participant->gender ?? app(\App\Services\TerminologyService::class)->get('not_available_label') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">@term('enrollment_date_label')</dt>
                    <dd class="text-sm text-gray-900">{{ $participant->enrollment_date?->format('M d, Y') ?? app(\App\Services\TerminologyService::class)->get('not_available_label') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">@term('iep_status_label')</dt>
                    <dd class="text-sm text-gray-900">{{ $participant->iep_status ? app(\App\Services\TerminologyService::class)->get('yes_label') : app(\App\Services\TerminologyService::class)->get('no_label') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">@term('ell_status_label')</dt>
                    <dd class="text-sm text-gray-900">{{ $participant->ell_status ? app(\App\Services\TerminologyService::class)->get('yes_label') : app(\App\Services\TerminologyService::class)->get('no_label') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">@term('risk_label') @term('score_label')</dt>
                    <dd class="text-sm text-gray-900">{{ $participant->risk_score ?? app(\App\Services\TerminologyService::class)->get('not_available_label') }}</dd>
                </div>
            </dl>

            @if($participant->tags && count($participant->tags) > 0)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <dt class="text-sm text-gray-500 mb-2">@term('tag_plural')</dt>
                <div class="flex flex-wrap gap-2">
                    @foreach($participant->tags as $tag)
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
                    <x-button variant="ghost" size="small">@term('assign_action')</x-button>
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
        @if($participant->surveyAttempts->count() > 0)
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
                    @foreach($participant->surveyAttempts->take(5) as $attempt)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $attempt->survey->title ?? (app(\App\Services\TerminologyService::class)->get('unknown_label') . ' ' . app(\App\Services\TerminologyService::class)->get('survey_singular')) }}</td>
                        <td class="px-4 py-3">
                            <x-badge :color="$attempt->status === 'completed' ? 'green' : 'yellow'">
                                {{ ucfirst($attempt->status) }}
                            </x-badge>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $attempt->overall_score ?? app(\App\Services\TerminologyService::class)->get('not_available_label') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $attempt->completed_at?->format('M d, Y') ?? app(\App\Services\TerminologyService::class)->get('in_progress_label') }}</td>
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
        @if($participant->resourceAssignments->count() > 0)
        <div class="space-y-3">
            @foreach($participant->resourceAssignments as $assignment)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <div class="font-medium text-gray-900">{{ $assignment->resource->title }}</div>
                    <div class="text-sm text-gray-500">@term('assigned_label') {{ $assignment->assigned_at->format('M d, Y') }}</div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-600">{{ $assignment->progress_percent }}% @term('complete_label')</div>
                    <x-badge :color="$assignment->status === 'completed' ? 'green' : ($assignment->status === 'in_progress' ? 'yellow' : 'gray')">
                        {{ ucfirst($assignment->status) }}
                    </x-badge>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-gray-500 text-sm">@term('no_label') @term('resource_plural') @term('assigned_label') @term('yet_label').</p>
        @endif
    </x-card>
</x-layouts.dashboard>
