@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Purchase Order: {{ $purchase->invoice_number }}</h1>
                <div class="text-sm text-gray-500">
                    {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('M d, Y') }} •
                    <span class="font-medium">{{ $purchase->supplier->name }}</span>
                </div>

            </div>
            <div class="flex space-x-2">
                <a href="{{ route('purchases.edit', $purchase->id) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="{{ route('purchases.invoice', $purchase->id) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    <i class="fas fa-file-invoice"></i> Invoice
                </a>
                <a href="{{ route('purchases.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                    Back to Purchases
                </a>
                <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg" onclick="return confirm('Are you sure?')">
                        Delete Purchase
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <h3 class="text-lg font-medium mb-2">Supplier Information</h3>
                        <div class="space-y-1">
                            <div><span class="font-medium">Name:</span> {{ $purchase->supplier->name }}</div>
                            <div><span class="font-medium">Phone:</span> {{ $purchase->supplier->phone ?? 'N/A' }}</div>
                            <div><span class="font-medium">Email:</span> {{ $purchase->supplier->email ?? 'N/A' }}</div>
                            <div><span class="font-medium">Address:</span> {{ $purchase->supplier->address ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-lg font-medium mb-2">Order Information</h3>
                        <div class="space-y-1">
                            <div><span class="font-medium">Status:</span>
                                @if($purchase->payment_status === 'paid')
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Paid</span>
                                @elseif($purchase->payment_status === 'partial')
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Partial</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Unpaid</span>
                                @endif
                            </div>
                            <div><span class="font-medium">Total Amount:</span> {{ config('settings.currency_symbol') }}{{ number_format($purchase->total_amount, 2) }}</div>
                            <div><span class="font-medium">Paid Amount:</span> {{ config('settings.currency_symbol') }}{{ number_format($purchase->paid_amount, 2) }}</div>
                            <div><span class="font-medium">Balance:</span> {{ config('settings.currency_symbol') }}{{ number_format($purchase->total_amount - $purchase->paid_amount, 2) }}</div>
                        </div>
                    </div>
                </div>

                <h3 class="text-lg font-medium mb-2">Purchase Items</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($purchase->items as $item)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        @if($item->product->image)
                                            <div class="flex-shrink-0 h-10 w-10">
                                                <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/'.$item->product->image) }}" alt="{{ $item->product->name }}">
                                            </div>
                                        @endif
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->product->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $item->product->barcode }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $item->quantity }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ config('settings.currency_symbol') }}{{ number_format($item->unit_price, 2) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ config('settings.currency_symbol') }}{{ number_format($item->total_price, 2) }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                        @php
                            $itemsSubtotal = $purchase->items->sum('total_price');
                        @endphp
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right font-medium text-gray-600">Items Subtotal</td>
                            <td class="px-6 py-4 text-sm font-medium">Rs. {{ number_format($itemsSubtotal, 2) }}</td>
                        </tr>
                        @if(!empty($purchase->expenses))
                            @foreach($purchase->expenses as $exp)
                            <tr>
                                <td colspan="3" class="px-6 py-4 text-right text-sm text-yellow-700">+ {{ $exp['label'] ?? 'Expense' }}</td>
                                <td class="px-6 py-4 text-sm text-yellow-700">Rs. {{ number_format($exp['amount'], 2) }}</td>
                            </tr>
                            @endforeach
                        @endif
                        @if(($purchase->discount ?? 0) > 0)
                        <tr>
                            <td colspan="3" class="px-6 py-4 text-right text-sm text-green-700">− Discount</td>
                            <td class="px-6 py-4 text-sm text-green-700">Rs. {{ number_format($purchase->discount, 2) }}</td>
                        </tr>
                        @endif
                        <tr class="bg-gray-50">
                            <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-800">Grand Total</td>
                            <td class="px-6 py-4 text-sm font-bold text-gray-800">Rs. {{ number_format($purchase->total_amount, 2) }}</td>
                        </tr>
                        </tfoot>
                    </table>
                </div>

                @if($purchase->notes)
                    <div class="mt-6">
                        <h3 class="text-lg font-medium mb-2">Notes</h3>
                        <div class="bg-gray-50 p-4 rounded">
                            {{ $purchase->notes }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
