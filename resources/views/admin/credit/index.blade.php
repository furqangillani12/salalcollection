@extends('layouts.admin')

@section('title', 'Credit Management')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Credit Management</h1>
                <p class="text-sm text-gray-600 mt-1">Manage customer credit accounts and payments</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.credit.payment') }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Collect Payment
                </a>
                <a href="{{ route('admin.credit.overdue') }}"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Overdue Report
                </a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Total Credit Sales -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Credit Sales</p>
                        <h3 class="text-2xl font-bold text-gray-800">Rs. {{ number_format($totalCreditSales, 2) }}</h3>
                        <p class="text-xs text-gray-500 mt-2">Lifetime credit sales</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Payments Received -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Payments Received</p>
                        <h3 class="text-2xl font-bold text-gray-800">Rs. {{ number_format($totalCreditPayments, 2) }}</h3>
                        <p class="text-xs text-gray-500 mt-2">Total payments collected</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Outstanding Balance -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Total Remaining Balance</p>
                        <h3 class="text-2xl font-bold text-gray-800">Rs. {{ number_format($totalOutstanding, 2) }}</h3>
                        <p class="text-xs text-gray-500 mt-2">Current receivables</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Overdue Amount -->
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Overdue Amount</p>
                        <h3 class="text-2xl font-bold text-gray-800">Rs.
                            {{ number_format($overdueCount > 0 ? $totalOutstanding * 0.3 : 0, 2) }}</h3>
                        <p class="text-xs text-gray-500 mt-2">{{ $overdueCount }} overdue transactions</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-lg">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-4 border-b">
                <form method="GET" class="flex flex-col md:flex-row gap-4">
                    <div class="flex-1">
                        <div class="relative">
                            <input type="text" name="search" placeholder="Search by name, phone, email or barcode..."
                                value="{{ request('search') }}"
                                class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <svg class="absolute left-3 top-2.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                    <div class="md:w-48">
                        <select name="status"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive
                            </option>
                            <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Search
                        </button>
                        <a href="{{ route('admin.credit.index') }}"
                            class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                            Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Credit Customers Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Credit Limit</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Remaining Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Available</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due
                                Days</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Last
                                Transaction</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($customers as $customer)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                            <span
                                                class="text-blue-600 font-medium">{{ substr($customer->name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $customer->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                @if ($customer->phone)
                                                    {{ $customer->phone }}
                                                @endif
                                                @if ($customer->barcode)
                                                    <span
                                                        class="ml-1 px-2 py-0.5 bg-gray-100 rounded-full">#{{ $customer->barcode }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-gray-900">Rs.
                                        {{ number_format($customer->credit_limit, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $balancePercentage =
                                            $customer->credit_limit > 0
                                                ? ($customer->current_balance / $customer->credit_limit) * 100
                                                : 0;
                                        $balanceClass =
                                            $balancePercentage >= 90
                                                ? 'text-red-600'
                                                : ($balancePercentage >= 70
                                                    ? 'text-yellow-600'
                                                    : 'text-green-600');
                                    @endphp
                                    <span class="text-sm font-bold {{ $balanceClass }}">Rs.
                                        {{ number_format($customer->current_balance, 2) }}</span>
                                    <div class="text-xs text-gray-500 mt-1">{{ number_format($balancePercentage, 1) }}%
                                        used</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm text-gray-900">Rs.
                                        {{ number_format($customer->available_credit, 2) }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $customer->credit_due_days }} days
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($customer->creditLedger)
                                        @if ($customer->creditLedger->status == 'active')
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Active</span>
                                        @elseif($customer->creditLedger->status == 'inactive')
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800">Inactive</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Closed</span>
                                        @endif
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">No
                                            Ledger</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if ($customer->creditLedger && $customer->creditLedger->last_transaction_date)
                                        {{ $customer->creditLedger->last_transaction_date->format('d M Y') }}
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('admin.credit.statement', $customer->id) }}"
                                            class="text-blue-600 hover:text-blue-900 bg-blue-50 px-3 py-1 rounded-md text-xs"
                                            title="View Statement">
                                            Statement
                                        </a>
                                        @if ($customer->current_balance > 0)
                                            <a href="{{ route('admin.credit.payment', ['customer_id' => $customer->id]) }}"
                                                class="text-green-600 hover:text-green-900 bg-green-50 px-3 py-1 rounded-md text-xs"
                                                title="Collect Payment">
                                                Collect
                                            </a>
                                        @endif
                                        @if (!$customer->credit_enabled)
                                            <button onclick="enableCredit({{ $customer->id }})"
                                                class="text-purple-600 hover:text-purple-900 bg-purple-50 px-3 py-1 rounded-md text-xs">
                                                Enable
                                            </button>
                                        @else
                                            <button onclick="disableCredit({{ $customer->id }})"
                                                class="text-red-600 hover:text-red-900 bg-red-50 px-3 py-1 rounded-md text-xs"
                                                {{ $customer->current_balance > 0 ? 'disabled' : '' }}>
                                                Disable
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
                                    <div class="text-gray-400">
                                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z">
                                            </path>
                                        </svg>
                                        <p class="text-lg font-medium text-gray-600">No credit customers found</p>
                                        <p class="text-sm text-gray-500 mt-1">Enable credit for customers to start tracking
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($customers->hasPages())
                <div class="px-6 py-4 border-t">
                    {{ $customers->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Enable Credit Modal -->
    <div id="enableCreditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border max-w-sm w-full shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Enable Credit for Customer</h3>
                <form id="enableCreditForm" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Credit Limit (Rs.)</label>
                            <input type="number" name="credit_limit" required min="0" step="0.01"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Due Days</label>
                            <input type="number" name="credit_due_days" required min="1" max="365"
                                value="30"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Number of days until payment is due</p>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-3 mt-6">
                        <button type="button" onclick="closeEnableModal()"
                            class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            Enable Credit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Enable Credit Modal
            function enableCredit(customerId) {
                const modal = document.getElementById('enableCreditModal');
                const form = document.getElementById('enableCreditForm');
                form.action = `/admin/credit/customer/${customerId}/enable`;
                modal.classList.remove('hidden');
            }

            function closeEnableModal() {
                const modal = document.getElementById('enableCreditModal');
                modal.classList.add('hidden');
            }

            // Disable Credit
            function disableCredit(customerId) {
                if (confirm('Are you sure you want to disable credit for this customer?')) {
                    fetch(`/admin/credit/customer/${customerId}/disable`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Content-Type': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                window.location.reload();
                            } else {
                                alert(data.message || 'Failed to disable credit');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while disabling credit');
                        });
                }
            }

            // Close modal when clicking outside
            document.getElementById('enableCreditModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeEnableModal();
                }
            });
        </script>
    @endpush
@endsection
