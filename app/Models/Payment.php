<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'payment_number',
        'order_id',
        'customer_id',
        'payment_type',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'method',        // Keep both for compatibility
        'reference',      // Keep both for compatibility
        'notes',
        'status',
        'created_by'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2'
    ];

    /**
     * Relationship with Order
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Relationship with Customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relationship with User who created the payment
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Generate unique payment number
     */
    public static function generatePaymentNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $lastPayment = self::whereYear('created_at', $year)
                          ->whereMonth('created_at', $month)
                          ->orderBy('id', 'desc')
                          ->first();
        
        if ($lastPayment && $lastPayment->payment_number) {
            $lastNumber = intval(substr($lastPayment->payment_number, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        
        return "PAY-{$year}{$month}-{$newNumber}";
    }
}