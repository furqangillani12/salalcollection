<form action="{{ isset($product) ? route('products.update', $product->id) : route('products.store') }}" method="POST"
    enctype="multipart/form-data">
    @csrf
    @if (isset($product))
        @method('PUT')
    @endif

    <div class="space-y-6">
        <!-- Product Name and Barcode -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Product Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $product->name ?? '') }}"
                    required class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm">
            </div>

            <div>
                <label for="barcode" class="block text-sm font-medium text-gray-700">Barcode</label>
                <input type="text" name="barcode" id="barcode"
                    value="{{ old('barcode', $product->barcode ?? '') }}"
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm">
            </div>
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

        <!-- Discount Calculator -->
        <div class="bg-white rounded-lg shadow border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-800 flex items-center">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-600" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                            clip-rule="evenodd" />
                    </svg>
                    Discount Calculator
                </h3>
                <p class="text-sm text-gray-600 mt-1">Set discount percentages to auto-calculate selling prices</p>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Wholesale Discount -->
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <label for="wholesale_discount" class="block text-sm font-medium text-gray-700 mb-2">
                            Wholesale Discount
                        </label>
                        <div class="flex items-center space-x-2">
                            <div class="relative flex-1">
                                @php
                                    $wholesaleDiscount = old('wholesale_discount', 25);
                                    if (isset($product) && $product->sale_price > 0 && $product->wholesale_price > 0) {
                                        $wholesaleDiscount = round(
                                            (($product->sale_price - $product->wholesale_price) /
                                                $product->sale_price) *
                                                100,
                                            2,
                                        );
                                    }
                                @endphp
                                <input type="number" step="0.01" min="0" max="100"
                                    id="wholesale_discount" value="{{ $wholesaleDiscount }}"
                                    class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500 discount-input"
                                    placeholder="25">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">%</span>
                                </div>
                            </div>
                            <button type="button" onclick="setDiscount('wholesale', 25)"
                                class="px-3 py-2 text-xs bg-green-100 text-green-700 rounded hover:bg-green-200">
                                25%
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Recommended: 25-40%</p>
                    </div>

                    <!-- Reseller Discount -->
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <label for="resale_discount" class="block text-sm font-medium text-gray-700 mb-2">
                            Reseller Discount
                        </label>
                        <div class="flex items-center space-x-2">
                            <div class="relative flex-1">
                                @php
                                    $resaleDiscount = old('resale_discount', 20);
                                    if (isset($product) && $product->sale_price > 0 && $product->resale_price > 0) {
                                        $resaleDiscount = round(
                                            (($product->sale_price - $product->resale_price) / $product->sale_price) *
                                                100,
                                            2,
                                        );
                                    }
                                @endphp
                                <input type="number" step="0.01" min="0" max="100"
                                    id="resale_discount" value="{{ $resaleDiscount }}"
                                    class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 discount-input"
                                    placeholder="20">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">%</span>
                                </div>
                            </div>
                            <button type="button" onclick="setDiscount('resale', 20)"
                                class="px-3 py-2 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                                20%
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Recommended: 15-25%</p>
                    </div>

                    <!-- Walk-in Discount -->
                    <div class="bg-white p-4 rounded-lg border border-gray-200 shadow-sm">
                        <label for="sale_discount" class="block text-sm font-medium text-gray-700 mb-2">
                            Walk-in Discount
                        </label>
                        <div class="flex items-center space-x-2">
                            <div class="relative flex-1">
                                <input type="number" step="0.01" min="0" max="100"
                                    id="sale_discount" value="{{ old('sale_discount', 10) }}"
                                    class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-purple-500 focus:border-purple-500 discount-input"
                                    placeholder="10">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500">%</span>
                                </div>
                            </div>
                            <button type="button" onclick="setDiscount('sale', 10)"
                                class="px-3 py-2 text-xs bg-purple-100 text-purple-700 rounded hover:bg-purple-200">
                                10%
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Recommended: 5-15%</p>
                    </div>
                </div>

                <!-- Calculate Button -->
                <div class="mb-6">
                    <button type="button" id="calculatePricesBtn"
                        class="w-full py-3 px-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg shadow-md transition-all duration-200 transform hover:-translate-y-0.5 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z"
                                clip-rule="evenodd" />
                        </svg>
                        Calculate Selling Prices
                    </button>
                </div>

                <!-- Price Preview -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
                    <!-- Base Price Card -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-300">
                        <div class="text-sm font-medium text-gray-600 mb-1">Base Price</div>
                        <div id="basePriceDisplay" class="text-2xl font-bold text-gray-800">Rs 0.00</div>
                        <div class="text-xs text-gray-500 mt-1">Suggested retail price</div>
                    </div>

                    <!-- Wholesale Price Card -->
                    <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                        <div class="flex justify-between items-center mb-1">
                            <div class="text-sm font-medium text-green-700">Wholesale Price</div>
                            <div id="wholesalePercent"
                                class="text-xs font-semibold bg-green-100 text-green-800 px-2 py-0.5 rounded-full">25%
                                off</div>
                        </div>
                        <div id="wholesalePriceDisplay" class="text-2xl font-bold text-green-800">Rs 0.00</div>
                        <div id="wholesaleMargin" class="text-xs text-green-600 mt-1">Margin: 0%</div>
                    </div>

                    <!-- Reseller Price Card -->
                    <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                        <div class="flex justify-between items-center mb-1">
                            <div class="text-sm font-medium text-blue-700">Reseller Price</div>
                            <div id="resalePercent"
                                class="text-xs font-semibold bg-blue-100 text-blue-800 px-2 py-0.5 rounded-full">20%
                                off</div>
                        </div>
                        <div id="resalePriceDisplay" class="text-2xl font-bold text-blue-800">Rs 0.00</div>
                        <div id="resaleMargin" class="text-xs text-blue-600 mt-1">Margin: 0%</div>
                    </div>

                    <!-- Walk-in Price Card -->
                    <div class="bg-purple-50 p-4 rounded-lg border border-purple-200">
                        <div class="flex justify-between items-center mb-1">
                            <div class="text-sm font-medium text-purple-700">Walk-in Price</div>
                            <div id="salePercent"
                                class="text-xs font-semibold bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full">
                                10% off</div>
                        </div>
                        <div id="salePriceDisplay" class="text-2xl font-bold text-purple-800">Rs 0.00</div>
                        <div id="saleMargin" class="text-xs text-purple-600 mt-1">Margin: 0%</div>
                    </div>
                </div>

                <!-- Apply Prices Button -->
                <div>
                    <button type="button" id="applyPricesBtn"
                        class="w-full py-3 px-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white font-medium rounded-lg shadow-md transition-all duration-200">
                        Apply Calculated Prices to Form
                    </button>
                </div>
            </div>
        </div>

        <!-- Actual Price Input Fields -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div>
                <label for="wholesale_price" class="block text-sm font-medium text-gray-700">Wholesale Price *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">Rs</span>
                    </div>
                    <input type="number" step="0.01" min="0" name="wholesale_price" id="wholesale_price"
                        value="{{ old('wholesale_price', $product->wholesale_price ?? 0) }}" required
                        class="mt-1 pl-10 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm price-input focus:ring-green-500 focus:border-green-500">
                </div>
                <div class="text-xs text-gray-500 mt-1">Price for bulk/wholesale customers</div>
            </div>

            <div>
                <label for="resale_price" class="block text-sm font-medium text-gray-700">Reseller Price *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">Rs</span>
                    </div>
                    <input type="number" step="0.01" min="0" name="resale_price" id="resale_price"
                        value="{{ old('resale_price', $product->resale_price ?? 0) }}" required
                        class="mt-1 pl-10 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm price-input focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="text-xs text-gray-500 mt-1">Price for resellers/shopkeepers</div>
            </div>

            <div>
                <label for="sale_price" class="block text-sm font-medium text-gray-700">Walk-in Price *</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500 sm:text-sm">Rs</span>
                    </div>
                    <input type="number" step="0.01" min="0" name="sale_price" id="sale_price"
                        value="{{ old('sale_price', $product->sale_price ?? 0) }}" required
                        class="mt-1 pl-10 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm price-input focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div class="text-xs text-gray-500 mt-1">Price for regular customers</div>
            </div>
        </div>

        <!-- Stock, Weight & Reorder -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div>
                <label for="stock_quantity" class="block text-sm font-medium text-gray-700">Initial Stock *</label>
                <input type="number" min="0" step="0.01" name="stock_quantity" id="stock_quantity"
                    value="{{ old('stock_quantity', (int)($product->branch_stock ?? $product->stock_quantity ?? 0)) }}" required
                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm">
            </div>

            <!-- Weight Input Section -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="weight_kg" class="block text-sm font-medium text-gray-700">Weight (kg)</label>
                    <input type="number" step="0.001" min="0" name="weight_kg" id="weight_kg"
                        value="{{ old('weight_kg', isset($product) ? ($product->weight ? number_format($product->weight, 3, '.', '') : '') : '') }}"
                        placeholder="e.g., 1.5"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm">
                </div>
                <div>
                    <label for="weight_g" class="block text-sm font-medium text-gray-700">Weight (grams)</label>
                    <input type="number" min="0" name="weight_g" id="weight_g"
                        value="{{ old('weight_g', isset($product) ? ($product->weight ? number_format($product->weight * 1000, 0, '.', '') : '') : '') }}"
                        placeholder="e.g., 1500"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm">
                    <p class="mt-1 text-sm text-gray-500">Auto-converts to kg</p>
                </div>
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
<!-- Discount Calculator JavaScript -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get DOM elements
        const costPriceInput = document.getElementById('cost_price');
        const wholesaleDiscountInput = document.getElementById('wholesale_discount');
        const resaleDiscountInput = document.getElementById('resale_discount');
        const saleDiscountInput = document.getElementById('sale_discount');
        const calculateBtn = document.getElementById('calculatePricesBtn');
        const applyBtn = document.getElementById('applyPricesBtn');

        // Price display elements
        const basePriceDisplay = document.getElementById('basePriceDisplay');
        const wholesalePriceDisplay = document.getElementById('wholesalePriceDisplay');
        const resalePriceDisplay = document.getElementById('resalePriceDisplay');
        const salePriceDisplay = document.getElementById('salePriceDisplay');

        // Percent display elements
        const wholesalePercent = document.getElementById('wholesalePercent');
        const resalePercent = document.getElementById('resalePercent');
        const salePercent = document.getElementById('salePercent');

        // Margin display elements
        const wholesaleMargin = document.getElementById('wholesaleMargin');
        const resaleMargin = document.getElementById('resaleMargin');
        const saleMargin = document.getElementById('saleMargin');

        // Actual price inputs
        const wholesalePriceInput = document.getElementById('wholesale_price');
        const resalePriceInput = document.getElementById('resale_price');
        const salePriceInput = document.getElementById('sale_price');

        // Notification function
        function showNotification(message, type = 'success') {
            // Remove existing notification
            const existing = document.querySelector('.price-notification');
            if (existing) existing.remove();

            // Create notification
            const notification = document.createElement('div');
            notification.className =
                `price-notification fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg ${type === 'success' ? 'bg-green-500' : 'bg-red-500'} text-white`;
            notification.textContent = message;
            notification.style.animation = 'slideIn 0.3s ease-out';

            document.body.appendChild(notification);

            // Remove after 3 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Format currency
        function formatCurrency(amount) {
            return 'Rs ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
        }

        // Format percentage
        function formatPercent(value) {
            return value.toFixed(1) + '%';
        }

        // Calculate base price (40% markup)
        function calculateBasePrice(cost) {
            return cost * 1.4; // 40% markup
        }

        // Calculate discounted price
        function calculateDiscountedPrice(basePrice, discount) {
            return basePrice - (basePrice * (discount / 100));
        }

        // Calculate profit margin
        function calculateMargin(sellingPrice, costPrice) {
            if (costPrice === 0) return 0;
            return ((sellingPrice - costPrice) / costPrice) * 100;
        }

        // Update margin color
        function updateMarginColor(element, margin) {
            element.classList.remove('text-red-600', 'text-yellow-600', 'text-green-600');
            if (margin < 10) {
                element.classList.add('text-red-600');
            } else if (margin < 20) {
                element.classList.add('text-yellow-600');
            } else {
                element.classList.add('text-green-600');
            }
        }

        // Calculate and display prices
        function calculateAndDisplay() {
            const costPrice = parseFloat(costPriceInput.value) || 0;
            const wholesaleDiscount = parseFloat(wholesaleDiscountInput.value) || 0;
            const resaleDiscount = parseFloat(resaleDiscountInput.value) || 0;
            const saleDiscount = parseFloat(saleDiscountInput.value) || 0;

            // Calculate base price
            const basePrice = calculateBasePrice(costPrice);

            // Calculate discounted prices
            const wholesalePrice = calculateDiscountedPrice(basePrice, wholesaleDiscount);
            const resalePrice = calculateDiscountedPrice(basePrice, resaleDiscount);
            const salePrice = calculateDiscountedPrice(basePrice, saleDiscount);

            // Calculate margins
            const wholesaleMarginValue = calculateMargin(wholesalePrice, costPrice);
            const resaleMarginValue = calculateMargin(resalePrice, costPrice);
            const saleMarginValue = calculateMargin(salePrice, costPrice);

            // Update displays
            basePriceDisplay.textContent = formatCurrency(basePrice);
            wholesalePriceDisplay.textContent = formatCurrency(wholesalePrice);
            resalePriceDisplay.textContent = formatCurrency(resalePrice);
            salePriceDisplay.textContent = formatCurrency(salePrice);

            // Update percent displays
            wholesalePercent.textContent = wholesaleDiscount + '% off';
            resalePercent.textContent = resaleDiscount + '% off';
            salePercent.textContent = saleDiscount + '% off';

            // Update margin displays
            wholesaleMargin.textContent = 'Margin: ' + formatPercent(wholesaleMarginValue);
            resaleMargin.textContent = 'Margin: ' + formatPercent(resaleMarginValue);
            saleMargin.textContent = 'Margin: ' + formatPercent(saleMarginValue);

            // Update margin colors
            updateMarginColor(wholesaleMargin, wholesaleMarginValue);
            updateMarginColor(resaleMargin, resaleMarginValue);
            updateMarginColor(saleMargin, saleMarginValue);

            return {
                wholesalePrice,
                resalePrice,
                salePrice
            };
        }

        // Apply calculated prices to form inputs
        function applyPrices() {
            const calculated = calculateAndDisplay();

            wholesalePriceInput.value = calculated.wholesalePrice.toFixed(2);
            resalePriceInput.value = calculated.resalePrice.toFixed(2);
            salePriceInput.value = calculated.salePrice.toFixed(2);

            showNotification('Prices applied successfully!');

            // Highlight the applied inputs
            const inputs = [wholesalePriceInput, resalePriceInput, salePriceInput];
            inputs.forEach(input => {
                input.classList.add('ring-2', 'ring-green-300');
                setTimeout(() => {
                    input.classList.remove('ring-2', 'ring-green-300');
                }, 2000);
            });
        }

        // Set discount with quick button
        window.setDiscount = function(type, value) {
            const input = document.getElementById(type + '_discount');
            if (input) {
                input.value = value;
                calculateAndDisplay();
            }
        };

        // Event Listeners
        if (calculateBtn) {
            calculateBtn.addEventListener('click', calculateAndDisplay);
        }

        if (applyBtn) {
            applyBtn.addEventListener('click', applyPrices);
        }

        // Auto-calculate when discount inputs change
        const discountInputs = document.querySelectorAll('.discount-input');
        discountInputs.forEach(input => {
            input.addEventListener('input', calculateAndDisplay);
        });

        // Auto-calculate when cost price changes
        if (costPriceInput) {
            costPriceInput.addEventListener('input', calculateAndDisplay);
        }

        // Update margins when price inputs are manually changed
        const priceInputs = document.querySelectorAll('.price-input');
        priceInputs.forEach(input => {
            input.addEventListener('input', function() {
                const costPrice = parseFloat(costPriceInput.value) || 0;
                const wholesalePrice = parseFloat(wholesalePriceInput.value) || 0;
                const resalePrice = parseFloat(resalePriceInput.value) || 0;
                const salePrice = parseFloat(salePriceInput.value) || 0;

                // Calculate margins
                const wholesaleMarginValue = calculateMargin(wholesalePrice, costPrice);
                const resaleMarginValue = calculateMargin(resalePrice, costPrice);
                const saleMarginValue = calculateMargin(salePrice, costPrice);

                // Update margin displays
                wholesaleMargin.textContent = 'Margin: ' + formatPercent(wholesaleMarginValue);
                resaleMargin.textContent = 'Margin: ' + formatPercent(resaleMarginValue);
                saleMargin.textContent = 'Margin: ' + formatPercent(saleMarginValue);

                // Update margin colors
                updateMarginColor(wholesaleMargin, wholesaleMarginValue);
                updateMarginColor(resaleMargin, resaleMarginValue);
                updateMarginColor(saleMargin, saleMarginValue);
            });
        });

        // Weight conversion
        const kgInput = document.getElementById('weight_kg');
        const gInput = document.getElementById('weight_g');

        if (kgInput && gInput) {
            kgInput.addEventListener('input', function() {
                if (this.value) {
                    gInput.value = Math.round(this.value * 1000);
                } else {
                    gInput.value = '';
                }
            });

            gInput.addEventListener('input', function() {
                if (this.value) {
                    kgInput.value = (this.value / 1000).toFixed(3);
                } else {
                    kgInput.value = '';
                }
            });
        }

        // Initialize calculations on page load
        calculateAndDisplay();
    });

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .discount-input:focus, .price-input:focus {
        border-color: #3b82f6;
        ring-width: 2px;
        ring-color: rgba(59, 130, 246, 0.5);
    }
`;
    document.head.appendChild(style);
</script>
