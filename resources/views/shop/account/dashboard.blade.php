@extends('shop.layouts.app')
@section('title', 'My Account')
@section('content')
<section class="py-10 sm:py-14">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Welcome --}}
        <div class="rounded-3xl p-8 sm:p-10 mb-8 text-white relative overflow-hidden reveal" style="background:linear-gradient(135deg,var(--brand-navy),var(--brand-cyan));">
            <div class="hero-pattern absolute inset-0"></div>
            <div class="relative">
                <div class="text-xs uppercase tracking-widest mb-2" style="color:var(--gold);">My account</div>
                <h1 class="display text-3xl sm:text-4xl font-bold">As-salamu alaykum, {{ $customer->name }}</h1>
                <p class="text-blue-100/80 mt-2 text-sm">Manage your orders, profile, and addresses from here.</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <div class="px-4 py-2 rounded-xl text-xs font-semibold" style="background:rgba(255,255,255,.12);backdrop-filter:blur(8px);">
                        <i class="fas fa-receipt mr-1"></i> {{ \App\Models\Order::where('customer_id', $customer->id)->count() }} orders
                    </div>
                    <div class="px-4 py-2 rounded-xl text-xs font-semibold" style="background:rgba(255,255,255,.12);backdrop-filter:blur(8px);">
                        <i class="fas fa-heart mr-1"></i> {{ \App\Models\Wishlist::where('customer_id', $customer->id)->count() }} in wishlist
                    </div>
                    @if (($customer->current_balance ?? 0) > 0)
                        <div class="px-4 py-2 rounded-xl text-xs font-semibold" style="background:rgba(41,171,226,.2);color:var(--gold);">
                            <i class="fas fa-book-open mr-1"></i> Khata: {{ shop_price($customer->current_balance) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Reward points banner --}}
        <a href="{{ route('shop.account.points') }}" class="block rounded-2xl p-5 mb-4 text-white reveal" style="background:linear-gradient(135deg,var(--brand-navy),var(--brand-cyan));">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-xs uppercase tracking-widest opacity-80">Reward points</div>
                    <div class="text-3xl font-extrabold mt-1">🏆 {{ number_format($customer->loyalty_points ?? 0) }}</div>
                </div>
                <span class="text-sm font-semibold opacity-90">View history <i class="fas fa-arrow-right text-xs"></i></span>
            </div>
        </a>

        {{-- Quick links --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 reveal-stagger">
            @foreach ([
                ['route'=>'shop.account.orders',   'icon'=>'fa-receipt',      'title'=>'My orders',  'desc'=>'View order history'],
                ['route'=>'shop.account.statement','icon'=>'fa-file-invoice', 'title'=>'Statement',  'desc'=>'Khata & history'],
                ['route'=>'shop.account.points',  'icon'=>'fa-star',         'title'=>'Points',     'desc'=>'Reward points'],
                ['route'=>'shop.wishlist',        'icon'=>'fa-heart',        'title'=>'Wishlist',   'desc'=>'Saved items'],
                ['route'=>'shop.account.profile', 'icon'=>'fa-user',         'title'=>'Profile',    'desc'=>'Edit your details'],
            ] as $card)
                <a href="{{ route($card['route']) }}" class="bg-white border border-gray-100 rounded-2xl p-5 hover:shadow-xl hover:-translate-y-1 transition group">
                    <span class="w-12 h-12 rounded-xl flex items-center justify-center mb-3" style="background:linear-gradient(135deg,#e8f1fb,#d6ecfa);color:var(--brand-navy);">
                        <i class="fas {{ $card['icon'] }} text-lg"></i>
                    </span>
                    <div class="font-bold text-gray-900">{{ $card['title'] }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $card['desc'] }}</div>
                </a>
            @endforeach
        </div>

        {{-- Recent orders --}}
        <div class="mt-10 reveal">
            <div class="flex items-end justify-between mb-4">
                <h2 class="display text-2xl font-bold">Recent orders</h2>
                <a href="{{ route('shop.account.orders') }}" class="text-sm font-semibold" style="color:var(--brand-cyan);">View all <i class="fas fa-arrow-right text-xs"></i></a>
            </div>

            @if ($recentOrders->isEmpty())
                <div class="bg-white border border-gray-100 rounded-2xl p-12 text-center text-gray-500">
                    <i class="fas fa-receipt text-4xl text-gray-300 mb-3 block"></i>
                    No orders yet. <a href="{{ route('shop.catalog') }}" class="font-semibold underline" style="color:var(--brand-cyan);">Start shopping</a>.
                </div>
            @else
                <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Order</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($recentOrders as $o)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs">{{ $o->order_number }}</td>
                                    <td class="px-4 py-3 text-gray-600">{{ $o->created_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="chip capitalize" style="background:#e8f1fb;color:var(--brand-cyan);">{{ str_replace('_', ' ', $o->status) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold">{{ shop_price($o->total) }}</td>
                                    <td class="px-4 py-3 text-right"><a href="{{ route('shop.account.order', $o) }}" class="text-blue-700 hover:underline text-xs">View</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection
@push('styles')<style>.hero-pattern{background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);background-size:48px 48px;}</style>@endpush
