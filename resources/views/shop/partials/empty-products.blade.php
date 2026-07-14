{{-- Static, friendly placeholder shown when a product section has no items.
     Intentionally NOT an animated skeleton, so it never looks like it's
     "stuck loading". --}}
<div class="reveal rounded-2xl border border-dashed border-gray-300 bg-white py-14 text-center">
    <span class="w-14 h-14 mx-auto mb-3 rounded-2xl flex items-center justify-center"
          style="background:linear-gradient(135deg,#e8f1fb,#d6ecfa);color:var(--brand-cyan);">
        <i class="fas fa-box-open text-xl"></i>
    </span>
    <p class="font-bold text-gray-700">More products coming soon</p>
    <p class="text-sm text-gray-500 mt-1">We're adding new items regularly — check back shortly.</p>
    <a href="{{ route('shop.catalog') }}" class="inline-flex items-center gap-2 mt-4 text-sm font-semibold" style="color:var(--brand-cyan);">
        Browse the catalog <i class="fas fa-arrow-right text-xs"></i>
    </a>
</div>
