<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'order_id', 'user_id', 'refund_number', 'amount', 'reason', 'items', 'status',
    ];

    protected $casts = [
        'items' => 'array',
    ];

    public static function generateRefundNumber(): string
    {
        $last = static::latest()->first();
        $next = $last ? (intval(substr($last->refund_number ?? 'RFN-0000', 4)) + 1) : 1;
        return 'RFN-' . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
