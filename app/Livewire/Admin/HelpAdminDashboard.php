<?php

namespace App\Livewire\Admin;

use App\Models\HelpArticle;
use App\Models\HelpCategory;
use App\Models\PageHelpHint;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.dashboard')]
class HelpAdminDashboard extends Component
{
    #[Url]
    public string $view = 'grid';

    public function setView(string $view): void
    {
        $this->view = $view;
    }

    #[Computed]
    public function stats()
    {
        return [
            'articles' => [
                'total' => HelpArticle::whereNull('org_id')->count(),
                'published' => HelpArticle::whereNull('org_id')->where('is_published', true)->count(),
                'draft' => HelpArticle::whereNull('org_id')->where('is_published', false)->count(),
                'featured' => HelpArticle::whereNull('org_id')->where('is_featured', true)->count(),
            ],
            'categories' => [
                'total' => HelpCategory::whereNull('org_id')->count(),
                'active' => HelpCategory::whereNull('org_id')->where('is_active', true)->count(),
            ],
            'hints' => [
                'total' => PageHelpHint::whereNull('org_id')->count(),
                'active' => PageHelpHint::whereNull('org_id')->where('is_active', true)->count(),
                'pages' => PageHelpHint::whereNull('org_id')->distinct('page_context')->count('page_context'),
            ],
        ];
    }

    #[Computed]
    public function recentArticles()
    {
        return HelpArticle::whereNull('org_id')
            ->with('category')
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function popularArticles()
    {
        return HelpArticle::whereNull('org_id')
            ->where('is_published', true)
            ->orderBy('view_count', 'desc')
            ->limit(5)
            ->get();
    }

    #[Computed]
    public function allArticles()
    {
        return HelpArticle::whereNull('org_id')
            ->with('category')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    #[Computed]
    public function categories()
    {
        return HelpCategory::whereNull('org_id')
            ->withCount('articles')
            ->orderBy('sort_order')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.help-admin-dashboard');
    }
}
