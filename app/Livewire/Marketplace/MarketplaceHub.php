<?php

namespace App\Livewire\Marketplace;

use App\Models\MarketplaceItem;
use App\Models\SellerProfile;
use Livewire\Component;

class MarketplaceHub extends Component
{
    public string $search = '';
    public bool $isSearching = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatedSearch(): void
    {
        $this->isSearching = strlen($this->search) >= 2;
    }

    public function clearSearch(): void
    {
        $this->search = '';
        $this->isSearching = false;
    }

    /**
     * Get counts for each category card.
     */
    public function getCountsProperty(): array
    {
        return [
            'surveys' => MarketplaceItem::published()->inCategory(MarketplaceItem::CATEGORY_SURVEY)->count(),
            'strategies' => MarketplaceItem::published()->inCategory(MarketplaceItem::CATEGORY_STRATEGY)->count(),
            'content' => MarketplaceItem::published()->inCategory(MarketplaceItem::CATEGORY_CONTENT)->count(),
            'providers' => MarketplaceItem::published()->inCategory(MarketplaceItem::CATEGORY_PROVIDER)->count(),
        ];
    }

    /**
     * Get featured items.
     */
    public function getFeaturedItemsProperty()
    {
        return MarketplaceItem::published()
            ->featured()
            ->with(['seller', 'primaryPricing'])
            ->orderByDesc('published_at')
            ->limit(4)
            ->get();
    }

    /**
     * Get recently added items.
     */
    public function getRecentItemsProperty()
    {
        return MarketplaceItem::published()
            ->with(['seller', 'primaryPricing'])
            ->orderByDesc('published_at')
            ->limit(8)
            ->get();
    }

    /**
     * Check if current user has a seller profile.
     */
    public function getHasSellerProfileProperty(): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }

        return SellerProfile::where('user_id', $user->id)->exists();
    }

    /**
     * Get search results grouped by category.
     */
    public function getSearchResultsProperty(): array
    {
        if (!$this->isSearching) {
            return [];
        }

        $searchTerm = '%' . $this->search . '%';

        $categories = [
            'surveys' => MarketplaceItem::CATEGORY_SURVEY,
            'strategies' => MarketplaceItem::CATEGORY_STRATEGY,
            'content' => MarketplaceItem::CATEGORY_CONTENT,
            'providers' => MarketplaceItem::CATEGORY_PROVIDER,
        ];

        $results = [];

        foreach ($categories as $key => $category) {
            $query = MarketplaceItem::published()
                ->inCategory($category)
                ->with(['seller', 'primaryPricing'])
                ->where(function ($q) use ($searchTerm) {
                    $q->where('title', 'ilike', $searchTerm)
                        ->orWhere('description', 'ilike', $searchTerm)
                        ->orWhere('short_description', 'ilike', $searchTerm);
                });

            $items = $query->limit(4)->get();
            $total = $query->count();

            $results[$key] = [
                'items' => $items,
                'count' => $items->count(),
                'total' => $total,
            ];
        }

        return $results;
    }

    /**
     * Get the icon for a category.
     */
    public function getCategoryIcon(string $category): string
    {
        return match ($category) {
            MarketplaceItem::CATEGORY_SURVEY => 'clipboard-document-list',
            MarketplaceItem::CATEGORY_STRATEGY => 'light-bulb',
            MarketplaceItem::CATEGORY_CONTENT => 'document-text',
            MarketplaceItem::CATEGORY_PROVIDER => 'users',
            default => 'squares-2x2',
        };
    }

    /**
     * Get the color for a category.
     */
    public function getCategoryColor(string $category): array
    {
        return match ($category) {
            MarketplaceItem::CATEGORY_SURVEY => ['bg' => 'bg-blue-100', 'text' => 'text-blue-600'],
            MarketplaceItem::CATEGORY_STRATEGY => ['bg' => 'bg-amber-100', 'text' => 'text-amber-600'],
            MarketplaceItem::CATEGORY_CONTENT => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-600'],
            MarketplaceItem::CATEGORY_PROVIDER => ['bg' => 'bg-purple-100', 'text' => 'text-purple-600'],
            default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-600'],
        };
    }

    public function render()
    {
        return view('livewire.marketplace.marketplace-hub', [
            'counts' => $this->counts,
            'featuredItems' => $this->featuredItems,
            'recentItems' => $this->recentItems,
            'searchResults' => $this->searchResults,
            'hasSellerProfile' => $this->hasSellerProfile,
        ])->layout('layouts.dashboard', ['title' => 'Marketplace']);
    }
}
