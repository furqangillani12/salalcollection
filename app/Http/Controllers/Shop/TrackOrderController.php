<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class TrackOrderController extends Controller
{
    public function show()
    {
        return view('shop.pages.track');
    }

    /**
     * Look up an order by order_number + (email OR phone).
     * Either match must be on the order itself — guests don't have accounts.
     */
    public function find(Request $request)
    {
        $data = $request->validate([
            'order_number' => 'required|string|max:50',
            'contact'      => 'required|string|max:191',
        ]);

        $contact   = trim($data['contact']);
        $isEmail   = filter_var($contact, FILTER_VALIDATE_EMAIL) !== false;
        $orderNum  = trim($data['order_number']);

        $order = Order::where('order_number', $orderNum)
            ->where(function ($q) use ($contact, $isEmail) {
                if ($isEmail) {
                    // Either the order's stashed customer_email (online orders) ...
                    $q->where('customer_email', $contact)
                      // ... or the linked customer's email (POS-created or older orders where customer_email is null)
                      ->orWhereExists(function ($sub) use ($contact) {
                          $sub->selectRaw(1)
                              ->from('customers')
                              ->whereColumn('customers.id', 'orders.customer_id')
                              ->where('customers.email', $contact);
                      });
                } else {
                    // Phone match: digits-only so spacing / dashes / country code prefix forgive.
                    $digits = preg_replace('/\D+/', '', $contact);
                    if ($digits === '') return;
                    $q->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(shipping_phone,''),' ',''),'-',''),'(',''),')','') LIKE ?", ['%' . $digits])
                      ->orWhereExists(function ($sub) use ($digits) {
                          $sub->selectRaw(1)
                              ->from('customers')
                              ->whereColumn('customers.id', 'orders.customer_id')
                              ->whereRaw("REPLACE(REPLACE(REPLACE(REPLACE(COALESCE(customers.phone,''),' ',''),'-',''),'(',''),')','') LIKE ?", ['%' . $digits]);
                      });
                }
            })
            ->first();

        if (!$order) {
            return back()->withInput()->with('shop_error', 'No order found with those details. Double-check your order number and email/phone.');
        }

        // Older POS-created orders may not have a receipt token. Mint one so the public page works.
        if (empty($order->receipt_token)) {
            $order->update(['receipt_token' => bin2hex(random_bytes(16))]);
        }

        return redirect()->route('shop.track.view', ['token' => $order->receipt_token]);
    }

    /**
     * Public, token-based view of the order — works for both guests and members.
     * The receipt_token is given to the customer in the thank-you confirmation.
     */
    public function view(string $token)
    {
        $order = Order::where('receipt_token', $token)->firstOrFail();
        $order->load('items.product');
        return view('shop.pages.track-result', compact('order'));
    }
}
