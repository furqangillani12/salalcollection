@extends('layouts.admin')

@section('content')
    <div class="p-6 bg-white rounded-lg shadow-md">
        <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
            <h1 class="text-2xl font-semibold text-gray-800">Sales Report</h1>
            {{-- Active filter badges --}}
            <div class="flex gap-2 flex-wrap">
                @if(request('payment_method'))
                    <span class="bg-indigo-100 text-indigo-700 text-xs px-3 py-1 rounded-full font-medium">
                        Payment: {{ ucfirst(request('payment_method')) }}
                        <a href="{{ route('admin.reports.sales', array_diff_key(request()->query(), ['payment_method' => ''])) }}" class="ml-1 text-indigo-500 hover:text-indigo-800">×</a>
                    </span>
                @endif
                @if(request('status') === 'pending')
                    <span class="bg-orange-100 text-orange-700 text-xs px-3 py-1 rounded-full font-medium">
                        Pending Payment
                        <a href="{{ route('admin.reports.sales', array_diff_key(request()->query(), ['status' => ''])) }}" class="ml-1 text-orange-500 hover:text-orange-800">×</a>
                    </span>
                @endif
            </div>
        </div>

        {{-- Filter Form --}}
        <form method="GET" class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
            <input type="hidden" name="payment_method" value="{{ request('payment_method') }}">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <div>
                <label for="order_number" class="block text-sm font-medium text-gray-700">Order Number</label>
                <input type="text" name="order_number" id="order_number"
                       value="{{ request('order_number') }}"
                       placeholder="e.g. ASM15"
                       class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ $start }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ $end }}" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>

            <div class="sm:self-end">
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Filter
                </button>
            </div>
        </form>


        {{-- Summary --}}
        <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-4 bg-green-100 border border-green-300 rounded-lg text-green-800">
                <h2 class="text-lg font-bold">Total Sales</h2>
                <p class="text-xl">Rs. {{ number_format($totalSales, 2) }}</p>
            </div>
            <div class="p-4 bg-blue-100 border border-blue-300 rounded-lg text-blue-800">
                <h2 class="text-lg font-bold">Total Orders</h2>
                <p class="text-xl">{{ $totalOrders }}</p>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border border-gray-200 shadow-sm rounded-lg overflow-hidden">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Customer</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @forelse($orders as $order)
                    <tr class="{{ $order->balance_amount > 0 ? 'bg-orange-50/30' : '' }}">
                        <td class="px-4 py-3 whitespace-nowrap font-mono text-sm">{{ $order->order_number }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $order->customer?->name ?? 'Walk-in' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right font-semibold">Rs. {{ number_format($order->total, 0) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right text-green-600">Rs. {{ number_format($order->paid_amount, 0) }}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-right">
                            @if($order->balance_amount > 0)
                                <span class="text-red-600 font-bold">Rs. {{ number_format($order->balance_amount, 0) }}</span>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs">
                            <span class="px-2 py-0.5 rounded-full capitalize
                                {{ $order->payment_method === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-gray-100 text-gray-700' }}">
                                {{ str_replace('_', ' ', $order->payment_method) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">{{ $order->created_at->format('d M, Y h:i A') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1">
                                @if($order->balance_amount > 0 && $order->customer_id)
                                    <a href="{{ route('admin.customers.khata', $order->customer_id) }}"
                                       class="inline-flex items-center px-2 py-1 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-700"
                                       title="Receive Payment">
                                        <i class="fas fa-money-bill-wave mr-1"></i> Pay
                                    </a>
                                @endif
                                <a href="{{ route('admin.pos.receipt', $order) }}" target="_blank"
                                   class="inline-flex items-center px-2 py-1 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-700"
                                   title="Print Receipt">
                                    <i class="fas fa-print mr-1"></i>
                                </a>
                                <a href="{{ route('admin.pos.edit', $order) }}"
                                   class="inline-flex items-center px-2 py-1 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600"
                                   title="Edit Order">
                                    <i class="fas fa-edit mr-1"></i>
                                </a>
                                @if($order->status !== 'cancelled' && $order->status !== 'refunded')
                                    <form action="{{ route('admin.pos.cancel', $order) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Cancel order #{{ $order->order_number }}? Stock will be restored.')">
                                        @csrf
                                        <button type="submit"
                                                class="inline-flex items-center px-2 py-1 bg-orange-500 text-white text-xs font-medium rounded hover:bg-orange-600"
                                                title="Cancel Order">
                                            <i class="fas fa-ban mr-1"></i> Cancel
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.pos.delete', $order) }}" method="POST" class="inline"
                                      onsubmit="return confirm('DELETE order #{{ $order->order_number }}? This cannot be undone!')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center px-2 py-1 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700"
                                            title="Delete Order">
                                        <i class="fas fa-trash mr-1"></i> Delete
                                    </button>
                                </form>
                            </div>
                            @if($order->status === 'cancelled')
                                <span class="inline-block mt-1 px-2 py-0.5 bg-gray-200 text-gray-600 text-xs rounded-full">Cancelled</span>
                            @elseif($order->status === 'refunded')
                                <span class="inline-block mt-1 px-2 py-0.5 bg-red-100 text-red-600 text-xs rounded-full">Refunded</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">No orders found for selected date range.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
