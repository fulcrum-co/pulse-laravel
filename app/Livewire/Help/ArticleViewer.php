<?php

namespace App\Livewire\Help;

use App\Models\HelpArticle;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.dashboard')]
class ArticleViewer extends Component
{
    public HelpArticle $article;

    public function mount(string $slug)
    {
        $user = auth()->user();
        $orgId = $user?->org_id;

        $this->article = HelpArticle::forOrganization($orgId)
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        // Increment view count
        $this->article->incrementViewCount();
    }

    public function markHelpful(bool $helpful)
    {
        $this->article->recordFeedback($helpful);
    }

    public function render()
    {
        $relatedArticles = HelpArticle::forOrganization(auth()->user()?->org_id)
            ->published()
            ->where('id', '!=', $this->article->id)
            ->where('category_id', $this->article->category_id)
            ->limit(3)
            ->get();

        return view('livewire.help.article-viewer', [
            'relatedArticles' => $relatedArticles,
        ])->title($this->article->title . ' - Help Center');
    }
}
