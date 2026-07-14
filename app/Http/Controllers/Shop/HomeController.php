<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $heroBanners = Banner::active()->position('hero')->orderBy('sort_order')->get();
        $midBanners  = Banner::active()->position('mid')->orderBy('sort_order')->limit(10)->get();

        $featuredCategories = Category::onWebsite()->where('is_featured', true)
            ->orderBy('sort_order')->orderBy('name')->limit(12)->get();

        // Fall back to any website categories when none are flagged "featured",
        // so the "Shop by category" section is always populated.
        if ($featuredCategories->isEmpty()) {
            $featuredCategories = Category::onWebsite()
                ->orderBy('sort_order')->orderBy('name')->limit(12)->get();
        }

        // New arrivals first — ordered by updated_at so a freshly ADDED product
        // *or* any product the admin edits/updates bubbles back to the top (stock
        // changes go to a separate table, so selling a product doesn't affect this).
        // Its ids are excluded from the fallback sections below so the three
        // product rows never show the same items.
        $newArrivals = Product::onWebsite()
            ->with('category', 'brand')
            ->orderByDesc('updated_at')->limit(8)->get();
        $usedIds = $newArrivals->pluck('id')->all();

        // Featured / best picks — curated when flagged, else a distinct set.
        $featuredProducts = Product::onWebsite()->featured()
            ->with('category', 'brand')
            ->orderByDesc('id')->limit(8)->get();
        if ($featuredProducts->isEmpty()) {
            $featuredProducts = Product::onWebsite()
                ->whereNotIn('id', $usedIds)
                ->with('category', 'brand')
                ->inRandomOrder()->limit(8)->get();
        }
        $usedIds = array_merge($usedIds, $featuredProducts->pluck('id')->all());

        // Top rated — real ratings when present, else a distinct best-available set.
        $bestRated = Product::onWebsite()
            ->where('avg_rating', '>=', 4)
            ->with('category', 'brand')
            ->orderByDesc('avg_rating')->orderByDesc('review_count')->limit(8)->get();
        if ($bestRated->isEmpty()) {
            $bestRated = Product::onWebsite()
                ->whereNotIn('id', $usedIds)
                ->with('category', 'brand')
                ->orderByDesc('review_count')->orderByDesc('id')
                ->limit(8)->get();
        }

        $brands = Brand::where('is_active', true)->where('is_featured', true)
            ->orderBy('sort_order')->limit(8)->get();

        if ($brands->isEmpty()) {
            $brands = Brand::where('is_active', true)->orderBy('sort_order')->orderBy('name')->limit(8)->get();
        }

        // ── Behaviour-based rows (#12) ────────────────────────────────────
        // Recently viewed (in the order the visitor opened them).
        $recentIds = session('shop.recent_products', []);
        $recentlyViewed = collect();
        if (!empty($recentIds)) {
            $recentlyViewed = Product::onWebsite()->whereIn('id', $recentIds)
                ->with('category', 'brand')->get()
                ->sortBy(fn ($p) => array_search($p->id, $recentIds))->values();
        }

        // "Recommended for you" — from the category the visitor opens most,
        // excluding what they've just seen. Falls back to popular products so the
        // section always has something to show.
        $catInterest = session('shop.cat_interest', []);
        $topCat = !empty($catInterest) ? array_key_first($catInterest) : null;
        $recommended = collect();
        if ($topCat) {
            $recommended = Product::onWebsite()->where('category_id', $topCat)
                ->whereNotIn('id', $recentIds ?: [0])
                ->with('category', 'brand')->popular()->limit(8)->get();
        }
        if ($recommended->isEmpty()) {
            $recommended = Product::onWebsite()
                ->whereNotIn('id', $recentIds ?: [0])
                ->with('category', 'brand')->popular()->limit(8)->get();
        }

        return view('shop.pages.home', compact(
            'heroBanners', 'midBanners', 'featuredCategories',
            'featuredProducts', 'newArrivals', 'bestRated', 'brands',
            'recentlyViewed', 'recommended'
        ));
    }
}
