<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\DispatchMethod;
use App\Models\DeliveryChargeSlab;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Services\Shop\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    public function __construct(private CartService $cart) {}

    public function index()
    {
        $items = $this->cart->items();
        if ($items->isEmpty()) return redirect()->route('shop.cart')->with('shop_error', 'Your cart is empty.');

        $totals = $this->cart->totals();
        $coupon = $this->cart->activeCoupon();
        $customer = Auth::guard('customer')->user();
        $isGuest = !$customer;

        // Only methods the admin has chosen to show on the website.
        $dispatchMethods = DispatchMethod::onWebsite()->get();
        $paymentMethods  = PaymentMethod::onWebsite()->get();

        $weight = $items->sum(fn ($i) => (float) ($i->product?->weight ?? 0) * (float) $i->qty);

        // Live delivery charge per dispatch method for the current cart weight,
        // so the form can show "Rs. X" the moment a method is selected.
        $deliveryCharges = [];
        foreach ($dispatchMethods as $dm) {
            $deliveryCharges[$dm->name] = $this->resolveDelivery($dm->name, $weight);
        }

        $provinces = config('pk_geo.provinces', []);

        // Points redemption (#B): how many points the logged-in customer holds
        // and what one is worth, so the form can offer "use my points".
        $pointsBalance = (int) ($customer->loyalty_points ?? 0);
        $pointValue    = shop_point_value();
        $afterCoupon   = max(0, $totals['subtotal'] - $totals['discount']);
        $maxRedeemable = $customer ? shop_max_redeemable_points($pointsBalance, $afterCoupon) : 0;

        return view('shop.pages.checkout', compact(
            'items', 'totals', 'coupon', 'customer', 'isGuest',
            'dispatchMethods', 'paymentMethods', 'deliveryCharges', 'weight', 'provinces',
            'pointsBalance', 'pointValue', 'maxRedeemable'
        ));
    }

    /**
     * Look up a previously used shipping address by phone, so returning buyers
     * can auto-fill the form. Returns the most recent matching online order.
     */
    public function lookup(Request $request)
    {
        $phone  = preg_replace('/\D+/', '', (string) $request->input('phone'));
        if (strlen($phone) < 7) return response()->json(['ok' => false]);

        $last10 = substr($phone, -10);
        $order = Order::where('order_source', 'online')
            ->whereNotNull('shipping_address1')
            ->whereRaw("RIGHT(REPLACE(REPLACE(REPLACE(shipping_phone,'+',''),'-',''),' ',''), 10) = ?", [$last10])
            ->latest('id')
            ->first();

        if (!$order) return response()->json(['ok' => false]);

        return response()->json([
            'ok'      => true,
            'address' => [
                'shipping_first_name' => $order->shipping_first_name,
                'shipping_last_name'  => $order->shipping_last_name,
                'shipping_address1'   => $order->shipping_address1,
                'shipping_address2'   => $order->shipping_address2,
                'shipping_city'       => $order->shipping_city,
                'shipping_tehsil'     => $order->shipping_tehsil,
                'shipping_district'   => $order->shipping_district,
                'shipping_province'   => $order->shipping_province,
                'shipping_country'    => $order->shipping_country,
                'shipping_post_code'  => $order->shipping_post_code,
            ],
        ]);
    }

    public function place(Request $request)
    {
        $isGuest = !Auth::guard('customer')->check();

        $dispatchNames = DispatchMethod::onWebsite()->pluck('name')->all();
        $paymentNames  = PaymentMethod::onWebsite()->pluck('name')->all();

        $data = $request->validate([
            'shipping_first_name'  => 'required|string|max:191',
            'shipping_last_name'   => 'nullable|string|max:191',
            'shipping_phone'       => 'required|string|max:30',
            'shipping_address1'    => 'required|string|max:500',
            'shipping_address2'    => 'nullable|string|max:500',
            'shipping_city'        => 'nullable|string|max:100',
            'shipping_tehsil'      => 'nullable|string|max:100',
            'shipping_district'    => 'nullable|string|max:100',
            'shipping_province'    => 'nullable|string|max:100',
            'shipping_country'     => 'required|string|max:100',
            'shipping_post_code'   => 'nullable|string|max:20',
            'dispatch_method'      => ['nullable', 'string', 'max:100'],
            'payment_method'       => ['required', 'string', 'max:100', Rule::in($paymentNames)],
            'order_notes_customer' => 'nullable|string|max:1000',
            'email'                => 'required|email|max:191',
            'redeem_points'        => 'nullable|integer|min:0',
            // Optional bank-transfer proof submitted at checkout.
            'payment_sender_name'  => 'nullable|string|max:191',
            'payment_sender_bank'  => 'nullable|string|max:191',
            'payment_sender_amount'=> 'nullable|numeric|min:0',
            'payment_proof'        => 'nullable|image|mimes:png,jpg,jpeg,webp|max:4096',
            // Optional reseller "From" address printed on the dispatch slip.
            'from_name'            => 'nullable|string|max:191',
            'from_phone'           => 'nullable|string|max:30',
            'from_address'         => 'nullable|string|max:500',
        ]);

        $items = $this->cart->items();
        if ($items->isEmpty()) return redirect()->route('shop.cart')->with('shop_error', 'Your cart is empty.');

        $customer = Auth::guard('customer')->user();
        $totals   = $this->cart->totals();
        $coupon   = $this->cart->activeCoupon();
        $weight   = $items->sum(fn ($i) => (float) ($i->product?->weight ?? 0) * (float) $i->qty);
        // Delivery method isn't chosen by the customer here — record a sensible
        // default (first available method, else "Delivery") so the order/slip work.
        $data['dispatch_method'] = $data['dispatch_method']
            ?: (DispatchMethod::onWebsite()->value('name') ?: DispatchMethod::query()->value('name') ?: 'Delivery');
        $delivery = $this->resolveDelivery($data['dispatch_method'], $weight);

        // Points redemption (#B) — logged-in customers only, capped so the points
        // discount can never exceed the after-coupon subtotal. Applied like a
        // discount, so (as in the POS) it also lowers the taxable base.
        $afterCoupon    = max(0, $totals['subtotal'] - $totals['discount']);
        $redeemPoints   = 0;
        $pointsDiscount = 0.0;
        if ($customer && shop_point_value() > 0) {
            $requested      = (int) $request->input('redeem_points', 0);
            $maxRedeemable  = shop_max_redeemable_points((int) ($customer->loyalty_points ?? 0), $afterCoupon);
            $redeemPoints   = max(0, min($requested, $maxRedeemable));
            $pointsDiscount = shop_points_to_rupees($redeemPoints);
        }

        // Tax computed the SAME way as the POS receipt: exclusive, on
        // (subtotal − discount + delivery). Driven by the storefront tax setting.
        $afterDiscount = max(0, $afterCoupon - $pointsDiscount);
        $taxableBase   = $afterDiscount + $delivery;
        $tax           = shop_tax_amount($taxableBase);
        $grandTotal    = max(0, $afterDiscount + $tax + $delivery);

        $paymentModel = PaymentMethod::where('name', $data['payment_method'])->first();
        $isCod = (bool) ($paymentModel?->is_cod);

        // Optional payment screenshot.
        $proofPath = null;
        if ($request->hasFile('payment_proof')) {
            $proofPath = $request->file('payment_proof')->store('payment-proofs', 'public');
        }
        // Empty numeric inputs arrive as '' — normalise to null for the decimal column.
        $data['payment_sender_amount'] = ($data['payment_sender_amount'] ?? '') === '' ? null : $data['payment_sender_amount'];

        $hasProof = $proofPath || !empty($data['payment_sender_name']) || $data['payment_sender_amount'] !== null;

        $order = DB::transaction(function () use ($items, $customer, $isGuest, $data, $totals, $coupon, $delivery, $grandTotal, $weight, $tax, $isCod, $proofPath, $hasProof, $redeemPoints, $pointsDiscount) {

            $branchId = $items->first()->branch_id ?? \App\Models\Branch::query()->value('id');

            $order = Order::create([
                'order_number'     => Order::generateOrderNumber($branchId),
                'order_source'     => 'online',
                'order_type'       => 'online',
                'customer_id'      => $customer?->id,
                'customer_email'   => $data['email'],
                'customer_type'    => $customer?->customer_type ?? 'customer',
                'user_id'          => null,
                'branch_id'        => $branchId,
                'subtotal'         => $totals['subtotal'],
                'discount'         => $totals['discount'],
                'coupon_code'      => $coupon?->code,
                'coupon_discount'  => $totals['discount'],
                'points_redeemed'  => $redeemPoints,
                'points_discount'  => $pointsDiscount,
                'tax'              => $tax,
                'tax_rate'         => $totals['tax_rate'],
                'tax_type'         => $totals['tax_type'],
                'delivery_charges' => $delivery,
                'weight'           => $weight,
                'total'            => $grandTotal,
                'paid_amount'      => 0,
                'previous_balance' => $customer ? (float) ($customer->current_balance ?? 0) : 0,
                'balance_amount'   => $grandTotal,
                'payment_method'   => $data['payment_method'],
                'payment_status'   => 'unpaid',
                'online_payment_status' => $isCod ? 'cod' : ($hasProof ? 'proof_submitted' : 'bank_pending'),
                'status'           => 'pending',
                'dispatch_method'  => $data['dispatch_method'],
                'shipping_first_name' => $data['shipping_first_name'],
                'shipping_last_name'  => $data['shipping_last_name'] ?? null,
                'shipping_phone'      => $data['shipping_phone'],
                'shipping_address1'   => $data['shipping_address1'],
                'shipping_address2'   => $data['shipping_address2'] ?? null,
                'shipping_city'       => $data['shipping_city'] ?? null,
                'shipping_tehsil'     => $data['shipping_tehsil'] ?? null,
                'shipping_district'   => $data['shipping_district'] ?? null,
                'shipping_province'   => $data['shipping_province'] ?? null,
                'shipping_country'    => $data['shipping_country'] ?: 'Pakistan',
                'shipping_post_code'  => $data['shipping_post_code'] ?? null,
                'payment_proof_path'     => $proofPath,
                'payment_sender_name'    => $data['payment_sender_name'] ?? null,
                'payment_sender_bank'    => $data['payment_sender_bank'] ?? null,
                'payment_sender_amount'  => $data['payment_sender_amount'] ?? null,
                'from_name'    => $data['from_name'] ?? null,
                'from_phone'   => $data['from_phone'] ?? null,
                'from_address' => $data['from_address'] ?? null,
                'order_notes_customer'=> $data['order_notes_customer'] ?? null,
                'receipt_token'       => bin2hex(random_bytes(16)),
            ]);

            foreach ($items as $row) {
                $product = $row->product;
                if (!$product) continue;
                OrderItem::create([
                    'order_id'    => $order->id,
                    'product_id'  => $product->id,
                    'quantity'    => $row->qty,
                    'unit_price'  => $row->unit_price,
                    'total_price' => round((float) $row->qty * (float) $row->unit_price, 2),
                ]);
                if ($product->track_inventory && $branchId) {
                    $product->decrementBranchStock($branchId, (float) $row->qty);
                }
            }

            if ($customer) {
                $customer->update([
                    'current_balance' => round((float) ($customer->current_balance ?? 0) + $grandTotal, 2),
                ]);

                // Deduct redeemed points (#B) and log the transaction.
                if ($redeemPoints > 0) {
                    $customer->awardPoints(-$redeemPoints, 'redeem_order', "Redeemed on order {$order->order_number}", $order->id);
                }
            }

            $order->recordStatus('pending', 'Order placed');

            $this->cart->clear();
            Session::put('shop.last_guest_order_token', $order->receipt_token);

            return $order;
        });

        // Order-received confirmation email (safe no-op if no email / mail fails).
        \App\Mail\OrderStatusMail::dispatchFor($order, 'placed');
        // Heads-up alert to the store team so they can action it quickly (#3).
        \App\Mail\NewOrderAdminMail::dispatchFor($order);

        return redirect()->route('shop.checkout.thanks', $order)->with('shop_success', 'Order placed!');
    }

    public function thankYou(Order $order)
    {
        $authorised = (Auth::guard('customer')->check() && (int) $order->customer_id === (int) Auth::guard('customer')->id())
                   || ($order->receipt_token && Session::get('shop.last_guest_order_token') === $order->receipt_token);

        abort_unless($authorised, 404);

        $order->load('items.product');
        return view('shop.pages.thanks', compact('order'));
    }

    private function resolveDelivery(string $dispatchMethodName, float $weight): float
    {
        $dm = DispatchMethod::where('name', $dispatchMethodName)->first();
        if (!$dm) return 0;
        $slab = DeliveryChargeSlab::active()
            ->where('dispatch_method_id', $dm->id)
            ->where('min_weight', '<=', $weight)
            ->where(function ($q) use ($weight) {
                $q->whereNull('max_weight')->orWhere('max_weight', '>=', $weight);
            })
            ->orderBy('min_weight')->first();
        return $slab ? (float) $slab->charge : 0;
    }
}
