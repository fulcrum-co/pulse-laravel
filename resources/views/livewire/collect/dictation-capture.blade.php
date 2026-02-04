<div class="min-h-screen bg-gray-50 py-8 px-4">
    <div class="max-w-lg mx-auto">
        {{-- Header with org branding --}}
        <div class="text-center mb-8">
            @if($settings->logo_path)
                <img src="{{ Storage::url($settings->logo_path) }}" alt="{{ $org->org_name }}" class="h-12 mx-auto mb-4">
            @else
                <div class="w-16 h-16 rounded-xl mx-auto mb-4 flex items-center justify-center text-white text-2xl font-bold"
                     style="background-color: {{ $settings->primary_color }}">
                    {{ substr($org->org_name, 0, 1) }}
                </div>
            @endif
            <h1 class="text-xl font-semibold text-gray-900">Share an Update</h1>
            <p class="text-sm text-gray-500 mt-1">
                About {{ $token->contact->name ?? 'this ' . $settings->contact_label_singular }}
            </p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            @if($submitted)
                {{-- Success State --}}
                <div class="p-8 text-center">
                    <div class="w-16 h-16 rounded-full bg-green-100 mx-auto mb-4 flex items-center justify-center">
                        <x-icon name="check" class="w-8 h-8 text-green-600" />
                    </div>
                    <h2 class="text-xl font-semibold text-gray-900 mb-2">Thank you!</h2>
                    <p class="text-gray-600 mb-6">Your update has been received.</p>

                    <div class="bg-gray-50 rounded-xl p-4 text-sm text-gray-600 text-left">
                        <p class="font-medium text-gray-900 mb-3">What happens next?</p>
                        <ul class="space-y-2">
                            <li class="flex items-start gap-2">
                                <x-icon name="clock" class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                <span>Your recording will be transcribed and reviewed by staff</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <x-icon name="document-text" class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                <span>Updates will be added to {{ $token->contact->name ?? 'the ' . $settings->contact_label_singular }}'s record</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <x-icon name="phone" class="w-4 h-4 text-gray-400 mt-0.5 flex-shrink-0" />
                                <span>You may be contacted if we have any questions</span>
                            </li>
                        </ul>
                    </div>
                </div>
            @else
                {{-- Recording Interface --}}
                <div x-data="audioRecorder()" x-init="init()" class="p-6">
                    {{-- Instructions --}}
                    <div class="text-center mb-6">
                        <p class="text-sm text-gray-600">
                            Tap the microphone to start recording your update.
                            Speak clearly about any observations, concerns, or progress.
                        </p>
                    </div>

                    {{-- Idle State --}}
                    <template x-if="state === 'idle'">
                        <div class="text-center py-8">
                            <button
                                @click="startRecording()"
                                class="w-24 h-24 rounded-full mx-auto flex items-center justify-center text-white shadow-lg transition-transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-opacity-50"
                                style="background-color: {{ $settings->primary_color }}; --tw-ring-color: {{ $settings->primary_color }}40;"
                            >
                                <x-icon name="microphone" class="w-10 h-10" />
                            </button>
                            <p class="mt-4 text-sm text-gray-500">Tap to record</p>

                            {{-- Browser support warning --}}
                            <template x-if="!isSupported">
                                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg text-sm text-yellow-800">
                                    <x-icon name="exclamation-triangle" class="w-4 h-4 inline mr-1" />
                                    Your browser doesn't support recording. Please use the file upload below.
                                </div>
                            </template>

                            {{-- Permission error --}}
                            <template x-if="permissionError">
                                <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-800">
                                    <x-icon name="exclamation-circle" class="w-4 h-4 inline mr-1" />
                                    <span x-text="permissionError"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    {{-- Recording State --}}
                    <template x-if="state === 'recording'">
                        <div class="text-center py-6">
                            {{-- Waveform visualization --}}
                            <div class="relative mb-6">
                                <canvas x-ref="waveform" class="w-full h-20 rounded-lg bg-gray-100"></canvas>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="flex items-center gap-2 bg-white/80 backdrop-blur-sm px-3 py-1.5 rounded-full">
                                        <span class="w-2 h-2 bg-red-500 rounded-full animate-pulse"></span>
                                        <span class="text-lg font-mono font-medium text-gray-900" x-text="formatTime(recordingTime)">00:00</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Stop button --}}
                            <button
                                @click="stopRecording()"
                                class="w-20 h-20 rounded-full mx-auto flex items-center justify-center bg-red-500 text-white shadow-lg transition-transform hover:scale-105 active:scale-95 focus:outline-none focus:ring-4 focus:ring-red-500/50"
                            >
                                <x-icon name="stop" class="w-8 h-8" />
                            </button>
                            <p class="mt-4 text-sm text-gray-500">Tap to stop</p>
                        </div>
                    </template>

                    {{-- Preview State --}}
                    <template x-if="state === 'preview'">
                        <div class="py-6">
                            {{-- Audio player --}}
                            <div class="bg-gray-50 rounded-xl p-4 mb-6">
                                <div class="flex items-center gap-4">
                                    <button
                                        @click="togglePlayback()"
                                        class="w-12 h-12 rounded-full flex items-center justify-center text-white flex-shrink-0"
                                        style="background-color: {{ $settings->primary_color }}"
                                    >
                                        <x-icon x-show="!isPlaying" name="play" class="w-5 h-5 ml-0.5" />
                                        <x-icon x-show="isPlaying" name="pause" class="w-5 h-5" x-cloak />
                                    </button>
                                    <div class="flex-1">
                                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full transition-all"
                                                 style="background-color: {{ $settings->primary_color }}"
                                                 :style="{ width: playbackProgress + '%' }">
                                            </div>
                                        </div>
                                        <div class="flex justify-between mt-1 text-xs text-gray-500">
                                            <span x-text="formatTime(currentPlaybackTime)">0:00</span>
                                            <span x-text="formatTime(recordingTime)">0:00</span>
                                        </div>
                                    </div>
                                </div>
                                <audio x-ref="audioPlayer" :src="audioUrl" @ended="isPlaying = false" @timeupdate="updatePlaybackProgress()"></audio>
                            </div>

                            {{-- Actions --}}
                            <div class="flex gap-3">
                                <button
                                    @click="retake()"
                                    class="flex-1 py-3 px-4 rounded-xl border-2 border-gray-200 text-gray-700 font-medium hover:bg-gray-50 transition-colors flex items-center justify-center gap-2"
                                >
                                    <x-icon name="arrow-path" class="w-5 h-5" />
                                    Re-record
                                </button>
                                <button
                                    @click="submitRecording()"
                                    class="flex-1 py-3 px-4 rounded-xl text-white font-medium transition-colors flex items-center justify-center gap-2"
                                    style="background-color: {{ $settings->primary_color }}"
                                >
                                    <x-icon name="paper-airplane" class="w-5 h-5" />
                                    Submit
                                </button>
                            </div>
                        </div>
                    </template>

                    {{-- Uploading State --}}
                    <template x-if="state === 'uploading'">
                        <div class="text-center py-12">
                            <div class="w-16 h-16 rounded-full bg-gray-100 mx-auto mb-4 flex items-center justify-center">
                                <svg class="w-8 h-8 animate-spin" style="color: {{ $settings->primary_color }}" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600">Uploading your recording...</p>
                            <div class="mt-4 max-w-xs mx-auto">
                                <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full transition-all duration-300"
                                         style="background-color: {{ $settings->primary_color }}"
                                         :style="{ width: uploadProgress + '%' }">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    {{-- Divider with file upload fallback --}}
                    <template x-if="state === 'idle' || state === 'preview'">
                        <div class="mt-8 pt-6 border-t border-gray-200">
                            <div class="flex items-center gap-4 mb-4">
                                <div class="flex-1 h-px bg-gray-200"></div>
                                <span class="text-xs text-gray-400 uppercase tracking-wider">or upload a file</span>
                                <div class="flex-1 h-px bg-gray-200"></div>
                            </div>

                            <label class="block">
                                <input
                                    type="file"
                                    wire:model="audioFile"
                                    accept="audio/*"
                                    class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 cursor-pointer"
                                    @change="handleFileSelect($event)"
                                />
                            </label>
                            @error('audioFile')
                                <p class="text-xs text-red-600 mt-2">{{ $message }}</p>
                            @enderror

                            {{-- File upload submit button --}}
                            <div wire:loading.remove wire:target="audioFile">
                                @if($audioFile)
                                    <button
                                        wire:click="submit"
                                        class="w-full mt-4 py-3 px-4 rounded-xl text-white font-medium transition-colors flex items-center justify-center gap-2"
                                        style="background-color: {{ $settings->primary_color }}"
                                    >
                                        <x-icon name="paper-airplane" class="w-5 h-5" />
                                        Submit Upload
                                    </button>
                                @endif
                            </div>

                            {{-- File upload loading state --}}
                            <div wire:loading wire:target="audioFile" class="mt-4 text-center text-sm text-gray-500">
                                <svg class="w-5 h-5 animate-spin inline mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Processing file...
                            </div>
                        </div>
                    </template>
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-gray-400 mt-6">
            Powered by {{ $org->org_name }}
        </p>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('audioRecorder', () => ({
        state: 'idle', // idle, recording, preview, uploading
        isSupported: true,
        permissionError: null,
        mediaRecorder: null,
        audioStream: null,
        audioChunks: [],
        audioBlob: null,
        audioUrl: null,
        recordingTime: 0,
        timer: null,
        analyser: null,
        animationFrame: null,
        isPlaying: false,
        playbackProgress: 0,
        currentPlaybackTime: 0,
        uploadProgress: 0,

        init() {
            // Check browser support
            this.isSupported = !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia && window.MediaRecorder);
        },

        async startRecording() {
            if (!this.isSupported) return;

            this.permissionError = null;

            try {
                this.audioStream = await navigator.mediaDevices.getUserMedia({
                    audio: {
                        echoCancellation: true,
                        noiseSuppression: true,
                        sampleRate: 44100
                    }
                });

                // Determine best supported format
                const mimeType = MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
                    ? 'audio/webm;codecs=opus'
                    : MediaRecorder.isTypeSupported('audio/webm')
                        ? 'audio/webm'
                        : 'audio/wav';

                this.mediaRecorder = new MediaRecorder(this.audioStream, { mimeType });
                this.audioChunks = [];

                this.mediaRecorder.ondataavailable = (e) => {
                    if (e.data.size > 0) {
                        this.audioChunks.push(e.data);
                    }
                };

                this.mediaRecorder.onstop = () => this.handleRecordingStop();

                // Start recording
                this.mediaRecorder.start(100); // Collect data every 100ms
                this.state = 'recording';
                this.recordingTime = 0;
                this.startTimer();
                this.startWaveform();

            } catch (err) {
                console.error('Recording error:', err);
                if (err.name === 'NotAllowedError') {
                    this.permissionError = 'Microphone access denied. Please allow microphone access and try again.';
                } else if (err.name === 'NotFoundError') {
                    this.permissionError = 'No microphone found. Please connect a microphone and try again.';
                } else {
                    this.permissionError = 'Could not start recording. Please try uploading a file instead.';
                }
            }
        },

        stopRecording() {
            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                this.mediaRecorder.stop();
            }
            this.stopTimer();
            this.stopWaveform();

            // Stop all audio tracks
            if (this.audioStream) {
                this.audioStream.getTracks().forEach(track => track.stop());
            }
        },

        handleRecordingStop() {
            const mimeType = this.mediaRecorder.mimeType || 'audio/webm';
            this.audioBlob = new Blob(this.audioChunks, { type: mimeType });
            this.audioUrl = URL.createObjectURL(this.audioBlob);
            this.state = 'preview';
        },

        retake() {
            if (this.audioUrl) {
                URL.revokeObjectURL(this.audioUrl);
            }
            this.audioBlob = null;
            this.audioUrl = null;
            this.recordingTime = 0;
            this.isPlaying = false;
            this.playbackProgress = 0;
            this.currentPlaybackTime = 0;
            this.state = 'idle';
        },

        async submitRecording() {
            if (!this.audioBlob) return;

            this.state = 'uploading';
            this.uploadProgress = 0;

            try {
                // Convert blob to base64 for Livewire upload
                const extension = this.audioBlob.type.includes('webm') ? 'webm' : 'wav';
                const fileName = `recording-${Date.now()}.${extension}`;

                // Create a File object from the blob
                const file = new File([this.audioBlob], fileName, { type: this.audioBlob.type });

                // Simulate progress while uploading
                const progressInterval = setInterval(() => {
                    if (this.uploadProgress < 90) {
                        this.uploadProgress += 10;
                    }
                }, 200);

                // Upload using Livewire's file upload
                await this.$wire.upload('audioFile', file, () => {
                    clearInterval(progressInterval);
                    this.uploadProgress = 100;
                    // Submit the form
                    this.$wire.submit();
                }, () => {
                    clearInterval(progressInterval);
                    this.state = 'preview';
                    alert('Upload failed. Please try again.');
                });

            } catch (err) {
                console.error('Upload error:', err);
                this.state = 'preview';
                alert('Upload failed. Please try again or use the file upload option.');
            }
        },

        togglePlayback() {
            const audio = this.$refs.audioPlayer;
            if (!audio) return;

            if (this.isPlaying) {
                audio.pause();
            } else {
                audio.play();
            }
            this.isPlaying = !this.isPlaying;
        },

        updatePlaybackProgress() {
            const audio = this.$refs.audioPlayer;
            if (!audio || !audio.duration) return;

            this.currentPlaybackTime = Math.floor(audio.currentTime);
            this.playbackProgress = (audio.currentTime / audio.duration) * 100;
        },

        startTimer() {
            this.timer = setInterval(() => {
                this.recordingTime++;
            }, 1000);
        },

        stopTimer() {
            if (this.timer) {
                clearInterval(this.timer);
                this.timer = null;
            }
        },

        startWaveform() {
            const canvas = this.$refs.waveform;
            if (!canvas || !this.audioStream) return;

            const ctx = canvas.getContext('2d');
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const source = audioContext.createMediaStreamSource(this.audioStream);
            this.analyser = audioContext.createAnalyser();
            this.analyser.fftSize = 256;

            source.connect(this.analyser);

            const bufferLength = this.analyser.frequencyBinCount;
            const dataArray = new Uint8Array(bufferLength);

            const primaryColor = '{{ $settings->primary_color }}';

            const draw = () => {
                if (this.state !== 'recording') return;

                this.animationFrame = requestAnimationFrame(draw);
                this.analyser.getByteFrequencyData(dataArray);

                // Set canvas dimensions
                canvas.width = canvas.offsetWidth * window.devicePixelRatio;
                canvas.height = canvas.offsetHeight * window.devicePixelRatio;
                ctx.scale(window.devicePixelRatio, window.devicePixelRatio);

                const width = canvas.offsetWidth;
                const height = canvas.offsetHeight;

                ctx.fillStyle = '#f3f4f6';
                ctx.fillRect(0, 0, width, height);

                const barWidth = (width / bufferLength) * 2.5;
                let x = 0;

                for (let i = 0; i < bufferLength; i++) {
                    const barHeight = (dataArray[i] / 255) * height * 0.8;

                    ctx.fillStyle = primaryColor;
                    ctx.fillRect(x, (height - barHeight) / 2, barWidth - 1, barHeight);

                    x += barWidth;
                }
            };

            draw();
        },

        stopWaveform() {
            if (this.animationFrame) {
                cancelAnimationFrame(this.animationFrame);
                this.animationFrame = null;
            }
        },

        handleFileSelect(event) {
            // Reset recording state if user selects a file
            if (this.audioUrl) {
                URL.revokeObjectURL(this.audioUrl);
            }
            this.audioBlob = null;
            this.audioUrl = null;
            this.state = 'idle';
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }
    }));
});
</script>
@endpush
