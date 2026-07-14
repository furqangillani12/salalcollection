@extends('layouts.admin')

@section('title', 'Purchase Invoice — ' . $purchase->invoice_number)

@push('styles')
<style>
    .invoice-wrap {
        max-width: 700px;
        margin: 0 auto;
        padding: 0;
    }

    .invoice-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,.08);
        overflow: hidden;
    }

    /* ── Header ── */
    .inv-header {
        text-align: center;
        padding: 24px 24px 16px;
        border-bottom: 2px dashed #e5e7eb;
    }

    .inv-header h1 {
        font-size: 22px;
        font-weight: 800;
        color: #1e293b;
        margin: 0 0 4px;
    }

    .inv-header p {
        font-size: 13px;
        color: #6b7280;
        margin: 2px 0;
    }

    .inv-badge {
        display: inline-block;
        background: #2563eb;
        color: #fff;
        padding: 4px 18px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .5px;
        margin-top: 10px;
    }

    /* ── Info Section ── */
    .inv-info {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        padding: 18px 24px;
        border-bottom: 1px solid #f1f5f9;
    }

    .inv-info-block h4 {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .5px;
        color: #9ca3af;
        margin: 0 0 8px;
    }

    .inv-info-block .info-line {
        font-size: 13px;
        color: #374151;
        padding: 2px 0;
    }

    .inv-info-block .info-line strong {
        color: #1e293b;
    }

    /* ── Items Table ── */
    .inv-table-wrap {
        padding: 0 24px;
    }

    .inv-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .inv-table thead th {
        background: #f8fafc;
        padding: 10px 8px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .3px;
        color: #6b7280;
        border-bottom: 2px solid #e5e7eb;
    }

    .inv-table thead th:last-child,
    .inv-table thead th:nth-child(3),
    .inv-table thead th:nth-child(4) {
        text-align: right;
    }

    .inv-table tbody td {
        padding: 10px 8px;
        border-bottom: 1px solid #f1f5f9;
        color: #374151;
    }

    .inv-table tbody td:last-child,
    .inv-table tbody td:nth-child(3),
    .inv-table tbody td:nth-child(4) {
        text-align: right;
        font-family: ui-monospace, monospace;
    }

    .inv-table tbody td:nth-child(2) {
        text-align: center;
    }

    .inv-table thead th:nth-child(2) {
        text-align: center;
    }

    .inv-table tbody tr:last-child td {
        border-bottom: none;
    }

    .product-name {
        font-weight: 600;
        color: #1e293b;
    }

    .product-barcode {
        font-size: 11px;
        color: #9ca3af;
        font-family: ui-monospace, monospace;
    }

    /* ── Totals ── */
    .inv-totals {
        padding: 16px 24px;
        border-top: 2px solid #e5e7eb;
    }

    .total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
        font-size: 13px;
    }

    .total-row .label {
        font-weight: 600;
        color: #374151;
    }

    .total-row .value {
        font-weight: 700;
        font-family: ui-monospace, monospace;
        color: #1e293b;
    }

    .total-row.grand {
        border-top: 2px solid #1e293b;
        margin-top: 6px;
        padding-top: 10px;
    }

    .total-row.grand .label {
        font-size: 16px;
        font-weight: 800;
        color: #1e293b;
    }

    .total-row.grand .value {
        font-size: 18px;
        font-weight: 900;
        color: #1e293b;
    }

    /* ── Payment Status ── */
    .payment-status-box {
        margin: 16px 24px;
        border-radius: 10px;
        padding: 14px 16px;
    }

    .payment-status-box.paid {
        background: #f0fdf4;
        border: 2px solid #22c55e;
    }

    .payment-status-box.partial {
        background: #fffbeb;
        border: 2px solid #f59e0b;
    }

    .payment-status-box.unpaid {
        background: #fef2f2;
        border: 2px solid #ef4444;
    }

    .ps-row {
        display: flex;
        justify-content: space-between;
        padding: 3px 0;
        font-size: 13px;
    }

    .ps-row .label { font-weight: 600; }
    .ps-row .value { font-weight: 700; font-family: ui-monospace, monospace; }

    /* ── Notes ── */
    .inv-notes {
        margin: 0 24px 16px;
        background: #f8fafc;
        border-radius: 8px;
        padding: 12px 14px;
        font-size: 13px;
        color: #6b7280;
    }

    .inv-notes strong {
        color: #374151;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    /* ── Footer ── */
    .inv-footer {
        text-align: center;
        padding: 16px 24px;
        border-top: 2px dashed #e5e7eb;
        color: #9ca3af;
        font-size: 12px;
    }

    .inv-footer p { margin: 2px 0; }

    /* ── Buttons ── */
    .inv-actions {
        max-width: 700px;
        margin: 16px auto 0;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
    }

    .inv-btn {
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
        transition: opacity .15s;
    }

    .inv-btn:hover { opacity: .85; }
    .inv-btn i { font-size: 14px; }
    .inv-btn.whatsapp { background: #16a34a; }
    .inv-btn.print { background: #2563eb; }
    .inv-btn.back { background: #6b7280; }

    /* ── Mobile ── */
    @media (max-width: 640px) {
        .invoice-wrap { max-width: 100%; margin: 0; }
        .invoice-card { border-radius: 0; box-shadow: none; }
        .inv-info { grid-template-columns: 1fr; gap: 10px; }
        .inv-header { padding: 16px 14px 12px; }
        .inv-header h1 { font-size: 18px; }
        .inv-table-wrap { padding: 0 12px; }
        .inv-totals { padding: 14px 12px; }
        .inv-actions { padding: 0 8px; }
        .inv-btn { padding: 10px 6px; font-size: 12px; }
    }

    /* ── Dark Mode ── */
    @media (prefers-color-scheme: dark) {
        .invoice-card { background: #1f2937; }
        .inv-header { border-color: #374151; }
        .inv-header h1 { color: #f3f4f6; }
        .inv-header p { color: #9ca3af; }
        .inv-info { border-color: #374151; }
        .inv-info-block h4 { color: #6b7280; }
        .inv-info-block .info-line { color: #d1d5db; }
        .inv-info-block .info-line strong { color: #f3f4f6; }
        .inv-table thead th { background: #111827; color: #9ca3af; border-color: #374151; }
        .inv-table tbody td { color: #d1d5db; border-color: #374151; }
        .product-name { color: #f3f4f6; }
        .product-barcode { color: #6b7280; }
        .inv-totals { border-color: #374151; }
        .total-row .label { color: #9ca3af; }
        .total-row .value { color: #e5e7eb; }
        .total-row.grand .label,
        .total-row.grand .value { color: #f3f4f6; }
        .total-row.grand { border-color: #6b7280; }
        .inv-notes { background: #111827; color: #9ca3af; }
        .inv-notes strong { color: #d1d5db; }
        .inv-footer { border-color: #374151; color: #6b7280; }
        .inv-btn.back { background: #374151; }
        .payment-status-box.paid { background: #064e3b; border-color: #4ade80; }
        .payment-status-box.partial { background: #451a03; border-color: #fbbf24; }
        .payment-status-box.unpaid { background: #450a0a; border-color: #f87171; }
    }

    /* ── Print ── */
    @media print {
        body * { visibility: hidden; }
        .print-content, .print-content * { visibility: visible; }
        .print-content {
            position: absolute;
            left: 0; top: 0;
            width: 100%;
            max-width: 100% !important;
            padding: 15px;
            background: white;
        }
        .invoice-card { box-shadow: none; border-radius: 0; }
        .no-print { display: none !important; }
    }
</style>
@endpush

@section('content')
    <div class="invoice-wrap print-content">
        <div class="invoice-card">

            {{-- Header --}}
            <div class="inv-header">
                <h1>ALMufeed Saqafti Markaz</h1>
                <p>www.almufeed.com.pk | 03007951919</p>
                <div class="inv-badge">PURCHASE INVOICE (خریداری انوائس)</div>
            </div>

            {{-- Info: Supplier + Invoice --}}
            <div class="inv-info">
                <div class="inv-info-block">
                    <h4>Supplier (سپلائر)</h4>
                    <div class="info-line"><strong>{{ $purchase->supplier->name }}</strong></div>
                    @if($purchase->supplier->phone)
                        <div class="info-line">Ph: {{ $purchase->supplier->phone }}</div>
                    @endif
                    @if($purchase->supplier->email)
                        <div class="info-line">{{ $purchase->supplier->email }}</div>
                    @endif
                    @if($purchase->supplier->address)
                        <div class="info-line">{{ $purchase->supplier->address }}</div>
                    @endif
                </div>
                <div class="inv-info-block" style="text-align:right;">
                    <h4>Invoice Details</h4>
                    <div class="info-line"><strong>{{ $purchase->invoice_number }}</strong></div>
                    <div class="info-line">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') }}</div>
                    <div class="info-line">{{ $purchase->items->sum('quantity') }} item(s)</div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="inv-table-wrap">
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th style="width:45%">Product</th>
                            <th style="width:12%">Qty</th>
                            <th style="width:20%">Rate</th>
                            <th style="width:23%">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $item)
                            <tr>
                                <td>
                                    <span class="product-name">{{ $item->product->name ?? 'Deleted' }}</span>
                                    @if($item->product?->barcode)
                                        <br><span class="product-barcode">{{ $item->product->barcode }}</span>
                                    @endif
                                </td>
                                <td style="text-align:center">{{ $item->quantity }}</td>
                                <td style="text-align:right">Rs. {{ number_format($item->unit_price, 0) }}</td>
                                <td style="text-align:right;font-weight:600">Rs. {{ number_format($item->total_price, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="inv-totals">
                @php $itemsSubtotal = $purchase->items->sum('total_price'); @endphp
                @if(!empty($purchase->expenses) || ($purchase->discount ?? 0) > 0)
                    <div class="total-row">
                        <span class="label" style="color:#6b7280">Items Subtotal</span>
                        <span class="value" style="color:#6b7280">Rs. {{ number_format($itemsSubtotal, 0) }}</span>
                    </div>
                    @foreach(($purchase->expenses ?? []) as $exp)
                        <div class="total-row">
                            <span class="label" style="color:#b45309">+ {{ $exp['label'] ?? 'Expense' }} (اخراجات)</span>
                            <span class="value" style="color:#b45309">Rs. {{ number_format($exp['amount'], 0) }}</span>
                        </div>
                    @endforeach
                    @if(($purchase->discount ?? 0) > 0)
                        <div class="total-row">
                            <span class="label" style="color:#16a34a">− Discount (ڈسکاؤنٹ)</span>
                            <span class="value" style="color:#16a34a">Rs. {{ number_format($purchase->discount, 0) }}</span>
                        </div>
                    @endif
                @endif
                @if(($previousBalance ?? 0) > 0)
                    <div class="total-row" style="color:#c2410c;">
                        <span class="label">Previous Balance (سابقہ بقایا)</span>
                        <span class="value">Rs. {{ number_format($previousBalance, 0) }}</span>
                    </div>
                @endif
                <div class="total-row grand">
                    <span class="label">Invoice Amount (رقم انوائس)</span>
                    <span class="value">Rs. {{ number_format($purchase->total_amount, 0) }}</span>
                </div>
                @if(($previousBalance ?? 0) > 0)
                    <div class="total-row grand" style="border-top:2px solid #1e293b;">
                        <span class="label">Grand Total (کل رقم)</span>
                        <span class="value">Rs. {{ number_format($purchase->total_amount + $previousBalance, 0) }}</span>
                    </div>
                @endif
            </div>

            {{-- Payment Status --}}
            @php
                $balance = $purchase->total_amount - $purchase->paid_amount;
                $totalOwed = $balance + ($previousBalance ?? 0);
                $statusClass = $purchase->payment_status === 'paid' ? 'paid' : ($purchase->payment_status === 'partial' ? 'partial' : 'unpaid');
            @endphp
            <div class="payment-status-box {{ $statusClass }}">
                <div class="ps-row">
                    <span class="label">Payment Status</span>
                    <span class="value">
                        @if($purchase->payment_status === 'paid')
                            Paid (ادائیگی مکمل)
                        @elseif($purchase->payment_status === 'partial')
                            Partial (جزوی ادائیگی)
                        @else
                            Unpaid (غیر ادا شدہ)
                        @endif
                    </span>
                </div>
                <div class="ps-row">
                    <span class="label">Paid Amount</span>
                    <span class="value" style="color:#16a34a">Rs. {{ number_format($purchase->paid_amount, 0) }}</span>
                </div>
                @if($balance > 0)
                    <div class="ps-row" style="border-top:1px solid rgba(0,0,0,.1);margin-top:4px;padding-top:6px;">
                        <span class="label" style="font-weight:800">Balance Due (بقایا)</span>
                        <span class="value" style="color:#dc2626;font-size:15px">Rs. {{ number_format($balance, 0) }}</span>
                    </div>
                @elseif($balance == 0 && $purchase->paid_amount > 0)
                    <div class="ps-row" style="border-top:1px solid rgba(0,0,0,.1);margin-top:4px;padding-top:6px;">
                        <span class="label" style="font-weight:800">Status</span>
                        <span class="value" style="color:#16a34a">Fully Settled (حساب برابر)</span>
                    </div>
                @endif
                @if(($previousBalance ?? 0) > 0 && $totalOwed > 0)
                    <div class="ps-row" style="border-top:2px solid rgba(0,0,0,.15);margin-top:6px;padding-top:8px;">
                        <span class="label" style="font-weight:900;font-size:13px;">Total Owed (کل واجبات)</span>
                        <span class="value" style="color:#dc2626;font-size:16px;font-weight:900;">Rs. {{ number_format($totalOwed, 0) }}</span>
                    </div>
                @endif
            </div>

            {{-- Notes --}}
            @if($purchase->notes)
                <div class="inv-notes">
                    <strong>Notes:</strong><br>
                    {{ $purchase->notes }}
                </div>
            @endif

            {{-- Footer --}}
            <div class="inv-footer">
                <p>ALMufeed Saqafti Markaz — Purchase Record</p>
                <p>This is a computer-generated invoice.</p>
                <p style="margin-top:6px;font-size:10px;color:#c9c9c9">Generated on {{ now()->format('d M, Y h:i A') }}</p>
            </div>

        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="inv-actions no-print">
        <button id="whatsapp-share" class="inv-btn whatsapp">
            <i class="fab fa-whatsapp"></i> WhatsApp
        </button>
        <button onclick="window.print()" class="inv-btn print">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('suppliers.ledger', $purchase->supplier) }}" class="inv-btn back">
            <i class="fas fa-book"></i> Ledger
        </a>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('whatsapp-share').addEventListener('click', function() {
        let phone = "{{ $purchase->supplier->phone ?? '' }}";
        if (!phone) {
            phone = prompt("Enter supplier phone number (with country code, e.g., 923001234567):", "92");
            if (!phone) { alert("Phone number required."); return; }
        }
        phone = phone.replace(/\D/g, '');
        if (!phone.startsWith('92')) {
            phone = phone.startsWith('0') ? '92' + phone.substring(1) : '92' + phone;
        }

        let msg = `*AlMufeed Saqafti Markaz*\n`;
        msg += `*Purchase Invoice (خریداری انوائس)*\n\n`;
        msg += `*Invoice #*: {{ $purchase->invoice_number }}\n`;
        msg += `*Date*: {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M, Y') }}\n`;
        msg += `*Supplier*: {{ $purchase->supplier->name }}\n\n`;
        msg += `*Items:*\n`;
        @foreach($purchase->items as $item)
        msg += `• {{ $item->product->name ?? 'Item' }} × {{ $item->quantity }} = Rs. {{ number_format($item->total_price, 0) }}\n`;
        @endforeach
        msg += `\n*Total*: Rs. {{ number_format($purchase->total_amount, 0) }}\n`;
        msg += `*Paid*: Rs. {{ number_format($purchase->paid_amount, 0) }}\n`;
        @if($purchase->total_amount - $purchase->paid_amount > 0)
        msg += `*Balance Due*: Rs. {{ number_format($purchase->total_amount - $purchase->paid_amount, 0) }}\n`;
        @else
        msg += `*Status*: Fully Paid ✅\n`;
        @endif
        msg += `\nAlMufeed Saqafti Markaz\n03007951919`;

        window.open(`https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(msg)}`, '_blank');
    });
</script>
@endpush
