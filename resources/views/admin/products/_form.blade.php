<form action="{{ isset($product) ? route('products.update', $product->id) : route('products.store') }}" method="POST"
    enctype="multipart/form-data">
    @csrf
    @if (isset($product))
        @method('PUT')
    @endif

    <div class="space-y-6">
        <!-- Product Name -->
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Product Name *</label>
            <input type="text" name="name" id="name" value="{{ old('name', $product->name ?? '') }}"
                required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm">
        </div>

        <!-- Category -->
        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700">Category *</label>
            <select name="category_id" id="category_id" required
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm">
                <option value="">Select Category</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id', $product->category_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="unit_id" class="block text-sm font-medium text-gray-700">Unit</label>
            <select name="unit_id" id="unit_id"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm">
                <option value="">Select Unit</option>
                @foreach ($units as $unit)
                    <option value="{{ $unit->id }}"
                        {{ old('unit_id', $product->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->name }} ({{ $unit->abbreviation }})
                    </option>
                @endforeach
            </select>
            <p class="mt-1 text-sm text-gray-500">Select the unit of measurement for this product</p>
        </div>

        <!-- Description -->
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" id="description" rows="3"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm">{{ old('description', $product->description ?? '') }}</textarea>
        </div>

        <!-- Rank/Box Placement -->
        <div>
            <label for="rank" class="block text-sm font-medium text-gray-700">Box Placement / Rank</label>
            <input type="text" name="rank" id="rank" value="{{ old('rank', $product->rank ?? '') }}"
                placeholder="e.g., A1, B2, Shelf3-Box5"
                class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm">
            <p class="mt-1 text-sm text-gray-500">Enter the box/shelf location where this product is stored in the shop
            </p>
        </div>

        <!-- Cost Price -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <label for="cost_price" class="block text-sm font-medium text-gray-700 mb-2">Cost Price *</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">Rs</span>
                </div>
                <input type="number" step="0.01" min="0" name="cost_price" id="cost_price"
                    value="{{ old('cost_price', $product->cost_price ?? 0) }}" required
                    class="mt-1 pl-12 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm focus:ring-blue-500 focus:border-blue-500">
            </div>
        </div>

        <!-- Retail Price -->
        <div class="bg-gray-50 p-4 rounded-lg">
            <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-2">Retail Price *</label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">Rs</span>
                </div>
                <input type="number" step="0.01" min="0" name="sale_price" id="sale_price"
                    value="{{ old('sale_price', $product->sale_price ?? 0) }}" required
                    class="mt-1 pl-12 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="text-xs text-gray-500 mt-1">The price customers pay on the website.</div>
        </div>

        <!-- Stock & Reorder -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Initial Stock *</label>
                <input type="number" min="0" step="0.01" name="stock_quantity" id="stock_quantity"
                    value="{{ old('stock_quantity', (int)($product->branch_stock ?? $product->stock_quantity ?? 0)) }}" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm">
            </div>

            <div>
                <label for="reorder_level" class="block text-sm font-medium text-gray-700">Reorder Level *</label>
                <input type="number" min="0" name="reorder_level" id="reorder_level"
                    value="{{ old('reorder_level', $product->reorder_level ?? 0) }}" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm">
            </div>
        </div>

        <!-- Image -->
        <div class="grid grid-cols-1 gap-6">
            <div>
                <label for="image" class="block text-sm font-medium text-gray-700">Product Image</label>
                <input type="file" name="image" id="image"
                    class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                @if (isset($product) && $product->image)
                    <div class="mt-2">
                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                            class="h-20 w-20 object-cover rounded">
                    </div>
                @endif
            </div>
        </div>

        <!-- Active and Track Inventory -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div class="flex items-center">
                <input type="checkbox" name="is_active" id="is_active" value="1"
                    {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">Active Product</label>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="track_inventory" id="track_inventory" value="1"
                    {{ old('track_inventory', $product->track_inventory ?? true) ? 'checked' : '' }}
                    class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                <label for="track_inventory" class="ml-2 block text-sm text-gray-700">Track Inventory</label>
            </div>

            <div class="flex items-center sm:col-span-2 bg-cyan-50/60 border border-cyan-100 rounded-lg p-3">
                <input type="checkbox" name="show_on_website" id="show_on_website" value="1"
                    {{ old('show_on_website', $product->show_on_website ?? true) ? 'checked' : '' }}
                    class="h-4 w-4 text-cyan-600 border-gray-300 rounded focus:ring-cyan-500">
                <label for="show_on_website" class="ml-2 block text-sm text-gray-700">
                    <i class="fas fa-globe text-cyan-600 mr-1"></i> Show on website
                    <span class="text-gray-400">— when on, this product is visible on the online store</span>
                </label>
            </div>
        </div>

        <!-- Submit -->
        <div class="flex justify-end pt-6 border-t border-gray-200">
            <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium shadow-md transition-colors">
                {{ isset($product) ? 'Update Product' : 'Create Product' }}
            </button>
        </div>
    </div>
</form>
