<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = [
        'branch_id', 'name', 'code', 'sale_price', 'resale_price', 'wholesale_price', 'is_active',
    ];

    protected $casts = [
        'sale_price'      => 'float',
        'resale_price'    => 'float',
        'wholesale_price' => 'float',
        'is_active'       => 'boolean',
    ];

    public function items()
    {
        return $this->hasMany(PackageItem::class)->with('product');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function getCostPriceAttribute(): float
    {
        return $this->items->sum(fn($i) => ($i->product->cost_price ?? 0) * $i->quantity);
    }

    public function getRetailTotalAttribute(): float
    {
        return $this->items->sum(fn($i) => ($i->product->sale_price ?? 0) * $i->quantity);
    }

    public function getDiscountAmountAttribute(): float
    {
        return max(0, $this->retail_total - $this->sale_price);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
