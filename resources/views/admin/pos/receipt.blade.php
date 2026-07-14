@extends('layouts.admin')

@section('title', 'Receipt #' . $order->order_number)

@push('styles')
<style>
    /* ── Receipt container ── */
    .receipt-wrap {
        max-width: 520px;
        margin: 0 auto;
        padding: 0;
    }

    .receipt-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,.08);
        overflow: hidden;
    }

    /* ── Header ── */
    .receipt-header {
        text-align: center;
        padding: 24px 20px 16px;
        border-bottom: 2px dashed #e5e7eb;
    }

    .receipt-header h1 {
        font-size: 20px;
        font-weight: 800;
        color: #1e293b;
        margin: 0 0 4px;
    }

    .receipt-header p {
        font-size: 13px;
        color: #6b7280;
        margin: 2px 0;
    }

    /* ── Info rows ── */
    .receipt-info {
        padding: 14px 20px;
        border-bottom: 1px solid #f1f5f9;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 3px 0;
        font-size: 13px;
    }

    .info-row .label {
        font-weight: 600;
        color: #374151;
    }

    .info-row .value {
        color: #1e293b;
        text-align: right;
    }

    /* ── Items table ── */
    .receipt-items {
        padding: 0 20px 14px;
        border-bottom: 2px dashed #e5e7eb;
        overflow-x: auto;
    }

    .items-table {
        width: 100%;
        border-collapse: collapse;
    }

    .items-table thead th {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .3px;
        color: #6b7280;
        padding: 10px 0 8px;
        border-bottom: 2px solid #1e293b;
    }

    .items-table thead th:first-child { text-align: left; }
    .items-table thead th:nth-child(2) { text-align: center; width: 50px; }
    .items-table thead th:nth-child(3) { text-align: right; width: 70px; }
    .items-table thead th:last-child { text-align: right; width: 80px; }

    .items-table tbody td {
        padding: 7px 0;
        font-size: 13px;
        color: #1e293b;
        border-bottom: 1px solid #f3f4f6;
        vertical-align: top;
    }

    .items-table tbody td:first-child {
        text-align: left;
        padding-right: 8px;
        word-break: break-word;
    }

    .items-table tbody td:nth-child(2) { text-align: center; font-weight: 500; }
    .items-table tbody td:nth-child(3) { text-align: right; font-family: ui-monospace, monospace; font-size: 12px; }
    .items-table tbody td:last-child { text-align: right; font-family: ui-monospace, monospace; font-size: 12px; font-weight: 600; }

    .items-table tbody tr:nth-child(even) { background: #f9fafb; }

    /* ── Totals ── */
    .receipt-totals {
        padding: 14px 20px;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 4px 0;
        font-size: 13px;
    }

    .total-row .label { color: #6b7280; font-weight: 600; }
    .total-row .value { font-family: ui-monospace, monospace; color: #1e293b; font-weight: 600; }

    .total-row.grand {
        border-top: 2px solid #1e293b;
        margin-top: 6px;
        padding-top: 8px;
    }

    .total-row.grand .label { font-size: 15px; font-weight: 800; color: #1e293b; }
    .total-row.grand .value { font-size: 17px; font-weight: 900; color: #1e293b; }

    .total-row.paid { background: #f0fdf4; padding: 6px 8px; border-radius: 6px; margin-top: 6px; }
    .total-row.paid .label { color: #16a34a; }
    .total-row.paid .value { color: #16a34a; }

    .total-row.balance { background: #fef2f2; padding: 6px 8px; border-radius: 6px; margin-top: 4px; }
    .total-row.balance .label { color: #dc2626; }
    .total-row.balance .value { color: #dc2626; }

    .total-row.prev-balance { background: #fff7ed; padding: 5px 8px; border-radius: 6px; margin-top: 4px; }
    .total-row.prev-balance .label { color: #c2410c; font-size: 12px; }
    .total-row.prev-balance .value { color: #c2410c; font-size: 12px; }

    .total-row.due { background: #fefce8; padding: 8px; border-radius: 6px; margin-top: 4px; border: 1.5px solid #fbbf24; }
    .total-row.due .label { color: #a16207; font-weight: 800; }
    .total-row.due .value { color: #a16207; font-weight: 800; }

    .total-row.advance { background: #f0fdf4; padding: 8px; border-radius: 6px; margin-top: 4px; border: 1.5px solid #4ade80; }
    .total-row.advance .label { color: #16a34a; font-weight: 800; }
    .total-row.advance .value { color: #16a34a; font-weight: 800; }

    .total-row.settled { background: #f0fdf4; padding: 8px; border-radius: 6px; margin-top: 4px; border: 1.5px solid #4ade80; text-align: center; }
    .total-row.settled span { color: #16a34a; font-weight: 800; width: 100%; text-align: center; }

    .total-row.discount .label { color: #dc2626; }
    .total-row.discount .value { color: #dc2626; }

    /* ── Payment & Dispatch meta ── */
    .receipt-meta {
        padding: 10px 20px 14px;
        border-top: 1px solid #f1f5f9;
    }

    /* ── Footer ── */
    .receipt-footer {
        text-align: center;
        padding: 16px 20px;
        border-top: 2px dashed #e5e7eb;
        color: #9ca3af;
        font-size: 12px;
    }

    .receipt-footer p { margin: 2px 0; }

    .qr-block {
        margin-top: 12px;
        padding: 10px;
        background: #f9fafb;
        border-radius: 8px;
        display: inline-block;
    }

    .qr-block p { font-size: 10px; color: #9ca3af; }

    #qrcode-container {
        width: 100px;
        height: 100px;
        margin: 6px auto;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #qrcode-container canvas,
    #qrcode-container img {
        width: 100px !important;
        height: 100px !important;
        max-width: 100px !important;
        max-height: 100px !important;
        display: block !important;
    }

    #qrcode-container canvas + img { display: none !important; }

    .receipt-url {
        font-size: 10px;
        color: #3b82f6;
        word-break: break-all;
        margin-top: 4px;
    }

    /* ── Action buttons ── */
    .receipt-actions {
        max-width: 520px;
        margin: 16px auto 0;
        padding: 0;
    }

    .action-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 8px;
    }

    .action-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 8px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #fff;
        text-decoration: none;
        border: none;
        cursor: pointer;
        white-space: nowrap;
        transition: opacity .15s;
    }

    .action-btn:hover { opacity: .85; }
    .action-btn i { font-size: 14px; }

    .action-btn.view { background: #0891b2; }
    .action-btn.whatsapp { background: #16a34a; }
    .action-btn.print { background: #2563eb; }
    .action-btn.pdf { background: #7c3aed; }
    .action-btn.copy { background: #4f46e5; }
    .action-btn.edit { background: #eab308; color: #1e293b; }
    .action-btn.new-sale { background: #e5e7eb; color: #374151; }

    /* ── Mobile responsive ── */
    @media (max-width: 640px) {
        .receipt-wrap {
            margin: 0;
            max-width: 100%;
        }

        .receipt-card {
            border-radius: 0;
            box-shadow: none;
        }

        .receipt-header {
            padding: 16px 14px 12px;
        }

        .receipt-header h1 { font-size: 17px; }
        .receipt-header p { font-size: 12px; }

        .receipt-info { padding: 10px 14px; }
        .info-row { font-size: 12px; }

        .receipt-items { padding: 0 14px 10px; }

        .items-table thead th { font-size: 10px; padding: 8px 0 6px; }

        .items-table tbody td {
            padding: 6px 0;
            font-size: 12px;
        }

        .items-table tbody td:nth-child(3),
        .items-table tbody td:last-child { font-size: 11px; }

        .receipt-totals { padding: 10px 14px; }
        .total-row { font-size: 12px; }
        .total-row.grand .label { font-size: 14px; }
        .total-row.grand .value { font-size: 15px; }

        .receipt-meta { padding: 8px 14px 10px; }
        .receipt-footer { padding: 12px 14px; }

        .receipt-actions { margin: 10px 0 0; padding: 0 8px; }

        .action-grid {
            grid-template-columns: repeat(3, 1fr);
            gap: 6px;
        }

        .action-btn {
            padding: 10px 6px;
            font-size: 11px;
            gap: 4px;
        }

        .action-btn i { font-size: 13px; }
    }

    @media (max-width: 380px) {
        .action-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .items-table thead th:nth-child(3),
        .items-table tbody td:nth-child(3) {
            display: none;
        }

        .items-table thead th:last-child { width: 70px; }
    }

    /* ── Dark mode ── */
    @media (prefers-color-scheme: dark) {
        .receipt-card { background: #1f2937; }
        .receipt-header { border-color: #374151; }
        .receipt-header h1 { color: #f3f4f6; }
        .receipt-header p { color: #9ca3af; }
        .receipt-info { border-color: #374151; }
        .info-row .label { color: #9ca3af; }
        .info-row .value { color: #e5e7eb; }
        .receipt-items { border-color: #374151; }
        .items-table thead th { color: #9ca3af; border-color: #374151; }
        .items-table tbody td { color: #e5e7eb; border-color: #374151; }
        .items-table tbody tr:nth-child(even) { background: #111827; }
        .receipt-totals .total-row .label { color: #9ca3af; }
        .receipt-totals .total-row .value { color: #e5e7eb; }
        .total-row.grand { border-color: #e5e7eb; }
        .total-row.grand .label, .total-row.grand .value { color: #f3f4f6; }
        .total-row.paid { background: #064e3b; }
        .total-row.paid .label, .total-row.paid .value { color: #6ee7b7; }
        .total-row.balance { background: #7f1d1d; }
        .total-row.balance .label, .total-row.balance .value { color: #fca5a5; }
        .total-row.prev-balance { background: #78350f; }
        .total-row.prev-balance .label, .total-row.prev-balance .value { color: #fde68a; }
        .total-row.due { background: #78350f; border-color: #a16207; }
        .total-row.due .label, .total-row.due .value { color: #fde68a; }
        .total-row.advance { background: #064e3b; border-color: #4ade80; }
        .total-row.advance .label, .total-row.advance .value { color: #6ee7b7; }
        .total-row.settled { background: #064e3b; border-color: #4ade80; }
        .total-row.settled span { color: #6ee7b7; }
        .total-row.discount .label, .total-row.discount .value { color: #f87171; }
        .receipt-meta { border-color: #374151; }
        .receipt-footer { border-color: #374151; color: #6b7280; }
        .qr-block { background: #111827; }
        .qr-block p { color: #6b7280; }
        .receipt-url { color: #60a5fa; }
        .action-btn.edit { color: #1e293b; }
        .action-btn.new-sale { background: #374151; color: #d1d5db; }
    }

    /* ── Print styles ── */
    @media print {
        body * { visibility: hidden; }

        .print-content, .print-content * { visibility: visible; }

        .print-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: 100% !important;
            padding: 15px;
            background: white;
        }

        .receipt-card {
            box-shadow: none;
            border-radius: 0;
        }

        .no-print { display: none !important; }

        #qrcode-container { width: 80px; height: 80px; }
        #qrcode-container canvas, #qrcode-container img {
            width: 80px !important; height: 80px !important;
            max-width: 80px !important; max-height: 80px !important;
        }
    }
</style>
@endpush

@section('content')
    {{-- ── Receipt Card ── --}}
    <div class="receipt-wrap print-content">
        <div class="receipt-card">

            {{-- Header --}}
            <div class="receipt-header">
                <h1>{{ $order->branch->name ?? 'ALMufeed Saqafti Markaz' }}</h1>
                <p>www.almufeed.com.pk</p>
                <p>Phone: {{ $order->branch->phone ?? '03007951919' }}</p>
            </div>

            {{-- Order Info --}}
            <div class="receipt-info">
                <div class="info-row">
                    <span class="label">Receipt #:</span>
                    <span class="value">{{ $order->order_number }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Date:</span>
                    <span class="value">{{ $order->created_at?->format('M d, Y h:i A') ?? 'N/A' }}</span>
                </div>
                @if ($order->customer)
                    <div class="info-row">
                        <span class="label">Customer:</span>
                        <span class="value">
                            {{ $order->customer->name }}
                            ({{ ucfirst($order->customer->customer_type) }})
                        </span>
                    </div>
                @endif
            </div>

            {{-- Items Table --}}
            @php
                $retQtyMap = [];
                foreach ($order->refunds->where('status','completed') as $ref) {
                    foreach (($ref->items ?? []) as $ri) {
                        $pid = $ri['product_id'] ?? null;
                        if ($pid) $retQtyMap[$pid] = ($retQtyMap[$pid] ?? 0) + ($ri['quantity'] ?? 0);
                    }
                }
                $adminTotalRefunded  = $order->refunds->where('status','completed')->sum('amount');
                $adminEffectiveTotal = max(0, $order->total - $adminTotalRefunded);
            @endphp
            <div class="receipt-items">
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($order->items as $item)
                        @php
                            $rQty      = $retQtyMap[$item->product_id] ?? 0;
                            $isFull    = $rQty >= $item->quantity;
                            $isPartial = $rQty > 0 && !$isFull;
                            $remQty    = max(0, $item->quantity - $rQty);
                        @endphp
                            @php
                                $hasLineDisc  = $item->hasLineDiscount();
                            @endphp
                            <tr style="{{ $isFull ? 'background:#fef2f2;' : '' }}">
                                <td>
                                    @if($isFull)
                                        <span style="text-decoration:line-through;color:#9ca3af;">{{ $item->product?->name ?? 'Deleted Product' }}</span>
                                        <span style="background:#fee2e2;color:#dc2626;font-size:9px;font-weight:700;padding:1px 5px;border-radius:4px;margin-left:4px;">RETURNED</span>
                                    @else
                                        {{ $item->product?->name ?? 'Deleted Product' }}
                                        @if($isPartial)
                                            <span style="background:#fff7ed;color:#c2410c;font-size:9px;font-weight:700;padding:1px 5px;border-radius:4px;margin-left:4px;">{{ $rQty }} RETURNED</span>
                                        @endif
                                    @endif
                                    @if($hasLineDisc)
                                        <div style="font-size:10px;color:#dc2626;margin-top:1px;">
                                            −Rs.{{ number_format($item->line_discount, 0) }}/unit discount
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($isPartial)
                                        <span style="text-decoration:line-through;color:#9ca3af;font-size:10px;">{{ $item->quantity }}</span>
                                        <span style="font-weight:700;display:block;">{{ $remQty }}</span>
                                    @else
                                        <span style="{{ $isFull ? 'text-decoration:line-through;color:#9ca3af;' : '' }}">{{ $item->quantity ?? 0 }}</span>
                                    @endif
                                    @if($item->product?->unit?->abbreviation) {{ $item->product->unit->abbreviation }}@endif
                                </td>
                                <td>
                                    @if($hasLineDisc)
                                        <span style="text-decoration:line-through;color:#9ca3af;font-size:10px;">{{ number_format($item->original_price, 0) }}</span><br>
                                        <span style="{{ $isFull ? 'color:#9ca3af;' : 'color:#16a34a;font-weight:700;' }}">{{ number_format($item->unit_price, 0) }}</span>
                                    @elseif(is_numeric($item->unit_price) && floor($item->unit_price) == $item->unit_price)
                                        <span style="{{ $isFull ? 'color:#9ca3af;' : '' }}">{{ number_format($item->unit_price, 0) }}</span>
                                    @else
                                        <span style="{{ $isFull ? 'color:#9ca3af;' : '' }}">{{ number_format($item->unit_price ?? 0, 2) }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($isFull)
                                        <span style="text-decoration:line-through;color:#9ca3af;">{{ number_format($item->total_price, 0) }}</span>
                                    @elseif($isPartial)
                                        <span style="text-decoration:line-through;color:#9ca3af;font-size:10px;display:block;">{{ number_format($item->total_price, 0) }}</span>
                                        <span style="font-weight:700;">{{ number_format($remQty * $item->unit_price, 0) }}</span>
                                    @else
                                        {{ number_format($item->total_price ?? 0, 0) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="receipt-totals">
                <div class="total-row">
                    <span class="label">Subtotal</span>
                    <span class="value">
                        @if (is_numeric($order->subtotal) && floor($order->subtotal) == $order->subtotal)
                            {{ number_format($order->subtotal, 0) }}
                        @else
                            {{ number_format($order->subtotal ?? 0, 2) }}
                        @endif
                    </span>
                </div>

                <div class="total-row">
                    <span class="label">Tax @if(($order->tax ?? 0) > 0)({{ ($order->tax_type ?? 'percent') === 'fixed' ? 'Rs.' : ($order->tax_rate ?? 0) . '%' }})@endif</span>
                    <span class="value">
                        @if (is_numeric($order->tax) && floor($order->tax) == $order->tax)
                            {{ number_format($order->tax, 0) }}
                        @else
                            {{ number_format($order->tax ?? 0, 2) }}
                        @endif
                    </span>
                </div>

                @if (($order->delivery_charges ?? 0) > 0)
                    <div class="total-row">
                        <span class="label">Delivery Charges</span>
                        <span class="value">
                            @if (is_numeric($order->delivery_charges) && floor($order->delivery_charges) == $order->delivery_charges)
                                {{ number_format($order->delivery_charges, 0) }}
                            @else
                                {{ number_format($order->delivery_charges ?? 0, 2) }}
                            @endif
                        </span>
                    </div>
                @endif

                @if (($order->discount ?? 0) > 0)
                    <div class="total-row discount">
                        <span class="label">{{ $order->discount_label ?? 'Discount' }}</span>
                        <span class="value">
                            -@if (is_numeric($order->discount) && floor($order->discount) == $order->discount)
                                {{ number_format($order->discount, 0) }}
                            @else
                                {{ number_format($order->discount ?? 0, 2) }}
                            @endif
                        </span>
                    </div>
                @endif

                <div class="total-row grand">
                    <span class="label">Total Bill</span>
                    <span class="value">Rs. {{ number_format($order->total ?? 0, 0) }}</span>
                </div>

                @if($adminTotalRefunded > 0)
                <div class="total-row" style="background:#fef2f2;padding:6px 8px;border-radius:6px;margin-top:6px;">
                    <span class="label" style="color:#dc2626;font-weight:700;">Total Returned (واپسی)</span>
                    <span class="value" style="color:#dc2626;font-weight:700;">− Rs. {{ number_format($adminTotalRefunded, 0) }}</span>
                </div>
                <div class="total-row" style="background:#f0fdf4;padding:6px 8px;border-radius:6px;margin-top:4px;border:1.5px solid #4ade80;">
                    <span class="label" style="color:#16a34a;font-weight:800;">Amount Payable</span>
                    <span class="value" style="color:#16a34a;font-weight:800;">Rs. {{ number_format($adminEffectiveTotal, 0) }}</span>
                </div>
                @endif

                {{-- Payment / Balance rows — only show if customer has any khata/balance record --}}
                @php
                    $paidAmount = $order->paid_amount ?? $order->total;
                    $balanceOnBill = max(0, $order->total - $paidAmount);
                    $prevBalance = $order->computePreviousBalance();
                    $currentBalance = $prevBalance + $order->total - $paidAmount;
                    $hasKhata = $order->customer_id && ($balanceOnBill > 0 || $prevBalance != 0 || $paidAmount != $order->total);
                @endphp

                @if ($hasKhata)
                    @php
                        $advanceUsed = ($prevBalance < 0) ? min(abs($prevBalance), $balanceOnBill) : 0;
                    @endphp

                    @if ($paidAmount > 0)
                        <div class="total-row paid">
                            <span class="label">Amount Paid</span>
                            <span class="value">Rs. {{ number_format($paidAmount, 0) }}</span>
                        </div>
                    @endif

                    @if ($prevBalance > 0)
                        <div class="total-row prev-balance">
                            <span class="label">Previous Balance</span>
                            <span class="value">Rs. {{ number_format($prevBalance, 0) }}</span>
                        </div>
                    @elseif ($prevBalance < 0)
                        <div class="total-row prev-balance" style="background:#f0fdf4;">
                            <span class="label" style="color:#16a34a;">Previous Advance (پچھلی واپسی)</span>
                            <span class="value" style="color:#16a34a;">Rs. {{ number_format(abs($prevBalance), 0) }}</span>
                        </div>
                        @if ($advanceUsed > 0)
                            <div class="total-row" style="background:#eff6ff;padding:5px 8px;border-radius:6px;margin-top:4px;">
                                <span class="label" style="color:#2563eb;font-size:12px;">Adjusted from Advance</span>
                                <span class="value" style="color:#2563eb;font-size:12px;">- Rs. {{ number_format($advanceUsed, 0) }}</span>
                            </div>
                        @endif
                    @endif

                    @if ($balanceOnBill > 0 && $prevBalance >= 0)
                        <div class="total-row balance">
                            <span class="label">Balance on Bill</span>
                            <span class="value">Rs. {{ number_format($balanceOnBill, 0) }}</span>
                        </div>
                    @endif

                    @if ($currentBalance > 0)
                        <div class="total-row due">
                            <span class="label">Current Balance Due</span>
                            <span class="value">Rs. {{ number_format($currentBalance, 0) }}</span>
                        </div>
                    @elseif ($currentBalance < 0)
                        <div class="total-row advance">
                            <span class="label">Change Due (واپسی)</span>
                            <span class="value">Rs. {{ number_format(abs($currentBalance), 0) }}</span>
                        </div>
                    @elseif ($prevBalance != 0)
                        <div class="total-row settled">
                            <span>✅ All Settled (حساب برابر)</span>
                        </div>
                    @endif
                @endif
            </div>

            {{-- Payment & Dispatch meta --}}
            <div class="receipt-meta">
                <div class="info-row">
                    <span class="label">Payment Method:</span>
                    <span class="value">{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</span>
                </div>
                @if ($order->dispatch_method)
                    <div class="info-row">
                        <span class="label">Dispatch:</span>
                        <span class="value">{{ $order->dispatch_method }}</span>
                    </div>
                @endif
                @if ($order->tracking_id)
                    <div class="info-row">
                        <span class="label">Tracking ID:</span>
                        <span class="value">{{ $order->tracking_id }}</span>
                    </div>
                @endif
                @if ($order->weight)
                    <div class="info-row">
                        <span class="label">Parcel Weight:</span>
                        <span class="value">{{ $order->weight }} kg</span>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="receipt-footer">
                <p>Thank you for your business!</p>
                <p>Items can be returned within 7 days with receipt</p>
                <div class="qr-block">
                    <p>Scan to view receipt online:</p>
                    <div id="qrcode-container"></div>
                    <p class="receipt-url">{{ $order->receipt_url }}</p>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Flash Messages ── --}}
    @if(session('success'))
    <div class="receipt-wrap no-print" id="flash-msg" style="margin-top:12px;">
        <div style="background:#f0fdf4;border:2px solid #22c55e;border-radius:10px;padding:14px 18px;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-check-circle" style="color:#16a34a;font-size:20px;flex-shrink:0;"></i>
            <span style="font-size:14px;font-weight:600;color:#166534;">{{ session('success') }}</span>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="receipt-wrap no-print" id="flash-msg" style="margin-top:12px;">
        <div style="background:#fef2f2;border:2px solid #ef4444;border-radius:10px;padding:14px 18px;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-exclamation-circle" style="color:#dc2626;font-size:20px;flex-shrink:0;"></i>
            <span style="font-size:14px;font-weight:600;color:#991b1b;">{{ session('error') }}</span>
        </div>
    </div>
    @endif
    @if($errors->any())
    <div class="receipt-wrap no-print" id="flash-msg" style="margin-top:12px;">
        <div style="background:#fef2f2;border:2px solid #ef4444;border-radius:10px;padding:14px 18px;">
            <p style="font-size:13px;font-weight:700;color:#991b1b;margin-bottom:6px;"><i class="fas fa-exclamation-circle mr-1"></i> Please fix the following:</p>
            <ul style="margin:0;padding-left:18px;">
                @foreach($errors->all() as $error)
                    <li style="font-size:13px;color:#dc2626;">{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- ── Existing Refunds ── --}}
    @if($order->refunds->isNotEmpty())
    @php
        $totalRefunded   = $order->refunds->where('status','completed')->sum('amount');
        $remainingReturn = max(0, $order->total - $totalRefunded);
    @endphp
    <div class="receipt-wrap no-print" style="margin-top:16px;">
        <div class="receipt-card" style="border:2px solid #fca5a5;">
            <div style="padding:14px 20px;background:#fef2f2;border-bottom:1px solid #fca5a5;display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;font-size:14px;font-weight:700;color:#dc2626;">
                    <i class="fas fa-undo" style="margin-right:6px;"></i>Returns / Refunds
                </h3>
                <div style="text-align:right;">
                    <div style="font-size:12px;color:#dc2626;font-weight:700;">Total Returned: Rs. {{ number_format($totalRefunded, 0) }}</div>
                    @if($remainingReturn > 0)
                    <div style="font-size:11px;color:#6b7280;">Remaining returnable: Rs. {{ number_format($remainingReturn, 0) }}</div>
                    @else
                    <div style="font-size:11px;color:#16a34a;font-weight:600;">✓ Fully Returned</div>
                    @endif
                </div>
            </div>
            @foreach($order->refunds as $refund)
            <div style="padding:12px 20px;border-bottom:1px solid #fee2e2;font-size:13px;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                    <span style="font-weight:700;color:#dc2626;">{{ $refund->refund_number ?? 'Refund' }} — Rs. {{ number_format($refund->amount, 0) }}</span>
                    <span style="font-size:11px;color:#9ca3af;">{{ $refund->created_at->format('d M Y h:i A') }}</span>
                </div>
                <div style="color:#6b7280;margin-bottom:4px;">Reason: {{ $refund->reason }}</div>
                @if($refund->items)
                <div style="color:#374151;font-size:12px;">
                    Items: {{ collect($refund->items)->map(fn($i) => ($i['name'] ?? 'Item') . ' × ' . $i['quantity'])->implode(', ') }}
                </div>
                @endif
                <div style="font-size:11px;color:#9ca3af;margin-top:4px;">By: {{ $refund->user?->name ?? 'Staff' }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Action Buttons ── --}}
    <div class="receipt-actions no-print">
        <div class="action-grid">
            <a href="{{ $order->receipt_url }}" target="_blank" class="action-btn view">
                <i class="fas fa-eye"></i> View
            </a>
            <button id="whatsapp-share-btn" class="action-btn whatsapp">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </button>
            <button onclick="window.print()" class="action-btn print">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="{{ route('admin.pos.receipt.thermal', $order) }}" target="_blank" class="action-btn print" style="background:#0d9488;">
                <i class="fas fa-receipt"></i> Thermal
            </a>
            <a href="{{ route('admin.pos.receipt.pdf', $order) }}" class="action-btn pdf">
                <i class="fas fa-download"></i> PDF
            </a>
            <button id="copy-link-btn" class="action-btn copy">
                <i class="fas fa-copy"></i> Copy Link
            </button>
            <a href="{{ route('admin.pos.edit', $order) }}" class="action-btn edit">
                <i class="fas fa-edit"></i> Edit
            </a>
            @if($order->isRefundable())
            <button onclick="document.getElementById('refund-modal').style.display='flex'"
                class="action-btn" style="background:#dc2626;">
                <i class="fas fa-undo"></i> Return Items
            </button>
            @endif
            <a href="{{ route('admin.pos.index') }}" class="action-btn new-sale" style="grid-column: span 2;">
                <i class="fas fa-plus"></i> New Sale
            </a>
        </div>
    </div>

    {{-- ── Return/Refund Modal ── --}}
    @if($order->isRefundable())
    <div id="refund-modal"
        style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);z-index:9999;align-items:flex-start;justify-content:center;padding:12px;overflow-y:auto;">
        <div style="background:#fff;border-radius:12px;width:100%;max-width:500px;margin:auto;box-shadow:0 20px 60px rgba(0,0,0,.3);">

            <div style="padding:18px 20px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;">
                <h3 style="margin:0;font-size:16px;font-weight:700;color:#1e293b;">
                    <i class="fas fa-undo" style="color:#dc2626;margin-right:8px;"></i>Process Return
                </h3>
                <button onclick="document.getElementById('refund-modal').style.display='none'"
                    style="background:none;border:none;font-size:18px;cursor:pointer;color:#6b7280;">✕</button>
            </div>

            <form action="{{ route('admin.pos.refund', $order) }}" method="POST" id="refund-form">
                @csrf
                <div style="padding:20px;">

                    {{-- Item selection --}}
                    @php
                        $modalRetQty = [];
                        foreach($order->refunds->where('status','completed') as $ref) {
                            foreach(($ref->items ?? []) as $ri) {
                                $pid = $ri['product_id'] ?? null;
                                if($pid) $modalRetQty[$pid] = ($modalRetQty[$pid] ?? 0) + ($ri['quantity'] ?? 0);
                            }
                        }
                    @endphp
                    <p style="font-size:12px;font-weight:700;color:#374151;text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px;">
                        Select Items to Return
                    </p>
                    @if(array_sum($modalRetQty) > 0)
                    <p style="font-size:11px;color:#9ca3af;margin-bottom:10px;">
                        Already returned items are greyed out. You can only return remaining quantities.
                    </p>
                    @endif
                    <div style="border:1px solid #e5e7eb;border-radius:8px;overflow:hidden;margin-bottom:16px;">
                        @foreach($order->items as $idx => $item)
                        @php
                            $alreadyRet  = $modalRetQty[$item->product_id] ?? 0;
                            $remainingQt = max(0, $item->quantity - $alreadyRet);
                            $fullyRet    = $remainingQt <= 0;
                        @endphp
                        <div class="refund-item-row" style="padding:10px 14px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:10px;{{ $fullyRet ? 'background:#fef2f2;opacity:0.6;' : '' }}">
                            <input type="checkbox" name="items[{{ $idx }}][selected]" value="1"
                                class="refund-item-check" data-idx="{{ $idx }}" data-price="{{ $item->unit_price }}"
                                style="width:16px;height:16px;{{ $fullyRet ? 'cursor:not-allowed;' : 'cursor:pointer;' }}"
                                onchange="updateRefundTotal()"
                                {{ $fullyRet ? 'disabled' : '' }}>
                            <div style="flex:1;">
                                <div style="font-size:13px;font-weight:600;color:{{ $fullyRet ? '#9ca3af' : '#1e293b' }};">
                                    {{ $item->product?->name ?? 'Unknown' }}
                                    @if($fullyRet)
                                        <span style="background:#fee2e2;color:#dc2626;font-size:9px;font-weight:700;padding:1px 5px;border-radius:4px;margin-left:4px;">FULLY RETURNED</span>
                                    @elseif($alreadyRet > 0)
                                        <span style="background:#fff7ed;color:#c2410c;font-size:9px;font-weight:700;padding:1px 5px;border-radius:4px;margin-left:4px;">{{ $alreadyRet }} RETURNED</span>
                                    @endif
                                </div>
                                <div style="font-size:11px;color:#9ca3af;">
                                    Rs. {{ number_format($item->unit_price, 0) }} ×
                                    @if($alreadyRet > 0 && !$fullyRet)
                                        <span style="text-decoration:line-through;">{{ $item->quantity }}</span>
                                        <span style="color:#1e293b;font-weight:600;">{{ $remainingQt }} remaining</span>
                                    @else
                                        {{ $item->quantity }}
                                    @endif
                                    = Rs. {{ number_format($remainingQt * $item->unit_price, 0) }}
                                </div>
                            </div>
                            <div>
                                <input type="hidden" name="items[{{ $idx }}][product_id]" value="{{ $item->product_id }}">
                                <input type="hidden" name="items[{{ $idx }}][name]" value="{{ $item->product?->name }}">
                                <input type="hidden" name="items[{{ $idx }}][unit_price]" value="{{ $item->unit_price }}">
                                <input type="number" name="items[{{ $idx }}][quantity]"
                                    class="refund-qty-input" data-idx="{{ $idx }}"
                                    value="{{ $remainingQt }}" min="0.01" max="{{ $remainingQt }}"
                                    step="0.01"
                                    style="width:60px;padding:3px 6px;border:1px solid #e5e7eb;border-radius:4px;font-size:12px;text-align:center;opacity:0.4;{{ $fullyRet ? 'cursor:not-allowed;' : '' }}"
                                    oninput="updateRefundTotal()"
                                    {{ $fullyRet ? 'disabled' : '' }}>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Refund summary --}}
                    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:12px 14px;margin-bottom:4px;display:flex;justify-content:space-between;align-items:center;">
                        <span style="font-size:13px;font-weight:600;color:#dc2626;">Refund Amount</span>
                        <span id="refund-total-display" style="font-size:18px;font-weight:800;color:#dc2626;">Rs. 0</span>
                    </div>
                    <div id="refund-proportional-note" style="display:none;font-size:11px;color:#9ca3af;text-align:right;margin-bottom:16px;"></div>
                    <div id="refund-no-discount-spacer" style="margin-bottom:16px;"></div>

                    {{-- Reason --}}
                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;">
                            Reason for Return <span style="color:#ef4444;">*</span>
                        </label>
                        <textarea name="reason" required rows="2"
                            placeholder="e.g. Defective item, Wrong size, Customer changed mind..."
                            style="width:100%;padding:8px 10px;border:1px solid #e5e7eb;border-radius:6px;font-size:13px;resize:vertical;box-sizing:border-box;"></textarea>
                    </div>

                    {{-- Return to inventory --}}
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:20px;">
                        <input type="checkbox" name="return_to_inventory" value="1" style="width:16px;height:16px;">
                        <div>
                            <span style="font-size:13px;font-weight:600;color:#374151;">Return items to inventory</span>
                            <p style="margin:0;font-size:11px;color:#9ca3af;">Stock will be restored for selected items</p>
                        </div>
                    </label>

                    <div style="display:flex;gap:10px;">
                        <button type="button"
                            onclick="document.getElementById('refund-modal').style.display='none'"
                            style="flex:1;padding:10px;border:1px solid #e5e7eb;border-radius:6px;background:#fff;font-size:13px;cursor:pointer;color:#6b7280;">
                            Cancel
                        </button>
                        <button type="submit" id="refund-submit-btn"
                            style="flex:2;padding:10px;background:#dc2626;color:#fff;border:none;border-radius:6px;font-size:13px;font-weight:700;cursor:pointer;">
                            <i class="fas fa-undo"></i> Process Return
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        const _orderSubtotal = {{ $order->subtotal ?? 0 }};
        const _orderTotal    = {{ $order->total ?? 0 }};
        const _orderDiscount = {{ $order->discount ?? 0 }};

        function updateRefundTotal() {
            let rawTotal = 0;
            document.querySelectorAll('.refund-item-check').forEach(cb => {
                const idx = cb.dataset.idx;
                const qtyInput = document.querySelector(`.refund-qty-input[data-idx="${idx}"]`);
                if (cb.checked && qtyInput) {
                    const qty = parseFloat(qtyInput.value) || 0;
                    const price = parseFloat(cb.dataset.price) || 0;
                    rawTotal += qty * price;
                    qtyInput.style.opacity = '1';
                    qtyInput.style.pointerEvents = 'auto';
                } else if (qtyInput) {
                    qtyInput.style.opacity = '0.35';
                    qtyInput.style.pointerEvents = 'none';
                }
            });

            // Proportional refund when order has a level discount (e.g. package discount)
            let refundAmount = rawTotal;
            if (_orderDiscount > 0 && _orderSubtotal > 0) {
                refundAmount = Math.round(rawTotal / _orderSubtotal * _orderTotal);
            }

            document.getElementById('refund-total-display').textContent =
                'Rs. ' + refundAmount.toLocaleString('en-PK', {maximumFractionDigits: 0});

            const noteEl   = document.getElementById('refund-proportional-note');
            const spacerEl = document.getElementById('refund-no-discount-spacer');
            if (_orderDiscount > 0 && rawTotal > 0) {
                noteEl.style.display   = 'block';
                spacerEl.style.display = 'none';
                noteEl.textContent     = 'Items subtotal Rs. ' +
                    rawTotal.toLocaleString('en', {maximumFractionDigits: 0}) +
                    ' — adjusted proportionally for discount (Rs. ' +
                    _orderDiscount.toLocaleString('en', {maximumFractionDigits: 0}) + ')';
            } else {
                noteEl.style.display   = 'none';
                spacerEl.style.display = 'block';
            }
        }

        // Auto-scroll to flash message if present
        const flashMsg = document.getElementById('flash-msg');
        if (flashMsg) {
            setTimeout(() => flashMsg.scrollIntoView({ behavior: 'smooth', block: 'center' }), 200);
        }

        document.getElementById('refund-form').addEventListener('submit', function(e) {
            const anyChecked = [...document.querySelectorAll('.refund-item-check')].some(cb => cb.checked);
            if (!anyChecked) {
                e.preventDefault();
                alert('Please select at least one item to return.');
                return;
            }
            const reason = this.querySelector('textarea[name=reason]').value.trim();
            if (!reason) {
                e.preventDefault();
                alert('Please enter a reason for the return.');
                return;
            }
            // Show loading state
            const btn = document.getElementById('refund-submit-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            }
        });
    </script>
    @endif

@endsection

@push('scripts')
    <!-- QR Code Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ── QR Code ──
            const receiptUrl = "{{ $order->receipt_url }}";
            if (receiptUrl) {
                try {
                    const container = document.getElementById('qrcode-container');
                    container.innerHTML = '';
                    const wrapper = document.createElement('div');
                    wrapper.style.cssText = 'width:100px;height:100px;position:relative;';
                    container.appendChild(wrapper);

                    new QRCode(wrapper, {
                        text: receiptUrl,
                        width: 100,
                        height: 100,
                        colorDark: "#000000",
                        colorLight: "#ffffff",
                        correctLevel: QRCode.CorrectLevel.H
                    });

                    // Clean up duplicate elements QRCode.js creates
                    setTimeout(() => {
                        const imgs = wrapper.getElementsByTagName('img');
                        for (let i = 1; i < imgs.length; i++) imgs[i].remove();
                    }, 100);
                } catch (e) {
                    document.getElementById('qrcode-container').innerHTML =
                        `<p style="font-size:10px;color:#9ca3af;text-align:center;">QR unavailable</p>`;
                }
            }

            // ── WhatsApp Share ──
            document.getElementById('whatsapp-share-btn').addEventListener('click', function(e) {
                e.preventDefault();

                let customerName = "{{ $order->customer?->name ?? 'Customer' }}";
                let phone = "{{ $order->customer?->phone ?? '' }}";

                if (!phone) {
                    phone = prompt("Enter customer phone number (with country code, e.g., 923001234567):", "92");
                    if (!phone) { alert("Phone number is required."); return; }
                }

                phone = phone.replace(/\D/g, '');
                if (!phone.startsWith('92')) {
                    phone = phone.startsWith('0') ? '92' + phone.substring(1) : '92' + phone;
                }

                let message = `*{{ $order->branch->name ?? 'AlMufeed Saqafti Markaz' }} - Receipt*\n\n`;
                message += `Dear ${customerName},\n\n`;
                message += `Thank you for shopping with us!\n\n`;
                message += `*Receipt #*: {{ $order->order_number }}\n`;
                message += `*Date*: {{ $order->created_at?->format('d M, Y h:i A') }}\n`;
                message += `*Total Bill*: Rs. {{ number_format($order->total ?? 0, 0) }}\n`;
                @if ($hasKhata)
                    message += `*Amount Paid*: Rs. {{ number_format($paidAmount, 0) }}\n`;
                    @if ($balanceOnBill > 0)
                        message += `*Balance on Bill*: Rs. {{ number_format($balanceOnBill, 0) }}\n`;
                    @endif
                    @if ($prevBalance > 0)
                        message += `*Previous Balance*: Rs. {{ number_format($prevBalance, 0) }}\n`;
                    @endif
                    @if ($currentBalance > 0)
                        message += `\n*Total Balance Due*: Rs. {{ number_format($currentBalance, 0) }}\n`;
                    @elseif ($currentBalance < 0)
                        message += `\n*Change Due (واپسی)*: Rs. {{ number_format(abs($currentBalance), 0) }}\n`;
                    @endif
                @endif
                message += `\n*Payment Method*: {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}\n`;
                @if ($order->dispatch_method)
                    message += `*Dispatch Method*: {{ $order->dispatch_method }}\n`;
                @endif
                @if ($order->tracking_id)
                    message += `*Tracking ID*: {{ $order->tracking_id }}\n`;
                    @if ($order->weight)
                        message += `*Weight*: {{ $order->weight }} kg\n`;
                    @endif
                @endif
                message += `\n*View Your Receipt Online:*\n${receiptUrl}\n\n`;
                message += `*Contact Us:*\n{{ $order->branch->phone ?? '03007951919' }}\nwww.almufeed.com.pk\n\nWe appreciate your purchase!`;

                window.open(`https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(message)}`, '_blank');
            });

            // ── Copy Link ──
            document.getElementById('copy-link-btn').addEventListener('click', async function() {
                const btn = this;
                try {
                    await navigator.clipboard.writeText(receiptUrl);
                } catch {
                    const t = document.createElement('input');
                    t.value = receiptUrl;
                    document.body.appendChild(t);
                    t.select();
                    document.execCommand('copy');
                    document.body.removeChild(t);
                }
                const orig = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                btn.style.background = '#16a34a';
                setTimeout(() => { btn.innerHTML = orig; btn.style.background = ''; }, 2000);
            });
        });
    </script>
@endpush
