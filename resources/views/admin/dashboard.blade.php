@extends('layouts.admin')
@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Stat cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $cards = [
                ['Total Orders', number_format($stats['orders_total']), 'fa-bag-shopping', 'linear-gradient(135deg,#0f6b58,#14846d)'],
                ['Pending Orders', number_format($stats['orders_pending']), 'fa-clock', 'linear-gradient(135deg,#c9a227,#e0b93a)'],
                ['Revenue (all)', 'Rs. '.number_format($stats['revenue'], 0), 'fa-coins', 'linear-gradient(135deg,#0b3a30,#0f6b58)'],
                ['Revenue (month)', 'Rs. '.number_format($stats['revenue_month'], 0), 'fa-chart-line', 'linear-gradient(135deg,#14846d,#1aa07f)'],
            ];
        @endphp
        @foreach ($cards as [$label, $value, $icon, $grad])
            <div class="rounded-2xl p-5 text-white shadow-sm" style="background:{{ $grad }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold uppercase tracking-wide opacity-90">{{ $label }}</span>
                    <i class="fas {{ $icon }} opacity-80"></i>
                </div>
                <div class="text-2xl font-extrabold mt-3">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    {{-- Secondary stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $mini = [
                ['Orders Today', number_format($stats['orders_today']), 'fa-calendar-day', route('admin.online-orders.index')],
                ['Products (live/total)', number_format($stats['products_live']).' / '.number_format($stats['products']), 'fa-box', route('products.index')],
                ['Customers', number_format($stats['customers']), 'fa-users', route('admin.customers.index')],
                ['Pending Reviews', number_format($stats['reviews_pending']), 'fa-star', route('admin.reviews.index')],
            ];
        @endphp
        @foreach ($mini as [$label, $value, $icon, $url])
            <a href="{{ $url }}" class="rounded-2xl p-5 bg-white border border-gray-100 hover:border-green-300 hover:shadow-sm transition">
                <div class="flex items-center gap-2 text-green-700"><i class="fas {{ $icon }}"></i><span class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $label }}</span></div>
                <div class="text-xl font-extrabold text-gray-800 mt-2">{{ $value }}</div>
            </a>
        @endforeach
    </div>

    {{-- Recent orders --}}
    <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-bold text-gray-800"><i class="fas fa-bag-shopping text-green-600 mr-2"></i> Recent Orders</h2>
            <a href="{{ route('admin.online-orders.index') }}" class="text-sm font-semibold text-green-700 hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-5 py-3">Order</th>
                        <th class="text-left px-5 py-3">Customer</th>
                        <th class="text-left px-5 py-3">Status</th>
                        <th class="text-right px-5 py-3">Total</th>
                        <th class="text-right px-5 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($recentOrders as $o)
                        <tr class="hover:bg-green-50/40">
                            <td class="px-5 py-3 font-semibold"><a href="{{ route('admin.online-orders.show', $o) }}" class="text-green-700 hover:underline">{{ $o->order_number }}</a></td>
                            <td class="px-5 py-3">{{ trim(($o->shipping_first_name ?? '').' '.($o->shipping_last_name ?? '')) ?: ($o->customer?->name ?? $o->customer_email ?? '—') }}</td>
                            <td class="px-5 py-3"><span class="inline-block px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700 capitalize">{{ str_replace('_',' ',$o->status) }}</span></td>
                            <td class="px-5 py-3 text-right font-bold text-gray-800">Rs. {{ number_format((float) $o->total, 0) }}</td>
                            <td class="px-5 py-3 text-right text-gray-500">{{ $o->created_at?->format('d M Y') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-gray-400">No orders yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
