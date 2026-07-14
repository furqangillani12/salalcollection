@extends('layouts.admin')

@section('title', 'Combined Statement — ' . $customer->name)

@section('content')
<div class="p-3 sm:p-6">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
        <div>
            <a href="{{ route('admin.customers.khata', $customer) }}"
               class="inline-flex items-center gap-1.5 text-xs text-gray-500 hover:text-cyan-700 mb-2">
                <i class="fas fa-arrow-left"></i> Back to khata
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-file-invoice" style="color:#0891b2;"></i>
                Combined Statement
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                Customer <strong>{{ $customer->name }}</strong>
                <span class="mx-2 text-gray-300">↔</span>
                Supplier <strong>{{ $supplier->name }}</strong>
            </p>
        </div>

        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 mb-1">From</label>
                <input type="date" name="from_date" value="{{ $from }}"
                       class="px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-600 mb-1">To</label>
                <input type="date" name="to_date" value="{{ $to }}"
                       class="px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <button type="submit" class="px-4 py-1.5 text-white text-sm font-semibold rounded-md"
                    style="background:linear-gradient(135deg,#0891b2,#0e7490);">
                <i class="fas fa-filter mr-1"></i> Apply
            </button>
        </form>
    </div>

    {{-- ── Balance summary ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-rose-200 p-4 shadow-sm">
            <div class="text-[11px] uppercase tracking-wide text-rose-700 font-semibold">Customer Lena (لینا)</div>
            <div class="text-2xl font-extrabold text-rose-700 mt-1">Rs. {{ number_format(max(0, $custBalance), 0) }}</div>
            @if ($custBalance < 0)
                <div class="text-[10px] text-emerald-600">Advance: Rs. {{ number_format(abs($custBalance), 0) }} (پیشگی)</div>
            @endif
        </div>
        <div class="bg-white rounded-xl border border-amber-200 p-4 shadow-sm">
            <div class="text-[11px] uppercase tracking-wide text-amber-700 font-semibold">Supplier Dena (دینا)</div>
            <div class="text-2xl font-extrabold text-amber-700 mt-1">Rs. {{ number_format(max(0, $supBalance), 0) }}</div>
            @if ($supBalance < 0)
                <div class="text-[10px] text-emerald-600">Advance: Rs. {{ number_format(abs($supBalance), 0) }} (پیشگی)</div>
            @endif
        </div>
        <div class="bg-white rounded-xl border-2 p-4 shadow-sm col-span-2 sm:col-span-1"
             style="border-color:{{ $netBalance > 0 ? '#fda4af' : ($netBalance < 0 ? '#fcd34d' : '#86efac') }};">
            <div class="text-[11px] uppercase tracking-wide font-semibold"
                 style="color:{{ $netBalance > 0 ? '#be123c' : ($netBalance < 0 ? '#92400e' : '#047857') }};">
                Net Position
            </div>
            <div class="text-2xl font-extrabold mt-1"
                 style="color:{{ $netBalance > 0 ? '#be123c' : ($netBalance < 0 ? '#92400e' : '#047857') }};">
                Rs. {{ number_format(abs($netBalance), 0) }}
            </div>
            <div class="text-[10px] mt-0.5">
                @if ($netBalance > 0)
                    Net Lena (ہمارا لینا)
                @elseif ($netBalance < 0)
                    Net Dena (ہمارا دینا)
                @else
                    Hisaab saaf (حساب صاف)
                @endif
            </div>
        </div>
    </div>

    {{-- ── Timeline ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr class="text-left text-[11px] uppercase tracking-wide text-gray-600">
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Side</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Reference / Notes</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-right">Running Net</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rows as $r)
                        @php
                            $isCust = $r['side'] === 'customer';
                            $kindLabels = [
                                'sale'                  => ['Sale',                   'rose'],
                                'khata_payment'         => ['Khata payment',          'emerald'],
                                'cash_out_to_customer'  => ['Cash out to customer',   'amber'],
                                'offset_credit'         => ['Adjust (customer side)', 'cyan'],
                                'purchase'              => ['Purchase',               'amber'],
                                'supplier_payment'      => ['Paid supplier',          'emerald'],
                                'supplier_refund'       => ['Refund from supplier',   'amber'],
                                'offset_debit'          => ['Adjust (supplier side)', 'cyan'],
                            ];
                            [$label, $color] = $kindLabels[$r['kind']] ?? [$r['kind'], 'gray'];
                            $colorMap = [
                                'rose'    => '#be123c',
                                'amber'   => '#92400e',
                                'emerald' => '#047857',
                                'cyan'    => '#0e7490',
                                'gray'    => '#6b7280',
                            ];
                            $textColor = $colorMap[$color];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-xs text-gray-700 whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($r['date'])->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($isCust)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-pink-100 text-pink-700"><i class="fas fa-user text-[9px]"></i> Customer</span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-700"><i class="fas fa-truck text-[9px]"></i> Supplier</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-semibold" style="color:{{ $textColor }};">{{ $label }}</td>
                            <td class="px-4 py-3 text-xs text-gray-600">
                                <span class="font-mono text-gray-500">{{ $r['reference'] }}</span>
                                @if (!empty($r['desc']))
                                    <div class="text-gray-400 truncate max-w-md" title="{{ $r['desc'] }}">{{ $r['desc'] }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-bold whitespace-nowrap"
                                style="color:{{ $r['effect'] > 0 ? '#be123c' : '#047857' }};">
                                {{ $r['effect'] > 0 ? '+' : '−' }} Rs. {{ number_format($r['amount'], 0) }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold whitespace-nowrap bg-yellow-50"
                                style="color:{{ $r['running_net'] > 0 ? '#be123c' : ($r['running_net'] < 0 ? '#92400e' : '#047857') }};">
                                Rs. {{ number_format(abs($r['running_net']), 0) }}
                                @if ($r['running_net'] < 0) <span class="text-[10px] font-normal">(دینا)</span>
                                @elseif ($r['running_net'] > 0) <span class="text-[10px] font-normal">(لینا)</span>
                                @else <span class="text-[10px] font-normal">(saaf)</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-gray-400">
                                <i class="fas fa-inbox text-3xl mb-2 block"></i>
                                No activity in this date range.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-center text-[11px] text-gray-400 mt-4">
        Effect column: <span class="text-rose-600 font-semibold">+</span> = aap ka <strong>Lena</strong> barhta hai (لینا زیادہ);
        <span class="text-emerald-600 font-semibold">−</span> = aap ka <strong>Dena</strong> barhta hai (دینا زیادہ).
    </p>
</div>
@endsection
