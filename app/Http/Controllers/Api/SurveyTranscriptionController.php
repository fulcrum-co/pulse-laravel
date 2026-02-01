<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TranscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SurveyTranscriptionController extends Controller
{
    public function __construct(
        protected TranscriptionService $transcriptionService
    ) {}

    /**
     * Transcribe an uploaded audio file.
     */
    public function transcribe(Request $request)
    {
        $request->validate([
            'audio' => 'required|file|mimes:webm,wav,mp3,m4a,ogg,flac|max:25600', // 25MB max
        ]);

        $file = $request->file('audio');
        $filename = 'transcriptions/'.Str::uuid().'.'.$file->getClientOriginalExtension();

        // Store the file temporarily
        Storage::disk('local')->put($filename, file_get_contents($file->getRealPath()));

        try {
            // Transcribe the file
            $result = $this->transcriptionService->transcribe($filename, 'local');

            // Clean up the temporary file
            Storage::disk('local')->delete($filename);

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'transcription' => $result['text'],
                    'duration' => $result['duration'] ?? null,
                ]);
            }

            return response()->json([
                'success' => false,
                'error' => $result['error'] ?? 'Transcription failed',
            ], 422);
        } catch (\Exception $e) {
            // Clean up the temporary file on error
            Storage::disk('local')->delete($filename);

            return response()->json([
                'success' => false,
                'error' => 'Transcription service error: '.$e->getMessage(),
            ], 500);
        }
    }
}
