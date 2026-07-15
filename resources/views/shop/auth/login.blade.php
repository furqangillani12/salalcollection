@extends('shop.layouts.app')
@section('title', 'Sign in')

@section('content')
<section class="py-16 sm:py-20">
    <div class="max-w-md mx-auto px-4 reveal">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl p-8">
            <div class="text-center mb-6">
                <img src="{{ asset('assets/images/brand/almufeed-traders.png') }}" alt="SALAL COLLECTION" class="h-10 mx-auto mb-4">
                <h1 class="display text-2xl font-bold">Welcome back</h1>
                <p class="text-gray-500 text-sm mt-1">Sign in to your account</p>
            </div>

            <form method="POST" action="{{ route('shop.login.post') }}" x-data="{ show: false }" class="space-y-4">
                @csrf
                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-lg px-3 py-2">
                        {{ $errors->first() }}
                    </div>
                @endif
                <div>
                    <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Email</label>
                    <div style="position:relative;">
                        <i class="fas fa-envelope" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;pointer-events:none;"></i>
                        <input type="text" name="login" required autofocus value="{{ old('login') }}"
                               placeholder="you@example.com"
                               style="padding-left:40px;"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Password</label>
                    <div style="position:relative;">
                        <i class="fas fa-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#94a3b8;font-size:13px;pointer-events:none;"></i>
                        <input :type="show ? 'text' : 'password'" name="password" required
                               style="padding-left:40px;padding-right:42px;"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <button type="button" @click="show = !show" tabindex="-1"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;">
                            <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                </div>
                <label class="inline-flex items-center gap-2 text-sm text-gray-600 cursor-pointer select-none">
                    <input type="checkbox" name="remember" value="1" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    Remember me
                </label>

                <button type="submit" class="btn btn-dark btn-block"><i class="fas fa-sign-in-alt"></i> Sign in</button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Don't have an account? <a href="{{ route('shop.register') }}" class="font-semibold" style="color:var(--brand-cyan);">Create one</a>
            </p>
        </div>
        <p class="text-center text-xs text-gray-400 mt-4"><i class="fas fa-shield-halved"></i> Secure connection</p>
    </div>
</section>
@endsection
