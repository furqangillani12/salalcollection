@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Purchase Orders</h1>
            <a href="{{ route('purchases.create') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                Create New Purchase
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice #</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($purchases as $purchase)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $purchase->invoice_number }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900">{{ $purchase->supplier->name }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('M d, Y') }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ config('settings.currency_symbol') }}{{ number_format($purchase->total_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($purchase->payment_status === 'paid')
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Paid</span>
                                @elseif($purchase->payment_status === 'partial')
                                    <span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Partial</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Unpaid</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('purchases.edit', $purchase->id) }}" class="text-yellow-500 hover:text-yellow-700 mr-3" title="Edit"><i class="fas fa-edit"></i></a>
                                <a href="{{ route('purchases.invoice', $purchase->id) }}" class="text-green-500 hover:text-green-700 mr-3" title="Invoice"><i class="fas fa-file-invoice"></i></a>
                                <a href="{{ route('purchases.show', $purchase->id) }}" class="text-blue-500 hover:text-blue-700 mr-3">View</a>
                                <form action="{{ route('purchases.destroy', $purchase->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700" onclick="return confirm('Are you sure?')">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">No purchase orders found</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>
@endsection
