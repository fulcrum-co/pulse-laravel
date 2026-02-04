<?php

namespace App\Services;

use App\Models\PendingExtraction;
use App\Models\Resource;
use Illuminate\Support\Collection;

class ResourceRecommendationService
{
    public function getSuggestions(PendingExtraction $extraction): Collection
    {
        $data = $extraction->extracted_data ?? [];
        $keywords = collect($data)->flatten()->filter()->values()->all();

        if (empty($keywords)) {
            return collect();
        }

        return Resource::query()
            ->active()
            ->where('org_id', $extraction->contact->org_id)
            ->where(function ($query) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $query->orWhereJsonContains('domain_tags', $keyword);
                }
            })
            ->limit(3)
            ->get();
    }
}
