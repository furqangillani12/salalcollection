@extends('layouts.admin')

@section('content')
    <div class="p-6 bg-white rounded-lg shadow-md">
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Customers</h1>
                <p class="text-sm text-gray-600 mt-1">Manage customers for your POS system</p>
            </div>
            <div class="flex flex-wrap gap-2">

                <a href="{{ route('admin.customers.create') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center">
                    <svg class="h-5 w-5 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                            clip-rule="evenodd" />
                    </svg>
                    Add Customer
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-4 p-3 bg-yellow-100 text-yellow-800 rounded border border-yellow-200">
                {{ session('warning') }}
            </div>
        @endif

        <!-- Search and Filter Bar -->
        <form method="GET" action="{{ route('admin.customers.index') }}" class="mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="sm:col-span-1">
                    <input type="text" name="search" id="searchInput" value="{{ request('search') }}" placeholder="Search name, phone, barcode..."
                        class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm" autofocus>
                </div>
                <div>
                    <select name="type" id="typeFilter" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm">
                        <option value="">All Types</option>
                        <option value="customer" {{ request('type') === 'customer' ? 'selected' : '' }}>Customer</option>
                        <option value="reseller" {{ request('type') === 'reseller' ? 'selected' : '' }}>Reseller</option>
                        <option value="wholesale" {{ request('type') === 'wholesale' ? 'selected' : '' }}>Wholesale</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">
                        Search
                    </button>
                    <a href="{{ route('admin.customers.index') }}"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 text-sm">
                        Clear
                    </a>
                </div>
            </div>
        </form>



        <div class="overflow-x-auto rounded-lg border border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type
                        </th>

                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions
                        </th>
                    </tr>
                </thead>
                <tbody id="customerTableBody" class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $customer)
                        <tr data-searchable="{{ $customer->name }} {{ $customer->phone }} {{ $customer->email }} {{ $customer->barcode }} {{ $customer->address }}"
                            data-type="{{ $customer->customer_type }}"
                            class="hover:bg-gray-50 {{ $customer->credit_enabled && $customer->current_balance > $customer->credit_limit * 0.8 ? 'bg-red-50' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($customer->barcode)
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <svg class="h-3 w-3 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M1 4a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1V4zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1V4zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V4zM1 9a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1V9zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1V9zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V9zM1 14a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1v-2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1v-2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1v-2z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        {{ $customer->barcode }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-sm">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div
                                        class="flex-shrink-0 h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center">
                                        <span
                                            class="text-gray-600 font-medium text-sm">{{ substr($customer->name, 0, 1) }}</span>
                                    </div>
                                    <div class="ml-3">
                                        <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                        @if ($customer->address)
                                            <div class="text-xs text-gray-500 truncate max-w-xs">{{ $customer->address }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">
                                    @if ($customer->phone)
                                        <div class="flex items-center">
                                            <svg class="h-4 w-4 mr-1 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path
                                                    d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                            </svg>
                                            {{ $customer->phone }}
                                        </div>
                                    @endif
                                    @if ($customer->email)
                                        <div class="text-xs text-gray-500 truncate max-w-xs">{{ $customer->email }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if ($customer->customer_type === 'reseller')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        🏪 Reseller
                                    </span>
                                @elseif($customer->customer_type === 'wholesale')
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        🏭 Wholesale
                                    </span>
                                @else
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        🛒 Customer
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.customers.show', $customer) }}"
                                        class="text-blue-600 hover:text-blue-900 flex items-center" title="View Details">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd"
                                                d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </a>

                                    <a href="{{ route('admin.customers.khata', $customer) }}"
                                        class="text-emerald-600 hover:text-emerald-800 text-xs font-medium"
                                        title="View Khata">
                                        📒 Khata
                                    </a>

                                    <a href="{{ route('admin.customers.edit', $customer) }}"
                                        class="text-yellow-600 hover:text-yellow-900 flex items-center" title="Edit">
                                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path
                                                d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                        </svg>
                                    </a>

                                    <form action="{{ route('admin.customers.destroy', $customer) }}" method="POST"
                                        onsubmit="return confirm('Are you sure you want to delete this customer?');"
                                        class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 flex items-center"
                                            title="Delete">
                                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="text-gray-400">
                                    <svg class="h-12 w-12 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <p class="text-lg font-medium text-gray-600">No customers found</p>
                                    <p class="text-sm text-gray-500 mt-1">Get started by creating your first customer</p>
                                    <a href="{{ route('admin.customers.create') }}"
                                        class="mt-4 inline-block px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                                        Add First Customer
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    <tr id="noFilterResults" style="display:none">
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                            No customers match your search.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $customers->appends(request()->query())->links() }}
        </div>
    </div>

    <script>
        // Auto-submit form when user stops typing (1 second delay)
        let searchTimer;
        const searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => {
                this.closest('form').submit();
            }, 1000);
        });

        // On page load: if search is active, focus input and place cursor at end
        if (searchInput.value) {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }

        // Submit on type filter change
        document.getElementById('typeFilter').addEventListener('change', function() {
            this.closest('form').submit();
        });
    </script>
@endsection
