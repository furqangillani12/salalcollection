@extends('layouts.admin')

@section('content')
<div class="p-6 max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Profit / Loss Report (منافع / نقصان)</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ \Carbon\Carbon::parse($start)->format('d M Y') }} —
                {{ \Carbon\Carbon::parse($end)->format('d M Y') }}
                · {{ number_format($orderCount) }} completed orders
            </p>
        </div>
        <form method="GET" class="flex flex-wrap gap-2 items-center">
            <input type="date" name="start_date" value="{{ $start }}"
                class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
            <input type="date" name="end_date" value="{{ $end }}"
                class="border border-gray-300 rounded-md px-3 py-1.5 text-sm focus:ring-blue-500 focus:border-blue-500">
            <button type="submit"
                class="bg-blue-600 text-white px-4 py-1.5 rounded-md text-sm hover:bg-blue-700">Filter</button>
        </form>
    </div>

    {{-- Result banner --}}
    @if($netProfit >= 0)
    <div class="mb-6 bg-green-50 border-2 border-green-300 rounded-xl p-5 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-green-700 uppercase tracking-wide">Net Profit (خالص منافع)</p>
            <p class="text-4xl font-black text-green-800 mt-1">Rs. {{ number_format($profit, 0) }}</p>
        </div>
        <div class="text-green-400 text-6xl opacity-40">↑</div>
    </div>
    @else
    <div class="mb-6 bg-red-50 border-2 border-red-300 rounded-xl p-5 flex items-center justify-between">
        <div>
            <p class="text-sm font-semibold text-red-700 uppercase tracking-wide">Net Loss (خالص نقصان)</p>
            <p class="text-4xl font-black text-red-800 mt-1">Rs. {{ number_format($loss, 0) }}</p>
        </div>
        <div class="text-red-400 text-6xl opacity-40">↓</div>
    </div>
    @endif

    {{-- P&L Statement --}}
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
            <h2 class="text-sm font-bold text-gray-600 uppercase tracking-wider">Profit & Loss Statement</h2>
        </div>

        {{-- INCOME --}}
        <div class="px-5 py-4 border-b border-gray-100">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Income (آمدنی)</p>

            <div class="flex justify-between items-center py-1.5">
                <div>
                    <span class="text-sm text-gray-700">Gross Sales Revenue (کل فروخت)</span>
                    <span class="ml-2 text-xs text-gray-400">line discounts already applied in unit price</span>
                </div>
                <span class="text-sm font-semibold text-gray-800">Rs. {{ number_format($totalRevenue, 0) }}</span>
            </div>

            @if($totalTax > 0)
            <div class="flex justify-between items-center py-1.5">
                <span class="text-sm text-gray-500">Tax Collected</span>
                <span class="text-sm text-gray-500">Rs. {{ number_format($totalTax, 0) }}</span>
            </div>
            @endif
        </div>

        {{-- DEDUCTIONS --}}
        <div class="px-5 py-4 border-b border-gray-100 bg-orange-50/40">
            <p class="text-xs font-bold text-orange-500 uppercase tracking-wider mb-3">Less: Deductions (منہا)</p>

            <div class="flex justify-between items-center py-1.5">
                <div>
                    <span class="text-sm text-gray-700">Order Discounts (ڈسکاؤنٹ)</span>
                    <span class="ml-2 text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded-full">package &amp; manual</span>
                </div>
                <span class="text-sm font-semibold text-orange-700">− Rs. {{ number_format($totalDiscount, 0) }}</span>
            </div>

            @if($totalRefunds > 0)
            <div class="flex justify-between items-center py-1.5">
                <div>
                    <span class="text-sm text-gray-700">Refunds (واپسی)</span>
                    <span class="ml-2 text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded-full">returned</span>
                </div>
                <span class="text-sm font-semibold text-red-700">− Rs. {{ number_format($totalRefunds, 0) }}</span>
            </div>
            @endif

            <div class="flex justify-between items-center py-2 mt-1 border-t border-orange-200">
                <span class="text-sm font-semibold text-gray-700">Net Revenue (خالص آمدنی)</span>
                <span class="text-sm font-bold text-gray-800">Rs. {{ number_format($netRevenue, 0) }}</span>
            </div>
        </div>

        {{-- COST OF GOODS SOLD --}}
        <div class="px-5 py-4 border-b border-gray-100">
            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Cost of Goods Sold — COGS (لاگت)</p>

            <div class="flex justify-between items-center py-1.5">
                <span class="text-sm text-gray-700">Product Cost (مال کی قیمت)</span>
                <span class="text-sm font-semibold text-yellow-700">− Rs. {{ number_format($totalCost, 0) }}</span>
            </div>

            <div class="flex justify-between items-center py-2 mt-1 border-t border-gray-200">
                <span class="text-sm font-semibold text-gray-700">Gross Profit (خام منافع)</span>
                <span class="text-sm font-bold {{ $grossProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">
                    Rs. {{ number_format($grossProfit, 0) }}
                </span>
            </div>
        </div>

        {{-- OPERATING EXPENSES --}}
        @if($totalDelivery > 0 || $totalPurchaseExpenses > 0)
        <div class="px-5 py-4 border-b border-gray-100 bg-yellow-50/40">
            <p class="text-xs font-bold text-yellow-600 uppercase tracking-wider mb-3">Operating Expenses (آپریشنل اخراجات)</p>

            @if($totalDelivery > 0)
            <div class="flex justify-between items-center py-1.5">
                <div>
                    <span class="text-sm text-gray-700">Delivery / Courier Charges (ڈلیوری)</span>
                    <span class="ml-2 text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">collected & paid to courier</span>
                </div>
                <span class="text-sm font-semibold text-yellow-700">− Rs. {{ number_format($totalDelivery, 0) }}</span>
            </div>
            @endif

            @if($totalPurchaseExpenses > 0)
            <div class="flex justify-between items-center py-1.5">
                <div>
                    <span class="text-sm text-gray-700">Purchase Expenses (خریداری اخراجات)</span>
                    <span class="ml-2 text-xs bg-yellow-100 text-yellow-700 px-2 py-0.5 rounded-full">customs, handling, etc.</span>
                </div>
                <span class="text-sm font-semibold text-yellow-700">− Rs. {{ number_format($totalPurchaseExpenses, 0) }}</span>
            </div>
            @endif
        </div>
        @endif

        {{-- BOTTOM LINE --}}
        <div class="px-5 py-5 {{ $netProfit >= 0 ? 'bg-green-50' : 'bg-red-50' }}">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-base font-bold {{ $netProfit >= 0 ? 'text-green-800' : 'text-red-800' }}">
                        {{ $netProfit >= 0 ? 'Net Profit (خالص منافع)' : 'Net Loss (خالص نقصان)' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">Net Revenue − COGS{{ $totalPurchaseExpenses > 0 ? ' − Operating Expenses' : '' }}</p>
                </div>
                <p class="text-2xl font-black {{ $netProfit >= 0 ? 'text-green-800' : 'text-red-800' }}">
                    Rs. {{ number_format(abs($netProfit), 0) }}
                </p>
            </div>

            @php
                $margin = $totalGrossIncome > 0 ? (abs($netProfit) / $totalGrossIncome) * 100 : 0;
            @endphp
            @if($totalGrossIncome > 0)
            <div class="mt-3 pt-3 border-t {{ $netProfit >= 0 ? 'border-green-200' : 'border-red-200' }} flex flex-wrap gap-4 text-xs text-gray-500">
                <span>Profit Margin: <strong class="{{ $netProfit >= 0 ? 'text-green-700' : 'text-red-700' }}">{{ number_format($margin, 1) }}%</strong></span>
                <span>Orders: <strong class="text-gray-700">{{ number_format($orderCount) }}</strong></span>
                @if($orderCount > 0)
                <span>Avg Revenue/Order: <strong class="text-gray-700">Rs. {{ number_format($totalGrossIncome / $orderCount, 0) }}</strong></span>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- Quick summary table --}}
    <div class="mt-4 bg-white rounded-xl shadow overflow-hidden">
        <div class="px-5 py-3 bg-gray-50 border-b border-gray-200">
            <h2 class="text-sm font-bold text-gray-600 uppercase tracking-wider">Quick Summary</h2>
        </div>
        <div class="divide-y divide-gray-100">
            @php
                $rows = [
                    ['label' => 'Item Sales Revenue',    'value' => $totalRevenue,          'color' => 'text-gray-800'],
                    ['label' => 'Order Discounts',        'value' => -$totalDiscount,         'color' => 'text-orange-600'],
                    ['label' => 'Refunds',                'value' => -$totalRefunds,          'color' => 'text-red-600'],
                    ['label' => 'Net Revenue',            'value' => $netRevenue,             'color' => 'text-blue-700',   'bold' => true],
                    ['label' => 'Cost of Goods (COGS)',  'value' => -$totalCost,             'color' => 'text-yellow-700'],
                    ['label' => 'Gross Profit',           'value' => $grossProfit,            'color' => $grossProfit >= 0 ? 'text-green-700' : 'text-red-700', 'bold' => true],
                    ['label' => 'Delivery / Courier',     'value' => -$totalDelivery,         'color' => 'text-yellow-600'],
                    ['label' => 'Purchase Expenses',      'value' => -$totalPurchaseExpenses, 'color' => 'text-yellow-600'],
                    ['label' => 'NET PROFIT',             'value' => $netProfit,              'color' => $netProfit >= 0 ? 'text-green-700' : 'text-red-700', 'bold' => true, 'large' => true],
                ];
            @endphp
            @foreach($rows as $row)
                @if(($row['value'] ?? 0) == 0 && !($row['bold'] ?? false)) @continue @endif
                <div class="flex justify-between items-center px-5 py-2.5 {{ ($row['large'] ?? false) ? 'bg-gray-50' : '' }}">
                    <span class="text-sm {{ ($row['bold'] ?? false) ? 'font-bold' : '' }} text-gray-700">{{ $row['label'] }}</span>
                    <span class="text-sm font-{{ ($row['bold'] ?? false) ? 'black' : 'semibold' }} {{ $row['color'] }} {{ ($row['large'] ?? false) ? 'text-lg' : '' }}">
                        {{ $row['value'] >= 0 ? '' : '− ' }}Rs. {{ number_format(abs($row['value']), 0) }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Note --}}
    <div class="mt-4 text-xs text-gray-400 bg-gray-50 rounded-lg px-4 py-3 space-y-1">
        <p><strong>Notes:</strong></p>
        <p>• Cancelled and pending orders are excluded.</p>
        <p>• Per-item (line) discounts are already baked into the unit price, so they reduce Item Sales Revenue directly.</p>
        <p>• Order-level discounts (package deals, manual discounts) are shown as a separate deduction.</p>
        <p>• Delivery charges are income — money the customer paid for shipping.</p>
        @if($totalPurchaseExpenses > 0)
        <p>• Purchase expenses (courier, customs, handling on stock purchases) are deducted as operating expenses.</p>
        @endif
        <p>• COGS is calculated from each product's stored cost price × quantity sold.</p>
    </div>

</div>
@endsection
