@extends('layouts.admin')

@section('title', 'Collect Credit Payment')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Collect Credit Payment</h1>
                <p class="text-sm text-gray-600 mt-1">Receive payment from customer</p>
            </div>
            <a href="{{ route('admin.credit.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18">
                    </path>
                </svg>
                Back to Credit
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Customer Selection & Payment Form -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="px-6 py-4 border-b bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-800">Step 1: Select Customer</h3>
                    </div>
                    <div class="p-6">
                        <form method="GET" action="{{ route('admin.credit.payment') }}" class="flex gap-4">
                            <div class="flex-1">
                                <select name="customer_id" onchange="this.form.submit()"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select a customer</option>
                                    @foreach ($customers as $cust)
                                        <option value="{{ $cust->id }}"
                                            {{ isset($customer) && $customer->id == $cust->id ? 'selected' : '' }}>
                                            {{ $cust->name }} (Rs. {{ number_format($cust->current_balance, 2) }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            @if (request('customer_id'))
                                <a href="{{ route('admin.credit.payment') }}"
                                    class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                                    Clear
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                @if (isset($customer))
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-6 py-4 border-b bg-gray-50">
                            <h3 class="text-lg font-medium text-gray-800">Step 2: Payment Details</h3>
                        </div>
                        <div class="p-6">
                            <!-- Customer Balance Summary -->
                            <div class="bg-blue-50 rounded-lg p-4 mb-6">
                                <div class="grid grid-cols-3 gap-4">
                                    <div>
                                        <p class="text-xs text-blue-600 mb-1">Current Balance</p>
                                        <p class="text-xl font-bold text-gray-800">Rs.
                                            {{ number_format($customer->current_balance, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-600 mb-1">Credit Limit</p>
                                        <p class="text-xl font-bold text-gray-800">Rs.
                                            {{ number_format($customer->credit_limit, 2) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-blue-600 mb-1">Available Credit</p>
                                        <p class="text-xl font-bold text-gray-800">Rs.
                                            {{ number_format($customer->available_credit, 2) }}</p>
                                    </div>
                                </div>
                            </div>

                            <form id="paymentForm" action="{{ route('admin.credit.payment.process') }}" method="POST">
                                @csrf
                                <input type="hidden" name="customer_id" value="{{ $customer->id }}">

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Date *</label>
                                        <input type="date" name="payment_date" required
                                            value="{{ now()->format('Y-m-d') }}"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                                        <select name="payment_method" required
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                            <option value="">Select Method</option>
                                            @foreach(\App\Models\PaymentMethod::where('is_active', true)->where('name', '!=', 'pending')->orderBy('sort_order')->get() as $pm)
                                                <option value="{{ $pm->name }}">{{ $pm->label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Reference Number</label>
                                        <input type="text" name="reference_number"
                                            placeholder="Transaction ID / Cheque No"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                                        <input type="text" name="notes" placeholder="Additional notes"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>

                                @if (count($outstandingTransactions) > 0)
                                    <div class="mb-6">
                                        <div class="flex justify-between items-center mb-3">
                                            <h4 class="font-medium text-gray-700">Outstanding Invoices</h4>
                                            <div class="flex items-center">
                                                <span class="text-sm text-gray-600 mr-2">Total Due:</span>
                                                <span class="text-lg font-bold text-red-600 total-due">Rs.
                                                    {{ number_format($outstandingTransactions->sum('remaining_amount'), 2) }}</span>
                                            </div>
                                        </div>

                                        <div class="overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 border">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th
                                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                            <input type="checkbox" id="selectAll"
                                                                class="rounded border-gray-300 text-blue-600">
                                                        </th>
                                                        <th
                                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                            Date</th>
                                                        <th
                                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                            Invoice #</th>
                                                        <th
                                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                                            Description</th>
                                                        <th
                                                            class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                                            Due Date</th>
                                                        <th
                                                            class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                                            Original Amount</th>
                                                        <th
                                                            class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                                            Paid</th>
                                                        <th
                                                            class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                                                            Remaining</th>
                                                        <th
                                                            class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">
                                                            Payment</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    @foreach ($outstandingTransactions as $transaction)
                                                        <tr class="hover:bg-gray-50">
                                                            <td class="px-4 py-2">
                                                                <input type="checkbox"
                                                                    name="transactions[{{ $loop->index }}][id]"
                                                                    value="{{ $transaction->id }}"
                                                                    class="transaction-checkbox rounded border-gray-300 text-blue-600"
                                                                    data-remaining="{{ $transaction->remaining_amount }}">
                                                            </td>
                                                            <td class="px-4 py-2 text-sm">
                                                                {{ $transaction->transaction_date->format('d M Y') }}</td>
                                                            <td class="px-4 py-2 text-sm font-medium">
                                                                {{ $transaction->reference_number }}</td>
                                                            <td class="px-4 py-2 text-sm max-w-xs truncate">
                                                                {{ $transaction->description }}</td>
                                                            <td class="px-4 py-2 text-sm text-right">
                                                                @if ($transaction->due_date < now())
                                                                    <span
                                                                        class="text-red-600">{{ $transaction->due_date->format('d M Y') }}</span>
                                                                @else
                                                                    {{ $transaction->due_date->format('d M Y') }}
                                                                @endif
                                                            </td>
                                                            <td class="px-4 py-2 text-sm text-right">Rs.
                                                                {{ number_format($transaction->amount, 2) }}</td>
                                                            <td class="px-4 py-2 text-sm text-right text-green-600">Rs.
                                                                {{ number_format($transaction->paid_amount, 2) }}</td>
                                                            <td
                                                                class="px-4 py-2 text-sm text-right font-medium text-red-600">
                                                                Rs. {{ number_format($transaction->remaining_amount, 2) }}
                                                            </td>
                                                            <td class="px-4 py-2">
                                                                <input type="number"
                                                                    name="transactions[{{ $loop->index }}][paid_amount]"
                                                                    value="{{ $transaction->remaining_amount }}"
                                                                    min="0"
                                                                    max="{{ $transaction->remaining_amount }}"
                                                                    step="0.01"
                                                                    class="payment-amount w-24 px-2 py-1 text-right border border-gray-300 rounded-md text-sm"
                                                                    disabled data-transaction-id="{{ $transaction->id }}">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="flex justify-end mt-4">
                                            <div class="w-64 bg-gray-50 p-4 rounded-lg">
                                                <div class="flex justify-between mb-2">
                                                    <span class="text-sm text-gray-600">Selected Amount:</span>
                                                    <span id="selectedTotal" class="font-bold text-blue-600">Rs.
                                                        0.00</span>
                                                </div>
                                                <div class="flex justify-between pt-2 border-t">
                                                    <span class="font-medium">Payment Total:</span>
                                                    <span id="paymentTotal" class="font-bold text-lg text-green-600">Rs.
                                                        0.00</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end space-x-3">
                                        <button type="button"
                                            onclick="window.location.href='{{ route('admin.credit.payment') }}'"
                                            class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                            Cancel
                                        </button>
                                        <button type="submit" id="submitPayment"
                                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                                            Process Payment
                                        </button>
                                    </div>
                                @else
                                    <div class="text-center py-8">
                                        <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        <p class="text-gray-500">No outstanding invoices for this customer</p>
                                        <p class="text-sm text-gray-400 mt-1">All payments are up to date</p>
                                    </div>
                                @endif
                            </form>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Quick Stats -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow sticky top-20">
                    <div class="px-6 py-4 border-b bg-gray-50">
                        <h3 class="text-lg font-medium text-gray-800">Quick Stats</h3>
                    </div>
                    <div class="p-6">
                        @if (isset($customer))
                            <div class="space-y-4">
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Customer Since</p>
                                    <p class="text-lg font-medium">{{ $customer->created_at->format('d M Y') }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Total Credit Purchases</p>
                                    <p class="text-lg font-medium">Rs.
                                        {{ number_format($customer->creditLedger->total_debit ?? 0, 2) }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Total Payments Made</p>
                                    <p class="text-lg font-medium text-green-600">Rs.
                                        {{ number_format($customer->creditLedger->total_credit ?? 0, 2) }}</p>
                                </div>
                                <div class="pt-4 border-t">
                                    <p class="text-xs text-gray-500 mb-1">Credit Utilization</p>
                                    @php
                                        $utilization =
                                            $customer->credit_limit > 0
                                                ? ($customer->current_balance / $customer->credit_limit) * 100
                                                : 0;
                                    @endphp
                                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-2">
                                        <div class="h-2.5 rounded-full {{ $utilization >= 90 ? 'bg-red-600' : ($utilization >= 70 ? 'bg-yellow-500' : 'bg-green-600') }}"
                                            style="width: {{ min($utilization, 100) }}%"></div>
                                    </div>
                                    <p class="text-sm text-gray-600">{{ number_format($utilization, 1) }}% used</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 mb-1">Payment Terms</p>
                                    <p class="text-sm">{{ $customer->credit_due_days }} days</p>
                                </div>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                                <p class="text-gray-500">Select a customer to view stats</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Payment form handling
            document.addEventListener('DOMContentLoaded', function() {
                const selectAllCheckbox = document.getElementById('selectAll');
                const transactionCheckboxes = document.querySelectorAll('.transaction-checkbox');
                const paymentAmounts = document.querySelectorAll('.payment-amount');
                const selectedTotal = document.getElementById('selectedTotal');
                const paymentTotal = document.getElementById('paymentTotal');

                // Format currency
                function formatCurrency(amount) {
                    return 'Rs. ' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }

                // Format number (without Rs. prefix)
                function formatNumber(amount) {
                    return parseFloat(amount || 0).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
                }

                // Update totals
                function updateTotals() {
                    let selected = 0;
                    let totalPayment = 0;

                    transactionCheckboxes.forEach((checkbox, index) => {
                        if (checkbox.checked) {
                            const remaining = parseFloat(checkbox.dataset.remaining) || 0;
                            const amount = parseFloat(paymentAmounts[index]?.value) || 0;
                            selected += remaining;
                            totalPayment += amount;
                        }
                    });

                    if (selectedTotal) {
                        selectedTotal.textContent = formatCurrency(selected);
                    }
                    if (paymentTotal) {
                        paymentTotal.textContent = formatCurrency(totalPayment);
                    }

                    // Call payment summary update
                    updatePaymentSummary(selected, totalPayment);
                }

                // Add payment summary
                function updatePaymentSummary(totalSelected, totalPayment) {
                    const paymentTotal = document.getElementById('paymentTotal');

                    if (!paymentTotal) return;

                    // Remove existing summary note
                    const existingNote = document.getElementById('paymentSummaryNote');
                    if (existingNote) {
                        existingNote.remove();
                    }

                    if (totalPayment > 0) {
                        const summaryDiv = document.createElement('div');
                        summaryDiv.className = 'mt-2 text-xs text-gray-500';
                        summaryDiv.id = 'paymentSummaryNote';
                        summaryDiv.innerHTML =
                            `You are paying Rs. ${formatNumber(totalPayment)} towards total due of Rs. ${formatNumber(totalSelected)}`;

                        // Insert after paymentTotal
                        paymentTotal.parentElement.appendChild(summaryDiv);
                    }
                }

                // Make functions available globally if needed
                window.updateTotals = updateTotals;
                window.formatNumber = formatNumber;

                // Select All functionality
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        transactionCheckboxes.forEach((checkbox, index) => {
                            checkbox.checked = this.checked;
                            if (paymentAmounts[index]) {
                                paymentAmounts[index].disabled = !this.checked;
                                if (!this.checked) {
                                    paymentAmounts[index].value = paymentAmounts[index].dataset.max ||
                                    0;
                                }
                            }
                        });
                        updateTotals();
                    });
                }

                // Individual checkbox change
                transactionCheckboxes.forEach((checkbox, index) => {
                    checkbox.addEventListener('change', function() {
                        if (paymentAmounts[index]) {
                            paymentAmounts[index].disabled = !this.checked;
                            if (!this.checked) {
                                paymentAmounts[index].value = paymentAmounts[index].dataset.max || 0;
                            }
                        }
                        updateTotals();

                        // Update Select All checkbox
                        if (selectAllCheckbox) {
                            const allChecked = Array.from(transactionCheckboxes).every(cb => cb
                            .checked);
                            selectAllCheckbox.checked = allChecked;
                            selectAllCheckbox.indeterminate = !allChecked && Array.from(
                                transactionCheckboxes).some(cb => cb.checked);
                        }
                    });

                    // Trigger initial state
                    if (checkbox.checked && paymentAmounts[index]) {
                        paymentAmounts[index].disabled = false;
                    }
                });

                // Payment amount change
                paymentAmounts.forEach((input, index) => {
                    if (input) {
                        // Store max value
                        input.dataset.max = input.value;

                        input.addEventListener('input', function() {
                            let value = parseFloat(this.value) || 0;
                            const max = parseFloat(this.dataset.max) || 0;

                            if (value > max) {
                                this.value = max;
                                value = max;
                            }
                            if (value < 0) {
                                this.value = 0;
                                value = 0;
                            }

                            updateTotals();
                        });
                    }
                });

                // Form validation
                const paymentForm = document.getElementById('paymentForm');
                if (paymentForm) {
                    paymentForm.addEventListener('submit', function(e) {
                        const checkedBoxes = Array.from(transactionCheckboxes).filter(cb => cb.checked);

                        if (checkedBoxes.length === 0) {
                            e.preventDefault();
                            alert('Please select at least one invoice to pay');
                            return;
                        }

                        let totalPayment = 0;
                        checkedBoxes.forEach((checkbox) => {
                            const index = Array.from(transactionCheckboxes).indexOf(checkbox);
                            totalPayment += parseFloat(paymentAmounts[index]?.value) || 0;
                        });

                        if (totalPayment <= 0) {
                            e.preventDefault();
                            alert('Payment amount must be greater than 0');
                            return;
                        }

                        if (!confirm(`Process payment of ${formatCurrency(totalPayment)}?`)) {
                            e.preventDefault();
                        }
                    });
                }

                // Initial update
                updateTotals();
            });
        </script>
    @endpush
@endsection
