@extends('layouts.admin')
@section('title', 'Product Statement')

@section('content')
<div class="space-y-6">
    <h2 class="text-2xl font-bold">Product Statement</h2>

    @php
        $selectedProduct = $productId ? $products->firstWhere('id', (int) $productId) : null;
    @endphp
    <form method="GET" class="bg-white p-4 rounded-lg shadow flex flex-col sm:flex-row flex-wrap gap-4 items-start sm:items-end"
          x-data="productPicker({
              products: @js($products->map(fn($p) => ['id'=>$p->id,'name'=>$p->name,'barcode'=>$p->barcode ?? ''])),
              initialId: '{{ $productId }}',
              initialName: @js($selectedProduct?->name ?? ''),
          })">
        <div style="position:relative;min-width:280px;" @click.outside="open=false">
            <label class="block text-sm font-medium text-gray-700">Product</label>
            <input type="hidden" name="product_id" :value="selectedId" required>
            <div style="position:relative;">
                <input type="text" x-model="search"
                       @focus="open=true"
                       @input="open=true; if(!search) selectedId=''"
                       placeholder="Search product by name or barcode..."
                       autocomplete="off"
                       class="mt-1 block w-full border border-gray-300 rounded-md py-2 pl-9 pr-9 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                <i class="fas fa-search text-gray-400"
                   style="position:absolute;left:12px;top:55%;transform:translateY(-50%);pointer-events:none;"></i>
                <button type="button" x-show="search" @click="clear()"
                        class="text-gray-400 hover:text-rose-500"
                        style="position:absolute;right:10px;top:55%;transform:translateY(-50%);background:none;border:none;cursor:pointer;">
                    <i class="fas fa-times-circle"></i>
                </button>
            </div>
            <div x-show="open && filtered().length" x-cloak
                 class="mt-1 border border-gray-200 rounded-md max-h-64 overflow-y-auto bg-white shadow-lg"
                 style="position:absolute;left:0;right:0;z-index:50;">
                <template x-for="p in filtered()" :key="p.id">
                    <button type="button" @click="pick(p)"
                            class="w-full text-left px-3 py-2 hover:bg-blue-50 border-b border-gray-100 last:border-0 text-sm"
                            :class="selectedId == p.id ? 'bg-blue-50' : ''">
                        <div class="font-medium text-gray-800 truncate" x-text="p.name"></div>
                        <div class="text-xs text-gray-500" x-text="p.barcode ? 'Barcode: ' + p.barcode : ''"></div>
                    </button>
                </template>
            </div>
            <p x-show="open && search && !filtered().length" x-cloak
               class="mt-1 border border-gray-200 rounded-md bg-white shadow-lg px-3 py-2 text-xs text-gray-500 italic"
               style="position:absolute;left:0;right:0;z-index:50;">
                No products match.
            </p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">From</label>
            <input type="date" name="start_date" value="{{ $start }}" class="mt-1 block border border-gray-300 rounded-md py-2 px-3 text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">To</label>
            <input type="date" name="end_date" value="{{ $end }}" class="mt-1 block border border-gray-300 rounded-md py-2 px-3 text-sm">
        </div>
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700">
            View Statement
        </button>
    </form>

    @push('scripts')
    <script>
    function productPicker({ products, initialId, initialName }) {
        return {
            products,
            search: initialName || '',
            selectedId: initialId || '',
            open: false,
            filtered() {
                const q = (this.search || '').toLowerCase().trim();
                if (!q) return this.products.slice(0, 50);
                return this.products.filter(p =>
                    (p.name || '').toLowerCase().includes(q)
                    || (p.barcode || '').toLowerCase().includes(q)
                ).slice(0, 50);
            },
            pick(p) {
                this.selectedId = p.id;
                this.search = p.name;
                this.open = false;
            },
            clear() {
                this.search = '';
                this.selectedId = '';
                this.open = true;
            },
        };
    }
    </script>
    @endpush

    @if($statement)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-blue-50 border border-blue-200 p-4 rounded-lg">
                <div class="text-sm text-blue-600 font-medium">Total Qty Sold</div>
                <div class="text-2xl font-bold text-blue-800">{{ $statement['total_qty'] }}</div>
            </div>
            <div class="bg-green-50 border border-green-200 p-4 rounded-lg">
                <div class="text-sm text-green-600 font-medium">Total Revenue</div>
                <div class="text-2xl font-bold text-green-800">Rs. {{ number_format($statement['total_revenue'], 2) }}</div>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                <div class="text-sm text-yellow-600 font-medium">Total Cost</div>
                <div class="text-2xl font-bold text-yellow-800">Rs. {{ number_format($statement['total_cost'], 2) }}</div>
            </div>
            <div class="p-4 rounded-lg {{ $statement['total_profit'] >= 0 ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200' }}">
                <div class="text-sm font-medium {{ $statement['total_profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                    {{ $statement['total_profit'] >= 0 ? 'Total Profit' : 'Total Loss' }}
                </div>
                <div class="text-2xl font-bold {{ $statement['total_profit'] >= 0 ? 'text-emerald-800' : 'text-red-800' }}">
                    Rs. {{ number_format(abs($statement['total_profit']), 2) }}
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-3">Sales by Price Tier</h3>
            <div class="overflow-x-auto">
            <table class="min-w-full text-sm border">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="p-3 border text-left">Unit Price</th>
                        <th class="p-3 border text-left">Qty Sold</th>
                        <th class="p-3 border text-left">Revenue</th>
                        <th class="p-3 border text-left">Profit</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($statement['sales_by_price'] as $tier)
                        @php
                            $tierProfit = $tier['revenue'] - ($tier['quantity'] * ($statement['product']->cost_price ?? 0));
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 border">Rs. {{ number_format($tier['price'], 2) }}</td>
                            <td class="p-3 border">{{ $tier['quantity'] }}</td>
                            <td class="p-3 border">Rs. {{ number_format($tier['revenue'], 2) }}</td>
                            <td class="p-3 border {{ $tierProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                Rs. {{ number_format($tierProfit, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-3">Sale Details</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm border">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="p-3 border text-left">Date</th>
                            <th class="p-3 border text-left">Order #</th>
                            <th class="p-3 border text-left">Customer</th>
                            <th class="p-3 border text-left">Qty</th>
                            <th class="p-3 border text-left">Unit Price</th>
                            <th class="p-3 border text-left">Total</th>
                            <th class="p-3 border text-left">Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statement['items'] as $item)
                            @php
                                $itemProfit = $item->total_price - ($item->quantity * ($statement['product']->cost_price ?? 0));
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 border">{{ $item->order->created_at->format('d M Y') }}</td>
                                <td class="p-3 border">{{ $item->order->order_number }}</td>
                                <td class="p-3 border">{{ $item->order->customer->name ?? 'Walk-in' }}</td>
                                <td class="p-3 border">{{ $item->quantity }}</td>
                                <td class="p-3 border">Rs. {{ number_format($item->unit_price, 2) }}</td>
                                <td class="p-3 border">Rs. {{ number_format($item->total_price, 2) }}</td>
                                <td class="p-3 border {{ $itemProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    Rs. {{ number_format($itemProfit, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif($productId)
        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg text-yellow-700">
            No sales found for this product in the selected period.
        </div>
    @endif
</div>
@endsection
