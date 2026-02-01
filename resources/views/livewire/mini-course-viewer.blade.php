<div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumbs -->
        <x-breadcrumbs :items="[
            ['label' => 'Resources', 'url' => route('resources.index')],
            ['label' => 'Courses', 'url' => route('resources.index') . '?activeTab=courses'],
            ['label' => $course->title],
        ]" />

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
                        <div class="space-y-6 mb-6">
                            @if($currentStep->instructions)
                            <div class="bg-gray-50 rounded-lg p-4">
                                {!! nl2br(e($currentStep->instructions)) !!}
                            </div>
                            @endif

                            {{-- Video Embed --}}
                            @if($currentStep->content_data && isset($currentStep->content_data['video_url']))
                            <div class="aspect-video rounded-lg overflow-hidden bg-gray-900">
                                <iframe
                                    src="{{ $currentStep->content_data['video_url'] }}"
                                    class="w-full h-full"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen
                                ></iframe>
                            </div>
                            @endif

                            {{-- Main Body Content (Markdown) --}}
                            @if($currentStep->content_data && isset($currentStep->content_data['body']))
                            <div class="prose prose-sm max-w-none prose-headings:text-gray-900 prose-p:text-gray-600 prose-strong:text-gray-900 prose-ul:text-gray-600 prose-ol:text-gray-600">
                                {!! \Illuminate\Support\Str::markdown($currentStep->content_data['body']) !!}
                            </div>
                            @endif

                            {{-- Key Points Callout --}}
                            @if($currentStep->content_data && isset($currentStep->content_data['key_points']) && count($currentStep->content_data['key_points']) > 0)
                            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                                <h4 class="flex items-center gap-2 text-sm font-semibold text-amber-800 mb-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                                    </svg>
                                    Key Takeaways
                                </h4>
                                <ul class="space-y-2">
                                    @foreach($currentStep->content_data['key_points'] as $point)
                                    <li class="flex items-start gap-2 text-sm text-amber-900">
                                        <svg class="w-4 h-4 mt-0.5 text-amber-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                        {{ $point }}
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            {{-- Downloads Section --}}
                            @if($currentStep->content_data && isset($currentStep->content_data['downloads']) && count($currentStep->content_data['downloads']) > 0)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                <h4 class="flex items-center gap-2 text-sm font-semibold text-blue-800 mb-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Downloadable Resources
                                </h4>
                                <div class="space-y-2">
                                    @foreach($currentStep->content_data['downloads'] as $download)
                                    <a href="#" class="flex items-center justify-between p-3 bg-white rounded-lg border border-blue-100 hover:border-blue-300 hover:bg-blue-50 transition-colors group">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                                                @if(($download['type'] ?? 'pdf') === 'pdf')
                                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"></path>
                                                </svg>
                                                @else
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                                </svg>
                                                @endif
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900 group-hover:text-blue-700">{{ $download['title'] }}</p>
                                                <p class="text-xs text-gray-500">{{ strtoupper($download['type'] ?? 'PDF') }} &bull; {{ $download['size'] ?? 'Unknown size' }}</p>
                                            </div>
                                        </div>
                                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                        </svg>
                                    </a>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            {{-- Assessment/Quiz Questions --}}
                            @if($currentStep->content_data && isset($currentStep->content_data['questions']) && count($currentStep->content_data['questions']) > 0)
                            <div class="space-y-4">
                                @if(isset($currentStep->content_data['instructions']))
                                <p class="text-sm text-gray-600 italic">{{ $currentStep->content_data['instructions'] }}</p>
                                @endif

                                @foreach($currentStep->content_data['questions'] as $question)
                                <div class="bg-white border border-gray-200 rounded-lg p-4">
                                    <p class="font-medium text-gray-900 mb-3">{{ $loop->iteration }}. {{ $question['question'] }}</p>

                                    @if(($question['type'] ?? 'multiple_choice') === 'multiple_choice')
                                    <div class="space-y-2">
                                        @foreach($question['options'] as $optionIndex => $option)
                                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 hover:border-pulse-orange-300 hover:bg-pulse-orange-50 cursor-pointer transition-colors">
                                            <input type="radio" name="question_{{ $question['id'] }}" value="{{ $optionIndex }}" class="w-4 h-4 text-pulse-orange-500 focus:ring-pulse-orange-500">
                                            <span class="text-sm text-gray-700">{{ $option }}</span>
                                        </label>
                                        @endforeach
                                    </div>
                                    @elseif(($question['type'] ?? '') === 'scale')
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between text-xs text-gray-500">
                                            <span>{{ $question['min_label'] ?? 'Low' }}</span>
                                            <span>{{ $question['max_label'] ?? 'High' }}</span>
                                        </div>
                                        <div class="flex items-center justify-between gap-2">
                                            @for($i = ($question['min'] ?? 1); $i <= ($question['max'] ?? 5); $i++)
                                            <label class="flex-1">
                                                <input type="radio" name="question_{{ $question['id'] }}" value="{{ $i }}" class="sr-only peer">
                                                <div class="w-full py-3 text-center rounded-lg border border-gray-200 cursor-pointer peer-checked:bg-pulse-orange-500 peer-checked:border-pulse-orange-500 peer-checked:text-white hover:border-pulse-orange-300 transition-colors">
                                                    {{ $i }}
                                                </div>
                                            </label>
                                            @endfor
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                            @endif

                            {{-- Interactive Activity (Breathing Exercise) --}}
                            @if($currentStep->content_data && isset($currentStep->content_data['activity']))
                            @php $activity = $currentStep->content_data['activity']; @endphp
                            <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-200 rounded-lg p-6">
                                <h4 class="flex items-center gap-2 text-lg font-semibold text-indigo-900 mb-2">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $activity['title'] ?? 'Interactive Activity' }}
                                </h4>
                                <p class="text-sm text-indigo-700 mb-4">{{ $activity['instructions'] ?? '' }}</p>

                                @if(isset($activity['steps']) && count($activity['steps']) > 0)
                                <div class="bg-white/60 rounded-lg p-4">
                                    <div class="flex items-center justify-around">
                                        @foreach($activity['steps'] as $step)
                                        <div class="text-center">
                                            <div class="w-16 h-16 rounded-full bg-indigo-100 flex items-center justify-center mx-auto mb-2">
                                                <span class="text-2xl font-bold text-indigo-600">{{ $step['duration'] }}</span>
                                            </div>
                                            <p class="text-sm font-medium text-indigo-900">{{ $step['action'] }}</p>
                                            <p class="text-xs text-indigo-600">seconds</p>
                                        </div>
                                        @if(!$loop->last)
                                        <svg class="w-6 h-6 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        @endif
                                        @endforeach
                                    </div>
                                    @if(isset($activity['cycles']))
                                    <p class="text-center text-sm text-indigo-600 mt-4">Repeat {{ $activity['cycles'] }} times</p>
                                    @endif
                                </div>
                                @endif
                            </div>
                            @endif

                            {{-- Human Connection Resources --}}
                            @if($currentStep->content_data && isset($currentStep->content_data['resources']) && count($currentStep->content_data['resources']) > 0)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                                <h4 class="flex items-center gap-2 text-sm font-semibold text-green-800 mb-3">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                    </svg>
                                    Support Resources
                                </h4>
                                <div class="space-y-2">
                                    @foreach($currentStep->content_data['resources'] as $resource)
                                    <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-green-100">
                                        <div>
                                            <p class="text-sm font-medium text-gray-900">{{ $resource['title'] }}</p>
                                            <p class="text-xs text-gray-500">{{ $resource['description'] ?? '' }}</p>
                                        </div>
                                        @if(isset($resource['url']))
                                        <a href="{{ $resource['url'] }}" target="_blank" class="px-3 py-1.5 text-xs font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200 transition-colors">
                                            Visit
                                        </a>
                                        @else
                                        <button class="px-3 py-1.5 text-xs font-medium text-green-700 bg-green-100 rounded-lg hover:bg-green-200 transition-colors">
                                            Connect
                                        </button>
                                        @endif
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            {{-- Reflection Prompts --}}
                            @if($currentStep->content_data && isset($currentStep->content_data['prompts']) && count($currentStep->content_data['prompts']) > 0)
                            <div class="space-y-4">
                                <h4 class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                                    <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                    </svg>
                                    Reflection Prompts
                                </h4>
                                @foreach($currentStep->content_data['prompts'] as $prompt)
                                <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                                    <label class="block text-sm font-medium text-purple-900 mb-2">{{ $prompt }}</label>
                                    <textarea class="w-full px-3 py-2 border border-purple-200 rounded-lg text-sm resize-none focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-white" rows="3" placeholder="Write your thoughts here..."></textarea>
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
