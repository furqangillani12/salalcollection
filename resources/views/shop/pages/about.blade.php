@extends('shop.layouts.app')
@section('title', 'About')
@section('content')
<section class="hero py-20 text-center">
    <div class="hero-pattern absolute inset-0"></div>
    <div class="relative max-w-3xl mx-auto px-4">
        <img src="{{ asset('assets/images/brand/almufeed-traders.png') }}" alt="SALAL COLLECTION"
             class="h-14 mx-auto mb-6 brightness-0 invert opacity-95">
        <span class="chip mb-4 inline-block" style="background:rgba(41,171,226,.16);color:#f3dca0;border:1px solid rgba(41,171,226,.35);">About us</span>
        <h1 class="display text-5xl sm:text-6xl font-bold text-white mb-4 leading-tight">Quality you can trust.</h1>
        <p class="text-base sm:text-lg text-blue-100/80">SALAL COLLECTION has been a trusted name in our community in PanjGirain, Bhakkar. Today we extend the same care online.</p>
    </div>
</section>
<section class="py-20">
    <div class="max-w-3xl mx-auto px-4 prose prose-lg reveal">
        <p class="text-lg text-gray-700 leading-relaxed">We started as a single shop with a simple promise: <strong>quality and affordability you can count on</strong>. Over time, that promise has powered multiple branches and now reaches every Pakistani household through this online storefront.</p>
        <p class="text-lg text-gray-700 leading-relaxed mt-4">Every product on our shelves — and on this site — is hand-picked, tested, and stocked by people who actually use them. When you buy from us you're buying from neighbours.</p>
    </div>
</section>
@endsection
@push('styles')
<style>.hero-pattern{background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);background-size:48px 48px;mask-image:radial-gradient(ellipse at center,black 30%,transparent 80%);-webkit-mask-image:radial-gradient(ellipse at center,black 30%,transparent 80%);}</style>
@endpush
