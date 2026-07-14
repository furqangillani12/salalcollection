@extends('shop.layouts.app')
@section('title', 'Account statement')
@section('content')
<section class="py-10 sm:py-14">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-8 reveal">
            <div>
                <a href="{{ route('shop.account') }}" class="text-xs text-gray-500 hover:text-blue-700 inline-flex items-center gap-2 mb-2"><i class="fas fa-arrow-left"></i> Back to account</a>
                <h1 class="display text-3xl sm:text-4xl font-bold">Account statement</h1>
                <p class="text-gray-500 text-sm mt-1">
                    {{ $customer->name }}
                    <span class="chip ml-1" style="background:#e8f1fb;color:var(--rose);">{{ $customer->type_label }}</span>
                </p>
            </div>
        </div>

        {{-- Summary cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6 reveal-stagger">
            <div class="bg-white border border-gray-100 rounded-2xl p-5">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Total purchased</div>
                <div class="text-2xl font-extrabold mt-1" style="color:var(--brand-navy);">{{ shop_price($summary['business']) }}</div>
                <div class="text-[11px] text-gray-400 mt-0.5">{{ $summary['orders'] }} {{ \Str::plural('order', $summary['orders']) }}</div>
            </div>
            <div class="bg-white border border-gray-100 rounded-2xl p-5">
                <div class="text-xs text-gray-500 uppercase tracking-wide">Total paid</div>
                <div class="text-2xl font-extrabold mt-1 text-emerald-600">{{ shop_price($summary['paid']) }}</div>
            </div>
            <div class="rounded-2xl p-5 text-white" style="background:linear-gradient(135deg,var(--rose),var(--rose-deep));">
                <div class="text-xs uppercase tracking-wide opacity-90">Remaining (khata)</div>
                <div class="text-2xl font-extrabold mt-1">{{ shop_price(abs($summary['outstanding'])) }}</div>
                <div class="text-[11px] opacity-80 mt-0.5">
                    @if ($summary['outstanding'] > 0) You owe this balance
                    @elseif ($summary['outstanding'] < 0) Advance in your favour
                    @else All clear — nothing due @endif
                </div>
            </div>
        </div>

        {{-- Date filter --}}
        <form method="GET" class="flex flex-wrap items-end gap-3 mb-6 no-print reveal">
            <div>
                <label class="block text-[11px] font-semibold text-gray-500 mb-1">From</label>
                <input type="date" name="from" value="{{ request('from') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-gray-500 mb-1">To</label>
                <input type="date" name="to" value="{{ request('to') }}" class="px-3 py-2 border border-gray-200 rounded-lg text-sm">
            </div>
            <button class="btn btn-dark !py-2 !text-xs">Filter</button>
            @if (request('from') || request('to'))
                <a href="{{ route('shop.account.statement') }}" class="text-xs text-blue-500 hover:underline">Clear</a>
            @endif
        </form>

        {{-- Statement table --}}
        <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden reveal">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                <h2 class="font-bold text-gray-800">Transaction history</h2>
                <span class="text-xs text-gray-400">{{ count($rows) }} entries</span>
            </div>
            @if (count($rows))
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-white text-[11px] uppercase tracking-wider text-gray-500 border-b border-gray-100">
                            <tr>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Reference</th>
                                <th class="px-4 py-3 text-left">Details</th>
                                <th class="px-4 py-3 text-right">Bill</th>
                                <th class="px-4 py-3 text-right">Paid</th>
                                <th class="px-4 py-3 text-right">Balance</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($rows as $r)
                                @php
                                    $isOrder = $r['type'] === 'order';
                                    $label = match ($r['type']) {
                                        'order'   => 'Order',
                                        'payment' => 'Khata payment',
                                        'payout'  => 'Payout to you',
                                        'offset'  => 'Adjustment',
                                        default   => ucfirst($r['type']),
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ \Illuminate\Support\Carbon::parse($r['date'])->format('d M Y') }}</td>
                                    <td class="px-4 py-3">
                                        @if ($isOrder && $r['order'])
                                            <a href="{{ route('shop.account.order', $r['order']) }}" class="font-mono text-xs font-semibold hover:underline" style="color:var(--rose);">{{ $r['reference'] }}</a>
                                        @else
                                            <span class="font-mono text-xs text-gray-600">{{ $r['reference'] ?: '—' }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-600">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold
                                            {{ $isOrder ? 'bg-blue-50 text-blue-700' : ($r['type']==='payout' ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700') }}">{{ $label }}</span>
                                        @if ($isOrder)
                                            <span class="text-xs text-gray-500 ml-1">{{ $r['items_count'] }} {{ \Str::plural('item', $r['items_count']) }} · {{ $r['channel'] }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap">{{ $isOrder ? shop_price($r['amount']) : '—' }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap text-emerald-600">{{ $r['paid'] > 0 ? shop_price($r['paid']) : '—' }}</td>
                                    <td class="px-4 py-3 text-right whitespace-nowrap font-bold" style="color:var(--brand-navy);">{{ shop_price($r['running']) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 text-xs text-gray-500">
                                <td class="px-4 py-2" colspan="5">Opening balance (before this period)</td>
                                <td class="px-4 py-2 text-right font-semibold">{{ shop_price($summary['opening']) }}</td>
                            </tr>
                            <tr class="bg-gray-50 font-bold text-gray-800">
                                <td class="px-4 py-3" colspan="3">Totals</td>
                                <td class="px-4 py-3 text-right">{{ shop_price($summary['business']) }}</td>
                                <td class="px-4 py-3 text-right text-emerald-600">{{ shop_price($summary['paid']) }}</td>
                                <td class="px-4 py-3 text-right" style="color:var(--rose);">{{ shop_price($summary['outstanding']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @else
                <div class="p-12 text-center text-gray-500">
                    <i class="fas fa-file-invoice text-4xl text-gray-300 mb-3 block"></i>
                    No transactions in this period.
                    <a href="{{ route('shop.catalog') }}" class="font-semibold underline" style="color:var(--rose);">Start shopping</a>.
                </div>
            @endif
        </div>

        <p class="text-[11px] text-gray-400 mt-4 reveal">
            This statement matches our shop records. For any query please contact us @if (setting('site_phone')) at {{ setting('site_phone') }}@endif.
        </p>
    </div>
</section>
@endsection

@push('styles')
<style>
    @media print {
        .no-print, header, footer, .drawer, .drawer-overlay, .toast-stack { display: none !important; }
        body { background: #fff; }
        main { animation: none; }
    }
</style>
@endpush
