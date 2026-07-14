<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function index(Request $request)
    {
        return $this->render($request, null, null, null);
    }

    public function category(Request $request, Category $category)
    {
        return $this->render($request, $category, null, null);
    }

    public function brand(Request $request, Brand $brand)
    {
        return $this->render($request, null, $brand, null);
    }

    public function search(Request $request)
    {
        return $this->render($request, null, null, $request->input('q'));
    }

    private function render(Request $request, ?Category $category, ?Brand $brand, ?string $q)
    {
        $query = Product::onWebsite()->with('category', 'brand');

        if ($category) {
            $catIds = collect([$category->id])
                ->merge(Category::where('parent_id', $category->id)->pluck('id'))
                ->all();
            $query->whereIn('category_id', $catIds);
        }
        if ($brand) {
            $query->where('brand_id', $brand->id);
        }
        if ($q) {
            $query->where(function ($w) use ($q) {
                $w->where('name', 'like', "%{$q}%")
                  ->orWhere('summary', 'like', "%{$q}%")
                  ->orWhere('barcode', 'like', "%{$q}%");
            });
        }
        if ($brandId = $request->input('brand_id')) {
            $query->where('brand_id', $brandId);
        }
        if ($min = $request->input('price_min')) {
            $query->where('sale_price', '>=', (float) $min);
        }
        if ($max = $request->input('price_max')) {
            $query->where('sale_price', '<=', (float) $max);
        }

        $sort = $request->input('sort', 'newest');
        match ($sort) {
            'price_asc'   => $query->orderBy('sale_price', 'asc'),
            'price_desc'  => $query->orderBy('sale_price', 'desc'),
            'rating'      => $query->orderByDesc('avg_rating')->orderByDesc('review_count'),
            'popular'     => $query->orderByDesc('views')->orderByDesc('review_count'),
            'name'        => $query->orderBy('name'),
            default       => $query->orderByDesc('id'),
        };

        $products = $query->paginate(24)->withQueryString();

        $allCategories = Category::onWebsite()->whereNull('parent_id')->orderBy('sort_order')->get();
        $allBrands     = Brand::where('is_active', true)->orderBy('name')->get();

        return view('shop.pages.catalog', compact('products', 'category', 'brand', 'q', 'allCategories', 'allBrands', 'sort'));
    }
}
