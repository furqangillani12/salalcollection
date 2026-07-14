<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    /** @var array{heading:string,intro:string,lines:array<int,string>} */
    public array $content;

    public function __construct(public Order $order, public string $event)
    {
        $this->content = self::contentFor($order, $event);
    }

    public function build(): self
    {
        $num = $this->order->order_number;
        return $this->subject($this->content['heading'] . " — Order {$num}")
            ->view('emails.order-status');
    }

    /**
     * Build human copy for each lifecycle event. Unknown events fall back to a
     * generic status line so callers can pass any status safely.
     */
    public static function contentFor(Order $order, string $event): array
    {
        $num = $order->order_number;
        $key = $event === 'placed' ? 'placed' : order_status_norm($event);

        $content = match ($key) {
            'placed' => [
                'heading' => 'Order received',
                'intro'   => "Thank you! We've received your order {$num} and will confirm it shortly.",
                'lines'   => ['We will message you as your order is confirmed, packed, dispatched and delivered.'],
            ],
            'confirmed' => [
                'heading' => 'Order confirmed',
                'intro'   => "Good news — your order {$num} has been confirmed and is being prepared.",
                'lines'   => [],
            ],
            'packed' => [
                'heading' => 'Order packed',
                'intro'   => "Your order {$num} has been packed and is ready to be dispatched.",
                'lines'   => [],
            ],
            'dispatched' => [
                'heading' => 'Order dispatched',
                'intro'   => "Your order {$num} is on its way" . ($order->dispatch_method ? " via {$order->dispatch_method}" : '') . '.',
                'lines'   => array_values(array_filter([
                    $order->tracking_id ? "Tracking number: {$order->tracking_id}" : null,
                    order_track_url($order) ? 'Track it here: ' . order_track_url($order) : null,
                ])),
            ],
            'delivered' => [
                'heading' => 'Order delivered',
                'intro'   => "Your order {$num} has been delivered. Thank you for shopping with us!",
                'lines'   => [],
            ],
            'returned' => [
                'heading' => 'Order returned',
                'intro'   => "Your order {$num} has been marked as returned. Please contact us if you have any questions.",
                'lines'   => [],
            ],
            'cancelled' => [
                'heading' => 'Order cancelled',
                'intro'   => "Your order {$num} has been cancelled. If this is unexpected, please contact us.",
                'lines'   => [],
            ],
            default => [
                'heading' => 'Order update',
                'intro'   => "There's an update on your order {$num}: " . ucfirst(str_replace('_', ' ', $event)) . '.',
                'lines'   => [],
            ],
        };

        // Append the admin-editable note for this status (e.g. review-points
        // copy) below the email's own structured copy — template only, so the
        // greeting/detail isn't duplicated (#M5).
        if ($key !== 'placed') {
            $extra = order_status_template($order, $key);
            if ($extra !== '') $content['lines'][] = $extra;
        }

        return $content;
    }

    /**
     * Resolve recipient + send safely. Never throws — a mail failure must not
     * break checkout or an admin status change. No-ops when no email on file.
     */
    public static function dispatchFor(Order $order, string $event): void
    {
        $to = $order->customer_email ?: $order->customer?->email;
        if (empty($to)) return;

        try {
            Mail::to($to)->send(new self($order, $event));
        } catch (\Throwable $e) {
            Log::warning('Order status email failed', [
                'order' => $order->id, 'event' => $event, 'error' => $e->getMessage(),
            ]);
        }
    }
}
