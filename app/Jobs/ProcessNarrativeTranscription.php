<?php

namespace App\Jobs;

use App\Models\PendingExtraction;
use App\Services\AIExtractionService;
use App\Services\TranscriptionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessNarrativeTranscription implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $pendingExtractionId) {}

    public function handle(
        TranscriptionService $transcriptionService,
        AIExtractionService $extractionService
    ): void {
        $extraction = PendingExtraction::with('collectionEvent')->find($this->pendingExtractionId);

        if (! $extraction || ! $extraction->audio_path) {
            return;
        }

        $disk = config('filesystems.default');
        $result = $transcriptionService->transcribe($extraction->audio_path, $disk);

        if (! ($result['success'] ?? false)) {
            Log::warning('ProcessNarrativeTranscription failed', [
                'extraction_id' => $this->pendingExtractionId,
                'error' => $result['error'] ?? 'unknown',
            ]);
            return;
        }

        $transcript = $result['text'] ?? '';
        $extraction->update(['raw_transcript' => $transcript]);

        $schema = $extraction->collectionEvent?->schema_mapping ?? [];
        $aiResult = $extractionService->extract($transcript, $schema);

        if ($aiResult['success'] ?? false) {
            $extraction->update([
                'extracted_data' => $aiResult['data'],
                'confidence_score' => 90,
            ]);
        } else {
            $extraction->update([
                'confidence_score' => 0,
            ]);
        }
    }
}
