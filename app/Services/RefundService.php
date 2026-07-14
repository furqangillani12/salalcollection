<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Refund;
use Illuminate\Support\Facades\DB;

class RefundService
{
    public function processRefund(Order $order, float $amount, string $reason, int $userId, bool $returnToInventory = false)
    {
        if (!$order->isRefundable()) {
            throw new \Exception('This order cannot be refunded');
        }

        if ($amount > $order->total) {
            throw new \Exception('Refund amount cannot exceed order total');
        }

        return DB::transaction(function () use ($order, $amount, $reason, $userId, $returnToInventory) {
            // Create refund record
            $refund = Refund::create([
                'order_id' => $order->id,
                'user_id' => $userId,
                'amount' => $amount,
                'reason' => $reason,
                'status' => 'completed'
            ]);

            // Update order status
            $order->update([
                'status' => $amount >= $order->total ?
                    Order::STATUS_REFUNDED : Order::STATUS_COMPLETED
            ]);

            // Process inventory returns if needed
            if ($returnToInventory) {
                $this->returnItemsToInventory($order);
            }

            // In a real application, you would also process the payment refund here

            return $refund;
        });
    }

    protected function returnItemsToInventory(Order $order)
    {
        foreach ($order->items as $item) {
            if ($item->product->track_inventory) {
                $inventory = $item->variant ? $item->variant->inventory : $item->product->inventory;
                $inventory->increment('quantity', $item->quantity);
            }
        }
    }
}
