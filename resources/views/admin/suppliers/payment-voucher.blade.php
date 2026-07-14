@extends('layouts.admin')

@php $isReceipt = $isReceipt ?? (($payment->direction ?? 'out') === 'in'); @endphp

@section('title', ($isReceipt ? 'Cash Receipt — ' : 'Payment Voucher — ') . $payment->payment_number)

@push('styles')
<style>
    .voucher-wrap { max-width: 520px; margin: 0 auto; padding: 0; }
    .voucher-card { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,.08); overflow: hidden; }

    .voucher-header { text-align: center; padding: 24px 20px 16px; border-bottom: 2px dashed #e5e7eb; }
    .voucher-header h1 { font-size: 20px; font-weight: 800; color: #1e293b; margin: 0 0 4px; }
    .voucher-header p { font-size: 13px; color: #6b7280; margin: 2px 0; }
    .voucher-badge { display: inline-block; background: #2563eb; color: #fff; padding: 4px 16px; border-radius: 20px; font-size: 13px; font-weight: 700; letter-spacing: .5px; margin-top: 10px; }
    .voucher-badge.receipt { background: #ea580c; }

    .amount-box.receipt { background: #fff7ed; border-color: #ea580c; }
    .amount-box.receipt .amount-label, .amount-box.receipt .amount-value { color: #ea580c; }

    .voucher-info { padding: 14px 20px; border-bottom: 1px solid #f1f5f9; }
    .info-row { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; font-size: 13px; }
    .info-row .label { font-weight: 600; color: #374151; }
    .info-row .value { color: #1e293b; text-align: right; font-weight: 500; }

    .amount-box { background: #eff6ff; border: 2px solid #3b82f6; border-radius: 12px; padding: 20px; text-align: center; margin: 16px 20px; }
    .amount-box .amount-label { font-size: 12px; font-weight: 600; color: #2563eb; text-transform: uppercase; letter-spacing: .5px; }
    .amount-box .amount-value { font-size: 32px; font-weight: 900; color: #2563eb; margin-top: 4px; font-family: ui-monospace, monospace; }

    .balance-section { margin: 0 20px 16px; background: #f8fafc; border-radius: 8px; padding: 12px 14px; }
    .balance-row { display: flex; justify-content: space-between; align-items: center; padding: 4px 0; font-size: 13px; }
    .balance-row .label { color: #6b7280; font-weight: 600; }
    .balance-row .value { font-weight: 700; font-family: ui-monospace, monospace; }
    .balance-row.after { border-top: 1.5px solid #e5e7eb; margin-top: 4px; padding-top: 8px; }
    .balance-row.after .label { color: #1e293b; font-weight: 800; }
    .balance-row.after .value { font-size: 15px; }

    .voucher-footer { text-align: center; padding: 16px 20px; border-top: 2px dashed #e5e7eb; color: #9ca3af; font-size: 12px; }
    .voucher-footer p { margin: 2px 0; }

    .voucher-actions { max-width: 520px; margin: 16px auto 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; }
    .v-btn { display: flex; align-items: center; justify-content: center; gap: 6px; padding: 10px 8px; border-radius: 8px; font-size: 13px; font-weight: 600; color: #fff; text-decoration: none; border: none; cursor: pointer; transition: opacity .15s; }
    .v-btn:hover { opacity: .85; }
    .v-btn i { font-size: 14px; }
    .v-btn.whatsapp { background: #16a34a; }
    .v-btn.print { background: #2563eb; }
    .v-btn.back { background: #6b7280; }

    @media (max-width: 640px) {
        .voucher-wrap { max-width: 100%; margin: 0; }
        .voucher-card { border-radius: 0; box-shadow: none; }
        .amount-box .amount-value { font-size: 26px; }
        .voucher-actions { padding: 0 8px; }
        .v-btn { padding: 10px 6px; font-size: 12px; }
    }

    @media (prefers-color-scheme: dark) {
        .voucher-card { background: #1f2937; }
        .voucher-header { border-color: #374151; }
        .voucher-header h1 { color: #f3f4f6; }
        .voucher-header p { color: #9ca3af; }
        .voucher-info { border-color: #374151; }
        .info-row .label { color: #9ca3af; }
        .info-row .value { color: #e5e7eb; }
        .amount-box { background: #1e3a5f; border-color: #60a5fa; }
        .amount-box .amount-label { color: #93c5fd; }
        .amount-box .amount-value { color: #93c5fd; }
        .balance-section { background: #111827; }
        .balance-row .label { color: #9ca3af; }
        .balance-row .value { color: #e5e7eb; }
        .balance-row.after .label { color: #f3f4f6; }
        .voucher-footer { border-color: #374151; color: #6b7280; }
        .v-btn.back { background: #374151; }
    }

    @media print {
        body * { visibility: hidden; }
        .print-content, .print-content * { visibility: visible; }
        .print-content { position: absolute; left: 0; top: 0; width: 100%; max-width: 100% !important; padding: 15px; background: white; }
        .voucher-card { box-shadow: none; border-radius: 0; }
        .no-print { display: none !important; }
    }
</style>
@endpush

@section('content')
    <div class="voucher-wrap print-content">
        <div class="voucher-card">

            <div class="voucher-header">
                <h1>ALMufeed Saqafti Markaz</h1>
                <p>www.almufeed.com.pk | 03007951919</p>
                <div class="voucher-badge {{ $isReceipt ? 'receipt' : '' }}">
                    {{ $isReceipt ? 'SUPPLIER CASH RECEIPT (وصولی)' : 'SUPPLIER PAYMENT VOUCHER (سپلائر ادائیگی)' }}
                </div>
            </div>

            <div class="voucher-info">
                <div class="info-row">
                    <span class="label">Voucher #:</span>
                    <span class="value">{{ $payment->payment_number }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Date:</span>
                    <span class="value">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Supplier:</span>
                    <span class="value">{{ $supplier->name }}</span>
                </div>
                @if($supplier->company_name)
                    <div class="info-row">
                        <span class="label">Company:</span>
                        <span class="value">{{ $supplier->company_name }}</span>
                    </div>
                @endif
                @if($supplier->phone)
                    <div class="info-row">
                        <span class="label">Phone:</span>
                        <span class="value">{{ $supplier->phone }}</span>
                    </div>
                @endif
                <div class="info-row">
                    <span class="label">{{ $isReceipt ? 'Receipt Method:' : 'Payment Method:' }}</span>
                    <span class="value">{{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}</span>
                </div>
                @if(!$isReceipt && $payment->purchase)
                    <div class="info-row">
                        <span class="label">Against Invoice:</span>
                        <span class="value">{{ $payment->purchase->invoice_number }}</span>
                    </div>
                @endif
                @if($payment->notes)
                    <div class="info-row">
                        <span class="label">Notes:</span>
                        <span class="value">{{ $payment->notes }}</span>
                    </div>
                @endif
            </div>

            <div class="amount-box {{ $isReceipt ? 'receipt' : '' }}">
                <div class="amount-label">
                    {{ $isReceipt ? 'Amount Received (وصول شدہ رقم)' : 'Amount Paid (ادا شدہ رقم)' }}
                </div>
                <div class="amount-value">Rs. {{ number_format($payment->amount, 0) }}</div>
            </div>

            <div class="balance-section">
                <div class="balance-row">
                    <span class="label">Balance Before</span>
                    <span class="value" style="color:{{ $balanceBefore > 0 ? '#dc2626' : '#16a34a' }}">
                        Rs. {{ number_format(abs($balanceBefore), 0) }}
                        {{ $balanceBefore > 0 ? '(Due)' : ($balanceBefore < 0 ? '(Advance)' : '') }}
                    </span>
                </div>
                <div class="balance-row">
                    <span class="label">{{ $isReceipt ? 'Amount Received' : 'Amount Paid' }}</span>
                    <span class="value" style="color:{{ $isReceipt ? '#ea580c' : '#16a34a' }}">
                        {{ $isReceipt ? '+' : '-' }} Rs. {{ number_format($payment->amount, 0) }}
                    </span>
                </div>
                <div class="balance-row after">
                    <span class="label">Balance After</span>
                    <span class="value" style="color:{{ $balanceAfter > 0 ? '#dc2626' : '#16a34a' }}">
                        @if($balanceAfter == 0)
                            <span style="color:#16a34a">Rs. 0 — Settled (حساب برابر)</span>
                        @elseif($balanceAfter > 0)
                            Rs. {{ number_format($balanceAfter, 0) }} (Due)
                        @else
                            Rs. {{ number_format(abs($balanceAfter), 0) }} (Advance)
                        @endif
                    </span>
                </div>
            </div>

            <div class="voucher-footer">
                <p>{{ $isReceipt ? 'Cash receipt recorded successfully.' : 'Payment recorded successfully.' }}</p>
                <p>This is a computer-generated voucher.</p>
                <p style="margin-top:6px;font-size:10px;color:#c9c9c9">{{ $payment->payment_number }}</p>
            </div>

        </div>
    </div>

    <div class="voucher-actions no-print">
        <button id="whatsapp-share" class="v-btn whatsapp">
            <i class="fab fa-whatsapp"></i> WhatsApp
        </button>
        <button onclick="window.print()" class="v-btn print">
            <i class="fas fa-print"></i> Print
        </button>
        <a href="{{ route('suppliers.ledger', $supplier) }}" class="v-btn back">
            <i class="fas fa-arrow-left"></i> Ledger
        </a>
    </div>
@endsection

@push('scripts')
<script>
    document.getElementById('whatsapp-share').addEventListener('click', function() {
        let phone = "{{ $supplier->phone ?? '' }}";
        if (!phone) {
            phone = prompt("Enter supplier phone number (with country code, e.g., 923001234567):", "92");
            if (!phone) { alert("Phone number required."); return; }
        }
        phone = phone.replace(/\D/g, '');
        if (!phone.startsWith('92')) {
            phone = phone.startsWith('0') ? '92' + phone.substring(1) : '92' + phone;
        }

        let msg = `*AlMufeed Saqafti Markaz*\n`;
        msg += `*{{ $isReceipt ? 'Supplier Cash Receipt' : 'Supplier Payment Voucher' }}*\n\n`;
        msg += `*Voucher #*: {{ $payment->payment_number }}\n`;
        msg += `*Date*: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M, Y') }}\n`;
        msg += `*Supplier*: {{ $supplier->name }}\n\n`;
        msg += `*{{ $isReceipt ? 'Amount Received' : 'Amount Paid' }}*: Rs. {{ number_format($payment->amount, 0) }}\n`;
        msg += `*Method*: {{ ucfirst(str_replace('_', ' ', $payment->payment_method)) }}\n\n`;
        msg += `*Balance Before*: Rs. {{ number_format(abs($balanceBefore), 0) }}{{ $balanceBefore > 0 ? ' (Due)' : ($balanceBefore < 0 ? ' (Advance)' : '') }}\n`;
        msg += `*Balance After*: {{ $balanceAfter == 0 ? 'Rs. 0 — Settled' : 'Rs. ' . number_format(abs($balanceAfter), 0) . ($balanceAfter > 0 ? ' (Due)' : ' (Advance)') }}\n\n`;
        msg += `AlMufeed Saqafti Markaz\n03007951919`;

        window.open(`https://api.whatsapp.com/send?phone=${phone}&text=${encodeURIComponent(msg)}`, '_blank');
    });
</script>
@endpush
