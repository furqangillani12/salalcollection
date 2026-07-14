@extends('shop.layouts.app')
@section('title', 'Your cart')

@section('content')
<section class="py-10 sm:py-14">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="flex items-end justify-between mb-8 reveal">
            <div>
                <h1 class="display text-3xl sm:text-4xl font-bold">Your cart</h1>
                <p class="text-gray-500 text-sm mt-2">{{ $items->count() }} {{ \Str::plural('item', $items->count()) }}</p>
            </div>
            <a href="{{ route('shop.catalog') }}" class="text-sm text-blue-700 hover:underline hidden sm:inline-flex items-center gap-2">
                <i class="fas fa-arrow-left text-xs"></i> Continue shopping
            </a>
        </div>

        @include('shop.partials.notice', ['class' => 'mb-6 reveal'])

        @if ($items->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 p-16 text-center reveal">
                <i class="fas fa-shopping-cart text-5xl text-gray-300 mb-4 block"></i>
                <h2 class="display text-2xl font-bold mb-2">Your cart is empty</h2>
                <p class="text-gray-500 mb-6">Discover something beautiful in our shop.</p>
                <a href="{{ route('shop.catalog') }}" class="btn btn-dark">Start shopping <i class="fas fa-arrow-right text-xs"></i></a>
            </div>
        @else
            <div class="grid lg:grid-cols-[1fr_360px] gap-8">
                <div class="space-y-3 reveal-stagger">
                    @foreach ($items as $item)
                        <div class="bg-white rounded-2xl border border-gray-100 p-4 flex flex-col sm:flex-row gap-4 hover:shadow-md transition">
                            <a href="{{ route('shop.product', $item->product?->slug ?? $item->product?->id) }}" class="flex-shrink-0">
                                <img src="{{ shop_image($item->product?->image) }}" alt="" class="w-full sm:w-28 sm:h-32 object-cover rounded-xl" style="background:#f5f1e8;">
                            </a>
                            <div class="flex-1 min-w-0">
                                <a href="{{ route('shop.product', $item->product?->slug ?? $item->product?->id) }}" class="font-bold text-gray-900 hover:text-blue-700 transition block">{{ $item->product?->name ?? 'Product' }}</a>
                                @if ($item->product?->brand)
                                    <div class="text-[10px] uppercase tracking-widest text-gray-400 mt-0.5">{{ $item->product->brand->name }}</div>
                                @endif
                                <div class="text-sm font-bold mt-2" style="color:var(--brand-cyan);">{{ shop_price($item->unit_price) }}</div>
                                <div class="flex flex-wrap items-center gap-3 mt-3">
                                    <form method="POST" action="{{ route('shop.cart.update', $item) }}" class="flex items-center gap-1 bg-gray-100 rounded-lg overflow-hidden">
                                        @csrf
                                        <button type="submit" name="qty" value="{{ max(1, $item->qty - 1) }}" class="px-3 py-1.5 text-gray-600 hover:text-gray-900"><i class="fas fa-minus text-[10px]"></i></button>
                                        <span class="px-3 text-sm font-bold">{{ (int) $item->qty }}</span>
                                        <button type="submit" name="qty" value="{{ $item->qty + 1 }}" class="px-3 py-1.5 text-gray-600 hover:text-gray-900"><i class="fas fa-plus text-[10px]"></i></button>
                                    </form>
                                    <form method="POST" action="{{ route('shop.cart.remove', $item) }}">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-500 hover:underline"><i class="fas fa-trash text-[10px] mr-1"></i> Remove</button>
                                    </form>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs text-gray-500">Line total</div>
                                <div class="text-lg font-extrabold" style="color:var(--brand-navy);">{{ shop_price($item->qty * $item->unit_price) }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Summary --}}
                <aside class="lg:sticky lg:top-24 lg:self-start reveal">
                    <div class="bg-white rounded-2xl border border-gray-100 p-6">
                        <h2 class="font-bold text-gray-900 mb-4">Order summary</h2>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-semibold">{{ shop_price($totals['subtotal']) }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">Discount</span><span class="font-semibold {{ $totals['discount'] > 0 ? 'text-emerald-600' : '' }}">-{{ shop_price($totals['discount']) }}</span></div>
                            @if (($totals['tax'] ?? 0) > 0)
                                <div class="flex justify-between"><span class="text-gray-500">Tax @if ($totals['tax_type'] === 'percent')({{ rtrim(rtrim(number_format($totals['tax_rate'],2),'0'),'.') }}%)@endif</span><span class="font-semibold">{{ shop_price($totals['tax']) }}</span></div>
                            @endif
                            <div class="flex justify-between text-xs text-gray-500 italic"><span>Delivery</span><span>calculated at checkout</span></div>
                        </div>
                        <hr class="my-4 border-gray-100">
                        <div class="flex items-baseline justify-between">
                            <span class="font-bold">Total</span>
                            <span class="text-2xl font-extrabold" style="color:var(--brand-navy);">{{ shop_price($totals['total']) }}</span>
                        </div>

                        <a href="{{ route('shop.checkout') }}" class="btn btn-primary btn-block mt-5">Proceed to checkout <i class="fas fa-arrow-right text-xs"></i></a>

                        {{-- Coupon --}}
                        <div class="mt-5 pt-5 border-t border-gray-100">
                            @if ($coupon)
                                <div class="bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2 flex items-center justify-between">
                                    <div class="text-xs">
                                        <div class="font-bold text-emerald-700">{{ $coupon->code }}</div>
                                        <div class="text-emerald-600">applied</div>
                                    </div>
                                    <form method="POST" action="{{ route('shop.cart.coupon.remove') }}">@csrf @method('DELETE')<button class="text-red-500 text-xs"><i class="fas fa-times"></i></button></form>
                                </div>
                            @else
                                <form method="POST" action="{{ route('shop.cart.coupon') }}" class="flex gap-2">
                                    @csrf
                                    <input type="text" name="code" placeholder="Coupon code" class="flex-1 px-3 py-2 border border-gray-200 rounded-lg text-sm">
                                    <button class="btn btn-ghost !py-2 !px-4 !text-xs">Apply</button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-center gap-4 text-xs text-gray-400">
                        <span><i class="fas fa-shield-halved"></i> Secure</span>
                        <span><i class="fas fa-lock"></i> Encrypted</span>
                    </div>
                </aside>
            </div>
        @endif
    </div>
</section>
@endsection
