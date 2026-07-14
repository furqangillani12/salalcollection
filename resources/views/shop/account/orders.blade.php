@extends('shop.layouts.app')
@section('title', 'My Orders')
@section('content')
<section class="py-10 sm:py-14">
    <div class="max-w-5xl mx-auto px-4 reveal">
        <a href="{{ route('shop.account') }}" class="text-xs text-gray-500 hover:text-blue-700 inline-flex items-center gap-2 mb-4"><i class="fas fa-arrow-left"></i> Back to account</a>
        <h1 class="display text-3xl font-bold mb-6">My Orders</h1>

        @if ($orders->isEmpty())
            <div class="bg-white border border-gray-100 rounded-2xl p-12 text-center text-gray-500">
                <i class="fas fa-receipt text-4xl text-gray-300 mb-3 block"></i>
                <p class="font-bold text-gray-700">No orders yet</p>
                <a href="{{ route('shop.catalog') }}" class="btn btn-dark mt-4 !text-xs">Start shopping</a>
            </div>
        @else
            <div class="bg-white border border-gray-100 rounded-2xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-[11px] uppercase tracking-wider text-gray-500">
                            <tr>
                                <th class="px-4 py-3 text-left">Order #</th>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">Status</th>
                                <th class="px-4 py-3 text-left">Payment</th>
                                <th class="px-4 py-3 text-right">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($orders as $o)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-mono text-xs">{{ $o->order_number }}</td>
                                    <td class="px-4 py-3 text-gray-600 whitespace-nowrap">{{ $o->created_at->format('d M Y') }}</td>
                                    <td class="px-4 py-3"><span class="chip capitalize" style="background:#e8f1fb;color:var(--brand-cyan);">{{ str_replace('_', ' ', $o->status) }}</span></td>
                                    <td class="px-4 py-3 text-xs capitalize text-gray-600">{{ str_replace('_', ' ', $o->payment_method) }}</td>
                                    <td class="px-4 py-3 text-right font-bold whitespace-nowrap">{{ shop_price($o->total) }}</td>
                                    <td class="px-4 py-3 text-right"><a href="{{ route('shop.account.order', $o) }}" class="text-blue-700 hover:underline text-xs">Details</a></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </div>
</section>
@endsection
