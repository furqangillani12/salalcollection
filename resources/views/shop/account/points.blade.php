@extends('shop.layouts.app')
@section('title', 'Reward points')
@section('content')
<section class="py-10 sm:py-14">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <a href="{{ route('shop.account') }}" class="text-xs text-gray-500 hover:text-blue-700 inline-flex items-center gap-2 mb-2"><i class="fas fa-arrow-left"></i> Back to account</a>
        <h1 class="display text-3xl sm:text-4xl font-bold mb-1">Reward points</h1>

        <div class="rounded-2xl p-6 text-white my-6 reveal" style="background:linear-gradient(135deg,var(--brand-navy),var(--brand-cyan));">
            <div class="text-xs uppercase tracking-widest opacity-80">Your balance</div>
            <div class="text-4xl font-extrabold mt-1">🏆 {{ number_format($customer->loyalty_points ?? 0) }} points</div>
            @if (shop_point_value() > 0)
                <div class="text-sm font-semibold mt-1 opacity-95">Worth {{ shop_price(shop_points_to_rupees((int) ($customer->loyalty_points ?? 0))) }} — redeem at checkout.</div>
            @endif
            <p class="text-sm opacity-90 mt-2">Earn points on delivered orders and reviews. Photo, video and social-media reviews earn extra — share and let us know!</p>
        </div>

        <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden reveal">
            <div class="px-5 py-4 border-b border-gray-100 bg-gray-50 font-bold text-gray-800">History</div>
            @if ($transactions->count())
                <div class="divide-y divide-gray-100">
                    @foreach ($transactions as $txn)
                        <div class="flex items-center justify-between px-5 py-3">
                            <div>
                                <div class="font-semibold text-gray-800">{{ $txn->label }}</div>
                                @if ($txn->note)<div class="text-xs text-gray-500">{{ $txn->note }}</div>@endif
                                <div class="text-[11px] text-gray-400">{{ $txn->created_at->format('d M Y · h:i A') }}</div>
                            </div>
                            <span class="text-lg font-extrabold {{ $txn->points >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $txn->points >= 0 ? '+' : '' }}{{ $txn->points }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="px-5 py-4">{{ $transactions->links() }}</div>
            @else
                <div class="p-10 text-center text-gray-500">
                    <i class="fas fa-star text-3xl text-gray-300 mb-2 block"></i>
                    No points yet. Place an order or leave a review to start earning!
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
