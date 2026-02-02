<?php

namespace App\Livewire\Help;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.dashboard')]
class KnowledgeBase extends Component
{
    public string $search = '';

    public function render()
    {
        $user = auth()->user();
        $orgId = $user?->org_id;

        $categories = HelpCategory::forOrganization($orgId)
            ->active()
            ->whereNull('parent_id')
            ->withCount(['articles as published_articles_count' => function ($q) use ($orgId) {
                $q->forOrganization($orgId)->published();
            }])
            ->orderBy('sort_order')
            ->get();

        $featuredArticles = HelpArticle::forOrganization($orgId)
            ->published()
            ->featured()
            ->with('category')
            ->limit(6)
            ->get();

        $popularArticles = HelpArticle::forOrganization($orgId)
            ->published()
            ->with('category')
            ->orderByDesc('view_count')
            ->limit(10)
            ->get();

        return view('livewire.help.knowledge-base', [
            'categories' => $categories,
            'featuredArticles' => $featuredArticles,
            'popularArticles' => $popularArticles,
        ])->title('Help Center');
    }
}
