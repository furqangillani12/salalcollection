@csrf
<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        {{-- Basic Information --}}
        <div class="space-y-4">
            <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Basic Information</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $supplier->name ?? '') }}" required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Supplier name">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Company Name</label>
                <div class="relative">
                    <input type="text" name="company_name" value="{{ old('company_name', $supplier->company_name ?? '') }}"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 pl-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        placeholder="e.g. ABC Trading Co.">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <span class="text-gray-500">🏢</span>
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Leave blank for individual suppliers</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Phone <span class="text-red-500">*</span></label>
                <input type="text" name="phone" value="{{ old('phone', $supplier->phone ?? '') }}" required
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="+92 300 1234567">
            </div>
        </div>

        {{-- Additional Information --}}
        <div class="space-y-4">
            <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Additional Information</h3>

            <div>
                <label class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" name="email" value="{{ old('email', $supplier->email ?? '') }}"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="supplier@example.com">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Address</label>
                <textarea name="address" rows="5"
                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    placeholder="Full address">{{ old('address', $supplier->address ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- ── LINKED-CUSTOMER OPTION ── --}}
    @php $alreadyLinked = isset($supplier) && $supplier && $supplier->linked_customer_id; @endphp
    <div class="bg-cyan-50/40 border border-cyan-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-cyan-800 flex items-center gap-2 mb-3">
            <i class="fas fa-link text-xs"></i> Supplier–Customer Link
        </h3>

        @if ($alreadyLinked)
            @php $lc = $supplier->linkedCustomer; @endphp
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-white border border-cyan-200 rounded-md px-3 py-2">
                <div class="text-sm">
                    <span class="text-gray-600">Already linked to customer:</span>
                    <a href="{{ route('admin.customers.khata', $lc) }}" class="font-semibold text-cyan-700 hover:underline">{{ $lc->name }}</a>
                    @if ($lc->phone) <span class="text-gray-400 text-xs">· {{ $lc->phone }}</span> @endif
                </div>
                <span class="text-[11px] text-gray-500">Use the supplier ledger to unlink or apply offsets.</span>
            </div>
        @else
            <label for="also_customer" class="flex items-start gap-2 cursor-pointer select-none">
                <input type="checkbox" name="also_customer" id="also_customer" value="1"
                       {{ old('also_customer') ? 'checked' : '' }}
                       class="mt-0.5 rounded border-gray-300 text-cyan-600 focus:ring-cyan-500">
                <div>
                    <div class="text-sm font-semibold text-gray-800">This supplier is also a customer</div>
                    <div class="text-xs text-gray-500">
                        We also sell goods to them. A matching customer record will be created and linked automatically,
                        so you can offset what you owe them against their khata in one click.
                        @if (isset($supplier) && $supplier && $supplier->phone)
                            <br>If a customer with phone <strong>{{ $supplier->phone }}</strong> already exists, that one will be linked instead.
                        @endif
                    </div>
                </div>
            </label>
        @endif
    </div>
</div>

<div class="mt-8 pt-6 border-t border-gray-200 flex justify-between">
    <a href="{{ route('suppliers.index') }}"
        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50">
        Cancel
    </a>
    <button type="submit"
        class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
        {{ $buttonText }}
    </button>
</div>
