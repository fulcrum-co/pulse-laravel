<div class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-lg">
        {{-- Welcome Screen --}}
        @if($showWelcome && !$showThankYou)
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="w-16 h-16 bg-pulse-orange-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-icon name="clipboard-document-list" class="w-8 h-8 text-pulse-orange-600" />
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $survey->title }}</h1>

                @if($survey->description)
                    <p class="text-gray-600 mb-6">{{ $survey->description }}</p>
                @endif

                <div class="flex items-center justify-center gap-4 text-sm text-gray-500 mb-8">
                    <div class="flex items-center gap-1">
                        <x-icon name="question-mark-circle" class="w-4 h-4" />
                        {{ count($survey->questions ?? []) }} @term('questions_label')
                    </div>
                    <div class="flex items-center gap-1">
                        <x-icon name="clock" class="w-4 h-4" />
                        ~{{ $survey->estimated_duration_minutes ?? 5 }} @term('minutes_label')
                    </div>
                </div>

                @if($survey->is_anonymous)
                    <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-6">
                        <div class="flex items-center gap-2 text-green-800 text-sm">
                            <x-icon name="shield-check" class="w-5 h-5" />
                            <span>@term('anonymous_responses_label')</span>
                        </div>
                    </div>
                @endif

                <button
                    wire:click="startSurvey"
                    class="w-full py-3 px-6 bg-pulse-orange-500 text-white font-medium rounded-lg hover:bg-pulse-orange-600 transition-colors"
                >
                    @term('start_survey_label')
                </button>
            </div>

        {{-- Thank You Screen --}}
        @elseif($showThankYou)
            <div class="bg-white rounded-2xl shadow-xl p-8 text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <x-icon name="check-circle" class="w-10 h-10 text-green-600" />
                </div>

                <h1 class="text-2xl font-bold text-gray-900 mb-2">@term('thank_you_label')</h1>
                <p class="text-gray-600 mb-6">@term('responses_submitted_label')</p>

                @if($attempt->risk_level === 'high')
                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6">
                        <p class="text-amber-800 text-sm">
                            Based on your responses, a staff member may reach out to check in with you.
                            If you need immediate support, please contact your organization support_person.
                        </p>
                    </div>
                @endif

                <p class="text-sm text-gray-500">@term('close_window_label')</p>
            </div>

        {{-- Question Display --}}
        @else
            @php $currentQuestion = $this->getCurrentQuestion(); @endphp

            @if($currentQuestion)
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                    {{-- Progress Bar --}}
                    <div class="h-2 bg-gray-100">
                        <div
                            class="h-full bg-pulse-orange-500 transition-all duration-300"
                            style="width: {{ $this->progress }}%"
                        ></div>
                    </div>

                    <div class="p-8">
                        {{-- Question Counter --}}
                        <div class="text-sm text-gray-500 mb-4">
                            @term('question_singular') {{ $currentQuestionIndex + 1 }} @term('question_of_label') {{ count($survey->questions) }}
                        </div>

                        {{-- Question Text --}}
                        <h2 class="text-xl font-semibold text-gray-900 mb-8">
                            {{ $currentQuestion['question'] }}
                            @if($currentQuestion['required'] ?? true)
                                <span class="text-red-500">*</span>
                            @endif
                        </h2>

                        {{-- Answer Options --}}
                        @if(($currentQuestion['type'] ?? 'scale') === 'scale')
                            <div class="mb-8">
                                <div class="flex justify-between gap-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <button
                                            wire:click="submitAnswer({{ $i }})"
                                            class="flex-1 group"
                                        >
                                            <div class="w-full aspect-square rounded-full border-2 flex items-center justify-center text-lg font-medium transition-all duration-200 group-hover:border-pulse-orange-500 group-hover:bg-pulse-orange-50 group-hover:text-pulse-orange-600 {{ ($responses[$currentQuestion['id']] ?? null) == $i ? 'border-pulse-orange-500 bg-pulse-orange-500 text-white' : 'border-gray-300 text-gray-600' }}">
                                                {{ $i }}
                                            </div>
                                        </button>
                                    @endfor
                                </div>
                                <div class="flex justify-between mt-3 text-sm text-gray-500">
                                    <span>{{ $currentQuestion['options']['1'] ?? app(\App\Services\TerminologyService::class)->get('scale_low_label') }}</span>
                                    <span>{{ $currentQuestion['options']['5'] ?? app(\App\Services\TerminologyService::class)->get('scale_high_label') }}</span>
                                </div>
                            </div>

                        @elseif(($currentQuestion['type'] ?? 'scale') === 'multiple_choice')
                            <div class="space-y-3 mb-8">
                                @foreach($currentQuestion['options'] ?? [] as $index => $option)
                                    <button
                                        wire:click="submitAnswer('{{ $option }}')"
                                        class="w-full text-left p-4 rounded-lg border-2 transition-all duration-200 hover:border-pulse-orange-500 hover:bg-pulse-orange-50 {{ ($responses[$currentQuestion['id']] ?? null) === $option ? 'border-pulse-orange-500 bg-pulse-orange-50' : 'border-gray-200' }}"
                                    >
                                        <span class="text-gray-900">{{ $option }}</span>
                                    </button>
                                @endforeach
                            </div>

                        @elseif(($currentQuestion['type'] ?? 'scale') === 'text')
                            <div class="mb-8" x-data="{ answer: '' }">
                                <textarea
                                    x-model="answer"
                                    rows="4"
                                    class="w-full rounded-lg border-gray-300 focus:border-pulse-orange-500 focus:ring-pulse-orange-500"
                                    placeholder="{{ app(\App\Services\TerminologyService::class)->get('answer_here_label') }}"
                                ></textarea>
                                <button
                                    @click="$wire.submitAnswer(answer)"
                                    x-bind:disabled="!answer.trim()"
                                    class="mt-4 w-full py-3 px-6 bg-pulse-orange-500 text-white font-medium rounded-lg hover:bg-pulse-orange-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                                >
                                    @term('continue_label')
                                </button>
                            </div>

                        @elseif(($currentQuestion['type'] ?? 'scale') === 'voice')
                            <div class="mb-8" x-data="voiceResponse()">
                                <div class="text-center">
                                    <button
                                        @click="toggleRecording"
                                        :class="isRecording ? 'bg-red-500 hover:bg-red-600' : 'bg-pulse-orange-500 hover:bg-pulse-orange-600'"
                                        class="w-20 h-20 rounded-full flex items-center justify-center text-white transition-colors mx-auto mb-4"
                                    >
                                        <x-icon name="microphone" class="w-8 h-8" />
                                    </button>
                                    <p class="text-gray-600" x-text="isRecording ? '{{ app(\App\Services\TerminologyService::class)->get('recording_tap_to_stop_label') }}' : '{{ app(\App\Services\TerminologyService::class)->get('tap_to_record_label') }}'"></p>
                                </div>

                                <template x-if="transcription">
                                    <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                        <p class="text-gray-700" x-text="transcription"></p>
                                        <button
                                            @click="$wire.submitAnswer(transcription)"
                                            class="mt-3 w-full py-2 bg-pulse-orange-500 text-white rounded-lg hover:bg-pulse-orange-600"
                                        >
                                            @term('submit_answer_label')
                                        </button>
                                    </div>
                                </template>
                            </div>
                        @endif

                        {{-- Navigation --}}
                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <button
                                wire:click="previousQuestion"
                                class="text-gray-500 hover:text-gray-700 text-sm {{ $currentQuestionIndex === 0 ? 'invisible' : '' }}"
                            >
                                <x-icon name="arrow-left" class="w-4 h-4 inline mr-1" />
                                @term('previous_label')
                            </button>

                            @if(!($currentQuestion['required'] ?? true))
                                <button
                                    wire:click="skipQuestion"
                                    class="text-gray-400 hover:text-gray-600 text-sm"
                                >
                                    @term('skip_label')
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Survey Title (subtle) --}}
                <p class="text-center text-sm text-gray-400 mt-4">{{ $survey->title }}</p>
            @endif
        @endif
    </div>
</div>

@push('scripts')
<script>
function voiceResponse() {
    return {
        isRecording: false,
        transcription: null,
        mediaRecorder: null,
        audioChunks: [],

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
                    // For demo purposes, using simulated transcription
                    this.transcription = "This is a simulated voice response transcription.";
                };

                this.mediaRecorder.start();
                this.isRecording = true;
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
            }
        }
    }
}
</script>
@endpush
