@extends('layouts.admin')

@section('title', 'Select Branch')

@section('content')
<style>
    .brand-hero {
        background: linear-gradient(135deg, #0c1f3d, #1e3a8a, #0891b2);
        position: relative; overflow: hidden;
    }
    .brand-hero::before {
        content:''; position:absolute; inset:0;
        background:
            radial-gradient(circle at 15% 20%, rgba(251,191,36,.18), transparent 45%),
            radial-gradient(circle at 85% 80%, rgba(31,143,193,.25), transparent 50%);
    }
    .brand-grid {
        position:absolute; inset:0;
        background-image:
            linear-gradient(rgba(255,255,255,.05) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255,255,255,.05) 1px, transparent 1px);
        background-size:36px 36px;
        mask-image:radial-gradient(ellipse at center, black 30%, transparent 80%);
        -webkit-mask-image:radial-gradient(ellipse at center, black 30%, transparent 80%);
    }
    .branch-card {
        background:#fff;
        border-radius:18px;
        border:1px solid #e5e7eb;
        padding:20px;
        text-align:left;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        cursor:pointer;
        width:100%;
    }
    .branch-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 35px -18px rgba(8,145,178,.35);
        border-color:#0891b2;
    }
    .branch-logo-wrap {
        width:64px; height:64px; border-radius:14px;
        background:#f1f5f9;
        display:flex; align-items:center; justify-content:center;
        flex-shrink:0; overflow:hidden;
        font-weight:800; color:#0c1f3d; font-size:18px;
        border:1px solid #e2e8f0;
    }
    .branch-logo-wrap img { width:100%; height:100%; object-fit:contain; padding:4px; }
    .all-card {
        background: linear-gradient(135deg, rgba(8,145,178,.06), rgba(251,191,36,.05));
        border:2px dashed #cbd5e1;
    }
    .all-card:hover { border-color:#0891b2; background: linear-gradient(135deg, rgba(8,145,178,.12), rgba(251,191,36,.08)); }
</style>

<div class="brand-hero text-white py-10 px-4 sm:px-6 -mx-3 sm:-mx-6 -mt-3 sm:-mt-6 mb-8 rounded-b-2xl">
    <div class="brand-grid"></div>
    <div style="position:relative;" class="max-w-5xl mx-auto">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="rounded-xl p-2"
                     style="background:rgba(255,255,255,.96); box-shadow:0 10px 25px -10px rgba(12,31,61,.5);">
                    <img src="{{ asset('assets/images/mufeed.png') }}" alt="Salal Collection" style="height:48px;width:auto;display:block;">
                </div>
                <div>
                    <div class="text-[11px] uppercase tracking-widest font-semibold" style="color:#fbbf24;">
                        <i class="fas fa-store mr-1"></i> Choose a branch
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold mt-1 leading-tight">
                        Welcome back, <span style="color:#fbbf24;">{{ auth()->user()->name }}</span>
                    </h1>
                    <p class="text-sm text-sky-100/80 mt-1">Select which branch you want to work with today.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-white"
                        style="background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.2); backdrop-filter:blur(8px); -webkit-backdrop-filter:blur(8px);">
                    <i class="fas fa-sign-out-alt"></i> Sign out
                </button>
            </form>
        </div>
    </div>
</div>

<div class="max-w-5xl mx-auto px-1">
    @if($branches->isEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 text-center text-amber-800">
            <i class="fas fa-triangle-exclamation text-2xl mb-2 block"></i>
            <p class="font-semibold">No active branches found.</p>
            <p class="text-sm mt-1">Ask an administrator to add a branch before you can continue.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($branches as $branch)
                <form method="POST" action="{{ route('admin.branch.store-selection') }}">
                    @csrf
                    <input type="hidden" name="branch_id" value="{{ $branch->id }}">
                    <button type="submit" class="branch-card">
                        <div class="flex items-start gap-4">
                            <div class="branch-logo-wrap">
                                @if($branch->logo)
                                    <img src="{{ asset('storage/' . $branch->logo) }}" alt="{{ $branch->name }}">
                                @else
                                    {{ strtoupper(substr($branch->code ?? $branch->name, 0, 2)) }}
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-bold text-gray-900 text-base truncate">{{ $branch->name }}</h3>
                                    @if($branch->code)
                                        <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full"
                                              style="background:#ecfeff;color:#0e7490;border:1px solid #a5f3fc;">
                                            {{ $branch->code }}
                                        </span>
                                    @endif
                                </div>
                                @if($branch->address)
                                    <p class="text-xs text-gray-500 mb-2 truncate flex items-center gap-1">
                                        <i class="fas fa-map-marker-alt text-gray-400"></i> {{ $branch->address }}
                                    </p>
                                @endif
                                <div class="flex items-center gap-4 text-xs">
                                    <span class="inline-flex items-center gap-1.5 text-emerald-700 font-semibold">
                                        <i class="fas fa-cart-shopping"></i> {{ $branch->orders_count ?? 0 }} orders
                                    </span>
                                    <span class="inline-flex items-center gap-1.5 text-indigo-700 font-semibold">
                                        <i class="fas fa-users"></i> {{ $branch->employees_count ?? 0 }} staff
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center self-stretch">
                                <span style="width:32px;height:32px;border-radius:9999px;background:#f1f5f9;color:#0891b2;display:inline-flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </span>
                            </div>
                        </div>
                    </button>
                </form>
            @endforeach

            @can('view all branches')
                <form method="POST" action="{{ route('admin.branch.store-selection') }}" class="sm:col-span-2">
                    @csrf
                    <input type="hidden" name="branch_id" value="all">
                    <button type="submit" class="branch-card all-card">
                        <div class="flex items-start gap-4">
                            <div class="branch-logo-wrap" style="background:#0c1f3d;color:#fbbf24;border-color:#1e3a8a;">
                                <i class="fas fa-globe text-xl"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-bold text-gray-900 text-base">All Branches</h3>
                                    <span class="text-[10px] font-bold uppercase px-2 py-0.5 rounded-full"
                                          style="background:#fef3c7;color:#92400e;border:1px solid #fde68a;">
                                        Combined view
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">View combined sales, stock and reports across every branch. Useful for owners and admins.</p>
                            </div>
                            <div class="flex items-center self-stretch">
                                <span style="width:32px;height:32px;border-radius:9999px;background:#fff;color:#0891b2;display:inline-flex;align-items:center;justify-content:center;border:1px solid #e2e8f0;">
                                    <i class="fas fa-arrow-right text-xs"></i>
                                </span>
                            </div>
                        </div>
                    </button>
                </form>
            @endcan
        </div>

        <p class="text-center text-xs text-gray-400 mt-8">
            <i class="fas fa-shield-halved mr-1"></i>
            You can switch branch any time from the top of the sidebar.
        </p>
    @endif
</div>
@endsection
