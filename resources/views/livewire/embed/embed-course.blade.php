<div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-4">
    @if($notFound)
        <div class="flex items-center justify-center min-h-[400px]">
            <div class="text-center">
                <x-icon name="academic-cap" class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                <h2 class="text-xl font-semibold text-gray-900 mb-2">Course Not Found</h2>
                <p class="text-gray-500">This course is no longer available or has been made private.</p>
            </div>
        </div>
    @else
        <div class="max-w-3xl mx-auto">
            {{-- Course Card --}}
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 px-6 py-5 text-white">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-white/20 flex items-center justify-center">
                                <x-icon name="academic-cap" class="w-5 h-5" />
                            </div>
                            <div>
                                <h1 class="text-xl font-bold">{{ $course->title }}</h1>
                                <p class="text-sm opacity-90">
                                    {{ ucwords(str_replace('_', ' ', $course->course_type)) }}
                                    @if($course->estimated_duration_minutes)
                                        • {{ $course->estimated_duration_minutes }} min
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="text-sm font-medium">Step {{ $currentStepIndex + 1 }} of {{ $course->steps->count() }}</span>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    <div class="mt-4">
                        <div class="h-2 bg-white/30 rounded-full overflow-hidden">
                            <div
                                class="h-full bg-white rounded-full transition-all duration-300"
                                style="width: {{ ($currentStepIndex + 1) / max($course->steps->count(), 1) * 100 }}%"
                            ></div>
                        </div>
                    </div>
                </div>

                {{-- Step Navigation (Pills) --}}
                <div class="px-6 py-3 bg-gray-50 border-b border-gray-200 overflow-x-auto">
                    <div class="flex gap-2">
                        @foreach($course->steps as $index => $step)
                            <button
                                wire:click="goToStep({{ $index }})"
                                class="flex-shrink-0 px-3 py-1.5 rounded-full text-xs font-medium transition-colors
                                    {{ $index === $currentStepIndex
                                        ? 'bg-orange-500 text-white'
                                        : ($index < $currentStepIndex
                                            ? 'bg-green-100 text-green-700'
                                            : 'bg-gray-200 text-gray-600 hover:bg-gray-300')
                                    }}"
                            >
                                @if($index < $currentStepIndex)
                                    <x-icon name="check" class="w-3 h-3 inline-block mr-1" />
                                @endif
                                {{ $index + 1 }}. {{ Str::limit($step->title, 15) }}
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Current Step Content --}}
                @if($course->steps->count() > 0)
                    @php $currentStep = $course->steps[$currentStepIndex]; @endphp
                    <div class="p-6">
                        <div class="flex items-start gap-4 mb-4">
                            @php
                                $stepIcon = match($currentStep->step_type) {
                                    'content' => 'document-text',
                                    'reflection' => 'chat-bubble-left-ellipsis',
                                    'action' => 'check-circle',
                                    'practice' => 'academic-cap',
                                    'human_connection' => 'user-group',
                                    'assessment' => 'clipboard-document-check',
                                    'checkpoint' => 'flag',
                                    default => 'document',
                                };
                                $stepColor = match($currentStep->step_type) {
                                    'content' => 'blue',
                                    'reflection' => 'purple',
                                    'action' => 'green',
                                    'practice' => 'indigo',
                                    'human_connection' => 'pink',
                                    'assessment' => 'yellow',
                                    'checkpoint' => 'orange',
                                    default => 'gray',
                                };
                            @endphp
                            <div class="w-12 h-12 rounded-xl bg-{{ $stepColor }}-100 flex items-center justify-center flex-shrink-0">
                                <x-icon name="{{ $stepIcon }}" class="w-6 h-6 text-{{ $stepColor }}-600" />
                            </div>
                            <div>
                                <span class="text-xs font-medium text-{{ $stepColor }}-600 uppercase tracking-wide">
                                    {{ ucfirst(str_replace('_', ' ', $currentStep->step_type)) }}
                                </span>
                                <h2 class="text-xl font-bold text-gray-900">{{ $currentStep->title }}</h2>
                            </div>
                        </div>

                        @if($currentStep->description)
                            <p class="text-gray-600 mb-4">{{ $currentStep->description }}</p>
                        @endif

                        @if($currentStep->instructions)
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                                <p class="text-sm text-blue-800">
                                    <x-icon name="light-bulb" class="w-4 h-4 inline-block mr-1" />
                                    {{ $currentStep->instructions }}
                                </p>
                            </div>
                        @endif

                        {{-- Content based on content_type --}}
                        @if($currentStep->content_data)
                            @if(isset($currentStep->content_data['body']))
                                <div class="prose prose-sm max-w-none">
                                    {!! Str::markdown($currentStep->content_data['body']) !!}
                                </div>
                            @endif

                            @if(isset($currentStep->content_data['prompts']))
                                <div class="space-y-4 mt-4">
                                    @foreach($currentStep->content_data['prompts'] as $prompt)
                                        <div class="bg-gray-50 rounded-lg p-4">
                                            <p class="text-sm font-medium text-gray-700 mb-2">{{ $prompt }}</p>
                                            <textarea
                                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm resize-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
                                                rows="3"
                                                placeholder="Your response..."
                                            ></textarea>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif

                        @if($currentStep->estimated_duration_minutes)
                            <p class="text-xs text-gray-500 mt-4">
                                <x-icon name="clock" class="w-3 h-3 inline-block mr-1" />
                                Estimated time: {{ $currentStep->estimated_duration_minutes }} minutes
                            </p>
                        @endif
                    </div>

                    {{-- Navigation --}}
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
                        <button
                            wire:click="previousStep"
                            @if($currentStepIndex === 0) disabled @endif
                            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg transition-colors
                                {{ $currentStepIndex === 0
                                    ? 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    : 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
                                }}"
                        >
                            <x-icon name="chevron-left" class="w-4 h-4" />
                            Previous
                        </button>

                        @if($currentStepIndex < $course->steps->count() - 1)
                            <button
                                wire:click="nextStep"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg hover:bg-orange-600 transition-colors"
                            >
                                Next
                                <x-icon name="chevron-right" class="w-4 h-4" />
                            </button>
                        @else
                            <div class="inline-flex items-center gap-2 px-4 py-2 bg-green-500 text-white text-sm font-medium rounded-lg">
                                <x-icon name="check-circle" class="w-4 h-4" />
                                Course Complete!
                            </div>
                        @endif
                    </div>
                @else
                    <div class="p-6 text-center text-gray-500">
                        This course has no steps yet.
                    </div>
                @endif

                {{-- Footer --}}
                <div class="bg-white px-6 py-3 border-t border-gray-100">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">
                            Powered by Pulse
                        </span>
                        @if($course->organization)
                            <span class="text-xs text-gray-500">
                                {{ $course->organization->org_name }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
