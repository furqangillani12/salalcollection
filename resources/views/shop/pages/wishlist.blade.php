@extends('shop.layouts.app')
@section('title', 'My Wishlist')
@section('content')
<section class="py-10 sm:py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8 reveal">
            <h1 class="display text-3xl sm:text-4xl font-bold flex items-center gap-3"><i class="fas fa-heart text-blue-500"></i> Wishlist</h1>
            <p class="text-gray-500 text-sm mt-2">Save your favourites for later.</p>
        </div>

        @if ($items->isEmpty())
            <div class="bg-white rounded-2xl border border-gray-100 p-16 text-center reveal">
                <i class="far fa-heart text-5xl text-gray-300 mb-4 block"></i>
                <h2 class="display text-2xl font-bold mb-2">Your wishlist is empty</h2>
                <p class="text-gray-500 mb-6">Tap the heart on any product to save it here.</p>
                <a href="{{ route('shop.catalog') }}" class="btn btn-dark">Browse products</a>
            </div>
        @else
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 reveal-stagger">
                @foreach ($items as $w)
                    @if ($w->product)
                        @php $product = $w->product; @endphp
                        @include('shop.partials.product-card', compact('product'))
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</section>
@endsection
