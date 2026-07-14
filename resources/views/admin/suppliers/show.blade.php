@extends('layouts.admin')

@section('title', 'Supplier Details')

@section('content')
    @php
        $purchases       = $supplier->purchases;
        $totalPurchased  = $purchases->sum('total_amount');
        $totalPaidOnPurchases = $purchases->sum('paid_amount');
        $purchaseCount   = $purchases->count();
    @endphp

    <div class="p-6 bg-white rounded-lg shadow-md">
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Supplier Details</h1>
                <p class="text-sm text-gray-600 mt-1">Complete supplier information</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('suppliers.edit', $supplier) }}"
                    class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 flex items-center">
                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Edit
                </a>
                <a href="{{ route('suppliers.ledger', $supplier) }}"
                    class="px-4 py-2 bg-emerald-600 text-white rounded hover:bg-emerald-700 flex items-center">
                    <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    View Ledger
                </a>
                <a href="{{ route('suppliers.index') }}"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 flex items-center">
                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    Back
                </a>
            </div>
        </div>

        {{-- Supplier Header Card --}}
        <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b">
                <div class="flex flex-wrap justify-between items-center gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">{{ $supplier->name }}</h2>
                        <p class="text-sm text-gray-600">
                            @if ($supplier->company_name)
                                <span class="inline-flex items-center gap-1 font-mono bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded">
                                    🏢 {{ $supplier->company_name }}
                                </span>
                            @else
                                <span class="text-gray-400">Individual supplier</span>
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-600">📦 {{ $purchaseCount }}</div>
                        <div class="text-xs text-gray-500">Total Purchases</div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Contact Info --}}
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Contact Information</h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-gray-900">
                                @if ($supplier->email)
                                    <a href="mailto:{{ $supplier->email }}" class="text-blue-600 hover:underline">
                                        {{ $supplier->email }}
                                    </a>
                                @else
                                    <span class="text-gray-400">Not provided</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Phone</label>
                            <p class="mt-1 text-gray-900">
                                @if ($supplier->phone)
                                    <a href="tel:{{ $supplier->phone }}" class="text-blue-600 hover:underline">
                                        {{ $supplier->phone }}
                                    </a>
                                @else
                                    <span class="text-gray-400">Not provided</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Address</label>
                            <p class="mt-1 text-gray-900 whitespace-pre-line">
                                {{ $supplier->address ?? 'Not provided' }}
                            </p>
                        </div>
                    </div>

                    {{-- Supplier Stats --}}
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Purchase Summary</h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white p-4 rounded-lg border text-center">
                                <div class="text-lg font-bold text-blue-600">
                                    Rs. {{ number_format($totalPurchased, 0) }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Total Purchased</div>
                            </div>

                            <div class="bg-white p-4 rounded-lg border text-center">
                                <div class="text-lg font-bold text-green-600">
                                    Rs. {{ number_format($totalPaidOnPurchases, 0) }}
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Paid on Purchases</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Created</label>
                            <p class="mt-1 text-gray-900">
                                {{ $supplier->created_at->format('F d, Y h:i A') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                            <p class="mt-1 text-gray-900">
                                {{ $supplier->updated_at->format('F d, Y h:i A') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Purchases --}}
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-800">Recent Purchases</h3>
                @if ($purchaseCount > 0)
                    <a href="{{ route('suppliers.ledger', $supplier) }}"
                        class="text-sm text-emerald-600 hover:text-emerald-800 font-medium">
                        View full ledger →
                    </a>
                @endif
            </div>
            <div class="p-6">
                @if ($purchaseCount > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Paid</th>
                                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($purchases->sortByDesc('purchase_date')->take(5) as $purchase)
                                    @php
                                        $balance = max(0, $purchase->total_amount - $purchase->paid_amount);
                                        $status  = $purchase->payment_status ?? ($balance <= 0 ? 'paid' : ($purchase->paid_amount > 0 ? 'partial' : 'unpaid'));
                                        $statusColors = [
                                            'paid'    => 'bg-green-100 text-green-800',
                                            'partial' => 'bg-yellow-100 text-yellow-800',
                                            'unpaid'  => 'bg-red-100 text-red-800',
                                        ];
                                        $badgeClass = $statusColors[$status] ?? 'bg-gray-100 text-gray-800';
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 text-sm text-blue-600 font-mono">
                                            <a href="{{ route('purchases.invoice', $purchase->id) }}" class="hover:underline">
                                                {{ $purchase->invoice_number }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-right text-gray-900">
                                            Rs. {{ number_format($purchase->total_amount, 0) }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-right text-green-600">
                                            Rs. {{ number_format($purchase->paid_amount, 0) }}
                                        </td>
                                        <td class="px-4 py-2 text-sm text-right {{ $balance > 0 ? 'text-red-600 font-semibold' : 'text-gray-400' }}">
                                            {{ $balance > 0 ? 'Rs. ' . number_format($balance, 0) : '—' }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badgeClass }}">
                                                {{ ucfirst($status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($purchaseCount > 5)
                        <div class="mt-4 text-center">
                            <a href="{{ route('suppliers.ledger', $supplier) }}"
                                class="text-blue-600 hover:underline text-sm">
                                View all {{ $purchaseCount }} purchases in ledger →
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-8 text-gray-500">
                        <svg class="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <p>No purchases recorded for this supplier yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
