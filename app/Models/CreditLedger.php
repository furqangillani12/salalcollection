<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditLedger extends Model
{
    protected $fillable = [
        'ledger_number',
        'customer_id',
        'total_debit',
        'total_credit',
        'opening_balance',
        'closing_balance',
        'credit_limit',
        'status',
        'last_transaction_date',
        'notes'
    ];

    protected $casts = [
        'last_transaction_date' => 'date',
    ];

    /**
     * Generate unique ledger number
     */
    public static function generateLedgerNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastLedger = self::whereYear('created_at', $year)
                         ->whereMonth('created_at', $month)
                         ->orderBy('id', 'desc')
                         ->first();
        
        if ($lastLedger) {
            $lastNumber = intval(substr($lastLedger->ledger_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "LED-{$year}{$month}-{$newNumber}";
    }

    /**
     * Relationship with Customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship with Credit Transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    /**
     * Relationship with Orders (credit sales)
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Update ledger balance
     */
    public function updateBalance(): void
    {
        $this->closing_balance = $this->transactions()
            ->where('transaction_type', 'debit')
            ->sum('amount') - 
            $this->transactions()
            ->where('transaction_type', 'credit')
            ->sum('amount');
        
        $this->save();
        
        // Update customer current balance
        $this->customer->current_balance = $this->closing_balance;
        $this->customer->save();
    }

    /**
     * Get total overdue amount
     */
    public function getOverdueAmountAttribute(): float
    {
        return $this->transactions()
            ->where('transaction_type', 'debit')
            ->where('payment_status', 'pending')
            ->where('due_date', '<', now())
            ->sum('remaining_amount');
    }
}