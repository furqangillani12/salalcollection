<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salal Collection Point Of Sale</title>
    <meta name="description" content="Salal Collection Point Of Sale — modern POS, inventory, khata and multi-branch management.">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%230c1f3d'/><text x='16' y='22' text-anchor='middle' font-family='Inter,sans-serif' font-size='16' font-weight='800' fill='%23fbbf24'>A</text></svg>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite('resources/css/app.css')

    <style>
        /* Brand palette — sourced from the Salal Collection logo:
           - Logo blue:  #1f8fc1  (the M / arch)
           - Logo navy:  #0e1f3d  (Arabic text, stars)
           - Accent gold:#fbbf24  (warm contrast on blue) */
        :root {
            --brand-navy: #0c1f3d;
            --brand-blue: #1e3a8a;
            --brand-cyan: #0891b2;
            --brand-light:#1f8fc1;
            --gold:       #fbbf24;
        }
        html, body { font-family: 'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
        body { background:#f8fafc; color:#1f2937; -webkit-font-smoothing:antialiased; }

        /* Animated gradient background — navy → deep blue → cyan, matches logo */
        .hero-bg {
            background: linear-gradient(120deg, var(--brand-navy), var(--brand-blue), var(--brand-cyan), var(--brand-blue));
            background-size: 300% 300%;
            animation: gradientShift 18s ease infinite;
            position: relative;
            overflow: hidden;
        }
        @keyframes gradientShift {
            0%, 100% { background-position: 0% 50%; }
            50%      { background-position: 100% 50%; }
        }
        .hero-bg::before {
            content: '';
            position: absolute; inset: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(251,191,36,.18), transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(31,143,193,.25), transparent 45%);
            pointer-events: none;
        }
        .hero-pattern {
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
            background-size: 48px 48px;
            mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
            -webkit-mask-image: radial-gradient(ellipse at center, black 30%, transparent 80%);
            pointer-events: none;
        }

        /* Floating logo — bright white card so the blue logo pops */
        .logo-card {
            background: rgba(255,255,255,.96);
            border: 1px solid rgba(255,255,255,.5);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            box-shadow: 0 25px 50px -12px rgba(12,31,61,.5), 0 0 0 6px rgba(255,255,255,.08);
            animation: floaty 6s ease-in-out infinite;
        }
        @keyframes floaty {
            0%,100% { transform: translateY(0); }
            50%     { transform: translateY(-10px); }
        }

        /* Primary button — gold (warm contrast on blue) */
        .btn-primary {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            box-shadow: 0 10px 25px -10px rgba(251,191,36,.6);
            transition: transform .2s ease, box-shadow .2s ease;
        }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 14px 30px -10px rgba(251,191,36,.75); }
        .btn-ghost {
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.25);
            backdrop-filter: blur(8px);
            transition: all .2s ease;
        }
        .btn-ghost:hover { background: rgba(255,255,255,.15); }

        /* Feature cards */
        .feature-card {
            background: white;
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            padding: 28px 24px;
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -20px rgba(8,145,178,.25);
            border-color: var(--brand-cyan);
        }
        .feature-icon {
            width: 56px; height: 56px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 14px;
            font-size: 22px;
            margin-bottom: 16px;
        }

        /* CTA band — navy → cyan */
        .cta-band {
            background: linear-gradient(135deg, var(--brand-navy), var(--brand-cyan));
            position: relative; overflow: hidden;
        }
        .cta-band::before {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(circle at 90% 30%, rgba(251,191,36,.25), transparent 50%);
        }

        @media (max-width: 640px) {
            .logo-card { padding: 16px !important; }
            .logo-card img { max-height: 90px !important; }
        }
    </style>
</head>
<body>

    {{-- ═══════════════════════════════ HERO ═══════════════════════════════ --}}
    <section class="hero-bg pt-16 sm:pt-20 pb-20 sm:pb-28 px-4 sm:px-6 lg:px-8 text-white">
        <div class="hero-pattern"></div>
        <div class="relative max-w-6xl mx-auto text-center">

            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-semibold mb-6"
                 style="background:rgba(251,191,36,.15);border:1px solid rgba(251,191,36,.3);color:#fde68a;">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                Multi-Branch Retail Management Platform
            </div>

            <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold tracking-tight mb-2 leading-tight">
                Salal Collection
            </h1>
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-extrabold tracking-tight mb-5 leading-tight" style="color:#fbbf24;">
                Point Of Sale
            </h2>
            <div class="flex justify-center mb-6">
                <span style="display:block;width:80px;height:3px;border-radius:9999px;background:linear-gradient(90deg,#fbbf24,transparent);"></span>
            </div>

            <p class="text-base sm:text-lg md:text-xl text-sky-100/90 max-w-2xl mx-auto mb-3">
                One platform to run every branch of your business.
            </p>
            <p class="text-sm sm:text-base text-sky-100/70 max-w-xl mx-auto mb-10">
                Complete POS, inventory, khata, payroll and accounting — all in one place, branch-aware out of the box.
            </p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                @auth
                    <a href="{{ route('admin.dashboard') }}"
                       class="btn-primary inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl text-gray-900 font-bold text-base w-full sm:w-auto">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                    <a href="{{ route('admin.pos.index') }}"
                       class="btn-ghost inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl text-white font-bold text-base w-full sm:w-auto">
                        <i class="fas fa-cash-register"></i> Open POS
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="btn-primary inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl text-gray-900 font-bold text-base w-full sm:w-auto">
                        <i class="fas fa-sign-in-alt"></i> Login to Dashboard
                    </a>
                    <a href="#features"
                       class="btn-ghost inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl text-white font-bold text-base w-full sm:w-auto">
                        <i class="fas fa-circle-info"></i> Learn More
                    </a>
                @endauth
            </div>

            {{-- Stats row --}}
            <div class="grid grid-cols-3 gap-4 sm:gap-8 max-w-3xl mx-auto mt-16 pt-10 border-t border-white/15">
                <div>
                    <div class="text-2xl sm:text-4xl font-extrabold" style="color:#fbbf24;">Multi</div>
                    <div class="text-[11px] sm:text-sm text-sky-100/80 mt-1">Branch Support</div>
                </div>
                <div>
                    <div class="text-2xl sm:text-4xl font-extrabold" style="color:#fbbf24;">24/7</div>
                    <div class="text-[11px] sm:text-sm text-sky-100/80 mt-1">Always Available</div>
                </div>
                <div>
                    <div class="text-2xl sm:text-4xl font-extrabold" style="color:#fbbf24;">100%</div>
                    <div class="text-[11px] sm:text-sm text-sky-100/80 mt-1">Khata Tracking</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════ FEATURES ═══════════════════════════════ --}}
    <section id="features" class="py-16 sm:py-24 px-4 sm:px-6 lg:px-8 bg-gray-50">
        <div class="max-w-7xl mx-auto">
            <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16">
                <span class="inline-block text-xs font-bold uppercase tracking-widest text-cyan-700 mb-3">Features</span>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-gray-900 mb-4">
                    Everything you need to run your business
                </h2>
                <p class="text-base text-gray-600">From the cash counter to the back office — one platform, every branch.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @php
                    $features = [
                        ['icon'=>'fa-cash-register','title'=>'POS Terminal','desc'=>'Lightning-fast checkout with barcode scan, tier pricing, weight-based delivery and thermal receipts.','bg'=>'#ecfdf5','color'=>'#059669'],
                        ['icon'=>'fa-store-alt','title'=>'Multi-Branch','desc'=>'Independent stock, sales and reports per branch — switch on the fly with a single account.','bg'=>'#eff6ff','color'=>'#2563eb'],
                        ['icon'=>'fa-book-open','title'=>'Khata / Credit','desc'=>'Pakistani-style customer credit ledger with limits, due dates, statements and overdue tracking.','bg'=>'#fef3c7','color'=>'#d97706'],
                        ['icon'=>'fa-boxes','title'=>'Inventory & Stock','desc'=>'Per-branch stock, low-stock alerts, audit logs, Excel import / export and barcode generation.','bg'=>'#f3e8ff','color'=>'#7c3aed'],
                        ['icon'=>'fa-money-bill-wave','title'=>'Cash In / Out','desc'=>'One screen for customer, supplier and ledger cash transactions with full double-entry.','bg'=>'#fee2e2','color'=>'#dc2626'],
                        ['icon'=>'fa-chart-line','title'=>'Reports & Analytics','desc'=>'Sales, profit-and-loss, top products, customer analysis — filter by date, branch and category.','bg'=>'#cffafe','color'=>'#0891b2'],
                    ];
                @endphp
                @foreach ($features as $f)
                    <div class="feature-card">
                        <div class="feature-icon" style="background:{{ $f['bg'] }};color:{{ $f['color'] }};">
                            <i class="fas {{ $f['icon'] }}"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-900 mb-2">{{ $f['title'] }}</h3>
                        <p class="text-sm text-gray-600 leading-relaxed">{{ $f['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════ CTA BAND ═══════════════════════════════ --}}
    <section class="cta-band py-16 sm:py-20 px-4 sm:px-6 lg:px-8 text-white">
        <div class="relative max-w-4xl mx-auto text-center">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full mb-5 border border-white/20"
                 style="background:rgba(251,191,36,.15);color:#fde68a;border-color:rgba(251,191,36,.3);">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                <span class="text-xs font-semibold uppercase tracking-widest">Salal Collection POS</span>
            </div>
            <h2 class="text-3xl sm:text-4xl md:text-5xl font-extrabold mb-4 leading-tight">
                Ready to manage your business better?
            </h2>
            <p class="text-base sm:text-lg text-sky-100 max-w-xl mx-auto mb-8">
                Sign in to access the dashboard, POS, inventory, khata and reports — all in one place.
            </p>
            @auth
                <a href="{{ route('admin.dashboard') }}"
                   class="btn-primary inline-flex items-center gap-2 px-8 py-4 rounded-xl text-gray-900 font-bold text-base">
                    <i class="fas fa-arrow-right-to-bracket"></i> Go to Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="btn-primary inline-flex items-center gap-2 px-8 py-4 rounded-xl text-gray-900 font-bold text-base">
                    <i class="fas fa-sign-in-alt"></i> Sign in to your account
                </a>
            @endauth
        </div>
    </section>

    {{-- ═══════════════════════════════ FOOTER ═══════════════════════════════ --}}
    <footer class="bg-gray-900 text-gray-400 py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg text-white font-extrabold"
                      style="background:linear-gradient(135deg,#0c1f3d,#0891b2);">A</span>
                <div>
                    <div class="font-bold text-white">Salal Collection Point Of Sale</div>
                    <div class="text-xs">Multi-branch retail management.</div>
                </div>
            </div>
            <div class="text-xs text-center sm:text-right">
                &copy; {{ now()->year }} Salal Collection POS. All rights reserved.<br>
                <span class="text-gray-500">www.almufeed.com.pk</span>
            </div>
        </div>
    </footer>

</body>
</html>
