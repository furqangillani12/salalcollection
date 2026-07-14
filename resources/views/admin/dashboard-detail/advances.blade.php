@extends('layouts.admin')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Advances (ایڈوانس)</h1>
                <p class="text-sm text-gray-500 mt-1">Customers who paid us in advance — we owe them</p>
            </div>
            <a href="{{ route('admin.dashboard') }}"
                class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm">
                ← Back to Dashboard
            </a>
        </div>

        {{-- Summary Card --}}
        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl p-6 shadow">
            <p class="text-sm font-medium text-blue-100 uppercase">Total Advances</p>
            <p class="text-4xl font-bold mt-2">Rs. {{ number_format($total, 0) }}</p>
            <p class="text-sm text-blue-100 mt-2">{{ $customers->count() }} customer{{ $customers->count() != 1 ? 's' : '' }} have paid in advance</p>
        </div>

        {{-- Customer List --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50">
                <h3 class="font-semibold text-gray-800">Customer Details</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">#</th>
                            <th class="px-4 py-3 text-left">Customer</th>
                            <th class="px-4 py-3 text-left">Phone</th>
                            <th class="px-4 py-3 text-right">Advance Amount</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($customers as $i => $customer)
                            <tr class="hover:bg-blue-50/50 transition">
                                <td class="px-4 py-3 text-gray-400">{{ $i + 1 }}</td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-gray-800">{{ $customer->name }}</p>
                                    @if($customer->address)
                                        <p class="text-xs text-gray-400 truncate max-w-xs">{{ $customer->address }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-600">{{ $customer->phone ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    <span class="text-lg font-bold text-blue-600">Rs. {{ number_format(abs($customer->current_balance), 0) }}</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <a href="{{ route('admin.customers.khata', $customer) }}"
                                        class="inline-flex items-center gap-1 bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-xs font-medium">
                                        <i class="fas fa-book"></i> View Khata
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400">No advance payments</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($customers->count())
                    <tfoot class="bg-gray-50 font-bold">
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-right text-gray-600">Total:</td>
                            <td class="px-4 py-3 text-right text-blue-600 text-lg">Rs. {{ number_format($total, 0) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
@endsection
