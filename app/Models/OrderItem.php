<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id',
        'quantity', 'unit_price', 'original_price', 'line_discount', 'total_price',
    ];

    protected $casts = [
        'unit_price'     => 'decimal:2',
        'original_price' => 'decimal:2',
        'line_discount'  => 'decimal:2',
        'total_price'    => 'decimal:2',
    ];

    public function hasLineDiscount(): bool
    {
        return ($this->line_discount ?? 0) > 0 && ($this->original_price ?? 0) > 0;
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Calculate total price for the item
    public function calculateTotal()
    {
        $this->total_price = $this->unit_price * $this->quantity;
    }
}
