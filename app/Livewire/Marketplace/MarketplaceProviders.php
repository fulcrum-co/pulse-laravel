<?php

namespace App\Livewire\Marketplace;

use App\Models\MarketplaceItem;
use Livewire\Component;
use Livewire\WithPagination;

class MarketplaceProviders extends Component
{
    use WithPagination;

    public string $search = '';

    public string $priceFilter = '';

    public string $ratingFilter = '';

    public array $selectedTypes = [];

    public bool $verifiedOnly = false;

    public string $sortBy = 'popular';

    public string $viewMode = 'list'; // Providers default to list view

    protected $queryString = [
        'search' => ['except' => '', 'as' => 'q'],
        'priceFilter' => ['except' => '', 'as' => 'price'],
        'ratingFilter' => ['except' => '', 'as' => 'rating'],
        'selectedTypes' => ['except' => [], 'as' => 'type'],
        'verifiedOnly' => ['except' => false, 'as' => 'verified'],
        'sortBy' => ['except' => 'popular', 'as' => 'sort'],
        'viewMode' => ['except' => 'list', 'as' => 'view'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleType(string $type): void
    {
        if (in_array($type, $this->selectedTypes)) {
            $this->selectedTypes = array_values(array_diff($this->selectedTypes, [$type]));
        } else {
            $this->selectedTypes[] = $type;
        }
        $this->resetPage();
    }

    public function toggleVerified(): void
    {
        $this->verifiedOnly = ! $this->verifiedOnly;
        $this->resetPage();
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->priceFilter = '';
        $this->ratingFilter = '';
        $this->selectedTypes = [];
        $this->verifiedOnly = false;
        $this->resetPage();
    }

    public function getItemsProperty()
    {
        $query = MarketplaceItem::published()
            ->inCategory(MarketplaceItem::CATEGORY_PROVIDER)
            ->with(['seller', 'primaryPricing']);

        // Search
        if ($this->search) {
            $query->search($this->search);
        }

        // Verified only
        if ($this->verifiedOnly) {
            $query->where('is_verified', true);
        }

        // Price filter
        if ($this->priceFilter === 'free') {
            $query->free();
        } elseif ($this->priceFilter === 'paid') {
            $query->where('pricing_type', '!=', MarketplaceItem::PRICING_FREE);
        }

        // Rating filter
        if ($this->ratingFilter === '4plus') {
            $query->minRating(4.0);
        } elseif ($this->ratingFilter === '3plus') {
            $query->minRating(3.0);
        }

        // Provider type filter
        if (count($this->selectedTypes) > 0) {
            $query->where(function ($q) {
                foreach ($this->selectedTypes as $type) {
                    $q->orWhereJsonContains('subcategories', $type);
                }
            });
        }

        // Sorting
        $query = match ($this->sortBy) {
            'newest' => $query->orderByDesc('published_at'),
            'rating' => $query->orderByDesc('ratings_average'),
            default => $query->orderByDesc('ratings_count')->orderByDesc('ratings_average'),
        };

        return $query->paginate(12);
    }

    public function getProviderTypesProperty(): array
    {
        return [
            'therapist' => 'Therapist',
            'counselor' => 'Counselor',
            'tutor' => 'Tutor',
            'coach' => 'Coach',
            'specialist' => 'Specialist',
            'consultant' => 'Consultant',
        ];
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return $this->search !== '' ||
            $this->priceFilter !== '' ||
            $this->ratingFilter !== '' ||
            count($this->selectedTypes) > 0 ||
            $this->verifiedOnly;
    }

    public function render()
    {
        return view('livewire.marketplace.marketplace-providers', [
            'items' => $this->items,
        ])->layout('layouts.dashboard', ['title' => 'Marketplace - Providers', 'hideHeader' => true]);
    }
}
