@extends('layouts.admin')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Receivables / وصولی</h1>
            <p class="text-sm text-gray-500 mt-1">All orders with outstanding balance — including partial payments</p>
        </div>
        <a href="{{ route('admin.dashboard') }}"
            class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-sm">
            ← Back to Dashboard
        </a>
    </div>

    {{-- Summary --}}
    <div class="bg-gradient-to-br from-red-500 to-red-600 text-white rounded-xl p-6 shadow">
        <p class="text-sm font-medium text-red-100 uppercase tracking-wide">Total Receivables (کل وصولی)</p>
        <p class="text-4xl font-bold mt-2">Rs. {{ number_format($total, 0) }}</p>
        <p class="text-sm text-red-100 mt-2">
            {{ count($customerRows) }} customer{{ count($customerRows) != 1 ? 's' : '' }}
            @if($walkinOrders->count()) + {{ $walkinOrders->count() }} walk-in order{{ $walkinOrders->count() != 1 ? 's' : '' }} @endif
        </p>
    </div>

    {{-- Named Customers --}}
    @if(count($customerRows))
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
            <h3 class="font-semibold text-gray-800">Customer Balances</h3>
            <span class="text-xs text-gray-400">Sorted by amount due</span>
        </div>
        <div class="divide-y divide-gray-100">
            @foreach($customerRows as $i => $row)
            @php $customer = $row['customer']; @endphp
            <details class="group">
                <summary class="flex items-center gap-3 px-5 py-4 cursor-pointer hover:bg-red-50/40 transition list-none">
                    <span class="text-gray-400 text-xs w-5">{{ $i + 1 }}</span>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-800">{{ $customer?->name ?? 'Unknown' }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $customer?->phone ?? '—' }}
                            · {{ $row['order_count'] }} order{{ $row['order_count'] != 1 ? 's' : '' }} pending
                        </p>
                    </div>
                    <span class="text-lg font-bold text-red-600 whitespace-nowrap">
                        Rs. {{ number_format($row['total_due'], 0) }}
                    </span>
                    <svg class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform flex-shrink-0"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </summary>

                {{-- Order detail rows --}}
                <div class="bg-gray-50 border-t border-gray-100 overflow-x-auto">
                    @foreach($row['orders'] as $order)
                    <div class="flex flex-wrap items-center gap-3 px-8 py-3 text-sm border-b border-gray-100 last:border-0 min-w-0">
                        <div class="flex-1">
                            <a href="{{ route('admin.pos.receipt', $order) }}"
                                class="font-medium text-blue-600 hover:underline">#{{ $order->order_number }}</a>
                            <span class="ml-2 text-xs text-gray-400">
                                {{ $order->created_at->format('d M Y') }}
                                · {{ ucfirst($order->order_type ?? 'pos') }}
                                @if($order->payment_method)
                                    · {{ ucfirst($order->payment_method) }}
                                @endif
                            </span>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-gray-400">
                                Total: Rs. {{ number_format($order->total, 0) }}
                                · Paid: Rs. {{ number_format($order->paid_amount, 0) }}
                            </div>
                            <div class="font-semibold text-red-600">
                                Due: Rs. {{ number_format($order->balance_amount, 0) }}
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @if($customer)
                    <div class="px-8 py-2 flex gap-2">
                        <a href="{{ route('admin.customers.khata', $customer) }}"
                            class="text-xs bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded font-medium">
                            <i class="fas fa-book mr-1"></i> View Khata
                        </a>
                        <a href="{{ route('admin.customers.show', $customer) }}"
                            class="text-xs bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-1.5 rounded font-medium">
                            Profile
                        </a>
                    </div>
                    @endif
                </div>
            </details>
            @endforeach
        </div>
        <div class="px-5 py-3 bg-gray-50 border-t flex justify-between font-bold text-sm">
            <span class="text-gray-600">Total (named customers)</span>
            <span class="text-red-600">Rs. {{ number_format(collect($customerRows)->sum('total_due'), 0) }}</span>
        </div>
    </div>
    @endif

    {{-- Walk-in Orders --}}
    @if($walkinOrders->count())
    <div class="bg-white rounded-xl shadow-sm border border-orange-200 overflow-hidden">
        <div class="px-6 py-4 border-b bg-orange-50 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-orange-800">Walk-in Orders with Pending Balance</h3>
                <p class="text-xs text-orange-600 mt-0.5">These orders have no customer record — assign a customer to track properly</p>
            </div>
            <span class="font-bold text-orange-700">Rs. {{ number_format($walkinOrders->sum('balance_amount'), 0) }}</span>
        </div>
        <div class="overflow-x-auto min-w-full">
            <table class="min-w-full text-sm divide-y divide-gray-100">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Order #</th>
                        <th class="px-4 py-3 text-left">Date</th>
                        <th class="px-4 py-3 text-left">Type</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-right">Paid</th>
                        <th class="px-4 py-3 text-right">Due</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($walkinOrders as $order)
                    <tr class="hover:bg-orange-50/40">
                        <td class="px-4 py-3 font-medium text-gray-800">#{{ $order->order_number }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $order->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ ucfirst($order->order_type ?? 'pos') }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rs. {{ number_format($order->total, 0) }}</td>
                        <td class="px-4 py-3 text-right text-green-600">Rs. {{ number_format($order->paid_amount, 0) }}</td>
                        <td class="px-4 py-3 text-right font-bold text-red-600">Rs. {{ number_format($order->balance_amount, 0) }}</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.pos.receipt', $order) }}"
                                class="text-blue-500 hover:text-blue-700 text-xs">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gray-50 font-bold">
                    <tr>
                        <td colspan="5" class="px-4 py-3 text-right text-gray-600">Total Walk-in Due:</td>
                        <td class="px-4 py-3 text-right text-orange-600">Rs. {{ number_format($walkinOrders->sum('balance_amount'), 0) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    @if(!count($customerRows) && $walkinOrders->isEmpty())
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 px-6 py-12 text-center text-gray-400">
        <i class="fas fa-check-circle text-4xl text-green-400 mb-3 block"></i>
        No outstanding receivables 🎉
    </div>
    @endif

</div>
@endsection
