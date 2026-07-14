<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Purchase extends Model
{
    protected $fillable = [
        'supplier_id', 'branch_id', 'invoice_number', 'total_amount', 'paid_amount', 'payment_status', 'payment_method', 'purchase_date', 'notes', 'expenses', 'discount',
    ];

    protected $casts = [
        'expenses' => 'array',
        'discount' => 'float',
    ];

    // Relationship with Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Relationship with Purchase Items
    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }
}
