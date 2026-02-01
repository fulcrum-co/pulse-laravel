<?php

namespace App\Livewire\Marketplace;

use App\Models\MarketplaceItem;
use Livewire\Component;
use Livewire\WithPagination;

class MarketplaceContent extends Component
{
    use WithPagination;

    public string $search = '';

    public string $priceFilter = '';

    public string $ratingFilter = '';

    public array $selectedGrades = [];

    public array $selectedTypes = [];

    public string $sortBy = 'popular';

    public string $viewMode = 'grid';

    protected $queryString = [
        'search' => ['except' => '', 'as' => 'q'],
        'priceFilter' => ['except' => '', 'as' => 'price'],
        'ratingFilter' => ['except' => '', 'as' => 'rating'],
        'selectedGrades' => ['except' => [], 'as' => 'grade'],
        'selectedTypes' => ['except' => [], 'as' => 'type'],
        'sortBy' => ['except' => 'popular', 'as' => 'sort'],
        'viewMode' => ['except' => 'grid', 'as' => 'view'],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function toggleGrade(string $grade): void
    {
        if (in_array($grade, $this->selectedGrades)) {
            $this->selectedGrades = array_values(array_diff($this->selectedGrades, [$grade]));
        } else {
            $this->selectedGrades[] = $grade;
        }
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

    public function clearFilters(): void
    {
        $this->search = '';
        $this->priceFilter = '';
        $this->ratingFilter = '';
        $this->selectedGrades = [];
        $this->selectedTypes = [];
        $this->resetPage();
    }

    public function getItemsProperty()
    {
        $query = MarketplaceItem::published()
            ->inCategory(MarketplaceItem::CATEGORY_CONTENT)
            ->with(['seller', 'primaryPricing']);

        // Search
        if ($this->search) {
            $query->search($this->search);
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

        // Grade filter
        if (count($this->selectedGrades) > 0) {
            $query->where(function ($q) {
                foreach ($this->selectedGrades as $grade) {
                    $q->orWhereJsonContains('target_grades', $grade);
                }
            });
        }

        // Content type filter
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
            'price_low' => $query->orderByRaw('COALESCE((SELECT price FROM marketplace_pricing WHERE marketplace_pricing.marketplace_item_id = marketplace_items.id AND is_active = true LIMIT 1), 0) ASC'),
            'price_high' => $query->orderByRaw('COALESCE((SELECT price FROM marketplace_pricing WHERE marketplace_pricing.marketplace_item_id = marketplace_items.id AND is_active = true LIMIT 1), 0) DESC'),
            'rating' => $query->orderByDesc('ratings_average'),
            default => $query->orderByDesc('purchase_count')->orderByDesc('ratings_count'),
        };

        return $query->paginate(12);
    }

    public function getContentTypesProperty(): array
    {
        return [
            'article' => 'Article',
            'video' => 'Video',
            'worksheet' => 'Worksheet',
            'activity' => 'Activity',
            'lesson_plan' => 'Lesson Plan',
            'presentation' => 'Presentation',
            'ebook' => 'eBook',
            'template' => 'Template',
        ];
    }

    public function getGradesProperty(): array
    {
        return [
            'K-2' => 'K-2',
            '3-5' => '3-5',
            '6-8' => '6-8',
            '9-12' => '9-12',
        ];
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return $this->search !== '' ||
            $this->priceFilter !== '' ||
            $this->ratingFilter !== '' ||
            count($this->selectedGrades) > 0 ||
            count($this->selectedTypes) > 0;
    }

    public function render()
    {
        return view('livewire.marketplace.marketplace-category', [
            'items' => $this->items,
            'category' => 'content',
            'categoryLabel' => 'Content',
            'categoryIcon' => 'document-text',
            'categoryColor' => 'emerald',
            'filterTypes' => $this->contentTypes,
            'filterTypeLabel' => 'Content Type',
        ])->layout('layouts.dashboard', ['title' => 'Marketplace - Content', 'hideHeader' => true]);
    }
}
