@extends('layouts.admin')

@section('title', 'Order #'.$order->order_number)

@section('content')
    <style>
        .order-detail-card {
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .info-item label {
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: 600;
        }
        .info-item p {
            font-size: 0.95rem;
            color: #111827;
            margin-top: 2px;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-completed { background: #d1fae5; color: #065f46; }
        .status-pending { background: #fef3c7; color: #92400e; }
        .status-cancelled { background: #e5e7eb; color: #4b5563; }
        .status-refunded { background: #fee2e2; color: #991b1b; }

        @media (max-width: 640px) {
            .info-grid { grid-template-columns: 1fr; }
            .order-detail-card { padding: 1rem; }
        }

        @media (prefers-color-scheme: dark) {
            .order-detail-card { background: #1f2937; box-shadow: 0 1px 3px rgba(0,0,0,0.4); }
            .info-item label { color: #9ca3af; }
            .info-item p { color: #f3f4f6; }
            .status-completed { background: #064e3b; color: #6ee7b7; }
            .status-pending { background: #78350f; color: #fde68a; }
            .status-cancelled { background: #374151; color: #9ca3af; }
            .status-refunded { background: #7f1d1d; color: #fca5a5; }
        }
    </style>

    <div class="max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <h1 class="text-2xl font-bold text-gray-800">Order #{{ $order->order_number }}</h1>
            <span class="status-badge status-{{ $order->status }}">{{ ucfirst($order->status) }}</span>
        </div>

        {{-- Action Buttons --}}
        <div class="order-detail-card">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.pos.receipt', $order) }}" target="_blank"
                   class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700">
                    <i class="fas fa-print mr-1"></i> Print Receipt
                </a>

                <a href="{{ route('admin.pos.edit', $order) }}"
                   class="inline-flex items-center px-3 py-2 bg-yellow-500 text-white text-sm font-medium rounded hover:bg-yellow-600">
                    <i class="fas fa-edit mr-1"></i> Edit Order
                </a>

                @if($order->status !== 'cancelled' && $order->status !== 'refunded')
                    <form action="{{ route('admin.pos.cancel', $order) }}" method="POST" class="inline"
                          onsubmit="return confirm('Cancel order #{{ $order->order_number }}? Stock will be restored.')">
                        @csrf
                        <button type="submit"
                                class="inline-flex items-center px-3 py-2 bg-orange-500 text-white text-sm font-medium rounded hover:bg-orange-600">
                            <i class="fas fa-ban mr-1"></i> Cancel Order
                        </button>
                    </form>
                @endif

                <form action="{{ route('admin.pos.delete', $order) }}" method="POST" class="inline"
                      onsubmit="return confirm('DELETE order #{{ $order->order_number }}? This cannot be undone!')">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-sm font-medium rounded hover:bg-red-700">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                </form>

                <a href="{{ route('admin.reports.sales') }}"
                   class="inline-flex items-center px-3 py-2 bg-gray-500 text-white text-sm font-medium rounded hover:bg-gray-600">
                    <i class="fas fa-arrow-left mr-1"></i> Back to Sales
                </a>
            </div>
        </div>

        {{-- Order Info --}}
        <div class="order-detail-card">
            <h2 class="text-lg font-semibold mb-3 text-gray-700">Order Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Customer</label>
                    <p>{{ $order->customer->name ?? 'Walk-in Customer' }}</p>
                </div>
                <div class="info-item">
                    <label>Date</label>
                    <p>{{ $order->created_at->format('d M, Y h:i A') }}</p>
                </div>
                <div class="info-item">
                    <label>Payment Method</label>
                    <p>{{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}</p>
                </div>
                <div class="info-item">
                    <label>Created By</label>
                    <p>{{ $order->user->name ?? 'N/A' }}</p>
                </div>
                @if($order->dispatch_method)
                    <div class="info-item">
                        <label>Dispatch Method</label>
                        <p>{{ ucfirst($order->dispatch_method) }}</p>
                    </div>
                @endif
                @if($order->tracking_id)
                    <div class="info-item">
                        <label>Tracking ID</label>
                        <p>{{ $order->tracking_id }}</p>
                    </div>
                @endif
            </div>
            @if($order->notes)
                <div class="mt-3 info-item">
                    <label>Notes</label>
                    <p>{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Order Items --}}
        <div class="order-detail-card">
            <h2 class="text-lg font-semibold mb-3 text-gray-700">Items</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">#</th>
                        <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-500 uppercase">Qty</th>
                        <th class="px-4 py-2 text-right font-medium text-gray-500 uppercase">Total</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                    @foreach($order->items as $i => $item)
                        <tr>
                            <td class="px-4 py-2">{{ $i + 1 }}</td>
                            <td class="px-4 py-2">{{ $item->product->name ?? 'Deleted Product' }}</td>
                            <td class="px-4 py-2 text-right">Rs. {{ number_format($item->unit_price, 2) }}</td>
                            <td class="px-4 py-2 text-right">{{ $item->quantity }}</td>
                            <td class="px-4 py-2 text-right">Rs. {{ number_format($item->total_price ?? $item->unit_price * $item->quantity, 2) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="mt-4 border-t pt-3 space-y-1 text-sm max-w-xs ml-auto">
                <div class="flex justify-between">
                    <span class="text-gray-600">Subtotal:</span>
                    <span>Rs. {{ number_format($order->subtotal, 2) }}</span>
                </div>
                @if($order->tax > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Tax:</span>
                        <span>Rs. {{ number_format($order->tax, 2) }}</span>
                    </div>
                @endif
                @if($order->discount > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Discount:</span>
                        <span class="text-red-600">- Rs. {{ number_format($order->discount, 2) }}</span>
                    </div>
                @endif
                @if($order->delivery_charges > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Delivery:</span>
                        <span>Rs. {{ number_format($order->delivery_charges, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between font-bold text-base border-t pt-2">
                    <span>Total:</span>
                    <span>Rs. {{ number_format($order->total, 2) }}</span>
                </div>
                @if($order->paid_amount !== null)
                    <div class="flex justify-between">
                        <span class="text-gray-600">Paid:</span>
                        <span>Rs. {{ number_format($order->paid_amount, 2) }}</span>
                    </div>
                @endif
                @if($order->balance_amount > 0)
                    <div class="flex justify-between text-red-600 font-semibold">
                        <span>Balance Due:</span>
                        <span>Rs. {{ number_format($order->balance_amount, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Payments --}}
        @if($order->payments->count() > 0)
            <div class="order-detail-card">
                <h2 class="text-lg font-semibold mb-3 text-gray-700">Payments</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Method</th>
                            <th class="px-4 py-2 text-right font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach($order->payments as $payment)
                            <tr>
                                <td class="px-4 py-2">{{ ucfirst(str_replace('_', ' ', $payment->payment_method ?? $payment->method ?? 'N/A')) }}</td>
                                <td class="px-4 py-2 text-right">Rs. {{ number_format($payment->amount, 2) }}</td>
                                <td class="px-4 py-2">{{ $payment->created_at->format('d M, Y h:i A') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        {{-- Refund Section --}}
        @if($order->isRefundable() && $order->status !== 'cancelled')
            <div class="order-detail-card">
                <h2 class="text-lg font-semibold mb-3 text-gray-700">Process Refund / Return</h2>
                <form action="{{ route('admin.pos.refund', $order) }}" method="POST"
                      onsubmit="return confirm('Process this refund? This action cannot be easily undone.')">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label for="refund_amount" class="block text-sm font-medium text-gray-700 mb-1">Refund Amount</label>
                            <input type="number" name="amount" id="refund_amount"
                                   step="0.01" min="0.01" max="{{ $order->total }}"
                                   value="{{ $order->total }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">Max: Rs. {{ number_format($order->total, 2) }}</p>
                        </div>
                        <div>
                            <label for="refund_reason" class="block text-sm font-medium text-gray-700 mb-1">Reason</label>
                            <input type="text" name="reason" id="refund_reason"
                                   placeholder="e.g. Customer returned goods"
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                   required>
                        </div>
                    </div>
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" name="return_to_inventory" value="1" checked
                                   class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Return items to inventory (restore stock)</span>
                        </label>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded hover:bg-red-700">
                        <i class="fas fa-undo mr-1"></i> Process Refund
                    </button>
                </form>
            </div>
        @endif

        {{-- Refund History --}}
        @if($order->refunds && $order->refunds->count() > 0)
            <div class="order-detail-card">
                <h2 class="text-lg font-semibold mb-3 text-gray-700">Refund History</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Amount</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Reason</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                        @foreach($order->refunds as $refund)
                            <tr>
                                <td class="px-4 py-2 text-red-600 font-medium">Rs. {{ number_format($refund->amount, 2) }}</td>
                                <td class="px-4 py-2">{{ $refund->reason }}</td>
                                <td class="px-4 py-2">{{ $refund->created_at->format('d M, Y h:i A') }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

    </div>
@endsection
