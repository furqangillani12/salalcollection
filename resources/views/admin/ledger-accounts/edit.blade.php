@extends('layouts.admin')

@section('title', 'Edit Ledger Account')

@section('content')
    <div class="container mx-auto px-4 py-6 max-w-2xl">

        <div class="flex items-center gap-3 mb-6">
            <a href="{{ route('admin.ledger-accounts.show', $ledgerAccount) }}" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">
                ✏️ Edit — {{ $ledgerAccount->name }}
            </h1>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <form method="POST" action="{{ route('admin.ledger-accounts.update', $ledgerAccount) }}">
                @csrf @method('PUT')

                {{-- Account Code (read-only) --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Account Code</label>
                    <input type="text" value="{{ $ledgerAccount->account_code }}" readonly
                        class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 dark:bg-gray-700 font-mono text-gray-500">
                </div>

                {{-- Name --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Ledger Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $ledgerAccount->name) }}" required
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Type --}}
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
                                    {{ old('type', $ledgerAccount->type) == $value ? 'checked' : '' }}>
                                <div
                                    class="border-2 rounded-lg p-3 text-center text-sm font-medium transition {{ $bgColors[$value] ?? '' }} peer-checked:ring-2">
                                    <div class="text-xl mb-1">{{ $icons[$value] ?? '📌' }}</div>
                                    {{ $label }}
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                {{-- Category --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                    <input type="text" name="category" value="{{ old('category', $ledgerAccount->category) }}"
                        placeholder="e.g. Utilities, Office Expenses..."
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                {{-- Opening Balance --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Opening Balance
                        (Rs.)</label>
                    <input type="number" name="opening_balance"
                        value="{{ old('opening_balance', $ledgerAccount->opening_balance) }}" min="0" step="0.01"
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                </div>

                {{-- Description --}}
                <div class="mb-5">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea name="description" rows="2"
                        class="w-full border rounded-lg px-3 py-2 text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-white">{{ old('description', $ledgerAccount->description) }}</textarea>
                </div>

                {{-- Active --}}
                <div class="mb-6 flex items-center gap-3">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ $ledgerAccount->is_active ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded">
                    <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
                        Active (visible in ledger list)
                    </label>
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">
                        💾 Save Changes
                    </button>
                    <a href="{{ route('admin.ledger-accounts.show', $ledgerAccount) }}"
                        class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg dark:bg-gray-600 dark:text-gray-200">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
