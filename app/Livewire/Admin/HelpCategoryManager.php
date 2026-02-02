<?php

namespace App\Livewire\Admin;

use App\Models\HelpCategory;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.dashboard')]
class HelpCategoryManager extends Component
{
    public bool $showModal = false;
    public bool $editMode = false;

    // Form fields
    public ?int $editingId = null;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public string $icon = 'book-open';
    public ?int $parentId = null;
    public int $sortOrder = 0;
    public bool $isActive = true;

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/',
            'description' => 'nullable|string|max:1000',
            'icon' => 'required|string|max:50',
            'parentId' => 'nullable|exists:help_categories,id',
            'sortOrder' => 'required|integer|min:0',
            'isActive' => 'boolean',
        ];
    }

    protected $messages = [
        'slug.regex' => 'Slug must be lowercase with hyphens only',
    ];

    #[Computed]
    public function categories()
    {
        return HelpCategory::whereNull('org_id')
            ->with(['parent', 'children', 'articles'])
            ->orderBy('sort_order')
            ->get();
    }

    #[Computed]
    public function parentCategories()
    {
        return HelpCategory::whereNull('org_id')
            ->whereNull('parent_id')
            ->when($this->editingId, fn ($q) => $q->where('id', '!=', $this->editingId))
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function icons()
    {
        return [
            'book-open' => 'Book Open',
            'academic-cap' => 'Academic Cap',
            'light-bulb' => 'Light Bulb',
            'question-mark-circle' => 'Question Mark',
            'cog-6-tooth' => 'Settings',
            'chart-bar' => 'Chart',
            'users' => 'Users',
            'document-text' => 'Document',
            'folder' => 'Folder',
            'home' => 'Home',
            'bell' => 'Bell',
            'shield-check' => 'Shield',
            'arrow-trending-up' => 'Trending',
            'clipboard-document-list' => 'Clipboard',
            'play-circle' => 'Play',
        ];
    }

    public function updatedName($value): void
    {
        if (! $this->editMode) {
            $this->slug = Str::slug($value);
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;

        $maxOrder = HelpCategory::whereNull('org_id')->max('sort_order');
        $this->sortOrder = ($maxOrder ?? -1) + 1;
    }

    public function openEditModal(int $id): void
    {
        $category = HelpCategory::findOrFail($id);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->description = $category->description ?? '';
        $this->icon = $category->icon ?? 'book-open';
        $this->parentId = $category->parent_id;
        $this->sortOrder = $category->sort_order;
        $this->isActive = $category->is_active;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'org_id' => null,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description ?: null,
            'icon' => $this->icon,
            'parent_id' => $this->parentId,
            'sort_order' => $this->sortOrder,
            'is_active' => $this->isActive,
        ];

        if ($this->editMode && $this->editingId) {
            $category = HelpCategory::findOrFail($this->editingId);
            $category->update($data);
            $this->dispatch('toast', message: 'Category updated successfully', type: 'success');
        } else {
            $exists = HelpCategory::whereNull('org_id')
                ->where('slug', $this->slug)
                ->exists();

            if ($exists) {
                $this->addError('slug', 'This slug is already in use.');

                return;
            }

            HelpCategory::create($data);
            $this->dispatch('toast', message: 'Category created successfully', type: 'success');
        }

        $this->closeModal();
        unset($this->categories);
    }

    public function delete(int $id): void
    {
        $category = HelpCategory::findOrFail($id);

        if ($category->articles()->count() > 0) {
            $this->dispatch('toast', message: 'Cannot delete category with articles. Move or delete articles first.', type: 'error');

            return;
        }

        if ($category->children()->count() > 0) {
            $this->dispatch('toast', message: 'Cannot delete category with subcategories. Delete subcategories first.', type: 'error');

            return;
        }

        $category->delete();
        $this->dispatch('toast', message: 'Category deleted', type: 'success');
        unset($this->categories);
    }

    public function toggleActive(int $id): void
    {
        $category = HelpCategory::findOrFail($id);
        $category->update(['is_active' => ! $category->is_active]);
        unset($this->categories);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->slug = '';
        $this->description = '';
        $this->icon = 'book-open';
        $this->parentId = null;
        $this->sortOrder = 0;
        $this->isActive = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.help-category-manager');
    }
}
