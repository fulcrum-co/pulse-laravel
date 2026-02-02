<?php

namespace App\Livewire\Help;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.dashboard')]
class CategoryBrowser extends Component
{
    use WithPagination;

    public HelpCategory $category;

    public function mount(string $slug)
    {
        $user = auth()->user();
        $orgId = $user?->org_id;

        $this->category = HelpCategory::forOrganization($orgId)
            ->active()
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function render()
    {
        $user = auth()->user();
        $orgId = $user?->org_id;

        $articles = HelpArticle::forOrganization($orgId)
            ->published()
            ->where('category_id', $this->category->id)
            ->orderByDesc('is_featured')
            ->orderByDesc('published_at')
            ->paginate(12);

        $subcategories = HelpCategory::forOrganization($orgId)
            ->active()
            ->where('parent_id', $this->category->id)
            ->withCount(['articles as published_articles_count' => function ($q) use ($orgId) {
                $q->forOrganization($orgId)->published();
            }])
            ->orderBy('sort_order')
            ->get();

        return view('livewire.help.category-browser', [
            'articles' => $articles,
            'subcategories' => $subcategories,
        ])->title($this->category->name . ' - Help Center');
    }
}
