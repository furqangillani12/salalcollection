@extends('layouts.admin')
@section('title', 'Edit Order #' . $order->order_number)

@push('styles')
    <style>
        .edit-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }

        .edit-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .08);
            padding: 20px;
            margin-bottom: 16px;
        }

        .edit-card h3 {
            font-size: 16px;
            font-weight: 700;
            margin-bottom: 12px;
            color: #1e293b;
        }

        .item-row {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
            flex-wrap: wrap;
        }

        .item-row:last-child {
            border-bottom: none;
        }

        .item-input {
            padding: 6px 10px;
            border: 1.5px solid #e5e7eb;
            border-radius: 6px;
            font-size: 13px;
            box-sizing: border-box;
        }

        .item-input:focus {
            border-color: #3b82f6;
            outline: none;
        }

        .btn-add {
            background: #3b82f6;
            color: #fff;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }

        .btn-add:hover {
            background: #2563eb;
        }

        .btn-remove {
            background: #ef4444;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 4px 10px;
            cursor: pointer;
            font-size: 12px;
        }

        /* Searchable product select */
        .product-search-wrap {
            flex: 1;
            min-width: 150px;
            position: relative;
        }

        .product-search-input {
            width: 100%;
            padding: 6px 10px;
            border: 1.5px solid #e5e7eb;
            border-radius: 6px;
            font-size: 13px;
            box-sizing: border-box;
        }

        .product-search-input:focus {
            border-color: #3b82f6;
            outline: none;
        }

        .product-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: #fff;
            border: 1.5px solid #3b82f6;
            border-top: none;
            border-radius: 0 0 6px 6px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 100;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,.12);
        }

        .product-dropdown.show { display: block; }

        .product-option {
            padding: 8px 10px;
            font-size: 13px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
        }

        .product-option:hover, .product-option.active {
            background: #eff6ff;
        }

        .product-option .po-name { font-weight: 600; color: #1e293b; }
        .product-option .po-meta { font-size: 11px; color: #9ca3af; }

        @media (prefers-color-scheme: dark) {
            .product-search-input { background: #374151; color: #f3f4f6; border-color: #4b5563; }
            .product-dropdown { background: #1f2937; border-color: #3b82f6; }
            .product-option { border-color: #374151; }
            .product-option:hover, .product-option.active { background: #374151; }
            .product-option .po-name { color: #f3f4f6; }
            .product-option .po-meta { color: #6b7280; }
        }

        .btn-save {
            background: #16a34a;
            color: #fff;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            width: 100%;
        }

        .btn-save:hover {
            background: #15803d;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            font-size: 14px;
        }

        .summary-row.total {
            font-weight: 800;
            font-size: 16px;
            border-top: 2px solid #e5e7eb;
            padding-top: 8px;
            margin-top: 4px;
        }

        .order-details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        @media (prefers-color-scheme: dark) {
            .edit-card {
                background: #1f2937;
                box-shadow: 0 1px 6px rgba(0, 0, 0, .3);
            }

            .edit-card h3 {
                color: #f3f4f6;
            }

            .item-row {
                border-color: #374151;
            }

            .item-input {
                background: #374151;
                color: #f3f4f6;
                border-color: #4b5563;
            }

            .summary-row {
                color: #e5e7eb;
            }

            .summary-row.total {
                border-color: #4b5563;
            }
        }

        @media (max-width: 768px) {
            .edit-container {
                padding: 8px;
            }

            .edit-card {
                padding: 12px;
            }

            .order-details-grid {
                grid-template-columns: 1fr;
            }

            .item-row {
                gap: 6px;
            }

            .item-row .edit-product-select {
                width: 100% !important;
                min-width: 0 !important;
                flex: none !important;
            }

            .item-row .edit-qty {
                width: 70px !important;
            }

            .item-row .edit-price {
                width: 80px !important;
            }

            .item-row .edit-item-total {
                min-width: 60px !important;
                font-size: 12px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="edit-container">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
            <h2 style="font-size:20px;font-weight:800;">Edit Order #{{ $order->order_number }}</h2>
            <a href="{{ route('admin.pos.receipt', $order) }}" style="color:#3b82f6;font-size:13px;">View Receipt</a>
        </div>

        <div class="edit-card">
            <h3>Customer</h3>
            <div class="customer-search-wrap" style="position:relative;">
                <input type="hidden" id="editCustomer" value="{{ $order->customer_id ?? '' }}">
                <input type="text" id="customer_search" class="item-input" style="width:100%;"
                    placeholder="Search customer by name, phone, barcode..."
                    value="{{ $order->customer ? $order->customer->name . ' (' . $order->customer->customer_type . ')' : '' }}" autocomplete="off">
                <div id="customer_dropdown" style="position:absolute;top:100%;left:0;right:0;background:#fff;border:1.5px solid #3b82f6;border-top:none;border-radius:0 0 6px 6px;max-height:220px;overflow-y:auto;z-index:100;display:none;box-shadow:0 4px 12px rgba(0,0,0,.12);"></div>
            </div>
        </div>

        <div class="edit-card">
            <h3>Items</h3>
            <div id="editItems">
                @foreach ($order->items as $item)
                    <div class="item-row" data-product-id="{{ $item->product_id }}">
                        <div class="product-search-wrap">
                            <input type="hidden" class="edit-product-id" value="{{ $item->product_id }}">
                            <input type="text" class="product-search-input" value="{{ $item->product?->name ?? 'Product' }}" onfocus="openProductDropdown(this)" oninput="filterProductDropdown(this)">
                            <div class="product-dropdown"></div>
                        </div>
                        <input type="number" class="item-input edit-qty" value="{{ $item->quantity }}" step="0.01"
                            min="0.01" style="width:80px;" placeholder="Qty" oninput="recalcEdit()">
                        <input type="number" class="item-input edit-price" value="{{ $item->unit_price }}" step="0.01"
                            min="0" style="width:100px;" placeholder="Price" oninput="recalcEdit()">
                        <span class="edit-item-total" style="font-weight:700;min-width:80px;text-align:right;">Rs.
                            {{ number_format($item->total_price, 2) }}</span>
                        <button class="btn-remove" onclick="this.closest('.item-row').remove();recalcEdit();">X</button>
                    </div>
                @endforeach
            </div>
            <button class="btn-add" style="margin-top:10px;" onclick="addEditItem()">+ Add Item</button>
        </div>

        <div class="edit-card">
            <h3>Order Details</h3>
            <div class="order-details-grid">
                <div>
                    <label style="font-size:12px;color:#6b7280;">Tax
                        <select id="editTaxType" class="item-input" style="width:60px;display:inline;padding:2px;" onchange="recalcEdit()">
                            <option value="percent" {{ ($order->tax_type ?? 'percent') === 'percent' ? 'selected' : '' }}>%</option>
                            <option value="fixed" {{ ($order->tax_type ?? 'percent') === 'fixed' ? 'selected' : '' }}>Rs.</option>
                        </select>
                    </label>
                    <input type="number" id="editTaxRate" class="item-input" value="{{ $order->tax_rate }}" step="0.01"
                        min="0" style="width:100%;" oninput="recalcEdit()">
                </div>
                <div>
                    <label style="font-size:12px;color:#6b7280;">Discount (Rs.)</label>
                    <input type="number" id="editDiscount" class="item-input" value="{{ $order->discount }}"
                        step="0.01" min="0" style="width:100%;" oninput="recalcEdit()">
                </div>
                <div>
                    <label style="font-size:12px;color:#6b7280;">Delivery Charges</label>
                    <input type="number" id="editDelivery" class="item-input" value="{{ $order->delivery_charges }}"
                        step="0.01" min="0" style="width:100%;" oninput="recalcEdit()">
                </div>
                <div>
                    <label style="font-size:12px;color:#6b7280;">Amount Paid</label>
                    <input type="number" id="editPaid" class="item-input" value="{{ $order->paid_amount }}"
                        step="0.01" min="0" style="width:100%;">
                </div>
                <div>
                    <label style="font-size:12px;color:#6b7280;">Order Date (تاریخ)</label>
                    <input type="date" id="editOrderDate" class="item-input" value="{{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d') }}"
                        style="width:100%;">
                </div>
                <div>
                    <label style="font-size:12px;color:#6b7280;">Payment Method</label>
                    <select id="editPaymentMethod" class="item-input" style="width:100%;">
                        @foreach ($paymentMethods as $pm)
                            <option value="{{ $pm->name }}" {{ $order->payment_method == $pm->name ? 'selected' : '' }}>
                                {{ $pm->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:12px;color:#6b7280;">Dispatch Method</label>
                    <select id="editDispatch" class="item-input" style="width:100%;" onchange="toggleEditTracking()">
                        @foreach ($dispatchMethods as $dm)
                            <option value="{{ $dm->name }}" data-has-tracking="{{ $dm->has_tracking ? '1' : '0' }}" {{ $order->dispatch_method == $dm->name ? 'selected' : '' }}>
                                {{ $dm->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="editTrackingWrap"
                    style="{{ $dispatchMethods->where('name', $order->dispatch_method)->first()?->has_tracking ? '' : 'display:none;' }}">
                    <label style="font-size:12px;color:#6b7280;">Tracking ID</label>
                    <input type="text" id="editTrackingId" class="item-input" value="{{ $order->tracking_id }}"
                        style="width:100%;" placeholder="Tracking ID">
                </div>
            </div>
            <div style="margin-top:10px;">
                <label style="font-size:12px;color:#6b7280;">Notes</label>
                <textarea id="editNotes" class="item-input" style="width:100%;min-height:60px;" placeholder="Order notes...">{{ $order->notes }}</textarea>
            </div>
        </div>

        <div class="edit-card">
            <h3>Summary</h3>
            <div id="editSummary">
                <div class="summary-row"><span>Subtotal</span><span id="sumSubtotal">Rs. 0.00</span></div>
                <div class="summary-row"><span>Tax</span><span id="sumTax">Rs. 0.00</span></div>
                <div class="summary-row"><span>Discount</span><span id="sumDiscount">Rs. 0.00</span></div>
                <div class="summary-row"><span>Delivery</span><span id="sumDelivery">Rs. 0.00</span></div>
                <div class="summary-row total"><span>Total</span><span id="sumTotal">Rs. 0.00</span></div>
            </div>
        </div>

        <button class="btn-save" onclick="saveEditOrder()">Save Changes</button>
    </div>
@endsection

@push('scripts')
    <script>
        const productsData = @json($products);
        const customersData = @json($customers);
        const fmt = n => parseFloat(n || 0).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

        // ── Customer search ──
        (function() {
            const input = document.getElementById('customer_search');
            const hidden = document.getElementById('editCustomer');
            const dropdown = document.getElementById('customer_dropdown');

            function filterCustomers() {
                const search = input.value.toLowerCase().trim();
                const filtered = customersData.filter(c => {
                    return (c.name || '').toLowerCase().includes(search)
                        || (c.phone || '').toLowerCase().includes(search)
                        || (c.barcode || '').toLowerCase().includes(search);
                }).slice(0, 15);

                let html = '<div class="copt" data-id="" data-name="Walk-in Customer" data-type="walkin" style="padding:8px 10px;font-size:13px;cursor:pointer;border-bottom:1px solid #f1f5f9;color:#6b7280;">Walk-in Customer</div>';
                filtered.forEach(c => {
                    html += `<div class="copt" data-id="${c.id}" data-name="${c.name} (${c.customer_type})" data-type="${c.customer_type}" style="padding:8px 10px;font-size:13px;cursor:pointer;border-bottom:1px solid #f1f5f9;">
                        <div style="font-weight:600;color:#1e293b;">${c.name}</div>
                        <div style="font-size:11px;color:#9ca3af;">${c.phone || ''} · ${c.customer_type} ${c.barcode ? '· ' + c.barcode : ''}</div>
                    </div>`;
                });

                dropdown.innerHTML = html;
                dropdown.style.display = 'block';

                dropdown.querySelectorAll('.copt').forEach(opt => {
                    opt.onmousedown = function(e) {
                        e.preventDefault();
                        input.value = this.dataset.name;
                        hidden.value = this.dataset.id;
                        dropdown.style.display = 'none';
                        repriceAllItems();
                    };
                });
            }

            input.addEventListener('focus', filterCustomers);
            input.addEventListener('input', function() {
                hidden.value = '';
                filterCustomers();
            });
            input.addEventListener('blur', function() {
                setTimeout(() => dropdown.style.display = 'none', 200);
            });
        })();

        function getPriceForCustomerType(product, customerType) {
            if (customerType === 'reseller' && product.resale_price) return product.resale_price;
            if (customerType === 'wholesale' && product.wholesale_price) return product.wholesale_price;
            return product.sale_price;
        }

        function getCurrentCustomerType() {
            const id = document.getElementById('editCustomer').value;
            if (!id) return 'walkin';
            const c = customersData.find(c => c.id == id);
            return c?.customer_type || 'walkin';
        }

        function repriceAllItems() {
            const customerType = getCurrentCustomerType();
            document.querySelectorAll('.item-row').forEach(row => {
                const productId = row.querySelector('.edit-product-id')?.value;
                if (!productId) return;
                const product = productsData.find(p => p.id == productId);
                if (!product) return;
                row.querySelector('.edit-price').value = getPriceForCustomerType(product, customerType);
            });
            recalcEdit();
        }

        function addEditItem() {
            const container = document.getElementById('editItems');
            const row = document.createElement('div');
            row.className = 'item-row';
            row.innerHTML = `
            <div class="product-search-wrap">
                <input type="hidden" class="edit-product-id" value="">
                <input type="text" class="product-search-input" placeholder="Search product..." onfocus="openProductDropdown(this)" oninput="filterProductDropdown(this)">
                <div class="product-dropdown"></div>
            </div>
            <input type="number" class="item-input edit-qty" value="1" step="0.01" min="0.01" style="width:80px;" placeholder="Qty" oninput="recalcEdit()">
            <input type="number" class="item-input edit-price" value="0" step="0.01" min="0" style="width:100px;" placeholder="Price" oninput="recalcEdit()">
            <span class="edit-item-total" style="font-weight:700;min-width:80px;text-align:right;">Rs. 0.00</span>
            <button class="btn-remove" onclick="this.closest('.item-row').remove();recalcEdit();">X</button>
            `;
            container.appendChild(row);
            // Focus the search input
            row.querySelector('.product-search-input').focus();
            recalcEdit();
        }

        function openProductDropdown(input) {
            // Close all other dropdowns first
            document.querySelectorAll('.product-dropdown.show').forEach(d => d.classList.remove('show'));
            filterProductDropdown(input);
        }

        function filterProductDropdown(input) {
            const wrap = input.closest('.product-search-wrap');
            const dropdown = wrap.querySelector('.product-dropdown');
            const search = input.value.toLowerCase().trim();

            const filtered = productsData.filter(p => {
                const name = (p.name || '').toLowerCase();
                const barcode = (p.barcode || '').toLowerCase();
                const category = (p.category?.name || '').toLowerCase();
                return !search || name.includes(search) || barcode.includes(search) || category.includes(search);
            }).slice(0, 15);

            if (filtered.length === 0) {
                dropdown.innerHTML = '<div class="product-option" style="color:#9ca3af;cursor:default;">No products found</div>';
            } else {
                dropdown.innerHTML = filtered.map(p => `
                    <div class="product-option" data-id="${p.id}" data-name="${p.name}" data-price="${p.sale_price}" onclick="selectProduct(this)">
                        <div class="po-name">${p.name}</div>
                        <div class="po-meta">${p.barcode || ''} · ${p.category?.name || ''} · Rs.${parseFloat(p.sale_price||0).toLocaleString()}</div>
                    </div>
                `).join('');
            }
            dropdown.classList.add('show');
        }

        function selectProduct(option) {
            const wrap = option.closest('.product-search-wrap');
            const input = wrap.querySelector('.product-search-input');
            const hiddenInput = wrap.querySelector('.edit-product-id');
            const row = wrap.closest('.item-row');
            const priceInput = row.querySelector('.edit-price');
            const dropdown = wrap.querySelector('.product-dropdown');

            input.value = option.dataset.name;
            hiddenInput.value = option.dataset.id;

            // Use customer-type-aware price
            const customerType = getCurrentCustomerType();
            const product = productsData.find(p => p.id == option.dataset.id);
            priceInput.value = product ? getPriceForCustomerType(product, customerType) : option.dataset.price;

            dropdown.classList.remove('show');
            recalcEdit();
        }

        // Close dropdowns on click outside
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.product-search-wrap')) {
                document.querySelectorAll('.product-dropdown.show').forEach(d => d.classList.remove('show'));
            }
        });

        function recalcEdit() {
            let subtotal = 0;
            document.querySelectorAll('.item-row').forEach(row => {
                const qty = parseFloat(row.querySelector('.edit-qty')?.value || 0);
                const price = parseFloat(row.querySelector('.edit-price')?.value || 0);
                const total = qty * price;
                subtotal += total;
                const totalEl = row.querySelector('.edit-item-total');
                if (totalEl) totalEl.textContent = 'Rs. ' + fmt(total);
            });

            const taxRate = parseFloat(document.getElementById('editTaxRate')?.value || 0);
            const discount = parseFloat(document.getElementById('editDiscount')?.value || 0);
            const delivery = parseFloat(document.getElementById('editDelivery')?.value || 0);
            const afterDiscount = subtotal - discount;
            const taxType = document.getElementById('editTaxType')?.value || 'percent';
            const tax = taxType === 'percent' ? (afterDiscount + delivery) * (taxRate / 100) : taxRate;
            const total = afterDiscount + tax + delivery;

            document.getElementById('sumSubtotal').textContent = 'Rs. ' + fmt(subtotal);
            document.getElementById('sumTax').textContent = 'Rs. ' + fmt(tax);
            document.getElementById('sumDiscount').textContent = 'Rs. ' + fmt(discount);
            document.getElementById('sumDelivery').textContent = 'Rs. ' + fmt(delivery);
            document.getElementById('sumTotal').textContent = 'Rs. ' + fmt(total);
        }

        async function saveEditOrder() {
            const items = [];
            let hasError = false;
            document.querySelectorAll('.item-row').forEach(row => {
                const productId = row.querySelector('.edit-product-id')?.value;
                if (!productId) {
                    hasError = true;
                    row.querySelector('.product-search-input').style.borderColor = '#ef4444';
                    return;
                }
                items.push({
                    product_id: parseInt(productId),
                    quantity: parseFloat(row.querySelector('.edit-qty').value),
                    unit_price: parseFloat(row.querySelector('.edit-price').value),
                });
            });

            if (hasError) {
                alert('Please select a product for all items.');
                return;
            }

            if (items.length === 0) {
                alert('Add at least one item.');
                return;
            }

            const data = {
                customer_id: document.getElementById('editCustomer').value || null,
                items: items,
                payment_method: document.getElementById('editPaymentMethod').value,
                paid_amount: parseFloat(document.getElementById('editPaid').value) || 0,
                tax_rate: parseFloat(document.getElementById('editTaxRate').value) || 0,
                discount: parseFloat(document.getElementById('editDiscount').value) || 0,
                delivery_charges: parseFloat(document.getElementById('editDelivery').value) || 0,
                dispatch_method: document.getElementById('editDispatch').value,
                tracking_id: document.getElementById('editTrackingId').value || null,
                order_date: document.getElementById('editOrderDate')?.value || null,
                notes: document.getElementById('editNotes').value,
            };

            const btn = document.querySelector('.btn-save');
            btn.disabled = true;
            btn.textContent = 'Saving...';

            try {
                const res = await fetch('{{ route('admin.pos.update', $order) }}', {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(data),
                });
                const result = await res.json();
                if (result.success) {
                    alert('Order updated successfully!');
                    window.location.href = result.receipt_url;
                } else {
                    throw new Error(result.message || 'Update failed');
                }
            } catch (err) {
                alert('Error: ' + err.message);
            } finally {
                btn.disabled = false;
                btn.textContent = 'Save Changes';
            }
        }

        function toggleEditTracking() {
            const sel = document.getElementById('editDispatch');
            const opt = sel.options[sel.selectedIndex];
            const wrap = document.getElementById('editTrackingWrap');
            wrap.style.display = opt.getAttribute('data-has-tracking') === '1' ? '' : 'none';
        }

        recalcEdit();
    </script>
@endpush
