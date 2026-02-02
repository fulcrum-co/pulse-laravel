<?php

namespace App\Livewire\Admin;

use App\Models\PageHelpHint;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.dashboard')]
class HelpHintManager extends Component
{
    public string $selectedContext = 'dashboard';
    public bool $showModal = false;
    public bool $editMode = false;

    // Form fields
    public ?int $editingId = null;
    public string $section = '';
    public string $selector = '';
    public string $title = '';
    public string $description = '';
    public string $position = 'bottom';
    public int $sortOrder = 0;
    public bool $isActive = true;

    protected function rules(): array
    {
        return [
            'section' => 'required|string|max:50|regex:/^[a-z0-9\-]+$/',
            'selector' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'position' => 'required|in:top,bottom,left,right',
            'sortOrder' => 'required|integer|min:0',
            'isActive' => 'boolean',
        ];
    }

    protected $messages = [
        'section.regex' => 'Section must be lowercase with hyphens only (e.g., search-reports)',
    ];

    #[Computed]
    public function hints()
    {
        return PageHelpHint::where('page_context', $this->selectedContext)
            ->whereNull('org_id') // System-wide hints only for admin
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function contexts()
    {
        return PageHelpHint::CONTEXTS;
    }

    #[Computed]
    public function positions()
    {
        return PageHelpHint::POSITIONS;
    }

    public function selectContext(string $context): void
    {
        $this->selectedContext = $context;
        unset($this->hints);
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;

        // Set default sort order to be after existing hints
        $maxOrder = PageHelpHint::where('page_context', $this->selectedContext)
            ->whereNull('org_id')
            ->max('sort_order');
        $this->sortOrder = ($maxOrder ?? -1) + 1;
    }

    public function openEditModal(int $id): void
    {
        $hint = PageHelpHint::findOrFail($id);

        $this->editingId = $hint->id;
        $this->section = $hint->section;
        $this->selector = $hint->selector;
        $this->title = $hint->title;
        $this->description = $hint->description;
        $this->position = $hint->position;
        $this->sortOrder = $hint->sort_order;
        $this->isActive = $hint->is_active;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'org_id' => null, // System-wide
            'page_context' => $this->selectedContext,
            'section' => $this->section,
            'selector' => $this->selector,
            'title' => $this->title,
            'description' => $this->description,
            'position' => $this->position,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
        ];

        if ($this->editMode && $this->editingId) {
            $hint = PageHelpHint::findOrFail($this->editingId);
            $hint->update($data);
            $this->dispatch('toast', message: 'Help hint updated successfully', type: 'success');
        } else {
            // Check for duplicate section
            $exists = PageHelpHint::where('page_context', $this->selectedContext)
                ->whereNull('org_id')
                ->where('section', $this->section)
                ->exists();

            if ($exists) {
                $this->addError('section', 'This section already exists for this page.');

                return;
            }

            PageHelpHint::create($data);
            $this->dispatch('toast', message: 'Help hint created successfully', type: 'success');
        }

        $this->closeModal();
        unset($this->hints);
    }

    public function delete(int $id): void
    {
        $hint = PageHelpHint::findOrFail($id);
        $hint->delete();

        $this->dispatch('toast', message: 'Help hint deleted', type: 'success');
        unset($this->hints);
    }

    public function toggleActive(int $id): void
    {
        $hint = PageHelpHint::findOrFail($id);
        $hint->update(['is_active' => ! $hint->is_active]);

        unset($this->hints);
    }

    public function reorder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            PageHelpHint::where('id', $id)->update(['sort_order' => $index]);
        }

        unset($this->hints);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->section = '';
        $this->selector = '';
        $this->title = '';
        $this->description = '';
        $this->position = 'bottom';
        $this->sortOrder = 0;
        $this->isActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.help-hint-manager');
    }
}
