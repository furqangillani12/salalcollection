<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1b1f5c">

    <title>@yield('title', 'Salal Collection') · Salal Collection</title>

    {{-- Meta values are SQUISHED + escaped via {{ }}: product descriptions can
         contain newlines / quotes that otherwise break the tag and stop Facebook
         from generating a preview (#3). --}}
    @php
        $metaTitle = \Illuminate\Support\Str::of(View::getSection('og_title') ?: View::getSection('title', 'SALAL COLLECTION'))->squish();
        $metaDesc  = \Illuminate\Support\Str::of(View::getSection('og_description') ?: View::getSection('description', 'SALAL COLLECTION — quality and affordability you can trust. Shop online from our trusted retail branches across Pakistan.'))->stripTags()->squish()->limit(200);
        $ogImage   = trim(View::getSection('og_image')) ?: asset('assets/images/brand/almufeed-traders-square.jpg');
    @endphp
    <meta name="description" content="{{ $metaDesc }}">

    {{-- Open Graph / Twitter — shared product links preview the product image. --}}
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="SALAL COLLECTION">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    <link rel="icon" type="image/png" href="{{ asset('assets/images/brand/almufeed-traders-square.jpg') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            /* ── Brand palette matched to the SALAL COLLECTION logo ────────
               Teal-green + gold, white text. Variable NAMES kept stable so
               every shop page inherits the new theme automatically. */
            --brand-navy:  #0b3a30;   /* deep green — dark surfaces/headings      */
            --brand-blue:  #0f6b58;   /* teal-green — primary (logo tree)         */
            --brand-cyan:  #14846d;   /* medium green — accent                    */
            --brand-light: #1aa07f;   /* bright green — hovers / highlights       */
            --rose:        #0f6b58;   /* primary (green) — buttons/eyebrows       */
            --rose-deep:   #0b4a3c;   /* darker green                             */
            --gold:        #c9a227;   /* GOLD — secondary accent (logo banner)    */
            --gold-deep:   #a8841c;   /* darker gold                              */
            --paper:       #f7faf8;   /* soft green-tinted page background        */
            --paper-warm:  #eef5f0;   /* light green section background           */
            --ink:         #1f2937;   /* slate body text                          */
        }
        html, body {
            font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif;
            background: var(--paper);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }
        h1, h2, h3, .display { font-family: 'Playfair Display', Georgia, serif; letter-spacing: -0.01em; }
        body { overflow-x: hidden; }
        a { color: inherit; text-decoration: none; }
        [x-cloak] { display: none !important; }

        /* ── Smooth nav transitions ────────────────────────────────────── */
        @view-transition { navigation: auto; }
        ::view-transition-old(root) { animation: fadeOut .2s ease both; }
        ::view-transition-new(root) { animation: fadeIn  .25s ease both; }
        @keyframes fadeIn  { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
        @keyframes fadeOut { from { opacity: 1; } to { opacity: 0; } }

        /* ── Reveal-on-scroll (progressive enhancement: only hides when JS marks <html>) ── */
        .reveal             { transition: opacity .7s ease, transform .7s ease; }
        .reveal-stagger > * { transition: opacity .7s ease, transform .7s ease; }
        html.js-reveal .reveal             { opacity: 0; transform: translateY(20px); }
        html.js-reveal .reveal.in          { opacity: 1; transform: none; }
        html.js-reveal .reveal-stagger > * { opacity: 0; transform: translateY(20px); }
        html.js-reveal .reveal-stagger.in > *:nth-child(1) { transition-delay: 0ms; }
        html.js-reveal .reveal-stagger.in > *:nth-child(2) { transition-delay: 80ms; }
        html.js-reveal .reveal-stagger.in > *:nth-child(3) { transition-delay: 160ms; }
        html.js-reveal .reveal-stagger.in > *:nth-child(4) { transition-delay: 240ms; }
        html.js-reveal .reveal-stagger.in > *:nth-child(5) { transition-delay: 320ms; }
        html.js-reveal .reveal-stagger.in > *:nth-child(6) { transition-delay: 400ms; }
        html.js-reveal .reveal-stagger.in > *:nth-child(n+7) { transition-delay: 480ms; }
        html.js-reveal .reveal-stagger.in > * { opacity: 1; transform: none; }

        /* ── Buttons ──────────────────────────────────────────────────── */
        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px;
               padding: 11px 22px; border-radius: 12px; font-weight: 600; font-size: 14px;
               transition: transform .15s ease, box-shadow .2s ease, background .2s ease, color .2s ease;
               border: 1px solid transparent; cursor: pointer; user-select: none; }
        .btn:hover { transform: translateY(-1px); }
        .btn-primary { background: linear-gradient(135deg, var(--brand-blue), var(--brand-cyan)); color: #fff;
                       box-shadow: 0 10px 25px -10px rgba(46,49,146,.6); }
        .btn-primary:hover { box-shadow: 0 16px 34px -10px rgba(28,159,216,.8); }
        .btn-gold { background: linear-gradient(135deg, var(--brand-cyan), var(--brand-light)); color: #fff;
                    box-shadow: 0 10px 25px -10px rgba(28,159,216,.55); }
        .btn-gold:hover { box-shadow: 0 14px 30px -10px rgba(28,159,216,.75); }
        .btn-dark { background: linear-gradient(135deg, var(--brand-navy), var(--brand-cyan)); color: #fff;
                    box-shadow: 0 10px 25px -10px rgba(27,31,92,.45); }
        .btn-ghost { background: transparent; border-color: rgba(0,0,0,.12); color: var(--ink); }
        .btn-ghost:hover { background: #fff; border-color: rgba(0,0,0,.25); }
        .btn-block { width: 100%; }

        /* ── Pill / chip ──────────────────────────────────────────────── */
        .chip { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px;
                border-radius: 9999px; font-size: 11px; font-weight: 600; letter-spacing: .02em; }

        /* ── Cards ────────────────────────────────────────────────────── */
        .product-card {
            background: #fff; border: 1px solid rgba(0,0,0,.06); border-radius: 18px; overflow: hidden;
            transition: transform .25s cubic-bezier(.2,.8,.2,1), box-shadow .25s ease, border-color .25s ease;
            display: flex; flex-direction: column;
        }
        .product-card:hover { transform: translateY(-6px); box-shadow: 0 25px 40px -25px rgba(27,31,92,.28); border-color: rgba(28,159,216,.45); }
        .product-card .img-wrap { position: relative; aspect-ratio: 4/5; background: var(--paper-warm); overflow: hidden; }
        .product-card .img-wrap img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s cubic-bezier(.2,.8,.2,1); }
        .product-card:hover .img-wrap img { transform: scale(1.06); }
        .product-card .quick { position: absolute; top: 12px; right: 12px; display: flex; flex-direction: column; gap: 8px; opacity: 0; transform: translateX(8px); transition: opacity .25s ease, transform .25s ease; }
        .product-card:hover .quick { opacity: 1; transform: none; }
        .product-card .quick button {
            width: 36px; height: 36px; border-radius: 9999px; background: #fff; color: var(--brand-navy);
            border: 1px solid rgba(0,0,0,.05); display: flex; align-items: center; justify-content: center;
            cursor: pointer; box-shadow: 0 6px 12px -4px rgba(0,0,0,.15); transition: background .2s ease, color .2s ease;
        }
        .product-card .quick button:hover { background: var(--brand-navy); color: var(--gold); }

        /* ── Hero ─────────────────────────────────────────────────────── */
        .hero {
            background: linear-gradient(120deg, var(--brand-navy), var(--brand-blue), var(--brand-cyan), var(--brand-blue));
            background-size: 300% 300%;
            animation: heroShift 20s ease infinite;
            position: relative; overflow: hidden; color: #fff;
        }
        @keyframes heroShift { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        .hero::before {
            content:''; position: absolute; inset: 0;
            background:
                radial-gradient(circle at 18% 22%, rgba(41,171,226,.28), transparent 42%),
                radial-gradient(circle at 82% 72%, rgba(46,49,146,.35), transparent 46%);
            pointer-events: none;
        }

        /* ── Mini cart drawer ─────────────────────────────────────────── */
        .drawer-overlay { position: fixed; inset: 0; background: rgba(15,23,42,.5); backdrop-filter: blur(2px); z-index: 90; opacity: 0; pointer-events: none; transition: opacity .3s ease; }
        .drawer-overlay.open { opacity: 1; pointer-events: auto; }
        .drawer { position: fixed; top: 0; right: 0; bottom: 0; width: 100%; max-width: 420px; background: #fff;
                  z-index: 100; transform: translateX(100%); transition: transform .35s cubic-bezier(.2,.8,.2,1);
                  display: flex; flex-direction: column; box-shadow: -20px 0 60px -20px rgba(0,0,0,.25); }
        .drawer.open { transform: translateX(0); }

        /* ── Toast ─────────────────────────────────────────────────────── */
        .toast-stack { position: fixed; bottom: 24px; right: 24px; z-index: 200; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
        .toast { background: #fff; border: 1px solid rgba(0,0,0,.06); border-radius: 12px;
                 padding: 12px 16px; min-width: 240px; max-width: 360px; font-size: 14px;
                 box-shadow: 0 20px 40px -15px rgba(0,0,0,.25);
                 display: flex; align-items: start; gap: 10px;
                 transform: translateX(20px); opacity: 0; pointer-events: auto;
                 transition: transform .35s cubic-bezier(.2,.8,.2,1), opacity .3s ease;
                 border-left: 4px solid var(--brand-cyan); }
        .toast.show { transform: none; opacity: 1; }
        .toast.success { border-left-color: #059669; }
        .toast.error   { border-left-color: #dc2626; }

        /* ── Skeleton ─────────────────────────────────────────────────── */
        .skel { background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: skel 1.4s linear infinite; border-radius: 8px; }
        @keyframes skel { from { background-position: 200% 0; } to { background-position: -200% 0; } }

        /* ── Header ──────────────────────────────────────────────────── */
        .nav-link { position: relative; padding: 6px 0; font-weight: 500; }
        .nav-link::after { content:''; position: absolute; left: 0; right: 100%; bottom: 0; height: 2px; background: var(--gold); transition: right .3s ease; }
        .nav-link:hover::after, .nav-link.active::after { right: 0; }

        /* ── Smooth image fade-in ────────────────────────────────────── */
        img.lazy { opacity: 0; transition: opacity .4s ease; }
        img.lazy.loaded { opacity: 1; }

        /* ── Page wrapper transition ─────────────────────────────────── */
        main { animation: pageIn .35s cubic-bezier(.2,.8,.2,1) both; }
        @keyframes pageIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }
    </style>
    @stack('styles')
</head>
<body x-data="{
        miniCartOpen: false,
        mobileNavOpen: false,
        searchOpen: false,
        cartCount: {{ shop_cart_count() }},
        cartItems: [],
        cartSubtotal: 0,
        cartLoading: false,
        async loadCart() {
            this.cartLoading = true;
            try {
                const res = await fetch('{{ route('shop.cart.json') }}', { headers: { 'Accept': 'application/json' }});
                const data = await res.json();
                this.cartItems    = data.items || [];
                this.cartSubtotal = data.subtotal || 0;
                this.cartCount    = (data.items || []).reduce((s, i) => s + Number(i.qty), 0);
            } finally { this.cartLoading = false; }
        },
        async removeFromCart(id) {
            const res = await fetch('{{ route('shop.cart.remove', ['item' => 'ITEMID']) }}'.replace('ITEMID', id), {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
            });
            if (res.ok) { window.toast && window.toast('Removed from cart', 'success'); this.loadCart(); }
            else        { window.toast && window.toast('Could not remove', 'error'); }
        },
    }"
    x-init="initStorefront($el); $watch('miniCartOpen', v => v && loadCart())">

    {{-- ═════════════════ Announcement bar (admin-editable in Settings) ═════════════════ --}}
    @php
        $topbarText = setting('topbar_text', 'Free delivery across Pakistan on orders above Rs. 5,000');
        $topbarLoc  = trim((string) setting('topbar_location')) !== '' ? setting('topbar_location') : setting('site_address');
        $hasTopText = trim((string) $topbarText) !== '';
        $hasTopLoc  = trim((string) $topbarLoc) !== '';
    @endphp
    @if ($hasTopText || $hasTopLoc)
        <style>
            .tm-bar { overflow: hidden; }
            /* padding-left:100% parks the text just off the RIGHT edge; the animation
               then slides it all the way across and off the LEFT edge (full width). */
            .tm-track { display: inline-block; white-space: nowrap; padding-left: 100%; animation: tm-scroll 16s linear infinite; }
            .tm-bar:hover .tm-track { animation-play-state: paused; }
            .tm-item { display: inline-flex; align-items: center; gap: .5rem; padding: 7px 0; }
            .tm-dot { opacity: .6; }
            @keyframes tm-scroll { 0% { transform: translateX(0); } 100% { transform: translateX(-100%); } }
            @media (prefers-reduced-motion: reduce) { .tm-bar { text-align: center; } .tm-track { animation: none; padding-left: 0; } }
        </style>
        <div class="tm-bar text-white text-xs font-medium" style="background:var(--brand-navy);">
            <div class="tm-track">
                <span class="tm-item">
                    <i class="fas fa-truck" style="color:var(--gold);"></i>
                    @if ($hasTopText)<span>{{ $topbarText }}</span>@endif
                    @if ($hasTopText && $hasTopLoc)<span class="tm-dot">·</span>@endif
                    @if ($hasTopLoc)<span>{{ $topbarLoc }}</span>@endif
                </span>
            </div>
        </div>
    @endif

    {{-- ═════════════════ Header ═════════════════ --}}
    <header class="sticky top-0 z-40 bg-white/85 backdrop-blur-md border-b border-gray-200/60"
            style="-webkit-backdrop-filter:blur(12px);">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center gap-4">
            {{-- Left: hamburger (mobile) + logo --}}
            <div class="flex items-center gap-3 flex-1">
                <button @click="mobileNavOpen = true" class="lg:hidden text-gray-700 text-xl"><i class="fas fa-bars"></i></button>

                <a href="{{ route('shop.home') }}" class="flex items-center group" aria-label="Salal Collection home">
                    <img src="{{ asset('assets/images/brand/almufeed-traders.png') }}"
                         alt="SALAL COLLECTION"
                         class="h-14 sm:h-16 w-auto transition group-hover:scale-105">
                </a>
            </div>

            @php
                /* All categories from every branch, deduplicated by slug, with product counts.
                   Shown in the hover-mega-menu in the header. */
                $allShopCategories = \Illuminate\Support\Facades\Cache::remember('shop:nav-cats', 300, function () {
                    return \App\Models\Category::onWebsite()
                        ->withCount(['products' => fn ($q) => $q->where('is_active', true)->where('show_on_website', true)])
                        ->orderByDesc('is_featured')
                        ->orderBy('sort_order')->orderBy('name')
                        ->get()
                        ->unique(fn ($c) => $c->slug ?: \Illuminate\Support\Str::slug($c->name))
                        ->values();
                });
                $featuredCats = $allShopCategories->where('is_featured', true)->take(4);
            @endphp

            {{-- Center: nav (tabs centered between logo and actions) --}}
            <nav class="hidden lg:flex items-center justify-center gap-8 text-sm text-gray-700 flex-shrink-0">
                <a href="{{ route('shop.home') }}"    class="nav-link {{ request()->routeIs('shop.home') ? 'active' : '' }}">Home</a>
                <a href="{{ route('shop.catalog') }}" class="nav-link {{ request()->routeIs('shop.catalog') ? 'active' : '' }}">Shop</a>

                {{-- ─── Categories mega-menu (hover) ─── --}}
                <div class="relative" x-data="{ open: false, t: null }"
                     @mouseenter="clearTimeout(t); open = true"
                     @mouseleave="t = setTimeout(() => open = false, 150)">
                    <button class="nav-link inline-flex items-center gap-1.5 {{ request()->routeIs('shop.category') ? 'active' : '' }}">
                        Categories
                        <i class="fas fa-chevron-down text-[10px]" :class="open ? 'rotate-180' : ''" style="transition:transform .2s;"></i>
                    </button>

                    {{-- Invisible bridge to prevent the menu from closing when the cursor moves between trigger and panel --}}
                    <div x-show="open" x-cloak class="absolute left-0 right-0 top-full h-3"></div>

                    <div x-show="open" x-cloak
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-2"
                         class="absolute left-1/2 -translate-x-1/2 top-full mt-3 w-[680px] max-w-[92vw] bg-white rounded-2xl shadow-2xl border border-gray-100 z-40 overflow-hidden">
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-1 p-3 max-h-[60vh] overflow-y-auto">
                            @foreach ($allShopCategories as $cat)
                                <a href="{{ route('shop.category', $cat->slug) }}"
                                   class="group flex items-center gap-3 p-3 rounded-xl hover:bg-blue-50 transition">
                                    <span class="w-10 h-10 rounded-lg bg-gray-100 group-hover:bg-blue-100 transition overflow-hidden flex items-center justify-center flex-shrink-0">
                                        @if ($cat->photo)
                                            <img src="{{ shop_image($cat->photo) }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-folder text-gray-400 group-hover:text-blue-600 text-sm"></i>
                                        @endif
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-semibold text-gray-800 group-hover:text-blue-700 truncate text-sm">{{ $cat->name }}</div>
                                        <div class="text-[11px] text-gray-400">{{ $cat->products_count }} {{ \Str::plural('product', $cat->products_count) }}</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ route('shop.catalog') }}"
                           class="block px-5 py-3 text-center text-sm font-semibold border-t border-gray-100 text-gray-700 hover:bg-gray-50 transition">
                            View all products <i class="fas fa-arrow-right text-xs ml-1"></i>
                        </a>
                    </div>
                </div>
            </nav>

            {{-- Right: actions (search, track, account, cart) --}}
            <div class="flex items-center gap-2 flex-1 justify-end">
                <button @click="searchOpen = !searchOpen" class="w-10 h-10 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-700"><i class="fas fa-search"></i></button>

                <a href="{{ route('shop.track') }}" class="hidden sm:inline-flex w-10 h-10 rounded-full hover:bg-gray-100 items-center justify-center text-gray-700" title="Track order"><i class="fas fa-truck"></i></a>
                @auth('customer')
                    <a href="{{ route('shop.wishlist') }}" class="hidden sm:inline-flex w-10 h-10 rounded-full hover:bg-gray-100 items-center justify-center text-gray-700" title="Wishlist"><i class="fas fa-heart"></i></a>

                    {{-- Account dropdown with Sign out --}}
                    <div class="relative hidden sm:block" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open"
                                class="inline-flex items-center gap-2 px-2 py-1.5 rounded-full hover:bg-gray-100 text-gray-700"
                                :class="open ? 'bg-gray-100' : ''" title="My account">
                            <span class="w-8 h-8 rounded-full text-white font-bold flex items-center justify-center text-xs"
                                  style="background:linear-gradient(135deg,var(--brand-navy),var(--brand-cyan));">
                                {{ strtoupper(substr(auth('customer')->user()->name ?? 'U', 0, 1)) }}
                            </span>
                            <i class="fas fa-chevron-down text-[10px] text-gray-500" :class="open ? 'rotate-180' : ''" style="transition:transform .2s;"></i>
                        </button>
                        <div x-show="open" x-cloak
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="absolute right-0 mt-2 w-60 bg-white rounded-2xl shadow-2xl border border-gray-100 py-2 z-50">
                            <div class="px-4 py-3 border-b border-gray-100">
                                <div class="text-[11px] uppercase tracking-widest text-gray-400">Signed in as</div>
                                <div class="font-bold text-gray-900 truncate">{{ auth('customer')->user()->name }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ auth('customer')->user()->email }}</div>
                            </div>
                            <a href="{{ route('shop.account') }}"          class="px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"><i class="fas fa-user-circle text-gray-400 w-4"></i> My account</a>
                            <a href="{{ route('shop.account.orders') }}"   class="px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"><i class="fas fa-receipt text-gray-400 w-4"></i> My orders</a>
                            <a href="{{ route('shop.account.statement') }}" class="px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"><i class="fas fa-file-invoice text-gray-400 w-4"></i> Statement &amp; khata</a>
                            <a href="{{ route('shop.wishlist') }}"         class="px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"><i class="fas fa-heart text-gray-400 w-4"></i> Wishlist</a>
                            <a href="{{ route('shop.account.profile') }}"  class="px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"><i class="fas fa-pen text-gray-400 w-4"></i> Edit profile</a>
                            <a href="{{ route('shop.account.password') }}" class="px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"><i class="fas fa-lock text-gray-400 w-4"></i> Change password</a>
                            <div class="border-t border-gray-100 mt-2 pt-2">
                                <form method="POST" action="{{ route('shop.logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-red-50 text-red-600 flex items-center gap-2">
                                        <i class="fas fa-sign-out-alt text-red-400 w-4"></i> Sign out
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @else
                    <a href="{{ route('shop.login') }}" class="hidden sm:inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user text-xs"></i> Sign in
                    </a>
                @endauth

                <button @click="miniCartOpen = true" class="relative w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-800" title="Cart">
                    <i class="fas fa-shopping-bag"></i>
                    <span x-show="cartCount > 0" x-cloak x-text="cartCount" class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-amber-400 text-[10px] font-bold text-gray-900 flex items-center justify-center"></span>
                </button>
            </div>
        </div>

        {{-- Search overlay --}}
        <div x-show="searchOpen" x-cloak x-transition.opacity class="border-t border-gray-200/60 bg-white/95 backdrop-blur">
            <form action="{{ route('shop.search') }}" method="GET" class="max-w-3xl mx-auto px-4 py-4 flex items-center gap-3">
                <i class="fas fa-search text-gray-400"></i>
                <input type="text" name="q" value="{{ request('q') }}" autofocus placeholder="Search products, brands, categories..."
                       class="flex-1 bg-transparent outline-none text-base">
                <button type="button" @click="searchOpen = false" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
            </form>
        </div>
    </header>

    {{-- ═════════════════ Flash messages ═════════════════ --}}
    @include('shop.partials.flash')

    {{-- ═════════════════ Page content ═════════════════ --}}
    <main class="min-h-[60vh]">
        @yield('content')
    </main>

    {{-- ═════════════════ Footer ═════════════════ --}}
    <footer class="{{ request()->routeIs('shop.home') ? '' : 'mt-20' }} bg-gray-900 text-gray-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
            <div>
                <img src="{{ asset('assets/images/brand/almufeed-traders.png') }}"
                     alt="SALAL COLLECTION"
                     class="h-12 mb-4 brightness-0 invert opacity-90"
                     style="max-width:200px;">
                <p class="text-sm text-gray-400 leading-relaxed">Quality and affordability you can trust. {{ setting('site_address', 'PanjGirain, Tehsil Darya Khan, District Bhakkar.') }}</p>
                <div class="text-xs text-gray-500 mt-3 space-y-1">
                    <div><i class="fas fa-phone mr-2"></i> {{ setting('site_phone', '+92 300 7951919') }}</div>
                    <div><i class="fas fa-envelope mr-2"></i> {{ setting('site_email', 'Amt7212@gmail.com') }}</div>
                    @if (setting('site_whatsapp'))
                        <div><i class="fab fa-whatsapp mr-2"></i>
                            <a href="https://wa.me/{{ preg_replace('/\D+/', '', setting('site_whatsapp')) }}" target="_blank" rel="noopener" class="hover:text-white">{{ setting('site_whatsapp') }}</a>
                        </div>
                    @endif
                </div>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm uppercase tracking-wider">Shop</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('shop.catalog') }}" class="hover:text-white">All products</a></li>
                    @foreach ($featuredCats ?? collect() as $cat)
                        <li><a href="{{ route('shop.category', $cat->slug) }}" class="hover:text-white">{{ $cat->name }}</a></li>
                    @endforeach
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm uppercase tracking-wider">Help</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('shop.track') }}" class="hover:text-white">Track an order</a></li>
                    <li><a href="{{ route('shop.about') }}" class="hover:text-white">About us</a></li>
                    <li><a href="{{ route('shop.contact') }}" class="hover:text-white">Contact</a></li>
                    <li><a href="{{ route('shop.returns') }}" class="hover:text-white">Returns &amp; refunds</a></li>
                    <li><a href="{{ route('shop.privacy') }}" class="hover:text-white">Privacy policy</a></li>
                    <li><a href="{{ route('shop.terms') }}" class="hover:text-white">Terms</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm uppercase tracking-wider">Stay in touch</h4>
                <p class="text-sm text-gray-400 mb-3">New arrivals and offers, straight to your inbox.</p>
                <form class="flex gap-2">
                    <input type="email" placeholder="you@example.com" class="flex-1 px-3 py-2 rounded-lg bg-gray-800 border border-gray-700 text-sm focus:ring-2 focus:ring-amber-400 focus:border-amber-400">
                    <button type="submit" class="btn btn-primary !py-2 !px-4 !text-xs">Join</button>
                </form>
                @php
                    $socials = array_filter([
                        ['url' => setting('social_facebook'),  'icon' => 'fa-facebook-f', 'label' => 'Facebook'],
                        ['url' => setting('social_instagram'), 'icon' => 'fa-instagram',  'label' => 'Instagram'],
                        ['url' => setting('social_whatsapp') ? 'https://wa.me/' . preg_replace('/\D+/', '', setting('social_whatsapp')) : null, 'icon' => 'fa-whatsapp', 'label' => 'WhatsApp'],
                        ['url' => setting('social_tiktok'),    'icon' => 'fa-tiktok',     'label' => 'TikTok'],
                        ['url' => setting('social_x'),         'icon' => 'fa-x-twitter',  'label' => 'X'],
                        ['url' => setting('social_youtube'),   'icon' => 'fa-youtube',    'label' => 'YouTube'],
                    ], fn ($s) => !empty($s['url']));
                @endphp
                @if (count($socials))
                    <div class="flex items-center flex-wrap gap-3 mt-4 text-gray-400">
                        @foreach ($socials as $s)
                            <a href="{{ $s['url'] }}" target="_blank" rel="noopener" aria-label="{{ $s['label'] }}"
                               class="w-9 h-9 rounded-full bg-gray-800 hover:bg-blue-600 hover:text-white flex items-center justify-center transition"><i class="fab {{ $s['icon'] }}"></i></a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
        <div class="border-t border-gray-800 py-5 text-center text-xs text-gray-500">
            &copy; {{ now()->year }} SALAL COLLECTION. All rights reserved.
        </div>
    </footer>

    {{-- ═════════════════ Mobile nav drawer ═════════════════ --}}
    <div class="drawer-overlay" :class="mobileNavOpen ? 'open' : ''" @click="mobileNavOpen = false"></div>
    <div class="drawer" :class="mobileNavOpen ? 'open' : ''" style="max-width:320px;">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <span class="font-bold text-gray-900">Menu</span>
            <button @click="mobileNavOpen = false" class="text-gray-400 hover:text-gray-700"><i class="fas fa-times"></i></button>
        </div>
        <nav class="flex-1 overflow-y-auto p-5 space-y-2 text-sm" x-data="{ catsOpen: false }">
            <a href="{{ route('shop.home') }}"     class="block py-2 hover:text-blue-700 font-semibold">Home</a>
            <a href="{{ route('shop.catalog') }}"  class="block py-2 hover:text-blue-700 font-semibold">Shop</a>

            <button type="button" @click="catsOpen = !catsOpen"
                    class="w-full flex items-center justify-between py-2 hover:text-blue-700 font-semibold">
                <span>Categories</span>
                <i class="fas fa-chevron-down text-[10px] text-gray-400" :class="catsOpen ? 'rotate-180' : ''" style="transition:transform .2s;"></i>
            </button>
            <div x-show="catsOpen" x-cloak
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="pl-3 space-y-1 border-l border-gray-200">
                @foreach ($allShopCategories ?? collect() as $cat)
                    <a href="{{ route('shop.category', $cat->slug) }}" class="block py-1.5 text-gray-600 hover:text-blue-700">
                        {{ $cat->name }}
                        <span class="text-[10px] text-gray-400">({{ $cat->products_count ?? 0 }})</span>
                    </a>
                @endforeach
            </div>

            <hr class="border-gray-200 my-3">
            @auth('customer')
                <a href="{{ route('shop.account') }}" class="block py-2"><i class="fas fa-user mr-2 text-gray-400"></i> My account</a>
                <a href="{{ route('shop.wishlist') }}" class="block py-2"><i class="fas fa-heart mr-2 text-gray-400"></i> Wishlist</a>
                <form method="POST" action="{{ route('shop.logout') }}">
                    @csrf
                    <button class="block py-2 text-red-600"><i class="fas fa-sign-out-alt mr-2"></i> Sign out</button>
                </form>
            @else
                <a href="{{ route('shop.login') }}"    class="block py-2"><i class="fas fa-sign-in-alt mr-2 text-gray-400"></i> Sign in</a>
                <a href="{{ route('shop.register') }}" class="block py-2"><i class="fas fa-user-plus mr-2 text-gray-400"></i> Create account</a>
            @endauth
            <hr class="border-gray-200 my-3">
            <a href="{{ route('shop.track') }}"   class="block py-2"><i class="fas fa-truck mr-2 text-gray-400"></i> Track an order</a>
            <a href="{{ route('shop.about') }}"   class="block py-2">About</a>
            <a href="{{ route('shop.contact') }}" class="block py-2">Contact</a>
        </nav>
    </div>

    {{-- ═════════════════ Mini cart drawer (uses body's x-data scope) ═════════════════ --}}
    <div class="drawer-overlay" :class="miniCartOpen ? 'open' : ''" @click="miniCartOpen = false"></div>
    <div class="drawer" :class="miniCartOpen ? 'open' : ''">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <span class="font-bold text-gray-900 flex items-center gap-2"><i class="fas fa-shopping-cart"></i> Your cart <span class="text-xs text-gray-500" x-text="'(' + cartItems.length + ')'"></span></span>
            <button type="button" @click="miniCartOpen = false" class="text-gray-400 hover:text-gray-700 w-9 h-9 rounded-full hover:bg-gray-100 flex items-center justify-center"><i class="fas fa-times"></i></button>
        </div>
        <div class="flex-1 overflow-y-auto p-5">
            <template x-if="cartLoading">
                <div class="space-y-3">
                    <div class="skel h-20"></div>
                    <div class="skel h-20"></div>
                </div>
            </template>
            <template x-if="!cartLoading && cartItems.length === 0">
                <div class="text-center py-12 text-gray-500">
                    <i class="fas fa-shopping-bag text-4xl text-gray-300 mb-3 block"></i>
                    <p class="font-semibold">Your cart is empty</p>
                    <p class="text-xs mt-1">Add some beautiful pieces to it.</p>
                    <a href="{{ route('shop.catalog') }}" @click="miniCartOpen = false" class="btn btn-dark mt-4 !text-xs">Start shopping</a>
                </div>
            </template>
            <template x-if="!cartLoading && cartItems.length > 0">
                <div class="space-y-3">
                    <template x-for="it in cartItems" :key="it.id">
                        <div class="flex gap-3 p-3 rounded-xl border border-gray-100 hover:border-gray-200 bg-white">
                            <img :src="it.image" alt="" class="w-16 h-20 object-cover rounded-lg" style="background:#f5f1e8;">
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold text-gray-800 truncate" x-text="it.name"></div>
                                <div class="text-[11px] text-gray-500 mt-0.5" x-text="'Qty ' + it.qty"></div>
                                <div class="text-sm font-bold mt-1" style="color:var(--brand-cyan);" x-text="'Rs. ' + (it.qty * it.unit_price).toLocaleString()"></div>
                            </div>
                            <button type="button" @click="removeFromCart(it.id)" class="text-gray-300 hover:text-blue-500 self-start"><i class="fas fa-times-circle"></i></button>
                        </div>
                    </template>
                </div>
            </template>
        </div>
        <div class="border-t border-gray-200 p-5" x-show="cartItems.length > 0">
            <div class="flex items-center justify-between mb-4">
                <span class="text-gray-500 text-sm">Subtotal</span>
                <span class="font-bold text-lg" style="color:var(--brand-navy);" x-text="'Rs. ' + cartSubtotal.toLocaleString()"></span>
            </div>
            <a href="{{ route('shop.cart') }}" class="btn btn-ghost btn-block mb-2">View cart</a>
            <a href="{{ route('shop.checkout') }}" class="btn btn-primary btn-block">Checkout <i class="fas fa-arrow-right text-xs"></i></a>
        </div>
    </div>

    {{-- ═════════════════ Floating WhatsApp button ═════════════════ --}}
    @php $waFloat = wa_link(shop_whatsapp_number() ?: setting('site_phone'), 'Assalam-o-Alaikum! I have a question about your products.'); @endphp
    @if ($waFloat)
        <a href="{{ $waFloat }}" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"
           class="fixed left-5 bottom-5 z-[150] flex items-center justify-center w-14 h-14 rounded-full text-white shadow-2xl transition hover:scale-110"
           style="background:#25D366; box-shadow:0 12px 30px -8px rgba(37,211,102,.6);">
            <i class="fab fa-whatsapp text-3xl"></i>
            <span class="absolute inline-flex h-full w-full rounded-full opacity-40 animate-ping" style="background:#25D366;"></span>
        </a>
    @endif

    {{-- ═════════════════ Toast stack ═════════════════ --}}
    <div class="toast-stack" id="toastStack"></div>

    @stack('scripts')

    <script>
        // ── Storefront init (animations + lazy images + link prefetch) ───
        // Only adds the .js-reveal opacity-hide-then-fade-in if IO is supported,
        // so if anything fails the page remains visible.
        window.initStorefront = function (root) {
            if (!('IntersectionObserver' in window)) return;
            document.documentElement.classList.add('js-reveal');

            const io = new IntersectionObserver((es) => es.forEach(e => {
                if (e.isIntersecting) { e.target.classList.add('in'); io.unobserve(e.target); }
            }), { threshold: 0.12 });
            document.querySelectorAll('.reveal, .reveal-stagger').forEach(el => io.observe(el));

            // Force-mark anything already in viewport as visible immediately
            requestAnimationFrame(() => {
                document.querySelectorAll('.reveal, .reveal-stagger').forEach(el => {
                    const r = el.getBoundingClientRect();
                    if (r.top < window.innerHeight && r.bottom > 0) el.classList.add('in');
                });
            });

            // Lazy images
            const li = new IntersectionObserver((es, obs) => es.forEach(e => {
                if (e.isIntersecting) {
                    const i = e.target;
                    if (i.dataset.src) i.src = i.dataset.src;
                    i.addEventListener('load', () => i.classList.add('loaded'));
                    obs.unobserve(i);
                }
            }), { rootMargin: '200px' });
            document.querySelectorAll('img.lazy[data-src]').forEach(i => li.observe(i));

            // Prefetch on hover
            document.body.addEventListener('mouseover', (e) => {
                const a = e.target.closest('a[href]');
                if (!a || a.dataset.prefetched) return;
                try {
                    const u = new URL(a.href, location.href);
                    if (u.origin !== location.origin) return;
                    a.dataset.prefetched = '1';
                    const l = document.createElement('link'); l.rel = 'prefetch'; l.href = a.href; document.head.appendChild(l);
                } catch (err) {}
            });
        };

        // ── Hard fallback: after 2s, force-show everything no matter what ──
        // Guarantees content is visible even if IO never fires.
        setTimeout(() => {
            document.querySelectorAll('.reveal, .reveal-stagger').forEach(el => el.classList.add('in'));
        }, 2000);

        // ── Toast helper ─────────────────────────────────────────────────
        window.toast = function (msg, type = 'info') {
            const stack = document.getElementById('toastStack');
            const t = document.createElement('div');
            t.className = 'toast ' + (type === 'success' ? 'success' : type === 'error' ? 'error' : '');
            const icon = type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-circle-exclamation' : 'fa-circle-info';
            const color = type === 'success' ? '#059669' : type === 'error' ? '#dc2626' : '#1c9fd8';
            t.innerHTML = `<i class="fas ${icon} text-base mt-0.5" style="color:${color};"></i><div class="flex-1 text-gray-700">${msg}</div>`;
            stack.appendChild(t);
            requestAnimationFrame(() => t.classList.add('show'));
            setTimeout(() => { t.classList.remove('show'); setTimeout(() => t.remove(), 350); }, 3500);
        };

        // ── Add-to-cart helper (used from product cards / detail page) ───
        // Reaches into the body's Alpine scope to bump the cart count and
        // open / refresh the mini-cart drawer.
        window.addToCart = async function (productId, qty = 1, opts = {}) {
            const fd = new FormData();
            fd.append('product_id', productId);
            fd.append('qty', qty);
            if (opts.size)  fd.append('size',  opts.size);
            if (opts.color) fd.append('color', opts.color);
            try {
                const res = await fetch('{{ route('shop.cart.add') }}', {
                    method: 'POST', body: fd,
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (!res.ok || !data.ok) { window.toast(data.message || 'Could not add', 'error'); return false; }
                window.toast(data.message || 'Added to cart', 'success');

                // Update body's Alpine state (cartCount + reload mini-cart)
                const bodyData = window.Alpine ? window.Alpine.$data(document.body) : null;
                if (bodyData) {
                    bodyData.cartCount = data.cart_count;
                    if (typeof bodyData.loadCart === 'function') bodyData.loadCart();
                    if (opts.openDrawer !== false) bodyData.miniCartOpen = true;
                }
                return true;
            } catch (e) { window.toast('Network error', 'error'); return false; }
        };

        // ── Wishlist toggle helper ───────────────────────────────────────
        window.toggleWishlist = async function (productId, btn) {
            try {
                const res = await fetch('{{ route('shop.wishlist.toggle', ['product' => 'PRODUCTID']) }}'.replace('PRODUCTID', productId), {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json'}
                });
                if (res.status === 401) { window.location = '{{ route('shop.login') }}'; return; }
                const data = await res.json();
                if (data.ok) {
                    btn?.classList.toggle('text-blue-500', data.in_wishlist);
                    btn?.querySelector('i')?.classList.toggle('fas', data.in_wishlist);
                    btn?.querySelector('i')?.classList.toggle('far', !data.in_wishlist);
                    window.toast(data.in_wishlist ? 'Added to wishlist' : 'Removed from wishlist', 'success');
                }
            } catch(e) { window.toast('Network error', 'error'); }
        };

        // ── Buy now: add to cart silently, then jump straight to checkout ──
        window.buyNow = async function (productId, qty = 1) {
            const ok = await window.addToCart(productId, qty, { openDrawer: false });
            if (ok) window.location = '{{ route('shop.checkout') }}';
        };

        // ── Copy text to clipboard (name / description / link) ───────────
        window.copyText = async function (text, msg = 'Copied to clipboard') {
            try {
                await navigator.clipboard.writeText(text);
                window.toast(msg, 'success');
            } catch (e) {
                // Fallback for older / insecure contexts
                const ta = document.createElement('textarea');
                ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
                document.body.appendChild(ta); ta.select();
                try { document.execCommand('copy'); window.toast(msg, 'success'); }
                catch (_) { window.toast('Could not copy', 'error'); }
                ta.remove();
            }
        };

        // ── Share a product (native share sheet, else copy the link) ─────
        window.shareProduct = async function (url, title) {
            if (navigator.share) {
                try { await navigator.share({ title: title, url: url }); } catch (e) {}
            } else {
                window.copyText(url, 'Link copied — share it anywhere');
            }
        };
    </script>
</body>
</html>
