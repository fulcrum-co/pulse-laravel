<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\ContactNote;
use App\Services\VoiceMemoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactNoteController extends Controller
{
    public function __construct(
        protected VoiceMemoService $voiceMemoService
    ) {}

    /**
     * List notes for a contact.
     */
    public function index(Request $request, string $contactType, int $contactId): JsonResponse
    {
        $user = auth()->user();

        // Map shorthand contact type to full class name
        $typeMap = [
            'participant' => 'App\\Models\\Participant',
            'user' => 'App\\Models\\User',
        ];
        $fullType = $typeMap[$contactType] ?? $contactType;

        $notes = ContactNote::where('contact_type', $fullType)
            ->where('contact_id', $contactId)
            ->visibleTo($user)
            ->with(['author', 'replies.author'])
            ->latest()
            ->paginate(20);

        return response()->json($notes);
    }

    /**
     * Create a new note.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'contact_type' => 'required|string|in:participant,user,App\\Models\\Participant,App\\Models\\User',
            'contact_id' => 'required|integer',
            'note_type' => 'required|string|in:general,follow_up,concern,milestone,voice_memo',
            'content' => 'required_without:voice_memo|string|min:1',
            'voice_memo' => 'required_without:content|file|mimes:mp3,wav,m4a,ogg,webm|max:51200',
            'is_private' => 'boolean',
            'visibility' => 'in:private,team,organization',
            'related_plan_id' => 'nullable|exists:strategic_plans,id',
        ]);

        $user = auth()->user();

        // Map shorthand contact type to full class name
        $typeMap = [
            'participant' => 'App\\Models\\Participant',
            'user' => 'App\\Models\\User',
        ];
        $contactType = $typeMap[$validated['contact_type']] ?? $validated['contact_type'];

        if ($request->hasFile('voice_memo')) {
            $note = $this->voiceMemoService->uploadVoiceMemo(
                $request->file('voice_memo'),
                $contactType,
                $validated['contact_id'],
                $user->org_id,
                $user->id
            );

            // Update with additional fields
            $note->update([
                'note_type' => $validated['note_type'],
                'is_private' => $validated['is_private'] ?? false,
                'visibility' => $validated['visibility'] ?? 'organization',
                'related_plan_id' => $validated['related_plan_id'] ?? null,
            ]);
        } else {
            $note = ContactNote::create([
                'org_id' => $user->org_id,
                'contact_type' => $contactType,
                'contact_id' => $validated['contact_id'],
                'note_type' => $validated['note_type'],
                'content' => $validated['content'],
                'is_private' => $validated['is_private'] ?? false,
                'visibility' => $validated['visibility'] ?? 'organization',
                'related_plan_id' => $validated['related_plan_id'] ?? null,
                'created_by' => $user->id,
            ]);
        }

        AuditLog::log('create', $note);

        return response()->json([
            'success' => true,
            'note' => $note->load('author'),
        ], 201);
    }

    /**
     * Update a note.
     */
    public function update(Request $request, ContactNote $note): JsonResponse
    {
        $this->authorize('update', $note);

        $validated = $request->validate([
            'content' => 'required|string|min:1',
            'note_type' => 'sometimes|string|in:general,follow_up,concern,milestone',
            'is_private' => 'boolean',
            'visibility' => 'in:private,team,organization',
        ]);

        $oldValues = $note->only(['content', 'note_type', 'is_private', 'visibility']);

        $note->update($validated);

        AuditLog::log('update', $note, $oldValues, $validated);

        return response()->json([
            'success' => true,
            'note' => $note->fresh()->load('author'),
        ]);
    }

    /**
     * Delete a note.
     */
    public function destroy(ContactNote $note): JsonResponse
    {
        $this->authorize('delete', $note);

        // Delete audio file if voice memo
        if ($note->is_voice_memo && $note->audio_file_path) {
            $this->voiceMemoService->deleteAudioFile($note);
        }

        AuditLog::log('delete', $note);

        $note->delete();

        return response()->json([
            'success' => true,
        ]);
    }

    /**
     * Get audio file for playback.
     */
    public function audio(ContactNote $note)
    {
        $this->authorize('view', $note);

        if (! $note->is_voice_memo || ! $note->audio_file_path) {
            abort(404);
        }

        AuditLog::log('view', $note);

        $disk = \Illuminate\Support\Facades\Storage::disk($note->audio_disk);

        if (! $disk->exists($note->audio_file_path)) {
            abort(404);
        }

        return response()->file(
            $disk->path($note->audio_file_path),
            ['Content-Type' => $this->getMimeType($note->audio_file_path)]
        );
    }

    /**
     * Get MIME type from file path.
     */
    private function getMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($extension) {
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'm4a' => 'audio/mp4',
            'ogg' => 'audio/ogg',
            'webm' => 'audio/webm',
            default => 'audio/mpeg',
        };
    }
}
