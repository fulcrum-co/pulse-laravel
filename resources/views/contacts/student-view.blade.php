<x-layouts.dashboard :title="$student->user->first_name . ' ' . $student->user->last_name">
    <x-slot name="actions">
        <x-button variant="secondary">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path>
            </svg>
            Share
        </x-button>
    </x-slot>

    <!-- Contact Header (Compact) -->
    <livewire:contact-header :contact="$student" />

    <!-- Quick Stats Bar -->
    @php
        $latestMetrics = $chartData ?? [];
        $latestSurvey = $student->surveyAttempts?->where('status', 'completed')->sortByDesc('completed_at')->first();
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">GPA</div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($latestMetrics['gpa'] ?? 0, 1) }}</div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Attendance</div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($latestMetrics['attendance'] ?? 0, 0) }}%</div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Wellness</div>
            <div class="text-2xl font-bold text-gray-900">{{ number_format($latestMetrics['wellness_score'] ?? 0, 0) }}/10</div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Last Survey</div>
            <div class="text-lg font-semibold text-gray-900">
                @if($latestSurvey)
                    {{ $latestSurvey->completed_at->diffForHumans() }}
                @else
                    <span class="text-gray-400">None</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Layout: Action Sidebar + Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Sidebar: Take Action -->
        <div class="lg:col-span-1 space-y-4">

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Quick Actions</h3>
                <div class="space-y-2">
                    <button onclick="document.getElementById('notes-section').scrollIntoView({behavior: 'smooth'})"
                            class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4 text-pulse-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                        Add Note
                    </button>
                    <button onclick="document.getElementById('resources-section').scrollIntoView({behavior: 'smooth'})"
                            class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4 text-pulse-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        Assign Resource
                    </button>
                    <a href="{{ route('surveys.index') }}" class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                        <svg class="w-4 h-4 text-pulse-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        Send Survey
                    </a>
                </div>
            </div>

            <!-- Suggested Resources -->
            <div class="bg-white rounded-lg border border-gray-200 p-4" id="resources-section">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Suggested Resources</h3>
                <livewire:resource-suggestions
                    contact-type="student"
                    :contact-id="$student->id"
                />
            </div>

            <!-- Student Details (Collapsible) -->
            <div x-data="{ open: false }" class="bg-white rounded-lg border border-gray-200">
                <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left">
                    <h3 class="text-sm font-semibold text-gray-900">Student Details</h3>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="px-4 pb-4 border-t border-gray-100">
                    <dl class="space-y-2 pt-3 text-sm">
                        <div class="flex justify-between">
                            <dt class="text-gray-500">DOB</dt>
                            <dd class="text-gray-900">{{ $student->date_of_birth?->format('M d, Y') ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Gender</dt>
                            <dd class="text-gray-900 capitalize">{{ $student->gender ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Enrolled</dt>
                            <dd class="text-gray-900">{{ $student->enrollment_date?->format('M d, Y') ?? 'N/A' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">IEP</dt>
                            <dd class="text-gray-900">{{ $student->iep_status ? 'Yes' : 'No' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-gray-500">ELL</dt>
                            <dd class="text-gray-900">{{ $student->ell_status ? 'Yes' : 'No' }}</dd>
                        </div>
                    </dl>
                    @if($student->tags && count($student->tags) > 0)
                    <div class="mt-3 pt-3 border-t border-gray-100">
                        <div class="flex flex-wrap gap-1">
                            @foreach($student->tags as $tag)
                            <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $tag }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Notes & Voice Memos (Primary) -->
            <div class="bg-white rounded-lg border border-gray-200 p-4" id="notes-section">
                <h3 class="text-sm font-semibold text-gray-900 mb-3">Notes & Activity</h3>
                <livewire:contact-notes
                    contact-type="student"
                    :contact-id="$student->id"
                />
            </div>

            <!-- Performance Charts (Collapsible) -->
            <div x-data="{ open: false }" class="bg-white rounded-lg border border-gray-200">
                <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left">
                    <h3 class="text-sm font-semibold text-gray-900">Performance Charts</h3>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="px-4 pb-4 border-t border-gray-100">
                    <div class="pt-3">
                        <livewire:contact-overview-charts
                            :contact-type="\App\Models\Student::class"
                            :contact-id="$student->id"
                        />
                    </div>
                </div>
            </div>

            <!-- Survey History (Collapsible) -->
            <div x-data="{ open: false }" class="bg-white rounded-lg border border-gray-200">
                <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold text-gray-900">Survey History</h3>
                        @if($student->surveyAttempts && $student->surveyAttempts->count() > 0)
                        <span class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $student->surveyAttempts->count() }}</span>
                        @endif
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="border-t border-gray-100">
                    @if($student->surveyAttempts && $student->surveyAttempts->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($student->surveyAttempts->take(5) as $attempt)
                        <div class="flex items-center justify-between px-4 py-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $attempt->survey->title ?? 'Unknown Survey' }}</div>
                                <div class="text-xs text-gray-500">{{ $attempt->completed_at?->format('M d, Y') ?? 'In Progress' }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-600">{{ $attempt->overall_score ?? 'N/A' }}</span>
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $attempt->status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ ucfirst($attempt->status) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="px-4 py-3 text-sm text-gray-500">No survey history available.</p>
                    @endif
                </div>
            </div>

            <!-- Assigned Resources (Collapsible) -->
            <div x-data="{ open: false }" class="bg-white rounded-lg border border-gray-200">
                <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold text-gray-900">Assigned Resources</h3>
                        @if($student->resourceAssignments && $student->resourceAssignments->count() > 0)
                        <span class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $student->resourceAssignments->count() }}</span>
                        @endif
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="border-t border-gray-100">
                    @if($student->resourceAssignments && $student->resourceAssignments->count() > 0)
                    <div class="divide-y divide-gray-100">
                        @foreach($student->resourceAssignments as $assignment)
                        <div class="flex items-center justify-between px-4 py-3">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $assignment->resource->title }}</div>
                                <div class="text-xs text-gray-500">Assigned {{ $assignment->assigned_at->format('M d, Y') }}</div>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="text-sm text-gray-600">{{ $assignment->progress_percent }}%</span>
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $assignment->status === 'completed' ? 'bg-green-100 text-green-700' : ($assignment->status === 'in_progress' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ ucfirst($assignment->status) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="px-4 py-3 text-sm text-gray-500">No resources assigned yet.</p>
                    @endif
                </div>
            </div>

            <!-- Strategic Plans (Collapsible) -->
            @if($student->strategicPlans && $student->strategicPlans->count() > 0)
            <div x-data="{ open: false }" class="bg-white rounded-lg border border-gray-200">
                <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left">
                    <div class="flex items-center gap-2">
                        <h3 class="text-sm font-semibold text-gray-900">Strategic Plans</h3>
                        <span class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $student->strategicPlans->count() }}</span>
                    </div>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="border-t border-gray-100">
                    <div class="divide-y divide-gray-100">
                        @foreach($student->strategicPlans as $plan)
                        <a href="{{ route('strategies.show', $plan) }}" class="flex items-center justify-between px-4 py-3 hover:bg-gray-50 transition-colors">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $plan->title }}</div>
                                <div class="text-xs text-gray-500">{{ $plan->school_year }} - {{ ucfirst($plan->status) }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($plan->progress_percent)
                                <span class="text-sm text-gray-600">{{ $plan->progress_percent }}%</span>
                                @endif
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-layouts.dashboard>
