<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checklist — {{ $order->order_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: #f3f4f6; color: #111827; }
        .toolbar { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 10px 16px; display: flex; gap: 8px; }
        .toolbar button, .toolbar a { font-size: 12px; padding: 6px 12px; border-radius: 8px; border: 1px solid #d1d5db; background: #fff; color: #374151; text-decoration: none; cursor: pointer; }
        .toolbar .print { background: #111827; color: #fff; border-color: #111827; margin-left: auto; }
        .sheet { max-width: 800px; margin: 16px auto; background: #fff; border: 1px solid #d1d5db; padding: 24px; }
        h1 { font-size: 18px; margin: 0 0 2px; }
        .muted { color: #6b7280; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; border-bottom: 2px solid #111827; padding: 6px; }
        td { border-bottom: 1px solid #e5e7eb; padding: 8px 6px; font-size: 13px; vertical-align: middle; }
        td img { width: 44px; height: 52px; object-fit: cover; border-radius: 4px; background: #f3f4f6; }
        .chk { width: 20px; height: 20px; border: 2px solid #9ca3af; border-radius: 4px; display: inline-block; }
        .code { font-family: monospace; font-size: 11px; color: #374151; }
        .totals { margin-top: 16px; text-align: right; font-size: 14px; }
        @media print { body { background: #fff; } .toolbar { display: none; } .sheet { margin: 0; border: none; max-width: none; } @page { margin: 12mm; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <strong style="font-size:13px;align-self:center;">Picking checklist</strong>
        <button class="print" onclick="window.print()">🖨 Print</button>
        <a href="{{ route('admin.online-orders.show', $order) }}">← Back</a>
    </div>
    <div class="sheet">
        <h1>Order {{ $order->order_number }}</h1>
        <div class="muted">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }} · {{ $order->shipping_phone }} · {{ $order->created_at->format('d M Y') }}</div>

        <table>
            <thead>
                <tr>
                    <th style="width:30px;">✓</th>
                    <th style="width:56px;">Image</th>
                    <th>Product</th>
                    <th>Item code</th>
                    <th style="text-align:right;">Price</th>
                    <th style="text-align:center;">Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->items as $item)
                    <tr>
                        <td><span class="chk"></span></td>
                        <td><img src="{{ shop_image($item->product?->image) }}" alt=""></td>
                        <td>
                            <div style="font-weight:700;">{{ $item->product?->name ?? 'Product' }}</div>
                            @if ($item->product?->brand)<div class="muted">{{ $item->product->brand->name }}</div>@endif
                        </td>
                        <td><span class="code">{{ $item->product?->barcode ?: '—' }}</span></td>
                        <td style="text-align:right;">Rs. {{ number_format($item->unit_price, 0) }}</td>
                        <td style="text-align:center;font-weight:800;font-size:15px;">{{ (int) $item->quantity }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totals">
            <div>Total items: <strong>{{ (int) $order->items->sum('quantity') }}</strong></div>
            <div>Order total: <strong>Rs. {{ number_format($order->total, 0) }}</strong></div>
        </div>
    </div>
</body>
</html>
