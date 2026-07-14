@csrf
<div class="p-6 space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Package Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $package->name ?? '') }}" required
                placeholder="e.g. Rizvi Package, Family Deal..."
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Package Code</label>
            <input type="text" name="code" value="{{ old('code', $package->code ?? '') }}"
                placeholder="e.g. PKG-001"
                class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
        </div>
    </div>

    {{-- Product selection --}}
    <div>
        <div class="flex items-center justify-between mb-2">
            <label class="block text-sm font-medium text-gray-700">Package Items <span class="text-red-500">*</span></label>
            <button type="button" id="add-pkg-item"
                class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded">
                + Add Product
            </button>
        </div>
        <div id="pkg-items" class="space-y-2 mb-3">
            {{-- rows injected by JS --}}
        </div>
        <p class="text-xs text-gray-400">Select the products that make up this package and their quantities.</p>
    </div>

    {{-- Cost/retail summary (read-only) --}}
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 grid grid-cols-3 gap-4 text-sm">
        <div>
            <p class="text-xs text-gray-500 mb-1">Cost Price (لاگت)</p>
            <p id="pkg-cost" class="font-bold text-gray-800">Rs. 0</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 mb-1">Retail Total (اگر الگ بیچیں)</p>
            <p id="pkg-retail" class="font-bold text-gray-800">Rs. 0</p>
        </div>
        <div>
            <p class="text-xs text-gray-500 mb-1">Customer Saves</p>
            <p id="pkg-saves" class="font-bold text-green-700">Rs. 0</p>
        </div>
    </div>

    {{-- Sale prices per customer type --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Package Price per Customer Type <span class="text-red-500">*</span></label>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Retail Price (عام گاہک) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">Rs.</span>
                    <input type="number" name="sale_price" id="pkg-sale-price"
                        value="{{ old('sale_price', $package->sale_price ?? '') }}"
                        min="0" step="0.01" required oninput="updatePkgSummary()"
                        class="w-full border border-gray-300 rounded-md pl-10 pr-3 py-2 text-base font-bold focus:ring-blue-500 focus:border-blue-500"
                        placeholder="0">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Reseller Price (ریسیلر)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">Rs.</span>
                    <input type="number" name="resale_price" id="pkg-resale-price"
                        value="{{ old('resale_price', $package->resale_price ?? '') }}"
                        min="0" step="0.01" oninput="updatePkgSummary()"
                        class="w-full border border-gray-300 rounded-md pl-10 pr-3 py-2 text-base font-bold focus:ring-purple-500 focus:border-purple-500"
                        placeholder="Leave blank = same as retail">
                </div>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Wholesale Price (ہول سیل)</label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold text-sm">Rs.</span>
                    <input type="number" name="wholesale_price" id="pkg-wholesale-price"
                        value="{{ old('wholesale_price', $package->wholesale_price ?? '') }}"
                        min="0" step="0.01" oninput="updatePkgSummary()"
                        class="w-full border border-gray-300 rounded-md pl-10 pr-3 py-2 text-base font-bold focus:ring-orange-500 focus:border-orange-500"
                        placeholder="Leave blank = same as retail">
                </div>
            </div>
        </div>
        <p class="text-xs text-gray-400 mt-1">Set different prices per customer type. POS will auto-apply the correct price based on selected customer.</p>
    </div>

    {{-- Receipt preview --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <p class="text-xs text-gray-500 mb-1">Receipt preview (retail customer)</p>
        <p class="text-sm font-semibold text-blue-800" id="pkg-receipt-preview">
            Items total: Rs. 0<br>
            Package Discount: −Rs. 0<br>
            <strong>Customer pays: Rs. 0</strong>
        </p>
    </div>

</div>

<div class="px-6 py-4 bg-gray-50 border-t flex justify-between">
    <a href="{{ route('admin.packages.index') }}"
        class="px-4 py-2 border border-gray-300 text-gray-600 rounded-md hover:bg-gray-50 text-sm">Cancel</a>
    <button type="submit"
        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-semibold">
        {{ $buttonText ?? 'Save Package' }}
    </button>
</div>

@php
    $allProductsJson = $products->map(fn($p) => [
        'id'         => $p->id,
        'name'       => $p->name,
        'cost_price' => (float)($p->cost_price ?? 0),
        'sale_price' => (float)($p->sale_price ?? 0),
    ])->values();

    $existingItemsJson = isset($package)
        ? $package->items->map(fn($i) => [
            'product_id'   => $i->product_id,
            'product_name' => $i->product?->name ?? '',
            'quantity'     => $i->quantity,
          ])->values()
        : collect([]);
@endphp

@push('styles')
<style>
.pkg-search-wrap { position: relative; flex: 1; min-width: 0; }
.pkg-search-input {
    width: 100%; border: 1px solid #d1d5db; border-radius: 0.375rem;
    padding: 0.5rem 0.75rem; font-size: 0.875rem; box-sizing: border-box;
}
.pkg-search-input:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 1px #3b82f6; }
.pkg-dropdown {
    position: absolute; top: 100%; left: 0; right: 0; z-index: 200;
    background: #fff; border: 1.5px solid #3b82f6; border-top: none;
    border-radius: 0 0 6px 6px; max-height: 220px; overflow-y: auto;
    display: none; box-shadow: 0 4px 12px rgba(0,0,0,.12);
}
.pkg-dropdown.show { display: block; }
.pkg-opt { padding: 8px 10px; font-size: 13px; cursor: pointer; border-bottom: 1px solid #f1f5f9; }
.pkg-opt:hover { background: #eff6ff; }
.pkg-opt .po-name { font-weight: 600; color: #1e293b; }
.pkg-opt .po-meta { font-size: 11px; color: #9ca3af; }
</style>
@endpush

@push('scripts')
<script>
const allProducts = @json($allProductsJson);
const existingItems = @json($existingItemsJson);

// Returns a display label for the per-row cost column.
// If sale_price is 0, falls back to cost_price with a warning.
function buildRowCostLabel(sale, cost, qty) {
    const q = parseFloat(qty) || 0;
    if (sale > 0) {
        return 'Rs. ' + (sale * q).toLocaleString('en', {maximumFractionDigits: 0});
    }
    if (cost > 0) {
        return '<span style="color:#f59e0b;" title="No sale price set — showing cost price">⚠ Rs. ' +
            (cost * q).toLocaleString('en', {maximumFractionDigits: 0}) + '</span>';
    }
    return '<span style="color:#ef4444;" title="No price set on this product">⚠ No price</span>';
}

let pkgItemCount = 0;

function addPkgItemRow(data = null) {
    pkgItemCount++;
    const idx = pkgItemCount;

    const selectedName = data ? (data.product_name || '') : '';
    const selectedCost = data ? (allProducts.find(p => p.id == data.product_id)?.cost_price ?? 0) : 0;
    const selectedSale = data ? (allProducts.find(p => p.id == data.product_id)?.sale_price ?? 0) : 0;
    const rowCost = data ? buildRowCostLabel(selectedSale, selectedCost, data.quantity) : '—';

    const html = `
    <div class="pkg-item-row flex items-center gap-3 flex-wrap sm:flex-nowrap">
        <div class="pkg-search-wrap">
            <input type="hidden" name="items[${idx}][product_id]"
                class="pkg-product-id" value="${data ? data.product_id : ''}"
                data-cost="${selectedCost}" data-sale="${selectedSale}" required>
            <input type="text" class="pkg-search-input" autocomplete="off"
                placeholder="Search by name or barcode..."
                value="${selectedName}">
            <div class="pkg-dropdown"></div>
        </div>
        <div class="w-24 flex-shrink-0">
            <input type="number" name="items[${idx}][quantity]"
                value="${data ? data.quantity : 1}"
                min="0.01" step="0.01" required oninput="updatePkgSummary()"
                placeholder="Qty"
                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm text-center focus:ring-blue-500 focus:border-blue-500">
        </div>
        <span class="pkg-row-cost text-xs w-24 text-right flex-shrink-0">${rowCost}</span>
        <button type="button" onclick="this.closest('.pkg-item-row').remove(); updatePkgSummary();"
            class="text-red-400 hover:text-red-600 text-xl leading-none flex-shrink-0">×</button>
    </div>`;

    document.getElementById('pkg-items').insertAdjacentHTML('beforeend', html);
    bindPkgRow(document.getElementById('pkg-items').lastElementChild);
    updatePkgSummary();
}

function bindPkgRow(row) {
    const searchInput = row.querySelector('.pkg-search-input');
    const hiddenInput = row.querySelector('.pkg-product-id');
    const dropdown    = row.querySelector('.pkg-dropdown');

    function showDropdown(term) {
        // Close all other pkg dropdowns
        document.querySelectorAll('.pkg-dropdown.show').forEach(d => d.classList.remove('show'));

        const filtered = allProducts.filter(p =>
            !term || p.name.toLowerCase().includes(term.toLowerCase())
        ).slice(0, 20);

        if (!filtered.length) {
            dropdown.innerHTML = '<div class="pkg-opt" style="color:#9ca3af;cursor:default;">No products found</div>';
        } else {
            dropdown.innerHTML = filtered.map(p => `
                <div class="pkg-opt"
                    data-id="${p.id}" data-name="${p.name}"
                    data-cost="${p.cost_price}" data-sale="${p.sale_price}">
                    <div class="po-name">${p.name}</div>
                    <div class="po-meta">Cost: Rs.${p.cost_price.toLocaleString('en',{maximumFractionDigits:0})} · Sale: Rs.${p.sale_price.toLocaleString('en',{maximumFractionDigits:0})}</div>
                </div>`).join('');

            dropdown.querySelectorAll('.pkg-opt[data-id]').forEach(opt => {
                opt.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    searchInput.value   = this.dataset.name;
                    hiddenInput.value   = this.dataset.id;
                    hiddenInput.dataset.cost = this.dataset.cost;
                    hiddenInput.dataset.sale = this.dataset.sale;
                    dropdown.classList.remove('show');
                    updatePkgSummary();
                });
            });
        }
        dropdown.classList.add('show');
    }

    searchInput.addEventListener('focus', () => showDropdown(searchInput.value));
    searchInput.addEventListener('input', function() {
        hiddenInput.value = '';
        hiddenInput.dataset.cost = 0;
        hiddenInput.dataset.sale = 0;
        showDropdown(this.value);
    });
    searchInput.addEventListener('blur', () => {
        setTimeout(() => dropdown.classList.remove('show'), 150);
    });
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.pkg-search-wrap')) {
        document.querySelectorAll('.pkg-dropdown.show').forEach(d => d.classList.remove('show'));
    }
});

document.getElementById('add-pkg-item').addEventListener('click', () => {
    addPkgItemRow();
    // Focus the new search input
    const rows = document.querySelectorAll('.pkg-item-row');
    rows[rows.length - 1].querySelector('.pkg-search-input')?.focus();
});

// Load existing items on edit
existingItems.forEach(item => addPkgItemRow(item));
if (existingItems.length === 0) addPkgItemRow();

function updatePkgSummary() {
    let totalCost = 0, totalRetail = 0;

    document.querySelectorAll('.pkg-item-row').forEach(row => {
        const hidden  = row.querySelector('.pkg-product-id');
        const qtyInput = row.querySelector('input[type=number]');
        const costEl  = row.querySelector('.pkg-row-cost');
        if (!hidden?.value) return;
        const cost = parseFloat(hidden.dataset.cost) || 0;
        const sale = parseFloat(hidden.dataset.sale) || 0;
        const qty  = parseFloat(qtyInput?.value) || 0;
        totalCost   += cost * qty;
        totalRetail += sale * qty;
        if (costEl) costEl.innerHTML = buildRowCostLabel(sale, cost, qty);
    });

    const salePrice = parseFloat(document.getElementById('pkg-sale-price')?.value) || 0;
    const saves = Math.max(0, totalRetail - salePrice);

    document.getElementById('pkg-cost').textContent    = 'Rs. ' + totalCost.toLocaleString('en',    {maximumFractionDigits: 0});
    document.getElementById('pkg-retail').textContent  = 'Rs. ' + totalRetail.toLocaleString('en',  {maximumFractionDigits: 0});
    document.getElementById('pkg-saves').textContent   = 'Rs. ' + saves.toLocaleString('en',        {maximumFractionDigits: 0});

    const pkgName = document.querySelector('input[name=name]')?.value || 'Package';
    document.getElementById('pkg-receipt-preview').innerHTML =
        `Items total: Rs. ${totalRetail.toLocaleString('en', {maximumFractionDigits: 0})}<br>` +
        `${pkgName} Discount: −Rs. ${saves.toLocaleString('en', {maximumFractionDigits: 0})}<br>` +
        `<strong>Customer pays: Rs. ${salePrice.toLocaleString('en', {maximumFractionDigits: 0})}</strong>`;
}

document.querySelector('input[name=name]')?.addEventListener('input', updatePkgSummary);
</script>
@endpush
