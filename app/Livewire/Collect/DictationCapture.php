<?php

namespace App\Livewire\Collect;

use App\Models\CollectionToken;
use App\Models\PendingExtraction;
use App\Jobs\ProcessNarrativeTranscription;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class DictationCapture extends Component
{
    use WithFileUploads;

    public CollectionToken $token;

    public $audioFile;

    public bool $submitted = false;

    public function mount(string $token): void
    {
        $this->token = CollectionToken::with(['event.organization.settings', 'contact'])->where('token', $token)->firstOrFail();
        if ($this->token->isExpired() || $this->token->isUsed()) {
            abort(410);
        }
    }

    public function submit(): void
    {
        $this->validate([
            'audioFile' => 'required|file|mimetypes:audio/webm,audio/wav,audio/mpeg|max:51200',
        ]);

        $path = $this->audioFile->store('collection-audio', ['disk' => config('filesystems.default')]);

        $extraction = PendingExtraction::create([
            'contact_id' => $this->token->contact_id,
            'collection_event_id' => $this->token->collection_event_id,
            'audio_path' => $path,
            'status' => 'pending',
        ]);

        $this->token->update(['used_at' => now()]);

        ProcessNarrativeTranscription::dispatch($extraction->id)->onQueue('collections');

        $this->submitted = true;
    }

    public function render()
    {
        $org = $this->token->event->organization;
        $settings = $org?->settings ?? $org?->getOrCreateSettings();

        return view('livewire.collect.dictation-capture', [
            'org' => $org,
            'settings' => $settings,
        ]);
    }
}
