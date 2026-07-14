<?php

namespace App\Services;

use App\Models\LedgerEntry;
use App\Models\Order;
use App\Models\Purchase;
use App\Models\Expense;
use App\Models\Refund;
use App\Models\Payroll;
use Illuminate\Support\Facades\Auth;

class LedgerService
{
    /**
     * Record a completed sale (cash / card / mobile_money)
     */
    public static function recordSale(Order $order): void
    {
        LedgerEntry::create([
            'entry_number'     => LedgerEntry::generateEntryNumber(),
            'entry_date'       => $order->created_at->toDateString(),
            'account_type'     => LedgerEntry::ACCOUNT_SALES,
            'transaction_type' => LedgerEntry::TYPE_SALE,
            'reference_type'   => Order::class,
            'reference_id'     => $order->id,
            'reference_number' => $order->order_number,
            'description'      => 'Sale - Order #' . $order->order_number,
            'debit'            => 0,
            'credit'           => $order->total,
            'payment_method'   => $order->payment_method,
            'party_type'       => 'customer',
            'party_id'         => $order->customer_id,
            'party_name'       => optional($order->customer)->name ?? 'Walk-in Customer',
            'user_id'          => $order->user_id,
        ]);
    }

    /**
     * Record a credit sale (order on credit)
     */
    public static function recordCreditSale(Order $order): void
    {
        LedgerEntry::create([
            'entry_number'     => LedgerEntry::generateEntryNumber(),
            'entry_date'       => $order->created_at->toDateString(),
            'account_type'     => LedgerEntry::ACCOUNT_ACCOUNTS_RECEIVABLE,
            'transaction_type' => LedgerEntry::TYPE_CREDIT_SALE,
            'reference_type'   => Order::class,
            'reference_id'     => $order->id,
            'reference_number' => $order->order_number,
            'description'      => 'Credit Sale - Order #' . $order->order_number,
            'debit'            => $order->total,   // amount owed to us (receivable)
            'credit'           => 0,
            'payment_method'   => 'credit',
            'party_type'       => 'customer',
            'party_id'         => $order->customer_id,
            'party_name'       => optional($order->customer)->name,
            'user_id'          => $order->user_id,
        ]);
    }

    /**
     * Record a credit payment received from customer
     */
    public static function recordCreditPayment(Order $order, float $amount, int $userId): void
    {
        LedgerEntry::create([
            'entry_number'     => LedgerEntry::generateEntryNumber(),
            'entry_date'       => now()->toDateString(),
            'account_type'     => LedgerEntry::ACCOUNT_CASH_IN,
            'transaction_type' => LedgerEntry::TYPE_CREDIT_PAYMENT,
            'reference_type'   => Order::class,
            'reference_id'     => $order->id,
            'reference_number' => $order->order_number,
            'description'      => 'Credit Payment Received - Order #' . $order->order_number,
            'debit'            => 0,
            'credit'           => $amount,
            'payment_method'   => 'cash',
            'party_type'       => 'customer',
            'party_id'         => $order->customer_id,
            'party_name'       => optional($order->customer)->name,
            'user_id'          => $userId,
        ]);
    }

    /**
     * Record a purchase (stock buy)
     */
    public static function recordPurchase(Purchase $purchase): void
    {
        LedgerEntry::create([
            'entry_number'     => LedgerEntry::generateEntryNumber(),
            'entry_date'       => $purchase->purchase_date ?? $purchase->created_at->toDateString(),
            'account_type'     => LedgerEntry::ACCOUNT_PURCHASES,
            'transaction_type' => LedgerEntry::TYPE_PURCHASE,
            'reference_type'   => Purchase::class,
            'reference_id'     => $purchase->id,
            'reference_number' => $purchase->invoice_number,
            'description'      => 'Purchase - Invoice #' . $purchase->invoice_number,
            'debit'            => $purchase->total_amount,
            'credit'           => 0,
            'payment_method'   => $purchase->payment_status === 'paid' ? 'cash' : 'credit',
            'party_type'       => 'supplier',
            'party_id'         => $purchase->supplier_id,
            'party_name'       => optional($purchase->supplier)->name,
            'user_id'          => Auth::id(),
        ]);
    }

    /**
     * Record an expense
     */
    public static function recordExpense(Expense $expense): void
    {
        LedgerEntry::create([
            'entry_number'     => LedgerEntry::generateEntryNumber(),
            'entry_date'       => $expense->date ?? $expense->created_at->toDateString(),
            'account_type'     => LedgerEntry::ACCOUNT_EXPENSES,
            'transaction_type' => LedgerEntry::TYPE_EXPENSE,
            'reference_type'   => Expense::class,
            'reference_id'     => $expense->id,
            'reference_number' => 'EXP-' . str_pad($expense->id, 5, '0', STR_PAD_LEFT),
            'description'      => 'Expense: ' . $expense->title,
            'debit'            => $expense->amount,
            'credit'           => 0,
            'payment_method'   => 'cash',
            'party_type'       => null,
            'party_id'         => null,
            'party_name'       => null,
            'user_id'          => $expense->user_id,
        ]);
    }

    /**
     * Record a refund
     */
    public static function recordRefund(Refund $refund): void
    {
        $order = $refund->order;

        LedgerEntry::create([
            'entry_number'     => LedgerEntry::generateEntryNumber(),
            'entry_date'       => $refund->created_at->toDateString(),
            'account_type'     => LedgerEntry::ACCOUNT_REFUNDS,
            'transaction_type' => LedgerEntry::TYPE_REFUND,
            'reference_type'   => Refund::class,
            'reference_id'     => $refund->id,
            'reference_number' => optional($order)->order_number,
            'description'      => 'Refund for Order #' . optional($order)->order_number,
            'debit'            => $refund->amount,
            'credit'           => 0,
            'payment_method'   => 'cash',
            'party_type'       => 'customer',
            'party_id'         => optional($order)->customer_id,
            'party_name'       => optional(optional($order)->customer)->name ?? 'Walk-in Customer',
            'user_id'          => Auth::id(),
        ]);
    }

    /**
     * Record a payroll payment
     */
    public static function recordPayroll(Payroll $payroll): void
    {
        LedgerEntry::create([
            'entry_number'     => LedgerEntry::generateEntryNumber(),
            'entry_date'       => $payroll->payment_date ?? now()->toDateString(),
            'account_type'     => LedgerEntry::ACCOUNT_PAYROLL,
            'transaction_type' => LedgerEntry::TYPE_PAYROLL,
            'reference_type'   => Payroll::class,
            'reference_id'     => $payroll->id,
            'reference_number' => 'PAY-' . str_pad($payroll->id, 5, '0', STR_PAD_LEFT),
            'description'      => 'Payroll - ' . optional($payroll->employee)->name . ' (' . $payroll->month . '/' . $payroll->year . ')',
            'debit'            => $payroll->net_salary,
            'credit'           => 0,
            'payment_method'   => 'cash',
            'party_type'       => 'employee',
            'party_id'         => $payroll->employee_id,
            'party_name'       => optional($payroll->employee)->name,
            'user_id'          => Auth::id(),
        ]);
    }
}