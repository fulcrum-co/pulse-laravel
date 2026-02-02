<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HelpArticle;
use App\Models\HelpCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HelpController extends Controller
{
    /**
     * Get featured articles for the help widget.
     */
    public function featuredArticles(Request $request): JsonResponse
    {
        $orgId = $request->user()?->org_id;

        $articles = HelpArticle::forOrganization($orgId)
            ->published()
            ->featured()
            ->with('category:id,name,slug')
            ->select('id', 'title', 'slug', 'excerpt', 'category_id')
            ->orderBy('view_count', 'desc')
            ->limit(5)
            ->get();

        // If no featured articles, get most viewed
        if ($articles->isEmpty()) {
            $articles = HelpArticle::forOrganization($orgId)
                ->published()
                ->with('category:id,name,slug')
                ->select('id', 'title', 'slug', 'excerpt', 'category_id')
                ->orderBy('view_count', 'desc')
                ->limit(5)
                ->get();
        }

        return response()->json([
            'articles' => $articles,
        ]);
    }

    /**
     * Get categories for the help widget.
     */
    public function categories(Request $request): JsonResponse
    {
        $orgId = $request->user()?->org_id;

        $categories = HelpCategory::forOrganization($orgId)
            ->active()
            ->whereNull('parent_id') // Top-level categories only
            ->select('id', 'name', 'slug', 'icon', 'description')
            ->orderBy('sort_order')
            ->limit(6)
            ->get();

        return response()->json([
            'categories' => $categories,
        ]);
    }

    /**
     * Search help articles.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        $orgId = $request->user()?->org_id;

        if (strlen($query) < 2) {
            return response()->json(['articles' => []]);
        }

        $articles = HelpArticle::forOrganization($orgId)
            ->published()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('excerpt', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%")
                    ->orWhereJsonContains('search_keywords', $query);
            })
            ->with('category:id,name,slug')
            ->select('id', 'title', 'slug', 'excerpt', 'category_id')
            ->orderBy('view_count', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'articles' => $articles,
        ]);
    }
}
