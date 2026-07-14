@extends('shop.layouts.app')
@section('title', 'Create account')

@section('content')
<section class="py-16 sm:py-20">
    <div class="max-w-md mx-auto px-4 reveal">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl p-8">
            <div class="text-center mb-6">
                <img src="{{ asset('assets/images/brand/almufeed-traders.png') }}" alt="SALAL COLLECTION" class="h-10 mx-auto mb-4">
                <h1 class="display text-2xl font-bold">Create your account</h1>
                <p class="text-gray-500 text-sm mt-1">Join Salal Collection Traders today</p>
            </div>

            <form method="POST" action="{{ route('shop.register.post') }}" x-data="{ show: false }" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Full name</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Email</label>
                    <input type="email" name="email" required value="{{ old('email') }}"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+92 300 1234567"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Password</label>
                    <div style="position:relative;">
                        <input :type="show ? 'text' : 'password'" name="password" required minlength="8"
                               style="padding-right:42px;"
                               class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <button type="button" @click="show = !show" tabindex="-1"
                                style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:#94a3b8;">
                            <i class="fas" :class="show ? 'fa-eye-slash' : 'fa-eye'"></i>
                        </button>
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">Min 8 characters</p>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Confirm password</label>
                    <input type="password" name="password_confirmation" required minlength="8"
                           class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <button type="submit" class="btn btn-dark btn-block"><i class="fas fa-user-plus"></i> Create account</button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-6">
                Already have an account? <a href="{{ route('shop.login') }}" class="font-semibold" style="color:var(--brand-cyan);">Sign in</a>
            </p>
        </div>
    </div>
</section>
@endsection
