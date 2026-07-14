@extends('shop.layouts.app')
@section('title', 'Order placed!')
@section('content')
<section class="py-20 sm:py-28 text-center">
    <div class="max-w-xl mx-auto px-4 reveal">
        <div class="w-20 h-20 mx-auto rounded-full flex items-center justify-center mb-6 shadow-xl"
             style="background:linear-gradient(135deg,#10b981,#059669);color:#fff;">
            <i class="fas fa-check text-3xl"></i>
        </div>
        <h1 class="display text-4xl sm:text-5xl font-bold mb-3">Thank you!</h1>
        <p class="text-gray-600 text-lg">Your order <span class="font-bold" style="color:var(--brand-navy);">#{{ $order->order_number }}</span> has been placed.</p>
        <p class="text-sm text-gray-500 mt-2">We've sent a confirmation to <strong>{{ $order->customer_email ?? '—' }}</strong>.</p>

        <div class="mt-10 bg-white border border-gray-100 rounded-2xl p-6 text-left">
            <div class="flex items-center justify-between mb-4">
                <span class="font-bold">Order total</span>
                <span class="text-2xl font-extrabold" style="color:var(--brand-navy);">{{ shop_price($order->total) }}</span>
            </div>
            <div class="text-sm text-gray-600 space-y-1.5">
                <div class="flex justify-between"><span>Subtotal</span><span>{{ shop_price($order->subtotal) }}</span></div>
                @if ($order->coupon_discount > 0)
                    <div class="flex justify-between text-emerald-600"><span>Coupon</span><span>-{{ shop_price($order->coupon_discount) }}</span></div>
                @endif
                <div class="flex justify-between"><span>Delivery</span><span>{{ shop_price($order->delivery_charges ?? 0) }}</span></div>
                <div class="flex justify-between"><span>Payment</span><span class="capitalize">{{ str_replace('_', ' ', $order->payment_method) }}</span></div>
                <div class="flex justify-between"><span>Dispatch</span><span>{{ $order->dispatch_method }}</span></div>
            </div>
        </div>

        @php $waOrder = wa_link(shop_whatsapp_number(), 'Hi, I just placed order ' . $order->order_number . '. I have a question about it.'); @endphp

        @auth('customer')
            <div class="flex flex-wrap justify-center gap-3 mt-8">
                <a href="{{ route('shop.account.order', $order) }}" class="btn btn-dark"><i class="fas fa-receipt"></i> View order</a>
                @if ($waOrder)
                    <a href="{{ $waOrder }}" target="_blank" rel="noopener" class="btn" style="background:#25D366;color:#fff;"><i class="fab fa-whatsapp"></i> WhatsApp us</a>
                @endif
                <a href="{{ route('shop.catalog') }}" class="btn btn-ghost">Continue shopping</a>
            </div>
        @else
            <div class="mt-8 bg-amber-50 border border-amber-200 rounded-2xl p-5 text-left">
                <div class="flex items-start gap-3">
                    <i class="fas fa-link text-amber-600 mt-1"></i>
                    <div class="flex-1">
                        <div class="font-bold text-gray-800 text-sm">Save this link to track your order any time</div>
                        <div class="text-xs text-gray-600 mt-1 break-all">{{ route('shop.track.view', $order->receipt_token) }}</div>
                        <div class="flex flex-wrap gap-2 mt-3">
                            <a href="{{ route('shop.track.view', $order->receipt_token) }}" class="btn btn-dark !text-xs"><i class="fas fa-truck"></i> Track this order</a>
                            @if ($waOrder)
                                <a href="{{ $waOrder }}" target="_blank" rel="noopener" class="btn !text-xs" style="background:#25D366;color:#fff;"><i class="fab fa-whatsapp"></i> WhatsApp us</a>
                            @endif
                            <a href="{{ route('shop.track') }}" class="btn btn-ghost !text-xs">Track another order</a>
                            <a href="{{ route('shop.catalog') }}" class="btn btn-ghost !text-xs">Continue shopping</a>
                        </div>
                    </div>
                </div>
            </div>
        @endauth
    </div>
</section>
@endsection
