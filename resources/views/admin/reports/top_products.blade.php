@extends('layouts.admin')

@section('content')
    <div class="px-6 py-6">
        <h1 class="text-2xl font-bold mb-6">Top Products Sold</h1>

        <form method="GET" action="{{ route('admin.reports.top-products') }}" class="mb-6 flex flex-wrap gap-4 items-end">
            <div>
                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date</label>
                <input type="date" name="start_date" id="start_date" value="{{ request('start_date', now()->startOfMonth()->toDateString()) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
            </div>

            <div>
                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                <input type="date" name="end_date" id="end_date" value="{{ request('end_date', now()->endOfMonth()->toDateString()) }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50">
            </div>

            <div class="self-end">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Filter
                </button>
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow">
                <thead class="bg-gray-100 text-left text-gray-600 uppercase text-sm">
                <tr>
                    <th class="py-3 px-4 border-b">Product</th>
                    <th class="py-3 px-4 border-b">Quantity Sold</th>
                    <th class="py-3 px-4 border-b">Total Revenue</th>
                </tr>
                </thead>
                <tbody class="text-gray-700">
                @forelse($topProducts as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="py-3 px-4 border-b">{{ $product->name }}</td>
                        <td class="py-3 px-4 border-b">{{ $product->total_quantity }}</td>
                        <td class="py-3 px-4 border-b">Rs {{ number_format($product->total_revenue, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-4 px-4 text-center text-gray-500">No data available for selected range.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
