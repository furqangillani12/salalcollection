<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Heads-up email to the shop team the moment a website order is placed, so they
 * can take action quickly. Sent to the configured store email (Settings →
 * site_email), falling back to the mail "from" address. Safe no-op on failure.
 */
class NewOrderAdminMail extends Mailable
{
    public function __construct(public Order $order) {}

    public function build(): self
    {
        return $this->subject('🛒 New website order — ' . $this->order->order_number)
            ->view('emails.new-order-admin');
    }

    public static function dispatchFor(Order $order): void
    {
        $to = setting('site_email') ?: config('mail.from.address');
        if (empty($to)) return;

        try {
            Mail::to($to)->send(new self($order->loadMissing('items')));
        } catch (\Throwable $e) {
            Log::warning('New-order admin email failed', [
                'order' => $order->id, 'error' => $e->getMessage(),
            ]);
        }
    }
}
