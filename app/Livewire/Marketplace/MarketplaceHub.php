<?php

namespace App\Livewire\Marketplace;

use App\Models\MarketplaceItem;
use App\Models\SellerProfile;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class MarketplaceHub extends Component
{
    public string $search = '';

    public bool $isSearching = false;

    public array $selectedCategories = [];

    public string $priceFilter = '';

    public string $ratingFilter = '';

    public string $sortBy = 'popular';

    protected $queryString = [
        'search' => ['except' => '', 'as' => 'q'],
        'selectedCategories' => ['except' => [], 'as' => 'category'],
        'priceFilter' => ['except' => '', 'as' => 'price'],
        'ratingFilter' => ['except' => '', 'as' => 'rating'],
        'sortBy' => ['except' => 'popular', 'as' => 'sort'],
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

    public function toggleCategory(string $category): void
    {
        if (in_array($category, $this->selectedCategories)) {
            $this->selectedCategories = array_values(array_diff($this->selectedCategories, [$category]));
        } else {
            $this->selectedCategories[] = $category;
        }
    }

    public function clearFilters(): void
    {
        $this->selectedCategories = [];
        $this->priceFilter = '';
        $this->ratingFilter = '';
        $this->sortBy = 'popular';
    }

    public function selectAllCategories(): void
    {
        $this->selectedCategories = [
            MarketplaceItem::CATEGORY_SURVEY,
            MarketplaceItem::CATEGORY_STRATEGY,
            MarketplaceItem::CATEGORY_CONTENT,
            MarketplaceItem::CATEGORY_PROVIDER,
        ];
    }

    public function clearCategories(): void
    {
        $this->selectedCategories = [];
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return count($this->selectedCategories) > 0
            || $this->priceFilter !== ''
            || $this->ratingFilter !== ''
            || $this->sortBy !== 'popular';
    }

    /**
     * Get counts for each category card.
     */
    public function getCountsProperty(): array
    {
        try {
            if (! Schema::hasTable('marketplace_items')) {
                return ['surveys' => 0, 'strategies' => 0, 'content' => 0, 'providers' => 0];
            }

            return [
                'surveys' => MarketplaceItem::published()->inCategory(MarketplaceItem::CATEGORY_SURVEY)->count(),
                'strategies' => MarketplaceItem::published()->inCategory(MarketplaceItem::CATEGORY_STRATEGY)->count(),
                'content' => MarketplaceItem::published()->inCategory(MarketplaceItem::CATEGORY_CONTENT)->count(),
                'providers' => MarketplaceItem::published()->inCategory(MarketplaceItem::CATEGORY_PROVIDER)->count(),
            ];
        } catch (\Exception $e) {
            return ['surveys' => 0, 'strategies' => 0, 'content' => 0, 'providers' => 0];
        }
    }

    /**
     * Get featured items (with filters applied).
     */
    public function getFeaturedItemsProperty()
    {
        try {
            if (! Schema::hasTable('marketplace_items')) {
                return collect();
            }

            $query = MarketplaceItem::published()
                ->featured()
                ->with(['seller', 'primaryPricing']);

            // Apply category filter
            if (count($this->selectedCategories) > 0) {
                $query->whereIn('category', $this->selectedCategories);
            }

            // Apply price filter
            if ($this->priceFilter === 'free') {
                $query->free();
            } elseif ($this->priceFilter === 'paid') {
                $query->where('pricing_type', '!=', MarketplaceItem::PRICING_FREE);
            }

            // Apply rating filter
            if ($this->ratingFilter === '4plus') {
                $query->minRating(4.0);
            } elseif ($this->ratingFilter === '3plus') {
                $query->minRating(3.0);
            }

            return $query->orderByDesc('published_at')
                ->limit(4)
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Get recently added items (with filters applied).
     */
    public function getRecentItemsProperty()
    {
        try {
            if (! Schema::hasTable('marketplace_items')) {
                return collect();
            }

            $query = MarketplaceItem::published()
                ->with(['seller', 'primaryPricing']);

            // Apply category filter
            if (count($this->selectedCategories) > 0) {
                $query->whereIn('category', $this->selectedCategories);
            }

            // Apply price filter
            if ($this->priceFilter === 'free') {
                $query->free();
            } elseif ($this->priceFilter === 'paid') {
                $query->where('pricing_type', '!=', MarketplaceItem::PRICING_FREE);
            }

            // Apply rating filter
            if ($this->ratingFilter === '4plus') {
                $query->minRating(4.0);
            } elseif ($this->ratingFilter === '3plus') {
                $query->minRating(3.0);
            }

            // Apply sorting
            if ($this->sortBy === 'newest') {
                $query->orderByDesc('published_at');
            } elseif ($this->sortBy === 'rating') {
                $query->orderByDesc('ratings_average');
            } else {
                // popular - order by downloads/purchases count
                $query->orderByDesc('downloads_count');
            }

            return $query->limit(12)->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    /**
     * Check if current user has a seller profile.
     */
    public function getHasSellerProfileProperty(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        try {
            if (! Schema::hasTable('seller_profiles')) {
                return false;
            }

            return SellerProfile::where('user_id', $user->id)->exists();
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get search results grouped by category.
     */
    public function getSearchResultsProperty(): array
    {
        if (! $this->isSearching) {
            return [];
        }

        try {
            if (! Schema::hasTable('marketplace_items')) {
                return [];
            }

            $searchTerm = '%'.$this->search.'%';

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
        } catch (\Exception $e) {
            return [];
        }
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
            'hasActiveFilters' => $this->hasActiveFilters,
        ])->layout('layouts.dashboard', ['title' => 'Marketplace']);
    }
}
