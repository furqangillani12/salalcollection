@extends('layouts.admin')

@push('styles')
<style>
    @media (max-width: 769px) {
        .container { padding-left: 8px; padding-right: 8px; }
    }
</style>
@endpush

@section('content')
    <div class="container mx-auto px-4 py-6">

        {{-- Header --}}
        <div class="flex flex-wrap justify-between items-center mb-4 gap-3">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Product Management</h1>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('products.create') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded-lg flex items-center text-sm">
                    <i class="fas fa-plus mr-1"></i> Add
                </a>
                <a href="{{ route('units.index') }}"
                    class="bg-purple-500 hover:bg-purple-600 text-white px-3 py-2 rounded-lg flex items-center text-sm">
                    <i class="fas fa-cog mr-1"></i> Units
                </a>
                <a href="{{ route('products.export') }}"
                    class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg flex items-center text-sm">
                    <i class="fas fa-download mr-1"></i> Export
                </a>
            </div>
        </div>

        {{-- Search & Filter --}}
        <div class="bg-white rounded-lg shadow overflow-hidden mb-4">
            <div class="p-3 sm:p-4 border-b">
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" id="productSearch" placeholder="Search name, barcode, rank..."
                        class="w-full sm:flex-1 px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm" autofocus>
                    <div class="flex gap-2">
                        <select id="categoryFilter"
                            class="flex-1 sm:flex-none px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                            <option value="">All Categories</option>
                            @php $categoryNames = $products->pluck('category.name')->filter()->unique()->sort(); @endphp
                            @foreach ($categoryNames as $catName)
                                <option value="{{ $catName }}">{{ $catName }}</option>
                            @endforeach
                        </select>
                        <button onclick="clearProductFilters()"
                            class="px-3 py-2 border border-gray-300 text-gray-600 rounded-lg hover:bg-gray-50 text-sm">
                            Clear
                        </button>
                    </div>
                </div>
                <div class="mt-2">
                    <span id="productCount" class="text-xs text-gray-400"></span>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════
             DESKTOP TABLE (hidden on mobile)
         ═══════════════════════════════════════════ --}}
        <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Box/Rank</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Walk-in</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Reseller</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Wholesale</th>
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-3 py-3 text-center text-xs font-medium text-gray-500 uppercase">Website</th>
                            <th class="px-3 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="productTableBody" class="bg-white divide-y divide-gray-200">
                        @forelse($products as $product)
                            <tr data-searchable="{{ $product->name }} {{ $product->barcode }} {{ $product->rank }} {{ $product->category->name ?? '' }} {{ $product->unit->name ?? '' }}"
                                data-category="{{ $product->category->name ?? '' }}"
                                class="hover:bg-gray-50">

                                {{-- Product (image + name + barcode) --}}
                                <td class="px-3 py-3">
                                    <div class="flex items-center gap-2">
                                        @if ($product->image)
                                            <img src="{{ shop_image($product->image) }}" class="h-8 w-8 rounded-full object-cover flex-shrink-0">
                                        @else
                                            <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-image text-gray-400 text-xs"></i>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="font-medium text-gray-900 truncate">{{ $product->name }}</div>
                                            @if ($product->barcode)
                                                <div class="text-xs text-gray-400 font-mono">{{ $product->barcode }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-3 py-3">
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-800">
                                        {{ $product->category->name ?? 'N/A' }}
                                    </span>
                                </td>

                                <td class="px-3 py-3">
                                    @if ($product->unit)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-800">
                                            {{ $product->unit->abbreviation ?: $product->unit->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>

                                <td class="px-3 py-3">
                                    @if ($product->rank)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-purple-100 text-purple-800">{{ $product->rank }}</span>
                                    @else
                                        <span class="text-gray-300 text-xs">—</span>
                                    @endif
                                </td>

                                <td class="px-3 py-3 text-right font-mono text-xs">Rs.{{ number_format($product->sale_price, 0) }}</td>
                                <td class="px-3 py-3 text-right font-mono text-xs">Rs.{{ number_format($product->resale_price, 0) }}</td>
                                <td class="px-3 py-3 text-right font-mono text-xs">Rs.{{ number_format($product->wholesale_price, 0) }}</td>

                                <td class="px-3 py-3 text-center">
                                    <span class="{{ ($product->branch_stock ?? $product->stock_quantity) <= ($product->reorder_level ?? 0) ? 'text-red-600 font-bold' : 'text-gray-900' }}">
                                        {{ (int)($product->branch_stock ?? $product->stock_quantity) }}
                                    </span>
                                    @if (($product->branch_stock ?? $product->stock_quantity) <= ($product->reorder_level ?? 0))
                                        <div class="text-xs text-red-500">Low</div>
                                    @endif
                                </td>

                                <td class="px-3 py-3 text-center">
                                    <span class="px-2 py-0.5 text-xs rounded-full {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $product->is_active ? 'Active' : 'Off' }}
                                    </span>
                                </td>

                                <td class="px-3 py-3 text-center">
                                    <form action="{{ route('products.toggle-website', $product->id) }}" method="POST" class="inline">
                                        @csrf @method('PATCH')
                                        <button type="submit"
                                            title="{{ $product->show_on_website ? 'Visible on website — click to hide' : 'Hidden — click to show on website' }}"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium {{ $product->show_on_website ? 'bg-cyan-100 text-cyan-800 hover:bg-cyan-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">
                                            <i class="fas {{ $product->show_on_website ? 'fa-globe' : 'fa-eye-slash' }}"></i>
                                            {{ $product->show_on_website ? 'On' : 'Off' }}
                                        </button>
                                    </form>
                                </td>

                                <td class="px-3 py-3 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <a href="{{ route('products.edit', $product->id) }}" class="text-blue-500 hover:text-blue-700 p-1" title="Edit"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Delete this product?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 p-1" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-8 text-center text-gray-400">No products found</td>
                            </tr>
                        @endforelse
                        <tr id="noProductResults" style="display:none">
                            <td colspan="11" class="px-4 py-8 text-center text-gray-400">No products match your search.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════
             MOBILE CARDS (hidden on desktop)
         ═══════════════════════════════════════════ --}}
        <div id="mobileProductCards" class="md:hidden space-y-3">
            @forelse($products as $product)
                <div class="product-card bg-white rounded-lg shadow p-3 border border-gray-100"
                     data-searchable="{{ $product->name }} {{ $product->barcode }} {{ $product->rank }} {{ $product->category->name ?? '' }} {{ $product->unit->name ?? '' }}"
                     data-category="{{ $product->category->name ?? '' }}">

                    {{-- Top: Image + Name + Status --}}
                    <div class="flex items-start gap-3">
                        @if ($product->image)
                            <img src="{{ shop_image($product->image) }}" class="h-12 w-12 rounded-lg object-cover flex-shrink-0">
                        @else
                            <div class="h-12 w-12 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-image text-gray-300"></i>
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-gray-900 text-sm truncate">{{ $product->name }}</h3>
                                    @if ($product->barcode)
                                        <p class="text-xs text-gray-400 font-mono">{{ $product->barcode }}</p>
                                    @endif
                                </div>
                                <span class="flex-shrink-0 px-2 py-0.5 text-xs rounded-full {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $product->is_active ? 'Active' : 'Off' }}
                                </span>
                            </div>

                            {{-- Tags --}}
                            <div class="flex flex-wrap gap-1 mt-1.5">
                                <span class="px-1.5 py-0.5 text-xs rounded bg-blue-50 text-blue-700">{{ $product->category->name ?? 'N/A' }}</span>
                                @if ($product->unit)
                                    <span class="px-1.5 py-0.5 text-xs rounded bg-green-50 text-green-700">{{ $product->unit->abbreviation ?: $product->unit->name }}</span>
                                @endif
                                @if ($product->rank)
                                    <span class="px-1.5 py-0.5 text-xs rounded bg-purple-50 text-purple-700">{{ $product->rank }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Prices Grid --}}
                    <div class="grid grid-cols-3 gap-2 mt-3 text-center">
                        <div class="bg-gray-50 rounded p-1.5">
                            <p class="text-xs text-gray-400">Walk-in</p>
                            <p class="text-sm font-bold text-gray-800">Rs.{{ number_format($product->sale_price, 0) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded p-1.5">
                            <p class="text-xs text-gray-400">Reseller</p>
                            <p class="text-sm font-bold text-gray-800">Rs.{{ number_format($product->resale_price, 0) }}</p>
                        </div>
                        <div class="bg-gray-50 rounded p-1.5">
                            <p class="text-xs text-gray-400">Wholesale</p>
                            <p class="text-sm font-bold text-gray-800">Rs.{{ number_format($product->wholesale_price, 0) }}</p>
                        </div>
                    </div>

                    {{-- Stock + Actions --}}
                    <div class="flex items-center justify-between mt-3 pt-2 border-t border-gray-100">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">Stock:</span>
                            <span class="text-sm font-bold {{ ($product->branch_stock ?? $product->stock_quantity) <= ($product->reorder_level ?? 0) ? 'text-red-600' : 'text-gray-900' }}">
                                {{ (int)($product->branch_stock ?? $product->stock_quantity) }}
                                @if ($product->unit)
                                    {{ $product->unit->abbreviation ?: $product->unit->name }}
                                @endif
                            </span>
                            @if (($product->branch_stock ?? $product->stock_quantity) <= ($product->reorder_level ?? 0))
                                <span class="text-xs text-red-500 font-medium">Low!</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2">
                            <form action="{{ route('products.toggle-website', $product->id) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium {{ $product->show_on_website ? 'bg-cyan-100 text-cyan-800' : 'bg-gray-100 text-gray-500' }}">
                                    <i class="fas {{ $product->show_on_website ? 'fa-globe' : 'fa-eye-slash' }}"></i> {{ $product->show_on_website ? 'Web on' : 'Web off' }}
                                </button>
                            </form>
                            <a href="{{ route('products.edit', $product->id) }}"
                                class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded text-xs font-medium">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Delete this product?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 bg-red-500 hover:bg-red-600 text-white rounded text-xs font-medium">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg shadow p-8 text-center text-gray-400">
                    No products found
                </div>
            @endforelse
            <div id="noProductResultsMobile" style="display:none" class="bg-white rounded-lg shadow p-8 text-center text-gray-400">
                No products match your search.
            </div>
        </div>

    </div>

    <script>
        function filterProducts() {
            const search = document.getElementById('productSearch').value.toLowerCase().trim();
            const category = document.getElementById('categoryFilter').value;
            let visibleCount = 0;
            const total = {{ count($products) }};

            // Filter desktop table rows
            document.querySelectorAll('#productTableBody tr[data-searchable]').forEach(row => {
                const text = row.getAttribute('data-searchable').toLowerCase();
                const rowCat = row.getAttribute('data-category');
                const show = (!search || text.includes(search)) && (!category || rowCat === category);
                row.style.display = show ? '' : 'none';
                if (show) visibleCount++;
            });

            // Filter mobile cards
            document.querySelectorAll('#mobileProductCards .product-card').forEach(card => {
                const text = card.getAttribute('data-searchable').toLowerCase();
                const cardCat = card.getAttribute('data-category');
                const show = (!search || text.includes(search)) && (!category || cardCat === category);
                card.style.display = show ? '' : 'none';
            });

            // No results messages
            const noDesktop = document.getElementById('noProductResults');
            const noMobile = document.getElementById('noProductResultsMobile');
            if (noDesktop) noDesktop.style.display = visibleCount === 0 ? '' : 'none';
            if (noMobile) noMobile.style.display = visibleCount === 0 ? '' : 'none';

            const countEl = document.getElementById('productCount');
            if (countEl) countEl.textContent = visibleCount + ' of ' + total + ' products';
        }

        function clearProductFilters() {
            document.getElementById('productSearch').value = '';
            document.getElementById('categoryFilter').value = '';
            filterProducts();
            document.getElementById('productSearch').focus();
        }

        document.getElementById('productSearch').addEventListener('input', filterProducts);
        document.getElementById('categoryFilter').addEventListener('change', filterProducts);
        document.addEventListener('DOMContentLoaded', filterProducts);
    </script>
@endsection
