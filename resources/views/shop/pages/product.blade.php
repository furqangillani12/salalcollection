@extends('shop.layouts.app')

@section('title', $product->meta_title ?: $product->name)
@section('description', $product->meta_description ?: ($product->summary ?: \Str::limit(strip_tags($product->description), 150)))

{{-- Share preview = the product image (not the company logo). --}}
@section('og_type', 'product')
@section('og_image', shop_image($product->image))

@section('content')
@php
    $price    = shop_product_price($product);
    $strike   = shop_strike_price($product);
    $hasSale  = $strike !== null;
    $cover    = shop_image($product->image);
    $gallery  = collect([$product->image])
        ->merge(is_array($product->gallery) ? $product->gallery : [])
        ->filter()->unique()->values();
    $stock    = method_exists($product, 'getStockForBranch') && $product->branch_id
        ? $product->getStockForBranch($product->branch_id)
        : ($product->stock_quantity ?? 0);
    $inWishlist = auth('customer')->check()
        && $product->wishlists()->where('customer_id', auth('customer')->id())->exists();
@endphp

<section class="py-10 sm:py-14">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Breadcrumb --}}
        <nav class="text-xs text-gray-500 mb-6 flex items-center gap-2 reveal">
            <a href="{{ route('shop.home') }}" class="hover:text-blue-700">Home</a> /
            @if ($product->category) <a href="{{ route('shop.category', $product->category->slug ?? '#') }}" class="hover:text-blue-700">{{ $product->category->name }}</a> / @endif
            <span class="text-gray-700">{{ $product->name }}</span>
        </nav>

        <div class="grid lg:grid-cols-2 gap-10">
            {{-- Gallery --}}
            <div x-data="{ active: 0 }" class="reveal">
                <div class="rounded-3xl overflow-hidden bg-gray-100 mb-4 relative" style="aspect-ratio:4/5;">
                    <template x-for="(img, i) in {{ $gallery->map(fn($g) => shop_image($g))->toJson() }}" :key="i">
                        <img :src="img" :alt="'{{ addslashes($product->name) }}'"
                             class="w-full h-full object-cover absolute inset-0 transition-opacity duration-500"
                             :class="active === i ? 'opacity-100' : 'opacity-0 pointer-events-none'">
                    </template>
                </div>
                @if ($gallery->count() > 1)
                    <div class="grid grid-cols-5 gap-2">
                        @foreach ($gallery as $i => $g)
                            <button @click="active = {{ $i }}" type="button"
                                    class="rounded-xl overflow-hidden border-2 transition" style="aspect-ratio:4/5;"
                                    :class="active === {{ $i }} ? 'border-blue-500' : 'border-transparent'">
                                <img src="{{ shop_image($g) }}" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Details --}}
            <div x-data="{ qty: 1 }" class="reveal">
                @if ($product->brand)
                    <a href="{{ route('shop.brand', $product->brand->slug) }}" class="text-xs uppercase tracking-widest font-semibold hover:underline" style="color:var(--brand-cyan);">{{ $product->brand->name }}</a>
                @endif
                <h1 class="display text-3xl sm:text-4xl font-bold mt-2 leading-tight">{{ $product->name }}</h1>

                @if ($product->barcode)
                    <div class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                        <span>Item code: <span class="font-mono font-semibold text-gray-700">{{ $product->barcode }}</span></span>
                        <button type="button" onclick="copyText('{{ $product->barcode }}', 'Item code copied')" class="text-gray-400 hover:text-blue-600" title="Copy code"><i class="far fa-copy"></i></button>
                    </div>
                @endif

                @if ((float) $product->avg_rating > 0)
                    <div class="flex items-center gap-2 mt-3 text-sm">
                        <div class="text-amber-500">
                            @for ($i = 1; $i <= 5; $i++)<i class="fas fa-star {{ $i <= round($product->avg_rating) ? '' : 'text-gray-200' }}"></i>@endfor
                        </div>
                        <span class="text-gray-600">{{ number_format($product->avg_rating, 1) }} · {{ $product->review_count }} reviews</span>
                    </div>
                @endif

                <div class="flex items-baseline flex-wrap gap-x-3 gap-y-1 mt-6">
                    <span class="text-3xl font-extrabold" style="color:var(--brand-navy);">{{ shop_price($price) }}</span>
                    @if ($hasSale)
                        <span class="text-lg text-gray-400 line-through">{{ shop_price($strike) }}</span>
                        @if (shop_is_reseller())
                            <span class="chip" style="background:#dcfce7;color:#047857;">Retail price · you save {{ shop_price($strike - $price) }}</span>
                        @else
                            <span class="chip" style="background:#fee2e2;color:#b91c1c;">SAVE {{ shop_price($strike - $price) }}</span>
                        @endif
                    @endif
                </div>

                @if ($product->summary)
                    <p class="text-gray-600 mt-5 leading-relaxed">{{ $product->summary }}</p>
                @endif

                <div class="mt-6 inline-flex items-center gap-2 text-sm">
                    @if ($stock > 0)
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-emerald-700 font-semibold">In stock</span>
                    @else
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span>
                        <span class="text-red-600 font-semibold">Out of stock</span>
                    @endif
                </div>

                {{-- Add to cart / Buy now --}}
                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <div class="inline-flex items-center bg-gray-100 rounded-xl">
                        <button type="button" @click="qty = Math.max(1, qty - 1)" class="px-4 py-3 text-gray-600 hover:text-gray-900"><i class="fas fa-minus text-xs"></i></button>
                        <input type="number" min="1" x-model.number="qty" class="w-14 bg-transparent text-center font-bold border-0 focus:ring-0">
                        <button type="button" @click="qty = qty + 1" class="px-4 py-3 text-gray-600 hover:text-gray-900"><i class="fas fa-plus text-xs"></i></button>
                    </div>
                    <button type="button" @click="addToCart({{ $product->id }}, qty)"
                            class="btn btn-ghost flex-1 sm:flex-none" {{ $stock <= 0 ? 'disabled' : '' }}>
                        <i class="fas fa-cart-plus"></i> Add to cart
                    </button>
                    <button type="button" @click="buyNow({{ $product->id }}, qty)"
                            class="btn btn-primary flex-1 sm:flex-none" {{ $stock <= 0 ? 'disabled' : '' }}>
                        <i class="fas fa-bolt"></i> Buy now
                    </button>
                    @auth('customer')
                        <button type="button" onclick="toggleWishlist({{ $product->id }}, this)"
                                class="w-12 h-12 rounded-xl border border-gray-200 hover:border-blue-300 transition flex items-center justify-center {{ $inWishlist ? 'text-blue-500' : 'text-gray-500' }}">
                            <i class="{{ $inWishlist ? 'fas' : 'far' }} fa-heart"></i>
                        </button>
                    @endauth
                </div>

                {{-- Share / copy --}}
                @php
                    $shareUrl  = route('shop.product', $product->slug ?? $product->id);
                    // Copy/share layout (client request): name → code → price → full detail.
                    $rawDetail = $product->description ?: $product->summary ?: '';
                    $rawDetail = preg_replace('/<\s*br\s*\/?>/i', "\n", $rawDetail);
                    $rawDetail = preg_replace('#</\s*(p|div|li|h[1-6])\s*>#i', "\n", $rawDetail);
                    $detail    = trim(preg_replace("/[ \t]*\n{3,}/", "\n\n", strip_tags($rawDetail)));
                    $shareLines = [$product->name];
                    if ($product->barcode) $shareLines[] = 'Item code: ' . $product->barcode;
                    $shareLines[] = shop_price($price);
                    if ($detail !== '') { $shareLines[] = ''; $shareLines[] = $detail; }
                    $shareText = implode("\n", $shareLines);
                @endphp
                <div class="mt-4 flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-gray-500 mr-1">Share:</span>
                    <a href="https://wa.me/?text={{ rawurlencode($shareText . "\n" . $shareUrl) }}"
                       target="_blank" rel="noopener"
                       class="w-9 h-9 rounded-full border border-gray-200 hover:bg-green-50 hover:border-green-300 flex items-center justify-center text-green-600" title="Share on WhatsApp"><i class="fab fa-whatsapp"></i></a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}" target="_blank" rel="noopener"
                       class="w-9 h-9 rounded-full border border-gray-200 hover:bg-blue-50 hover:border-blue-300 flex items-center justify-center text-blue-600" title="Share on Facebook"><i class="fab fa-facebook-f"></i></a>
                    <button type="button" onclick="shareProduct('{{ $shareUrl }}', '{{ addslashes($product->name) }}')"
                            class="w-9 h-9 rounded-full border border-gray-200 hover:bg-gray-50 flex items-center justify-center text-gray-600" title="Share / more"><i class="fas fa-share-nodes"></i></button>
                    <button type="button" onclick="copyText('{{ $shareUrl }}', 'Product link copied')"
                            class="w-9 h-9 rounded-full border border-gray-200 hover:bg-gray-50 flex items-center justify-center text-gray-600" title="Copy link"><i class="fas fa-link"></i></button>
                    <button type="button" onclick="copyText(@js($shareText), 'Name & details copied')"
                            class="inline-flex items-center gap-1.5 h-9 px-3 rounded-full border border-gray-200 hover:bg-gray-50 text-gray-600 text-xs" title="Copy name & details"><i class="far fa-copy"></i> Copy details</button>
                </div>

                {{-- Trust strip --}}
                <div class="grid grid-cols-3 gap-3 mt-8 pt-6 border-t border-gray-100 text-xs text-gray-600">
                    <div class="flex items-center gap-2"><i class="fas fa-truck" style="color:var(--brand-cyan);"></i> Fast delivery</div>
                    <div class="flex items-center gap-2"><i class="fas fa-shield-halved" style="color:var(--brand-cyan);"></i> Authentic</div>
                    <div class="flex items-center gap-2"><i class="fas fa-rotate-left" style="color:var(--brand-cyan);"></i> 7-day returns</div>
                </div>

                @if ($product->description)
                    <div class="mt-8 prose max-w-none text-gray-700">
                        <h3 class="display text-xl font-bold mb-3 text-gray-900">About this product</h3>
                        <div>{!! nl2br(e($product->description)) !!}</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Reviews --}}
        <div class="mt-16 grid lg:grid-cols-[1fr_320px] gap-10 reveal">
            <div>
                <h2 class="display text-2xl font-bold mb-6">Customer reviews</h2>
                @forelse ($reviews as $r)
                    <div class="border-t border-gray-100 py-5">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="text-amber-500 text-sm">
                                @for ($i = 1; $i <= 5; $i++)<i class="fas fa-star {{ $i <= $r->rating ? '' : 'text-gray-200' }}"></i>@endfor
                            </div>
                            <span class="text-sm font-semibold">{{ $r->customer?->name ?? 'Customer' }}</span>
                            <span class="text-xs text-gray-400">{{ $r->created_at->diffForHumans() }}</span>
                        </div>
                        @if ($r->title)<div class="font-semibold text-gray-800">{{ $r->title }}</div>@endif
                        @if ($r->body)<p class="text-gray-600 mt-1">{{ $r->body }}</p>@endif
                        @if (count($r->mediaItems()))
                            <div class="flex flex-wrap gap-2 mt-2">
                                @foreach ($r->mediaItems() as $m)
                                    <a href="{{ asset('storage/'.$m['path']) }}" target="_blank" rel="noopener" class="block">
                                        @if ($m['type'] === 'video')
                                            <video src="{{ asset('storage/'.$m['path']) }}" class="w-16 h-16 object-cover rounded-lg border border-gray-200" muted></video>
                                        @else
                                            <img src="{{ asset('storage/'.$m['path']) }}" class="w-16 h-16 object-cover rounded-lg border border-gray-200" alt="review photo">
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @empty
                    <p class="text-gray-500 italic">No reviews yet — be the first!</p>
                @endforelse
            </div>

            @auth('customer')
                <form method="POST" action="{{ route('shop.review.store', $product->slug) }}" enctype="multipart/form-data"
                      class="bg-white rounded-2xl border border-gray-100 p-5 sticky top-24 self-start"
                      x-data="{ rating: 5 }">
                    @csrf
                    <h3 class="font-bold text-gray-900 mb-3">Write a review</h3>
                    <div class="flex gap-1 text-2xl text-amber-400 mb-4">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button" @click="rating = {{ $i }}" :class="rating >= {{ $i }} ? '' : 'text-gray-200'">
                                <i class="fas fa-star"></i>
                            </button>
                        @endfor
                        <input type="hidden" name="rating" :value="rating">
                    </div>
                    <input type="text" name="title" placeholder="Headline (optional)" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm mb-2">
                    <textarea name="body" rows="3" placeholder="Tell others what you think..." class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm"></textarea>
                    <label class="block text-xs font-semibold text-gray-600 mt-3 mb-1"><i class="fas fa-camera"></i> Add photos / video (optional)</label>
                    <input type="file" name="media[]" multiple accept="image/png,image/jpeg,image/webp,video/mp4,video/webm,video/quicktime"
                           class="w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-blue-50 file:text-blue-700">
                    <p class="text-[11px] text-gray-400 mt-1">Up to 5 files, 20 MB each. Your review appears after our team approves it.</p>
                    @error('media.*')<p class="text-[11px] text-red-600 mt-1">{{ $message }}</p>@enderror
                    <button type="submit" class="btn btn-dark btn-block mt-3 !text-xs">Submit review</button>
                </form>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 p-5 text-center self-start">
                    <p class="text-sm text-gray-600 mb-3">Want to share your thoughts?</p>
                    <a href="{{ route('shop.login') }}" class="btn btn-dark btn-block !text-xs">Sign in to review</a>
                </div>
            @endauth
        </div>

        {{-- Packages / deals that include this product --}}
        @if (($packages ?? collect())->isNotEmpty())
            <div class="mt-16 reveal">
                <div class="flex items-center gap-2 mb-6">
                    <i class="fas fa-box-open text-emerald-500"></i>
                    <h2 class="display text-2xl sm:text-3xl font-bold">Save more with a package</h2>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach ($packages as $package)
                        @include('shop.partials.package-card', ['package' => $package])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Related --}}
        @if ($related->isNotEmpty())
            <div class="mt-16 reveal">
                <h2 class="display text-2xl sm:text-3xl font-bold mb-6">You might also like</h2>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach ($related as $relatedProduct)
                        @include('shop.partials.product-card', ['product' => $relatedProduct])
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Popular --}}
        @if (($popular ?? collect())->isNotEmpty())
            <div class="mt-16 reveal">
                <h2 class="display text-2xl sm:text-3xl font-bold mb-6">Popular right now</h2>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-5">
                    @foreach ($popular as $popularProduct)
                        @include('shop.partials.product-card', ['product' => $popularProduct])
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</section>
@endsection
