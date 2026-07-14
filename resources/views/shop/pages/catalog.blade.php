@extends('shop.layouts.app')

@section('title', $category?->name ?? $brand?->name ?? ($q ? 'Search: ' . $q : 'Shop'))

@section('content')
@php
    // Active filter chips for the toolbar.
    $activeBrand = $allBrands->firstWhere('id', request('brand_id'));
    $hasFilters  = request('brand_id') || request('price_min') || request('price_max');
@endphp

<section class="hero py-10 sm:py-12 text-center">
    <div class="hero-pattern absolute inset-0"></div>
    <div class="relative max-w-3xl mx-auto px-4 reveal">
        <span class="chip mb-3 inline-block" style="background:rgba(41,171,226,.16);color:#f3dca0;border:1px solid rgba(41,171,226,.35);">
            {{ $category ? 'Category' : ($brand ? 'Brand' : ($q ? 'Search' : 'Shop')) }}
        </span>
        <h1 class="display text-3xl sm:text-5xl font-bold text-white">
            {{ $category?->name ?? $brand?->name ?? ($q ? 'Results for "' . $q . '"' : 'All products') }}
        </h1>
        <p class="text-blue-100/80 mt-2">{{ $products->total() }} products found</p>
    </div>
</section>

<section class="py-6 sm:py-8" x-data="{ filtersOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- ── Toolbar: Filters button + active chips + Sort ── --}}
        <form method="GET" id="catalogFilters">
            @if ($q) <input type="hidden" name="q" value="{{ $q }}"> @endif
            <input type="hidden" name="brand_id"  value="{{ request('brand_id') }}">
            <input type="hidden" name="price_min" value="{{ request('price_min') }}">
            <input type="hidden" name="price_max" value="{{ request('price_max') }}">

            <div class="flex items-center justify-between gap-3 mb-6 reveal">
                <button type="button" @click="filtersOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm font-semibold text-gray-700 hover:border-blue-300 hover:text-blue-700 transition">
                    <i class="fas fa-sliders"></i> Filters
                    @if ($hasFilters)<span class="w-2 h-2 rounded-full bg-blue-500"></span>@endif
                </button>

                {{-- Active chips --}}
                <div class="hidden sm:flex items-center gap-2 flex-1 flex-wrap">
                    @if ($activeBrand)
                        <span class="chip" style="background:#e8f1fb;color:var(--brand-blue);">{{ $activeBrand->name }}</span>
                    @endif
                    @if (request('price_min') || request('price_max'))
                        <span class="chip" style="background:#e8f1fb;color:var(--brand-blue);">Rs. {{ request('price_min') ?: 0 }}–{{ request('price_max') ?: '∞' }}</span>
                    @endif
                    @if ($hasFilters)
                        <a href="{{ $category ? route('shop.category',$category->slug) : ($brand ? route('shop.brand',$brand->slug) : ($q ? route('shop.search', ['q'=>$q]) : route('shop.catalog'))) }}"
                           class="text-xs text-gray-500 hover:text-blue-700 underline">Clear all</a>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <label class="text-xs text-gray-400 hidden sm:inline">Sort</label>
                    <select name="sort" onchange="document.getElementById('catalogFilters').submit()"
                            class="px-3 py-2.5 border border-gray-200 rounded-xl text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="newest"    @selected($sort==='newest')>Newest</option>
                        <option value="price_asc" @selected($sort==='price_asc')>Price ↑</option>
                        <option value="price_desc"@selected($sort==='price_desc')>Price ↓</option>
                        <option value="rating"    @selected($sort==='rating')>Top rated</option>
                        <option value="popular"   @selected($sort==='popular')>Most popular</option>
                        <option value="name"      @selected($sort==='name')>Name A–Z</option>
                    </select>
                </div>
            </div>
        </form>

        {{-- ── Products grid (full width, abundant) + infinite scroll ── --}}
        @if ($products->count())
            <div x-data="catalogInfinite('{{ $products->nextPageUrl() }}')">
                <div x-ref="grid" data-products-grid
                     class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-5">
                    @foreach ($products as $product)
                        @include('shop.partials.product-card', compact('product'))
                    @endforeach
                </div>

                {{-- Sentinel + Load more (works with JS; plain links as fallback) --}}
                <div x-ref="sentinel" class="h-1"></div>
                <div class="mt-10 text-center">
                    <template x-if="loading">
                        <div class="inline-flex items-center gap-2 text-gray-500 text-sm"><i class="fas fa-circle-notch fa-spin"></i> Loading more…</div>
                    </template>
                    <button type="button" x-show="nextUrl && !loading" @click="loadMore()"
                            class="btn btn-ghost">Load more <i class="fas fa-arrow-down text-xs"></i></button>
                    <div x-show="!nextUrl && !loading" class="text-xs text-gray-400">You’ve reached the end · {{ $products->total() }} products</div>
                </div>

                {{-- No-JS pagination fallback --}}
                <noscript>
                    <div class="mt-8">{{ $products->onEachSide(1)->links() }}</div>
                </noscript>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-gray-100 p-12 text-center reveal">
                <i class="fas fa-magnifying-glass text-4xl text-gray-300 mb-3"></i>
                <p class="font-bold text-gray-700">No products found</p>
                <p class="text-sm text-gray-500 mt-1">Try a different filter or browse our full catalog.</p>
                <a href="{{ route('shop.catalog') }}" class="btn btn-dark mt-4 !text-xs">Browse all</a>
            </div>
        @endif
    </div>

    {{-- ── Off-canvas filter panel (hidden until toggled) ── --}}
    <div x-show="filtersOpen" x-cloak style="display:none" class="fixed inset-0 z-[120]">
        {{-- backdrop --}}
        <div @click="filtersOpen = false"
             x-show="filtersOpen" x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="absolute inset-0 bg-black/40"></div>

        {{-- panel --}}
        <div x-show="filtersOpen"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
             class="absolute left-0 top-0 bottom-0 w-full max-w-[340px] bg-white shadow-2xl flex flex-col">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <span class="font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-sliders"></i> Filters</span>
            <button type="button" @click="filtersOpen = false" class="text-gray-400 hover:text-gray-700 w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center"><i class="fas fa-times"></i></button>
        </div>

        <form method="GET" class="flex-1 overflow-y-auto p-5 space-y-6">
            @if ($q) <input type="hidden" name="q" value="{{ $q }}"> @endif

            <div>
                <h3 class="font-bold text-gray-800 mb-3">Sort by</h3>
                <select name="sort" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <option value="newest"    @selected($sort==='newest')>Newest first</option>
                    <option value="price_asc" @selected($sort==='price_asc')>Price: low to high</option>
                    <option value="price_desc"@selected($sort==='price_desc')>Price: high to low</option>
                    <option value="rating"    @selected($sort==='rating')>Top rated</option>
                    <option value="name"      @selected($sort==='name')>Name A–Z</option>
                </select>
            </div>

            <div>
                <h3 class="font-bold text-gray-800 mb-3">Price (Rs.)</h3>
                <div class="flex gap-2">
                    <input type="number" name="price_min" value="{{ request('price_min') }}" placeholder="Min" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                    <input type="number" name="price_max" value="{{ request('price_max') }}" placeholder="Max" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm">
                </div>
            </div>

            @if ($allBrands->isNotEmpty() && !$brand)
                <div>
                    <h3 class="font-bold text-gray-800 mb-3">Brand</h3>
                    <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                        <label class="flex items-center gap-2 text-sm cursor-pointer hover:text-blue-700">
                            <input type="radio" name="brand_id" value="" {{ request('brand_id') ? '' : 'checked' }}> All brands
                        </label>
                        @foreach ($allBrands as $b)
                            <label class="flex items-center gap-2 text-sm cursor-pointer hover:text-blue-700">
                                <input type="radio" name="brand_id" value="{{ $b->id }}" {{ request('brand_id') == $b->id ? 'checked' : '' }}>
                                {{ $b->name }}
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($allCategories->isNotEmpty())
                <div>
                    <h3 class="font-bold text-gray-800 mb-3">Categories</h3>
                    <ul class="space-y-1 text-sm max-h-64 overflow-y-auto pr-1">
                        @foreach ($allCategories as $cat)
                            <li><a href="{{ route('shop.category', $cat->slug) }}"
                                   class="block py-1 hover:text-blue-700 transition {{ $category && $category->id === $cat->id ? 'text-blue-700 font-semibold' : '' }}">{{ $cat->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="flex gap-2 pt-2">
                <button type="submit" class="btn btn-primary btn-block">Apply filters</button>
            </div>
            @if ($hasFilters)
                <a href="{{ $category ? route('shop.category',$category->slug) : ($brand ? route('shop.brand',$brand->slug) : ($q ? route('shop.search', ['q'=>$q]) : route('shop.catalog'))) }}"
                   class="block text-center text-xs text-gray-500 hover:text-blue-700 underline">Clear all filters</a>
            @endif
        </form>
        </div>{{-- /panel --}}
    </div>{{-- /off-canvas --}}
</section>
@endsection

@push('styles')
<style>.hero-pattern{background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);background-size:48px 48px;mask-image:radial-gradient(ellipse at center,black 30%,transparent 80%);-webkit-mask-image:radial-gradient(ellipse at center,black 30%,transparent 80%);}</style>
@endpush

@push('scripts')
<script>
    // Infinite scroll for the catalog grid: fetches the next page, appends its
    // product cards, and keeps a "Load more" button as an explicit control too.
    window.catalogInfinite = function (nextUrl) {
        return {
            nextUrl: nextUrl || null,
            loading: false,
            init() {
                if (!('IntersectionObserver' in window) || !this.$refs.sentinel) return;
                const io = new IntersectionObserver((entries) => {
                    entries.forEach(e => { if (e.isIntersecting) this.loadMore(); });
                }, { rootMargin: '500px' });
                io.observe(this.$refs.sentinel);
            },
            async loadMore() {
                if (this.loading || !this.nextUrl) return;
                this.loading = true;
                try {
                    const res = await fetch(this.nextUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    const html = await res.text();
                    const doc = new DOMParser().parseFromString(html, 'text/html');
                    const newGrid = doc.querySelector('[data-products-grid]');
                    if (newGrid && this.$refs.grid) {
                        this.$refs.grid.insertAdjacentHTML('beforeend', newGrid.innerHTML);
                    }
                    // The next page's own catalogInfinite call carries the following URL.
                    const m = html.match(/catalogInfinite\('([^']*)'\)/);
                    this.nextUrl = (m && m[1]) ? m[1] : null;
                } catch (e) {
                    window.toast && window.toast('Could not load more', 'error');
                } finally {
                    this.loading = false;
                }
            },
        };
    };
</script>
@endpush
