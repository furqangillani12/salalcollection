<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Refund;
use App\Models\Payroll;
use App\Services\LedgerService;

/**
 * Run this seeder ONCE after migration to backfill all existing historical data.
 * Command: php artisan db:seed --class=LedgerSeeder
 */
class LedgerSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Backfilling ledger entries from existing data...');

        // ── Orders ──────────────────────────────────────────────────────────
        $orders = Order::where('status', Order::STATUS_COMPLETED)
            ->with('customer')
            ->get();

        $this->command->info("Processing {$orders->count()} completed orders...");
        foreach ($orders as $order) {
            try {
                if ($order->payment_method === 'credit' || $order->credit_status === 'pending') {
                    LedgerService::recordCreditSale($order);
                } else {
                    LedgerService::recordSale($order);
                }
            } catch (\Exception $e) {
                $this->command->warn("Order #{$order->id}: " . $e->getMessage());
            }
        }

        // ── Purchases ────────────────────────────────────────────────────────
        $purchases = Purchase::with('supplier')->get();
        $this->command->info("Processing {$purchases->count()} purchases...");
        foreach ($purchases as $purchase) {
            try {
                LedgerService::recordPurchase($purchase);
            } catch (\Exception $e) {
                $this->command->warn("Purchase #{$purchase->id}: " . $e->getMessage());
            }
        }

        // ── Expenses ─────────────────────────────────────────────────────────
        $expenses = Expense::all();
        $this->command->info("Processing {$expenses->count()} expenses...");
        foreach ($expenses as $expense) {
            try {
                LedgerService::recordExpense($expense);
            } catch (\Exception $e) {
                $this->command->warn("Expense #{$expense->id}: " . $e->getMessage());
            }
        }

        // ── Refunds ──────────────────────────────────────────────────────────
        $refunds = Refund::with('order.customer')->get();
        $this->command->info("Processing {$refunds->count()} refunds...");
        foreach ($refunds as $refund) {
            try {
                LedgerService::recordRefund($refund);
            } catch (\Exception $e) {
                $this->command->warn("Refund #{$refund->id}: " . $e->getMessage());
            }
        }

        // ── Paid Payrolls ────────────────────────────────────────────────────
        $payrolls = Payroll::where('status', 'paid')->with('employee')->get();
        $this->command->info("Processing {$payrolls->count()} paid payrolls...");
        foreach ($payrolls as $payroll) {
            try {
                LedgerService::recordPayroll($payroll);
            } catch (\Exception $e) {
                $this->command->warn("Payroll #{$payroll->id}: " . $e->getMessage());
            }
        }

        $this->command->info('✅ Ledger backfill complete!');
    }
}