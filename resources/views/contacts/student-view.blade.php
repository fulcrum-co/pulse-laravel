<x-layouts.dashboard :title="$student->user->first_name . ' ' . $student->user->last_name">
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
    <livewire:contact-header :contact="$student" />

    <!-- Overview Chart -->
    <x-card class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Overview</h3>
        <livewire:contact-overview-charts
            :contact-type="\App\Models\Student::class"
            :contact-id="$student->id"
        />
    </x-card>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Left Column -->
        <div class="space-y-8">
            <!-- Resource Suggestions -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Suggested Resources</h3>
                <livewire:resource-suggestions
                    contact-type="student"
                    :contact-id="$student->id"
                />
            </x-card>

            <!-- Notes -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes & Voice Memos</h3>
                <livewire:contact-notes
                    contact-type="student"
                    :contact-id="$student->id"
                />
            </x-card>
        </div>

        <!-- Right Column -->
        <div class="space-y-8">
            <!-- Assigned Strategic Plans -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Assigned Strategic Plans</h3>
                @if($student->strategicPlans && $student->strategicPlans->count() > 0)
                <div class="space-y-3">
                    @foreach($student->strategicPlans as $plan)
                    <a href="{{ route('strategies.show', $plan) }}" class="block p-3 hover:bg-gray-50 rounded-lg transition-colors border border-gray-200">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="font-medium text-gray-900">{{ $plan->title }}</div>
                                <div class="text-sm text-gray-500">{{ $plan->school_year }} - {{ ucfirst($plan->status) }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($plan->progress_percent)
                                <span class="text-sm text-gray-600">{{ $plan->progress_percent }}%</span>
                                @endif
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
                @else
                <p class="text-gray-500 text-sm">No strategic plans assigned.</p>
                @endif
            </x-card>

            <!-- Plan Progress Heat Map -->
            <x-card>
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Plan Progress Heat Map</h3>
                <livewire:student-plan-heat-map
                    :student="$student"
                    :school-year="$schoolYear"
                />
            </x-card>

            <!-- Student Information -->
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
        </div>
    </div>

    <!-- Survey History -->
    <x-card class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Survey History</h3>
        @if($student->surveyAttempts && $student->surveyAttempts->count() > 0)
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

    <!-- Assigned Resources -->
    <x-card>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Assigned Resources</h3>
        @if($student->resourceAssignments && $student->resourceAssignments->count() > 0)
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
