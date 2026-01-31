<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Back Link -->
        <a href="{{ route('resources.index') }}?activeTab=courses" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-6">
            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Back to Courses
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Course Header -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-br from-pulse-orange-500 to-pulse-orange-600 px-6 py-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="px-3 py-1 bg-white/20 text-white text-xs rounded-full mb-3 inline-block">
                                    {{ ucfirst(str_replace('_', ' ', $course->course_type)) }}
                                </span>
                                <h1 class="text-2xl font-bold text-white mb-2">{{ $course->title }}</h1>
                                <p class="text-orange-100">{{ $course->description }}</p>
                            </div>
                            @if($enrollment)
                            <div class="text-right">
                                <div class="text-3xl font-bold text-white">{{ $enrollment->progress_percent }}%</div>
                                <div class="text-orange-200 text-sm">Complete</div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    @if($enrollment)
                    <div class="h-2 bg-orange-200">
                        <div class="h-full bg-green-500 transition-all" style="width: {{ $enrollment->progress_percent }}%"></div>
                    </div>
                    @endif

                    <!-- Objectives -->
                    @if($course->objectives && count($course->objectives) > 0)
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Objectives</h2>
                        <ul class="space-y-2">
                            @foreach($course->objectives as $index => $objective)
                            <li class="flex items-start gap-2 text-sm">
                                @if($enrollment && $enrollment->progress_percent >= (($index + 1) / count($course->objectives)) * 100)
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                @else
                                <svg class="w-5 h-5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                @endif
                                <span class="text-gray-700">{{ $objective }}</span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                </div>

                <!-- Current Step Content -->
                @if($currentStep)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6">
                        <!-- Step Header -->
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <span class="px-2 py-1 text-xs font-medium rounded bg-gray-100 text-gray-600 mb-2 inline-block">
                                    Step {{ $course->steps->search(fn($s) => $s->id === $currentStep->id) + 1 }} of {{ $course->steps->count() }}
                                </span>
                                <h2 class="text-xl font-bold text-gray-900">{{ $currentStep->title }}</h2>
                            </div>
                            @if($currentStep->estimated_duration_minutes)
                            <span class="text-sm text-gray-500">
                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                {{ $currentStep->estimated_duration_minutes }} min
                            </span>
                            @endif
                        </div>

                        @if($currentStep->description)
                        <p class="text-gray-600 mb-4">{{ $currentStep->description }}</p>
                        @endif

                        <!-- Step Content -->
                        <div class="prose prose-sm max-w-none mb-6">
                            @if($currentStep->instructions)
                            <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                {!! nl2br(e($currentStep->instructions)) !!}
                            </div>
                            @endif

                            @if($currentStep->content_data && isset($currentStep->content_data['body']))
                            <div class="whitespace-pre-wrap">{!! \Illuminate\Support\Str::markdown($currentStep->content_data['body']) !!}</div>
                            @endif

                            @if($currentStep->content_data && isset($currentStep->content_data['prompts']))
                            <div class="space-y-4 mt-4">
                                @foreach($currentStep->content_data['prompts'] as $prompt)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ $prompt }}</label>
                                    <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-pulse-orange-500 focus:border-transparent" rows="3" placeholder="Your response..."></textarea>
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <!-- Step Navigation -->
                        <div class="flex items-center justify-between border-t border-gray-200 pt-4">
                            @if($currentStep->previous_step)
                            <button wire:click="selectStep({{ $currentStep->previous_step->id }})" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                </svg>
                                Previous
                            </button>
                            @else
                            <div></div>
                            @endif

                            @if($enrollment)
                            <button wire:click="completeCurrentStep" class="inline-flex items-center px-4 py-2 bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors">
                                @if($currentStep->next_step)
                                Mark Complete & Continue
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                @else
                                Complete Course
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                @endif
                            </button>
                            @else
                            <button wire:click="startEnrollment" class="inline-flex items-center px-4 py-2 bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600 transition-colors">
                                Start This Course
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Why This Course? (Explainability) -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <button wire:click="toggleRationale" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                        <span class="font-medium text-gray-900">Why This Course?</span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform {{ $showRationale ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    @if($showRationale)
                    <div class="p-4 border-t border-gray-200 bg-gray-50">
                        @if($course->rationale)
                        <p class="text-gray-700 mb-4">{{ $course->rationale }}</p>
                        @endif
                        @if($course->expected_experience)
                        <div class="mt-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">What to Expect</h4>
                            <p class="text-gray-600 text-sm">{{ $course->expected_experience }}</p>
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            <!-- Sidebar - Steps List -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Push to Schools Button -->
                @if($canPush)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
                    <button
                        wire:click="openPushModal"
                        class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 transition-colors"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Push to Schools
                    </button>
                </div>
                @endif

                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden sticky top-6">
                    <div class="p-4 border-b border-gray-200">
                        <h3 class="font-semibold text-gray-900">Course Steps</h3>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto">
                        @foreach($course->steps as $index => $step)
                        <button
                            wire:click="selectStep({{ $step->id }})"
                            class="w-full flex items-center gap-3 p-3 text-left hover:bg-gray-50 transition-colors {{ $currentStep && $currentStep->id === $step->id ? 'bg-pulse-orange-50 border-l-4 border-pulse-orange-500' : '' }}"
                        >
                            <!-- Step Status Icon -->
                            <div class="flex-shrink-0">
                                @if(isset($stepProgress[$step->id]) && $stepProgress[$step->id] === 'completed')
                                <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                @elseif($currentStep && $currentStep->id === $step->id)
                                <div class="w-8 h-8 rounded-full bg-pulse-orange-100 flex items-center justify-center">
                                    <span class="text-sm font-medium text-pulse-orange-600">{{ $index + 1 }}</span>
                                </div>
                                @else
                                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                                    <span class="text-sm text-gray-500">{{ $index + 1 }}</span>
                                </div>
                                @endif
                            </div>

                            <!-- Step Info -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $step->title }}</p>
                                <p class="text-xs text-gray-500">{{ ucfirst($step->step_type) }}</p>
                            </div>

                            <!-- Duration -->
                            @if($step->estimated_duration_minutes)
                            <span class="text-xs text-gray-400">{{ $step->estimated_duration_minutes }}m</span>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Push Content Modal -->
    @livewire('push-content-modal')
</div>
