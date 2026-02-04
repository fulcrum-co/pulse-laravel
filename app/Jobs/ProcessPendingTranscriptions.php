<?php

namespace App\Jobs;

use App\Models\PendingExtraction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPendingTranscriptions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $pending = PendingExtraction::query()
            ->whereNull('raw_transcript')
            ->whereNotNull('audio_path')
            ->where('status', 'pending')
            ->limit(50)
            ->get(['id']);

        foreach ($pending as $item) {
            ProcessNarrativeTranscription::dispatch($item->id)->onQueue('collections');
        }
    }
}
