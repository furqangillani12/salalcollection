@extends('shop.layouts.app')
@section('title', 'Order ' . $order->order_number)
@section('content')
<section class="py-10 sm:py-14">
    <div class="max-w-4xl mx-auto px-4 reveal">
        <a href="{{ route('shop.account.orders') }}" class="text-xs text-gray-500 hover:text-blue-700 inline-flex items-center gap-2 mb-4"><i class="fas fa-arrow-left"></i> Back to my orders</a>

        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6">
            <div>
                <span class="text-xs uppercase tracking-widest" style="color:var(--brand-cyan);">Order</span>
                <h1 class="display text-3xl font-bold mt-1">{{ $order->order_number }}</h1>
                <p class="text-gray-500 text-sm mt-1">Placed {{ $order->created_at->format('d M Y · h:i A') }}</p>
            </div>
            @php $sm = order_status_meta($order->status); @endphp
            <span class="chip self-start" style="background:{{ $sm['bg'] }};color:{{ $sm['text'] }};font-size:13px;padding:6px 14px;">{{ $sm['label'] }}</span>
        </div>

        {{-- Status timeline --}}
        @php
            $timeline   = config('order_flow.timeline');
            $statuses   = config('order_flow.statuses');
            $current    = order_status_norm($order->status);
            $terminal   = in_array($current, config('order_flow.terminal'), true);
            $currentIdx = array_search($current, $timeline);
        @endphp

        <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6 reveal {{ $terminal ? 'opacity-60' : '' }}">
            @if ($terminal)
                <div class="mb-3 text-sm font-semibold" style="color:{{ $sm['text'] }};"><i class="fas {{ $sm['icon'] }} mr-1"></i> This order is {{ $sm['label'] }}.</div>
            @endif
            <div class="grid grid-cols-5 gap-2">
                @foreach ($timeline as $idx => $key)
                    @php $meta = $statuses[$key]; $done = !$terminal && $currentIdx !== false && $idx <= $currentIdx; @endphp
                    <div class="text-center">
                        <div class="w-10 h-10 rounded-full mx-auto flex items-center justify-center text-sm transition"
                             style="background:{{ $done ? 'var(--brand-cyan)' : '#e5e7eb' }};color:{{ $done ? 'white' : '#9ca3af' }};">
                            <i class="fas {{ $meta['icon'] }}"></i>
                        </div>
                        <div class="text-[11px] mt-1.5 font-semibold" style="color:{{ $done ? 'var(--brand-navy)' : '#9ca3af' }};">{{ $meta['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Customer can confirm receipt of a parcel that's on its way --}}
        @if (in_array($order->status, ['dispatched', 'shipped'], true))
            <form method="POST" action="{{ route('shop.account.order.delivered', $order) }}" class="mb-6 reveal"
                  onsubmit="return confirm('Confirm that you have received this parcel?');">
                @csrf
                <button class="btn btn-primary btn-block !py-2"><i class="fas fa-box-open"></i> I've received it — mark as delivered</button>
            </form>
        @endif

        @include('shop.partials.tracking-history')

        @include('shop.partials.dispatch-media')

        {{-- Items + summary --}}
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
                        @if (($order->points_discount ?? 0) > 0)
                            <div class="flex justify-between text-amber-600"><span><i class="fas fa-star text-[11px]"></i> Points ({{ (int) $order->points_redeemed }})</span><span>-{{ shop_price($order->points_discount) }}</span></div>
                        @endif
                        <div class="flex justify-between"><span class="text-gray-500">Delivery</span><span class="font-semibold">{{ shop_price($order->delivery_charges ?? 0) }}</span></div>
                    </div>
                    <hr class="my-3 border-gray-100">
                    <div class="flex justify-between items-baseline"><span class="font-bold">Total</span><span class="text-lg font-extrabold" style="color:var(--brand-navy);">{{ shop_price($order->total) }}</span></div>

                    {{-- Previous khata + what's left to clear (logged-in customers) --}}
                    @if (($order->previous_balance ?? 0) > 0)
                        <div class="mt-3 rounded-lg bg-amber-50 border border-amber-100 p-3 text-sm">
                            <div class="flex justify-between"><span class="text-amber-800">Previous balance (khata)</span><span class="font-semibold">{{ shop_price($order->previous_balance) }}</span></div>
                            <div class="flex justify-between mt-1"><span class="text-amber-800">This order</span><span class="font-semibold">{{ shop_price($order->total) }}</span></div>
                            <div class="flex justify-between mt-1 pt-1 border-t border-amber-200 font-bold text-amber-900"><span>Total to clear</span><span>{{ shop_price(($order->previous_balance ?? 0) + ($order->balance_amount ?? 0)) }}</span></div>
                        </div>
                    @endif
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
                    @if ($order->tracking_id)
                        @php $courierUrl = courier_track_url($order->dispatch_method, $order->tracking_id); @endphp
                        <div class="mt-2 text-xs">
                            <span class="text-gray-500">Tracking:</span> <span class="font-semibold text-gray-800">{{ $order->tracking_id }}</span>
                            @if ($courierUrl)
                                <a href="{{ $courierUrl }}" target="_blank" rel="noopener" class="ml-1 font-semibold" style="color:var(--brand-cyan);">Track <i class="fas fa-arrow-up-right-from-square text-[9px]"></i></a>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="bg-white rounded-2xl border border-gray-100 p-5">
                    <h3 class="font-bold text-gray-800 mb-3">Payment</h3>
                    <div class="text-sm capitalize">{{ str_replace('_', ' ', $order->payment_method) }}</div>
                    <div class="text-xs text-gray-500 mt-1 capitalize">{{ str_replace('_', ' ', $order->online_payment_status ?: $order->payment_status) }}</div>

                    @php
                        $isPaid = in_array($order->online_payment_status, ['paid', 'bank_paid'], true) || $order->payment_status === 'paid';
                    @endphp
                    @if ($isPaid)
                        <div class="mt-3 text-xs text-emerald-700 bg-emerald-50 rounded-lg px-3 py-2"><i class="fas fa-check-circle"></i> Payment received — thank you!</div>
                    @elseif ($order->payment_proof_path)
                        <div class="mt-3 text-xs text-amber-700 bg-amber-50 rounded-lg px-3 py-2"><i class="fas fa-clock"></i> Payment proof submitted. We'll verify it shortly.</div>
                    @endif

                    {{-- Uploaded payment proof — viewable + downloadable by the customer --}}
                    @if ($order->payment_proof_path)
                        <div class="mt-3">
                            <div class="text-xs text-gray-500 mb-1"><i class="fas fa-receipt"></i> Your payment proof</div>
                            <a href="{{ asset('storage/'.$order->payment_proof_path) }}" target="_blank" rel="noopener" class="block">
                                <img src="{{ asset('storage/'.$order->payment_proof_path) }}" alt="Payment proof" class="w-full rounded-lg border border-gray-200" style="max-width:220px;">
                            </a>
                            <div class="flex items-center gap-3 mt-2">
                                <a href="{{ asset('storage/'.$order->payment_proof_path) }}" target="_blank" rel="noopener" class="inline-flex items-center gap-1 text-xs font-semibold" style="color:var(--brand-cyan);"><i class="fas fa-up-right-from-square text-[11px]"></i> View full</a>
                                <a href="{{ asset('storage/'.$order->payment_proof_path) }}" download class="inline-flex items-center gap-1 text-xs font-semibold" style="color:var(--brand-cyan);"><i class="fas fa-download text-[11px]"></i> Download</a>
                            </div>
                            @if ($order->payment_sender_amount)
                                <div class="text-xs text-gray-500 mt-1">Amount sent: {{ shop_price($order->payment_sender_amount) }}</div>
                            @endif
                        </div>
                    @endif

                    {{-- Attach / re-attach proof for an unpaid bank order --}}
                    @if ($order->balance_amount > 0 && $order->online_payment_status !== 'cod' && !in_array($order->status, ['cancelled','returned']))
                        <div x-data="{ open: {{ $order->payment_proof_path ? 'false' : 'true' }} }" class="mt-4 pt-4 border-t border-gray-100">
                            <button type="button" @click="open=!open" class="text-sm font-semibold" style="color:var(--brand-cyan);">
                                <i class="fas fa-receipt"></i> {{ $order->payment_proof_path ? 'Update payment proof' : 'I have paid — attach proof' }}
                            </button>
                            <form x-show="open" x-cloak method="POST" action="{{ route('shop.account.order.proof', $order) }}" enctype="multipart/form-data" class="mt-3 space-y-2">
                                @csrf
                                <input type="text" name="payment_sender_name" value="{{ $order->payment_sender_name }}" placeholder="Account title you sent from" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                                <input type="text" name="payment_sender_bank" value="{{ $order->payment_sender_bank }}" placeholder="Bank / wallet" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                                <input type="number" step="0.01" name="payment_sender_amount" value="{{ $order->payment_sender_amount }}" placeholder="Amount sent (Rs.)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                                <input type="file" name="payment_proof" accept="image/*" required class="w-full text-sm text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700">
                                <button class="btn btn-primary btn-block !py-2 !text-sm"><i class="fas fa-upload"></i> Submit proof</button>
                            </form>
                        </div>
                    @endif
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
