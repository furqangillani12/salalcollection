<!DOCTYPE html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;background:#faf7f7;font-family:Arial,Helvetica,sans-serif;color:#2b2127;">
    <div style="max-width:560px;margin:0 auto;padding:24px;">
        <div style="background:#1b1f5c;border-radius:14px 14px 0 0;padding:22px 24px;text-align:center;">
            <div style="color:#fff;font-size:18px;font-weight:bold;letter-spacing:.5px;">SALAL COLLECTION</div>
        </div>
        <div style="background:#fff;border:1px solid #eee;border-top:none;border-radius:0 0 14px 14px;padding:26px 24px;">
            <h1 style="margin:0 0 10px;font-size:20px;color:#2e3192;">{{ $content['heading'] }}</h1>
            <p style="margin:0 0 14px;font-size:15px;line-height:1.6;">{{ $content['intro'] }}</p>

            @foreach ($content['lines'] as $line)
                <p style="margin:0 0 8px;font-size:14px;line-height:1.6;color:#444;">{{ $line }}</p>
            @endforeach

            <table style="width:100%;border-collapse:collapse;margin:18px 0;font-size:14px;">
                <tr><td style="padding:6px 0;color:#888;">Order number</td><td style="padding:6px 0;text-align:right;font-weight:bold;">{{ $order->order_number }}</td></tr>
                <tr><td style="padding:6px 0;color:#888;">Status</td><td style="padding:6px 0;text-align:right;text-transform:capitalize;">{{ str_replace('_',' ', $order->status) }}</td></tr>
                <tr><td style="padding:6px 0;color:#888;">Total</td><td style="padding:6px 0;text-align:right;font-weight:bold;">{{ shop_price($order->total) }}</td></tr>
            </table>

            @php
                $trackUrl = $order->receipt_token ? route('shop.track.view', $order->receipt_token) : route('shop.track');
            @endphp
            <a href="{{ $trackUrl }}" style="display:inline-block;background:#2e3192;color:#fff;text-decoration:none;font-weight:bold;font-size:14px;padding:11px 22px;border-radius:10px;">Track your order</a>

            @if (shop_whatsapp_number())
                <a href="{{ wa_link(shop_whatsapp_number(), 'Hi, I have a question about my order ' . $order->order_number) }}"
                   style="display:inline-block;background:#25D366;color:#fff;text-decoration:none;font-weight:bold;font-size:14px;padding:11px 22px;border-radius:10px;margin-left:8px;">WhatsApp us</a>
            @endif

            <p style="margin:22px 0 0;font-size:12px;color:#999;line-height:1.6;">
                Need help? Reply to this email.
                @if (setting('site_phone')) Or call {{ setting('site_phone') }}. @endif
                <br>
                {{ setting('site_address', 'PanjGirain, Tehsil Darya Khan, District Bhakkar') }}
            </p>
        </div>
        <p style="text-align:center;font-size:11px;color:#bbb;margin:16px 0 0;">&copy; {{ now()->year }} SALAL COLLECTION</p>
    </div>
</body>
</html>
