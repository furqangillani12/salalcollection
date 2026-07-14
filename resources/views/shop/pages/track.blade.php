@extends('shop.layouts.app')
@section('title', 'Track Order')
@section('content')
<section class="py-16 sm:py-20">
    <div class="max-w-md mx-auto px-4 reveal">
        <div class="bg-white rounded-3xl border border-gray-100 shadow-xl p-8">
            <div class="text-center mb-6">
                <div class="w-14 h-14 rounded-2xl mx-auto flex items-center justify-center mb-4"
                     style="background:linear-gradient(135deg,#e8f1fb,#d6ecfa);color:var(--brand-navy);">
                    <i class="fas fa-truck text-xl"></i>
                </div>
                <h1 class="display text-2xl font-bold">Track your order</h1>
                <p class="text-gray-500 text-sm mt-1">No login needed. Just your order number and email or phone.</p>
            </div>

            <form method="POST" action="{{ route('shop.track.find') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Order number</label>
                    <input type="text" name="order_number" required value="{{ old('order_number') }}"
                           placeholder="ASM1234" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-700 mb-1.5 block">Email or phone</label>
                    <input type="text" name="contact" required value="{{ old('contact') }}"
                           placeholder="you@example.com or +92 300 1234567" class="w-full px-3 py-2.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <button type="submit" class="btn btn-dark btn-block"><i class="fas fa-magnifying-glass"></i> Track order</button>
            </form>

            @auth('customer')
                <p class="text-center text-xs text-gray-500 mt-6">
                    Logged in? <a href="{{ route('shop.account.orders') }}" class="font-semibold" style="color:var(--brand-cyan);">View all your orders</a>
                </p>
            @endauth
        </div>
    </div>
</section>
@endsection
