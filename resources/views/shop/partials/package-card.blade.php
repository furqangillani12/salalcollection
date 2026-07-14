@php
    /** @var \App\Models\Package $package */
    $pkgPrice = shop_package_price($package);
    $retail   = (float) $package->retail_total;
    $save     = max(0, $retail - $pkgPrice);
    $items    = $package->items->filter(fn ($i) => $i->product);
@endphp

<div class="bg-white border border-gray-100 rounded-2xl overflow-hidden flex flex-col hover:shadow-lg transition">
    {{-- Item image collage --}}
    <div class="grid grid-cols-3 gap-px bg-gray-100">
        @foreach ($items->take(3) as $it)
            <div class="aspect-square bg-white overflow-hidden">
                <img src="{{ shop_image($it->product->image) }}" alt="{{ $it->product->name }}" class="w-full h-full object-cover">
            </div>
        @endforeach
    </div>

    <div class="p-4 flex flex-col flex-1">
        <div class="flex items-center gap-2 mb-1">
            <span class="chip" style="background:#dcfce7;color:#047857;"><i class="fas fa-box-open"></i> Package</span>
            @if ($items->count() > 0)
                <span class="text-[11px] text-gray-400">{{ $items->count() }} items</span>
            @endif
        </div>
        <h3 class="font-bold text-gray-900 leading-snug line-clamp-1">{{ $package->name }}</h3>

        {{-- Item list --}}
        <ul class="text-xs text-gray-500 mt-2 space-y-0.5">
            @foreach ($items->take(4) as $it)
                <li class="flex items-center gap-1.5 truncate">
                    <i class="fas fa-circle text-[4px] text-gray-300"></i>
                    <span class="truncate">{{ rtrim(rtrim(number_format($it->quantity, 2), '0'), '.') }}× {{ $it->product->name }}</span>
                </li>
            @endforeach
            @if ($items->count() > 4)
                <li class="text-gray-400">+ {{ $items->count() - 4 }} more…</li>
            @endif
        </ul>

        <div class="mt-3 flex items-baseline gap-2">
            <span class="text-lg font-extrabold" style="color:var(--brand-navy);">{{ shop_price($pkgPrice) }}</span>
            @if ($save > 0)
                <span class="text-xs text-gray-400 line-through">{{ shop_price($retail) }}</span>
            @endif
        </div>
        @if ($save > 0)
            <div class="text-[11px] font-semibold text-emerald-600">Save {{ shop_price($save) }} with this package</div>
        @endif

        <form method="POST" action="{{ route('shop.cart.add-package') }}" class="mt-3 mt-auto pt-3">
            @csrf
            <input type="hidden" name="package_id" value="{{ $package->id }}">
            <button type="submit" class="btn btn-primary btn-block !py-2.5 !text-sm"><i class="fas fa-cart-plus"></i> Add package to cart</button>
        </form>
    </div>
</div>
