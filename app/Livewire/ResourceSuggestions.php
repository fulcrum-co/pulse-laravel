<?php

namespace App\Livewire;

use App\Models\ContactResourceSuggestion;
use App\Models\Resource;
use App\Services\ResourceSuggestionService;
use Livewire\Component;

class ResourceSuggestions extends Component
{
    public string $contactType;

    public int $contactId;

    // Manual suggestion form
    public bool $showAddForm = false;

    public ?int $selectedResourceId = null;

    // Review modal
    public ?int $reviewingSuggestionId = null;

    public string $reviewNotes = '';

    protected ResourceSuggestionService $suggestionService;

    public function boot(ResourceSuggestionService $suggestionService)
    {
        $this->suggestionService = $suggestionService;
    }

    public function mount(string $contactType, int $contactId)
    {
        $this->contactType = $contactType;
        $this->contactId = $contactId;
    }

    public function toggleAddForm()
    {
        $this->showAddForm = ! $this->showAddForm;
        $this->selectedResourceId = null;
    }

    public function addManualSuggestion()
    {
        $this->validate([
            'selectedResourceId' => 'required|exists:resources,id',
        ]);

        // Map shorthand contact type to full class name
        $typeMap = [
            'student' => 'App\\Models\\Student',
            'user' => 'App\\Models\\User',
        ];
        $fullContactType = $typeMap[$this->contactType] ?? $this->contactType;

        $this->suggestionService->manualSuggest(
            $fullContactType,
            $this->contactId,
            $this->selectedResourceId,
            auth()->id()
        );

        $this->showAddForm = false;
        $this->selectedResourceId = null;

        $this->dispatch('suggestion-added');
    }

    public function openReview(int $suggestionId)
    {
        $this->reviewingSuggestionId = $suggestionId;
        $this->reviewNotes = '';
    }

    public function closeReview()
    {
        $this->reviewingSuggestionId = null;
        $this->reviewNotes = '';
    }

    public function acceptSuggestion()
    {
        $suggestion = ContactResourceSuggestion::findOrFail($this->reviewingSuggestionId);

        $suggestion->accept(auth()->user(), $this->reviewNotes);

        $this->closeReview();
        $this->dispatch('suggestion-accepted');
    }

    public function declineSuggestion()
    {
        $suggestion = ContactResourceSuggestion::findOrFail($this->reviewingSuggestionId);

        $suggestion->decline(auth()->user(), $this->reviewNotes);

        $this->closeReview();
        $this->dispatch('suggestion-declined');
    }

    public function getSuggestionsProperty()
    {
        // Map shorthand contact type to full class name
        $typeMap = [
            'student' => 'App\\Models\\Student',
            'user' => 'App\\Models\\User',
        ];
        $fullContactType = $typeMap[$this->contactType] ?? $this->contactType;

        return ContactResourceSuggestion::where('contact_type', $fullContactType)
            ->where('contact_id', $this->contactId)
            ->with('resource')
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
    }

    public function getAvailableResourcesProperty()
    {
        $user = auth()->user();

        return Resource::forOrganization($user->org_id)
            ->active()
            ->orderBy('title')
            ->get();
    }

    public function render()
    {
        return view('livewire.resource-suggestions', [
            'suggestions' => $this->suggestions,
            'availableResources' => $this->availableResources,
        ]);
    }
}
