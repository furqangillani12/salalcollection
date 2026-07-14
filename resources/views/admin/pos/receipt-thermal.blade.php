<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $order->order_number }}</title>
    <style>
        /* ── Thermal Receipt: 80mm (302px) or 58mm (219px) ── */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
            font-weight: 600;
            color: #000;
            background: #fff;
            width: 302px;
            margin: 0 auto;
            padding: 8px;
            -webkit-font-smoothing: none;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        .bold { font-weight: 900; }
        .small { font-size: 11px; }
        .large { font-size: 16px; }

        .divider {
            border: none;
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .double-divider {
            border: none;
            border-top: 2px solid #000;
            margin: 6px 0;
        }

        /* ── Header ── */
        .header {
            text-align: center;
            margin-bottom: 4px;
        }

        .header .shop-name {
            font-size: 15px;
            font-weight: 900;
            letter-spacing: 0.5px;
        }

        .header .shop-info {
            font-size: 11px;
            font-weight: 700;
        }

        /* ── Info rows ── */
        .info-row {
            display: flex;
            justify-content: space-between;
            line-height: 1.7;
            font-size: 12px;
            font-weight: 700;
        }

        .info-row span:last-child {
            font-weight: 800;
        }

        /* ── Items ── */
        .items-header {
            display: flex;
            font-weight: 900;
            font-size: 12px;
            border-bottom: 1px solid #000;
            padding-bottom: 3px;
            margin-bottom: 3px;
        }

        .items-header .col-name { flex: 1; }
        .items-header .col-qty { width: 35px; text-align: center; }
        .items-header .col-price { width: 55px; text-align: right; }
        .items-header .col-total { width: 60px; text-align: right; }

        .item-row {
            display: flex;
            line-height: 1.6;
            font-size: 12px;
            font-weight: 700;
            border-bottom: 1px dotted #ccc;
            padding: 2px 0;
        }

        .item-row .col-name {
            flex: 1;
            word-break: break-word;
            white-space: normal;
            padding-right: 4px;
        }

        .item-row .col-qty {
            width: 35px;
            text-align: center;
            flex-shrink: 0;
        }

        .item-row .col-price {
            width: 55px;
            text-align: right;
            font-size: 11px;
            flex-shrink: 0;
        }

        .item-row .col-total {
            width: 60px;
            text-align: right;
            font-weight: 900;
            flex-shrink: 0;
        }

        /* ── Totals ── */
        .total-row {
            display: flex;
            justify-content: space-between;
            line-height: 1.7;
            font-size: 13px;
            font-weight: 700;
        }

        .total-row.grand {
            font-size: 15px;
            font-weight: 900;
            margin: 4px 0;
        }

        .total-row.highlight {
            font-weight: 900;
        }

        /* ── Footer ── */
        .footer {
            text-align: center;
            margin-top: 8px;
            font-size: 11px;
            font-weight: 700;
        }

        .footer .bold {
            font-size: 12px;
            font-weight: 900;
        }

        /* ── Print controls (hidden in print) ── */
        .no-print {
            width: 302px;
            margin: 20px auto;
            text-align: center;
        }

        .no-print button {
            padding: 10px 30px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border: 2px solid #000;
            background: #fff;
            margin: 4px;
        }

        .no-print button:hover { background: #f0f0f0; }
        .no-print .btn-print { background: #2563eb; color: #fff; border-color: #2563eb; }
        .no-print .btn-back { background: #e5e7eb; color: #374151; border-color: #d1d5db; }

        /* ── Print ── */
        @media print {
            body {
                width: 100%;
                padding: 0;
                margin: 0;
            }

            .no-print { display: none !important; }

            @page {
                size: 80mm auto;
                margin: 0;
            }
        }

        /* ── 58mm mode ── */
        body.thermal-58mm { width: 219px; }
        body.thermal-58mm .item-row .col-price { display: none; }
        body.thermal-58mm .items-header .col-price { display: none; }
        body.thermal-58mm .header .shop-name { font-size: 13px; }
        body.thermal-58mm .large { font-size: 14px; }
        body.thermal-58mm .item-row .col-total { width: 55px; }
    </style>
</head>

<body id="receipt-body">

    {{-- ── Header ── --}}
    <div class="header">
        <div class="shop-name">{{ strtoupper($order->branch->name ?? 'ALMUFEED SAQAFTI MARKAZ') }}</div>
        <div class="shop-info">www.almufeed.com.pk</div>
        <div class="shop-info">Ph: {{ $order->branch->phone ?? '03007951919' }}</div>
    </div>

    <hr class="double-divider">

    {{-- ── Order Info ── --}}
    <div class="info-row">
        <span>Receipt#:</span>
        <span>{{ $order->order_number }}</span>
    </div>
    <div class="info-row">
        <span>Date:</span>
        <span>{{ $order->created_at?->format('d/m/Y h:i A') }}</span>
    </div>
    @if ($order->customer)
        <div class="info-row">
            <span>Customer:</span>
            <span>{{ $order->customer->name }}</span>
        </div>
    @endif
    <div class="info-row">
        <span>Cashier:</span>
        <span>{{ $order->user->name ?? 'N/A' }}</span>
    </div>

    <hr class="divider">

    {{-- ── Items ── --}}
    <div class="items-header">
        <span class="col-name">Item</span>
        <span class="col-qty">Qty</span>
        <span class="col-price">Rate</span>
        <span class="col-total">Amt</span>
    </div>

    @foreach ($order->items as $item)
        <div class="item-row">
            <span class="col-name">
                {{ $item->product?->name ?? 'Deleted' }}
                @if($item->hasLineDiscount())
                    <br><small style="font-size:9px;color:#888;">was {{ number_format($item->original_price,0) }}, disc -{{ number_format($item->line_discount,0) }}</small>
                @endif
            </span>
            <span class="col-qty">{{ $item->quantity }}@if($item->product?->unit?->abbreviation) {{ $item->product->unit->abbreviation }}@endif</span>
            <span class="col-price">{{ number_format($item->unit_price, 0) }}</span>
            <span class="col-total">{{ number_format($item->total_price, 0) }}</span>
        </div>
    @endforeach

    <hr class="divider">

    {{-- ── Totals ── --}}
    <div class="total-row">
        <span>Subtotal</span>
        <span>{{ number_format($order->subtotal, 0) }}</span>
    </div>

    @if (($order->tax ?? 0) > 0)
        <div class="total-row">
            <span>Tax ({{ $order->tax_rate }}%)</span>
            <span>{{ number_format($order->tax, 0) }}</span>
        </div>
    @endif

    @if (($order->delivery_charges ?? 0) > 0)
        <div class="total-row">
            <span>Delivery</span>
            <span>{{ number_format($order->delivery_charges, 0) }}</span>
        </div>
    @endif

    @if (($order->discount ?? 0) > 0)
        <div class="total-row">
            <span>Discount</span>
            <span>-{{ number_format($order->discount, 0) }}</span>
        </div>
    @endif

    <hr class="double-divider">

    <div class="total-row grand">
        <span>TOTAL</span>
        <span>Rs.{{ number_format($order->total, 0) }}</span>
    </div>

    {{-- ── Payment / Balance ── --}}
    @php
        $paidAmount = $order->paid_amount ?? $order->total;
        $balanceOnBill = max(0, $order->total - $paidAmount);
        $prevBalance = $order->computePreviousBalance();
        $currentBalance = $prevBalance + $order->total - $paidAmount;
        $hasKhata = $order->customer_id && ($balanceOnBill > 0 || $prevBalance != 0 || $paidAmount != $order->total);
    @endphp

    @if ($hasKhata)
        @php
            $advanceUsed = $prevBalance < 0 ? min(abs($prevBalance), $balanceOnBill) : 0;
        @endphp
        <hr class="divider">
        @if ($paidAmount > 0)
            <div class="total-row highlight">
                <span>Paid</span>
                <span>Rs.{{ number_format($paidAmount, 0) }}</span>
            </div>
        @endif
        @if ($prevBalance > 0)
            <div class="total-row small">
                <span>Prev Balance</span>
                <span>Rs.{{ number_format($prevBalance, 0) }}</span>
            </div>
        @elseif($prevBalance < 0)
            <div class="total-row small">
                <span>Prev Advance</span>
                <span>Rs.{{ number_format(abs($prevBalance), 0) }}</span>
            </div>
            @if ($advanceUsed > 0)
                <div class="total-row small">
                    <span>Adjusted</span>
                    <span>-Rs.{{ number_format($advanceUsed, 0) }}</span>
                </div>
            @endif
        @endif
        @if ($balanceOnBill > 0 && $prevBalance >= 0)
            <div class="total-row">
                <span>Bill Balance</span>
                <span>Rs.{{ number_format($balanceOnBill, 0) }}</span>
            </div>
        @endif
        @if ($currentBalance > 0)
            <div class="total-row grand">
                <span>BALANCE DUE</span>
                <span>Rs.{{ number_format($currentBalance, 0) }}</span>
            </div>
        @elseif($currentBalance < 0)
            <div class="total-row grand">
                <span>CHANGE DUE</span>
                <span>Rs.{{ number_format(abs($currentBalance), 0) }}</span>
            </div>
        @elseif($prevBalance != 0)
            <div class="total-row grand center">
                <span>ALL SETTLED</span>
            </div>
        @endif
    @endif

    <hr class="divider">

    <div class="info-row">
        <span>Payment:</span>
        <span>{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'Cash')) }}</span>
    </div>

    @if ($order->dispatch_method)
        <div class="info-row">
            <span>Dispatch:</span>
            <span>{{ $order->dispatch_method }}</span>
        </div>
    @endif

    @if ($order->tracking_id)
        <div class="info-row">
            <span>Tracking:</span>
            <span>{{ $order->tracking_id }}</span>
        </div>
    @endif

    <hr class="divider">

    {{-- ── Footer ── --}}
    <div class="footer">
        <p class="bold">Thank you for shopping!</p>
        <p>Returns within 7 days with receipt</p>
    </div>

    <br><br>

    {{-- ── Print Controls (hidden in print) ── --}}
    <div class="no-print">
        <button class="btn-print" onclick="window.print()">Print Receipt</button>
        <br>
        <button class="btn-back" onclick="toggleSize()">Switch 58mm / 80mm</button>
        <button class="btn-back" onclick="window.close()">Close</button>
    </div>

    <script>
        window.addEventListener('load', function() {
            setTimeout(function() { window.print(); }, 300);
        });

        function toggleSize() {
            document.getElementById('receipt-body').classList.toggle('thermal-58mm');
        }
    </script>
</body>

</html>
