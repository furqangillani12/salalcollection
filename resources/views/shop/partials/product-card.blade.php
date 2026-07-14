@php
    /** @var \App\Models\Product $product */
    $price     = shop_product_price($product);
    $strike    = shop_strike_price($product);
    $hasSale   = $strike !== null;
    $cover     = shop_image($product->image);
    $badge     = $hasSale ? 'sale' : ($product->condition_label ?? 'default');
    $inWishlist = auth('customer')->check()
        && $product->wishlists()->where('customer_id', auth('customer')->id())->exists();
@endphp

<article class="product-card group">
    <a href="{{ route('shop.product', $product->slug ?? $product->id) }}" class="img-wrap block">
        <img src="{{ $cover }}" alt="{{ $product->name }}" loading="lazy">

        @if ($badge !== 'default')
            <span class="chip absolute top-3 left-3 z-10"
                  style="background:{{ $badge === 'sale' ? '#fee2e2' : ($badge === 'new' ? '#e8f1fb' : '#cfeefb') }};
                         color:{{ $badge === 'sale' ? '#b91c1c' : ($badge === 'new' ? '#0e7490' : '#92400e') }};">
                {{ $badge === 'sale' ? 'SALE' : ($badge === 'new' ? 'NEW' : 'HOT') }}
            </span>
        @endif

        <div class="quick">
            @auth('customer')
                <button type="button" onclick="event.preventDefault(); toggleWishlist({{ $product->id }}, this)"
                        class="{{ $inWishlist ? 'text-blue-500' : '' }}" title="Wishlist">
                    <i class="{{ $inWishlist ? 'fas' : 'far' }} fa-heart"></i>
                </button>
            @else
                <button type="button" onclick="event.preventDefault(); window.location='{{ route('shop.login') }}'" title="Wishlist">
                    <i class="far fa-heart"></i>
                </button>
            @endauth
            <button type="button" onclick="event.preventDefault(); addToCart({{ $product->id }})" title="Quick add">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </a>

    <div class="p-4">
        @if ($product->brand)
            <div class="text-[10px] uppercase tracking-widest text-gray-400 font-semibold mb-1">{{ $product->brand->name }}</div>
        @endif
        <a href="{{ route('shop.product', $product->slug ?? $product->id) }}"
           class="font-semibold text-gray-900 leading-snug line-clamp-2 hover:text-blue-700 transition">
            {{ $product->name }}
        </a>

        @if ($product->barcode)
            <div class="text-[10px] text-gray-400 font-mono mt-1">Code: {{ $product->barcode }}</div>
        @endif

        <div class="flex items-center flex-wrap gap-x-2 gap-y-0.5 mt-2">
            <span class="font-bold text-base" style="color:var(--brand-navy);">{{ shop_price($price) }}</span>
            @if ($hasSale)
                <span class="text-xs text-gray-400 line-through">{{ shop_price($strike) }}</span>
                @if (shop_is_reseller())
                    <span class="text-[10px] font-semibold text-emerald-600">retail · save {{ shop_price($strike - $price) }}</span>
                @endif
            @endif
        </div>

        @if ((float) $product->avg_rating > 0)
            <div class="flex items-center gap-1 mt-1.5 text-xs text-amber-500">
                @for ($i = 1; $i <= 5; $i++)
                    <i class="fas fa-star {{ $i <= round($product->avg_rating) ? '' : 'text-gray-200' }}"></i>
                @endfor
                <span class="text-gray-400 ml-1">({{ $product->review_count }})</span>
            </div>
        @endif
    </div>
</article>
