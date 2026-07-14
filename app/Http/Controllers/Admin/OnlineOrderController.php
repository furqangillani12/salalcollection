<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Traits\BranchScoped;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Admin manager for storefront / online orders. Reads the same orders
 * table as the POS but filters by order_source='online'.
 */
class OnlineOrderController extends Controller
{
    use BranchScoped;

    public function index(Request $request)
    {
        // Online orders are placed on ONE shared storefront; their branch_id is
        // just which branch owns the product. The online-order manager needs to
        // see them all in one place regardless of the selected POS branch, so
        // these are intentionally NOT branch-scoped (unlike POS sales).
        $query = Order::query()
            ->where('order_source', 'online')
            ->with('customer')
            ->withCount('items');

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($payment = $request->input('online_payment_status')) {
            $query->where('online_payment_status', $payment);
        }
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('shipping_first_name', 'like', "%{$search}%")
                  ->orWhere('shipping_last_name', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($c) use ($search) {
                      $c->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }
        if ($from = $request->input('from')) $query->whereDate('created_at', '>=', $from);
        if ($to   = $request->input('to'))   $query->whereDate('created_at', '<=', $to);

        $orders = $query->latest()->paginate(20)->withQueryString();

        // Stats (also cross-branch — see note above).
        $statBase = Order::query()->where('order_source', 'online');
        $stats = [
            'all'       => (clone $statBase)->count(),
            'pending'   => (clone $statBase)->where('status', 'pending')->count(),
            'confirmed' => (clone $statBase)->where('status', 'confirmed')->count(),
            'shipped'   => (clone $statBase)->where('status', 'shipped')->count(),
            'delivered' => (clone $statBase)->where('status', 'delivered')->count(),
            'cancelled' => (clone $statBase)->where('status', 'cancelled')->count(),
            'unpaid_bank' => (clone $statBase)->where('online_payment_status', 'bank_pending')->count(),
            'revenue'   => (clone $statBase)->where('status', '!=', 'cancelled')->sum('total'),
        ];

        return view('admin.online-orders.index', compact('orders', 'stats'));
    }

    public function show(Order $order)
    {
        abort_unless($order->order_source === 'online', 404);
        $order->load('items.product', 'customer', 'branch');
        return view('admin.online-orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        abort_unless($order->order_source === 'online', 404);

        $allowed = array_keys(config('order_flow.statuses', []));
        $data = $request->validate([
            'status'      => ['required', 'string', \Illuminate\Validation\Rule::in($allowed)],
            'tracking_id' => 'nullable|string|max:191',
        ]);

        $restockStatuses = config('order_flow.restock', ['cancelled']);

        DB::transaction(function () use ($order, $data, $restockStatuses) {
            // Returned / cancelled: put stock back and reverse khata (once).
            if (in_array($data['status'], $restockStatuses, true) && !in_array($order->status, $restockStatuses, true)) {
                foreach ($order->items as $item) {
                    if ($item->product && $item->product->track_inventory && $order->branch_id) {
                        $item->product->incrementBranchStock($order->branch_id, (float) $item->quantity);
                    }
                }
                // Reverse khata for logged-in customers
                if ($order->customer && $order->balance_amount > 0) {
                    $order->customer->update([
                        'current_balance' => round((float) ($order->customer->current_balance ?? 0) - (float) $order->balance_amount, 2),
                    ]);
                }
            }
            $changed = $order->status !== $data['status'];
            $order->update([
                'status'      => $data['status'],
                'tracking_id' => $data['tracking_id'] ?? $order->tracking_id,
            ]);

            // Tracking timeline event (#20).
            if ($changed) {
                $note = $data['status'] === 'dispatched' && ($data['tracking_id'] ?? $order->tracking_id)
                    ? trim(($order->dispatch_method ? $order->dispatch_method . ' · ' : '') . 'Tracking ' . ($data['tracking_id'] ?? $order->tracking_id))
                    : null;
                $order->recordStatus($data['status'], $note);
            }

            // Reward points on delivery (#22) — once per order, scheme-gated.
            if ($data['status'] === 'delivered' && $order->customer) {
                $already = \App\Models\PointTransaction::where('order_id', $order->id)
                    ->where('type', 'earn_order')->exists();
                $points = shop_order_points($order->total);
                if (!$already && $points > 0) {
                    $order->customer->awardPoints($points, 'earn_order', "Order {$order->order_number}", $order->id);
                }
            }
        });

        // Notify the customer of the new status (safe no-op without an email).
        \App\Mail\OrderStatusMail::dispatchFor($order->fresh(), $data['status']);

        return back()->with('success', 'Order status updated to ' . ucfirst($data['status']) . '.');
    }

    /** Printable bilingual dispatch slip (#15/#16/#17). */
    public function slip(Request $request, Order $order)
    {
        abort_unless($order->order_source === 'online', 404);
        $order->load('items.product', 'customer', 'branch');

        // Default language comes from settings; a ?lang= URL param overrides per-print.
        $defaultLang = in_array(setting('dispatch_slip_lang'), ['ur', 'en'], true) ? setting('dispatch_slip_lang') : 'en';
        $lang        = in_array($request->input('lang'), ['ur', 'en'], true) ? $request->input('lang') : $defaultLang;
        // Sender ("From") block default:
        //   • regular customer                    → show the company as sender
        //   • reseller/wholesaler + own address   → show the reseller's address
        //   • reseller/wholesaler, no own address → hide From (white-label)
        $resellerType = in_array($order->customer_type ?: optional($order->customer)->customer_type, ['reseller', 'wholesale'], true);
        $fromDefault  = $resellerType ? ($order->from_name ? 'reseller' : 'hide') : 'company';
        $from = in_array($request->input('from'), ['company', 'reseller', 'hide'], true) ? $request->input('from') : $fromDefault;
        if ($from === 'reseller' && ! $order->from_name) {
            $from = 'hide';
        }
        $withLogo    = $request->boolean('logo', true);
        $withDetails = $request->boolean('details', true);
        $dispatchMethod = \App\Models\DispatchMethod::where('name', $order->dispatch_method)->first();

        // Dispatch date = when the order first hit dispatched/shipped (legacy).
        $dispatchedAt = $order->statusHistory()
            ->whereIn('status', ['dispatched', 'shipped'])
            ->orderBy('id')
            ->value('created_at');

        // Print scale so a slip fits smaller courier labels: 100 / 85 / 70 %.
        $scale = in_array((int) $request->input('scale'), [100, 85, 70], true) ? (int) $request->input('scale') : 100;

        return view('admin.online-orders.slip', compact('order', 'lang', 'from', 'withLogo', 'withDetails', 'dispatchMethod', 'dispatchedAt', 'scale'));
    }

    /** Printable picking checklist (#18): image, name, barcode, price, qty. */
    public function checklist(Order $order)
    {
        abort_unless($order->order_source === 'online', 404);
        $order->load('items.product');
        return view('admin.online-orders.checklist', compact('order'));
    }

    /** Attach a dispatch photo / short video to the order. */
    public function uploadDispatchMedia(Request $request, Order $order)
    {
        abort_unless($order->order_source === 'online', 404);
        $request->validate([
            'dispatch_media' => 'required|file|mimes:png,jpg,jpeg,webp,mp4,webm,mov|max:20480',
        ]);

        if ($order->dispatch_media_path && \Storage::disk('public')->exists($order->dispatch_media_path)) {
            \Storage::disk('public')->delete($order->dispatch_media_path);
        }
        $order->update([
            'dispatch_media_path' => $request->file('dispatch_media')->store('dispatch-media', 'public'),
        ]);

        return back()->with('success', 'Dispatch photo/video attached.');
    }

    /**
     * Edit weight + delivery charge on an online order and recompute the tax and
     * total the same way checkout did. Keeps the customer's khata (balance) in
     * sync by the difference. POS orders are unaffected (online-only action).
     */
    public function adjust(Request $request, Order $order)
    {
        abort_unless($order->order_source === 'online', 404);

        $data = $request->validate([
            'weight'              => 'nullable|numeric|min:0|max:9999',
            'delivery_charges'    => 'nullable|numeric|min:0|max:9999999',
            // Editable COD amount for the dispatch slip (blank = auto: paid ? 0 : balance).
            'dispatch_cod_amount' => 'nullable|numeric|min:0|max:9999999',
            // Remarks printed on the dispatch slip (blank = empty box for handwriting).
            'dispatch_remarks'    => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($request, $order, $data) {
            $oldTotal = (float) $order->total;
            $delivery = (float) ($data['delivery_charges'] ?? 0);

            // Same formula as checkout: tax on (subtotal − discounts + delivery).
            $afterDiscount = max(0, (float) $order->subtotal - (float) $order->coupon_discount - (float) $order->points_discount);
            $tax           = shop_tax_amount($afterDiscount + $delivery);
            $newTotal      = round(max(0, $afterDiscount + $tax + $delivery), 2);
            $delta         = round($newTotal - $oldTotal, 2);

            // Blank COD field clears the override (back to auto); a number stores it.
            $codRaw = $request->input('dispatch_cod_amount');
            $codOverride = ($codRaw === null || $codRaw === '') ? null : round((float) $codRaw, 2);

            $order->update([
                'weight'              => $data['weight'] ?? 0,
                'delivery_charges'    => $delivery,
                'tax'                 => $tax,
                'total'               => $newTotal,
                'balance_amount'      => round($newTotal - (float) $order->paid_amount, 2),
                'dispatch_cod_amount' => $codOverride,
                'dispatch_remarks'    => $data['dispatch_remarks'] ?? null,
            ]);

            // Reflect the change on the customer's running balance.
            if ($order->customer && abs($delta) > 0) {
                $order->customer->increment('current_balance', $delta);
            }
        });

        return back()->with('success', 'Weight & delivery updated — total recalculated.');
    }

    /** Manually (re)send the current-status email to the customer. */
    public function notify(Order $order)
    {
        abort_unless($order->order_source === 'online', 404);
        \App\Mail\OrderStatusMail::dispatchFor($order, $order->status);
        return back()->with('success', 'Status email sent to the customer.');
    }

    public function markPaid(Request $request, Order $order)
    {
        abort_unless($order->order_source === 'online', 404);

        $data = $request->validate([
            'note'        => 'nullable|string|max:255',
            'payment_ref' => 'nullable|string|max:191',
        ]);

        DB::transaction(function () use ($order, $data) {
            $order->update([
                'paid_amount'           => (float) $order->total,
                'balance_amount'        => 0,
                'payment_status'        => 'paid',
                'online_payment_status' => $order->online_payment_status === 'cod' ? 'paid' : 'bank_paid',
                'online_payment_ref'    => $data['payment_ref'] ?? $order->online_payment_ref,
            ]);

            // Reduce customer khata since they've paid (if logged-in customer)
            if ($order->customer) {
                $order->customer->update([
                    'current_balance' => round((float) ($order->customer->current_balance ?? 0) - (float) $order->total, 2),
                ]);
            }
        });

        return back()->with('success', 'Payment marked as received.');
    }
}
