<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupplierPayment extends Model
{
    protected $fillable = [
        'payment_number',
        'supplier_id',
        'purchase_id',
        'branch_id',
        'amount',
        'payment_date',
        'payment_method',
        'direction',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function generatePaymentNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $last = self::whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->orderBy('id', 'desc')
                    ->first();

        if ($last && $last->payment_number) {
            $num = intval(substr($last->payment_number, -4)) + 1;
        } else {
            $num = 1;
        }

        return "SP-{$year}{$month}-" . str_pad($num, 4, '0', STR_PAD_LEFT);
    }
}
