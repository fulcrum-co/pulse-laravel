<?php

namespace App\Livewire\Admin;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.dashboard')]
class HelpArticleManager extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $categoryFilter = '';

    #[Url]
    public string $statusFilter = '';

    public bool $showModal = false;
    public bool $editMode = false;

    // Form fields
    public ?int $editingId = null;
    public string $title = '';
    public string $slug = '';
    public string $content = '';
    public string $excerpt = '';
    public ?int $categoryId = null;
    public array $targetRoles = [];
    public array $searchKeywords = [];
    public string $keywordsInput = '';
    public string $videoUrl = '';
    public bool $isPublished = false;
    public bool $isFeatured = false;

    protected function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|regex:/^[a-z0-9\-]+$/',
            'content' => 'required|string',
            'excerpt' => 'nullable|string|max:500',
            'categoryId' => 'nullable|exists:help_categories,id',
            'targetRoles' => 'array',
            'videoUrl' => 'nullable|url|max:500',
            'isPublished' => 'boolean',
            'isFeatured' => 'boolean',
        ];
    }

    protected $messages = [
        'slug.regex' => 'Slug must be lowercase with hyphens only',
    ];

    #[Computed]
    public function articles()
    {
        return HelpArticle::whereNull('org_id')
            ->with('category')
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->categoryFilter, fn ($q) => $q->where('category_id', $this->categoryFilter))
            ->when($this->statusFilter === 'published', fn ($q) => $q->where('is_published', true))
            ->when($this->statusFilter === 'draft', fn ($q) => $q->where('is_published', false))
            ->when($this->statusFilter === 'featured', fn ($q) => $q->where('is_featured', true))
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
    }

    #[Computed]
    public function categories()
    {
        return HelpCategory::whereNull('org_id')
            ->active()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function availableRoles()
    {
        return [
            'admin' => 'Administrator',
            'counselor' => 'Counselor',
            'teacher' => 'Teacher',
            'parent' => 'Parent',
            'student' => 'Student',
        ];
    }

    public function updatedTitle($value): void
    {
        if (! $this->editMode) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->editMode = false;
        $this->showModal = true;
    }

    public function openEditModal(int $id): void
    {
        $article = HelpArticle::findOrFail($id);

        $this->editingId = $article->id;
        $this->title = $article->title;
        $this->slug = $article->slug;
        $this->content = $article->content ?? '';
        $this->excerpt = $article->excerpt ?? '';
        $this->categoryId = $article->category_id;
        $this->targetRoles = $article->target_roles ?? [];
        $this->searchKeywords = $article->search_keywords ?? [];
        $this->keywordsInput = implode(', ', $this->searchKeywords);
        $this->videoUrl = $article->video_url ?? '';
        $this->isPublished = $article->is_published;
        $this->isFeatured = $article->is_featured;

        $this->editMode = true;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Parse keywords from comma-separated input
        $keywords = array_filter(array_map('trim', explode(',', $this->keywordsInput)));

        $data = [
            'org_id' => null,
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'excerpt' => $this->excerpt ?: Str::limit(strip_tags($this->content), 200),
            'category_id' => $this->categoryId,
            'target_roles' => $this->targetRoles ?: null,
            'search_keywords' => $keywords ?: null,
            'video_url' => $this->videoUrl ?: null,
            'is_published' => $this->isPublished,
            'is_featured' => $this->isFeatured,
            'published_at' => $this->isPublished ? now() : null,
        ];

        if ($this->editMode && $this->editingId) {
            $article = HelpArticle::findOrFail($this->editingId);
            // Don't update published_at if already published
            if ($article->is_published && $this->isPublished) {
                unset($data['published_at']);
            }
            $article->update($data);
            $this->dispatch('toast', message: 'Article updated successfully', type: 'success');
        } else {
            $exists = HelpArticle::whereNull('org_id')
                ->where('slug', $this->slug)
                ->exists();

            if ($exists) {
                $this->addError('slug', 'This slug is already in use.');

                return;
            }

            $data['created_by'] = auth()->id();
            HelpArticle::create($data);
            $this->dispatch('toast', message: 'Article created successfully', type: 'success');
        }

        $this->closeModal();
        unset($this->articles);
    }

    public function delete(int $id): void
    {
        $article = HelpArticle::findOrFail($id);
        $article->delete();

        $this->dispatch('toast', message: 'Article deleted', type: 'success');
        unset($this->articles);
    }

    public function togglePublished(int $id): void
    {
        $article = HelpArticle::findOrFail($id);
        $article->update([
            'is_published' => ! $article->is_published,
            'published_at' => ! $article->is_published ? now() : $article->published_at,
        ]);
        unset($this->articles);
    }

    public function toggleFeatured(int $id): void
    {
        $article = HelpArticle::findOrFail($id);
        $article->update(['is_featured' => ! $article->is_featured]);
        unset($this->articles);
    }

    public function duplicate(int $id): void
    {
        $article = HelpArticle::findOrFail($id);

        $newArticle = $article->replicate();
        $newArticle->title = $article->title.' (Copy)';
        $newArticle->slug = $article->slug.'-copy-'.Str::random(4);
        $newArticle->is_published = false;
        $newArticle->is_featured = false;
        $newArticle->view_count = 0;
        $newArticle->helpful_count = 0;
        $newArticle->not_helpful_count = 0;
        $newArticle->published_at = null;
        $newArticle->created_by = auth()->id();
        $newArticle->save();

        $this->dispatch('toast', message: 'Article duplicated', type: 'success');
        unset($this->articles);
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->slug = '';
        $this->content = '';
        $this->excerpt = '';
        $this->categoryId = null;
        $this->targetRoles = [];
        $this->searchKeywords = [];
        $this->keywordsInput = '';
        $this->videoUrl = '';
        $this->isPublished = false;
        $this->isFeatured = false;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.help-article-manager');
    }
}
