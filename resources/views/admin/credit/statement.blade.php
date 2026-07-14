@extends('layouts.admin')

@section('title', 'Credit Statement - ' . $customer->name)

@section('content')
    <div class="container mx-auto px-4 py-6">
        <!-- Header -->
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Credit Statement — {{ $customer->name }}</h1>
                <p class="text-sm text-gray-600 mt-1">
                    @if(request('from_date') || request('to_date'))
                        {{ request('from_date', 'Start') }} to {{ request('to_date', 'Today') }}
                    @else
                        Complete transaction history
                    @endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.credit.payment', ['customer_id' => $customer->id]) }}"
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Collect Payment
                </a>
                <a href="{{ route('admin.credit.statement.export', $customer->id) }}"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                    </svg>
                    Export PDF
                </a>
                <a href="{{ route('admin.credit.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back
                </a>
            </div>
        </div>

        <!-- Customer Info Card -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-6">
                <div class="flex items-start justify-between">
                    <div class="flex items-center">
                        <div class="h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-2xl text-blue-600 font-bold">{{ substr($customer->name, 0, 1) }}</span>
                        </div>
                        <div class="ml-4">
                            <h2 class="text-xl font-bold text-gray-800">{{ $customer->name }}</h2>
                            <div class="flex items-center space-x-4 mt-2">
                                @if ($customer->phone)
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z">
                                            </path>
                                        </svg>
                                        {{ $customer->phone }}
                                    </div>
                                @endif
                                @if ($customer->email)
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        {{ $customer->email }}
                                    </div>
                                @endif
                                @if ($customer->barcode)
                                    <div class="flex items-center text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                        {{ $customer->barcode }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div>
                        <span
                            class="px-3 py-1 text-sm font-medium rounded-full {{ $ledger->status == 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                            {{ ucfirst($ledger->status) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Balance Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg shadow p-6">
                <p class="text-sm text-blue-600 mb-1">Credit Limit</p>
                <h3 class="text-2xl font-bold text-gray-800">Rs. {{ number_format($summary['credit_limit'], 2) }}</h3>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg shadow p-6">
                <p class="text-sm text-purple-600 mb-1">Total Purchases</p>
                <h3 class="text-2xl font-bold text-gray-800">Rs. {{ number_format($summary['total_purchases'], 2) }}</h3>
            </div>

            <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg shadow p-6">
                <p class="text-sm text-green-600 mb-1">Total Payments</p>
                <h3 class="text-2xl font-bold text-gray-800">Rs. {{ number_format($summary['total_payments'], 2) }}</h3>
            </div>

            <div
                class="bg-gradient-to-br from-{{ $summary['current_balance'] > $summary['credit_limit'] * 0.8 ? 'red' : ($summary['current_balance'] > $summary['credit_limit'] * 0.5 ? 'yellow' : 'green') }}-50 to-{{ $summary['current_balance'] > $summary['credit_limit'] * 0.8 ? 'red' : ($summary['current_balance'] > $summary['credit_limit'] * 0.5 ? 'yellow' : 'green') }}-100 rounded-lg shadow p-6">
                <p
                    class="text-sm text-{{ $summary['current_balance'] > $summary['credit_limit'] * 0.8 ? 'red' : ($summary['current_balance'] > $summary['credit_limit'] * 0.5 ? 'yellow' : 'green') }}-600 mb-1">
                    Current Balance</p>
                <h3 class="text-2xl font-bold text-gray-800">Rs. {{ number_format($summary['current_balance'], 2) }}</h3>
                <p class="text-xs text-gray-600 mt-1">Available: Rs. {{ number_format($summary['available_credit'], 2) }}
                </p>
            </div>
        </div>

        <!-- Overdue Warning -->
        @if ($summary['overdue_amount'] > 0)
            <div class="bg-red-50 border-l-4 border-red-500 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-red-700 font-medium">Overdue Amount: Rs.
                        {{ number_format($summary['overdue_amount'], 2) }}</span>
                    <span class="text-red-600 text-sm ml-2">Please collect payment immediately</span>
                </div>
            </div>
        @endif

        <!-- Date Filter -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="{{ route('admin.credit.statement', $customer) }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}"
                        class="border rounded-lg px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}"
                        class="border rounded-lg px-3 py-2 text-sm">
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm">Filter</button>
                <a href="{{ route('admin.credit.statement', $customer) }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm">Reset</a>
            </form>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h3 class="text-lg font-medium text-gray-800">Transaction History
                    @if(request('from_date') || request('to_date'))
                        <span class="text-sm font-normal text-gray-500">— {{ request('from_date', 'Start') }} to {{ request('to_date', 'Today') }}</span>
                    @endif
                </h3>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Transaction #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Order #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Description</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Debit (Purchase)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Credit (Payment)</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Balance</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due
                                Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transaction->transaction_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="text-sm font-medium text-gray-900">{{ $transaction->transaction_number }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($transaction->order_id)
                                        <a href="{{ route('admin.pos.receipt', $transaction->order_id) }}" target="_blank"
                                            class="font-semibold text-blue-600 hover:underline">
                                            {{ $transaction->order->order_number ?? '#' . $transaction->order_id }}
                                        </a>
                                    @else
                                        <span class="text-gray-400">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                                    {{ $transaction->description }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    @if ($transaction->transaction_type == 'debit')
                                        <span class="text-red-600 font-medium">Rs.
                                            {{ number_format($transaction->amount, 2) }}</span>
                                        @if ($transaction->payment_status == 'partial')
                                            <span class="text-xs text-yellow-600 block">Paid: Rs.
                                                {{ number_format($transaction->paid_amount, 2) }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right">
                                    @if ($transaction->transaction_type == 'credit')
                                        <span class="text-green-600 font-medium">Rs.
                                            {{ number_format($transaction->amount, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium">
                                    Rs. {{ number_format($transaction->balance_after, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if ($transaction->due_date)
                                        @if ($transaction->due_date < now() && $transaction->payment_status != 'paid')
                                            <span
                                                class="text-red-600">{{ $transaction->due_date->format('d M Y') }}</span>
                                            <span class="text-xs text-red-500 block">Overdue</span>
                                        @else
                                            {{ $transaction->due_date->format('d M Y') }}
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if ($transaction->transaction_type == 'debit')
                                        @php
                                            $statusClass =
                                                [
                                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                                    'partial' => 'bg-blue-100 text-blue-800',
                                                    'paid' => 'bg-green-100 text-green-800',
                                                    'overdue' => 'bg-red-100 text-red-800',
                                                ][$transaction->payment_status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                                            {{ ucfirst($transaction->payment_status) }}
                                        </span>
                                        @if ($transaction->remaining_amount > 0)
                                            <span class="text-xs text-gray-500 block mt-1">
                                                Due: Rs. {{ number_format($transaction->remaining_amount, 2) }}
                                            </span>
                                        @endif
                                    @else
                                        <span
                                            class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">
                                            Completed
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-6 py-12 text-center">
                                    <div class="text-gray-400">
                                        <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                            </path>
                                        </svg>
                                        <p class="text-gray-500">No transactions found</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($transactions->hasPages())
                <div class="px-6 py-4 border-t">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
