<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    protected $fillable = [
        'transaction_number',
        'credit_ledger_id',
        'customer_id',
        'order_id',
        'payment_id',
        'transaction_type',
        'amount',
        'balance_before',
        'balance_after',
        'reference_number',
        'description',
        'transaction_date',
        'due_date',
        'payment_date',
        'payment_status',
        'items',
        'paid_amount',
        'remaining_amount',
        'notes',
        'created_by'
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'items' => 'array',
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2'
    ];

    /**
     * Generate unique transaction number
     */
    public static function generateTransactionNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastTransaction = self::whereYear('created_at', $year)
                               ->whereMonth('created_at', $month)
                               ->orderBy('id', 'desc')
                               ->first();
        
        if ($lastTransaction) {
            $lastNumber = intval(substr($lastTransaction->transaction_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "TXN-{$year}{$month}-{$newNumber}";
    }

    /**
     * Relationship with Credit Ledger
     */
    public function ledger(): BelongsTo
    {
        return $this->belongsTo(CreditLedger::class, 'credit_ledger_id');
    }

    /**
     * Relationship with Customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship with Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship with Payment
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    /**
     * Scope for overdue transactions
     */
    public function scopeOverdue($query)
    {
        return $query->where('transaction_type', 'debit')
                    ->where('payment_status', 'pending')
                    ->where('due_date', '<', now())
                    ->where('remaining_amount', '>', 0);
    }

    /**
     * Scope for today's due transactions
     */
    public function scopeDueToday($query)
    {
        return $query->where('transaction_type', 'debit')
                    ->where('payment_status', 'pending')
                    ->whereDate('due_date', now())
                    ->where('remaining_amount', '>', 0);
    }

    /**
     * Get transaction type badge class
     */
    public function getTypeBadgeAttribute(): string
    {
        return $this->transaction_type === 'debit' 
            ? 'bg-red-100 text-red-800' 
            : 'bg-green-100 text-green-800';
    }

    /**
     * Get payment status badge class
     */
    public function getStatusBadgeAttribute(): string
    {
        switch ($this->payment_status) {
            case 'paid':
                return 'bg-green-100 text-green-800';
            case 'partial':
                return 'bg-yellow-100 text-yellow-800';
            case 'overdue':
                return 'bg-red-100 text-red-800';
            default:
                return 'bg-gray-100 text-gray-800';
        }
    }
}