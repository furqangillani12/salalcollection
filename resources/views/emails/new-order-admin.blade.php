<!DOCTYPE html>
<html>
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#f3f4f6;font-family:Arial,Helvetica,sans-serif;color:#111827;">
    <div style="max-width:560px;margin:0 auto;padding:24px 16px;">
        <div style="background:#111827;color:#fff;border-radius:12px 12px 0 0;padding:18px 22px;">
            <div style="font-size:13px;letter-spacing:.06em;text-transform:uppercase;opacity:.8;">SALAL COLLECTION</div>
            <div style="font-size:20px;font-weight:800;margin-top:4px;">🛒 New website order</div>
        </div>
        <div style="background:#fff;border:1px solid #e5e7eb;border-top:0;border-radius:0 0 12px 12px;padding:22px;">
            <p style="margin:0 0 14px;font-size:15px;">A new order has just been placed on the storefront.</p>

            <table style="width:100%;border-collapse:collapse;font-size:14px;">
                <tr><td style="padding:6px 0;color:#6b7280;">Order</td><td style="padding:6px 0;text-align:right;font-weight:800;">{{ $order->order_number }}</td></tr>
                <tr><td style="padding:6px 0;color:#6b7280;">Customer</td><td style="padding:6px 0;text-align:right;font-weight:700;">{{ trim($order->shipping_first_name . ' ' . $order->shipping_last_name) ?: ($order->customer_email ?: '—') }}</td></tr>
                <tr><td style="padding:6px 0;color:#6b7280;">Phone</td><td style="padding:6px 0;text-align:right;">{{ $order->shipping_phone ?: '—' }}</td></tr>
                <tr><td style="padding:6px 0;color:#6b7280;">Items</td><td style="padding:6px 0;text-align:right;">{{ (int) $order->items->sum('quantity') }}</td></tr>
                <tr><td style="padding:6px 0;color:#6b7280;">Payment</td><td style="padding:6px 0;text-align:right;text-transform:capitalize;">{{ str_replace('_', ' ', $order->payment_method) }}</td></tr>
                <tr><td style="padding:6px 0;color:#6b7280;">Dispatch</td><td style="padding:6px 0;text-align:right;">{{ $order->dispatch_method ?: '—' }}</td></tr>
                <tr><td style="padding:10px 0 4px;border-top:1px solid #e5e7eb;color:#111827;font-weight:800;">Total</td><td style="padding:10px 0 4px;border-top:1px solid #e5e7eb;text-align:right;font-weight:800;font-size:16px;">Rs. {{ number_format((float) $order->total, 0) }}</td></tr>
            </table>

            @if ($order->order_notes_customer)
                <div style="margin-top:14px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:10px 12px;font-size:13px;">
                    <b>Customer note:</b> {{ $order->order_notes_customer }}
                </div>
            @endif

            <a href="{{ route('admin.online-orders.show', $order) }}"
               style="display:inline-block;margin-top:18px;background:#0891b2;color:#fff;text-decoration:none;font-weight:700;padding:11px 20px;border-radius:8px;font-size:14px;">
                View order in admin →
            </a>

            <p style="margin:18px 0 0;font-size:12px;color:#9ca3af;">Placed {{ $order->created_at?->format('d M Y · h:i A') }}. This is an automatic alert to the store team.</p>
        </div>
    </div>
</body>
</html>
