@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-wrap justify-between items-center gap-2 mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Inventory Management</h1>
            <div>
                <a href="{{ route('inventory.low-stock') }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                    View Low Stock
                </a>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b">
                <form method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center">
                    <input type="text" name="search" placeholder="Search inventory..." value="{{ request('search') }}"
                           class="px-4 py-2 border rounded-lg sm:rounded-r-none sm:rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 flex-grow">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-lg sm:rounded-l-none sm:rounded-r-lg hover:bg-blue-600 mt-2 sm:mt-0">
                        Search
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Current Stock</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reorder Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($products as $product)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($product->image)
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <img class="h-10 w-10 rounded-full object-cover" src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->name }}">
                                        </div>
                                    @endif
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $product->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $product->barcode }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $product->category->name ?? 'Uncategorized' }}
                            </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ (int)($product->branch_stock ?? $product->stock_quantity) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ $product->reorder_level }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if(($product->branch_stock ?? $product->stock_quantity) <= $product->reorder_level)
                                    <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Low Stock</span>
                                @elseif(($product->branch_stock ?? $product->stock_quantity) == 0)
                                    <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Out of Stock</span>
                                @else
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">In Stock</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button onclick="openAdjustModal({{ $product->id }}, '{{ $product->name }}')"
                                        class="text-blue-500 hover:text-blue-700 mr-3">Adjust</button>
                                <a href="{{ route('inventory.logs', ['product_id' => $product->id]) }}"
                                   class="text-gray-500 hover:text-gray-700">History</a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <!-- Adjust Inventory Modal -->
    <div id="adjustModal" class="fixed z-10 inset-0 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form id="adjustForm" method="POST" action="{{ route('inventory.adjust') }}">
                    @csrf
                    <input type="hidden" name="product_id" id="modalProductId">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modalTitle">Adjust Inventory</h3>
                        <div class="space-y-4">
                            <div>
                                <label for="action" class="block text-sm font-medium text-gray-700">Action *</label>
                                <select name="action" id="action" required
                                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                    <option value="add">Add Stock</option>
                                    <option value="remove">Remove Stock</option>
                                    <option value="set">Set Exact Quantity</option>
                                </select>
                            </div>
                            <div>
                                <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity *</label>
                                <input type="number" min="1" name="quantity" id="quantity" required
                                       class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Notes *</label>
                                <textarea name="notes" id="notes" rows="3" required
                                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-500 text-base font-medium text-white hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Save Changes
                        </button>
                        <button type="button" onclick="document.getElementById('adjustModal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openAdjustModal(productId, productName) {
            document.getElementById('modalProductId').value = productId;
            document.getElementById('modalTitle').textContent = `Adjust Inventory - ${productName}`;
            document.getElementById('adjustModal').classList.remove('hidden');
        }
    </script>
@endsection
