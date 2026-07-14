<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dispatch slip — {{ $order->order_number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Nastaliq+Urdu:wght@500;700&family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; margin: 0; background: #f3f4f6; color: #111827; }
        .urdu { font-family: 'Noto Nastaliq Urdu', serif; }

        .toolbar { background: #fff; border-bottom: 1px solid #e5e7eb; padding: 10px 16px; display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
        .toolbar a, .toolbar button { font-size: 12px; padding: 6px 12px; border-radius: 8px; border: 1px solid #d1d5db; background: #fff; color: #374151; text-decoration: none; cursor: pointer; }
        .toolbar a.on { background: #0891b2; color: #fff; border-color: #0891b2; }
        .toolbar .print { background: #111827; color: #fff; border-color: #111827; margin-left: auto; }

        /* ── Landscape A5 sheet ── */
        .sheet { width: 210mm; margin: 16px auto; background: #fff; border: 2px solid #111827; padding: 5px; }
        /* Each section is its own bordered box, separated by a small gap. The sheet
           border + box border reads as a double border (client wireframe). */
        .sheet > div { border: 2px solid #111827; }
        .sheet > div + div { margin-top: 5px; }

        /* Header : courier logo | title | company logo — NO dividers between.
           Logos are bottom-aligned so there is never empty space BELOW them; the
           title stays vertically centred. */
        .head { display: flex; align-items: flex-end; }
        .head > div { padding: 3px 10px; display: flex; flex-direction: column; align-items: center; justify-content: flex-end; text-align: center; }
        .head .courier { flex: 1.2; align-self: center; }
        .head .title   { flex: 1.2; align-self: center; }
        .head .brand   { flex: 1.4; }
        /* No vertical padding around the logos → they sit flush, no space below. */
        .head .courier, .head .brand { padding-top: 0; padding-bottom: 0; }
        .head .title .t { font-size: 24px; font-weight: 800; line-height: 1.05; }
        .head .title .sub { font-size: 12px; font-weight: 700; color: #111827; margin-top: 2px; }
        .head .clogo { max-height: 74px; max-width: 210px; object-fit: contain; display: block; }
        .head .blogo { max-height: 74px; max-width: 290px; object-fit: contain; display: block; }
        .head .cname { font-size: 13px; font-weight: 800; margin-top: 0; }
        .head .ph { font-size: 11px; color: #111827; font-weight: 700; }

        /* Row : tracking+barcode | order no + QR | date */
        .row3 { display: flex; }
        .row3 > div { flex: 1; padding: 5px 8px; text-align: center; }
        .row3 > div + div { border-left: 2px solid #111827; }
        .row3 .k { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #111827; }
        .row3 .track { display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .row3 .track svg { max-width: 230px; height: 36px; }
        .row3 .track .trackno { font-size: 15px; font-weight: 800; letter-spacing: .05em; margin-top: 1px; }
        .row3 .track .empty { font-size: 11px; color: #6b7280; padding: 10px 0; }
        .row3 .orderbox { display: flex; align-items: center; justify-content: center; gap: 10px; }
        .row3 .orderbox .ordno { font-size: 19px; font-weight: 800; }
        .row3 .orderbox .oqr { display: flex; flex-direction: column; align-items: center; }
        .row3 .orderbox .oqr .k { font-size: 8px; }
        .row3 .date { font-size: 15px; font-weight: 700; margin-top: 3px; }
        .row3 .time { font-size: 12px; font-weight: 600; color: #374151; }
        /* COD amount + barcode, sitting in the top row (line 1: amount, line 2: barcode) */
        .row3 .codcell { display: flex; flex-direction: column; align-items: center; justify-content: center; }
        .row3 .codcell.due { background: #fff7ed; }
        .row3 .codcell .cline { font-size: 13px; font-weight: 800; color: #111827; }
        .row3 .codcell .cline b { font-size: 22px; font-weight: 900; }
        .row3 .codcell .cline b.paid { color: #059669; }
        .row3 .codcell svg { max-width: 210px; height: 30px; margin-top: 4px; }

        /* Main : [from] | to | codes/parcel */
        .main { display: flex; }
        .main > div { padding: 7px 10px; }
        .main .from { flex: 1; border-right: 2px solid #111827; }
        .main .to   { flex: 1.7; border-right: 2px solid #111827; }
        .main .side { flex: 1; padding: 0; display: flex; flex-direction: column; }
        /* Crisp black label so it never prints faded, bigger & bolder (client). */
        .lead { font-size: 13px; font-weight: 900; text-transform: uppercase; letter-spacing: .03em; color: #111827; margin-bottom: 2px; }

        .to .cname { font-size: 21px; font-weight: 800; line-height: 1.15; }
        .to .cphone { font-size: 16px; font-weight: 800; margin-top: 2px; }
        .to .caddr { font-size: 15px; font-weight: 600; margin-top: 4px; line-height: 1.4; }
        .to .cmeta { font-size: 13px; margin-top: 5px; line-height: 1.5; }
        .to .cmeta b { font-weight: 800; }

        .from .fname { font-size: 15px; font-weight: 800; margin-top: 2px; }
        .from .fbody { font-size: 12px; font-weight: 600; margin-top: 3px; line-height: 1.4; }

        .side .cod { border-bottom: 1px solid #111827; padding: 7px 10px; text-align: center; }
        .side .cod.due { background: #fff7ed; }
        .side .cod .amt { font-size: 23px; font-weight: 800; line-height: 1.1; }
        .side .cod .tag { font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; color: #b45309; }
        .side .cod .amt.paid { color: #059669; }
        .side .cod svg { max-width: 180px; height: 30px; margin-top: 3px; }
        .side .parcel { flex: 1; padding: 7px 10px; display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .side .parcel .facts { font-size: 13px; font-weight: 700; line-height: 1.6; }
        .side .parcel .facts b { font-size: 16px; font-weight: 800; }
        .side .parcel .wqr { text-align: center; }
        .side .parcel .wqr .wt { font-size: 12px; font-weight: 800; margin-top: 1px; }
        /* Booking + Dispatch dates — clean two-row box (moved from the top row) */
        .side .datebox { border-bottom: 1px solid #111827; padding: 6px 10px; }
        .side .datebox .drow { display: flex; justify-content: space-between; align-items: baseline; padding: 4px 0; }
        .side .datebox .drow + .drow { border-top: 1px dashed #9ca3af; }
        .side .datebox .dk { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; color: #6b7280; }
        .side .datebox .dv { font-size: 14px; font-weight: 800; text-align: right; }
        .side .datebox .dv small { display: block; font-size: 10px; font-weight: 600; color: #6b7280; }

        /* Remarks — separate manual (handwriting) box below the address */
        .remarks { padding: 5px 10px; }
        .remarks .k { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #111827; }
        .remarks .space { height: 26px; }
        .remarks .rtext { font-size: 14px; font-weight: 700; margin-top: 2px; line-height: 1.4; }

        /* Postman note — full width, bold, unmissable. English stays on ONE line. */
        .note { padding: 7px 8px; text-align: center; }
        .note .urdu { font-size: 14px; font-weight: 700; line-height: 1.65; }
        .note .en { font-size: 11px; font-weight: 700; line-height: 1.5; white-space: nowrap; }

        /* Dark footer — phone · website · address all on ONE line, never wraps */
        .footer { background: #111827; color: #fff; padding: 8px 12px; display: flex; align-items: center; justify-content: space-around; gap: 10px; flex-wrap: nowrap; overflow: hidden; }
        .footer span { white-space: nowrap; font-size: 11px; font-weight: 800; }

        @media print {
            body { background: #fff; }
            .toolbar { display: none; }
            /* Keep the sheet at its fixed 210mm width so the layout NEVER reflows.
               Portrait or landscape, the browser just scales the whole slip to fit
               the page (Fit to page) — same alignment, nothing overflows. */
            .sheet { margin: 0 auto; border: 2px solid #111827; width: 210mm; }
            @page { margin: 6mm; }
        }
    </style>
</head>
@php
    $showEn = $lang !== 'ur';
    $showUr = $lang !== 'en';

    // Per-branch slip: the order's own branch supplies contact info, falling
    // back to the global website/brand settings.
    $branch = $order->branch;
    // Slip logo is language-specific (settings): Urdu slip → Urdu logo, English/
    // Both → English logo, each falling back to the other, then branch/static logo.
    $logoEn   = setting('dispatch_logo_en');
    $logoUr   = setting('dispatch_logo_ur');
    $slipLogo = $lang === 'ur' ? ($logoUr ?: $logoEn) : ($logoEn ?: $logoUr);
    $hasSlipLogo = (bool) $slipLogo;
    $brandLogo = $slipLogo
        ? asset('storage/'.$slipLogo)
        : ($branch?->logo ? asset('storage/'.$branch->logo) : asset('assets/images/brand/almufeed-traders.png'));
    $company = [
        'name'    => $branch?->name ?: setting('site_name', 'SALAL COLLECTION'),
        'name_ur' => setting('site_name_ur', 'المفید اسلامی ثقافتی مرکز'),
        'phone'   => $branch?->phone ?: setting('site_phone'),
        'addr'    => $branch?->address ?: setting('site_address'),
        'website' => setting('site_website', 'www.almufeed.com.pk'),
    ];

    // "From" (sender) box — controller decides the default per customer type:
    //   • regular customer            → company sender (shown)
    //   • reseller w/ own address     → reseller sender (shown)
    //   • reseller w/o own address    → hidden
    // $from is 'company' | 'reseller' | 'hide' (operator can override via toolbar).
    $isReseller = $from === 'reseller' && $order->from_name;
    $showFrom   = $from !== 'hide';
    $senderName = $isReseller
        ? $order->from_name
        : ($lang === 'ur' ? $company['name_ur'] : $company['name']);
    $sender = $isReseller
        ? ['name' => $order->from_name, 'phone' => $order->from_phone, 'addr' => $order->from_address]
        : ['name' => $company['name'], 'phone' => $company['phone'], 'addr' => $company['addr']];

    // ── COD amount to collect on delivery. Operator can override it on the order
    // page (dispatch_cod_amount); blank falls back to auto: paid → 0, else balance.
    $isPaid = in_array($order->online_payment_status, ['paid', 'bank_paid'], true)
        || $order->payment_status === 'paid'
        || (float) $order->balance_amount <= 0;
    $codAmt = $order->dispatch_cod_amount !== null
        ? (float) $order->dispatch_cod_amount
        : ($isPaid ? 0.0 : (float) $order->balance_amount);
    $isCod  = $codAmt > 0;

    $hasTracking = (bool) $order->tracking_id;

    // Order-tracking QR — scannable, opens the order's public tracking page.
    $payUrl    = $order->receipt_token ? route('shop.track.view', $order->receipt_token) : url('/');
    $weightTxt = (rtrim(rtrim(number_format((float) $order->weight, 3), '0'), '.') ?: '0') . ' kg';
    $pieces    = (int) $order->items->sum('quantity');

    $postmanEn = setting('dispatch_postman_note', 'Dear postman: if delivering this parcel is difficult, please call ' . ($company['phone'] ?: '') . ' — but kindly ensure delivery. Thank you.');
    $postmanUr = setting('dispatch_postman_note_ur', 'محترم ڈاک صاحب! اگر پارسل وصول کنندہ تک پہنچانے میں کوئی مشکل ہو تو ' . ($company['phone'] ?: '') . ' اس نمبر پر رابطہ کریں مگر پارسل کی ڈیلیوری یقینی بنائیں۔ شکریہ');

    $base = url()->current();
    $q = fn($params) => $base . '?' . http_build_query(array_merge(request()->query(), $params));

    // Bilingual inline label helper.
    $bi = function ($en, $ur) use ($showEn, $showUr) {
        $parts = [];
        if ($showUr) $parts[] = '<span class="urdu">' . e($ur) . '</span>';
        if ($showEn) $parts[] = '<span>' . e($en) . '</span>';
        return implode(' / ', $parts);
    };

    // Header logo side flips by language: company logo LEFT for English/Both,
    // RIGHT for Urdu (client). Only the CSS order swaps — placement is unchanged.
    $companyOnRight = $lang === 'ur';
@endphp
<body>
    <div class="toolbar">
        <strong style="font-size:12px;">Language:</strong>
        <a href="{{ $q(['lang'=>'en']) }}" class="{{ $lang==='en'?'on':'' }}">English</a>
        <a href="{{ $q(['lang'=>'ur']) }}" class="{{ $lang==='ur'?'on':'' }}">اردو</a>
        <span style="width:1px;height:18px;background:#e5e7eb;"></span>
        <strong style="font-size:12px;">From:</strong>
        <a href="{{ $q(['from'=>'company']) }}" class="{{ $from==='company'?'on':'' }}">Company</a>
        @if ($order->from_name)
            <a href="{{ $q(['from'=>'reseller']) }}" class="{{ $from==='reseller'?'on':'' }}">Reseller</a>
        @endif
        <a href="{{ $q(['from'=>'hide']) }}" class="{{ $from==='hide'?'on':'' }}">Hide</a>
        <span style="width:1px;height:18px;background:#e5e7eb;"></span>
        <strong style="font-size:12px;">Size:</strong>
        <a href="{{ $q(['scale'=>100]) }}" class="{{ $scale===100?'on':'' }}">100%</a>
        <a href="{{ $q(['scale'=>85]) }}" class="{{ $scale===85?'on':'' }}">85%</a>
        <a href="{{ $q(['scale'=>70]) }}" class="{{ $scale===70?'on':'' }}">70%</a>
        @if (! $hasTracking)
            <span style="font-size:11px;color:#b45309;background:#fffbeb;border:1px solid #fde68a;padding:4px 8px;border-radius:6px;">⚠ Add a tracking number to print the barcode</span>
        @endif
        <button class="print" style="margin-left:auto;background:#047857;border-color:#047857;" onclick="downloadSlip()">⬇ Image</button>
        <button class="print" style="margin-left:8px;" onclick="window.print()">🖨 Print</button>
        <a href="{{ route('admin.online-orders.show', $order) }}">← Back</a>
    </div>

    <div class="sheet" id="sheet" style="zoom: {{ $scale / 100 }};">
        {{-- ── Header: courier logo · title (top) · company logo — no dividers.
             Company/courier side flips by language via CSS order (placement kept). ── --}}
        <div class="head">
            <div class="courier" style="order: {{ $companyOnRight ? 0 : 2 }};">
                @if ($dispatchMethod?->logo)
                    <img class="clogo" src="{{ asset('storage/'.$dispatchMethod->logo) }}" alt="" onerror="this.style.display='none'">
                @else
                    <div class="ph urdu">کوریئر</div>
                    <div class="cname">{{ $order->dispatch_method ?: 'Courier' }}</div>
                @endif
            </div>
            <div class="title" style="order: 1;">
                <div class="t">Dispatch Slip</div>
                <div class="sub">{{ $isCod ? 'COD Parcel' : 'General Parcel' }}</div>
            </div>
            <div class="brand" style="order: {{ $companyOnRight ? 2 : 0 }};">
                <img class="blogo" src="{{ $brandLogo }}" alt="" onerror="this.style.display='none'">
                @unless ($hasSlipLogo)
                    <div class="cname urdu">{{ $company['name_ur'] }}</div>
                @endunless
            </div>
        </div>

        {{-- ── Row: Tracking+barcode · Order No + QR · Date ── --}}
        <div class="row3">
            <div class="track">
                @if ($hasTracking)
                    <svg id="barcode-track"></svg>
                    <div class="trackno">{{ $showUr ? 'ٹریکنگ' : 'Tracking' }} {{ $order->tracking_id }}</div>
                @else
                    <div class="empty">{{ $showUr ? 'ٹریکنگ نمبر بعد میں' : 'Tracking added later' }}</div>
                @endif
            </div>
            <div>
                <div class="k">{!! $bi('Order No', 'آرڈر نمبر') !!}</div>
                <div class="orderbox">
                    <div class="ordno">{{ $order->order_number }}</div>
                    <div class="oqr">
                        <div id="qr-track"></div>
                    </div>
                </div>
            </div>
            <div class="codcell {{ $isCod ? 'due' : '' }}">
                <div class="cline">{!! $bi('COD Amount', 'وصولی رقم') !!}: <b class="{{ $isCod ? '' : 'paid' }}">Rs. {{ number_format($codAmt, 0) }}</b></div>
                @if ($isCod)
                    <svg id="barcode-cod"></svg>
                @endif
            </div>
        </div>

        {{-- ── Main: [From — reseller only] · To · COD/Parcel ── --}}
        <div class="main">
            @if ($showFrom)
                <div class="from">
                    <div class="lead">{{ $showUr ? 'مرسل / From / Shipper' : 'From / Shipper' }}</div>
                    <div class="fname">{{ $senderName }}</div>
                    <div class="fbody">
                        @if ($sender['phone'])☎ {{ $sender['phone'] }}<br>@endif
                        {{ $sender['addr'] }}
                    </div>
                </div>
            @endif

            {{-- TO — stylish customer block --}}
            <div class="to">
                <div class="lead">{{ $showUr ? 'وصول کنندہ / To / Consignee' : 'To / Consignee' }}</div>
                <div class="cname">{{ $order->shipping_first_name }} {{ $order->shipping_last_name }}</div>
                <div class="cphone">☎ {{ $order->shipping_phone }}</div>
                <div class="caddr">
                    {{ $order->shipping_address1 }}
                    @if ($order->shipping_address2)<br>{{ $order->shipping_address2 }}@endif
                </div>
                <div class="cmeta">
                    @if ($order->shipping_tehsil)<b>{!! $bi('Tehsil', 'تحصیل') !!}:</b> {{ $order->shipping_tehsil }} &nbsp; @endif
                    <b>{!! $bi('District', 'ضلع') !!}:</b> {{ $order->shipping_district ?: $order->shipping_city ?: '—' }}
                    @if ($order->shipping_post_code) — {{ $order->shipping_post_code }}@endif
                    <br>
                    <b>{!! $bi('Province', 'صوبہ') !!}:</b> {{ $order->shipping_province ?: '—' }} ({{ $order->shipping_country ?: 'Pakistan' }})
                </div>
            </div>

            {{-- COD amount + barcode, then parcel facts + weight QR --}}
            <div class="side">
                <div class="datebox">
                    <div class="drow">
                        <span class="dk">{!! $bi('Booking', 'بکنگ') !!}</span>
                        <span class="dv">{{ $order->created_at?->format('d M Y') }}<small>{{ $order->created_at?->format('h:i A') }}</small></span>
                    </div>
                    <div class="drow">
                        <span class="dk">{!! $bi('Dispatch', 'ڈسپیچ') !!}</span>
                        <span class="dv">{{ $dispatchedAt ? \Illuminate\Support\Carbon::parse($dispatchedAt)->format('d M Y') : '—' }}</span>
                    </div>
                </div>
                <div class="parcel">
                    <div class="facts">
                        <div>{!! $bi('Pieces', 'تعداد') !!}: <b>{{ $pieces }}</b> {{ $showEn ? 'in 1 parcel' : '' }}</div>
                    </div>
                    <div class="wqr">
                        <div id="qr-weight"></div>
                        <div class="wt">{{ $showUr ? 'وزن' : 'Weight' }}: {{ $weightTxt }}</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ── Remarks — only prints when the operator typed something ── --}}
        @if (trim((string) $order->dispatch_remarks) !== '')
            <div class="remarks">
                <span class="k">{!! $bi('Remarks', 'ریمارکس') !!}</span>
                <div class="rtext">{{ $order->dispatch_remarks }}</div>
            </div>
        @endif

        {{-- ── Postman note (full width, no heading) ── --}}
        <div class="note">
            @if ($showUr)<div class="urdu">{{ $postmanUr }}</div>@endif
            @if ($showEn)<div class="en">{{ $postmanEn }}</div>@endif
        </div>

        {{-- ── Dark footer: contact · web · address spread across the line ── --}}
        <div class="footer">
            @if ($company['phone'])<span>☎ {{ $company['phone'] }}</span>@endif
            @if ($company['website'])<span>🌐 {{ $company['website'] }}</span>@endif
            @if ($company['addr'])<span>📍 {{ $company['addr'] }}</span>@endif
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            @if ($hasTracking)
            // Tracking barcode (Code128); readable number prints once below it.
            try { JsBarcode('#barcode-track', @json((string) $order->tracking_id), { format: 'CODE128', width: 1.6, height: 36, displayValue: false, margin: 0 }); } catch (e) {}
            @endif
            @if ($isCod)
            // COD amount barcode — scanning it reads back the amount to collect.
            try { JsBarcode('#barcode-cod', @json((string) (int) round($codAmt)), { format: 'CODE128', width: 1.5, height: 26, displayValue: false, margin: 0 }); } catch (e) {}
            @endif
            // Order-tracking QR — sits beside the order number.
            try { new QRCode(document.getElementById('qr-track'), { text: @json($payUrl), width: 58, height: 58, correctLevel: QRCode.CorrectLevel.M }); } catch (e) {}
            // Weight QR — encodes the parcel weight, with the weight printed below.
            try { new QRCode(document.getElementById('qr-weight'), { text: @json($weightTxt), width: 56, height: 56, correctLevel: QRCode.CorrectLevel.M }); } catch (e) {}
        });

        // Download the slip as a PNG image (alongside Print).
        function downloadSlip() {
            var el = document.getElementById('sheet');
            var prevZoom = el.style.zoom;
            el.style.zoom = 1; // capture at full resolution regardless of preview scale
            html2canvas(el, { scale: 2, backgroundColor: '#ffffff', useCORS: true }).then(function (canvas) {
                el.style.zoom = prevZoom;
                var a = document.createElement('a');
                a.download = 'dispatch-{{ $order->order_number }}.png';
                a.href = canvas.toDataURL('image/png');
                a.click();
            }).catch(function () { el.style.zoom = prevZoom; });
        }
    </script>
</body>
</html>
