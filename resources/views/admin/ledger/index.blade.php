@extends('layouts.admin')

@section('title', 'General Ledger')

@section('content')
    <div class="container mx-auto px-4 py-6">

        {{-- ── Header ── --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">📒 General Ledger</h1>
                <p class="text-sm text-gray-500 mt-1">All financial transactions in one place</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.ledger.accounts') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas fa-chart-pie"></i> Account Summary
                </a>
                <a href="{{ route('admin.ledger.export', request()->query()) }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
            </div>
        </div>

        {{-- ── Summary Cards ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border-l-4 border-green-500">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Income (Credit)</p>
                <p class="text-2xl font-bold text-green-600 mt-1">Rs. {{ number_format($totalCredit, 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">All money received</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border-l-4 border-red-500">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Outflow (Debit)</p>
                <p class="text-2xl font-bold text-red-600 mt-1">Rs. {{ number_format($totalDebit, 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">Purchases, expenses, refunds</p>
            </div>
            <div
                class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 border-l-4 {{ $netBalance >= 0 ? 'border-blue-500' : 'border-orange-500' }}">
                <p class="text-sm text-gray-500 dark:text-gray-400">Net Balance</p>
                <p class="text-2xl font-bold {{ $netBalance >= 0 ? 'text-blue-600' : 'text-orange-600' }} mt-1">
                    Rs. {{ number_format(abs($netBalance), 2) }}
                    <span class="text-sm font-normal">({{ $netBalance >= 0 ? 'Profit' : 'Loss' }})</span>
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $fromDate }} → {{ $toDate }}</p>
            </div>
        </div>

        {{-- ── Account Breakdown (mini) ── --}}
        @if ($accountBreakdown->count())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Account Breakdown (Period)</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-xs">
                        <thead>
                            <tr class="text-gray-500 border-b dark:border-gray-700">
                                <th class="pb-2 text-left">Account</th>
                                <th class="pb-2 text-right">Entries</th>
                                <th class="pb-2 text-right">Debit</th>
                                <th class="pb-2 text-right">Credit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($accountBreakdown as $acc)
                                <tr class="border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="py-1 text-gray-700 dark:text-gray-300">
                                        {{ \App\Models\LedgerEntry::ACCOUNT_LABELS[$acc->account_type] ?? $acc->account_type }}
                                    </td>
                                    <td class="py-1 text-right text-gray-500">{{ $acc->entry_count }}</td>
                                    <td class="py-1 text-right text-red-500">
                                        {{ $acc->total_debit > 0 ? 'Rs. ' . number_format($acc->total_debit, 2) : '—' }}</td>
                                    <td class="py-1 text-right text-green-600">
                                        {{ $acc->total_credit > 0 ? 'Rs. ' . number_format($acc->total_credit, 2) : '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- ── Filters ── --}}
        <form method="GET" action="{{ route('admin.ledger.index') }}"
            class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ $fromDate }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ $toDate }}"
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Account Type</label>
                    <select name="account_type"
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Accounts</option>
                        @foreach (\App\Models\LedgerEntry::ACCOUNT_LABELS as $key => $label)
                            <option value="{{ $key }}" {{ request('account_type') == $key ? 'selected' : '' }}>
                                {{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Transaction Type</label>
                    <select name="transaction_type"
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Types</option>
                        @foreach (\App\Models\LedgerEntry::TRANSACTION_LABELS as $key => $label)
                            <option value="{{ $key }}"
                                {{ request('transaction_type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Payment Method</label>
                    <select name="payment_method"
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">All Methods</option>
                        <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="card" {{ request('payment_method') == 'card' ? 'selected' : '' }}>Card</option>
                        <option value="mobile_money" {{ request('payment_method') == 'mobile_money' ? 'selected' : '' }}>
                            Mobile Money</option>
                        <option value="credit" {{ request('payment_method') == 'credit' ? 'selected' : '' }}>Credit
                        </option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Ref #, description…"
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>
            </div>
            <div class="flex gap-2 mt-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('admin.ledger.index') }}"
                    class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-5 py-2 rounded-lg text-sm dark:bg-gray-600 dark:text-gray-200">
                    Reset
                </a>
            </div>
        </form>

        {{-- ── Entries Table ── --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase text-xs">
                        <tr>
                            <th class="px-4 py-3 text-left">Entry #</th>
                            <th class="px-4 py-3 text-left">Date</th>
                            <th class="px-4 py-3 text-left">Account</th>
                            <th class="px-4 py-3 text-left">Type</th>
                            <th class="px-4 py-3 text-left">Ref #</th>
                            <th class="px-4 py-3 text-left">Party</th>
                            <th class="px-4 py-3 text-left">Method</th>
                            <th class="px-4 py-3 text-right">Debit</th>
                            <th class="px-4 py-3 text-right">Credit</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($entries as $entry)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs text-gray-500">{{ $entry->entry_number }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                    {{ $entry->entry_date->format('d M Y') }}
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $accountColors = [
                                            'sales' => 'bg-green-100 text-green-700',
                                            'purchases' => 'bg-orange-100 text-orange-700',
                                            'expenses' => 'bg-red-100 text-red-700',
                                            'cash_in' => 'bg-blue-100 text-blue-700',
                                            'cash_out' => 'bg-gray-100 text-gray-700',
                                            'accounts_receivable' => 'bg-yellow-100 text-yellow-700',
                                            'refunds' => 'bg-pink-100 text-pink-700',
                                            'payroll' => 'bg-purple-100 text-purple-700',
                                        ];
                                        $color = $accountColors[$entry->account_type] ?? 'bg-gray-100 text-gray-600';
                                    @endphp
                                    <span class="px-2 py-1 rounded text-xs font-medium {{ $color }}">
                                        {{ \App\Models\LedgerEntry::ACCOUNT_LABELS[$entry->account_type] ?? $entry->account_type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                                    {{ \App\Models\LedgerEntry::TRANSACTION_LABELS[$entry->transaction_type] ?? $entry->transaction_type }}
                                </td>
                                <td class="px-4 py-3">
                                    <span
                                        class="font-mono text-xs text-blue-600 dark:text-blue-400">{{ $entry->reference_number ?? '—' }}</span>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 text-xs">
                                    {{ $entry->party_name ?? '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($entry->payment_method)
                                        <span class="text-xs text-gray-500 capitalize">
                                            {{ str_replace('_', ' ', $entry->payment_method) }}
                                        </span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($entry->debit > 0)
                                        <span
                                            class="text-red-600 font-medium">{{ number_format($entry->debit, 2) }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($entry->credit > 0)
                                        <span
                                            class="text-green-600 font-medium">{{ number_format($entry->credit, 2) }}</span>
                                    @else
                                        <span class="text-gray-300">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('admin.ledger.show', $entry) }}"
                                        class="text-blue-500 hover:text-blue-700 text-xs">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-10 text-center text-gray-400">
                                    <i class="fas fa-book-open text-3xl mb-2 block opacity-30"></i>
                                    No ledger entries found for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($entries->count())
                        <tfoot class="bg-gray-50 dark:bg-gray-700 font-semibold text-sm">
                            <tr>
                                <td colspan="7" class="px-4 py-3 text-right text-gray-600 dark:text-gray-300">
                                    Page Total:
                                </td>
                                <td class="px-4 py-3 text-right text-red-600">
                                    Rs. {{ number_format($entries->sum('debit'), 2) }}
                                </td>
                                <td class="px-4 py-3 text-right text-green-600">
                                    Rs. {{ number_format($entries->sum('credit'), 2) }}
                                </td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- Pagination --}}
            @if ($entries->hasPages())
                <div class="px-4 py-3 border-t dark:border-gray-700">
                    {{ $entries->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
