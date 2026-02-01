<?php

namespace App\Livewire\Marketplace;

use App\Models\MarketplaceItem;
use App\Models\MarketplacePurchase;
use App\Models\MarketplaceReview;
use Livewire\Component;
use Livewire\WithPagination;

class MarketplaceItemDetail extends Component
{
    use WithPagination;

    public MarketplaceItem $item;

    public string $activeTab = 'description';

    public function mount(string $uuid)
    {
        $this->item = MarketplaceItem::where('uuid', $uuid)
            ->published()
            ->with(['seller', 'pricing', 'primaryPricing'])
            ->firstOrFail();

        // Record view
        $this->item->recordView();
    }

    /**
     * Check if user owns this item.
     */
    public function getHasAccessProperty(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return MarketplacePurchase::where('user_id', $user->id)
            ->where('marketplace_item_id', $this->item->id)
            ->withAccess()
            ->exists();
    }

    /**
     * Get reviews for this item.
     */
    public function getReviewsProperty()
    {
        return MarketplaceReview::where('marketplace_item_id', $this->item->id)
            ->published()
            ->with('user')
            ->orderByDesc('helpful_count')
            ->orderByDesc('created_at')
            ->paginate(5);
    }

    /**
     * Get related items.
     */
    public function getRelatedItemsProperty()
    {
        return MarketplaceItem::published()
            ->where('id', '!=', $this->item->id)
            ->where('category', $this->item->category)
            ->with(['seller', 'primaryPricing'])
            ->orderByDesc('ratings_average')
            ->limit(4)
            ->get();
    }

    /**
     * Get category route name.
     */
    public function getCategoryRouteProperty(): string
    {
        return match ($this->item->category) {
            'survey' => 'marketplace.surveys',
            'strategy' => 'marketplace.strategies',
            'content' => 'marketplace.content',
            'provider' => 'marketplace.providers',
            default => 'marketplace.index',
        };
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelProperty(): string
    {
        return match ($this->item->category) {
            'survey' => 'Surveys',
            'strategy' => 'Strategies',
            'content' => 'Content',
            'provider' => 'Providers',
            default => 'Marketplace',
        };
    }

    /**
     * Get category icon.
     */
    public function getCategoryIconProperty(): string
    {
        return match ($this->item->category) {
            'survey' => 'clipboard-document-list',
            'strategy' => 'light-bulb',
            'content' => 'document-text',
            'provider' => 'users',
            default => 'squares-2x2',
        };
    }

    /**
     * Get category color.
     */
    public function getCategoryColorProperty(): string
    {
        return match ($this->item->category) {
            'survey' => 'blue',
            'strategy' => 'amber',
            'content' => 'emerald',
            'provider' => 'purple',
            default => 'gray',
        };
    }

    public function render()
    {
        return view('livewire.marketplace.marketplace-item-detail', [
            'reviews' => $this->reviews,
            'relatedItems' => $this->relatedItems,
            'hasAccess' => $this->hasAccess,
        ])->layout('layouts.dashboard', ['title' => $this->item->title, 'hideHeader' => true]);
    }
}
