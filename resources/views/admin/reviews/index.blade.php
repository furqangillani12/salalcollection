@extends('layouts.admin')
@section('title', 'Product Reviews')

@section('content')
<div class="p-3 sm:p-6">

    <div class="mb-5">
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
            <i class="fas fa-star text-cyan-600"></i> Product Reviews
        </h1>
        <p class="text-xs sm:text-sm text-gray-500 mt-1">Approve genuine reviews (they appear on the storefront and earn the customer points) or reject / delete spam and mistaken ones.</p>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-2 text-sm">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 text-red-800 px-4 py-2 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Status tabs --}}
    <div class="flex flex-wrap gap-2 mb-5">
        @foreach (['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
            <a href="{{ route('admin.reviews.index', ['status' => $key]) }}"
               class="px-4 py-2 rounded-lg text-sm font-semibold border {{ $status === $key ? 'bg-cyan-600 text-white border-cyan-600' : 'bg-white text-gray-600 border-gray-300 hover:border-gray-400' }}">
                {{ $label }}
                <span class="ml-1 inline-flex items-center justify-center min-w-[20px] px-1.5 rounded-full text-xs {{ $status === $key ? 'bg-white/25' : 'bg-gray-100 text-gray-600' }}">{{ $counts[$key] }}</span>
            </a>
        @endforeach
    </div>

    <div class="space-y-3">
        @forelse ($reviews as $review)
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-amber-500 text-sm">
                                @for ($i = 1; $i <= 5; $i++)<i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-gray-200' }}"></i>@endfor
                            </span>
                            <span class="font-semibold text-gray-800 text-sm">{{ $review->customer?->name ?? 'Customer' }}</span>
                            <span class="text-xs text-gray-400">· {{ $review->created_at->format('d M Y') }}</span>
                            @if ($review->points_awarded)
                                <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200"><i class="fas fa-star"></i> points given</span>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                            on <a href="{{ route('shop.product', $review->product->slug) }}" target="_blank" class="text-cyan-700 font-semibold hover:underline">{{ $review->product?->name ?? 'Product' }}</a>
                        </div>
                        @if ($review->title)<div class="font-semibold text-gray-800 mt-2">{{ $review->title }}</div>@endif
                        @if ($review->body)<p class="text-gray-600 text-sm mt-1">{{ $review->body }}</p>@endif

                        @if (count($review->mediaItems()))
                            <div class="flex flex-wrap gap-2 mt-3">
                                @foreach ($review->mediaItems() as $m)
                                    <a href="{{ asset('storage/'.$m['path']) }}" target="_blank" rel="noopener" class="block">
                                        @if ($m['type'] === 'video')
                                            <video src="{{ asset('storage/'.$m['path']) }}" class="w-20 h-20 object-cover rounded-lg border border-gray-200" muted></video>
                                        @else
                                            <img src="{{ asset('storage/'.$m['path']) }}" class="w-20 h-20 object-cover rounded-lg border border-gray-200" alt="review media">
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="flex sm:flex-col gap-2 flex-shrink-0">
                        @if ($review->status !== 'approved')
                            <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">
                                @csrf @method('PATCH')
                                <button class="w-full px-3 py-1.5 rounded-lg text-sm font-semibold bg-emerald-600 text-white hover:bg-emerald-700"><i class="fas fa-check"></i> Approve</button>
                            </form>
                        @endif
                        @if ($review->status !== 'rejected')
                            <form method="POST" action="{{ route('admin.reviews.reject', $review) }}">
                                @csrf @method('PATCH')
                                <button class="w-full px-3 py-1.5 rounded-lg text-sm font-semibold bg-amber-100 text-amber-800 hover:bg-amber-200"><i class="fas fa-ban"></i> Reject</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review permanently?');">
                            @csrf @method('DELETE')
                            <button class="w-full px-3 py-1.5 rounded-lg text-sm font-semibold bg-red-50 text-red-700 hover:bg-red-100 border border-red-200"><i class="fas fa-trash"></i> Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-dashed border-gray-300 py-14 text-center text-gray-500">
                <i class="fas fa-star text-3xl mb-3 text-gray-300"></i>
                <p class="font-semibold text-gray-600">No {{ $status }} reviews.</p>
            </div>
        @endforelse
    </div>

    <div class="mt-5">{{ $reviews->links() }}</div>
</div>
@endsection
