<div class="max-w-4xl mx-auto" wire:poll.5s="checkGenerationStatus">
    {{-- Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                <x-icon name="sparkles" class="w-5 h-5 text-purple-600" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">AI Course Generator</h1>
                <p class="text-sm text-gray-500">Generate personalized mini-courses from your resource library</p>
            </div>
        </div>
    </div>

    @if($coursePreview)
        {{-- Course Preview --}}
        <div class="space-y-6">
            {{-- Success Banner --}}
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <x-icon name="check-circle" class="w-6 h-6 text-green-600" />
                    <div class="flex-1">
                        <p class="font-medium text-green-900">Course generated successfully!</p>
                        <p class="text-sm text-green-700">Review the course below and edit or publish when ready.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="startNew" class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Generate Another
                        </button>
                        <button wire:click="editCourse" class="px-3 py-1.5 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600">
                            Edit Course
                        </button>
                    </div>
                </div>
            </div>

            {{-- Course Card --}}
            <x-card>
                <x-slot:header>
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">{{ $coursePreview['title'] }}</h2>
                            <div class="flex items-center gap-3 mt-1">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-700">
                                    {{ ucwords(str_replace('_', ' ', $coursePreview['course_type'])) }}
                                </span>
                                <span class="text-sm text-gray-500">{{ $coursePreview['duration'] }} minutes</span>
                                <span class="text-sm text-gray-500">{{ count($coursePreview['steps']) }} steps</span>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            <x-icon name="clock" class="w-3 h-3" />
                            Pending Review
                        </span>
                    </div>
                </x-slot:header>

                <div class="space-y-6">
                    {{-- Description --}}
                    <div>
                        <p class="text-gray-700">{{ $coursePreview['description'] }}</p>
                    </div>

                    {{-- Objectives --}}
                    @if(!empty($coursePreview['objectives']))
                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 mb-2">Learning Objectives</h3>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach($coursePreview['objectives'] as $objective)
                                    <li class="text-sm text-gray-600">{{ $objective }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Steps --}}
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-3">Course Steps</h3>
                        <div class="space-y-3">
                            @foreach($coursePreview['steps'] as $index => $step)
                                @php
                                    $stepTypeConfig = \App\Models\MiniCourseStep::getStepTypes()[$step['step_type']] ?? ['icon' => 'document', 'label' => 'Content'];
                                @endphp
                                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0 w-6 h-6 rounded-full bg-pulse-orange-100 text-pulse-orange-600 flex items-center justify-center text-xs font-semibold">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-sm font-medium text-gray-900">{{ $step['title'] }}</h4>
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-xs bg-gray-200 text-gray-600">
                                                <x-icon name="{{ $stepTypeConfig['icon'] ?? 'document' }}" class="w-3 h-3" />
                                                {{ $stepTypeConfig['label'] ?? ucfirst($step['step_type']) }}
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-500 mt-0.5 line-clamp-2">{{ $step['description'] }}</p>
                                        @if($step['resource'])
                                            <div class="flex items-center gap-1.5 mt-1.5 text-xs text-blue-600">
                                                <x-icon name="link" class="w-3 h-3" />
                                                Linked: {{ $step['resource']['title'] }}
                                            </div>
                                        @endif
                                    </div>
                                    <span class="text-xs text-gray-400">{{ $step['duration'] }}m</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </x-card>
        </div>
    @else
        {{-- Generation Form --}}
        <x-card>
            <x-slot:header>
                <h2 class="text-lg font-semibold text-gray-900">Create a New Course</h2>
                <p class="text-sm text-gray-500">Describe what you want to teach and our AI will generate a complete course structure using your resource library.</p>
            </x-slot:header>

            <form wire:submit="generate" class="space-y-6">
                {{-- Topic Input --}}
                <div>
                    <label for="topic" class="block text-sm font-medium text-gray-700 mb-1">
                        Course Topic <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="topic"
                        wire:model="topic"
                        rows="3"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 resize-none"
                        placeholder="E.g., Managing test anxiety for high school students, Building healthy study habits, Understanding and regulating emotions..."
                        @disabled($isGenerating)
                    ></textarea>
                    @error('topic')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Describe the learning goal or topic. Be specific about what you want students to learn.</p>
                </div>

                {{-- Two Column Layout --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Course Type --}}
                    <div>
                        <label for="courseType" class="block text-sm font-medium text-gray-700 mb-1">Course Type</label>
                        <select
                            id="courseType"
                            wire:model="courseType"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            @disabled($isGenerating)
                        >
                            @foreach($courseTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Duration --}}
                    <div>
                        <label for="duration" class="block text-sm font-medium text-gray-700 mb-1">Target Duration</label>
                        <select
                            id="duration"
                            wire:model="targetDurationMinutes"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500"
                            @disabled($isGenerating)
                        >
                            @foreach($durationOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Target Grades --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Target Grades</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($gradeOptions as $value => $label)
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 border rounded-lg cursor-pointer transition-colors {{ in_array($value, $targetGrades) ? 'bg-purple-100 border-purple-300 text-purple-700' : 'bg-white border-gray-300 text-gray-600 hover:border-gray-400' }}">
                                <input
                                    type="checkbox"
                                    wire:model="targetGrades"
                                    value="{{ $value }}"
                                    class="sr-only"
                                    @disabled($isGenerating)
                                >
                                <span class="text-sm">{{ $value }}</span>
                            </label>
                        @endforeach
                    </div>
                    <p class="mt-1 text-xs text-gray-400">Leave empty for all grades</p>
                </div>

                {{-- Target Risk Levels --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Target Risk Levels (Optional)</label>
                    <div class="flex flex-wrap gap-2">
                        @foreach($riskLevelOptions as $value => $label)
                            <label class="inline-flex items-center gap-1.5 px-3 py-1.5 border rounded-lg cursor-pointer transition-colors {{ in_array($value, $targetRiskLevels) ? 'bg-purple-100 border-purple-300 text-purple-700' : 'bg-white border-gray-300 text-gray-600 hover:border-gray-400' }}">
                                <input
                                    type="checkbox"
                                    wire:model="targetRiskLevels"
                                    value="{{ $value }}"
                                    class="sr-only"
                                    @disabled($isGenerating)
                                >
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Error Message --}}
                @if($error)
                    <div class="p-4 bg-red-50 border border-red-200 rounded-lg">
                        <div class="flex items-center gap-2 text-red-700">
                            <x-icon name="exclamation-circle" class="w-5 h-5" />
                            <p class="text-sm font-medium">{{ $error }}</p>
                        </div>
                    </div>
                @endif

                {{-- Submit Button --}}
                <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-500">
                        <x-icon name="information-circle" class="w-4 h-4 inline-block" />
                        The AI will search your resource library and create a structured course.
                    </p>
                    <button
                        type="submit"
                        class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                        @disabled($isGenerating)
                    >
                        @if($isGenerating)
                            <x-icon name="arrow-path" class="w-4 h-4 animate-spin" />
                            Generating Course...
                        @else
                            <x-icon name="sparkles" class="w-4 h-4" />
                            Generate Course
                        @endif
                    </button>
                </div>
            </form>
        </x-card>

        {{-- Tips Card --}}
        <x-card class="mt-6">
            <x-slot:header>
                <h3 class="text-sm font-semibold text-gray-900">Tips for Better Results</h3>
            </x-slot:header>
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start gap-2">
                    <x-icon name="check-circle" class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                    <span>Be specific about the learning outcome you want to achieve</span>
                </li>
                <li class="flex items-start gap-2">
                    <x-icon name="check-circle" class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                    <span>Include context about the target audience (age, needs, challenges)</span>
                </li>
                <li class="flex items-start gap-2">
                    <x-icon name="check-circle" class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                    <span>The AI will reference resources from your library when relevant</span>
                </li>
                <li class="flex items-start gap-2">
                    <x-icon name="check-circle" class="w-4 h-4 text-green-500 mt-0.5 flex-shrink-0" />
                    <span>You can edit and refine the generated course before publishing</span>
                </li>
            </ul>
        </x-card>
    @endif
</div>
