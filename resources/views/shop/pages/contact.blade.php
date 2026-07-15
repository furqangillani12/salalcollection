@extends('shop.layouts.app')
@section('title', 'Contact')
@section('content')
<section class="py-16 sm:py-20">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 reveal">
        <div class="text-center mb-12">
            <span class="chip" style="background:#e8f1fb;color:var(--brand-cyan);">Contact</span>
            <h1 class="display text-4xl sm:text-5xl font-bold mt-3">Get in touch</h1>
            <p class="text-gray-600 mt-3 max-w-md mx-auto">We'd love to hear from you. Reach out by phone, WhatsApp or this form.</p>
        </div>

        @php
            $contactItems = array_values(array_filter([
                setting('site_phone')    ? ['fa-phone',    'Call us',  setting('site_phone')] : null,
                setting('site_whatsapp') ? ['fa-whatsapp', 'WhatsApp', setting('site_whatsapp')] : null,
                ['fa-envelope', 'Email', setting('site_email', 'hello@salalcollection.com')],
            ]));
        @endphp
        <div class="grid lg:grid-cols-3 gap-6 reveal-stagger">
            @foreach ($contactItems as [$icon, $label, $value])
                <div class="bg-white rounded-2xl border border-gray-100 p-6 text-center hover:shadow-lg transition">
                    <span class="w-14 h-14 rounded-2xl mx-auto flex items-center justify-center mb-4"
                          style="background:linear-gradient(135deg,#e8f1fb,#d6ecfa);color:var(--brand-navy);">
                        <i class="fa{{ $icon === 'fa-whatsapp' ? 'b' : 's' }} {{ $icon }} text-xl"></i>
                    </span>
                    <div class="text-xs uppercase tracking-widest text-gray-500 mb-1">{{ $label }}</div>
                    <div class="font-bold text-gray-800">{{ $value }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
