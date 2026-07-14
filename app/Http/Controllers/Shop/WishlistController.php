<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $items = Wishlist::with('product.category', 'product.brand')
            ->where('customer_id', Auth::guard('customer')->id())
            ->latest()->get();
        return view('shop.pages.wishlist', compact('items'));
    }

    public function toggle(Product $product)
    {
        $customerId = Auth::guard('customer')->id();
        $existing = Wishlist::where('customer_id', $customerId)
            ->where('product_id', $product->id)->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['ok' => true, 'in_wishlist' => false]);
        }
        Wishlist::create(['customer_id' => $customerId, 'product_id' => $product->id]);
        return response()->json(['ok' => true, 'in_wishlist' => true]);
    }

    public function remove(Wishlist $wishlist, Request $request)
    {
        abort_unless((int) $wishlist->customer_id === (int) Auth::guard('customer')->id(), 403);
        $wishlist->delete();
        if ($request->wantsJson()) return response()->json(['ok' => true]);
        return back()->with('shop_success', 'Removed from wishlist.');
    }
}
