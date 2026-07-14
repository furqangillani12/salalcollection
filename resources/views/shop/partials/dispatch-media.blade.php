{{-- Dispatch photo/video the team attached when packing the parcel (#M1).
     Shown to the customer so they can see exactly what was sent. --}}
@if ($order->dispatch_media_path)
    @php $ext = strtolower(pathinfo($order->dispatch_media_path, PATHINFO_EXTENSION)); @endphp
    <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6 reveal">
        <h2 class="font-bold text-gray-900 mb-1 flex items-center gap-2"><i class="fas fa-box-open" style="color:var(--brand-cyan);"></i> Your parcel</h2>
        <p class="text-xs text-gray-500 mb-3">Photo/video taken at the time of dispatch.</p>
        @if (in_array($ext, ['mp4', 'webm', 'mov']))
            <video src="{{ asset('storage/' . $order->dispatch_media_path) }}" controls playsinline class="w-full rounded-xl border border-gray-100" style="max-height:420px;"></video>
        @else
            <a href="{{ asset('storage/' . $order->dispatch_media_path) }}" target="_blank" rel="noopener">
                <img src="{{ asset('storage/' . $order->dispatch_media_path) }}" alt="Parcel" class="w-full rounded-xl border border-gray-100 object-contain" style="max-height:420px;background:#f8fafc;">
            </a>
        @endif
    </div>
@endif
