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
        $gpaMetric = $student->metrics()->where('metric_key', 'gpa')->latest('recorded_at')->first();
        $attendanceMetric = $student->metrics()->where('metric_key', 'attendance')->latest('recorded_at')->first();
        $wellnessMetric = $student->metrics()->where('metric_key', 'wellness_score')->latest('recorded_at')->first();
        $latestSurvey = $student->surveyAttempts?->where('status', 'completed')->sortByDesc('completed_at')->first();
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">GPA</div>
            <div class="text-2xl font-bold text-gray-900">
                @if($gpaMetric)
                    {{ number_format($gpaMetric->value, 1) }}
                @else
                    <span class="text-gray-400">--</span>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Attendance</div>
            <div class="text-2xl font-bold text-gray-900">
                @if($attendanceMetric)
                    {{ number_format($attendanceMetric->value, 0) }}%
                @else
                    <span class="text-gray-400">--</span>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Wellness</div>
            <div class="text-2xl font-bold text-gray-900">
                @if($wellnessMetric)
                    {{ number_format($wellnessMetric->value, 0) }}/10
                @else
                    <span class="text-gray-400">--</span>
                @endif
            </div>
        </div>
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-1">Last Survey</div>
            <div class="text-lg font-semibold text-gray-900">
                @if($latestSurvey && $latestSurvey->completed_at)
                    {{ $latestSurvey->completed_at->diffForHumans() }}
                @else
                    <span class="text-gray-400">None</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Performance Trends (Full Width) -->
    <div class="bg-white rounded-lg border border-gray-200 p-4 mb-6">
        <h3 class="text-sm font-semibold text-gray-900 mb-4">Performance Trends</h3>
        <livewire:contact-overview-charts
            :contact-type="\App\Models\Student::class"
            :contact-id="$student->id"
        />
    </div>

    <!-- Main Layout: Action Sidebar + Tabbed Activity -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Sidebar: Take Action -->
        <div class="lg:col-span-1 space-y-4">

            <!-- Quick Actions -->
            <div x-data="{ open: true }" class="bg-white rounded-lg border border-gray-200">
                <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left">
                    <h3 class="text-sm font-semibold text-gray-900">Quick Actions</h3>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="px-4 pb-4 border-t border-gray-100">
                    <div class="space-y-2 pt-3">
                        <button onclick="document.querySelector('[data-tab=notes]').click()"
                                class="w-full flex items-center gap-2 px-3 py-2 text-sm text-left text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <svg class="w-4 h-4 text-pulse-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                            Add Note
                        </button>
                        <button onclick="document.querySelector('[data-tab=resources]').click()"
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
            </div>

            <!-- Suggested Resources -->
            <div x-data="{ open: true }" class="bg-white rounded-lg border border-gray-200">
                <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left">
                    <h3 class="text-sm font-semibold text-gray-900">Suggested Resources</h3>
                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div x-show="open" x-collapse class="px-4 pb-4 border-t border-gray-100">
                    <div class="pt-3">
                        <livewire:resource-suggestions
                            contact-type="student"
                            :contact-id="$student->id"
                        />
                    </div>
                </div>
            </div>

            <!-- Student Details -->
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

            <!-- Strategic Plans -->
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
                                <div class="text-xs text-gray-500">{{ $plan->school_year }}</div>
                            </div>
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Main Content: Tabbed Activity -->
        <div class="lg:col-span-2">
            <div x-data="{ activeTab: 'timeline' }" class="bg-white rounded-lg border border-gray-200">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button @click="activeTab = 'timeline'" data-tab="timeline"
                                :class="activeTab === 'timeline' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                            Timeline
                        </button>
                        <button @click="activeTab = 'notes'" data-tab="notes"
                                :class="activeTab === 'notes' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="px-4 py-3 text-sm font-medium border-b-2 transition-colors">
                            Notes
                        </button>
                        <button @click="activeTab = 'surveys'" data-tab="surveys"
                                :class="activeTab === 'surveys' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="px-4 py-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-1">
                            Surveys
                            @if($student->surveyAttempts && $student->surveyAttempts->count() > 0)
                            <span class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $student->surveyAttempts->count() }}</span>
                            @endif
                        </button>
                        <button @click="activeTab = 'resources'" data-tab="resources"
                                :class="activeTab === 'resources' ? 'border-pulse-orange-500 text-pulse-orange-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="px-4 py-3 text-sm font-medium border-b-2 transition-colors flex items-center gap-1">
                            Resources
                            @if($student->resourceAssignments && $student->resourceAssignments->count() > 0)
                            <span class="px-1.5 py-0.5 text-xs bg-gray-100 text-gray-600 rounded">{{ $student->resourceAssignments->count() }}</span>
                            @endif
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-4">
                    <!-- Timeline Tab -->
                    <div x-show="activeTab === 'timeline'" x-cloak x-data="{ expandedItem: null }">
                        @php
                            // Combine all activities into a timeline
                            $timelineItems = collect();

                            // Add notes
                            foreach($student->notes ?? [] as $note) {
                                $timelineItems->push([
                                    'id' => 'note-' . $note->id,
                                    'model_id' => $note->id,
                                    'type' => 'note',
                                    'icon' => 'pencil',
                                    'color' => 'blue',
                                    'title' => ucfirst($note->note_type ?? 'General') . ' Note',
                                    'content' => $note->content,
                                    'date' => $note->created_at,
                                    'author' => $note->createdBy?->full_name ?? 'Unknown',
                                    'note_type' => $note->note_type,
                                    'is_private' => $note->is_private ?? false,
                                ]);
                            }

                            // Add survey attempts
                            foreach($student->surveyAttempts ?? [] as $attempt) {
                                $timelineItems->push([
                                    'id' => 'survey-' . $attempt->id,
                                    'model_id' => $attempt->id,
                                    'type' => 'survey',
                                    'icon' => 'clipboard',
                                    'color' => $attempt->status === 'completed' ? 'green' : 'yellow',
                                    'title' => ($attempt->survey->title ?? 'Survey') . ' - ' . ucfirst($attempt->status),
                                    'content' => $attempt->overall_score ? "Score: {$attempt->overall_score}" : null,
                                    'date' => $attempt->completed_at ?? $attempt->created_at,
                                    'author' => null,
                                    'status' => $attempt->status,
                                    'overall_score' => $attempt->overall_score,
                                    'risk_level' => $attempt->risk_level,
                                    'survey_title' => $attempt->survey->title ?? 'Unknown Survey',
                                    'responses' => $attempt->responses ?? [],
                                    'questions' => $attempt->survey->questions ?? [],
                                ]);
                            }

                            // Add resource assignments
                            foreach($student->resourceAssignments ?? [] as $assignment) {
                                $timelineItems->push([
                                    'id' => 'resource-' . $assignment->id,
                                    'model_id' => $assignment->id,
                                    'type' => 'resource',
                                    'icon' => 'book',
                                    'color' => 'purple',
                                    'title' => 'Resource Assigned: ' . ($assignment->resource->title ?? 'Unknown'),
                                    'content' => "{$assignment->progress_percent}% complete",
                                    'date' => $assignment->assigned_at ?? $assignment->created_at,
                                    'author' => $assignment->assigner?->name ?? null,
                                    'progress_percent' => $assignment->progress_percent ?? 0,
                                    'status' => $assignment->status ?? 'pending',
                                    'resource_title' => $assignment->resource->title ?? 'Unknown',
                                    'resource_description' => $assignment->resource->description ?? null,
                                    'resource_url' => $assignment->resource->url ?? null,
                                    'notes' => $assignment->notes ?? null,
                                ]);
                            }

                            // Sort by date descending
                            $timelineItems = $timelineItems->sortByDesc('date')->take(20);
                        @endphp

                        @if($timelineItems->count() > 0)
                        <div class="relative">
                            <!-- Timeline line -->
                            <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                            <div class="space-y-4">
                                @foreach($timelineItems as $item)
                                <div class="relative flex gap-4">
                                    <!-- Timeline dot -->
                                    <div class="relative z-10 flex items-center justify-center w-8 h-8 rounded-full flex-shrink-0
                                        @if($item['color'] === 'blue') bg-blue-100 text-blue-600
                                        @elseif($item['color'] === 'green') bg-green-100 text-green-600
                                        @elseif($item['color'] === 'yellow') bg-yellow-100 text-yellow-600
                                        @elseif($item['color'] === 'purple') bg-purple-100 text-purple-600
                                        @else bg-gray-100 text-gray-600
                                        @endif">
                                        @if($item['icon'] === 'pencil')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        @elseif($item['icon'] === 'clipboard')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                        </svg>
                                        @elseif($item['icon'] === 'book')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                        </svg>
                                        @endif
                                    </div>

                                    <!-- Content (Clickable) -->
                                    <div class="flex-1 min-w-0 pb-4">
                                        <button
                                            @click="expandedItem = expandedItem === '{{ $item['id'] }}' ? null : '{{ $item['id'] }}'"
                                            class="w-full text-left p-3 -m-3 rounded-lg hover:bg-gray-50 transition-colors"
                                        >
                                            <div class="flex items-center justify-between gap-2">
                                                <p class="text-sm font-medium text-gray-900">{{ $item['title'] }}</p>
                                                <div class="flex items-center gap-2">
                                                    <time class="text-xs text-gray-500 whitespace-nowrap">{{ $item['date']?->diffForHumans() ?? 'Unknown' }}</time>
                                                    <svg class="w-4 h-4 text-gray-400 transition-transform" :class="{ 'rotate-180': expandedItem === '{{ $item['id'] }}' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                    </svg>
                                                </div>
                                            </div>
                                            @if($item['content'])
                                            <p class="mt-1 text-sm text-gray-600" :class="{ 'line-clamp-2': expandedItem !== '{{ $item['id'] }}' }">{{ $item['content'] }}</p>
                                            @endif
                                            @if($item['author'])
                                            <p class="mt-1 text-xs text-gray-400">by {{ $item['author'] }}</p>
                                            @endif
                                        </button>

                                        <!-- Expanded Content -->
                                        <div x-show="expandedItem === '{{ $item['id'] }}'" x-collapse class="mt-3 p-3 bg-gray-50 rounded-lg border border-gray-200">
                                            @if($item['type'] === 'note')
                                            <!-- Note Details -->
                                            <div class="space-y-3">
                                                <div class="flex items-center gap-2">
                                                    @php
                                                        $noteTypeColor = match($item['note_type'] ?? 'general') {
                                                            'concern' => 'red',
                                                            'follow_up' => 'yellow',
                                                            'milestone' => 'green',
                                                            default => 'gray',
                                                        };
                                                    @endphp
                                                    <span class="px-2 py-0.5 text-xs rounded-full bg-{{ $noteTypeColor }}-100 text-{{ $noteTypeColor }}-700">
                                                        {{ ucfirst(str_replace('_', ' ', $item['note_type'] ?? 'general')) }}
                                                    </span>
                                                    @if($item['is_private'])
                                                    <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700">Private</span>
                                                    @endif
                                                </div>
                                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $item['content'] }}</p>
                                                <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                                    <span class="text-xs text-gray-500">{{ $item['date']?->format('M d, Y h:i A') }}</span>
                                                    <button
                                                        onclick="document.querySelector('[data-tab=notes]').click()"
                                                        class="text-xs text-pulse-orange-600 hover:text-pulse-orange-700"
                                                    >
                                                        View in Notes Tab
                                                    </button>
                                                </div>
                                            </div>
                                            @elseif($item['type'] === 'survey')
                                            <!-- Survey Details -->
                                            <div class="space-y-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-0.5 text-xs rounded-full {{ $item['status'] === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                        {{ ucfirst($item['status']) }}
                                                    </span>
                                                    @if($item['risk_level'])
                                                    <span class="px-2 py-0.5 text-xs rounded-full {{ $item['risk_level'] === 'high' ? 'bg-red-100 text-red-700' : ($item['risk_level'] === 'medium' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                                                        {{ ucfirst($item['risk_level']) }} Risk
                                                    </span>
                                                    @endif
                                                    @if($item['overall_score'])
                                                    <span class="text-sm font-semibold text-gray-700">Score: {{ number_format($item['overall_score'], 1) }}</span>
                                                    @endif
                                                </div>
                                                @if(count($item['questions']) > 0 && count($item['responses']) > 0)
                                                <div class="space-y-2 max-h-48 overflow-y-auto">
                                                    @foreach(array_slice($item['questions'], 0, 5) as $index => $question)
                                                    @php
                                                        $questionId = $question['id'] ?? "q{$index}";
                                                        $response = $item['responses'][$questionId] ?? null;
                                                    @endphp
                                                    <div class="p-2 bg-white rounded border border-gray-100">
                                                        <p class="text-xs font-medium text-gray-600">{{ Str::limit($question['text'] ?? $question['question'] ?? 'Question', 80) }}</p>
                                                        <p class="text-sm text-gray-900 mt-1">
                                                            @if($response !== null)
                                                            {{ is_array($response) ? implode(', ', $response) : $response }}
                                                            @else
                                                            <span class="text-gray-400 italic">No response</span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                    @endforeach
                                                    @if(count($item['questions']) > 5)
                                                    <p class="text-xs text-gray-500 text-center">+ {{ count($item['questions']) - 5 }} more questions</p>
                                                    @endif
                                                </div>
                                                @endif
                                                <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                                    <span class="text-xs text-gray-500">{{ $item['date']?->format('M d, Y h:i A') }}</span>
                                                    <button
                                                        onclick="document.querySelector('[data-tab=surveys]').click()"
                                                        class="text-xs text-pulse-orange-600 hover:text-pulse-orange-700"
                                                    >
                                                        View in Surveys Tab
                                                    </button>
                                                </div>
                                            </div>
                                            @elseif($item['type'] === 'resource')
                                            <!-- Resource Details -->
                                            <div class="space-y-3">
                                                <div class="flex items-center gap-2">
                                                    <span class="px-2 py-0.5 text-xs rounded-full {{ $item['status'] === 'completed' ? 'bg-green-100 text-green-700' : ($item['status'] === 'in_progress' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                                                        {{ ucfirst(str_replace('_', ' ', $item['status'])) }}
                                                    </span>
                                                    <div class="flex items-center gap-1 text-xs text-gray-500">
                                                        <div class="w-16 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                                            <div class="h-full bg-pulse-orange-500" style="width: {{ $item['progress_percent'] }}%"></div>
                                                        </div>
                                                        {{ $item['progress_percent'] }}%
                                                    </div>
                                                </div>
                                                @if($item['resource_description'])
                                                <p class="text-sm text-gray-600">{{ Str::limit($item['resource_description'], 150) }}</p>
                                                @endif
                                                @if($item['notes'])
                                                <div class="p-2 bg-white rounded border border-gray-100">
                                                    <p class="text-xs font-medium text-gray-500 mb-1">Notes</p>
                                                    <p class="text-sm text-gray-700">{{ $item['notes'] }}</p>
                                                </div>
                                                @endif
                                                <div class="flex items-center justify-between pt-2 border-t border-gray-200">
                                                    <span class="text-xs text-gray-500">{{ $item['date']?->format('M d, Y h:i A') }}</span>
                                                    <div class="flex items-center gap-3">
                                                        @if($item['resource_url'])
                                                        <a href="{{ $item['resource_url'] }}" target="_blank" class="text-xs text-blue-600 hover:text-blue-700">Open Resource</a>
                                                        @endif
                                                        <button
                                                            onclick="document.querySelector('[data-tab=resources]').click()"
                                                            class="text-xs text-pulse-orange-600 hover:text-pulse-orange-700"
                                                        >
                                                            View in Resources Tab
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <p class="text-sm text-gray-500">No activity yet</p>
                        </div>
                        @endif
                    </div>

                    <!-- Notes Tab -->
                    <div x-show="activeTab === 'notes'" x-cloak>
                        <livewire:contact-notes
                            contact-type="student"
                            :contact-id="$student->id"
                        />
                    </div>

                    <!-- Surveys Tab -->
                    <div x-show="activeTab === 'surveys'" x-cloak>
                        <livewire:contact-surveys
                            contact-type="student"
                            :contact-id="$student->id"
                        />
                    </div>

                    <!-- Resources Tab -->
                    <div x-show="activeTab === 'resources'" x-cloak>
                        <livewire:contact-resources
                            contact-type="student"
                            :contact-id="$student->id"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.dashboard>
