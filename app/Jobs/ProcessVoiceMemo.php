<?php

namespace App\Jobs;

use App\Models\ContactNote;
use App\Models\VoiceMemoJob;
use App\Services\TranscriptionService;
use App\Services\VoiceMemoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessVoiceMemo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ContactNote $note,
        public VoiceMemoJob $job
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        TranscriptionService $transcriptionService,
        VoiceMemoService $voiceMemoService
    ): void {
        Log::info('Processing voice memo', ['note_id' => $this->note->id]);

        $this->job->markStarted();

        try {
            // Update note status
            $this->note->update([
                'transcription_status' => ContactNote::TRANSCRIPTION_PROCESSING,
            ]);

            // Transcribe audio
            $result = $transcriptionService->transcribe(
                $this->note->audio_file_path,
                $this->note->audio_disk
            );

            if (!$result['success']) {
                throw new \Exception($result['error']);
            }

            Log::info('Transcription completed', [
                'note_id' => $this->note->id,
                'text_length' => strlen($result['text']),
            ]);

            // Update job status
            $this->job->update([
                'status' => VoiceMemoJob::STATUS_EXTRACTING,
                'transcription_result' => $result,
            ]);

            // Process transcription (extract structured data using Claude)
            $voiceMemoService->processTranscription($this->note, $result['text']);

            // Mark job as completed
            $this->job->markCompleted(
                $result,
                $this->note->fresh()->structured_data
            );

            Log::info('Voice memo processing completed', ['note_id' => $this->note->id]);

        } catch (\Exception $e) {
            Log::error('Voice memo processing failed', [
                'note_id' => $this->note->id,
                'error' => $e->getMessage(),
            ]);

            $this->job->markFailed($e->getMessage());

            $this->note->update([
                'transcription_status' => ContactNote::TRANSCRIPTION_FAILED,
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Voice memo job failed permanently', [
            'note_id' => $this->note->id,
            'error' => $exception->getMessage(),
        ]);

        $this->job->markFailed($exception->getMessage());

        $this->note->update([
            'transcription_status' => ContactNote::TRANSCRIPTION_FAILED,
            'content' => 'Voice memo transcription failed. Please try again.',
        ]);
    }
}
