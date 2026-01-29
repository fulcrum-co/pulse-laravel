<?php

namespace App\Livewire;

use App\Models\AuditLog;
use App\Models\ContactNote;
use App\Services\VoiceMemoService;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ContactNotes extends Component
{
    use WithFileUploads, WithPagination;

    public string $contactType;
    public int $contactId;

    // New note form
    public string $newNoteContent = '';
    public string $newNoteType = 'general';
    public bool $isPrivate = false;
    public string $visibility = 'organization';
    public $voiceMemo = null;

    // Recording state
    public bool $isRecording = false;

    // Edit state
    public ?int $editingNoteId = null;
    public string $editContent = '';
    public string $editNoteType = 'general';

    // Filter
    public string $filterType = 'all';

    protected VoiceMemoService $voiceMemoService;

    protected $rules = [
        'newNoteContent' => 'required_without:voiceMemo|string|min:1',
        'newNoteType' => 'required|in:general,follow_up,concern,milestone,voice_memo',
        'isPrivate' => 'boolean',
        'visibility' => 'in:private,team,organization',
        'voiceMemo' => 'nullable|file|mimes:mp3,wav,m4a,ogg,webm|max:51200',
    ];

    public function boot(VoiceMemoService $voiceMemoService)
    {
        $this->voiceMemoService = $voiceMemoService;
    }

    public function mount(string $contactType, int $contactId)
    {
        $this->contactType = $contactType;
        $this->contactId = $contactId;
    }

    public function saveNote()
    {
        $this->validate();

        $user = auth()->user();

        // Map shorthand contact type to full class name
        $typeMap = [
            'student' => 'App\\Models\\Student',
            'user' => 'App\\Models\\User',
        ];
        $fullContactType = $typeMap[$this->contactType] ?? $this->contactType;

        if ($this->voiceMemo) {
            $note = $this->voiceMemoService->uploadVoiceMemo(
                $this->voiceMemo,
                $fullContactType,
                $this->contactId,
                $user->org_id,
                $user->id
            );

            $note->update([
                'note_type' => 'voice_memo',
                'is_private' => $this->isPrivate,
                'visibility' => $this->visibility,
            ]);
        } else {
            $note = ContactNote::create([
                'org_id' => $user->org_id,
                'contact_type' => $fullContactType,
                'contact_id' => $this->contactId,
                'note_type' => $this->newNoteType,
                'content' => $this->newNoteContent,
                'is_private' => $this->isPrivate,
                'visibility' => $this->visibility,
                'created_by' => $user->id,
            ]);
        }

        AuditLog::log('create', $note);

        $this->reset(['newNoteContent', 'newNoteType', 'isPrivate', 'voiceMemo']);
        $this->visibility = 'organization';

        $this->dispatch('note-saved');
    }

    public function startEdit(int $noteId)
    {
        $note = ContactNote::findOrFail($noteId);
        $this->authorize('update', $note);

        $this->editingNoteId = $noteId;
        $this->editContent = $note->content;
        $this->editNoteType = $note->note_type;
    }

    public function cancelEdit()
    {
        $this->reset(['editingNoteId', 'editContent', 'editNoteType']);
    }

    public function updateNote()
    {
        $note = ContactNote::findOrFail($this->editingNoteId);
        $this->authorize('update', $note);

        $oldValues = $note->only(['content', 'note_type']);

        $note->update([
            'content' => $this->editContent,
            'note_type' => $this->editNoteType,
        ]);

        AuditLog::log('update', $note, $oldValues, [
            'content' => $this->editContent,
            'note_type' => $this->editNoteType,
        ]);

        $this->cancelEdit();
    }

    public function deleteNote(int $noteId)
    {
        $note = ContactNote::findOrFail($noteId);
        $this->authorize('delete', $note);

        if ($note->is_voice_memo && $note->audio_file_path) {
            $this->voiceMemoService->deleteAudioFile($note);
        }

        AuditLog::log('delete', $note);
        $note->delete();
    }

    public function setFilterType(string $type)
    {
        $this->filterType = $type;
        $this->resetPage();
    }

    public function getNotesProperty()
    {
        $user = auth()->user();

        // Map shorthand contact type to full class name
        $typeMap = [
            'student' => 'App\\Models\\Student',
            'user' => 'App\\Models\\User',
        ];
        $fullContactType = $typeMap[$this->contactType] ?? $this->contactType;

        $query = ContactNote::where('contact_type', $fullContactType)
            ->where('contact_id', $this->contactId)
            ->visibleTo($user)
            ->with(['author', 'replies.author']);

        if ($this->filterType !== 'all') {
            $query->where('note_type', $this->filterType);
        }

        return $query->latest()->paginate(10);
    }

    public function render()
    {
        return view('livewire.contact-notes', [
            'notes' => $this->notes,
        ]);
    }
}
