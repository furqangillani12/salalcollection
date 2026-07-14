@extends('shop.layouts.app')

@section('title', 'Home')
@section('description', 'SALAL COLLECTION — quality and affordability you can trust. Shop online from our trusted retail in PanjGirain, Bhakkar.')

@section('content')

@php
    $productTotal = \App\Models\Product::onWebsite()->count();
    $hero      = $heroBanners->take(6);
    $heroCount = max(1, $hero->count());
@endphp

{{-- ═════════════════ HERO — full-width prominent slider ═════════════════ --}}
<section class="relative">
    <div x-data="{
            active: 0,
            count: {{ $heroCount }},
            timer: null,
            start() { if (this.count > 1) { this.stop(); this.timer = setInterval(() => this.next(), 6000); } },
            stop()  { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
            next()  { this.active = (this.active + 1) % this.count; },
            prev()  { this.active = (this.active - 1 + this.count) % this.count; },
            go(i)   { this.active = i; this.start(); }
         }"
         x-init="start()"
         class="relative w-full overflow-hidden"
         @mouseenter="stop()" @mouseleave="start()">

        <div class="relative" style="height:clamp(460px, 64vh, 680px);">

            @if ($hero->isNotEmpty())
                @foreach ($hero as $i => $b)
                    <div class="absolute inset-0"
                         x-show="active === {{ $i }}"
                         x-transition:enter="transition-opacity ease-in-out duration-700"
                         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                         x-transition:leave="transition-opacity ease-in-out duration-700"
                         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                         @if ($i !== 0) style="display:none" @endif>

                        <img src="{{ shop_image($b->image) }}" alt="{{ $b->title ?? 'SALAL COLLECTION' }}"
                             class="absolute inset-0 w-full h-full object-cover">
                        {{-- wine-tinted scrim for text legibility --}}
                        <div class="absolute inset-0"
                             style="background:linear-gradient(90deg, rgba(31,21,23,.86) 0%, rgba(31,21,23,.55) 42%, rgba(31,21,23,.12) 100%);"></div>
                        <div class="absolute inset-0"
                             style="background:linear-gradient(0deg, rgba(31,21,23,.55), transparent 45%);"></div>

                        <div class="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center">
                            <div class="max-w-xl text-white reveal-stagger">
                                <span class="chip" style="background:rgba(41,171,226,.16);color:#f3dca0;border:1px solid rgba(41,171,226,.35);">
                                    <span style="width:6px;height:6px;background:var(--gold);border-radius:9999px;display:inline-block;animation:pulse 2s infinite;"></span>
                                    {{ $b->subtitle ?: "Pakistan's most trusted retail" }}
                                </span>
                                <h1 class="display text-4xl sm:text-5xl lg:text-6xl font-bold leading-[1.05] mt-5 drop-shadow">
                                    {{ $b->title ?: 'Quality & affordability in every box.' }}
                                </h1>
                                <div class="flex flex-wrap gap-3 mt-8">
                                    <a href="{{ $b->cta_url ?: route('shop.catalog') }}" class="btn btn-primary">
                                        {{ $b->cta_text ?: 'Shop now' }} <i class="fas fa-arrow-right text-xs"></i>
                                    </a>
                                    <a href="{{ route('shop.catalog') }}" class="btn btn-ghost text-white border-white/25 hover:bg-white/10">Browse all</a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                {{-- No banners yet: branded gradient hero so the section is never empty --}}
                <div class="absolute inset-0 hero">
                    <div class="hero-pattern absolute inset-0"></div>
                    <div class="relative h-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center">
                        <div class="max-w-xl text-white reveal-stagger">
                            <span class="chip" style="background:rgba(41,171,226,.16);color:#f3dca0;border:1px solid rgba(41,171,226,.35);">
                                <span style="width:6px;height:6px;background:var(--gold);border-radius:9999px;display:inline-block;animation:pulse 2s infinite;"></span>
                                Pakistan's most trusted retail
                            </span>
                            <h1 class="display text-4xl sm:text-5xl lg:text-6xl font-bold leading-[1.05] mt-5">
                                Quality &amp; <span style="color:var(--gold);">affordability</span><br>in every box.
                            </h1>
                            <p class="text-base sm:text-lg text-blue-50/85 max-w-md mt-5">
                                Hand-picked products from <strong>SALAL COLLECTION</strong> — now online with same-day fulfilment from our shop in PanjGirain.
                            </p>
                            <div class="flex flex-wrap gap-3 mt-8">
                                <a href="{{ route('shop.catalog') }}" class="btn btn-primary">Shop now <i class="fas fa-arrow-right text-xs"></i></a>
                                <a href="#categories" class="btn btn-ghost text-white border-white/25 hover:bg-white/10">Explore</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- stats ribbon (always present, sits above slides) --}}
            <div class="absolute inset-x-0 bottom-0 z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-7">
                    <div class="grid grid-cols-3 gap-3 sm:gap-6 max-w-md text-white border-t border-white/20 pt-4">
                        <div><div class="text-xl sm:text-2xl font-extrabold" style="color:var(--gold);">100%</div><div class="text-[11px] text-white/75 mt-0.5">Authentic</div></div>
                        <div><div class="text-xl sm:text-2xl font-extrabold" style="color:var(--gold);">{{ $productTotal }}+</div><div class="text-[11px] text-white/75 mt-0.5">Products</div></div>
                        <div><div class="text-xl sm:text-2xl font-extrabold" style="color:var(--gold);">24/7</div><div class="text-[11px] text-white/75 mt-0.5">Support</div></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- arrows + dots --}}
        <template x-if="count > 1">
            <div>
                <button type="button" @click.prevent="prev()" aria-label="Previous slide"
                    class="absolute left-3 sm:left-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white border border-white/30 flex items-center justify-center transition" style="backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" @click.prevent="next()" aria-label="Next slide"
                    class="absolute right-3 sm:right-5 top-1/2 -translate-y-1/2 z-20 w-11 h-11 rounded-full bg-white/10 hover:bg-white/25 text-white border border-white/30 flex items-center justify-center transition" style="backdrop-filter:blur(4px);-webkit-backdrop-filter:blur(4px);">
                    <i class="fas fa-chevron-right"></i>
                </button>
                <div class="absolute top-5 right-5 z-20 flex gap-2">
                    @for ($i = 0; $i < $heroCount; $i++)
                        <button type="button" @click.prevent="go({{ $i }})" aria-label="Go to slide {{ $i + 1 }}"
                            class="h-2.5 rounded-full transition-all"
                            :class="active === {{ $i }} ? 'bg-white w-7' : 'bg-white/50 hover:bg-white/80 w-2.5'"></button>
                    @endfor
                </div>
            </div>
        </template>
    </div>
</section>

{{-- ═════════════════ FEATURED CATEGORIES (always shown) ═════════════════ --}}
<section id="categories" class="py-8 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6 reveal">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--rose);">Browse</span>
                <h2 class="display text-3xl sm:text-4xl font-bold mt-2">Shop by category</h2>
            </div>
            <a href="{{ route('shop.catalog') }}" class="text-sm font-semibold inline-flex items-center gap-2 hover:gap-3 transition-all" style="color:var(--rose);">
                View all <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>

        @if ($featuredCategories->isNotEmpty())
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 reveal-stagger">
                @foreach ($featuredCategories as $cat)
                    <a href="{{ route('shop.category', $cat->slug) }}"
                       class="group relative rounded-2xl overflow-hidden bg-gray-100 hover:shadow-xl transition" style="aspect-ratio:1;">
                        <img src="{{ shop_image($cat->photo) }}" alt="{{ $cat->name }}" loading="lazy"
                             class="w-full h-full object-cover transition duration-700 group-hover:scale-110">
                        <div class="absolute inset-0" style="background:linear-gradient(180deg,transparent 45%,rgba(31,21,23,.78));"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-4 text-white">
                            <div class="font-bold text-base">{{ $cat->name }}</div>
                            <div class="text-[11px] opacity-85 inline-flex items-center gap-1 mt-1 group-hover:gap-2 transition-all">
                                Shop now <i class="fas fa-arrow-right text-[9px]"></i>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white py-14 text-center text-gray-500 reveal">
                <i class="fas fa-layer-group text-3xl mb-3" style="color:var(--rose);opacity:.6;"></i>
                <p class="font-semibold text-gray-700">Categories coming soon</p>
                <a href="{{ route('shop.catalog') }}" class="inline-flex items-center gap-2 mt-3 text-sm font-semibold" style="color:var(--rose);">Browse all products <i class="fas fa-arrow-right text-xs"></i></a>
            </div>
        @endif
    </div>
</section>

{{-- ═════════════════ FEATURED PRODUCTS (always shown) ═════════════════ --}}
<section class="py-8 sm:py-10" style="background:var(--paper-warm);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6 reveal">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--rose);">Featured</span>
                <h2 class="display text-3xl sm:text-4xl font-bold mt-2">Our best picks for you</h2>
            </div>
            <a href="{{ route('shop.catalog') }}" class="text-sm font-semibold inline-flex items-center gap-2 hover:gap-3 transition-all" style="color:var(--rose);">
                Shop all <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
        @if ($featuredProducts->isNotEmpty())
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 reveal-stagger">
                @foreach ($featuredProducts as $product)
                    @include('shop.partials.product-card', compact('product'))
                @endforeach
            </div>
        @else
            @include('shop.partials.empty-products')
        @endif
    </div>
</section>

{{-- ═════════════════ MID PROMO (carousel, always shown) ═════════════════ --}}
<section class="py-8 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 reveal">
        @if ($midBanners->isNotEmpty())
            <div x-data="{
                    active: 0,
                    count: {{ $midBanners->count() }},
                    timer: null,
                    start() { if (this.count > 1) { this.stop(); this.timer = setInterval(() => this.next(), 6000); } },
                    stop()  { if (this.timer) { clearInterval(this.timer); this.timer = null; } },
                    next()  { this.active = (this.active + 1) % this.count; },
                    prev()  { this.active = (this.active - 1 + this.count) % this.count; },
                    go(i)   { this.active = i; this.start(); }
                 }"
                 x-init="start()">
                <div class="relative rounded-3xl overflow-hidden shadow-lg aspect-video sm:aspect-[21/9]"
                     @mouseenter="stop()" @mouseleave="start()">

                    @foreach ($midBanners as $i => $b)
                        <a href="{{ $b->cta_url ?: route('shop.catalog') }}"
                           class="absolute inset-0"
                           x-show="active === {{ $i }}"
                           x-transition:enter="transition-opacity ease-in-out duration-700"
                           x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                           x-transition:leave="transition-opacity ease-in-out duration-700"
                           x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                           @if ($i !== 0) style="display:none" @endif>
                            <img src="{{ shop_image($b->image) }}" alt="{{ $b->title }}"
                                 class="w-full h-full object-cover">
                            <div class="absolute inset-0" style="background:linear-gradient(90deg,rgba(31,21,23,.82) 0%,rgba(31,21,23,.2) 60%);"></div>
                            <div class="absolute inset-0 p-8 sm:p-12 flex flex-col justify-center text-white">
                                @if ($b->subtitle) <div class="text-xs font-bold uppercase tracking-widest" style="color:var(--gold);">{{ $b->subtitle }}</div> @endif
                                @if ($b->title) <h3 class="display text-2xl sm:text-4xl font-bold mt-2 max-w-md">{{ $b->title }}</h3> @endif
                                @if ($b->cta_text)
                                    <span class="inline-flex items-center gap-2 mt-4 text-sm font-semibold w-max bg-white/90 text-gray-900 px-4 py-2 rounded-full">
                                        {{ $b->cta_text }} <i class="fas fa-arrow-right text-xs"></i>
                                    </span>
                                @endif
                            </div>
                        </a>
                    @endforeach

                    @if ($midBanners->count() > 1)
                        <button type="button" @click.prevent="prev()" aria-label="Previous slide"
                            class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/85 hover:bg-white text-gray-800 flex items-center justify-center shadow">
                            <i class="fas fa-chevron-left text-sm"></i>
                        </button>
                        <button type="button" @click.prevent="next()" aria-label="Next slide"
                            class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-10 h-10 rounded-full bg-white/85 hover:bg-white text-gray-800 flex items-center justify-center shadow">
                            <i class="fas fa-chevron-right text-sm"></i>
                        </button>
                        <div class="absolute bottom-4 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                            @foreach ($midBanners as $i => $b)
                                <button type="button" @click.prevent="go({{ $i }})" aria-label="Go to slide {{ $i + 1 }}"
                                    class="h-2.5 rounded-full transition-all"
                                    :class="active === {{ $i }} ? 'bg-white w-6' : 'bg-white/50 hover:bg-white/80 w-2.5'"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @else
            {{-- Fallback promo band so the section is never blank --}}
            <a href="{{ route('shop.catalog') }}"
               class="block relative rounded-3xl overflow-hidden shadow-lg aspect-video sm:aspect-[21/9]" style="background:linear-gradient(120deg,var(--brand-navy),var(--rose-deep),var(--rose));">
                <div class="absolute inset-0" style="background:radial-gradient(circle at 80% 30%, rgba(41,171,226,.25), transparent 50%);"></div>
                <div class="absolute inset-0 p-8 sm:p-12 flex flex-col justify-center text-white">
                    <div class="text-xs font-bold uppercase tracking-widest" style="color:var(--gold);">Special offers</div>
                    <h3 class="display text-2xl sm:text-4xl font-bold mt-2 max-w-md">Great deals, delivered to your door</h3>
                    <span class="inline-flex items-center gap-2 mt-4 text-sm font-semibold w-max bg-white/90 text-gray-900 px-4 py-2 rounded-full">
                        Shop the collection <i class="fas fa-arrow-right text-xs"></i>
                    </span>
                </div>
            </a>
        @endif
    </div>
</section>

{{-- ═════════════════ NEW ARRIVALS (always shown) ═════════════════ --}}
<section class="py-8 sm:py-10" style="background:var(--paper-warm);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6 reveal">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--rose);">Fresh</span>
                <h2 class="display text-3xl sm:text-4xl font-bold mt-2">New arrivals</h2>
            </div>
            <a href="{{ route('shop.catalog') }}" class="text-sm font-semibold inline-flex items-center gap-2 hover:gap-3 transition-all" style="color:var(--rose);">
                See more <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
        @if ($newArrivals->isNotEmpty())
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 reveal-stagger">
                @foreach ($newArrivals as $product)
                    @include('shop.partials.product-card', compact('product'))
                @endforeach
            </div>
        @else
            @include('shop.partials.empty-products')
        @endif
    </div>
</section>

{{-- ═════════════════ TOP RATED (always shown) ═════════════════ --}}
<section class="py-8 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6 reveal">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--rose);">Loved by customers</span>
                <h2 class="display text-3xl sm:text-4xl font-bold mt-2">Top rated products</h2>
            </div>
            <a href="{{ route('shop.catalog') }}" class="text-sm font-semibold inline-flex items-center gap-2 hover:gap-3 transition-all" style="color:var(--rose);">
                Shop all <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>
        @if (($bestRated ?? collect())->isNotEmpty())
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 reveal-stagger">
                @foreach ($bestRated as $product)
                    @include('shop.partials.product-card', compact('product'))
                @endforeach
            </div>
        @else
            @include('shop.partials.empty-products')
        @endif
    </div>
</section>

{{-- ═════════════════ RECOMMENDED FOR YOU (#12, behaviour-based) ═════════════════ --}}
@if (($recommended ?? collect())->isNotEmpty())
<section class="py-8 sm:py-10" style="background:var(--paper-warm);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-6 reveal">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--rose);">Picked for you</span>
                <h2 class="display text-3xl sm:text-4xl font-bold mt-2">Recommended for you</h2>
            </div>
            <a href="{{ route('shop.catalog') }}" class="text-sm font-semibold inline-flex items-center gap-2 hover:gap-3 transition-all" style="color:var(--rose);">Shop all <i class="fas fa-arrow-right text-xs"></i></a>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 reveal-stagger">
            @foreach ($recommended as $product)
                @include('shop.partials.product-card', compact('product'))
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═════════════════ RECENTLY VIEWED (#12) ═════════════════ --}}
@if (($recentlyViewed ?? collect())->isNotEmpty())
<section class="py-8 sm:py-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-end justify-between gap-3 mb-6 reveal">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--rose);">Continue browsing</span>
                <h2 class="display text-3xl sm:text-4xl font-bold mt-2">Recently viewed</h2>
            </div>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 reveal-stagger">
            @foreach ($recentlyViewed as $product)
                @include('shop.partials.product-card', compact('product'))
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ═════════════════ BRANDS (always shown) ═════════════════ --}}
<section class="py-8 sm:py-10 bg-white border-y border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8 reveal">
            <span class="text-xs font-bold uppercase tracking-widest" style="color:var(--rose);">Brands we love</span>
        </div>
        @if ($brands->isNotEmpty())
            <div class="flex items-center justify-center flex-wrap gap-8 sm:gap-14 grayscale hover:grayscale-0 transition opacity-80 reveal-stagger">
                @foreach ($brands as $brand)
                    <a href="{{ route('shop.brand', $brand->slug) }}" class="block hover:scale-105 transition">
                        @if ($brand->logo)
                            <img src="{{ shop_image($brand->logo) }}" alt="{{ $brand->name }}" class="h-12 object-contain" loading="lazy">
                        @else
                            <span class="text-xl font-bold text-gray-700">{{ $brand->name }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        @else
            <p class="text-center text-gray-400 text-sm">Featured brands will appear here soon.</p>
        @endif
    </div>
</section>

{{-- ═════════════════ CTA BAND ═════════════════ --}}
<section class="py-10 sm:py-12 relative overflow-hidden" style="background:var(--brand-navy);">
    <div class="absolute inset-0" style="background:radial-gradient(circle at 80% 30%, rgba(46,49,146,.35), transparent 50%);"></div>
    <div class="absolute inset-0" style="background:radial-gradient(circle at 15% 80%, rgba(41,171,226,.18), transparent 45%);"></div>
    <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-white reveal">
        <h2 class="display text-3xl sm:text-5xl font-bold mb-4">Discover something new every visit</h2>
        <p class="text-base sm:text-lg text-blue-50/80 mb-8 max-w-xl mx-auto">
            From everyday essentials to special occasion pieces — Salal Collection brings the best of our shops directly to your door.
        </p>
        <a href="{{ route('shop.catalog') }}" class="btn btn-primary"><i class="fas fa-bag-shopping"></i> Browse the full catalog</a>
    </div>
</section>

{{-- ═════════════════ TRUST STRIP (moved to the bottom per client) ═════════════════ --}}
<section id="features" class="bg-white border-t border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 grid grid-cols-2 lg:grid-cols-4 gap-6 reveal-stagger">
        @foreach ([
            ['fa-truck',          'Fast delivery',   'Same-day from local branch'],
            ['fa-shield-halved',  'Secure shopping', '100% authentic products'],
            ['fa-rotate-left',    'Easy returns',    '7-day return policy'],
            ['fa-headset',        'Real support',    'Talk to a real person'],
        ] as [$icon, $title, $sub])
            <div class="flex items-start gap-3">
                <span class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0"
                      style="background:linear-gradient(135deg,#e8f1fb,#f6ecd8);color:var(--rose);">
                    <i class="fas {{ $icon }}"></i>
                </span>
                <div>
                    <div class="font-bold text-gray-900 text-sm">{{ $title }}</div>
                    <div class="text-[12px] text-gray-500 mt-0.5">{{ $sub }}</div>
                </div>
            </div>
        @endforeach
    </div>
</section>

@endsection

@push('styles')
<style>
    .hero-pattern {
        background-image:
            linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
        background-size: 48px 48px;
        mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
        -webkit-mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
    }
    @keyframes pulse { 0%,100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.4); opacity: .5; } }
</style>
@endpush
