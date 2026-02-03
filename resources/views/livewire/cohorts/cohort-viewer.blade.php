<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('learn.dashboard') }}" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                    </a>
                    <div>
                        <h1 class="text-lg font-semibold text-gray-900">{{ $cohort->course?->title }}</h1>
                        <p class="text-sm text-gray-500">{{ $cohort->name }}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Overall Progress -->
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-600">@term('progress_label'):</span>
                        <div class="w-32 bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full transition-all" style="width: {{ $membership?->progress_percent ?? 0 }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-gray-900">{{ $membership?->progress_percent ?? 0 }}%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex gap-6">
            <!-- Sidebar - Step List -->
            <div class="w-80 flex-shrink-0">
                <div class="bg-white rounded-lg border border-gray-200 overflow-hidden sticky top-6">
                    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                        <h2 class="font-medium text-gray-900">@term('module_plural')</h2>
                        <p class="text-xs text-gray-500 mt-1">{{ $steps->count() }} @term('step_plural')</p>
                    </div>
                    <div class="divide-y divide-gray-100 max-h-[calc(100vh-200px)] overflow-y-auto">
                        @foreach($steps as $index => $step)
                            @php
                                $status = $this->getStepStatus($step->id);
                                $isAvailable = $this->isStepAvailable($step);
                                $isCurrent = $currentStep && $currentStep->id === $step->id;
                            @endphp
                            <button
                                wire:click="selectStep({{ $step->id }})"
                                @if(!$isAvailable) disabled @endif
                                class="w-full px-4 py-3 text-left hover:bg-gray-50 transition-colors flex items-start space-x-3
                                    {{ $isCurrent ? 'bg-purple-50 border-l-4 border-purple-600' : '' }}
                                    {{ !$isAvailable ? 'opacity-50 cursor-not-allowed' : '' }}"
                            >
                                <!-- Status Icon -->
                                <div class="flex-shrink-0 mt-0.5">
                                    @if(!$isAvailable)
                                        <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                            </svg>
                                        </div>
                                    @elseif($status === 'completed')
                                        <div class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    @elseif($status === 'in_progress')
                                        <div class="w-6 h-6 rounded-full bg-purple-100 flex items-center justify-center">
                                            <div class="w-2 h-2 rounded-full bg-purple-600"></div>
                                        </div>
                                    @else
                                        <div class="w-6 h-6 rounded-full bg-gray-100 flex items-center justify-center text-xs font-medium text-gray-500">
                                            {{ $index + 1 }}
                                        </div>
                                    @endif
                                </div>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">{{ $step->title }}</p>
                                    @if(!$isAvailable && $cohort->drip_content)
                                        @php
                                            $schedule = collect($cohort->drip_schedule)->firstWhere('step_id', $step->id);
                                            $releaseDate = $schedule ? $cohort->start_date->copy()->addDays($schedule['days_after_start'] ?? 0) : null;
                                        @endphp
                                        @if($releaseDate)
                                            <p class="text-xs text-gray-400">Unlocks {{ $releaseDate->format('M d') }}</p>
                                        @endif
                                    @elseif($status === 'completed')
                                        <p class="text-xs text-green-600">Completed</p>
                                    @elseif($status === 'in_progress')
                                        <p class="text-xs text-purple-600">In @term('progress_label')</p>
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="flex-1">
                @if($currentStep)
                    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
                        <!-- Step Header -->
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-purple-600 font-medium">
                                        @term('step_singular') {{ $steps->search(fn($s) => $s->id === $currentStep->id) + 1 }} of {{ $steps->count() }}
                                    </p>
                                    <h2 class="text-xl font-semibold text-gray-900 mt-1">{{ $currentStep->title }}</h2>
                                </div>
                                @php
                                    $currentStatus = $this->getStepStatus($currentStep->id);
                                @endphp
                                @if($currentStatus === 'completed')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                        </svg>
                                        Completed
                                    </span>
                                @elseif($currentStatus === 'in_progress')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                        In @term('progress_label')
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Step Content -->
                        <div class="p-6">
                            @if($currentStep->description)
                                <div class="prose prose-purple max-w-none mb-6">
                                    {!! $currentStep->description !!}
                                </div>
                            @endif

                            @if($currentStep->content)
                                <div class="prose prose-purple max-w-none">
                                    {!! $currentStep->content !!}
                                </div>
                            @endif

                            <!-- Video Embed -->
                            @if($currentStep->video_url)
                                <div class="mt-6 aspect-video bg-gray-100 rounded-lg overflow-hidden">
                                    <iframe
                                        src="{{ $currentStep->video_url }}"
                                        class="w-full h-full"
                                        frameborder="0"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen
                                    ></iframe>
                                </div>
                            @endif

                            <!-- Downloadable Resources -->
                            @if($currentStep->resources && count($currentStep->resources) > 0)
                                <div class="mt-6 pt-6 border-t border-gray-200">
                                    <h3 class="text-sm font-medium text-gray-900 mb-3">Resources</h3>
                                    <div class="space-y-2">
                                        @foreach($currentStep->resources as $resource)
                                            <a href="{{ $resource['url'] ?? '#' }}" target="_blank" class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                                <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="text-sm text-gray-700">{{ $resource['name'] ?? 'Download' }}</span>
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Step Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <!-- Previous Button -->
                                @php
                                    $currentIndex = $steps->search(fn($s) => $s->id === $currentStep->id);
                                    $prevStep = $currentIndex > 0 ? $steps[$currentIndex - 1] : null;
                                    $nextStep = $currentIndex < $steps->count() - 1 ? $steps[$currentIndex + 1] : null;
                                @endphp
                                <div>
                                    @if($prevStep)
                                        <button
                                            wire:click="selectStep({{ $prevStep->id }})"
                                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                            </svg>
                                            Previous
                                        </button>
                                    @endif
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex items-center space-x-3">
                                    @if($currentStatus === 'not_started')
                                        <button
                                            wire:click="startStep"
                                            class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700"
                                        >
                                            Start @term('step_singular')
                                        </button>
                                    @elseif($currentStatus === 'in_progress')
                                        <button
                                            wire:click="completeStep"
                                            class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700"
                                        >
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Mark Complete
                                        </button>
                                    @elseif($currentStatus === 'completed' && $nextStep && $this->isStepAvailable($nextStep))
                                        <button
                                            wire:click="selectStep({{ $nextStep->id }})"
                                            class="inline-flex items-center px-6 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700"
                                        >
                                            Next @term('step_singular')
                                            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                        </button>
                                    @elseif($currentStatus === 'completed' && !$nextStep)
                                        <!-- Course Complete -->
                                        <span class="inline-flex items-center px-6 py-2 text-sm font-medium text-green-700 bg-green-100 rounded-lg">
                                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            @term('course_singular') Complete!
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Completion Celebration -->
                    @if($membership && $membership->progress_percent === 100)
                        <div class="mt-6 bg-gradient-to-r from-purple-600 to-indigo-600 rounded-lg p-6 text-white">
                            <div class="flex items-center space-x-4">
                                <div class="flex-shrink-0">
                                    <svg class="w-12 h-12" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </div>
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold">Congratulations!</h3>
                                    <p class="text-purple-100">You've completed all @term('step_plural') in this @term('course_singular').</p>
                                </div>
                                @if($cohort->course?->certificate_enabled)
                                    <a href="#" class="px-4 py-2 bg-white text-purple-600 rounded-lg font-medium hover:bg-purple-50">
                                        View @term('certificate_singular')
                                    </a>
                                @endif
                            </div>
                        </div>
                    @endif
                @else
                    <!-- No Steps -->
                    <div class="bg-white rounded-lg border border-gray-200 p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <h3 class="mt-4 text-lg font-medium text-gray-900">No content yet</h3>
                        <p class="mt-1 text-gray-500">This @term('course_singular') doesn't have any @term('step_plural') configured yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
