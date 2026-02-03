<?php

declare(strict_types=1);

namespace App\Services;

use App\Services\Domain\TranscriptionProviderService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class TranscriptionService
{
    public function __construct(
        protected TranscriptionProviderService $providerService
    ) {}
    protected string $openaiApiKey;

    protected string $assemblyAiApiKey;

    protected string $defaultProvider;

    public function __construct()
    {
        // Use ?? operator because config() returns null when env var is not set,
        // even if a default is provided (the default only applies if the key doesn't exist)
        $this->openaiApiKey = config('services.openai.api_key') ?? '';
        $this->assemblyAiApiKey = config('services.assembly_ai.api_key') ?? '';
        $this->defaultProvider = config('services.transcription.default') ?? 'whisper';
    }

    /**
     * Transcribe an audio file.
     */
    public function transcribe(string $filePath, string $disk = 'local', ?string $provider = null): array
    {
        $provider = $provider ?? $this->defaultProvider;

        return match ($provider) {
            'whisper' => $this->transcribeWithWhisper($filePath, $disk),
            'assembly_ai' => $this->transcribeWithAssemblyAi($filePath, $disk),
            default => throw new \Exception("Unknown transcription provider: {$provider}"),
        };
    }

    /**
     * Transcribe using OpenAI Whisper API.
     */
    private function transcribeWithWhisper(string $filePath, string $disk): array
    {
        if (empty($this->openaiApiKey)) {
            return [
                'success' => false,
                'error' => 'OpenAI API key not configured',
            ];
        }

        try {
            $fileContent = Storage::disk($disk)->get($filePath);
            $fileName = basename($filePath);
            $mimeType = $this->providerService->getMimeType($filePath);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer '.$this->openaiApiKey,
            ])->attach(
                'file',
                $fileContent,
                $fileName,
                ['Content-Type' => $mimeType]
            )->post('https://api.openai.com/v1/audio/transcriptions', [
                'model' => 'whisper-1',
                'language' => 'en',
                'response_format' => 'json',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'text' => $data['text'] ?? '',
                    'provider' => 'whisper',
                    'duration' => $data['duration'] ?? null,
                ];
            }

            Log::error('Whisper API error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json()['error']['message'] ?? 'Transcription failed',
            ];
        } catch (\Exception $e) {
            Log::error('Whisper API exception', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Transcribe using AssemblyAI API.
     */
    private function transcribeWithAssemblyAi(string $filePath, string $disk): array
    {
        if (empty($this->assemblyAiApiKey)) {
            return [
                'success' => false,
                'error' => 'AssemblyAI API key not configured',
            ];
        }

        try {
            // First upload the file
            $fileContent = Storage::disk($disk)->get($filePath);

            $uploadResponse = Http::withHeaders([
                'Authorization' => $this->assemblyAiApiKey,
                'Content-Type' => 'application/octet-stream',
            ])->withBody($fileContent, 'application/octet-stream')
                ->post('https://api.assemblyai.com/v2/upload');

            if (! $uploadResponse->successful()) {
                return [
                    'success' => false,
                    'error' => 'Failed to upload audio file',
                ];
            }

            $uploadUrl = $uploadResponse->json()['upload_url'];

            // Submit for transcription
            $transcribeResponse = Http::withHeaders([
                'Authorization' => $this->assemblyAiApiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.assemblyai.com/v2/transcript', [
                'audio_url' => $uploadUrl,
                'language_code' => 'en',
            ]);

            if (! $transcribeResponse->successful()) {
                return [
                    'success' => false,
                    'error' => 'Failed to start transcription',
                ];
            }

            $transcriptId = $transcribeResponse->json()['id'];

            // Poll for result (in real implementation, this would be async)
            $maxAttempts = 60;
            $attempts = 0;

            while ($attempts < $maxAttempts) {
                sleep(2);
                $attempts++;

                $statusResponse = Http::withHeaders([
                    'Authorization' => $this->assemblyAiApiKey,
                ])->get("https://api.assemblyai.com/v2/transcript/{$transcriptId}");

                $status = $statusResponse->json()['status'];

                if ($status === 'completed') {
                    return [
                        'success' => true,
                        'text' => $statusResponse->json()['text'] ?? '',
                        'provider' => 'assembly_ai',
                        'duration' => $statusResponse->json()['audio_duration'] ?? null,
                    ];
                }

                if ($status === 'error') {
                    return [
                        'success' => false,
                        'error' => $statusResponse->json()['error'] ?? 'Transcription failed',
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Transcription timed out',
            ];
        } catch (\Exception $e) {
            Log::error('AssemblyAI API exception', ['message' => $e->getMessage()]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

}
