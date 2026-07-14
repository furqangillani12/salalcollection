@extends('layouts.admin')
@section('title', 'Online Orders')

@section('content')
<div class="p-3 sm:p-6">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-bag-shopping text-cyan-600"></i> Online Orders
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">All orders placed through the SALAL COLLECTION storefront.</p>
        </div>
        <a href="{{ url(env('SHOP_PREFIX', 'shop')) }}" target="_blank"
           class="inline-flex items-center gap-2 px-4 py-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 rounded-lg text-sm font-semibold shadow-sm">
            <i class="fas fa-external-link-alt"></i> View site
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-emerald-50 text-emerald-800 rounded-lg border border-emerald-200 text-sm">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Stats row --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="text-[11px] uppercase tracking-wide text-gray-500 font-semibold">All orders</div>
            <div class="text-2xl font-extrabold text-gray-800 mt-1">{{ $stats['all'] }}</div>
        </div>
        <div class="bg-white rounded-xl border border-amber-200 p-4 shadow-sm">
            <div class="text-[11px] uppercase tracking-wide text-amber-700 font-semibold">Pending</div>
            <div class="text-2xl font-extrabold text-amber-700 mt-1">{{ $stats['pending'] }}</div>
        </div>
        <div class="bg-white rounded-xl border border-emerald-200 p-4 shadow-sm">
            <div class="text-[11px] uppercase tracking-wide text-emerald-700 font-semibold">Delivered</div>
            <div class="text-2xl font-extrabold text-emerald-700 mt-1">{{ $stats['delivered'] }}</div>
        </div>
        <div class="bg-white rounded-xl border border-cyan-200 p-4 shadow-sm">
            <div class="text-[11px] uppercase tracking-wide text-cyan-700 font-semibold">Revenue</div>
            <div class="text-2xl font-extrabold text-cyan-700 mt-1">Rs. {{ number_format($stats['revenue'], 0) }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-4">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search order #, customer name, email, phone..."
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <div class="sm:col-span-2">
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">All statuses</option>
                    @foreach (config('order_flow.statuses') as $s => $meta)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ $meta['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <select name="online_payment_status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">All payments</option>
                    <option value="cod"          @selected(request('online_payment_status') === 'cod')>COD</option>
                    <option value="bank_pending" @selected(request('online_payment_status') === 'bank_pending')>Bank pending</option>
                    <option value="bank_paid"    @selected(request('online_payment_status') === 'bank_paid')>Bank paid</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <input type="date" name="from" value="{{ request('from') }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div class="sm:col-span-2 flex gap-2">
                <button class="flex-1 px-4 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold rounded-lg"><i class="fas fa-filter mr-1"></i> Filter</button>
                @if (request()->hasAny(['search','status','online_payment_status','from','to']))
                    <a href="{{ route('admin.online-orders.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg" title="Clear"><i class="fas fa-times"></i></a>
                @endif
            </div>
        </div>
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr class="text-left text-[11px] uppercase tracking-wide text-gray-600">
                        <th class="px-4 py-3">Order</th>
                        <th class="px-4 py-3">Customer</th>
                        <th class="px-4 py-3">Items</th>
                        <th class="px-4 py-3">Payment</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $o)
                        @php $sc = order_status_meta($o->status); @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.online-orders.show', $o) }}" class="font-mono text-xs font-semibold text-cyan-700 hover:underline">{{ $o->order_number }}</a>
                                <div class="text-[11px] text-gray-500 mt-0.5">{{ $o->created_at->format('d M Y · h:i A') }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="font-semibold text-gray-800 text-sm">
                                    {{ $o->shipping_first_name }} {{ $o->shipping_last_name }}
                                    @if (!$o->shipping_first_name && $o->customer)
                                        {{ $o->customer->name }}
                                    @endif
                                </div>
                                <div class="text-[11px] text-gray-500">
                                    {{ $o->customer_email ?? $o->customer?->email }}
                                    @if ($o->shipping_phone) · {{ $o->shipping_phone }} @endif
                                </div>
                                @if (!$o->customer_id)
                                    <span class="inline-block mt-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">GUEST</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-600">{{ $o->items_count }} item(s)</td>
                            <td class="px-4 py-3">
                                <div class="text-xs capitalize">{{ str_replace('_',' ', $o->payment_method) }}</div>
                                <div class="text-[11px] text-gray-500 capitalize">{{ str_replace('_',' ', $o->online_payment_status ?? $o->payment_status) }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold"
                                      style="background:{{ $sc['bg'] }};color:{{ $sc['text'] }};">{{ $sc['label'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-right font-bold whitespace-nowrap">Rs. {{ number_format($o->total, 0) }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.online-orders.show', $o) }}" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-md text-xs font-medium border border-blue-200" title="Manage"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400">
                            <i class="fas fa-bag-shopping text-3xl mb-2 block"></i> No online orders yet.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
