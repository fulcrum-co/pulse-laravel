<?php

namespace App\Livewire\Marketplace;

use App\Models\MarketplaceItem;
use App\Models\MarketplaceTransaction;
use App\Models\SellerProfile;
use Livewire\Component;

class SellerDashboard extends Component
{
    public SellerProfile $seller;

    public function mount()
    {
        $this->seller = SellerProfile::where('user_id', auth()->id())->firstOrFail();
    }

    /**
     * Get dashboard stats.
     */
    public function getStatsProperty(): array
    {
        return [
            'total_items' => $this->seller->items()->count(),
            'published_items' => $this->seller->items()->where('status', MarketplaceItem::STATUS_APPROVED)->count(),
            'pending_items' => $this->seller->items()->where('status', MarketplaceItem::STATUS_PENDING_REVIEW)->count(),
            'total_sales' => $this->seller->total_sales,
            'total_revenue' => $this->seller->lifetime_revenue,
            'ratings_average' => $this->seller->ratings_average,
            'ratings_count' => $this->seller->ratings_count,
        ];
    }

    /**
     * Get recent items.
     */
    public function getRecentItemsProperty()
    {
        return $this->seller->items()
            ->with('primaryPricing')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    /**
     * Get recent transactions.
     */
    public function getRecentTransactionsProperty()
    {
        return MarketplaceTransaction::where('seller_profile_id', $this->seller->id)
            ->with(['item', 'buyer'])
            ->completed()
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.marketplace.seller-dashboard', [
            'stats' => $this->stats,
            'recentItems' => $this->recentItems,
            'recentTransactions' => $this->recentTransactions,
        ])->layout('layouts.dashboard', ['title' => 'Seller Dashboard']);
    }
}
