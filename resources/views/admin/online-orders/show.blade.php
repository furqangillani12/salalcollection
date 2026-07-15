@extends('layouts.admin')
@section('title', 'Order ' . $order->order_number)

@section('content')
<div class="p-3 sm:p-6">

    <a href="{{ route('admin.online-orders.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-cyan-700 mb-3">
        <i class="fas fa-arrow-left text-xs"></i> Back to all online orders
    </a>

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3 mb-5">
        <div>
            <div class="text-xs uppercase tracking-widest" style="color:#0891b2;">Online order</div>
            <h1 class="text-2xl font-bold text-gray-800 mt-1">{{ $order->order_number }}</h1>
            <p class="text-xs text-gray-500 mt-1">Placed {{ $order->created_at->format('d M Y · h:i A') }}@if(!$order->customer_id) · <span class="font-semibold text-gray-600">GUEST</span>@endif</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('shop.track.view', $order->receipt_token) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 rounded-lg text-xs font-semibold">
                <i class="fas fa-eye"></i> Public tracking page
            </a>
            <a href="{{ route('admin.online-orders.slip', $order) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-white rounded-lg text-xs font-semibold" style="background:#0891b2;">
                <i class="fas fa-print"></i> Dispatch slip
            </a>
            <a href="{{ route('admin.online-orders.checklist', $order) }}" target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-2 bg-white border border-gray-300 hover:border-gray-400 text-gray-700 rounded-lg text-xs font-semibold">
                <i class="fas fa-list-check"></i> Checklist
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-emerald-50 text-emerald-800 rounded-lg border border-emerald-200 text-sm"><i class="fas fa-check-circle mr-1"></i> {{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-50 text-red-800 rounded-lg border border-red-200 text-sm"><i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[1fr_360px] gap-5">

        {{-- LEFT: items + status --}}
        <div class="space-y-5">
            {{-- Status update --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide flex items-center gap-2">
                        <i class="fas fa-truck text-cyan-600"></i> Status &amp; Tracking
                    </h2>
                </div>

                {{-- Timeline --}}
                @php
                    $timeline = config('order_flow.timeline');
                    $statuses = config('order_flow.statuses');
                    $current = order_status_norm($order->status);
                    $terminal = in_array($current, config('order_flow.terminal'), true);
                    $currentIdx = array_search($current, $timeline);
                @endphp
                <div class="p-5 grid grid-cols-5 gap-2 {{ $terminal ? 'opacity-50' : '' }}">
                    @foreach ($timeline as $idx => $key)
                        @php $meta = $statuses[$key]; $done = !$terminal && $currentIdx !== false && $idx <= $currentIdx; @endphp
                        <div class="text-center">
                            <div class="w-10 h-10 rounded-full mx-auto flex items-center justify-center text-sm transition"
                                 style="background:{{ $done ? '#0891b2' : '#e5e7eb' }};color:{{ $done ? 'white' : '#9ca3af' }};">
                                <i class="fas {{ $meta['icon'] }}"></i>
                            </div>
                            <div class="text-[11px] mt-1.5 font-semibold" style="color:{{ $done ? '#0c1f3d' : '#9ca3af' }};">{{ $meta['label'] }}</div>
                        </div>
                    @endforeach
                </div>
                @if ($terminal)
                    @php $tm = $statuses[$current]; @endphp
                    <div class="px-5 -mt-2 pb-3"><span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold" style="background:{{ $tm['bg'] }};color:{{ $tm['text'] }};"><i class="fas {{ $tm['icon'] }}"></i> {{ $tm['label'] }}</span></div>
                @endif

                <form method="POST" action="{{ route('admin.online-orders.status', $order) }}" class="border-t border-gray-100 p-5 flex flex-col sm:flex-row sm:items-end gap-3">
                    @csrf @method('PATCH')
                    <div class="sm:w-44 flex-shrink-0">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 whitespace-nowrap">Update status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                            @foreach ($statuses as $s => $meta)
                                <option value="{{ $s }}" @selected($current === $s)>{{ $meta['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-0">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 whitespace-nowrap">Tracking ID <span class="text-gray-400 font-normal">(optional)</span></label>
                        <input type="text" name="tracking_id" value="{{ $order->tracking_id }}" placeholder="e.g. TCS-1234567" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                    </div>
                    <div class="flex-shrink-0">
                        <button class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2 text-white text-sm font-semibold rounded-lg whitespace-nowrap" style="background:linear-gradient(135deg,#0891b2,#0e7490);">
                            <i class="fas fa-check"></i> Update
                        </button>
                    </div>
                </form>
            </div>

            {{-- Items --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                <div class="px-5 py-3 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide flex items-center gap-2">
                        <i class="fas fa-box text-cyan-600"></i> Items
                    </h2>
                    <span class="text-xs text-gray-500">{{ $order->items->count() }} item(s)</span>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach ($order->items as $item)
                        <div class="p-4 flex gap-3">
                            <img src="{{ shop_image($item->product?->image) }}" class="w-14 h-16 rounded-lg object-cover" style="background:#f5f1e8;">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-800 text-sm">{{ $item->product?->name ?? 'Product' }}</div>
                                <div class="text-[11px] text-gray-500 mt-0.5">Qty {{ (int) $item->quantity }} × Rs. {{ number_format($item->unit_price, 0) }}</div>
                            </div>
                            <div class="text-sm font-bold whitespace-nowrap" style="color:#0c1f3d;">Rs. {{ number_format($item->total_price, 0) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Order notes --}}
            @if ($order->order_notes_customer)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-5">
                    <div class="text-xs font-bold uppercase tracking-widest text-amber-700 mb-1">Customer note</div>
                    <p class="text-sm text-gray-800">{{ $order->order_notes_customer }}</p>
                </div>
            @endif
        </div>

        {{-- RIGHT: summary, customer, shipping, payment --}}
        <div class="space-y-4">
            {{-- Summary (weight + delivery editable; total recalculates) --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Summary</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-semibold">Rs. {{ number_format($order->subtotal, 0) }}</span></div>
                    @if ($order->coupon_discount > 0)
                        <div class="flex justify-between text-emerald-600"><span>Coupon ({{ $order->coupon_code }})</span><span>-Rs. {{ number_format($order->coupon_discount, 0) }}</span></div>
                    @endif
                    @if (($order->points_discount ?? 0) > 0)
                        <div class="flex justify-between text-amber-600"><span>Points ({{ (int) $order->points_redeemed }})</span><span>-Rs. {{ number_format($order->points_discount, 0) }}</span></div>
                    @endif
                    @if (($order->tax ?? 0) > 0)
                        <div class="flex justify-between"><span class="text-gray-500">Tax</span><span class="font-semibold">Rs. {{ number_format($order->tax, 0) }}</span></div>
                    @endif
                </div>

                <form method="POST" action="{{ route('admin.online-orders.adjust', $order) }}" class="mt-3 pt-3 border-t border-gray-100 space-y-2">
                    @csrf
                    @method('PATCH')
                    <div class="grid grid-cols-2 gap-2">
                        <label class="block">
                            <span class="text-[11px] text-gray-500">Delivery (Rs.)</span>
                            <input type="text" inputmode="decimal" name="delivery_charges" value="{{ rtrim(rtrim(number_format((float) ($order->delivery_charges ?? 0), 2, '.', ''), '0'), '.') }}" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm">
                        </label>
                        <label class="block">
                            <span class="text-[11px] text-gray-500">Weight (kg)</span>
                            <input type="text" inputmode="decimal" name="weight" value="{{ rtrim(rtrim(number_format((float) ($order->weight ?? 0), 3, '.', ''), '0'), '.') }}" class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm">
                        </label>
                    </div>
                    @php
                        $isPaidNow = in_array($order->online_payment_status, ['paid', 'bank_paid'], true)
                            || $order->payment_status === 'paid'
                            || (float) $order->balance_amount <= 0;
                        $codAuto = $isPaidNow ? 0 : (float) $order->balance_amount;
                    @endphp
                    <label class="block">
                        <span class="text-[11px] text-gray-500">COD amount to collect on slip (Rs.)</span>
                        <input type="text" inputmode="decimal" name="dispatch_cod_amount"
                               value="{{ $order->dispatch_cod_amount !== null ? rtrim(rtrim(number_format((float) $order->dispatch_cod_amount, 2, '.', ''), '0'), '.') : '' }}"
                               placeholder="Auto: Rs. {{ number_format($codAuto, 0) }}"
                               class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm">
                        <span class="text-[10px] text-gray-400">Blank = auto ({{ $isPaidNow ? 'paid → Rs. 0' : 'unpaid → balance' }}). Set 0 if the customer already paid online.</span>
                    </label>
                    <label class="block">
                        <span class="text-[11px] text-gray-500">Dispatch slip remarks</span>
                        <textarea name="dispatch_remarks" rows="2" maxlength="500"
                                  placeholder="e.g. Handle with care · Call before delivery"
                                  class="w-full px-2 py-1.5 border border-gray-300 rounded-lg text-sm">{{ $order->dispatch_remarks }}</textarea>
                        <span class="text-[10px] text-gray-400">Prints in the slip's Remarks box. Blank = empty box for handwriting.</span>
                    </label>
                    <button class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-gray-800 hover:bg-gray-900 text-white text-xs font-semibold rounded-lg"><i class="fas fa-calculator"></i> Update &amp; recalculate total</button>
                </form>

                <hr class="my-3 border-gray-100">
                <div class="flex justify-between items-baseline"><span class="font-bold">Total</span><span class="text-xl font-extrabold" style="color:#0c1f3d;">Rs. {{ number_format($order->total, 0) }}</span></div>
            </div>

            {{-- Payment --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Payment</h3>
                <div class="space-y-1 text-sm">
                    <div><span class="text-gray-500">Method:</span> <span class="font-semibold capitalize">{{ str_replace('_', ' ', $order->payment_method) }}</span></div>
                    <div><span class="text-gray-500">Status:</span> <span class="font-semibold capitalize">{{ str_replace('_', ' ', $order->online_payment_status ?? $order->payment_status) }}</span></div>
                    <div><span class="text-gray-500">Paid:</span> <span class="font-semibold">Rs. {{ number_format($order->paid_amount, 0) }}</span></div>
                    <div><span class="text-gray-500">Balance:</span> <span class="font-bold {{ $order->balance_amount > 0 ? 'text-rose-600' : 'text-emerald-600' }}">Rs. {{ number_format($order->balance_amount, 0) }}</span></div>
                    @if ($order->online_payment_ref)
                        <div class="text-xs text-gray-500 mt-1">Ref: <span class="font-mono">{{ $order->online_payment_ref }}</span></div>
                    @endif
                </div>

                {{-- Customer-submitted bank-transfer proof --}}
                @if ($order->payment_proof_path || $order->payment_sender_name || $order->payment_sender_amount)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-2"><i class="fas fa-receipt text-blue-500"></i> Payment proof</div>
                        <div class="space-y-1 text-sm">
                            @if ($order->payment_sender_name)<div><span class="text-gray-500">From title:</span> <span class="font-semibold">{{ $order->payment_sender_name }}</span></div>@endif
                            @if ($order->payment_sender_bank)<div><span class="text-gray-500">From bank:</span> <span class="font-semibold">{{ $order->payment_sender_bank }}</span></div>@endif
                            @if ($order->payment_sender_amount)<div><span class="text-gray-500">Amount sent:</span> <span class="font-semibold">Rs. {{ number_format($order->payment_sender_amount, 0) }}</span></div>@endif
                        </div>
                        @if ($order->payment_proof_path)
                            <a href="{{ asset('storage/' . $order->payment_proof_path) }}" target="_blank" rel="noopener" class="block mt-2">
                                <img src="{{ asset('storage/' . $order->payment_proof_path) }}" alt="Payment screenshot" class="w-full max-h-56 object-contain rounded-lg border border-gray-200 bg-gray-50">
                                <span class="text-[11px] text-blue-600">Open full screenshot <i class="fas fa-external-link-alt"></i></span>
                            </a>
                        @endif
                    </div>
                @endif

                @if ($order->balance_amount > 0 && $order->status !== 'cancelled')
                    <form method="POST" action="{{ route('admin.online-orders.mark-paid', $order) }}" class="mt-4 pt-4 border-t border-gray-100"
                          onsubmit="return confirm('Mark this order as fully paid? Customer balance will be updated.')">
                        @csrf @method('PATCH')
                        <input type="text" name="payment_ref" placeholder="Payment ref (optional)"
                               class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs mb-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <button class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold rounded-lg">
                            <i class="fas fa-check-circle"></i> Mark as Paid
                        </button>
                    </form>
                @endif
            </div>

            {{-- Customer --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Customer</h3>
                @if ($order->customer)
                    <div class="font-bold text-gray-900">{{ $order->customer->name }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $order->customer->email }}</div>
                    <div class="text-xs text-gray-500">{{ $order->customer->phone }}</div>
                    <a href="{{ route('admin.customers.show', $order->customer) }}"
                       class="inline-flex items-center gap-1.5 mt-3 text-xs font-semibold" style="color:#0891b2;">
                        <i class="fas fa-user"></i> View customer
                    </a>
                @else
                    <div class="font-bold text-gray-900">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</div>
                    <div class="text-xs text-gray-500 mt-0.5">{{ $order->customer_email }}</div>
                    <div class="text-xs text-gray-500">{{ $order->shipping_phone }}</div>
                    <span class="inline-block mt-2 text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">GUEST CHECKOUT</span>
                @endif
            </div>

            {{-- Shipping --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3">Shipping</h3>
                <div class="text-sm text-gray-700 leading-relaxed">
                    {{ $order->shipping_first_name }} {{ $order->shipping_last_name }}<br>
                    <i class="fas fa-phone text-xs text-gray-400"></i> {{ $order->shipping_phone }}<br>
                    {{ $order->shipping_address1 }}<br>
                    @if ($order->shipping_address2){{ $order->shipping_address2 }}<br>@endif
                    @php
                        $line = array_filter([$order->shipping_tehsil, $order->shipping_district, $order->shipping_city]);
                    @endphp
                    @if (count($line)){{ implode(', ', $line) }}<br>@endif
                    @if ($order->shipping_province){{ $order->shipping_province }}@if ($order->shipping_post_code) — {{ $order->shipping_post_code }}@endif<br>@endif
                    <span class="text-gray-500">{{ $order->shipping_country }}</span>
                </div>
                <div class="text-xs text-gray-500 mt-3"><i class="fas fa-truck"></i> {{ $order->dispatch_method }}</div>
                @if ($order->tracking_id)
                    <div class="text-xs text-gray-700 mt-1"><i class="fas fa-hashtag"></i> {{ $order->tracking_id }}
                        @if (order_track_url($order))
                            · <a href="{{ order_track_url($order) }}" target="_blank" rel="noopener" class="text-cyan-700 font-semibold hover:underline">Track <i class="fas fa-external-link-alt text-[9px]"></i></a>
                        @endif
                    </div>
                @endif

                {{-- Notify the account holder (the number/email the account was opened on) --}}
                @php
                    $holderPhone = order_holder_phone($order);
                    $waCust = wa_link($holderPhone, order_status_message($order, $order->status));
                @endphp
                <div class="mt-4 pt-4 border-t border-gray-100 space-y-2">
                    <div class="text-[11px] uppercase tracking-wide font-semibold text-gray-500">Notify customer ({{ $holderPhone ?: '—' }})</div>
                    <div class="flex flex-wrap gap-2">
                        @if ($waCust)
                            <a href="{{ $waCust }}" target="_blank" rel="noopener"
                               class="inline-flex items-center gap-2 text-sm font-semibold px-3 py-2 rounded-lg text-white" style="background:#25D366;">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                        @endif
                        @if ($order->customer_email ?: $order->customer?->email)
                            <form method="POST" action="{{ route('admin.online-orders.notify', $order) }}">
                                @csrf
                                <button class="inline-flex items-center gap-2 text-sm font-semibold px-3 py-2 rounded-lg text-white" style="background:#2563eb;">
                                    <i class="fas fa-envelope"></i> Email status
                                </button>
                            </form>
                        @endif
                    </div>
                    <p class="text-[11px] text-gray-400">WhatsApp opens pre-filled to the <strong>account holder</strong>'s number. Email sends the current status with your template.</p>
                </div>

                @if ($order->from_name)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <div class="text-[11px] uppercase tracking-wide font-semibold text-gray-500 mb-1"><i class="fas fa-user-tag text-purple-500"></i> Reseller "From" address</div>
                        <div class="text-sm text-gray-700">{{ $order->from_name }}<br>{{ $order->from_phone }}<br>{{ $order->from_address }}</div>
                        <div class="text-[11px] text-gray-400 mt-1">Printed as the sender on the slip when you pick "Reseller".</div>
                    </div>
                @endif
            </div>

            {{-- Dispatch photo / video --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide mb-3"><i class="fas fa-camera text-cyan-600"></i> Dispatch photo / video</h3>
                @if ($order->dispatch_media_path)
                    @php $ext = strtolower(pathinfo($order->dispatch_media_path, PATHINFO_EXTENSION)); @endphp
                    @if (in_array($ext, ['mp4','webm','mov']))
                        <video src="{{ asset('storage/'.$order->dispatch_media_path) }}" controls class="w-full rounded-lg border border-gray-200 mb-2"></video>
                    @else
                        <a href="{{ asset('storage/'.$order->dispatch_media_path) }}" target="_blank"><img src="{{ asset('storage/'.$order->dispatch_media_path) }}" class="w-full rounded-lg border border-gray-200 mb-2"></a>
                    @endif
                @endif
                <form method="POST" action="{{ route('admin.online-orders.dispatch-media', $order) }}" enctype="multipart/form-data" class="space-y-2">
                    @csrf
                    <input type="file" name="dispatch_media" accept="image/*,video/*" required class="w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:bg-cyan-50 file:text-cyan-700">
                    <button class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold rounded-lg"><i class="fas fa-upload"></i> {{ $order->dispatch_media_path ? 'Replace' : 'Attach' }} photo/video</button>
                    <p class="text-[11px] text-gray-400">Image or short video (up to 20 MB) of what you're sending.</p>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
