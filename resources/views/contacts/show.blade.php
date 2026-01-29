<x-layouts.dashboard :title="$student->user->first_name . ' ' . $student->user->last_name">
    <x-slot name="actions">
        <x-button variant="secondary">Message</x-button>
        <x-button variant="primary">Assign Resource</x-button>
    </x-slot>

    <!-- Hero Section -->
    <x-card class="mb-8">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-6">
            <div class="w-24 h-24 bg-pulse-orange-100 rounded-full flex items-center justify-center flex-shrink-0">
                <span class="text-pulse-orange-600 font-bold text-3xl">
                    {{ substr($student->user->first_name ?? 'U', 0, 1) }}{{ substr($student->user->last_name ?? '', 0, 1) }}
                </span>
            </div>

            <div class="flex-1">
                <h1 class="text-3xl font-semibold text-gray-900 mb-2">
                    {{ $student->user->first_name }} {{ $student->user->last_name }}
                </h1>
                <div class="space-y-1 text-sm text-gray-600">
                    <div class="flex items-center gap-2">
                        <span>{{ $student->user->email }}</span>
                    </div>
                    @if($student->user->phone)
                    <div class="flex items-center gap-2">
                        <span>{{ $student->user->phone }}</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-4 mt-2">
                        <span class="text-gray-900 font-medium">Grade {{ $student->grade_level }}</span>
                        <span>Student #{{ $student->student_number }}</span>
                        @php
                            $riskColor = match($student->risk_level) {
                                'good' => 'green',
                                'low' => 'yellow',
                                'high' => 'red',
                                default => 'gray',
                            };
                            $riskLabel = match($student->risk_level) {
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
        <!-- Student Info -->
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Student Information</h3>
            <dl class="space-y-3">
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Date of Birth</dt>
                    <dd class="text-sm text-gray-900">{{ $student->date_of_birth?->format('M d, Y') ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Gender</dt>
                    <dd class="text-sm text-gray-900 capitalize">{{ $student->gender ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Enrollment Date</dt>
                    <dd class="text-sm text-gray-900">{{ $student->enrollment_date?->format('M d, Y') ?? 'N/A' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">IEP Status</dt>
                    <dd class="text-sm text-gray-900">{{ $student->iep_status ? 'Yes' : 'No' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">ELL Status</dt>
                    <dd class="text-sm text-gray-900">{{ $student->ell_status ? 'Yes' : 'No' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-sm text-gray-500">Risk Score</dt>
                    <dd class="text-sm text-gray-900">{{ $student->risk_score ?? 'N/A' }}</dd>
                </div>
            </dl>

            @if($student->tags && count($student->tags) > 0)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <dt class="text-sm text-gray-500 mb-2">Tags</dt>
                <div class="flex flex-wrap gap-2">
                    @foreach($student->tags as $tag)
                    <x-badge color="gray">{{ $tag }}</x-badge>
                    @endforeach
                </div>
            </div>
            @endif
        </x-card>

        <!-- Suggested Resources -->
        <x-card>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Suggested Resources</h3>
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
            <p class="text-gray-500 text-sm">No suggested resources available.</p>
            @endif
        </x-card>
    </div>

    <!-- Survey History -->
    <x-card class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Survey History</h3>
        @if($student->surveyAttempts->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Survey</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Score</th>
                        <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Completed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($student->surveyAttempts->take(5) as $attempt)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $attempt->survey->title ?? 'Unknown Survey' }}</td>
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
        <p class="text-gray-500 text-sm">No survey history available.</p>
        @endif
    </x-card>

    <!-- Resource Assignments -->
    <x-card>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Assigned Resources</h3>
        @if($student->resourceAssignments->count() > 0)
        <div class="space-y-3">
            @foreach($student->resourceAssignments as $assignment)
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div>
                    <div class="font-medium text-gray-900">{{ $assignment->resource->title }}</div>
                    <div class="text-sm text-gray-500">Assigned {{ $assignment->assigned_at->format('M d, Y') }}</div>
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
        <p class="text-gray-500 text-sm">No resources assigned yet.</p>
        @endif
    </x-card>
</x-layouts.dashboard>
