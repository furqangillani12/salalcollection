<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Salal Collection · Admin</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'><rect width='32' height='32' rx='8' fill='%230c1f3d'/><text x='16' y='22' text-anchor='middle' font-family='Inter,sans-serif' font-size='16' font-weight='800' fill='%23fbbf24'>A</text></svg>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root { --brand-navy:#0b3a30; --brand-blue:#0f6b58; --brand-cyan:#14846d; --brand-light:#1aa07f; --gold:#d4af37; }
        html, body { font-family:'Inter', system-ui, -apple-system, Segoe UI, Roboto, sans-serif; }
        body { -webkit-font-smoothing:antialiased; }

        .auth-shell { min-height:100vh; display:grid; grid-template-columns:1fr; }
        @media (min-width:1024px) { .auth-shell { grid-template-columns:1.05fr 1fr; } }

        /* Left brand panel */
        .brand-panel {
            position:relative; overflow:hidden; color:#fff;
            background:linear-gradient(120deg, var(--brand-navy), var(--brand-blue), var(--brand-cyan), var(--brand-blue));
            background-size:300% 300%;
            animation:gradientShift 18s ease infinite;
            padding:48px 32px;
            display:flex; flex-direction:column; justify-content:space-between;
        }
        @keyframes gradientShift {
            0%,100% { background-position:0% 50%; }
            50%     { background-position:100% 50%; }
        }
        .brand-panel::before {
            content:''; position:absolute; inset:0;
            background:
                radial-gradient(circle at 20% 20%, rgba(251,191,36,.18), transparent 40%),
                radial-gradient(circle at 80% 70%, rgba(31,143,193,.25), transparent 45%);
            pointer-events:none;
        }
        .brand-grid {
            position:absolute; inset:0;
            background-image:
                linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
            background-size:48px 48px;
            mask-image:radial-gradient(ellipse at center, black 30%, transparent 80%);
            -webkit-mask-image:radial-gradient(ellipse at center, black 30%, transparent 80%);
            pointer-events:none;
        }
        .logo-card {
            background:rgba(255,255,255,.96);
            border-radius:24px; padding:24px 32px;
            box-shadow:0 25px 50px -12px rgba(12,31,61,.5), 0 0 0 6px rgba(255,255,255,.08);
            display:inline-flex; align-items:center; justify-content:center;
            animation:floaty 6s ease-in-out infinite;
        }
        @keyframes floaty {
            0%,100% { transform:translateY(0); }
            50%     { transform:translateY(-8px); }
        }

        .feature-pill {
            background:rgba(255,255,255,.08);
            border:1px solid rgba(255,255,255,.18);
            backdrop-filter:blur(8px);
            -webkit-backdrop-filter:blur(8px);
        }

        /* Right form panel */
        .form-panel {
            background:#f8fafc;
            display:flex; align-items:center; justify-content:center;
            padding:32px 20px;
        }
        .form-card {
            background:#fff; border-radius:24px;
            box-shadow:0 25px 50px -12px rgba(0,0,0,.08), 0 0 0 1px rgba(0,0,0,.04);
            padding:32px;
            width:100%; max-width:440px;
        }

        .field-input {
            width:100%; padding:11px 14px; font-size:14px;
            border:1px solid #d1d5db; border-radius:10px;
            background:#fff; transition:border-color .15s ease, box-shadow .15s ease;
        }
        .field-input:focus { outline:none; border-color:var(--brand-cyan); box-shadow:0 0 0 3px rgba(8,145,178,.15); }

        .btn-primary {
            background:linear-gradient(135deg, #fbbf24, #f59e0b);
            color:#111827; font-weight:700; font-size:14px;
            padding:12px 16px; border-radius:10px; border:0;
            box-shadow:0 10px 25px -10px rgba(251,191,36,.55);
            transition:transform .15s ease, box-shadow .15s ease;
            cursor:pointer;
        }
        .btn-primary:hover { transform:translateY(-1px); box-shadow:0 14px 30px -10px rgba(251,191,36,.7); }

        @media (max-width:1023px) {
            .brand-panel { padding:32px 24px; min-height:280px; }
            .logo-card { padding:16px 24px; }
            .logo-card img { max-height:80px !important; }
        }
    </style>
</head>
<body class="text-gray-900 antialiased">

<div class="auth-shell">

    {{-- ───────────────── BRAND PANEL ───────────────── --}}
    <aside class="brand-panel">
        <div class="brand-grid"></div>

        <div style="position:relative;">
            <a href="/" class="inline-flex items-center gap-2 text-sm font-semibold text-white/80 hover:text-white">
                <i class="fas fa-arrow-left text-xs"></i> Back to home
            </a>
        </div>

        <div style="position:relative;" class="text-center max-w-md mx-auto py-8">

            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold mb-6"
                 style="background:rgba(251,191,36,.15); border:1px solid rgba(251,191,36,.3); color:#fde68a;">
                <span style="width:6px;height:6px;background:#fbbf24;border-radius:9999px;display:inline-block;"></span>
                Online Store Management
            </div>

            <img src="{{ asset('assets/images/brand/salal-collection.png') }}" alt="Salal Collection" style="width:110px;height:110px;object-fit:contain;margin:0 auto 10px;display:block;border-radius:9999px;box-shadow:0 8px 24px rgba(0,0,0,.25);">
            <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight leading-tight">
                Salal Collection
            </h1>
            <h2 class="text-xl sm:text-3xl font-extrabold tracking-tight leading-tight mt-1" style="color:#d4af37;">
                Admin Panel
            </h2>
            <div class="flex justify-center mt-4 mb-4">
                <span style="display:block;width:60px;height:3px;border-radius:9999px;background:linear-gradient(90deg,#d4af37,transparent);"></span>
            </div>
            <p class="text-sm sm:text-base text-sky-100/80 mt-3 max-w-sm mx-auto">
                Manage your online store — products, orders and reports — from one secure dashboard.
            </p>

            {{-- Feature highlights --}}
            <div class="grid grid-cols-3 gap-2 mt-8 max-w-sm mx-auto">
                <div class="feature-pill rounded-xl p-3 text-center">
                    <i class="fas fa-bag-shopping text-lg" style="color:#d4af37;"></i>
                    <div class="text-[10px] font-semibold mt-1 text-sky-100/90">Orders</div>
                </div>
                <div class="feature-pill rounded-xl p-3 text-center">
                    <i class="fas fa-boxes text-lg" style="color:#d4af37;"></i>
                    <div class="text-[10px] font-semibold mt-1 text-sky-100/90">Inventory</div>
                </div>
                <div class="feature-pill rounded-xl p-3 text-center">
                    <i class="fas fa-chart-bar text-lg" style="color:#d4af37;"></i>
                    <div class="text-[10px] font-semibold mt-1 text-sky-100/90">Reports</div>
                </div>
            </div>
        </div>

        <div style="position:relative;" class="text-center text-xs text-sky-100/60">
            &copy; {{ now()->year }} Salal Collection. All rights reserved.
        </div>
    </aside>

    {{-- ───────────────── FORM PANEL ───────────────── --}}
    <main class="form-panel">
        <div class="form-card">
            {{ $slot }}
        </div>
    </main>

</div>

</body>
</html>
