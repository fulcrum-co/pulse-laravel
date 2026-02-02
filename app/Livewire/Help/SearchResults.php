<?php

namespace App\Livewire\Help;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.dashboard')]
class SearchResults extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public ?int $category = null;

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedCategory()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $orgId = $user?->org_id;

        $query = HelpArticle::forOrganization($orgId)
            ->published()
            ->with('category');

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', $searchTerm)
                    ->orWhere('content', 'like', $searchTerm)
                    ->orWhere('excerpt', 'like', $searchTerm);
            });
        }

        if ($this->category) {
            $query->where('category_id', $this->category);
        }

        $articles = $query->orderByDesc('is_featured')
            ->orderByDesc('view_count')
            ->paginate(12);

        $categories = HelpCategory::forOrganization($orgId)
            ->active()
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('livewire.help.search-results', [
            'articles' => $articles,
            'categories' => $categories,
        ])->title('Search Results - Help Center');
    }
}
