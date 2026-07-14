@csrf
<div class="space-y-6">
    <!-- Barcode Field -->
    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
        <div class="flex justify-between items-center mb-2">
            <label class="block text-sm font-medium text-gray-700">Barcode</label>
            <span class="text-xs text-gray-500">Auto-generated if left empty</span>
        </div>
        <div class="flex space-x-2">
            <div class="flex-1 relative">
                <input type="text" name="barcode" id="barcode"
                    value="{{ old('barcode', $customer->barcode ?? \App\Models\Customer::generateBarcode()) }}"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 pr-10" placeholder="CUST123456">
                <button type="button" onclick="generateBarcode()"
                    class="absolute inset-y-0 right-0 px-3 text-gray-400 hover:text-gray-600">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                            clip-rule="evenodd" />
                    </svg>
                </button>
            </div>
            <button type="button" onclick="scanBarcode()"
                class="px-3 py-2 border border-gray-300 rounded-md text-sm hover:bg-gray-50">
                <svg class="h-5 w-5 inline mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                    fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M1 4a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1V4zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1V4zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V4zM1 9a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1V9zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1V9zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V9zM1 14a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1v-2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1v-2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1v-2z"
                        clip-rule="evenodd" />
                </svg>
                Scan
            </button>
        </div>
        <p class="mt-1 text-xs text-gray-500">
            Format: CUST + 6 digits (e.g., CUST123456). Barcodes are required for POS scanning.
        </p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Basic Information -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Basic Information</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700">Name *</label>
                <input type="text" name="name" value="{{ old('name', $customer->name ?? '') }}" required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $customer->email ?? '') }}"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone ?? '') }}"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="+92 300 1234567">
            </div>

            <div class="sm:col-span-2 lg:col-span-3 bg-rose-50/60 border border-rose-100 rounded-lg p-3">
                <label class="block text-sm font-medium text-gray-700">
                    <i class="fas fa-globe text-rose-500 mr-1"></i> Website login password
                    <span class="text-gray-400 font-normal">(optional — lets this customer log in online to see their khata/statement)</span>
                </label>
                <input type="text" name="website_password" value="" autocomplete="off"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                    placeholder="{{ isset($customer) && $customer->password ? 'Login already enabled — type a new password to change it' : 'Set a password to enable website login' }}">
                <p class="text-xs text-gray-500 mt-1">Customer signs in at the website using their <strong>phone or email</strong> + this password. Min 6 characters. Leave blank to keep unchanged.</p>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="space-y-4">
            <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Additional Information</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700">Customer Type *</label>
                <select name="customer_type" required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="customer"
                        {{ old('customer_type', $customer->customer_type ?? '') === 'customer' ? 'selected' : '' }}>
                        🛒 Customer (Regular)
                    </option>
                    <option value="reseller"
                        {{ old('customer_type', $customer->customer_type ?? '') === 'reseller' ? 'selected' : '' }}>
                        🏪 Reseller (Shopkeeper)
                    </option>
                    <option value="wholesale"
                        {{ old('customer_type', $customer->customer_type ?? '') === 'wholesale' ? 'selected' : '' }}>
                        🏭 Wholesale (Bulk Buyer)
                    </option>
                </select>
                <p class="mt-1 text-xs text-gray-500">
                    Customer type determines pricing in POS system
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Loyalty Points</label>
                <div class="relative">
                    <input type="number" name="loyalty_points"
                        value="{{ old('loyalty_points', $customer->loyalty_points ?? 0) }}" min="0"
                        step="1"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 pl-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">🏆</span>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Reward points for customer loyalty program
                </p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Address</label>
                <textarea name="address" rows="3"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Full address for delivery">{{ old('address', $customer->address ?? '') }}</textarea>
            </div>
        </div>
    </div>


</div>

<div class="mt-8 pt-6 border-t border-gray-200 flex justify-between">
    <a href="{{ route('admin.customers.index') }}"
        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
        Cancel
    </a>
    <button type="submit"
        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        {{ $buttonText }}
    </button>
</div>

@push('scripts')
    <script>
        // Toggle credit fields based on checkbox
        function toggleCreditFields() {
            const isEnabled = document.getElementById('credit_enabled').checked;
            const creditLimit = document.getElementById('credit_limit');
            const creditDueDays = document.getElementById('credit_due_days');

            if (creditLimit) {
                creditLimit.disabled = !isEnabled;
                if (!isEnabled) creditLimit.value = 0;
            }

            if (creditDueDays) {
                creditDueDays.disabled = !isEnabled;
                if (!isEnabled) creditDueDays.value = 30;
            }
        }

        // Barcode functions
        function generateBarcode() {
            fetch('{{ route('admin.customers.generate-barcode') }}')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('barcode').value = data.barcode;
                    showNotification('New barcode generated: ' + data.barcode, 'success');
                })
                .catch(error => {
                    console.error('Error generating barcode:', error);
                    showNotification('Error generating barcode', 'error');
                });
        }

        function scanBarcode() {
            const scannerInput = document.createElement('input');
            scannerInput.type = 'text';
            scannerInput.style.position = 'fixed';
            scannerInput.style.top = '-100px';
            scannerInput.style.left = '-100px';
            scannerInput.style.opacity = '0';
            scannerInput.autocomplete = 'off';

            document.body.appendChild(scannerInput);
            scannerInput.focus();

            let barcode = '';
            let lastKeyTime = Date.now();

            scannerInput.addEventListener('keydown', function(e) {
                const currentTime = Date.now();

                if (currentTime - lastKeyTime > 100) {
                    barcode = '';
                }

                lastKeyTime = currentTime;

                if (e.key.length === 1 && !e.ctrlKey && !e.altKey && !e.metaKey) {
                    barcode += e.key;
                }

                if (e.key === 'Enter') {
                    e.preventDefault();

                    if (barcode.length >= 3) {
                        document.getElementById('barcode').value = barcode;
                        showNotification('Barcode scanned: ' + barcode, 'success');
                        scannerInput.remove();
                    } else {
                        showNotification('Invalid barcode length', 'error');
                    }

                    barcode = '';
                }
            });

            scannerInput.addEventListener('blur', function() {
                setTimeout(() => scannerInput.remove(), 100);
            });

            showNotification('Ready to scan barcode. Please scan now...', 'info');
        }

        function showNotification(message, type = 'info') {
            const existing = document.querySelector('.customer-notification');
            if (existing) existing.remove();

            const notification = document.createElement('div');
            notification.className = `customer-notification fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg ${
                type === 'success' ? 'bg-green-500' : 
                type === 'error' ? 'bg-red-500' : 
                'bg-blue-500'
            } text-white`;
            notification.textContent = message;
            notification.style.animation = 'slideIn 0.3s ease-out';

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleCreditFields();

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

                .credit-field {
                    transition: opacity 0.3s ease;
                }
            `;
            document.head.appendChild(style);
        });
    </script>
@endpush
