<div class="max-w-6xl mx-auto">
    {{-- Mode Selection --}}
    @if($mode === 'select')
        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900">@term('create_action') @term('survey_singular')</h1>
            <p class="text-gray-500 mt-1">@term('choose_how_label')</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 max-w-4xl mx-auto">
            <!-- AI Chat Builder -->
            <button
                wire:click="selectMode('chat')"
                @if(!($serviceStatus['claude'] ?? false)) disabled @endif
                class="group relative bg-white rounded-xl border-2 border-gray-200 p-6 text-left hover:border-indigo-400 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 {{ !($serviceStatus['claude'] ?? false) ? 'opacity-60 cursor-not-allowed' : '' }}"
            >
                <div class="flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-xl bg-indigo-100 flex items-center justify-center group-hover:bg-indigo-200 transition-colors mb-4">
                        <x-icon name="chat-bubble-left-right" class="w-7 h-7 text-indigo-600" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-indigo-600 transition-colors">@term('ai_chat_builder_label')</h3>
                    <p class="text-sm text-gray-500 mt-2">@term('ai_chat_builder_body')</p>
                    <ul class="mt-4 space-y-1 text-xs text-gray-500">
                        <li class="flex items-center gap-1.5">
                            <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                            @term('natural_conversation_label')
                        </li>
                        <li class="flex items-center gap-1.5">
                            <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                            @term('ai_suggestions_label')
                        </li>
                        <li class="flex items-center gap-1.5">
                            <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                            @term('quick_refinement_label')
                        </li>
                    </ul>
                </div>
                @if($serviceStatus['claude'] ?? false)
                    <div class="absolute top-3 right-3 text-xs font-medium text-indigo-600 bg-indigo-50 px-2 py-1 rounded-full">
                        @term('ai_powered_label')
                    </div>
                @else
                    <div class="absolute top-3 right-3 text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">
                        @term('not_configured_label')
                    </div>
                @endif
            </button>

            <!-- Voice Builder -->
            <button
                wire:click="selectMode('voice')"
                @if(!($serviceStatus['transcription'] ?? false)) disabled @endif
                class="group relative bg-white rounded-xl border-2 border-gray-200 p-6 text-left hover:border-purple-400 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 {{ !($serviceStatus['transcription'] ?? false) ? 'opacity-60 cursor-not-allowed' : '' }}"
            >
                <div class="flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-xl bg-purple-100 flex items-center justify-center group-hover:bg-purple-200 transition-colors mb-4">
                        <x-icon name="microphone" class="w-7 h-7 text-purple-600" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-purple-600 transition-colors">@term('voice_builder_label')</h3>
                    <p class="text-sm text-gray-500 mt-2">@term('voice_builder_body')</p>
                    <ul class="mt-4 space-y-1 text-xs text-gray-500">
                        <li class="flex items-center gap-1.5">
                            <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                            @term('hands_free_label')
                        </li>
                        <li class="flex items-center gap-1.5">
                            <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                            @term('audio_transcription_label')
                        </li>
                        <li class="flex items-center gap-1.5">
                            <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                            @term('easy_editing_label')
                        </li>
                    </ul>
                </div>
                @if(!($serviceStatus['transcription'] ?? false))
                    <div class="absolute top-3 right-3 text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full">
                        @term('not_configured_label')
                    </div>
                @endif
            </button>

            <!-- Form Builder -->
            <button
                wire:click="selectMode('form')"
                class="group relative bg-white rounded-xl border-2 border-gray-200 p-6 text-left hover:border-pulse-orange-400 hover:shadow-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-pulse-orange-500 focus:ring-offset-2"
            >
                <div class="flex flex-col items-center text-center">
                    <div class="w-14 h-14 rounded-xl bg-pulse-orange-100 flex items-center justify-center group-hover:bg-pulse-orange-200 transition-colors mb-4">
                        <x-icon name="clipboard-document-list" class="w-7 h-7 text-pulse-orange-600" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 group-hover:text-pulse-orange-600 transition-colors">@term('form_builder_label')</h3>
                    <p class="text-sm text-gray-500 mt-2">@term('form_builder_body')</p>
                    <ul class="mt-4 space-y-1 text-xs text-gray-500">
                        <li class="flex items-center gap-1.5">
                            <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                            @term('full_control_label')
                        </li>
                        <li class="flex items-center gap-1.5">
                            <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                            @term('question_bank_label')
                        </li>
                        <li class="flex items-center gap-1.5">
                            <x-icon name="check" class="w-3.5 h-3.5 text-green-500" />
                            @term('advanced_options_label')
                        </li>
                    </ul>
                </div>
                <div class="absolute top-3 right-3 text-xs font-medium text-pulse-orange-600 bg-pulse-orange-50 px-2 py-1 rounded-full">
                    @term('classic_label')
                </div>
            </button>
        </div>

        <!-- Templates Section -->
        <div class="mt-12 max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-900">@term('start_from_template_label')</h2>
                <button wire:click="$set('showTemplates', true)" class="text-sm text-pulse-orange-600 hover:text-pulse-orange-700">
                    @term('view_all_templates_label')
                </button>
            </div>

            <div class="grid md:grid-cols-3 gap-4">
                @foreach($this->templates->take(3) as $template)
                    <button
                        wire:click="selectTemplate({{ $template->id }})"
                        class="bg-white rounded-lg border border-gray-200 p-4 text-left hover:border-pulse-orange-300 hover:shadow transition-all"
                    >
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-medium text-gray-900">{{ $template->name }}</h3>
                                <p class="text-sm text-gray-500 mt-1">{{ Str::limit($template->description, 60) }}</p>
                            </div>
                            @if($template->is_featured)
                                <x-badge color="yellow">@term('featured_label')</x-badge>
                            @endif
                        </div>
                        <div class="flex items-center gap-3 mt-3 text-xs text-gray-400">
                            <span>{{ count($template->questions ?? []) }} @term('questions_label')</span>
                            <span>{{ $template->usage_count }} @term('uses_label')</span>
                        </div>
                    </button>
                @endforeach
            </div>
        </div>

        <div class="text-center mt-8">
            <a href="{{ route('surveys.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                @term('cancel_action')
            </a>
        </div>

    {{-- Chat Mode --}}
    @elseif($mode === 'chat')
        <div class="grid lg:grid-cols-2 gap-6">
            <!-- Chat Panel -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-[600px]">
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <x-icon name="sparkles" class="w-5 h-5 text-indigo-600" />
                            </div>
                            <div>
                                <h2 class="font-semibold text-gray-900">@term('ai_assistant_label')</h2>
                                <p class="text-xs text-gray-500">@term('describe_needs_label')</p>
                            </div>
                        </div>
                        @if(count($questions) > 0)
                            <button
                                wire:click="finishChatAndEdit"
                                class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700"
                            >
                                @term('finish_edit_label')
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Messages -->
                <div class="flex-1 overflow-y-auto p-4 space-y-4">
                    @foreach($chatMessages as $message)
                        <div class="flex {{ $message['role'] === 'user' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[80%] rounded-lg px-4 py-2 {{ $message['role'] === 'user' ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-900' }}">
                                <p class="text-sm whitespace-pre-line">{{ $message['content'] }}</p>
                            </div>
                        </div>
                    @endforeach

                    @if($isProcessing)
                        <div class="flex justify-start">
                            <div class="bg-gray-100 rounded-lg px-4 py-2">
                                <div class="flex items-center gap-2">
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                                    <div class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Input -->
                <div class="p-4 border-t border-gray-200">
                    <form wire:submit.prevent="sendChatMessage" class="flex gap-2">
                        <input
                            type="text"
                            wire:model="chatInput"
                            placeholder="{{ app(\App\Services\TerminologyService::class)->get('describe_needs_label') }}..."
                            class="flex-1 rounded-lg border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
                            @disabled($isProcessing)
                        />
                        <button
                            type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                            @disabled($isProcessing || empty(trim($chatInput)))
                        >
                            <x-icon name="paper-airplane" class="w-5 h-5" />
                        </button>
                    </form>
                </div>
            </div>

            <!-- Preview Panel -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">@term('survey_preview_label')</h3>

                @if(empty($questions))
                    <div class="text-center py-12">
                        <x-icon name="document-text" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                        <p class="text-gray-500">@term('no_questions_yet_label')</p>
                        <p class="text-sm text-gray-400">@term('start_chatting_label')</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($questions as $index => $question)
                            <div class="p-4 bg-gray-50 rounded-lg">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="text-xs text-gray-400 mb-1">Question {{ $index + 1 }}</div>
                                        <p class="text-gray-900">{{ $question['question'] }}</p>
                                        <div class="mt-2">
                                            <x-badge color="gray">{{ ucfirst(str_replace('_', ' ', $question['type'])) }}</x-badge>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="text-center mt-6">
            <button wire:click="$set('mode', 'select')" class="text-sm text-gray-500 hover:text-gray-700">
                <x-icon name="arrow-left" class="w-4 h-4 inline mr-1" />
                @term('back_to_mode_selection_label')
            </button>
        </div>

    {{-- Voice Mode --}}
    @elseif($mode === 'voice')
        <div class="max-w-2xl mx-auto">
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-gray-900">@term('voice_survey_builder_label')</h1>
                <p class="text-gray-500 mt-1">@term('voice_survey_builder_body')</p>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                @if(!$transcription)
                    <div class="text-center">
                        <div class="w-24 h-24 mx-auto mb-6 rounded-full bg-purple-100 flex items-center justify-center {{ $isRecording ? 'animate-pulse' : '' }}">
                            <x-icon name="microphone" class="w-12 h-12 text-purple-600" />
                        </div>

                        <p class="text-gray-600 mb-6">
                            @term('start_speaking_label')<br>
                            @term('example_prompt_label')
                        </p>

                        <div
                            x-data="voiceRecorder()"
                            x-init="init()"
                        >
                            <button
                                @click="toggleRecording"
                                :disabled="isTranscribing"
                                :class="isRecording ? 'bg-red-600 hover:bg-red-700' : (isTranscribing ? 'bg-gray-400 cursor-wait' : 'bg-purple-600 hover:bg-purple-700')"
                                class="px-6 py-3 text-white rounded-lg font-medium transition-colors disabled:opacity-75"
                            >
                                <span x-show="!isRecording && !isTranscribing">@term('start_recording_label')</span>
                                <span x-show="isRecording">@term('stop_recording_label')</span>
                                <span x-show="isTranscribing" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    @term('transcribing_label')
                                </span>
                            </button>

                            <p x-show="isRecording" class="mt-4 text-sm text-gray-500 animate-pulse">
                                @term('recording_instruction_label')
                            </p>
                            <p x-show="isTranscribing" class="mt-4 text-sm text-purple-600 animate-pulse">
                                @term('processing_instruction_label')
                            </p>
                        </div>
                    </div>
                @else
                    <div>
                        <h3 class="font-medium text-gray-900 mb-2">@term('transcription_label')</h3>
                        <div class="p-4 bg-gray-50 rounded-lg mb-6">
                            <p class="text-gray-700">{{ $transcription }}</p>
                        </div>

                        <h3 class="font-medium text-gray-900 mb-2">@term('extracted_questions_label')</h3>
                        @if(count($questions) > 0)
                            <div class="space-y-2 mb-6">
                                @foreach($questions as $index => $question)
                                    <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                        <span class="text-gray-400 text-sm">{{ $index + 1 }}.</span>
                                        <span class="flex-1">{{ $question['question'] }}</span>
                                        <button wire:click="removeQuestion({{ $index }})" class="text-gray-400 hover:text-red-500">
                                            <x-icon name="x-mark" class="w-4 h-4" />
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-gray-500 mb-6">@term('no_questions_detected_label')</p>
                        @endif

                        <div class="flex gap-3">
                            <button
                                wire:click="$set('transcription', null)"
                                class="px-4 py-2 text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50"
                            >
                                @term('record_again_label')
                            </button>
                            <button
                                wire:click="$set('mode', 'form')"
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700"
                            >
                                @term('continue_to_editor_label')
                            </button>
                        </div>
                    </div>
                @endif
            </div>

            <div class="text-center mt-6">
                <button wire:click="$set('mode', 'select')" class="text-sm text-gray-500 hover:text-gray-700">
                    <x-icon name="arrow-left" class="w-4 h-4 inline mr-1" />
                    @term('back_to_mode_selection_label')
                </button>
            </div>
        </div>

    {{-- Form Builder Mode --}}
    @elseif($mode === 'form')
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('surveys.index') }}" class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 mb-4">
                <x-icon name="arrow-left" class="w-4 h-4 mr-1" />
                @term('back_to_label') @term('survey_plural')
            </a>
            <h1 class="text-2xl font-bold text-gray-900">{{ $surveyId ? app(\App\Services\TerminologyService::class)->get('edit_action') : app(\App\Services\TerminologyService::class)->get('create_action') }} {{ app(\App\Services\TerminologyService::class)->get('survey_singular') }}</h1>
        </div>

        <div class="grid lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info Card -->
                <x-card>
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">@term('basic_information_label')</h2>

                    <div class="space-y-4">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">@term('survey_title_label') *</label>
                            <input
                                type="text"
                                id="title"
                                wire:model="title"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                                placeholder="{{ app(\App\Services\TerminologyService::class)->get('survey_title_example_label') }}"
                            />
                            @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">@term('description_label')</label>
                            <textarea
                                id="description"
                                wire:model="description"
                                rows="2"
                                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                                placeholder="{{ app(\App\Services\TerminologyService::class)->get('description_example_label') }}"
                            ></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">@term('survey_type_label')</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach($this->surveyTypes as $type => $info)
                                    <button
                                        wire:click="$set('surveyType', '{{ $type }}')"
                                        class="px-3 py-1.5 rounded-full border-2 text-sm font-medium transition-all {{ $surveyType === $type ? 'border-pulse-orange-500 bg-pulse-orange-50 text-pulse-orange-700' : 'border-gray-200 text-gray-600 hover:border-gray-300' }}"
                                    >
                                        {{ $info['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </x-card>

                <!-- Questions Card -->
                <x-card>
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">@term('questions_label')</h2>
                        <div class="flex gap-2">
                            <button
                                wire:click="$set('showQuestionBank', true)"
                                class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
                            >
                                <x-icon name="archive-box" class="w-4 h-4 mr-1" />
                                @term('question_bank_button_label')
                            </button>
                            <button
                                wire:click="openQuestionEditor"
                                class="inline-flex items-center text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
                            >
                                <x-icon name="plus" class="w-4 h-4 mr-1" />
                                @term('add_question_label')
                            </button>
                        </div>
                    </div>

                    @if(empty($questions))
                        <div class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
                            <x-icon name="question-mark-circle" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                            <p class="text-gray-500">@term('no_questions_added_label')</p>
                            <button
                                wire:click="openQuestionEditor"
                                class="mt-3 text-sm text-pulse-orange-600 hover:text-pulse-orange-700"
                            >
                                @term('add_first_question_label')
                            </button>
                        </div>
                    @else
                        <div
                            class="space-y-3"
                            x-data="questionSortable()"
                            x-init="initSortable()"
                        >
                            @foreach($questions as $index => $question)
                                <div
                                    wire:key="question-{{ $question['id'] ?? $index }}"
                                    data-index="{{ $index }}"
                                    class="sortable-item flex items-start gap-3 p-4 bg-gray-50 rounded-lg group hover:bg-gray-100 transition-colors cursor-move"
                                >
                                    <div class="flex-shrink-0 w-8 h-8 bg-white rounded-lg flex items-center justify-center text-sm font-medium text-gray-500 border border-gray-200 drag-handle">
                                        <x-icon name="bars-3" class="w-4 h-4 text-gray-400" />
                                    </div>
                                    <div class="flex-shrink-0 w-6 h-6 flex items-center justify-center text-sm font-medium text-gray-400">
                                        {{ $index + 1 }}.
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-gray-900">{{ $question['question'] }}</p>
                                        {{-- Show options for multiple choice --}}
                                        @if(($question['type'] ?? 'scale') === 'multiple_choice' && !empty($question['options']))
                                            <div class="flex flex-wrap gap-1 mt-2">
                                                @foreach(array_slice($question['options'], 0, 4) as $opt)
                                                    <span class="text-xs px-2 py-0.5 bg-gray-200 text-gray-600 rounded">{{ Str::limit($opt, 15) }}</span>
                                                @endforeach
                                                @if(count($question['options']) > 4)
                                                    <span class="text-xs px-2 py-0.5 bg-gray-200 text-gray-500 rounded">+{{ count($question['options']) - 4 }} more</span>
                                                @endif
                                            </div>
                                        @endif
                                        <div class="flex items-center gap-2 mt-2">
                                            <x-badge color="gray">{{ $this->questionTypes[$question['type']]['label'] ?? $question['type'] }}</x-badge>
                                            @if($question['required'] ?? true)
                                                <x-badge color="red">@term('required_label')</x-badge>
                                            @endif
                                            @if(($question['type'] ?? 'scale') === 'multiple_choice' && !empty($question['options']))
                                                <span class="text-xs text-gray-400">{{ count($question['options']) }} @term('options_label')</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button wire:click="openQuestionEditor({{ $index }})" class="p-1.5 text-gray-400 hover:text-gray-600 rounded">
                                            <x-icon name="pencil" class="w-4 h-4" />
                                        </button>
                                        <button wire:click="removeQuestion({{ $index }})" class="p-1.5 text-gray-400 hover:text-red-500 rounded">
                                            <x-icon name="trash" class="w-4 h-4" />
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    @error('questions') <p class="mt-2 text-sm text-red-600">{{ $message }}</p> @enderror
                </x-card>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Settings Card -->
                <x-card>
                <h3 class="font-semibold text-gray-900 mb-4">@term('settings_label')</h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">@term('anonymous_label') @term('responses_label')</label>
                                <p class="text-xs text-gray-500">@term('hide_identity_label')</p>
                            </div>
                            <button
                                wire:click="$toggle('isAnonymous')"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 {{ $isAnonymous ? 'bg-pulse-orange-500' : 'bg-gray-200' }}"
                            >
                                <span class="translate-x-0 pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 {{ $isAnonymous ? 'translate-x-5' : '' }}"></span>
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <label class="text-sm font-medium text-gray-700">@term('ai_follow_up_label')</label>
                                <p class="text-xs text-gray-500">@term('dynamic_questions_label')</p>
                            </div>
                            <button
                                wire:click="$toggle('aiFollowUpEnabled')"
                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 {{ $aiFollowUpEnabled ? 'bg-pulse-orange-500' : 'bg-gray-200' }}"
                            >
                                <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 {{ $aiFollowUpEnabled ? 'translate-x-5' : 'translate-x-0' }}"></span>
                            </button>
                        </div>

                        <div>
                            <label class="text-sm font-medium text-gray-700">@term('estimated_duration_label')</label>
                            <div class="flex items-center gap-2 mt-1">
                                <input
                                    type="number"
                                    wire:model="estimatedDuration"
                                    min="1"
                                    max="60"
                                    class="w-20 rounded-lg border-gray-300 text-sm"
                                />
                                <span class="text-sm text-gray-500">@term('minutes_label')</span>
                            </div>
                        </div>
                    </div>
                </x-card>

                <!-- Delivery Channels Card -->
                <x-card>
                <h3 class="font-semibold text-gray-900 mb-4">@term('delivery_channels_label')</h3>

                    <div class="space-y-3">
                        @foreach([
                            'web' => ['label' => app(\App\Services\TerminologyService::class)->get('web_link_label'), 'icon' => 'globe-alt'],
                            'sms' => ['label' => app(\App\Services\TerminologyService::class)->get('sms_label'), 'icon' => 'chat-bubble-left'],
                            'voice_call' => ['label' => app(\App\Services\TerminologyService::class)->get('voice_call_label'), 'icon' => 'phone'],
                            'whatsapp' => ['label' => app(\App\Services\TerminologyService::class)->get('whatsapp_label'), 'icon' => 'chat-bubble-oval-left'],
                        ] as $channel => $info)
                            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 transition-colors {{ in_array($channel, $deliveryChannels) ? 'border-pulse-orange-300 bg-pulse-orange-50' : '' }}">
                                <input
                                    type="checkbox"
                                    wire:model="deliveryChannels"
                                    value="{{ $channel }}"
                                    class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                                />
                                <x-icon name="{{ $info['icon'] }}" class="w-5 h-5 text-gray-400" />
                                <span class="text-sm text-gray-700">{{ $info['label'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </x-card>

                <!-- Actions Card -->
                <x-card class="bg-gray-50">
                    <div class="space-y-3">
                        <button
                            wire:click="$set('showPreview', true)"
                            class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center justify-center gap-2"
                        >
                            <x-icon name="eye" class="w-4 h-4" />
                            @term('preview_survey_label')
                        </button>

                        <button
                            wire:click="save(false)"
                            class="w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
                        >
                            @term('save_as_draft_label')
                        </button>

                        <button
                            wire:click="save(true)"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600 flex items-center justify-center gap-2"
                        >
                            <x-icon name="rocket-launch" class="w-4 h-4" />
                            @term('save_activate_label')
                        </button>
                    </div>
                </x-card>
            </div>
        </div>
    @endif

    {{-- Question Editor Modal --}}
    @if($showQuestionEditor)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="closeQuestionEditor"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">
                    {{ $editingQuestionIndex !== null ? app(\App\Services\TerminologyService::class)->get('edit_question_title_label') : app(\App\Services\TerminologyService::class)->get('add_question_title_label') }}
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">@term('question_type_label')</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($this->questionTypes as $type => $info)
                                <button
                                    wire:click="$set('questionForm.type', '{{ $type }}')"
                                    class="flex items-center gap-2 p-3 rounded-lg border-2 text-left transition-all {{ ($questionForm['type'] ?? '') === $type ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200 hover:border-gray-300' }}"
                                >
                                    <x-icon name="{{ $info['icon'] }}" class="w-5 h-5 {{ ($questionForm['type'] ?? '') === $type ? 'text-pulse-orange-500' : 'text-gray-400' }}" />
                                    <div>
                                        <span class="text-sm font-medium block">{{ $info['label'] }}</span>
                                        <span class="text-xs text-gray-500">{{ $info['description'] }}</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">@term('question_text_label') *</label>
                        <textarea
                            wire:model="questionForm.question"
                            rows="2"
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                            placeholder="{{ app(\App\Services\TerminologyService::class)->get('enter_question_label') }}"
                        ></textarea>
                        @error('questionForm.question') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    @if(in_array($questionForm['type'] ?? 'scale', ['scale', 'multiple_choice']))
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">@term('options_label')</label>
                            @if(($questionForm['type'] ?? 'scale') === 'scale')
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="text-xs text-gray-500">@term('low_label_suffix')</label>
                                        <input
                                            type="text"
                                            wire:model="questionForm.options.1"
                                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm"
                                            placeholder="{{ app(\App\Services\TerminologyService::class)->get('low_label_example') }}"
                                        />
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">@term('high_label_suffix')</label>
                                        <input
                                            type="text"
                                            wire:model="questionForm.options.5"
                                            class="mt-1 block w-full rounded-lg border-gray-300 text-sm"
                                            placeholder="{{ app(\App\Services\TerminologyService::class)->get('high_label_example') }}"
                                        />
                                    </div>
                                </div>
                            @else
                                {{-- Multiple Choice Options Editor --}}
                                <div class="space-y-2">
                                    @php
                                        $options = $questionForm['options'] ?? [];
                                        // Ensure options is an indexed array for multiple choice
                                        if (!is_array($options) || (is_array($options) && isset($options['1']))) {
                                            $options = ['Option 1', 'Option 2', 'Option 3'];
                                        }
                                    @endphp
                                    @foreach($options as $index => $option)
                                        <div class="flex items-center gap-2" wire:key="option-{{ $index }}">
                                            <div class="flex items-center justify-center w-6 h-6 rounded-full border border-gray-300 text-xs text-gray-400">
                                                {{ $index + 1 }}
                                            </div>
                                            <input
                                                type="text"
                                                wire:model.live="questionForm.options.{{ $index }}"
                                                class="flex-1 rounded-lg border-gray-300 text-sm focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                                                placeholder="{{ app(\App\Services\TerminologyService::class)->get('enter_option_label') }}"
                                            />
                                            @if(count($options) > 2)
                                                <button
                                                    wire:click="removeOption({{ $index }})"
                                                    type="button"
                                                    class="p-1.5 text-gray-400 hover:text-red-500 rounded transition-colors"
                                                    title="{{ app(\App\Services\TerminologyService::class)->get('remove_option_label') }}"
                                                >
                                                    <x-icon name="x-mark" class="w-4 h-4" />
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach

                                    <button
                                        wire:click="addOption"
                                        type="button"
                                        class="flex items-center gap-1.5 text-sm text-pulse-orange-600 hover:text-pulse-orange-700 mt-2 transition-colors"
                                    >
                                        <x-icon name="plus-circle" class="w-4 h-4" />
                                        @term('add_option_label')
                                    </button>

                                    @error('questionForm.options')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="flex items-center gap-2">
                        <input
                            type="checkbox"
                            id="required"
                            wire:model="questionForm.required"
                            class="rounded border-gray-300 text-pulse-orange-500 focus:ring-pulse-orange-500"
                        />
                        <label for="required" class="text-sm text-gray-700">@term('required_question_label')</label>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button
                        wire:click="closeQuestionEditor"
                        class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900"
                    >
                        @term('cancel_action')
                    </button>
                    <button
                        wire:click="saveQuestion"
                        class="px-4 py-2 text-sm font-medium text-white bg-pulse-orange-500 rounded-lg hover:bg-pulse-orange-600"
                    >
                        {{ $editingQuestionIndex !== null ? app(\App\Services\TerminologyService::class)->get('update_action') : app(\App\Services\TerminologyService::class)->get('add_action') }} @term('question_singular')
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Question Bank Modal --}}
    @if($showQuestionBank)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showQuestionBank', false)"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-2xl w-full p-6 max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">@term('question_bank_modal_label')</h3>
                    <button wire:click="$set('showQuestionBank', false)" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>

                @foreach($this->questionBank as $category => $questions)
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-3">{{ ucfirst($category) }}</h4>
                        <div class="space-y-2">
                            @foreach($questions as $question)
                                <button
                                    wire:click="addQuestionFromBank(@js($question->toArray()))"
                                    class="w-full text-left p-3 rounded-lg border border-gray-200 hover:border-pulse-orange-300 hover:bg-pulse-orange-50 transition-colors"
                                >
                                    <p class="text-gray-900">{{ $question->question_text }}</p>
                                    <div class="flex items-center gap-2 mt-2">
                                        <x-badge color="gray">{{ ucfirst(str_replace('_', ' ', $question->question_type)) }}</x-badge>
                                        <span class="text-xs text-gray-400">@term('used_times_label') {{ $question->usage_count }} @term('used_times_suffix_label')</span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                @if($this->questionBank->isEmpty())
                    <div class="text-center py-8">
                        <x-icon name="archive-box" class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                        <p class="text-gray-500">@term('no_questions_bank_label')</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Templates Modal --}}
    @if($showTemplates)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showTemplates', false)"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-3xl w-full p-6 max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">@term('survey_templates_label')</h3>
                    <button wire:click="$set('showTemplates', false)" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    @foreach($this->templates as $template)
                        <button
                            wire:click="selectTemplate({{ $template->id }})"
                            class="text-left p-4 rounded-lg border border-gray-200 hover:border-pulse-orange-300 hover:shadow transition-all"
                        >
                            <div class="flex items-start justify-between">
                                <h4 class="font-medium text-gray-900">{{ $template->name }}</h4>
                                @if($template->is_featured)
                                    <x-badge color="yellow">@term('featured_label')</x-badge>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mt-1">{{ $template->description }}</p>
                            <div class="flex items-center gap-3 mt-3 text-xs text-gray-400">
                                <span>{{ count($template->questions ?? []) }} @term('questions_label')</span>
                                <span>{{ ucfirst($template->template_type) }}</span>
                                <span>{{ $template->usage_count }} @term('uses_label')</span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Preview Modal --}}
    @if($showPreview)
    <div class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75" wire:click="$set('showPreview', false)"></div>

            <div class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 max-h-[80vh] overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900">@term('survey_preview_label')</h3>
                    <button wire:click="$set('showPreview', false)" class="text-gray-400 hover:text-gray-600">
                        <x-icon name="x-mark" class="w-5 h-5" />
                    </button>
                </div>

                <div class="border-b border-gray-200 pb-4 mb-6">
                    <h2 class="text-xl font-bold text-gray-900">{{ $title ?: app(\App\Services\TerminologyService::class)->get('untitled_survey_label') }}</h2>
                    @if($description)
                        <p class="text-gray-600 mt-2">{{ $description }}</p>
                    @endif
                </div>

                <div class="space-y-6">
                    @foreach($questions as $index => $question)
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <p class="font-medium text-gray-900 mb-3">
                                {{ $index + 1 }}. {{ $question['question'] }}
                                @if($question['required'] ?? true)
                                    <span class="text-red-500">*</span>
                                @endif
                            </p>

                            @if(($question['type'] ?? 'scale') === 'scale')
                                <div class="flex justify-between gap-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <div class="flex flex-col items-center">
                                            <div class="w-10 h-10 rounded-full border-2 border-gray-300 flex items-center justify-center text-sm text-gray-600">
                                                {{ $i }}
                                            </div>
                                            @if($i === 1 && isset($question['options']['1']))
                                                <span class="text-xs text-gray-500 mt-1 text-center">{{ $question['options']['1'] }}</span>
                                            @elseif($i === 5 && isset($question['options']['5']))
                                                <span class="text-xs text-gray-500 mt-1 text-center">{{ $question['options']['5'] }}</span>
                                            @endif
                                        </div>
                                    @endfor
                                </div>
                            @elseif(($question['type'] ?? 'scale') === 'multiple_choice')
                                <div class="space-y-2">
                                    @foreach(($question['options'] ?? ['Option 1', 'Option 2']) as $option)
                                        <label class="flex items-center gap-2">
                                            <input type="radio" name="q{{ $index }}" class="text-pulse-orange-500" disabled />
                                            <span class="text-gray-700">{{ $option }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            @elseif(($question['type'] ?? 'scale') === 'text')
                                <textarea class="w-full rounded-lg border-gray-300" rows="2" disabled placeholder="{{ app(\App\Services\TerminologyService::class)->get('your_answer_label') }}"></textarea>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if(count($questions) === 0)
                    <div class="text-center py-8">
                        <p class="text-gray-500">@term('no_questions_preview_label')</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
function questionSortable() {
    return {
        sortable: null,

        initSortable() {
            const el = this.$el;
            if (!el) return;

            this.sortable = new Sortable(el, {
                animation: 150,
                handle: '.drag-handle',
                ghostClass: 'opacity-50',
                dragClass: 'shadow-lg',
                onEnd: (evt) => {
                    const items = el.querySelectorAll('.sortable-item');
                    const newOrder = Array.from(items).map(item => parseInt(item.dataset.index));

                    // Call Livewire method to reorder
                    @this.reorderQuestions(newOrder);
                }
            });
        }
    }
}

function voiceRecorder() {
    return {
        isRecording: false,
        isTranscribing: false,
        mediaRecorder: null,
        audioChunks: [],

        init() {
            // Request microphone permission on init
        },

        async toggleRecording() {
            if (this.isRecording) {
                this.stopRecording();
            } else {
                await this.startRecording();
            }
        },

        async startRecording() {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.mediaRecorder = new MediaRecorder(stream);
                this.audioChunks = [];

                this.mediaRecorder.ondataavailable = (event) => {
                    this.audioChunks.push(event.data);
                };

                this.mediaRecorder.onstop = async () => {
                    const audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
                    await this.transcribeAudio(audioBlob);
                };

                this.mediaRecorder.start();
                this.isRecording = true;
                @this.set('isRecording', true);
            } catch (error) {
                console.error('Error accessing microphone:', error);
                alert('Could not access microphone. Please check permissions.');
            }
        },

        stopRecording() {
            if (this.mediaRecorder && this.isRecording) {
                this.mediaRecorder.stop();
                this.mediaRecorder.stream.getTracks().forEach(track => track.stop());
                this.isRecording = false;
                @this.set('isRecording', false);
            }
        },

        async transcribeAudio(audioBlob) {
            this.isTranscribing = true;

            try {
                const formData = new FormData();
                formData.append('audio', audioBlob, 'recording.webm');

                const response = await fetch('{{ route("api.surveys.transcribe") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    },
                    body: formData,
                });

                const result = await response.json();

                if (result.success && result.transcription) {
                    @this.processVoiceTranscription(result.transcription);
                } else {
                    // Show actual error instead of silent fallback
                    const errorMsg = result.error || 'Transcription failed';
                    console.error('Transcription failed:', result);
                    alert('Transcription Error: ' + errorMsg + '\n\nPlease check that ASSEMBLY_AI_API_KEY is configured in your .env file.');
                }
            } catch (error) {
                console.error('Transcription error:', error);
                alert('Transcription service error: ' + error.message + '\n\nPlease check your API configuration.');
            } finally {
                this.isTranscribing = false;
            }
        }
    }
}
</script>
@endpush
