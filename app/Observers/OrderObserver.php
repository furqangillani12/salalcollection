<?php

namespace App\Observers;

use App\Models\LedgerEntry;
use App\Models\Order;
use App\Services\LedgerService;

class OrderObserver
{
    /**
     * When an order is created, record it in the ledger.
     * POS orders are born "completed"; online orders are born "pending"
     * and are recognised later (on delivery) via updated().
     */
    public function created(Order $order): void
    {
        if ($this->isRecognisedSale($order)) {
            $this->recordOrder($order);
        }
    }

    /**
     * When an order is updated, recognise the sale once it reaches its
     * channel's "done" status:
     *   - POS / counter orders  → 'completed'
     *   - Online / storefront    → 'delivered'
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('status') && $this->isRecognisedSale($order)) {
            $this->recordOrder($order);
        }
    }

    /**
     * A sale is recognised for accounting when:
     *   - a POS order is 'completed', OR
     *   - an online order is 'delivered' (goods handed to the customer).
     */
    private function isRecognisedSale(Order $order): bool
    {
        if ($order->order_source === 'online') {
            return $order->status === 'delivered';
        }

        return $order->status === Order::STATUS_COMPLETED;
    }

    private function recordOrder(Order $order): void
    {
        // Idempotency: never post a second sale entry for the same order
        // (online statuses can be toggled, e.g. shipped → delivered → shipped).
        $alreadyPosted = LedgerEntry::where('reference_type', Order::class)
            ->where('reference_id', $order->id)
            ->whereIn('transaction_type', [LedgerEntry::TYPE_SALE, LedgerEntry::TYPE_CREDIT_SALE])
            ->exists();

        if ($alreadyPosted) {
            return;
        }

        if ($order->payment_method === 'credit' || $order->credit_status === 'pending') {
            LedgerService::recordCreditSale($order);
        } else {
            LedgerService::recordSale($order);
        }
    }
}