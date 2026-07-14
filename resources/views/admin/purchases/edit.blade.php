@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Purchase Order — {{ $purchase->invoice_number }}</h1>
            <a href="{{ route('purchases.show', $purchase) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Back to Purchase
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <form action="{{ route('purchases.update', $purchase) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="supplier_id" class="block text-sm font-medium text-gray-700">Supplier *</label>
                            <div class="supplier-search-wrap">
                                <input type="hidden" name="supplier_id" id="supplier_id" value="{{ $purchase->supplier_id }}" required>
                                <input type="text" id="supplier_search" class="product-search-input" placeholder="Search supplier by name, company, phone..." autocomplete="off"
                                    value="{{ $purchase->supplier->name ?? '' }}">
                                <div class="product-dropdown" id="supplier_dropdown"></div>
                            </div>
                        </div>
                        <div>
                            <label for="purchase_date" class="block text-sm font-medium text-gray-700">Purchase Date *</label>
                            <input type="date" name="purchase_date" id="purchase_date" required
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   value="{{ old('purchase_date', \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d')) }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Purchase Items *</label>
                        <div id="purchase-items" class="space-y-4">
                            <!-- Existing items will be loaded by JS -->
                        </div>
                        <button type="button" id="add-item" class="mt-2 bg-gray-200 hover:bg-gray-300 text-gray-800 px-3 py-1 rounded text-sm">
                            + Add Item
                        </button>
                    </div>

                    {{-- Previous Balance --}}
                    <div id="prev-balance-box" style="display:none;" class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="text-sm font-semibold text-orange-700">Supplier Previous Balance (سابقہ بقایا)</p>
                                <p class="text-xs text-orange-500">Outstanding from previous purchases</p>
                            </div>
                            <p class="text-xl font-bold text-orange-700" id="prev-balance-amount">Rs. 0</p>
                        </div>
                    </div>

                    {{-- Expenses Section --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Expenses / اخراجات (Bilty, Packing, Delivery, Labor etc.)</label>
                        <div id="expense-rows" class="space-y-2"></div>
                        <button type="button" id="add-expense" class="mt-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-3 py-1 rounded text-sm">
                            + Add Expense
                        </button>
                        <p class="text-xs text-gray-400 mt-1">Expenses will be divided across all items and added to their cost price</p>
                    </div>

                    {{-- Discount --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="purchase_discount" class="block text-sm font-medium text-gray-700">Discount (ڈسکاؤنٹ)</label>
                            <input type="number" step="0.01" min="0" name="discount" id="purchase_discount"
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   value="{{ old('discount', $purchase->discount ?? 0) }}" oninput="calculateTotal()">
                            <p class="text-xs text-gray-400 mt-1">Discount will be subtracted from items' cost price</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Total Expenses</label>
                            <input type="text" id="total_expenses_display" readonly
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-yellow-50 sm:text-sm"
                                   value="0.00">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <label for="payment_status" class="block text-sm font-medium text-gray-700">Payment Status *</label>
                            <select name="payment_status" id="payment_status" required
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option value="unpaid" {{ $purchase->payment_status === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="partial" {{ $purchase->payment_status === 'partial' ? 'selected' : '' }}>Partial</option>
                                <option value="paid" {{ $purchase->payment_status === 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>
                        <div>
                            <label for="paid_amount" class="block text-sm font-medium text-gray-700">Paid Amount *</label>
                            <input type="number" step="0.01" min="0" name="paid_amount" id="paid_amount" required
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                   value="{{ old('paid_amount', $purchase->paid_amount) }}">
                        </div>
                        <div>
                            <label for="payment_method" class="block text-sm font-medium text-gray-700">Paid From Account</label>
                            <select name="payment_method" id="payment_method"
                                    class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                @php $selectedPm = old('payment_method', $purchase->payment_method ?? 'cash'); @endphp
                                @foreach($paymentMethods as $method)
                                    @php $bal = $accountBals[strtolower($method->name)]['balance'] ?? 0; @endphp
                                    <option value="{{ $method->name }}" {{ $selectedPm === $method->name ? 'selected' : '' }}>
                                        {{ $method->label }} — Available: Rs. {{ number_format($bal, 0) }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1">Which account is this payment coming from? Balance shown is current available.</p>
                        </div>
                        <div>
                            <label for="total_amount" class="block text-sm font-medium text-gray-700">Grand Total</label>
                            <input type="text" id="total_amount" readonly
                                   class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 bg-gray-100 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm font-bold text-lg"
                                   value="{{ number_format($purchase->total_amount, 2) }}">
                        </div>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                        <textarea name="notes" id="notes" rows="3"
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">{{ old('notes', $purchase->notes) }}</textarea>
                    </div>
                </div>
                <div class="px-6 py-3 bg-gray-50 text-right">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                        Update Purchase Order
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        .product-search-wrap, .supplier-search-wrap { position: relative; }
        .product-search-input {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            box-sizing: border-box;
        }
        .product-search-input:focus { border-color: #3b82f6; outline: none; box-shadow: 0 0 0 1px #3b82f6; }
        .product-dropdown {
            position: absolute; top: 100%; left: 0; right: 0;
            background: #fff; border: 1.5px solid #3b82f6; border-top: none;
            border-radius: 0 0 6px 6px; max-height: 220px; overflow-y: auto;
            z-index: 100; display: none; box-shadow: 0 4px 12px rgba(0,0,0,.12);
        }
        .product-dropdown.show { display: block; }
        .product-option { padding: 8px 10px; font-size: 13px; cursor: pointer; border-bottom: 1px solid #f1f5f9; }
        .product-option:hover { background: #eff6ff; }
        .product-option .po-name { font-weight: 600; color: #1e293b; }
        .product-option .po-meta { font-size: 11px; color: #9ca3af; }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const products = @json($products);
            const suppliers = @json($suppliers);
            const existingItems = @json($existingItems);
            let itemCount = 0;

            // ── Supplier search ──
            const supplierInput = document.getElementById('supplier_search');
            const supplierHidden = document.getElementById('supplier_id');
            const supplierDropdown = document.getElementById('supplier_dropdown');

            function filterSuppliers() {
                const search = supplierInput.value.toLowerCase().trim();
                const filtered = suppliers.filter(s => {
                    return (s.name || '').toLowerCase().includes(search)
                        || (s.company_name || '').toLowerCase().includes(search)
                        || (s.phone || '').toLowerCase().includes(search);
                }).slice(0, 15);

                if (filtered.length === 0) {
                    supplierDropdown.innerHTML = '<div class="product-option" style="color:#9ca3af;cursor:default;">No suppliers found</div>';
                } else {
                    supplierDropdown.innerHTML = filtered.map(s => `
                        <div class="product-option" data-id="${s.id}" data-name="${s.name}">
                            <div class="po-name">${s.name}</div>
                            <div class="po-meta">${s.company_name || ''} · ${s.phone || ''}</div>
                        </div>
                    `).join('');
                }
                supplierDropdown.classList.add('show');

                supplierDropdown.querySelectorAll('.product-option[data-id]').forEach(opt => {
                    opt.onclick = function() {
                        supplierInput.value = this.dataset.name;
                        supplierHidden.value = this.dataset.id;
                        supplierDropdown.classList.remove('show');
                    };
                });
            }

            supplierInput.addEventListener('focus', filterSuppliers);
            supplierInput.addEventListener('input', function() {
                supplierHidden.value = '';
                filterSuppliers();
            });

            // ── Supplier balance display ──
            const supplierBalances = @json($supplierBalances);
            const prevBalBox = document.getElementById('prev-balance-box');
            const prevBalAmt = document.getElementById('prev-balance-amount');

            function updateSupplierBalance() {
                const sid = supplierHidden.value;
                if (sid && supplierBalances[sid] && supplierBalances[sid] != 0) {
                    const bal = parseFloat(supplierBalances[sid]);
                    prevBalBox.style.display = 'block';
                    if (bal > 0) {
                        prevBalAmt.textContent = 'Rs. ' + Math.abs(bal).toLocaleString();
                        prevBalBox.className = 'bg-orange-50 border border-orange-200 rounded-lg p-4';
                        prevBalBox.querySelector('p').className = 'text-sm font-semibold text-orange-700';
                        prevBalBox.querySelector('p').textContent = 'Supplier Previous Balance (سابقہ بقایا)';
                    } else {
                        prevBalAmt.textContent = 'Rs. ' + Math.abs(bal).toLocaleString();
                        prevBalBox.className = 'bg-blue-50 border border-blue-200 rounded-lg p-4';
                        prevBalBox.querySelector('p').className = 'text-sm font-semibold text-blue-700';
                        prevBalBox.querySelector('p').textContent = 'Our Advance Payment (ہماری ایڈوانس)';
                    }
                } else {
                    prevBalBox.style.display = 'none';
                }
            }
            supplierDropdown.addEventListener('click', () => setTimeout(updateSupplierBalance, 50));
            // Show balance on page load if supplier already selected
            updateSupplierBalance();

            // ── Expense rows ──
            const existingExpenses = @json($purchase->expenses ?? []);
            let expenseCount = 0;

            function addExpenseRow(label = '', amount = '') {
                expenseCount++;
                const html = `
                <div class="grid grid-cols-1 md:grid-cols-3 gap-2 expense-row">
                    <input type="text" name="expenses[${expenseCount}][label]" placeholder="e.g. Bilty, Packing, Labor..."
                        class="border border-gray-300 rounded-md px-3 py-2 text-sm" value="${label}">
                    <input type="number" name="expenses[${expenseCount}][amount]" placeholder="Amount" min="0" step="0.01"
                        class="expense-amount border border-gray-300 rounded-md px-3 py-2 text-sm" value="${amount}" oninput="calculateTotal()">
                    <button type="button" class="text-red-500 hover:text-red-700 text-sm" onclick="this.closest('.expense-row').remove();calculateTotal();">Remove</button>
                </div>`;
                document.getElementById('expense-rows').insertAdjacentHTML('beforeend', html);
            }

            // Pre-populate saved expenses
            existingExpenses.forEach(e => addExpenseRow(e.label || '', e.amount || ''));

            document.getElementById('add-expense').addEventListener('click', function() {
                addExpenseRow();
            });

            function addItemRow(data = null) {
                itemCount++;
                const itemHtml = `
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 item-row">
            <div class="product-search-wrap">
                <input type="hidden" name="items[${itemCount}][product_id]" class="product-id-hidden" value="${data ? data.product_id : ''}" required>
                <input type="text" class="product-search-input" placeholder="Search by name, code, barcode..." autocomplete="off" value="${data ? data.product_name : ''}">
                <div class="product-dropdown"></div>
            </div>
            <div>
                <input type="number" name="items[${itemCount}][quantity]" required min="1" placeholder="Qty"
                    class="quantity block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    value="${data ? data.quantity : ''}">
            </div>
            <div>
                <input type="number" step="0.01" name="items[${itemCount}][unit_price]" required min="0" placeholder="Unit Price"
                    class="unit-price block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    value="${data ? data.unit_price : ''}">
            </div>
            <div class="flex items-center">
                <span class="total-price text-sm font-medium">${data ? (data.quantity * data.unit_price).toFixed(2) : '0.00'}</span>
                <button type="button" class="ml-auto text-red-500 hover:text-red-700 remove-item">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
        </div>`;
                document.getElementById('purchase-items').insertAdjacentHTML('beforeend', itemHtml);
                addItemEventListeners();
            }

            // Load existing items
            existingItems.forEach(item => addItemRow(item));
            if (existingItems.length === 0) addItemRow();

            document.getElementById('add-item').addEventListener('click', function() {
                addItemRow();
                const rows = document.querySelectorAll('.item-row');
                rows[rows.length - 1].querySelector('.product-search-input').focus();
            });

            function addItemEventListeners() {
                document.querySelectorAll('.remove-item').forEach(btn => {
                    btn.onclick = function() {
                        this.closest('.item-row').remove();
                        calculateTotal();
                    };
                });

                document.querySelectorAll('.product-search-input').forEach(input => {
                    input.onfocus = function() { filterProducts(this); };
                    input.oninput = function() {
                        const hidden = this.closest('.product-search-wrap').querySelector('.product-id-hidden');
                        hidden.value = '';
                        filterProducts(this);
                    };
                });

                document.querySelectorAll('.quantity, .unit-price').forEach(input => {
                    input.oninput = function() { calculateRowTotal(this.closest('.item-row')); };
                });
            }

            function filterProducts(input) {
                document.querySelectorAll('.product-dropdown.show').forEach(d => d.classList.remove('show'));
                const wrap = input.closest('.product-search-wrap');
                const dropdown = wrap.querySelector('.product-dropdown');
                const search = input.value.toLowerCase().trim();

                const filtered = products.filter(p => {
                    const name = (p.name || '').toLowerCase();
                    const barcode = (p.barcode || '').toLowerCase();
                    const catName = (p.category && p.category.name ? p.category.name : '').toLowerCase();
                    return !search || name.includes(search) || barcode.includes(search) || catName.includes(search);
                }).slice(0, 20);

                if (filtered.length === 0) {
                    dropdown.innerHTML = '<div class="product-option" style="color:#9ca3af;cursor:default;">No products found</div>';
                } else {
                    dropdown.innerHTML = filtered.map(p => `
                        <div class="product-option" data-id="${p.id}" data-name="${p.name}" data-price="${p.cost_price || 0}">
                            <div class="po-name">${p.name}</div>
                            <div class="po-meta">${p.barcode || 'No barcode'} · ${p.category ? p.category.name : ''} · Cost: Rs.${parseFloat(p.cost_price||0).toLocaleString()}</div>
                        </div>
                    `).join('');
                }
                dropdown.classList.add('show');

                dropdown.querySelectorAll('.product-option[data-id]').forEach(opt => {
                    opt.onclick = function() {
                        const wrap = this.closest('.product-search-wrap');
                        const row = this.closest('.item-row');
                        wrap.querySelector('.product-search-input').value = this.dataset.name;
                        wrap.querySelector('.product-id-hidden').value = this.dataset.id;
                        row.querySelector('.unit-price').value = this.dataset.price;
                        dropdown.classList.remove('show');
                        calculateRowTotal(row);
                    };
                });
            }

            document.addEventListener('click', function(e) {
                if (!e.target.closest('.product-search-wrap') && !e.target.closest('.supplier-search-wrap')) {
                    document.querySelectorAll('.product-dropdown.show').forEach(d => d.classList.remove('show'));
                }
            });

            // Exposed on window so inline oninput="calculateTotal()" attributes
            // (on the discount input and dynamically inserted expense rows) can find them.
            window.calculateRowTotal = function (row) {
                const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
                const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
                const total = quantity * unitPrice;
                row.querySelector('.total-price').textContent = total.toFixed(2);
                window.calculateTotal();
            };

            window.calculateTotal = function () {
                let itemsTotal = 0;
                document.querySelectorAll('.item-row').forEach(row => {
                    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
                    const unitPrice = parseFloat(row.querySelector('.unit-price').value) || 0;
                    itemsTotal += quantity * unitPrice;
                });

                let expensesTotal = 0;
                document.querySelectorAll('.expense-amount').forEach(input => {
                    expensesTotal += parseFloat(input.value) || 0;
                });

                const discount = parseFloat(document.getElementById('purchase_discount').value) || 0;
                const grandTotal = itemsTotal + expensesTotal - discount;

                document.getElementById('total_expenses_display').value = expensesTotal.toFixed(2);
                document.getElementById('total_amount').value = grandTotal.toFixed(2);
            };

            // Local aliases so existing inner code keeps working unchanged.
            const calculateTotal = window.calculateTotal;
            const calculateRowTotal = window.calculateRowTotal;

            calculateTotal();
        });
    </script>
@endsection
