@extends('layouts.admin')

@section('title', 'Account Summary')

@section('content')
    <div class="container mx-auto px-4 py-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">📊 Account Summary</h1>
                <p class="text-sm text-gray-500 mt-1">Financial overview by account type</p>
            </div>
            <a href="{{ route('admin.ledger.index') }}" class="text-sm text-blue-600 hover:underline dark:text-blue-400">←
                Back to Ledger</a>
        </div>

        {{-- Date Filter --}}
        <form method="GET" action="{{ route('admin.ledger.accounts') }}"
            class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">From Date</label>
                <input type="date" name="from_date" value="{{ $fromDate }}"
                    class="border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">To Date</label>
                <input type="date" name="to_date" value="{{ $toDate }}"
                    class="border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm h-fit">
                Apply
            </button>
        </form>

        {{-- P&L Summary --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border-l-4 border-green-500">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Income</p>
                <p class="text-2xl font-bold text-green-600 mt-1">Rs. {{ number_format($totalIncome, 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">Sales + Cash In</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border-l-4 border-red-500">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Total Outflow</p>
                <p class="text-2xl font-bold text-red-600 mt-1">Rs. {{ number_format($totalExpense, 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">Purchases + Expenses + Payroll + Refunds</p>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border-l-4 {{ $netProfit >= 0 ? 'border-blue-500' : 'border-orange-500' }}">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Net {{ $netProfit >= 0 ? 'Profit' : 'Loss' }}</p>
                <p class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-blue-600' : 'text-orange-600' }} mt-1">
                    Rs. {{ number_format(abs($netProfit), 2) }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $fromDate }} → {{ $toDate }}</p>
            </div>
        </div>

        {{-- Account Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
            @forelse($accounts as $account)
                @php
                    $icons = [
                        'sales' => [
                            'icon' => 'fa-shopping-cart',
                            'color' => 'text-green-600',
                            'bg' => 'bg-green-50 dark:bg-green-900',
                        ],
                        'purchases' => [
                            'icon' => 'fa-truck',
                            'color' => 'text-orange-600',
                            'bg' => 'bg-orange-50 dark:bg-orange-900',
                        ],
                        'expenses' => [
                            'icon' => 'fa-receipt',
                            'color' => 'text-red-600',
                            'bg' => 'bg-red-50 dark:bg-red-900',
                        ],
                        'cash_in' => [
                            'icon' => 'fa-coins',
                            'color' => 'text-blue-600',
                            'bg' => 'bg-blue-50 dark:bg-blue-900',
                        ],
                        'cash_out' => [
                            'icon' => 'fa-money-bill-wave',
                            'color' => 'text-gray-600',
                            'bg' => 'bg-gray-50 dark:bg-gray-700',
                        ],
                        'accounts_receivable' => [
                            'icon' => 'fa-user-clock',
                            'color' => 'text-yellow-600',
                            'bg' => 'bg-yellow-50 dark:bg-yellow-900',
                        ],
                        'refunds' => [
                            'icon' => 'fa-undo',
                            'color' => 'text-pink-600',
                            'bg' => 'bg-pink-50 dark:bg-pink-900',
                        ],
                        'payroll' => [
                            'icon' => 'fa-id-badge',
                            'color' => 'text-purple-600',
                            'bg' => 'bg-purple-50 dark:bg-purple-900',
                        ],
                    ];
                    $meta = $icons[$account->account_type] ?? [
                        'icon' => 'fa-book',
                        'color' => 'text-gray-600',
                        'bg' => 'bg-gray-50',
                    ];
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 hover:shadow-md transition">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full {{ $meta['bg'] }} flex items-center justify-center">
                                <i class="fas {{ $meta['icon'] }} {{ $meta['color'] }}"></i>
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800 dark:text-white text-sm">{{ $account->label }}</p>
                                <p class="text-xs text-gray-400">{{ $account->entries }} entries</p>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs mt-3 pt-3 border-t dark:border-gray-700">
                        <div>
                            <p class="text-gray-500">Debit</p>
                            <p class="font-medium text-red-500 mt-0.5">{{ number_format($account->total_debit, 0) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Credit</p>
                            <p class="font-medium text-green-600 mt-0.5">{{ number_format($account->total_credit, 0) }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500">Net</p>
                            <p class="font-medium {{ $account->net >= 0 ? 'text-blue-600' : 'text-orange-500' }} mt-0.5">
                                {{ number_format(abs($account->net), 0) }}
                            </p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('admin.ledger.index', ['account_type' => $account->account_type, 'from_date' => $fromDate, 'to_date' => $toDate]) }}"
                            class="text-xs text-blue-500 hover:underline">View entries →</a>
                    </div>
                </div>
            @empty
                <div class="col-span-3 bg-white dark:bg-gray-800 rounded-lg shadow p-10 text-center text-gray-400">
                    No financial activity found for the selected period.
                </div>
            @endforelse
        </div>

        {{-- Detailed Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b dark:border-gray-700">
                <h3 class="font-semibold text-gray-700 dark:text-gray-300">Trial Balance</h3>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Account</th>
                        <th class="px-4 py-3 text-center">Entries</th>
                        <th class="px-4 py-3 text-right">Total Debit (Rs)</th>
                        <th class="px-4 py-3 text-right">Total Credit (Rs)</th>
                        <th class="px-4 py-3 text-right">Net Balance (Rs)</th>
                        <th class="px-4 py-3 text-left">Last Activity</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @foreach ($accounts as $account)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">{{ $account->label }}</td>
                            <td class="px-4 py-3 text-center text-gray-500">{{ $account->entries }}</td>
                            <td
                                class="px-4 py-3 text-right {{ $account->total_debit > 0 ? 'text-red-600 font-medium' : 'text-gray-300' }}">
                                {{ number_format($account->total_debit, 2) }}
                            </td>
                            <td
                                class="px-4 py-3 text-right {{ $account->total_credit > 0 ? 'text-green-600 font-medium' : 'text-gray-300' }}">
                                {{ number_format($account->total_credit, 2) }}
                            </td>
                            <td
                                class="px-4 py-3 text-right font-semibold {{ $account->net >= 0 ? 'text-blue-600' : 'text-orange-500' }}">
                                {{ $account->net >= 0 ? '' : '-' }}{{ number_format(abs($account->net), 2) }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">
                                {{ $account->last_activity ? \Carbon\Carbon::parse($account->last_activity)->format('d M Y') : '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-100 dark:bg-gray-700 font-bold text-sm">
                    <tr>
                        <td class="px-4 py-3 text-gray-700 dark:text-gray-300">TOTAL</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $accounts->sum('entries') }}</td>
                        <td class="px-4 py-3 text-right text-red-600">Rs.
                            {{ number_format($accounts->sum('total_debit'), 2) }}</td>
                        <td class="px-4 py-3 text-right text-green-600">Rs.
                            {{ number_format($accounts->sum('total_credit'), 2) }}</td>
                        <td class="px-4 py-3 text-right {{ $netProfit >= 0 ? 'text-blue-600' : 'text-orange-500' }}">
                            Rs. {{ number_format(abs($netProfit), 2) }} ({{ $netProfit >= 0 ? 'Profit' : 'Loss' }})
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
