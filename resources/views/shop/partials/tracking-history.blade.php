{{-- On-site tracking timeline (#20): every status event with date, so the
     customer can follow the order on our site. The courier deep-link covers the
     parcel's physical movement. --}}
@php
    $history = $order->statusHistory;
    $courierUrl = order_track_url($order);
@endphp
@if ($history->isNotEmpty() || $courierUrl)
    <div class="bg-white rounded-2xl border border-gray-100 p-6 mb-6 reveal">
        <h2 class="font-bold text-gray-900 mb-4 flex items-center gap-2"><i class="fas fa-route" style="color:var(--brand-cyan);"></i> Tracking history</h2>

        @if ($history->isNotEmpty())
            <ol class="relative border-l-2 border-gray-100 ml-3">
                @foreach ($history as $h)
                    @php $meta = order_status_meta($h->status); $last = $loop->last; @endphp
                    <li class="mb-5 ml-5 last:mb-0">
                        <span class="absolute -left-[11px] flex items-center justify-center w-5 h-5 rounded-full"
                              style="background:{{ $meta['bg'] }};color:{{ $meta['text'] }};">
                            <i class="fas {{ $meta['icon'] }} text-[9px]"></i>
                        </span>
                        <div class="flex items-center justify-between gap-2">
                            <span class="font-bold text-gray-800">{{ $meta['label'] }}</span>
                            <span class="text-[11px] text-gray-400 whitespace-nowrap">{{ $h->created_at->format('d M Y · h:i A') }}</span>
                        </div>
                        @if ($h->note)<div class="text-xs text-gray-500 mt-0.5">{{ $h->note }}</div>@endif
                    </li>
                @endforeach
            </ol>
        @endif

        @if ($courierUrl)
            <div class="mt-4 pt-4 border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                <div class="text-xs text-gray-500">
                    Live courier movement ({{ $order->dispatch_method }}@if ($order->tracking_id) · {{ $order->tracking_id }}@endif)
                </div>
                <a href="{{ $courierUrl }}" target="_blank" rel="noopener" class="btn btn-primary !py-2 !text-xs w-max">
                    Track on courier site <i class="fas fa-arrow-up-right-from-square text-[9px]"></i>
                </a>
            </div>
        @endif
    </div>
@endif
