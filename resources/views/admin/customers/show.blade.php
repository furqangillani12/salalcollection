@extends('layouts.admin')

@section('content')
    <div class="p-6 bg-white rounded-lg shadow-md">
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Customer Details</h1>
                <p class="text-sm text-gray-600 mt-1">Complete customer information</p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.customers.edit', $customer) }}"
                    class="px-4 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600 flex items-center">
                    <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                    </svg>
                    Edit
                </a>
                <a href="{{ route('admin.customers.index') }}"
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

        <!-- Customer Information Card -->
        <div class="bg-gray-50 rounded-lg border border-gray-200 overflow-hidden mb-6">
            <div class="px-6 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b">
                <div class="flex flex-wrap justify-between items-center gap-3">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">{{ $customer->name }}</h2>
                        <p class="text-sm text-gray-600">
                            {{ $customer->type_label }}
                            @if ($customer->barcode)
                                • Barcode: <span
                                    class="font-mono bg-blue-100 text-blue-800 px-2 py-0.5 rounded">{{ $customer->barcode }}</span>
                            @endif
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-blue-600">🏆 {{ $customer->loyalty_points }}</div>
                        <div class="text-xs text-gray-500">Loyalty Points</div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Contact Information -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Contact Information</h3>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Email</label>
                            <p class="mt-1 text-gray-900">
                                @if ($customer->email)
                                    <a href="mailto:{{ $customer->email }}" class="text-blue-600 hover:underline">
                                        {{ $customer->email }}
                                    </a>
                                @else
                                    <span class="text-gray-400">Not provided</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Phone</label>
                            <p class="mt-1 text-gray-900">
                                @if ($customer->phone)
                                    <a href="tel:{{ $customer->phone }}" class="text-blue-600 hover:underline">
                                        {{ $customer->phone }}
                                    </a>
                                @else
                                    <span class="text-gray-400">Not provided</span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Address</label>
                            <p class="mt-1 text-gray-900">
                                {{ $customer->address ?? 'Not provided' }}
                            </p>
                        </div>
                    </div>

                    <!-- Customer Stats -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-800 border-b pb-2">Customer Information</h3>

                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white p-4 rounded-lg border text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $customer->orders_count ?? 0 }}</div>
                                <div class="text-xs text-gray-500 mt-1">Total Orders</div>
                            </div>

                            <div class="bg-white p-4 rounded-lg border text-center">
                                <div class="text-2xl font-bold text-green-600">
                                    @if ($customer->customer_type === 'reseller')
                                        🏪
                                    @elseif($customer->customer_type === 'wholesale')
                                        🏭
                                    @else
                                        🛒
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500 mt-1">Customer Type</div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Created</label>
                            <p class="mt-1 text-gray-900">
                                {{ $customer->created_at->format('F d, Y h:i A') }}
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-500">Last Updated</label>
                            <p class="mt-1 text-gray-900">
                                {{ $customer->updated_at->format('F d, Y h:i A') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reward Points -->
        <div class="bg-white rounded-lg border border-gray-200 mb-6">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-800"><i class="fas fa-star text-amber-500 mr-1"></i> Reward Points</h3>
                <span class="text-2xl font-bold text-amber-600">🏆 {{ $customer->loyalty_points ?? 0 }}</span>
            </div>
            <div class="p-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Award / adjust --}}
                <form method="POST" action="{{ route('admin.customers.award-points', $customer) }}" class="space-y-3">
                    @csrf
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Points (+/−)</label>
                            <input type="number" name="points" required placeholder="e.g. 50" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-600 mb-1">Type</label>
                            <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="review_photo">Photo review bonus</option>
                                <option value="review_video">Video review bonus</option>
                                <option value="review_social">Social media review</option>
                                <option value="adjust">Manual adjustment</option>
                                <option value="redeem">Redeem (use negative)</option>
                            </select>
                        </div>
                    </div>
                    <input type="text" name="note" placeholder="Note (optional)" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <button class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg text-sm font-semibold"><i class="fas fa-plus mr-1"></i> Record points</button>
                </form>

                {{-- History --}}
                <div>
                    <div class="text-xs font-semibold text-gray-500 uppercase mb-2">Recent history</div>
                    @forelse ($customer->pointTransactions as $txn)
                        <div class="flex items-center justify-between py-1.5 border-b border-gray-100 text-sm">
                            <div>
                                <span class="font-medium">{{ $txn->label }}</span>
                                @if ($txn->note)<span class="text-xs text-gray-400">· {{ $txn->note }}</span>@endif
                                <div class="text-[11px] text-gray-400">{{ $txn->created_at->format('d M Y') }}</div>
                            </div>
                            <span class="font-bold {{ $txn->points >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $txn->points >= 0 ? '+' : '' }}{{ $txn->points }}</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-400">No points activity yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Customer Orders -->
        <div class="bg-white rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-medium text-gray-800">Recent Orders</h3>
            </div>
            <div class="p-6">
                @if ($customer->orders && $customer->orders->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Order #</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Date</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Total</th>
                                    <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customer->orders->take(5) as $order)
                                    <tr>
                                        <td class="px-4 py-2 text-sm text-gray-900">#{{ $order->id }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">
                                            {{ $order->created_at->format('Y-m-d') }}</td>
                                        <td class="px-4 py-2 text-sm text-gray-900">Rs
                                            {{ number_format($order->total_amount, 2) }}</td>
                                        <td class="px-4 py-2">
                                            <span
                                                class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                Completed
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if ($customer->orders->count() > 5)
                        <div class="mt-4 text-center">
                            <a href="#" class="text-blue-600 hover:underline text-sm">
                                View all {{ $customer->orders->count() }} orders →
                            </a>
                        </div>
                    @endif
                @else
                    <div class="text-center py-8 text-gray-500">
                        <p>No orders yet for this customer</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
