@extends('layouts.admin')

@section('title', 'Ledger Entry Detail')

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-3xl">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.ledger.index') }}"
                class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold text-gray-800 dark:text-white">Ledger Entry Detail</h1>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            {{-- Entry Header --}}
            <div class="p-6 border-b dark:border-gray-700 flex items-center justify-between">
                <div>
                    <span
                        class="font-mono text-lg font-bold text-gray-800 dark:text-white">{{ $ledger->entry_number }}</span>
                    <p class="text-sm text-gray-500 mt-1">{{ $ledger->entry_date->format('l, d F Y') }}</p>
                </div>
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
                    $color = $accountColors[$ledger->account_type] ?? 'bg-gray-100 text-gray-600';
                @endphp
                <span class="px-3 py-1 rounded-full text-sm font-medium {{ $color }}">
                    {{ \App\Models\LedgerEntry::ACCOUNT_LABELS[$ledger->account_type] ?? $ledger->account_type }}
                </span>
            </div>

            {{-- Amount Block --}}
            <div class="p-6 grid grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-750">
                <div
                    class="text-center p-4 bg-white dark:bg-gray-700 rounded-lg border {{ $ledger->debit > 0 ? 'border-red-200' : 'border-gray-200' }}">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Debit (Outflow)</p>
                    <p class="text-2xl font-bold {{ $ledger->debit > 0 ? 'text-red-600' : 'text-gray-300' }}">
                        Rs. {{ number_format($ledger->debit, 2) }}
                    </p>
                </div>
                <div
                    class="text-center p-4 bg-white dark:bg-gray-700 rounded-lg border {{ $ledger->credit > 0 ? 'border-green-200' : 'border-gray-200' }}">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Credit (Inflow)</p>
                    <p class="text-2xl font-bold {{ $ledger->credit > 0 ? 'text-green-600' : 'text-gray-300' }}">
                        Rs. {{ number_format($ledger->credit, 2) }}
                    </p>
                </div>
            </div>

            {{-- Details --}}
            <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4 text-sm">
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Description</p>
                    <p class="text-gray-800 dark:text-gray-200">{{ $ledger->description }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Transaction Type</p>
                    <p class="text-gray-800 dark:text-gray-200">
                        {{ \App\Models\LedgerEntry::TRANSACTION_LABELS[$ledger->transaction_type] ?? $ledger->transaction_type }}
                    </p>
                </div>
                @if ($ledger->reference_number)
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Reference Number</p>
                        <p class="font-mono text-blue-600 dark:text-blue-400">{{ $ledger->reference_number }}</p>
                    </div>
                @endif
                @if ($ledger->payment_method)
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Payment Method</p>
                        <p class="capitalize text-gray-800 dark:text-gray-200">
                            {{ str_replace('_', ' ', $ledger->payment_method) }}</p>
                    </div>
                @endif
                @if ($ledger->party_name)
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Party</p>
                        <p class="text-gray-800 dark:text-gray-200">
                            {{ $ledger->party_name }}
                            <span class="text-xs text-gray-400 ml-1">({{ ucfirst($ledger->party_type ?? '') }})</span>
                        </p>
                    </div>
                @endif
                @if ($ledger->user)
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Recorded By</p>
                        <p class="text-gray-800 dark:text-gray-200">{{ $ledger->user->name }}</p>
                    </div>
                @endif
                @if ($ledger->notes)
                    <div class="col-span-2">
                        <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Notes</p>
                        <p class="text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-700 rounded p-3">
                            {{ $ledger->notes }}</p>
                    </div>
                @endif
            </div>

            <div class="px-6 py-4 border-t dark:border-gray-700 flex justify-between items-center text-xs text-gray-400">
                <span>Created: {{ $ledger->created_at->format('d M Y, h:i A') }}</span>
                <a href="{{ route('admin.ledger.index') }}" class="text-blue-500 hover:underline">← Back to Ledger</a>
            </div>
        </div>
    </div>
@endsection
