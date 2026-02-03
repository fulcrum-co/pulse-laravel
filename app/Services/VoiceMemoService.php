<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\ProcessVoiceMemo;
use App\Models\ContactNote;
use App\Models\VoiceMemoJob;
use App\Services\Domain\VoiceMemoExtractionService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class VoiceMemoService
{
    public function __construct(
        protected ClaudeService $claudeService,
        protected TranscriptionService $transcriptionService,
        protected VoiceMemoExtractionService $extractionService
    ) {}

    /**
     * Upload and queue a voice memo for processing.
     */
    public function uploadVoiceMemo(
        UploadedFile $file,
        string $contactType,
        int $contactId,
        int $orgId,
        int $userId
    ): ContactNote {
        // Store file securely
        $path = $file->store("voice_memos/{$orgId}/{$contactId}", 'local');

        $note = ContactNote::create([
            'org_id' => $orgId,
            'contact_type' => $contactType,
            'contact_id' => $contactId,
            'note_type' => ContactNote::TYPE_VOICE_MEMO,
            'content' => 'Voice memo - transcription pending',
            'is_voice_memo' => true,
            'audio_file_path' => $path,
            'audio_disk' => 'local',
            'audio_duration_seconds' => $this->getAudioDuration($file),
            'transcription_status' => ContactNote::TRANSCRIPTION_PENDING,
            'contains_pii' => true,
            'created_by' => $userId,
        ]);

        // Create processing job record
        $job = VoiceMemoJob::create([
            'contact_note_id' => $note->id,
            'status' => VoiceMemoJob::STATUS_PENDING,
            'provider' => config('services.transcription.default', 'whisper'),
        ]);

        // Dispatch async processing job
        ProcessVoiceMemo::dispatch($note, $job);

        return $note;
    }

    /**
     * Process transcription result and extract structured data.
     */
    public function processTranscription(ContactNote $note, string $transcription): void
    {
        $note->update([
            'transcription' => $transcription,
            'transcription_status' => ContactNote::TRANSCRIPTION_COMPLETED,
            'transcribed_at' => now(),
        ]);

        // Extract structured data using Claude
        $extracted = $this->extractStructuredData($transcription, $note);

        if ($extracted['success']) {
            $note->update([
                'structured_data' => $extracted['data'],
                'content' => $this->extractionService->generateNoteSummary($transcription, $extracted['data']),
                'raw_content' => $transcription,
            ]);
        } else {
            // Just use the transcription as content
            $note->update([
                'content' => $transcription,
                'raw_content' => $transcription,
            ]);
        }
    }

    /**
     * Extract structured data from transcription using Claude.
     */
    public function extractStructuredData(string $transcription, ContactNote $note): array
    {
        $systemPrompt = $this->extractionService->buildExtractionPrompt();
        $userMessage = $this->extractionService->buildExtractionMessage($transcription);

        $response = $this->claudeService->sendMessage($userMessage, $systemPrompt);

        if (!$response['success']) {
            return ['success' => false, 'error' => $response['error']];
        }

        try {
            $data = json_decode($response['content'], true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                // Try to extract JSON from response
                preg_match('/\{.*\}/s', $response['content'], $matches);
                if (!empty($matches[0])) {
                    $data = json_decode($matches[0], true);
                }
            }

            return ['success' => true, 'data' => $data ?? []];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get audio file duration (if possible).
     */
    private function getAudioDuration(UploadedFile $file): ?int
    {
        // This would require getID3 or ffprobe to properly extract duration
        // For now, return null and let the transcription service provide it
        return null;
    }

    /**
     * Delete audio file for a note.
     */
    public function deleteAudioFile(ContactNote $note): bool
    {
        if ($note->audio_file_path) {
            return Storage::disk($note->audio_disk)->delete($note->audio_file_path);
        }

        return false;
    }

    /**
     * Get audio file URL for playback.
     */
    public function getAudioUrl(ContactNote $note): ?string
    {
        if (! $note->audio_file_path) {
            return null;
        }

        // For local disk, generate a temporary URL
        // For S3, use temporaryUrl
        if ($note->audio_disk === 's3') {
            return Storage::disk('s3')->temporaryUrl(
                $note->audio_file_path,
                now()->addMinutes(30)
            );
        }

        // For local storage, you'd need to set up a route to serve the file
        return route('contact-notes.audio', $note->id);
    }
}
