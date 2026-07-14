@extends('layouts.admin')

@section('title', 'Create Ledger Account')

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-2xl">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.ledger-accounts.index') }}" class="text-gray-500 hover:text-gray-700 dark:text-gray-400">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">📚 Create Ledger Account</h1>
                <p class="text-sm text-gray-500">Set up a new account head for tracking finances</p>
            </div>
        </div>

        {{-- Type Guide --}}
        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-700 rounded-lg p-4 mb-6">
            <p class="text-sm font-semibold text-blue-700 dark:text-blue-300 mb-2">💡 Which type to choose?</p>
            <div class="grid grid-cols-2 gap-2 text-xs text-blue-600 dark:text-blue-400">
                <div>✅ <strong>Expense</strong> — Shop Rent, Electricity, Salary, Tea/Food</div>
                <div>✅ <strong>Income</strong> — Sales Revenue, Commission, Profit</div>
                <div>✅ <strong>Asset</strong> — Cash in Hand, Bank Account, Equipment</div>
                <div>✅ <strong>Liability</strong> — Loans, Supplier Dues, Tax Payable</div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form method="POST" action="{{ route('admin.ledger-accounts.store') }}">
                @csrf

                {{-- Account Code (auto-generated) --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Account Code
                    </label>
                    <input type="text" value="{{ $accountCode }}" readonly
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 dark:bg-gray-700 dark:border-gray-600 font-mono text-gray-500">
                    <p class="text-xs text-gray-400 mt-1">Auto-generated — cannot be changed</p>
                </div>

                {{-- Ledger Name --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Ledger Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        placeholder="e.g. Shop Rent, Electricity Bill, Sales Account..."
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Account Type --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Account Type <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        @foreach (\App\Models\LedgerAccount::TYPE_LABELS as $value => $label)
                            @php
                                $bgColors = [
                                    'expense' =>
                                        'border-red-300 bg-red-50 text-red-700 peer-checked:border-red-500 peer-checked:bg-red-100',
                                    'income' =>
                                        'border-green-300 bg-green-50 text-green-700 peer-checked:border-green-500 peer-checked:bg-green-100',
                                    'asset' =>
                                        'border-blue-300 bg-blue-50 text-blue-700 peer-checked:border-blue-500 peer-checked:bg-blue-100',
                                    'liability' =>
                                        'border-orange-300 bg-orange-50 text-orange-700 peer-checked:border-orange-500 peer-checked:bg-orange-100',
                                ];
                                $icons = ['expense' => '🧾', 'income' => '💰', 'asset' => '🏦', 'liability' => '📋'];
                            @endphp
                            <label class="cursor-pointer">
                                <input type="radio" name="type" value="{{ $value }}" class="peer hidden"
                                    {{ old('type') == $value ? 'checked' : '' }} required>
                                <div
                                    class="border-2 rounded-lg p-3 text-center text-sm font-medium transition {{ $bgColors[$value] ?? '' }} peer-checked:ring-2">
                                    <div class="text-xl mb-1">{{ $icons[$value] ?? '📌' }}</div>
                                    {{ $label }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                    @error('type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Category --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Category <span class="text-xs text-gray-400">(optional — for grouping)</span>
                    </label>
                    <input type="text" name="category" value="{{ old('category') }}"
                        placeholder="e.g. Utilities, Office Expenses, Shop Expenses..."
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Group similar ledgers (e.g. Electricity + Water + Gas →
                        "Utilities")</p>
                </div>

                {{-- Opening Balance --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Opening Balance <span class="text-xs text-gray-400">(Rs. — if any existing balance)</span>
                    </label>
                    <input type="number" name="opening_balance" value="{{ old('opening_balance', 0) }}" min="0"
                        step="0.01"
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-400 mt-1">Leave 0 if starting fresh</p>
                </div>

                {{-- Description --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Description <span class="text-xs text-gray-400">(optional)</span>
                    </label>
                    <textarea name="description" rows="2" placeholder="Brief description of this ledger account..."
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                        ✅ Create Ledger Account
                    </button>
                    <a href="{{ route('admin.ledger-accounts.index') }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg dark:bg-gray-600 dark:text-gray-200">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
