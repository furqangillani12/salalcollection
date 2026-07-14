@extends('layouts.admin')

@section('title', 'Overdue Payments Report')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Overdue Payments</h1>
                <p class="text-sm text-gray-600 mt-1">Transactions past due date</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.credit.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to Credit
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                <p class="text-sm text-gray-600 mb-1">Total Overdue Amount</p>
                <h3 class="text-2xl font-bold text-gray-800">Rs. {{ number_format($totalOverdue, 2) }}</h3>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                <p class="text-sm text-gray-600 mb-1">Customers with Overdue</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $totalCustomers }}</h3>
            </div>
            <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
                <p class="text-sm text-gray-600 mb-1">Overdue Transactions</p>
                <h3 class="text-2xl font-bold text-gray-800">{{ $transactions->total() }}</h3>
            </div>
        </div>

        <!-- Filter -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="p-4 border-b">
                <form method="GET" class="flex flex-col md:flex-row gap-4">
                    <div class="md:w-48">
                        <select name="days"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <option value="">All Overdue</option>
                            <option value="7" {{ request('days') == 7 ? 'selected' : '' }}>Overdue by 1-7 days</option>
                            <option value="15" {{ request('days') == 15 ? 'selected' : '' }}>Overdue by 8-15 days
                            </option>
                            <option value="30" {{ request('days') == 30 ? 'selected' : '' }}>Overdue by 16-30 days
                            </option>
                            <option value="60" {{ request('days') == 60 ? 'selected' : '' }}>Overdue by 30+ days
                            </option>
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Filter
                        </button>
                        <a href="{{ route('admin.credit.overdue') }}"
                            class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                            Clear
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Transaction #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice #</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Purchase Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Overdue</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Remaining</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($transactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-8 w-8 bg-red-100 rounded-full flex items-center justify-center">
                                            <span
                                                class="text-red-600 font-medium text-sm">{{ substr($transaction->customer->name, 0, 1) }}</span>
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $transaction->customer->name }}</div>
                                            <div class="text-xs text-gray-500">
                                                {{ $transaction->customer->phone ?? 'No phone' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transaction->transaction_number }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $transaction->reference_number ?? 'N/A' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $transaction->transaction_date->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-medium text-red-600">
                                        {{ $transaction->due_date->format('d M Y') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $daysOverdue = now()->diffInDays($transaction->due_date);
                                        $severity =
                                            $daysOverdue >= 30 ? 'red' : ($daysOverdue >= 15 ? 'yellow' : 'orange');
                                    @endphp
                                    <span
                                        class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $severity }}-100 text-{{ $severity }}-800">
                                        {{ $daysOverdue }} days
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-gray-900">
                                    Rs. {{ number_format($transaction->amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right text-green-600">
                                    Rs. {{ number_format($transaction->paid_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-red-600">
                                    Rs. {{ number_format($transaction->remaining_amount, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="{{ route('admin.credit.payment', ['customer_id' => $transaction->customer_id]) }}"
                                        class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs rounded-md hover:bg-green-700">
                                        Collect Payment
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-6 py-12 text-center">
                                    <div class="text-gray-400">
                                        <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-500">No overdue payments found</p>
                                        <p class="text-sm text-gray-400 mt-1">All credit payments are up to date</p>
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
