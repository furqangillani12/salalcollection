<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $order->order_number }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f2f5;
            color: #1e293b;
            padding: 10px;
            -webkit-text-size-adjust: 100%;
        }

        /* ── Main card ── */
        .receipt-card {
            max-width: 640px;
            margin: 0 auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0,0,0,.08);
            overflow: hidden;
        }

        /* ── Company header ── */
        .company-header {
            text-align: center;
            padding: 10px 20px 8px;
            border-bottom: 2px dashed #e5e7eb;
        }

        .company-header .logo {
            max-height: 80px;
            width: auto;
            display: block;
            margin: 0 auto;
        }

        /* ── Invoice title ── */
        .invoice-title {
            text-align: center;
            padding: 10px 20px;
            border-bottom: 2px solid #2563eb;
        }

        .invoice-title h1 {
            font-size: 18px;
            font-weight: 800;
            color: #1e293b;
            letter-spacing: 1px;
        }

        .invoice-title .receipt-num {
            font-size: 13px;
            color: #6b7280;
            margin-top: 2px;
        }

        /* ── Section title ── */
        .section-title {
            background: #f8fafc;
            padding: 6px 20px;
            font-size: 12px;
            font-weight: 800;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

        /* ── Info grid ── */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            padding: 10px 20px;
        }

        .info-box {
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 12px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 3px 0;
            font-size: 12px;
            gap: 8px;
        }

        .info-row .label {
            color: #6b7280;
            flex-shrink: 0;
        }

        .info-row .value {
            color: #1e293b;
            font-weight: 500;
            text-align: right;
            word-break: break-word;
        }

        /* ── Items table ── */
        .items-section { padding: 0 20px 10px; }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table thead th {
            background: #f8fafc;
            border: 1px solid #e5e7eb;
            padding: 8px 6px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: #6b7280;
        }

        .items-table thead th:first-child { text-align: center; width: 36px; }
        .items-table thead th:nth-child(2) { text-align: left; }
        .items-table thead th:nth-child(3) { text-align: center; width: 50px; }
        .items-table thead th:nth-child(4) { text-align: right; width: 80px; }
        .items-table thead th:last-child { text-align: right; width: 90px; }

        .items-table tbody td {
            border: 1px solid #f1f5f9;
            padding: 8px 6px;
            font-size: 13px;
            color: #1e293b;
        }

        .items-table tbody td:first-child { text-align: center; color: #9ca3af; }
        .items-table tbody td:nth-child(2) { word-break: break-word; }
        .items-table tbody td:nth-child(3) { text-align: center; font-weight: 500; }
        .items-table tbody td:nth-child(4) { text-align: right; font-family: ui-monospace, monospace; font-size: 12px; }
        .items-table tbody td:last-child { text-align: right; font-family: ui-monospace, monospace; font-size: 12px; font-weight: 600; }

        .items-table tbody tr:nth-child(even) { background: #fafbfc; }

        .product-sku { font-size: 10px; color: #9ca3af; margin-top: 2px; }

        /* ── Summary ── */
        .summary-box {
            margin: 0 20px 10px;
            background: #f8fafc;
            border: 1px solid #f1f5f9;
            border-radius: 8px;
            padding: 10px 14px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 4px 0;
            font-size: 13px;
        }

        .summary-row .label { color: #6b7280; }
        .summary-row .value { font-weight: 600; color: #1e293b; font-family: ui-monospace, monospace; }

        .summary-row.discount .label,
        .summary-row.discount .value { color: #16a34a; }

        .summary-row.grand {
            border-top: 2px solid #1e293b;
            margin-top: 8px;
            padding-top: 10px;
        }

        .summary-row.grand .label { font-size: 15px; font-weight: 800; color: #1e293b; }
        .summary-row.grand .value { font-size: 18px; font-weight: 900; color: #2563eb; }

        .summary-row.payment-status {
            font-size: 12px;
            padding-top: 8px;
            border-top: 1px solid #e5e7eb;
            margin-top: 6px;
        }

        .summary-row.payment-status .label { color: #9ca3af; }
        .summary-row.payment-status .value { color: #9ca3af; }

        /* ── Notes ── */
        .notes-section {
            padding: 0 20px 10px;
            font-size: 12px;
            color: #6b7280;
            line-height: 1.5;
        }

        /* ── Footer ── */
        .receipt-footer {
            text-align: center;
            padding: 10px 20px;
            border-top: 2px dashed #e5e7eb;
            font-size: 12px;
            color: #9ca3af;
        }

        .receipt-footer p { margin: 2px 0; }

        /* ── Action buttons ── */
        .actions-wrap {
            max-width: 640px;
            margin: 16px auto 0;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
        }

        .act-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 12px 10px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            color: #fff;
            cursor: pointer;
            text-decoration: none;
            transition: opacity .15s;
            -webkit-tap-highlight-color: transparent;
        }

        .act-btn:hover { opacity: .85; }
        .act-btn i { font-size: 14px; }

        .act-btn.print-btn { background: #16a34a; }
        .act-btn.pdf-btn { background: #2563eb; }
        .act-btn.jpg-btn { background: #7c3aed; }

        /* ── Loading overlay ── */
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.6);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .loading-overlay.show { display: flex; }

        .loading-box {
            background: #fff;
            padding: 28px;
            border-radius: 12px;
            text-align: center;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e5e7eb;
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: spin .8s linear infinite;
            margin: 0 auto 14px;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Mobile responsive ── */
        @media (max-width: 640px) {
            body { padding: 0; }

            .receipt-card {
                border-radius: 0;
                box-shadow: none;
            }

            .company-header { padding: 8px 14px 6px; }
            .company-header .logo { max-height: 60px; }

            .invoice-title { padding: 8px 14px; }
            .invoice-title h1 { font-size: 16px; }

            .section-title { padding: 5px 14px; font-size: 11px; }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 6px;
                padding: 8px 14px;
            }

            .info-box { padding: 10px; }
            .info-row { font-size: 11px; }

            .items-section { padding: 0 10px 8px; overflow-x: auto; }

            .items-table thead th {
                padding: 6px 4px;
                font-size: 10px;
            }

            .items-table tbody td {
                padding: 6px 4px;
                font-size: 11px;
            }

            .items-table tbody td:nth-child(4),
            .items-table tbody td:last-child { font-size: 10px; }

            .items-table thead th:nth-child(4) { width: 65px; }
            .items-table thead th:last-child { width: 70px; }

            .summary-box { margin: 0 10px 8px; padding: 8px 10px; }
            .summary-row { font-size: 12px; }
            .summary-row.grand .label { font-size: 14px; }
            .summary-row.grand .value { font-size: 16px; }

            .notes-section { padding: 0 14px 8px; }
            .receipt-footer { padding: 8px 14px; }

            .actions-wrap { margin: 8px 8px 16px; }
            .actions-grid { gap: 6px; }
            .act-btn { padding: 10px 6px; font-size: 12px; }
        }

        @media (max-width: 380px) {
            .items-table thead th:first-child,
            .items-table tbody td:first-child { display: none; }

            .items-table thead th:nth-child(4),
            .items-table tbody td:nth-child(4) { display: none; }

            .items-table thead th:last-child { width: auto; }

            .items-table tbody td { font-size: 10px; padding: 5px 3px; }
            .items-table thead th { font-size: 9px; padding: 5px 3px; }

            .act-btn { padding: 10px 4px; font-size: 11px; gap: 4px; }
            .act-btn i { font-size: 12px; }
        }

        /* ── Dark mode ── */
        @media (prefers-color-scheme: dark) {
            body { background: #111827; }
            .receipt-card { background: #1f2937; box-shadow: 0 4px 24px rgba(0,0,0,.3); }
            .company-header .company-name { color: #f3f4f6; }
            .company-header .company-contact { color: #9ca3af; }
            .invoice-title { border-color: #3b82f6; }
            .invoice-title h1 { color: #f3f4f6; }
            .invoice-title .receipt-num { color: #9ca3af; }
            .section-title { background: #111827; color: #9ca3af; }
            .info-grid .info-box { background: #111827; border-color: #374151; }
            .info-row .label { color: #9ca3af; }
            .info-row .value { color: #e5e7eb; }
            .items-table thead th { background: #111827; border-color: #374151; color: #9ca3af; }
            .items-table tbody td { border-color: #374151; color: #e5e7eb; }
            .items-table tbody tr:nth-child(even) { background: rgba(55,65,81,0.3); }
            .product-sku { color: #6b7280; }
            .summary-box { background: #111827; border-color: #374151; }
            .summary-row .label { color: #9ca3af; }
            .summary-row .value { color: #e5e7eb; }
            .summary-row.grand .label { color: #f3f4f6; }
            .summary-row.grand .value { color: #60a5fa; }
            .summary-row.discount .label, .summary-row.discount .value { color: #4ade80; }
            .summary-row.payment-status .label, .summary-row.payment-status .value { color: #6b7280; }
            .notes-section { color: #9ca3af; }
            .receipt-footer { border-color: #374151; color: #6b7280; }
            .loading-box { background: #1f2937; color: #e5e7eb; }
            .spinner { border-color: #374151; border-top-color: #3b82f6; }
        }

        /* ── Print — Thermal Receipt Optimized (80mm) ── */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            /* Reset everything to black on white */
            *, *::before, *::after {
                color: #000 !important;
                background: #fff !important;
                background-color: #fff !important;
                box-shadow: none !important;
                text-shadow: none !important;
                border-color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            body {
                padding: 0 !important;
                margin: 0 !important;
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 13px !important;
                font-weight: 600 !important;
                width: 80mm !important;
                max-width: 80mm !important;
                -webkit-font-smoothing: none !important;
            }

            .no-print, .loading-overlay { display: none !important; }

            .receipt-card {
                border-radius: 0 !important;
                max-width: 80mm !important;
                width: 80mm !important;
                overflow: hidden !important;
                margin: 0 !important;
                padding: 0 3px !important;
            }

            /* ── Header ── */
            .company-header {
                padding: 4px 0 !important;
                border-bottom: 1px dashed #000 !important;
            }

            .company-header .logo {
                max-height: 45px !important;
                width: auto !important;
            }

            /* ── Invoice title ── */
            .invoice-title {
                padding: 4px 0 !important;
                border-bottom: 2px solid #000 !important;
            }

            .invoice-title h1 {
                font-size: 15px !important;
                font-weight: 900 !important;
                letter-spacing: 1px !important;
            }

            .invoice-title .receipt-num {
                font-size: 12px !important;
                font-weight: 700 !important;
            }

            /* ── Section headings ── */
            .section-title {
                padding: 3px 0 !important;
                font-size: 11px !important;
                font-weight: 900 !important;
                background: #fff !important;
                border-bottom: 1px dashed #000 !important;
                margin: 0 !important;
            }

            /* ── Info grid → single column, no boxes ── */
            .info-grid {
                display: block !important;
                padding: 3px 0 !important;
            }

            .info-box {
                background: #fff !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 1px 0 !important;
                margin: 0 !important;
            }

            .info-row {
                display: flex !important;
                justify-content: space-between !important;
                font-size: 12px !important;
                font-weight: 600 !important;
                padding: 2px 0 !important;
                line-height: 1.5 !important;
            }

            .info-row .label {
                font-size: 12px !important;
                font-weight: 600 !important;
            }

            .info-row .value {
                font-size: 12px !important;
                font-weight: 800 !important;
            }

            /* ── Items table ── */
            .items-section {
                padding: 0 !important;
            }

            .items-table {
                width: 100% !important;
                border-collapse: collapse !important;
            }

            .items-table thead th {
                background: #fff !important;
                border: none !important;
                border-bottom: 2px solid #000 !important;
                padding: 3px 2px !important;
                font-size: 11px !important;
                font-weight: 900 !important;
                font-family: Arial, Helvetica, sans-serif !important;
            }

            /* Hide # column on thermal */
            .items-table thead th:first-child,
            .items-table tbody td:first-child {
                display: none !important;
            }

            /* Product, Qty, Price, Total */
            .items-table tbody td {
                border: none !important;
                border-bottom: 1px solid #ccc !important;
                padding: 3px 2px !important;
                font-size: 12px !important;
                font-weight: 600 !important;
                font-family: Arial, Helvetica, sans-serif !important;
                line-height: 1.4 !important;
                word-break: break-word !important;
                white-space: normal !important;
            }

            /* Product name — allow wrapping */
            .items-table tbody td:nth-child(2) {
                word-break: break-word !important;
                white-space: normal !important;
                max-width: 120px !important;
            }

            /* Qty, Price, Total — no wrap */
            .items-table tbody td:nth-child(3),
            .items-table tbody td:nth-child(4),
            .items-table tbody td:last-child {
                white-space: nowrap !important;
                font-weight: 700 !important;
            }

            .items-table tbody tr:nth-child(even) {
                background: #fff !important;
            }

            .product-sku { display: none !important; }

            /* ── Summary ── */
            .summary-box {
                margin: 0 !important;
                background: #fff !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 3px 0 !important;
            }

            .summary-row {
                font-size: 12px !important;
                font-weight: 600 !important;
                padding: 2px 0 !important;
                background: #fff !important;
                border: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                line-height: 1.5 !important;
            }

            .summary-row .label,
            .summary-row .value {
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 12px !important;
                font-weight: 700 !important;
            }

            .summary-row.grand {
                border-top: 2px solid #000 !important;
                margin-top: 3px !important;
                padding-top: 4px !important;
            }

            .summary-row.grand .label {
                font-size: 14px !important;
                font-weight: 900 !important;
            }

            .summary-row.grand .value {
                font-size: 15px !important;
                font-weight: 900 !important;
            }

            /* ── Khata/Balance rows — strip ALL inline styles ── */
            .summary-box .summary-row[style] {
                background: #fff !important;
                background-color: #fff !important;
                border: none !important;
                border-radius: 0 !important;
                padding: 2px 0 !important;
                margin: 1px 0 !important;
            }

            .summary-box .summary-row[style] .label,
            .summary-box .summary-row[style] .value {
                color: #000 !important;
                font-size: 12px !important;
                font-weight: 700 !important;
                background: #fff !important;
            }

            /* Balance due row — stand out with dashes */
            .summary-box .summary-row[style*="border:1.5px"] {
                border-top: 2px dashed #000 !important;
                border-bottom: 2px dashed #000 !important;
                padding: 3px 0 !important;
                margin-top: 3px !important;
            }

            .summary-box .summary-row[style*="border:1.5px"] .label,
            .summary-box .summary-row[style*="border:1.5px"] .value {
                font-size: 13px !important;
                font-weight: 900 !important;
            }

            /* ── Notes ── */
            .notes-section {
                padding: 2px 0 !important;
                font-size: 12px !important;
                font-weight: 600 !important;
            }

            /* ── Footer ── */
            .receipt-footer {
                padding: 4px 0 !important;
                border-top: 1px dashed #000 !important;
            }

            .receipt-footer p {
                margin: 2px 0 !important;
                font-size: 11px !important;
                font-weight: 700 !important;
            }
        }
    </style>
</head>

<body>
    <!-- Loading overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-box">
            <div class="spinner"></div>
            <div id="loadingText">Converting to image...</div>
        </div>
    </div>

    <div class="receipt-card" id="receiptContent">

        <!-- Company Header -->
        <div class="company-header">
            @if ($order->branch && $order->branch->logo)
                <img src="{{ asset('storage/' . $order->branch->logo) }}" alt="{{ $order->branch->name }}" class="logo" style="max-height:110px;">
            @elseif (file_exists(public_path('assets/images/mufeed.png')))
                <img src="{{ asset('assets/images/mufeed.png') }}" alt="AlMufeed Saqafti Markaz" class="logo" style="max-height:110px;">
            @else
                <div class="company-name" style="font-size:18px;font-weight:800;margin-top:6px;">{{ $order->branch->name ?? 'AlMufeed Saqafti Markaz' }}</div>
                <div class="company-contact" style="font-size:12px;color:#6b7280;margin-top:2px;">Phone: {{ $order->branch->phone ?? '03007951919' }}</div>
            @endif
        </div>

        <!-- Invoice Title -->
        <div class="invoice-title">
            <h1>SALES INVOICE</h1>
            <div class="receipt-num">Receipt #{{ $order->order_number }}</div>
        </div>

        <!-- Order & Customer Information -->
        <div class="section-title">Order Information</div>
        <div class="info-grid">
            <div class="info-box">
                <div class="info-row">
                    <span class="label">Date & Time:</span>
                    <span class="value">{{ $order->created_at->format('d M, Y h:i A') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Payment:</span>
                    <span class="value" style="text-transform:capitalize;">{{ $order->payment_method }}</span>
                </div>
                @if ($order->dispatch_method)
                    <div class="info-row">
                        <span class="label">Dispatch:</span>
                        <span class="value">{{ $order->dispatch_method }}</span>
                    </div>
                @endif
                @if ($order->tracking_id)
                    <div class="info-row">
                        <span class="label">Tracking:</span>
                        <span class="value">{{ $order->tracking_id }}</span>
                    </div>
                @endif
            </div>

            <div class="info-box">
                @if ($order->customer)
                    <div class="info-row">
                        <span class="label">Customer:</span>
                        <span class="value">{{ $order->customer->name }}@if($order->customer->customer_type) ({{ ucfirst($order->customer->customer_type) }})@endif</span>
                    </div>
                    @if ($order->customer->phone)
                        <div class="info-row">
                            <span class="label">Phone:</span>
                            <span class="value">{{ $order->customer->phone }}</span>
                        </div>
                    @endif
                    @if ($order->customer->email)
                        <div class="info-row">
                            <span class="label">Email:</span>
                            <span class="value">{{ $order->customer->email }}</span>
                        </div>
                    @endif
                @else
                    <div class="info-row">
                        <span class="label">Customer:</span>
                        <span class="value">Walk-in Customer</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Items Table -->
        @php
            // Aggregate returned quantities per product from all completed refunds
            $returnedQty = [];
            foreach ($order->refunds->where('status', 'completed') as $refund) {
                foreach (($refund->items ?? []) as $ri) {
                    $pid = $ri['product_id'] ?? null;
                    if ($pid) $returnedQty[$pid] = ($returnedQty[$pid] ?? 0) + ($ri['quantity'] ?? 0);
                }
            }
            $totalRefunded     = $order->refunds->where('status','completed')->sum('amount');
            $hasAnyReturn      = $totalRefunded > 0;
            $effectiveTotal    = max(0, $order->total - $totalRefunded);
        @endphp
        <div class="section-title">Purchased Items{{ $hasAnyReturn ? ' (with returns)' : '' }}</div>
        <div class="items-section">
            <table class="items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $index => $item)
                        @php
                            $pid          = $item->product_id;
                            $retQty       = $returnedQty[$pid] ?? 0;
                            $isFullReturn = $retQty >= $item->quantity;
                            $isPartial    = $retQty > 0 && !$isFullReturn;
                            $remainQty    = max(0, $item->quantity - $retQty);
                        @endphp
                        <tr style="{{ $isFullReturn ? 'background:#fef2f2;opacity:0.7;' : '' }}">
                            <td>{{ $index + 1 }}</td>
                            <td>
                                @if($isFullReturn)
                                    <span style="text-decoration:line-through;color:#9ca3af;">{{ $item->product->name ?? 'Deleted Product' }}</span>
                                    <span style="display:inline-block;background:#fee2e2;color:#dc2626;font-size:9px;font-weight:700;padding:1px 5px;border-radius:4px;margin-left:4px;">RETURNED</span>
                                @else
                                    {{ $item->product->name ?? 'Deleted Product' }}
                                    @if($isPartial)
                                        <span style="display:inline-block;background:#fff7ed;color:#c2410c;font-size:9px;font-weight:700;padding:1px 5px;border-radius:4px;margin-left:4px;">{{ $retQty }} RETURNED</span>
                                    @endif
                                @endif
                                @if($item->hasLineDiscount())
                                    <div style="font-size:10px;color:#dc2626;margin-top:2px;">
                                        −Rs.{{ number_format($item->line_discount, 0) }} discount/unit
                                    </div>
                                @endif
                            </td>
                            <td>
                                @if($isFullReturn)
                                    <span style="text-decoration:line-through;color:#9ca3af;">{{ $item->quantity }}</span>
                                @elseif($isPartial)
                                    <span style="text-decoration:line-through;color:#9ca3af;font-size:10px;">{{ $item->quantity }}</span>
                                    <span style="color:#1e293b;font-weight:700;display:block;">{{ $remainQty }}</span>
                                @else
                                    {{ $item->quantity }}
                                @endif
                                @if($item->product?->unit?->abbreviation) {{ $item->product->unit->abbreviation }}@endif
                            </td>
                            <td>
                                @if($item->hasLineDiscount())
                                    <span style="text-decoration:line-through;color:#9ca3af;font-size:10px;display:block;">{{ number_format($item->original_price, 0) }}</span>
                                    <span style="{{ $isFullReturn ? 'color:#9ca3af;' : 'color:#16a34a;font-weight:700;' }}">{{ number_format($item->unit_price, 0) }}</span>
                                @elseif ($item->unit_price == floor($item->unit_price))
                                    <span style="{{ $isFullReturn ? 'color:#9ca3af;' : '' }}">{{ number_format($item->unit_price, 0) }}</span>
                                @else
                                    <span style="{{ $isFullReturn ? 'color:#9ca3af;' : '' }}">{{ number_format($item->unit_price, 2) }}</span>
                                @endif
                            </td>
                            <td>
                                @if($isFullReturn)
                                    <span style="text-decoration:line-through;color:#9ca3af;">{{ number_format($item->total_price, 0) }}</span>
                                @elseif($isPartial)
                                    <span style="text-decoration:line-through;color:#9ca3af;font-size:10px;display:block;">{{ number_format($item->total_price, 0) }}</span>
                                    <span style="font-weight:700;">{{ number_format($remainQty * $item->unit_price, 0) }}</span>
                                @else
                                    {{ number_format($item->total_price, 0) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Payment Summary -->
        <div class="section-title">Payment Summary</div>
        <div class="summary-box">
            <div class="summary-row">
                <span class="label">Subtotal</span>
                <span class="value">Rs. {{ number_format($order->subtotal, 2) }}</span>
            </div>

            @if ($order->tax_rate > 0 && $order->tax > 0)
                <div class="summary-row">
                    <span class="label">Tax ({{ $order->tax_rate }}%)</span>
                    <span class="value">Rs. {{ number_format($order->tax, 2) }}</span>
                </div>
            @endif

            @if ($order->discount > 0)
                <div class="summary-row discount">
                    <span class="label">{{ $order->discount_label ?? 'Discount' }}</span>
                    <span class="value">- Rs. {{ number_format($order->discount, 2) }}</span>
                </div>
            @endif

            @if ($order->delivery_charges > 0)
                <div class="summary-row">
                    <span class="label">Delivery Charges</span>
                    <span class="value">Rs. {{ number_format($order->delivery_charges, 2) }}</span>
                </div>
            @endif

            @if ($order->weight > 0)
                <div class="summary-row">
                    <span class="label">Total Weight</span>
                    <span class="value">{{ number_format($order->weight, 2) }} kg</span>
                </div>
            @endif

            <div class="summary-row grand">
                <span class="label">GRAND TOTAL</span>
                <span class="value">Rs. {{ number_format($order->total, 2) }}</span>
            </div>

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
                    <div class="summary-row" style="background:#f0fdf4;padding:6px 8px;border-radius:6px;margin-top:6px;">
                        <span class="label" style="color:#16a34a;font-weight:600;">Amount Paid</span>
                        <span class="value" style="color:#16a34a;">Rs. {{ number_format($paidAmount, 0) }}</span>
                    </div>
                @endif

                @if ($prevBalance > 0)
                    <div class="summary-row" style="background:#fff7ed;padding:5px 8px;border-radius:6px;margin-top:4px;">
                        <span class="label" style="color:#c2410c;font-size:12px;">Previous Balance</span>
                        <span class="value" style="color:#c2410c;font-size:12px;">Rs. {{ number_format($prevBalance, 0) }}</span>
                    </div>
                @elseif ($prevBalance < 0)
                    <div class="summary-row" style="background:#f0fdf4;padding:5px 8px;border-radius:6px;margin-top:4px;">
                        <span class="label" style="color:#16a34a;font-size:12px;">Previous Advance (پچھلی واپسی)</span>
                        <span class="value" style="color:#16a34a;font-size:12px;">Rs. {{ number_format(abs($prevBalance), 0) }}</span>
                    </div>
                    @if ($advanceUsed > 0)
                        <div class="summary-row" style="background:#eff6ff;padding:5px 8px;border-radius:6px;margin-top:4px;">
                            <span class="label" style="color:#2563eb;font-size:12px;">Adjusted from Advance</span>
                            <span class="value" style="color:#2563eb;font-size:12px;">- Rs. {{ number_format($advanceUsed, 0) }}</span>
                        </div>
                    @endif
                @endif

                @if ($balanceOnBill > 0 && $prevBalance >= 0)
                    <div class="summary-row" style="background:#fef2f2;padding:6px 8px;border-radius:6px;margin-top:4px;">
                        <span class="label" style="color:#dc2626;font-weight:600;">Balance on Bill</span>
                        <span class="value" style="color:#dc2626;">Rs. {{ number_format($balanceOnBill, 0) }}</span>
                    </div>
                @endif

                @if ($currentBalance > 0)
                    <div class="summary-row" style="background:#fefce8;padding:8px;border-radius:6px;margin-top:4px;border:1.5px solid #fbbf24;">
                        <span class="label" style="color:#a16207;font-weight:800;">Current Balance Due</span>
                        <span class="value" style="color:#a16207;font-weight:800;">Rs. {{ number_format($currentBalance, 0) }}</span>
                    </div>
                @elseif ($currentBalance < 0)
                    <div class="summary-row" style="background:#f0fdf4;padding:8px;border-radius:6px;margin-top:4px;border:1.5px solid #4ade80;">
                        <span class="label" style="color:#16a34a;font-weight:800;">Change Due (واپسی)</span>
                        <span class="value" style="color:#16a34a;font-weight:800;">Rs. {{ number_format(abs($currentBalance), 0) }}</span>
                    </div>
                @elseif ($prevBalance != 0)
                    <div class="summary-row" style="background:#f0fdf4;padding:8px;border-radius:6px;margin-top:4px;border:1.5px solid #4ade80;text-align:center;">
                        <span style="color:#16a34a;font-weight:800;width:100%;text-align:center;">✅ All Settled (حساب برابر)</span>
                    </div>
                @endif
            @endif

        </div>

        <!-- Return summary -->
        @if($hasAnyReturn)
        <div style="margin:12px 0;background:#fef2f2;border:1.5px solid #fca5a5;border-radius:8px;padding:12px 14px;">
            <div style="font-size:12px;font-weight:700;color:#dc2626;margin-bottom:6px;">
                <i class="fas fa-undo" style="margin-right:4px;"></i> Return Summary
            </div>
            @foreach($order->refunds->where('status','completed') as $refund)
            <div style="font-size:11px;color:#6b7280;margin-bottom:3px;">
                {{ $refund->refund_number ?? 'Refund' }}
                — {{ $refund->created_at->format('d M Y') }}
                — Rs. {{ number_format($refund->amount, 0) }}
                @if($refund->items)
                ({{ collect($refund->items)->map(fn($i) => ($i['name'] ?? 'Item').' ×'.$i['quantity'])->implode(', ') }})
                @endif
            </div>
            @endforeach
            <div style="border-top:1px solid #fca5a5;margin-top:6px;padding-top:6px;display:flex;justify-content:space-between;">
                <span style="font-size:12px;font-weight:700;color:#dc2626;">Total Returned</span>
                <span style="font-size:12px;font-weight:700;color:#dc2626;">− Rs. {{ number_format($totalRefunded, 0) }}</span>
            </div>
            <div style="display:flex;justify-content:space-between;margin-top:4px;">
                <span style="font-size:13px;font-weight:800;color:#1e293b;">Amount Payable</span>
                <span style="font-size:13px;font-weight:800;color:#1e293b;">Rs. {{ number_format($effectiveTotal, 0) }}</span>
            </div>
        </div>
        @endif

        <!-- Notes -->
        @if ($order->notes)
            <div class="section-title">Order Notes</div>
            <div class="notes-section">{{ $order->notes }}</div>
        @endif

        <!-- Footer -->
        <div class="receipt-footer">
            <p>Thank you for your business!</p>
            <p>Items can be returned within 7 days with receipt</p>
            <p style="margin-top:8px;font-size:11px;">{{ $order->branch->name ?? 'AlMufeed Saqafti Markaz' }} | www.almufeed.com.pk | {{ $order->branch->phone ?? '03007951919' }}</p>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="actions-wrap no-print">
        <div class="actions-grid">
            <button onclick="window.print()" class="act-btn print-btn">
                <i class="fas fa-print"></i> Print
            </button>
            <a href="{{ route('public.receipt.download', $order->receipt_token) }}" class="act-btn pdf-btn">
                <i class="fas fa-file-pdf"></i> PDF
            </a>
            <button onclick="downloadAsJPG()" class="act-btn jpg-btn">
                <i class="fas fa-image"></i> JPG
            </button>
        </div>
    </div>

    <script>
        // Print shortcut
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey && e.key === 'p') { e.preventDefault(); window.print(); }
            if (e.ctrlKey && e.shiftKey && e.key === 'J') { e.preventDefault(); downloadAsJPG(); }
        });

        function downloadAsJPG() {
            const overlay = document.getElementById('loadingOverlay');
            document.getElementById('loadingText').textContent = 'Converting to JPG...';
            overlay.classList.add('show');

            const actions = document.querySelector('.actions-wrap');
            if (actions) actions.style.display = 'none';

            setTimeout(() => {
                html2canvas(document.getElementById('receiptContent'), {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#ffffff',
                    logging: false,
                    allowTaint: true
                }).then(canvas => {
                    if (actions) actions.style.display = '';
                    const link = document.createElement('a');
                    link.download = `Receipt-{{ $order->order_number }}-${new Date().toISOString().split('T')[0]}.jpg`;
                    link.href = canvas.toDataURL('image/jpeg', 0.95);
                    link.click();
                    overlay.classList.remove('show');
                }).catch(err => {
                    console.error('JPG conversion error:', err);
                    alert('Failed to convert. Please try again.');
                    if (actions) actions.style.display = '';
                    overlay.classList.remove('show');
                });
            }, 100);
        }
    </script>
</body>

</html>
