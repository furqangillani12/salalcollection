<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'customer_id', 'session_id', 'product_id', 'branch_id',
        'qty', 'unit_price', 'selected_size', 'selected_color',
    ];

    protected $casts = [
        'qty'        => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function getLineTotalAttribute(): float
    {
        return round((float) $this->qty * (float) $this->unit_price, 2);
    }
}
