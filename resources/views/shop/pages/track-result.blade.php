@extends('shop.layouts.app')
@section('title', 'Order ' . $order->order_number)
@section('content')
<section class="py-10 sm:py-14">
    <div class="max-w-4xl mx-auto px-4 reveal">
        <a href="{{ route('shop.track') }}" class="text-xs text-gray-500 hover:text-blue-700 inline-flex items-center gap-2 mb-4"><i class="fas fa-arrow-left"></i> Track another order</a>

        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6">
            <div>
                <span class="text-xs uppercase tracking-widest" style="color:var(--brand-cyan);">Order</span>
                <h1 class="display text-3xl font-bold mt-1">{{ $order->order_number }}</h1>
                <p class="text-gray-500 text-sm mt-1">Placed {{ $order->created_at->format('d M Y · h:i A') }}</p>
            </div>
            @php $sm = order_status_meta($order->status); @endphp
            <span class="chip self-start" style="background:{{ $sm['bg'] }};color:{{ $sm['text'] }};font-size:13px;padding:6px 14px;">{{ $sm['label'] }}</span>
        </div>

        @php
            $timeline   = config('order_flow.timeline');
            $statuses   = config('order_flow.statuses');
            $current    = order_status_norm($order->status);
            $terminal   = in_array($current, config('order_flow.terminal'), true);
            $currentIdx = array_search($current, $timeline);
            $courierUrl = courier_track_url($order->dispatch_method, $order->tracking_id);
        @endphp

        @if (in_array($current, ['cancelled','returned']))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-2xl p-5 mb-6 reveal">
                <i class="fas fa-circle-xmark mr-2"></i> This order was {{ $sm['label'] }}. If you think this is a mistake, please contact us.
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6 reveal">
                <div class="grid grid-cols-5 gap-2">
                    @foreach ($timeline as $idx => $key)
                        @php $meta = $statuses[$key]; $done = $currentIdx !== false && $idx <= $currentIdx; @endphp
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full mx-auto flex items-center justify-center text-sm transition"
                                 style="background:{{ $done ? 'var(--brand-cyan)' : '#e5e7eb' }};color:{{ $done ? 'white' : '#9ca3af' }};">
                                <i class="fas {{ $meta['icon'] }}"></i>
                            </div>
                            <div class="text-[11px] mt-1.5 font-semibold" style="color:{{ $done ? 'var(--brand-navy)' : '#9ca3af' }};">{{ $meta['label'] }}</div>
                        </div>
                    @endforeach
                </div>

                @if ($order->tracking_id)
                    <div class="mt-5 pt-5 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <div class="text-xs text-gray-500">Tracking number ({{ $order->dispatch_method }})</div>
                            <div class="font-bold text-gray-800 tracking-wide">{{ $order->tracking_id }}</div>
                        </div>
                        @if ($courierUrl)
                            <a href="{{ $courierUrl }}" target="_blank" rel="noopener" class="btn btn-primary !py-2 !text-xs w-max">
                                <i class="fas fa-location-arrow"></i> Track on courier site
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        @include('shop.partials.tracking-history')

        @include('shop.partials.dispatch-media')

        <div class="grid lg:grid-cols-[1fr_320px] gap-6 reveal">
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-5 py-4 border-b border-gray-100 bg-gray-50"><h2 class="font-bold text-gray-800">Items</h2></div>
                <div class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <div class="p-4 flex gap-3">
                            <img src="{{ shop_image($item->product?->image) }}" class="w-16 h-20 rounded-lg object-cover" style="background:#f5f1e8;">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-800 truncate">{{ $item->product?->name ?? 'Product' }}</div>
                                <div class="text-xs text-gray-500 mt-0.5">Qty {{ (int) $item->quantity }} × {{ shop_price($item->unit_price) }}</div>
                            </div>
                            <div class="text-sm font-bold whitespace-nowrap" style="color:var(--brand-navy);">{{ shop_price($item->total_price) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-800 mb-3">Summary</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-semibold">{{ shop_price($order->subtotal) }}</span></div>
                        @if ($order->coupon_discount > 0)
                            <div class="flex justify-between text-emerald-600"><span>Coupon</span><span>-{{ shop_price($order->coupon_discount) }}</span></div>
                        @endif
                        <div class="flex justify-between"><span class="text-gray-500">Delivery</span><span class="font-semibold">{{ shop_price($order->delivery_charges ?? 0) }}</span></div>
                    </div>
                    <hr class="my-3 border-gray-100">
                    <div class="flex justify-between items-baseline"><span class="font-bold">Total</span><span class="text-lg font-extrabold" style="color:var(--brand-navy);">{{ shop_price($order->total) }}</span></div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-800 mb-3">Shipping</h3>
                    <div class="text-sm text-gray-700 leading-relaxed">
                        {{ $order->shipping_first_name }} {{ $order->shipping_last_name }}<br>
                        {{ $order->shipping_address1 }}<br>
                        @if ($order->shipping_address2){{ $order->shipping_address2 }}<br>@endif
                        {{ $order->shipping_city }}@if ($order->shipping_post_code), {{ $order->shipping_post_code }}@endif<br>
                        <span class="text-gray-500">{{ $order->shipping_phone }}</span>
                    </div>
                    <div class="text-xs text-gray-500 mt-3"><i class="fas fa-truck"></i> {{ $order->dispatch_method }}</div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-800 mb-3">Payment</h3>
                    <div class="text-sm capitalize">{{ str_replace('_', ' ', $order->payment_method) }}</div>
                    <div class="text-xs text-gray-500 mt-1 capitalize">{{ str_replace('_', ' ', $order->payment_status) }}</div>
                </div>

                @php $waOrder = wa_link(shop_whatsapp_number(), 'Assalam-o-Alaikum! I would like an update on my order ' . $order->order_number . '.'); @endphp
                @if ($waOrder)
                    <a href="{{ $waOrder }}" target="_blank" rel="noopener" class="btn btn-block !text-sm" style="background:#25D366;color:#fff;">
                        <i class="fab fa-whatsapp"></i> Message us about this order
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection
