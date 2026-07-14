<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .receipt {
            width: 280px;   /* ✅ Receipt width (like 80mm printer roll) */
            margin: 0 auto;
            padding: 10px;
            border: 1px dashed #333;
        }
        h1 { font-size: 14px; margin: 0; text-align: center; }
        .header p { margin: 2px 0; font-size: 11px; text-align: center; }
        .section { margin: 8px 0; }
        .info div { display: flex; justify-content: space-between; font-size: 11px; margin-bottom: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; font-size: 11px; }
        th, td { padding: 4px 0; }
        th { border-bottom: 1px solid #000; font-weight: bold; }
        td { border-bottom: 1px dashed #ccc; }
        td:first-child, th:first-child { text-align: left; }
        td, th { text-align: right; }
        td:first-child { text-align: left; }
        .totals { margin-top: 5px; font-size: 11px; }
        .totals div { display: flex; justify-content: space-between; margin-bottom: 2px; }
        .totals .grand { font-weight: bold; font-size: 12px; border-top: 1px solid #000; padding-top: 4px; }
        .footer { text-align: center; margin-top: 10px; font-size: 10px; }
    </style>
</head>
<body>
<div class="receipt">
    <!-- Receipt Header -->
    <div class="header">
        <h1>{{ $order->branch->name ?? 'ALMufeed Saqafti Markaz' }}</h1>
        <p>www.almufeed.com.pk</p>
        <p>Phone: {{ $order->branch->phone ?? '03007951919' }}</p>
    </div>

    <!-- Order Info -->
    <div class="section info">
        <div><span><strong>Receipt #:</strong></span><span>{{ $order->order_number }}</span></div>
        <div><span><strong>Date:</strong></span><span>{{ $order->created_at?->format('d M, Y h:i A') ?? 'N/A' }}</span></div>
        @if($order->customer)
            <div><span><strong>Customer:</strong></span><span>{{ $order->customer?->name ?? 'N/A' }}</span></div>
        @endif
    </div>

    <!-- Order Items -->
    <table>
        <thead>
        <tr>
            <th style="width: 40%;">Item</th>
            <th style="width: 20%;">Qty</th>
            <th style="width: 20%;">Price</th>
            <th style="width: 20%;">Total</th>
        </tr>
        </thead>
        <tbody>
        @foreach($order->items as $item)
            <tr>
                <td>
                    {{ $item->product?->name ?? 'Deleted' }}
                    @if($item->hasLineDiscount())
                        <br><small style="color:#dc2626;">−Rs.{{ number_format($item->line_discount,0) }}/unit disc.</small>
                    @endif
                </td>
                <td>{{ $item->quantity ?? 0 }}@if($item->product?->unit?->abbreviation) {{ $item->product->unit->abbreviation }}@endif</td>
                <td>
                    @if($item->hasLineDiscount())
                        <span style="text-decoration:line-through;color:#9ca3af;">{{ number_format($item->original_price,0) }}</span>
                        {{ number_format($item->unit_price,0) }}
                    @else
                        {{ number_format($item->unit_price ?? 0, 2) }}
                    @endif
                </td>
                <td>{{ number_format($item->total_price ?? 0, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals">
        <div><span>Subtotal:</span><span>{{ number_format($order->subtotal ?? 0, 2) }}</span></div>
        <div><span>Tax ({{ $order->tax_rate ?? 0 }}%):</span><span>{{ number_format($order->tax ?? 0, 2) }}</span></div>
        @if(($order->delivery_charges ?? 0) > 0)
            <div><span>Delivery Charges:</span><span>{{ number_format($order->delivery_charges ?? 0, 2) }}</span></div>
        @endif
        @if(($order->discount ?? 0) > 0)
            <div><span>Discount:</span><span>-{{ number_format($order->discount ?? 0, 2) }}</span></div>
        @endif
        <div class="grand"><span>Total:</span><span>{{ number_format($order->total ?? 0, 2) }}</span></div>
        <div><span>Payment:</span><span>{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</span></div>
        @if($order->dispatch_method)
            <div><span>Dispatch:</span><span>{{ $order->dispatch_method }}</span></div>
        @endif
        @if($order->tracking_id)
            <div><span>Tracking:</span><span>{{ $order->tracking_id }}</span></div>
        @endif
        @if($order->weight)
            <div><span>Weight:</span><span>{{ $order->weight }} kg</span></div>
        @endif
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Thank you for your purchase at {{ $order->branch->name ?? 'ALMufeed Saqafti Markaz' }}!</p>
        <p>Items can be returned within 7 days with receipt.</p>
    </div>
</div>
</body>
</html>
