@extends('shop.layouts.app')
@section('title', 'About')
@section('content')
<section class="hero py-20 text-center">
    <div class="hero-pattern absolute inset-0"></div>
    <div class="relative max-w-3xl mx-auto px-4">
        <img src="{{ asset('assets/images/brand/almufeed-traders.png') }}" alt="Salal Collection"
             class="h-24 w-24 mx-auto mb-6 object-contain">
        <span class="chip mb-4 inline-block" style="background:rgba(212,175,55,.16);color:#f3dca0;border:1px solid rgba(212,175,55,.35);">About us</span>
        <h1 class="display text-5xl sm:text-6xl font-bold text-white mb-4 leading-tight">Skincare you can trust.</h1>
        <p class="text-base sm:text-lg text-white/80">Salal Collection brings you premium skincare and beauty — thoughtfully made, honestly priced, and delivered across Pakistan.</p>
    </div>
</section>
<section class="py-20">
    <div class="max-w-3xl mx-auto px-4 prose prose-lg reveal">
        <p class="text-lg text-gray-700 leading-relaxed">Salal Collection was born from a simple belief: everyone deserves healthy, glowing skin without the guesswork. We craft and curate skincare and beauty essentials — face washes, whitening creams and Vitamin&nbsp;C serums — using quality ingredients that are gentle on your skin and kind to your routine.</p>
        <p class="text-lg text-gray-700 leading-relaxed mt-4">Every product is made to <strong>care for your skin gently and effectively</strong>, for both men and women. From our Rice Creamy Face Wash to our Double Action Serum, we focus on real results you can see and feel.</p>
        <p class="text-lg text-gray-700 leading-relaxed mt-4">Shop with confidence — <strong>quality, transparency and honest pricing</strong>, delivered straight to your door.</p>
    </div>
</section>
@endsection
@push('styles')
<style>.hero-pattern{background-image:linear-gradient(rgba(255,255,255,.05) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.05) 1px,transparent 1px);background-size:48px 48px;mask-image:radial-gradient(ellipse at center,black 30%,transparent 80%);-webkit-mask-image:radial-gradient(ellipse at center,black 30%,transparent 80%);}</style>
@endpush
