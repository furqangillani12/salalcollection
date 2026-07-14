@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-wrap justify-between items-center gap-2 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Inventory Logs</h1>
            <a href="{{ route('inventory.index') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                Back to Inventory
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b">
                <form method="GET" class="flex flex-col sm:flex-row sm:items-end gap-4">
                    <div class="w-full sm:flex-1">
                        <label for="product_id" class="block text-sm font-medium text-gray-700">Filter by Product</label>
                        <select name="product_id" id="product_id" onchange="this.form.submit()"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">All Products</option>
                            @foreach($products as $product)
                                <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->name }} ({{ $product->barcode }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-full sm:flex-1">
                        <label for="action" class="block text-sm font-medium text-gray-700">Filter by Action</label>
                        <select name="action" id="action" onchange="this.form.submit()"
                                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <option value="">All Actions</option>
                            <option value="initial" {{ request('action') == 'initial' ? 'selected' : '' }}>Initial</option>
                            <option value="add" {{ request('action') == 'add' ? 'selected' : '' }}>Add</option>
                            <option value="remove" {{ request('action') == 'remove' ? 'selected' : '' }}>Remove</option>
                            <option value="set" {{ request('action') == 'set' ? 'selected' : '' }}>Set</option>
                            <option value="sale" {{ request('action') == 'sale' ? 'selected' : '' }}>Sale</option>
                            <option value="purchase" {{ request('action') == 'purchase' ? 'selected' : '' }}>Purchase</option>
                        </select>
                    </div>
                    @if(request('product_id') || request('action'))
                        <div class="self-end">
                            <a href="{{ route('inventory.logs') }}" class="text-gray-500 hover:text-gray-700">
                                Clear Filters
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Qty Change</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $log->product->name ?? 'N/A' }}</div>
                                <div class="text-sm text-gray-500">{{ $log->product->barcode ?? '' }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs rounded-full
                                    @if($log->action === 'add') bg-green-100 text-green-800
                                    @elseif($log->action === 'remove') bg-red-100 text-red-800
                                    @else bg-blue-100 text-blue-800 @endif">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm
                                @if($log->quantity_change > 0) text-green-600
                                @elseif($log->quantity_change < 0) text-red-600
                                @else text-gray-500 @endif">
                                {{ $log->quantity_change > 0 ? '+' : '' }}{{ $log->quantity_change }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $log->user->name ?? 'System' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $log->notes }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No inventory logs found
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $logs->withQueryString()->links() }}
            </div>
        </div>
    </div>
@endsection
