@extends('layouts.admin')

@section('title', $ledgerAccount->name . ' — Ledger')

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-5xl">

        {{-- Header --}}
        <div class="flex flex-wrap items-start justify-between gap-3 mb-6">
            <div>
                <a href="{{ route('admin.ledger-accounts.index') }}"
                    class="text-sm text-blue-600 hover:underline mb-1 block">← All Ledger Accounts</a>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                        {{ $ledgerAccount->name }}
                    </h1>
                    @php
                        $typeBadge =
                            [
                                'expense' => 'bg-red-100 text-red-700',
                                'income' => 'bg-green-100 text-green-700',
                                'asset' => 'bg-blue-100 text-blue-700',
                                'liability' => 'bg-orange-100 text-orange-700',
                            ][$ledgerAccount->type] ?? 'bg-gray-100 text-gray-700';
                    @endphp
                    <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $typeBadge }}">
                        {{ \App\Models\LedgerAccount::TYPE_LABELS[$ledgerAccount->type] ?? $ledgerAccount->type }}
                    </span>
                </div>
                <p class="text-xs text-gray-400 mt-1 font-mono">{{ $ledgerAccount->account_code }}
                    @if ($ledgerAccount->category)
                        · {{ $ledgerAccount->category }}
                    @endif
                </p>
            </div>
            <div class="flex gap-2 flex-wrap">
                <a href="{{ route('admin.ledger-accounts.show', array_merge(['ledger_account' => $ledgerAccount->id], request()->query(), ['export' => '1'])) }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas fa-file-csv"></i> Export CSV
                </a>
                <button onclick="window.print()"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas fa-print"></i> Print
                </button>
                <a href="{{ route('admin.ledger-accounts.edit', $ledgerAccount) }}"
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-5">
                {{ session('success') }}
            </div>
        @endif

        {{-- Balance Summary Cards --}}
        @php
            $totalDebit = $ledgerAccount->entries->sum('debit');
            $totalCredit = $ledgerAccount->entries->sum('credit');
            $netBalance = in_array($ledgerAccount->type, ['income', 'liability'])
                ? $ledgerAccount->opening_balance + $totalCredit - $totalDebit
                : $ledgerAccount->opening_balance + $totalDebit - $totalCredit;
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-gray-400">
                <p class="text-xs text-gray-500">Opening Balance</p>
                <p class="text-lg font-bold text-gray-700">Rs. {{ number_format($ledgerAccount->opening_balance, 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-red-400">
                <p class="text-xs text-gray-500">Total Debit</p>
                <p class="text-lg font-bold text-red-600">Rs. {{ number_format($totalDebit, 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-green-400">
                <p class="text-xs text-gray-500">Total Credit</p>
                <p class="text-lg font-bold text-green-600">Rs. {{ number_format($totalCredit, 0) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 border-blue-500">
                <p class="text-xs text-gray-500">Net Balance</p>
                <p class="text-lg font-bold text-blue-700">Rs. {{ number_format($netBalance, 0) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- ── Left: Add Entry Form ── --}}
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-5 sticky top-4">
                    <h3 class="font-semibold text-gray-700 dark:text-gray-200 mb-4 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-blue-500"></i>
                        Add New Entry
                    </h3>

                    <form method="POST" action="{{ route('admin.ledger-accounts.add-entry', $ledgerAccount) }}">
                        @csrf

                        <div class="mb-4">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Date <span
                                    class="text-red-500">*</span></label>
                            <input type="date" name="entry_date" value="{{ date('Y-m-d') }}" required
                                class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description <span
                                    class="text-red-500">*</span></label>
                            <input type="text" name="description" required
                                placeholder="e.g. Jan rent paid, Electricity bill..."
                                class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Entry Type <span
                                    class="text-red-500">*</span></label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="cursor-pointer">
                                    <input type="radio" name="entry_type" value="debit" class="peer hidden" checked>
                                    <div
                                        class="border-2 border-red-200 bg-red-50 text-red-700 rounded-lg p-2 text-center text-sm font-medium peer-checked:border-red-500 peer-checked:ring-2 peer-checked:ring-red-200">
                                        Debit<br><span class="text-xs font-normal">(Money Out)</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="entry_type" value="credit" class="peer hidden">
                                    <div
                                        class="border-2 border-green-200 bg-green-50 text-green-700 rounded-lg p-2 text-center text-sm font-medium peer-checked:border-green-500 peer-checked:ring-2 peer-checked:ring-green-200">
                                        Credit<br><span class="text-xs font-normal">(Money In)</span>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Amount (Rs.)
                                <span class="text-red-500">*</span></label>
                            <input type="number" name="amount" min="0.01" step="0.01" required placeholder="0.00"
                                class="w-full border rounded-lg px-3 py-2 text-sm font-bold dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        </div>

                        <div class="mb-4">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Payment
                                Method</label>
                            <select name="payment_method"
                                class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="">Select method</option>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="jazzcash">JazzCash</option>
                                <option value="easypaisa">EasyPaisa</option>
                                <option value="card">Card</option>
                            </select>
                        </div>

                        <div class="mb-5">
                            <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Notes</label>
                            <textarea name="notes" rows="2" placeholder="Optional notes..."
                                class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>

                        <button type="submit"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg font-semibold text-sm">
                            ✅ Add Entry
                        </button>
                    </form>
                </div>
            </div>

            {{-- ── Right: Entries Table ── --}}
            <div class="lg:col-span-2">

                {{-- Date Filter --}}
                <form method="GET" action="{{ route('admin.ledger-accounts.show', $ledgerAccount) }}"
                    class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">From</label>
                        <input type="date" name="from_date" value="{{ $fromDate }}"
                            class="border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">To</label>
                        <input type="date" name="to_date" value="{{ $toDate }}"
                            class="border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    </div>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                        Filter
                    </button>
                </form>

                {{-- Period Totals --}}
                <div class="grid grid-cols-3 gap-3 mb-4 text-center text-sm">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <p class="text-xs text-gray-500">Opening</p>
                        <p class="font-bold text-gray-700 dark:text-gray-200">Rs. {{ number_format($openingRunning, 0) }}
                        </p>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3">
                        <p class="text-xs text-red-500">Period Debit</p>
                        <p class="font-bold text-red-600">Rs. {{ number_format($periodDebit, 0) }}</p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3">
                        <p class="text-xs text-green-500">Period Credit</p>
                        <p class="font-bold text-green-600">Rs. {{ number_format($periodCredit, 0) }}</p>
                    </div>
                </div>

                {{-- Entries --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                    <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-700 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-3 py-3 text-left">Date</th>
                                <th class="px-3 py-3 text-left">Description</th>
                                <th class="px-3 py-3 text-right text-red-500">Debit</th>
                                <th class="px-3 py-3 text-right text-green-600">Credit</th>
                                <th class="px-3 py-3 text-left">Method</th>
                                <th class="px-3 py-3 text-left">Ref</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                            {{-- Opening balance row --}}
                            <tr class="bg-blue-50 dark:bg-blue-900/20">
                                <td class="px-3 py-2 text-xs text-blue-500" colspan="2">
                                    Opening Balance (before {{ $fromDate }})
                                </td>
                                <td class="px-3 py-2 text-right text-xs text-blue-600 font-medium" colspan="4">
                                    Rs. {{ number_format($openingRunning, 0) }}
                                </td>
                            </tr>

                            @php $running = $openingRunning; @endphp
                            @forelse($entries as $entry)
                                @php
                                    if (in_array($ledgerAccount->type, ['income', 'liability'])) {
                                        $running += $entry->credit - $entry->debit;
                                    } else {
                                        $running += $entry->debit - $entry->credit;
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                    <td class="px-3 py-3 text-xs text-gray-500">
                                        {{ $entry->entry_date->format('d M Y') }}
                                    </td>
                                    <td class="px-3 py-3 text-gray-700 dark:text-gray-300">
                                        <div>{{ $entry->description }}</div>
                                        @if ($entry->notes)
                                            <div class="text-xs text-gray-400">{{ $entry->notes }}</div>
                                        @endif
                                    </td>
                                    <td
                                        class="px-3 py-3 text-right {{ $entry->debit > 0 ? 'text-red-600 font-semibold' : 'text-gray-300' }}">
                                        {{ $entry->debit > 0 ? number_format($entry->debit, 0) : '—' }}
                                    </td>
                                    <td
                                        class="px-3 py-3 text-right {{ $entry->credit > 0 ? 'text-green-600 font-semibold' : 'text-gray-300' }}">
                                        {{ $entry->credit > 0 ? number_format($entry->credit, 0) : '—' }}
                                    </td>
                                    <td class="px-3 py-3 text-xs text-gray-500 capitalize">
                                        {{ $entry->payment_method ? str_replace('_', ' ', $entry->payment_method) : '—' }}
                                    </td>
                                    <td class="px-3 py-3 text-xs text-gray-400 font-mono">
                                        {{ $entry->reference_number ?? '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-3 py-10 text-center text-gray-400">
                                        No entries in this period. Add one using the form.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if ($entries->count())
                            <tfoot class="bg-gray-100 dark:bg-gray-700 font-semibold text-sm">
                                <tr>
                                    <td colspan="2" class="px-3 py-3 text-gray-600 dark:text-gray-300">Period Total
                                    </td>
                                    <td class="px-3 py-3 text-right text-red-600">{{ number_format($periodDebit, 0) }}
                                    </td>
                                    <td class="px-3 py-3 text-right text-green-600">{{ number_format($periodCredit, 0) }}
                                    </td>
                                    <td colspan="2"></td>
                                </tr>
                                <tr class="bg-blue-50 dark:bg-blue-900/20">
                                    <td colspan="2" class="px-3 py-3 font-bold text-blue-700">Closing Balance</td>
                                    <td colspan="4" class="px-3 py-3 text-right font-bold text-blue-700">
                                        Rs. {{ number_format($running, 0) }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>

                    </div>
                    @if ($entries->hasPages())
                        <div class="px-4 py-3 border-t dark:border-gray-700">
                            {{ $entries->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            @media print {

                .no-print,
                nav,
                aside,
                header,
                form {
                    display: none !important;
                }

                .sticky {
                    position: relative !important;
                }
            }
        </style>
    @endpush
@endsection
