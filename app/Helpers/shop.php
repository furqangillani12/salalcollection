<?php

use App\Models\CartItem;
use App\Models\Setting;
use App\Services\Shop\CartService;
use Illuminate\Support\Facades\Auth;

if (!function_exists('setting')) {
    /**
     * Read a site setting (social links, contact info, etc.) managed from the
     * admin Settings screen. Cached. Falls back to $default when unset/empty.
     */
    function setting(string $key, $default = null)
    {
        return Setting::get($key, $default);
    }
}

if (!function_exists('wa_link')) {
    /**
     * Build a WhatsApp click-to-chat link (wa.me) for a number + prefilled text.
     * Returns null if no usable number.
     */
    function wa_link(?string $number, string $text = ''): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $number);
        if ($digits === '') return null;
        // Pakistani local 03xx… → 92 3xx…
        if (str_starts_with($digits, '0')) {
            $digits = '92' . ltrim($digits, '0');
        }
        $url = 'https://wa.me/' . $digits;
        if ($text !== '') $url .= '?text=' . rawurlencode($text);
        return $url;
    }
}

if (!function_exists('shop_whatsapp_number')) {
    /** The shop's public WhatsApp number from settings. */
    function shop_whatsapp_number(): ?string
    {
        return setting('social_whatsapp') ?: setting('site_whatsapp') ?: null;
    }
}

if (!function_exists('courier_track_url')) {
    /**
     * Build a public courier tracking URL from a dispatch-method name and a
     * tracking/consignment number. Returns null when we can't form one, so the
     * caller can fall back to plain text.
     */
    function courier_track_url(?string $dispatchMethod, ?string $trackingId): ?string
    {
        $trackingId = trim((string) $trackingId);
        if ($trackingId === '') return null;

        $key = strtolower((string) $dispatchMethod);
        $code = rawurlencode($trackingId);

        return match (true) {
            str_contains($key, 'tcs')      => "https://www.tcsexpress.com/track/?trackingNo={$code}",
            str_contains($key, 'leopard')  => "https://www.leopardscourier.com/leopards-tracking?cn_number={$code}",
            str_contains($key, 'm&p') ,
            str_contains($key, 'mp ')      => "https://mulphilog.com/track-and-trace?tracking={$code}",
            str_contains($key, 'post')     => "https://ep.gov.pk/track.asp?id={$code}",
            str_contains($key, 'daewoo')   => "https://daewoo.com.pk/track-parcel/?tracking={$code}",
            str_contains($key, 'trax')     => "https://sonic.pk/tracking?id={$code}",
            default                        => "https://www.google.com/search?q=" . rawurlencode(trim($dispatchMethod . ' tracking ' . $trackingId)),
        };
    }
}

if (!function_exists('shop_is_reseller')) {
    /** True when the logged-in customer buys at a reseller/wholesale tier. */
    function shop_is_reseller(): bool
    {
        $type = Auth::guard('customer')->user()?->customer_type ?? 'customer';
        return in_array($type, ['reseller', 'wholesale'], true);
    }
}

if (!function_exists('shop_strike_price')) {
    /**
     * The reference price to show struck-through next to what the visitor pays.
     * - Reseller / wholesale: the RETAIL price (sale_price) so they see their margin.
     * - Retail customer: the list/MRP price (price) when it's higher (a real sale).
     * Returns null when there's nothing meaningful to strike out.
     */
    function shop_strike_price($product): ?float
    {
        $paid = shop_product_price($product);
        if (shop_is_reseller()) {
            $retail = (float) ($product->sale_price ?: $product->price ?: 0);
            return $retail > $paid ? $retail : null;
        }
        $list = (float) ($product->price ?? 0);
        return $list > $paid ? $list : null;
    }
}

if (!function_exists('shop_order_points')) {
    /**
     * Points earned for an order total, using the "rupees per point" setting.
     * Returns 0 when the scheme is disabled (setting unset or 0).
     */
    function shop_order_points($total): int
    {
        $per = (float) setting('points_rupees_per_point', 0);
        if ($per <= 0) return 0;
        return (int) floor(((float) $total) / $per);
    }
}

if (!function_exists('shop_point_value')) {
    /**
     * Rupee value of ONE reward point on redemption (setting "points_value_rupees").
     * Decimal-friendly, e.g. 0.20 means 5 points = Rs 1. 0 disables redemption.
     */
    function shop_point_value(): float
    {
        return (float) setting('points_value_rupees', 0);
    }
}

if (!function_exists('shop_points_to_rupees')) {
    /** Rupee worth of a points amount, rounded to 2 dp. */
    function shop_points_to_rupees(int $points): float
    {
        return round(max(0, $points) * shop_point_value(), 2);
    }
}

if (!function_exists('shop_max_redeemable_points')) {
    /**
     * How many of the customer's points can actually be applied to an order,
     * capped so the points discount never exceeds the spendable amount.
     */
    function shop_max_redeemable_points(int $balance, float $maxRupees): int
    {
        $value = shop_point_value();
        if ($value <= 0 || $balance <= 0 || $maxRupees <= 0) return 0;
        return (int) min($balance, floor($maxRupees / $value));
    }
}

if (!function_exists('order_status_norm')) {
    /** Map legacy statuses onto the current online lifecycle vocabulary. */
    function order_status_norm(?string $status): string
    {
        return match ($status) {
            'shipped'   => 'dispatched',
            'completed' => 'delivered',
            default     => $status ?: 'pending',
        };
    }
}

if (!function_exists('order_status_meta')) {
    /** Label/icon/colours for a status, with a safe fallback. */
    function order_status_meta(?string $status): array
    {
        $key = order_status_norm($status);
        $all = config('order_flow.statuses', []);
        return $all[$key] ?? ['label' => ucfirst((string) $status), 'icon' => 'fa-circle', 'bg' => '#f3f4f6', 'text' => '#374151'];
    }
}

if (!function_exists('order_holder_phone')) {
    /**
     * The number the account was opened on — the registered customer's phone,
     * else the order's shipping phone. Status messages go HERE (not to a
     * reseller's end-customer).
     */
    function order_holder_phone($order): ?string
    {
        return $order->customer?->phone ?: $order->shipping_phone;
    }
}

if (!function_exists('order_track_url')) {
    /** Courier tracking deep-link for a dispatched order, or null. */
    function order_track_url($order): ?string
    {
        return courier_track_url($order->dispatch_method, $order->tracking_id);
    }
}

if (!function_exists('order_status_tokens')) {
    /** Placeholder map shared by the status template + message builders. */
    function order_status_tokens($order, string $status): array
    {
        $name = trim(($order->shipping_first_name ?? '') . ' ' . ($order->shipping_last_name ?? ''))
              ?: ($order->customer?->name ?? 'there');
        return [
            '{order}'      => $order->order_number,
            '{name}'       => $name,
            '{status}'     => order_status_meta($status)['label'],
            '{courier}'    => $order->dispatch_method ?? '',
            '{tracking}'   => $order->tracking_id ?? '',
            '{track_link}' => order_track_url($order) ?? '',
            '{total}'      => 'Rs. ' . number_format((float) $order->total, 0),
        ];
    }
}

if (!function_exists('order_status_template')) {
    /**
     * The admin-editable note for a status (setting "status_msg_{status}") with
     * placeholders expanded, or '' when none is configured. This is appended
     * BELOW the structured detail — it never replaces it.
     */
    function order_status_template($order, ?string $status = null): string
    {
        $status = order_status_norm($status ?: $order->status);
        $tpl = setting('status_msg_' . $status);
        return $tpl ? strtr($tpl, order_status_tokens($order, $status)) : '';
    }
}

if (!function_exists('order_status_message')) {
    /**
     * Full customer-facing message for a status change (WhatsApp / SMS). Always
     * starts with a greeting and the structured order detail (name, order #,
     * courier, tracking, track link), then appends the admin's template note
     * below — so configuring a template adds to the message, never strips the
     * detail (#M4/#M5).
     */
    function order_status_message($order, ?string $status = null): string
    {
        $status = order_status_norm($status ?: $order->status);
        $name   = trim(($order->shipping_first_name ?? '') . ' ' . ($order->shipping_last_name ?? '')) ?: ($order->customer?->name ?? 'there');
        $track  = order_track_url($order);
        $label  = order_status_meta($status)['label'];

        $lines = ['Assalam-o-Alaikum', "Dear: {$name},", "your order: {$order->order_number}"];

        switch ($status) {
            case 'dispatched':
                $lines[] = 'has been dispatched' . ($order->dispatch_method ? " via {$order->dispatch_method}" : '') . '.';
                if ($order->tracking_id) $lines[] = "Tracking: {$order->tracking_id}.";
                if ($track)              $lines[] = "Track: {$track}";
                break;
            case 'confirmed':
                $lines[] = 'has been confirmed and is being prepared.';
                break;
            case 'packed':
                $lines[] = 'has been packed and is ready to dispatch.';
                break;
            case 'delivered':
                $lines[] = 'has been delivered. Thank you for shopping with us!';
                break;
            case 'returned':
                $lines[] = 'has been marked as returned.';
                break;
            case 'cancelled':
                $lines[] = 'has been cancelled.';
                break;
            default:
                $lines[] = 'is now ' . $label . '.';
        }

        $message = implode("\n", $lines);

        // Append the admin note below the structured detail (if configured).
        $extra = order_status_template($order, $status);
        if ($extra !== '') $message .= "\n\n" . $extra;

        return $message;
    }
}

if (!function_exists('shop_package_price')) {
    /** The package price for the current visitor's tier (retail/reseller/wholesale). */
    function shop_package_price($package): float
    {
        $type = Auth::guard('customer')->user()?->customer_type ?? 'customer';
        return match ($type) {
            'reseller'  => (float) ($package->resale_price    ?: $package->sale_price),
            'wholesale' => (float) ($package->wholesale_price ?: $package->sale_price),
            default     => (float) $package->sale_price,
        };
    }
}

if (!function_exists('shop_tax_rate')) {
    /** Storefront tax rate (percent value or fixed amount) from settings. */
    function shop_tax_rate(): float
    {
        return (float) setting('shop_tax_rate', 0);
    }
}

if (!function_exists('shop_tax_type')) {
    /** 'percent' (default) or 'fixed' — how the storefront tax is applied. */
    function shop_tax_type(): string
    {
        return setting('shop_tax_type', 'percent') === 'fixed' ? 'fixed' : 'percent';
    }
}

if (!function_exists('shop_tax_amount')) {
    /**
     * Tax on a taxable base, computed exactly like the POS receipt
     * (exclusive: added on top). Returns 0 when no rate is set.
     */
    function shop_tax_amount(float $base): float
    {
        $rate = shop_tax_rate();
        if ($rate <= 0 || $base <= 0) return 0.0;
        return shop_tax_type() === 'fixed' ? $rate : round($base * $rate / 100, 2);
    }
}

if (!function_exists('shop_cart_count')) {
    /**
     * Sum of cart item quantities for the current customer or session.
     */
    function shop_cart_count(): int
    {
        return (int) app(CartService::class)->count();
    }
}

if (!function_exists('shop_cart_subtotal')) {
    function shop_cart_subtotal(): float
    {
        return (float) app(CartService::class)->subtotal();
    }
}

if (!function_exists('shop_image')) {
    /**
     * Resolve a stored image path to a public URL, with a graceful placeholder.
     */
    function shop_image(?string $path, string $placeholder = 'https://placehold.co/600x750/f5f1e8/0c1f3d?text=Almufeed'): string
    {
        if (empty($path)) return $placeholder;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
        if (str_starts_with($path, '/')) return $path;
        return asset('storage/' . ltrim($path, '/'));
    }
}

if (!function_exists('shop_price')) {
    /**
     * Format a price the Pakistani way.
     */
    function shop_price($amount): string
    {
        return 'Rs. ' . number_format((float) $amount, 0);
    }
}

if (!function_exists('shop_product_price')) {
    /**
     * Choose the right price for the visitor based on customer_type
     * (retail / reseller / wholesale). Falls back to sale_price.
     */
    function shop_product_price($product): float
    {
        $type = Auth::guard('customer')->user()?->customer_type ?? 'customer';
        return match ($type) {
            'reseller'  => (float) ($product->resale_price    ?: $product->sale_price ?: $product->price),
            'wholesale' => (float) ($product->wholesale_price ?: $product->sale_price ?: $product->price),
            default     => (float) ($product->sale_price ?: $product->price),
        };
    }
}
