<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Console\Command;

class RecalculateCustomerBalances extends Command
{
    protected $signature = 'customers:recalculate-balances {--customer= : Recalculate for a specific customer ID} {--dry-run : Show changes without saving}';
    protected $description = 'Recalculate customer current_balance and fix order previous_balance values';

    public function handle()
    {
        $dryRun     = $this->option('dry-run');
        $customerId = $this->option('customer');

        $query = Customer::query();
        if ($customerId) {
            $query->where('id', $customerId);
        }

        $customers = $query->get();
        $fixedCustomers = 0;
        $fixedOrders    = 0;

        $this->info(($dryRun ? '[DRY RUN] ' : '') . "Recalculating balances for {$customers->count()} customer(s)...\n");

        foreach ($customers as $customer) {
            // ── Get all orders and khata payments sorted by date ──
            $orders = Order::where('customer_id', $customer->id)
                ->where('status', '!=', 'cancelled')
                ->orderBy('created_at')
                ->get();

            $khataPayments = Payment::where('customer_id', $customer->id)
                ->whereIn('payment_type', ['khata', 'khata_payout'])
                ->orderBy('payment_date')
                ->get();

            // ── Merge into timeline ──
            $timeline = collect();

            foreach ($orders as $order) {
                $paidOnOrder = ($order->paid_amount == 0 && $order->balance_amount == 0)
                    ? $order->total
                    : $order->paid_amount;

                $timeline->push([
                    'type'     => 'order',
                    'date'     => $order->created_at,
                    'order'    => $order,
                    'total'    => (float) $order->total,
                    'paid'     => (float) $paidOnOrder,
                ]);
            }

            foreach ($khataPayments as $payment) {
                $timeline->push([
                    'type'   => $payment->payment_type === 'khata_payout' ? 'payout' : 'payment',
                    'date'   => $payment->payment_date,
                    'amount' => (float) $payment->amount,
                ]);
            }

            $timeline = $timeline->sortBy('date')->values();

            // ── Walk through timeline and fix previous_balance on each order ──
            $runningBalance = 0;

            foreach ($timeline as $txn) {
                if ($txn['type'] === 'order') {
                    $order         = $txn['order'];
                    $prevBalance   = $runningBalance;
                    $balanceOnBill = max(0, $txn['total'] - $txn['paid']);
                    $runningBalance = $prevBalance + $txn['total'] - $txn['paid'];

                    // Check if order fields need fixing
                    $needsFix = abs($order->previous_balance - $prevBalance) > 0.01
                             || abs($order->balance_amount - $balanceOnBill) > 0.01;

                    if ($needsFix) {
                        $this->warn(sprintf(
                            "    Order %-12s | prev_bal: %10s → %10s | bal_amt: %10s → %10s",
                            $order->order_number,
                            number_format($order->previous_balance, 2),
                            number_format($prevBalance, 2),
                            number_format($order->balance_amount, 2),
                            number_format($balanceOnBill, 2)
                        ));

                        if (!$dryRun) {
                            $order->update([
                                'previous_balance' => $prevBalance,
                                'balance_amount'   => $balanceOnBill,
                            ]);
                        }
                        $fixedOrders++;
                    }
                } elseif ($txn['type'] === 'payout') {
                    // Cash paid out to customer increases their balance
                    $runningBalance += $txn['amount'];
                } else {
                    // Khata payment reduces balance
                    $runningBalance -= $txn['amount'];
                }
            }

            // ── Fix customer current_balance ──
            $oldBalance = (float) ($customer->current_balance ?? 0);

            if (abs($runningBalance - $oldBalance) > 0.01) {
                $this->warn(sprintf(
                    "  %-30s | Old: %10s | New: %10s | Diff: %10s",
                    $customer->name,
                    number_format($oldBalance, 2),
                    number_format($runningBalance, 2),
                    number_format($runningBalance - $oldBalance, 2)
                ));

                if (!$dryRun) {
                    $customer->update(['current_balance' => $runningBalance]);
                }
                $fixedCustomers++;
            } else {
                $this->line(sprintf(
                    "  %-30s | Balance: %10s ✓",
                    $customer->name,
                    number_format($oldBalance, 2)
                ));
            }
        }

        $this->newLine();
        $this->info(($dryRun ? '[DRY RUN] Would fix' : 'Fixed') . " {$fixedCustomers} customer(s), {$fixedOrders} order(s).");
    }
}
