@extends('layouts.admin')

@section('title', 'Cash History')

@section('content')
<div class="p-3 sm:p-6">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-history text-blue-600"></i>
                Cash History
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">All cash transactions across customers, suppliers and ledger accounts.</p>
        </div>
        <a href="{{ route('admin.cash.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-medium shadow-sm">
            <i class="fas fa-plus"></i> New Cash Entry
        </a>
    </div>

    {{-- ── Summary cards ── --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-emerald-200 p-4 shadow-sm">
            <div class="text-[11px] uppercase tracking-wide text-emerald-700 font-semibold">Cash In</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-1">Rs. {{ number_format($totalIn, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-rose-200 p-4 shadow-sm">
            <div class="text-[11px] uppercase tracking-wide text-rose-700 font-semibold">Cash Out</div>
            <div class="text-2xl font-extrabold text-rose-700 mt-1">Rs. {{ number_format($totalOut, 2) }}</div>
        </div>
        <div class="bg-white rounded-xl border border-blue-200 p-4 shadow-sm col-span-2 sm:col-span-1">
            <div class="text-[11px] uppercase tracking-wide text-blue-700 font-semibold">Net</div>
            <div class="text-2xl font-extrabold {{ $net >= 0 ? 'text-emerald-700' : 'text-rose-700' }} mt-1">
                Rs. {{ number_format($net, 2) }}
            </div>
        </div>
    </div>

    {{-- ── Filters ── --}}
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-5">
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div>
                <label class="block text-[11px] font-medium text-gray-600 mb-1">From</label>
                <input type="date" name="from" value="{{ $filters['from'] }}"
                       class="w-full px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-[11px] font-medium text-gray-600 mb-1">To</label>
                <input type="date" name="to" value="{{ $filters['to'] }}"
                       class="w-full px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
            <div>
                <label class="block text-[11px] font-medium text-gray-600 mb-1">Direction</label>
                <select name="direction" class="w-full px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All</option>
                    <option value="in"  {{ $filters['direction'] === 'in'  ? 'selected' : '' }}>Cash In</option>
                    <option value="out" {{ $filters['direction'] === 'out' ? 'selected' : '' }}>Cash Out</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-medium text-gray-600 mb-1">Type</label>
                <select name="target_type" class="w-full px-3 py-1.5 border border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="">All</option>
                    <option value="customer" {{ $filters['targetType'] === 'customer' ? 'selected' : '' }}>Customer</option>
                    <option value="supplier" {{ $filters['targetType'] === 'supplier' ? 'selected' : '' }}>Supplier</option>
                    <option value="ledger"   {{ $filters['targetType'] === 'ledger'   ? 'selected' : '' }}>Ledger</option>
                </select>
            </div>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md">
                <i class="fas fa-filter mr-1"></i> Apply
            </button>
            <a href="{{ route('admin.cash.history') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-md">
                Clear
            </a>
        </div>
    </form>

    {{-- ── Table ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr class="text-left text-[11px] uppercase tracking-wide text-gray-600">
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Direction</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3">Account</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3">Method</th>
                        <th class="px-4 py-3">Reference</th>
                        <th class="px-4 py-3">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($rows as $r)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-2.5 whitespace-nowrap text-gray-700">
                                {{ \Carbon\Carbon::parse($r['date'])->format('d M Y') }}
                            </td>
                            <td class="px-4 py-2.5">
                                @if ($r['direction'] === 'in')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 text-[11px] font-semibold">
                                        <i class="fas fa-arrow-down text-[9px]"></i> In
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-rose-100 text-rose-700 text-[11px] font-semibold">
                                        <i class="fas fa-arrow-up text-[9px]"></i> Out
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 capitalize text-gray-600">{{ $r['target_type'] }}</td>
                            <td class="px-4 py-2.5 font-medium text-gray-800">{{ $r['target_label'] }}</td>
                            <td class="px-4 py-2.5 text-right font-bold {{ $r['direction'] === 'in' ? 'text-emerald-700' : 'text-rose-700' }}">
                                Rs. {{ number_format($r['amount'], 2) }}
                            </td>
                            <td class="px-4 py-2.5 text-gray-600 capitalize">{{ $r['payment_method'] }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-500 font-mono">{{ $r['reference'] }}</td>
                            <td class="px-4 py-2.5 text-xs text-gray-500">{{ $r['notes'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                <i class="fas fa-inbox text-4xl mb-2 block"></i>
                                No transactions match your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
