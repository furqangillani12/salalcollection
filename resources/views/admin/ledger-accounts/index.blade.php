@extends('layouts.admin')

@section('title', 'Ledger Accounts')

@section('content')
    <div class="container mx-auto px-4 py-6">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">📚 Ledger Accounts</h1>
                <p class="text-sm text-gray-500 mt-1">Create and manage your Chart of Accounts</p>
            </div>
            <a href="{{ route('admin.ledger-accounts.create') }}"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg flex items-center gap-2">
                <i class="fas fa-plus"></i> Create New Ledger
            </a>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-5">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-5">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter Bar --}}
        <form method="GET" class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6 flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Account Type</label>
                <select name="type"
                    class="border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                    <option value="">All Types</option>
                    @foreach (\App\Models\LedgerAccount::TYPE_LABELS as $key => $label)
                        <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>
                            {{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, code, category..."
                    class="border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
            </div>
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">Filter</button>
            <a href="{{ route('admin.ledger-accounts.index') }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm dark:bg-gray-600 dark:text-gray-200">Reset</a>
        </form>

        {{-- Type Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            @php
                $typeConfig = [
                    'income' => [
                        'label' => 'Income Ledgers',
                        'icon' => '💰',
                        'border' => 'border-green-500',
                        'text' => 'text-green-600',
                    ],
                    'expense' => [
                        'label' => 'Expense Ledgers',
                        'icon' => '🧾',
                        'border' => 'border-red-500',
                        'text' => 'text-red-600',
                    ],
                    'asset' => [
                        'label' => 'Asset Ledgers',
                        'icon' => '🏦',
                        'border' => 'border-blue-500',
                        'text' => 'text-blue-600',
                    ],
                    'liability' => [
                        'label' => 'Liability Ledgers',
                        'icon' => '📋',
                        'border' => 'border-orange-500',
                        'text' => 'text-orange-600',
                    ],
                ];
            @endphp
            @foreach ($typeConfig as $type => $config)
                @php
                    $typeAccounts = $accounts->where('type', $type);
                    $count = $typeAccounts->count();
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-l-4 {{ $config['border'] }}">
                    <p class="text-sm text-gray-500">{{ $config['label'] }}</p>
                    <p class="text-2xl font-bold {{ $config['text'] }} mt-1">{{ $count }}</p>
                    <a href="{{ route('admin.ledger-accounts.index', ['type' => $type]) }}"
                        class="text-xs text-blue-500 hover:underline mt-1 block">View all →</a>
                </div>
            @endforeach
        </div>

        {{-- Accounts Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase text-xs">
                    <tr>
                        <th class="px-4 py-3 text-left">Code</th>
                        <th class="px-4 py-3 text-left">Ledger Name</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-left">Category</th>
                        <th class="px-4 py-3 text-right">Entries</th>
                        <th class="px-4 py-3 text-right">Total Debit</th>
                        <th class="px-4 py-3 text-right">Total Credit</th>
                        <th class="px-4 py-3 text-right">Balance</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($accounts as $account)
                        @php
                            $debit = $account->entries->sum('debit');
                            $credit = $account->entries->sum('credit');
                            $balance = in_array($account->type, ['income', 'liability'])
                                ? $account->opening_balance + $credit - $debit
                                : $account->opening_balance + $debit - $credit;

                            $typeBadge =
                                [
                                    'expense' => 'bg-red-100 text-red-700',
                                    'income' => 'bg-green-100 text-green-700',
                                    'asset' => 'bg-blue-100 text-blue-700',
                                    'liability' => 'bg-orange-100 text-orange-700',
                                ][$account->type] ?? 'bg-gray-100 text-gray-600';
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 {{ !$account->is_active ? 'opacity-50' : '' }}">
                            <td class="px-4 py-3">
                                <span class="font-mono text-xs text-gray-500">{{ $account->account_code }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.ledger-accounts.show', $account) }}"
                                    class="font-semibold text-gray-800 dark:text-white hover:text-blue-600">
                                    {{ $account->name }}
                                </a>
                                @if ($account->description)
                                    <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($account->description, 50) }}</p>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-1 rounded text-xs font-medium {{ $typeBadge }}">
                                    {{ \App\Models\LedgerAccount::TYPE_LABELS[$account->type] ?? $account->type }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $account->category ?? '—' }}</td>
                            <td class="px-4 py-3 text-right text-gray-500">{{ $account->entries_count ?? 0 }}</td>
                            <td
                                class="px-4 py-3 text-right {{ $debit > 0 ? 'text-red-600 font-medium' : 'text-gray-300' }}">
                                {{ $debit > 0 ? 'Rs. ' . number_format($debit, 0) : '—' }}
                            </td>
                            <td
                                class="px-4 py-3 text-right {{ $credit > 0 ? 'text-green-600 font-medium' : 'text-gray-300' }}">
                                {{ $credit > 0 ? 'Rs. ' . number_format($credit, 0) : '—' }}
                            </td>
                            <td
                                class="px-4 py-3 text-right font-bold {{ $balance > 0 ? 'text-blue-700' : 'text-gray-400' }}">
                                Rs. {{ number_format(abs($balance), 0) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if ($account->is_active)
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Active</span>
                                @else
                                    <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Inactive</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('admin.ledger-accounts.show', $account) }}"
                                        class="text-blue-500 hover:text-blue-700" title="View Ledger">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.ledger-accounts.edit', $account) }}"
                                        class="text-yellow-500 hover:text-yellow-700" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.ledger-accounts.toggle', $account) }}"
                                        class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            class="{{ $account->is_active ? 'text-orange-400 hover:text-orange-600' : 'text-green-500 hover:text-green-700' }}"
                                            title="{{ $account->is_active ? 'Deactivate' : 'Activate' }}">
                                            <i class="fas fa-{{ $account->is_active ? 'toggle-on' : 'toggle-off' }}"></i>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.ledger-accounts.destroy', $account) }}"
                                        class="inline"
                                        onsubmit="return confirm('Delete this ledger? This cannot be undone.')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-400 hover:text-red-600" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-16 text-center text-gray-400">
                                <i class="fas fa-book-open text-4xl mb-3 block opacity-20"></i>
                                <p class="font-medium">No ledger accounts created yet</p>
                                <p class="text-sm mt-1">
                                    <a href="{{ route('admin.ledger-accounts.create') }}"
                                        class="text-blue-500 hover:underline">
                                        Create your first ledger account →
                                    </a>
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>
@endsection
