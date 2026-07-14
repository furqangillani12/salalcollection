<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Package;
use App\Models\Product;
use App\Services\Shop\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        $items   = $this->cart->items();
        $totals  = $this->cart->totals();
        $coupon  = $this->cart->activeCoupon();
        return view('shop.pages.cart', compact('items', 'totals', 'coupon'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'qty'        => 'required|numeric|min:0.01|max:9999',
            'size'       => 'nullable|string|max:50',
            'color'      => 'nullable|string|max:50',
        ]);

        $product = Product::findOrFail($data['product_id']);
        if (!$product->is_active || !$product->show_on_website) {
            return response()->json(['ok' => false, 'message' => 'Product unavailable'], 422);
        }

        $item = $this->cart->add($product, (float) $data['qty'], $data['size'] ?? null, $data['color'] ?? null);
        $totals = $this->cart->totals();

        return response()->json([
            'ok'         => true,
            'message'    => 'Added to your cart',
            'item'       => ['id' => $item->id, 'qty' => $item->qty],
            'cart_count' => $totals['count'],
            'subtotal'   => $totals['subtotal'],
        ]);
    }

    public function addPackage(Request $request)
    {
        $data = $request->validate(['package_id' => 'required|exists:packages,id']);
        $package = Package::with('items.product')->where('is_active', true)->findOrFail($data['package_id']);

        $added = $this->cart->addPackage($package);
        if ($added === 0) {
            return back()->with('shop_error', 'This package has no available items.');
        }

        return redirect()->route('shop.cart')->with('shop_success', "“{$package->name}” package added to your cart.");
    }

    public function update(Request $request, CartItem $item)
    {
        $data = $request->validate(['qty' => 'required|numeric|min:0.01|max:9999']);
        $this->cart->update($item, (float) $data['qty']);
        if ($request->wantsJson()) return $this->json();
        return back();
    }

    public function remove(Request $request, CartItem $item)
    {
        $this->cart->remove($item);
        if ($request->wantsJson()) return $this->json();
        return back();
    }

    public function json()
    {
        $items   = $this->cart->items()->map(fn ($i) => [
            'id'         => $i->id,
            'product_id' => $i->product_id,
            'name'       => $i->product?->name ?? 'Product',
            'image'      => shop_image($i->product?->image),
            'qty'        => (float) $i->qty,
            'unit_price' => (float) $i->unit_price,
            'size'       => $i->selected_size,
            'color'      => $i->selected_color,
        ]);
        $totals = $this->cart->totals();
        return response()->json(array_merge(['items' => $items], $totals));
    }

    public function applyCoupon(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|max:50']);
        $coupon = $this->cart->applyCoupon($data['code']);
        if (!$coupon) return back()->with('shop_error', 'Coupon not valid for this order.');
        return back()->with('shop_success', "Coupon \"{$coupon->code}\" applied.");
    }

    public function removeCoupon()
    {
        $this->cart->removeCoupon();
        return back();
    }
}
