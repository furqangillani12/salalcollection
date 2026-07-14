<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DispatchMethod extends Model
{
    protected $fillable = ['name', 'note', 'has_tracking', 'sort_order', 'is_active', 'show_on_website', 'logo'];

    protected $casts = [
        'is_active' => 'boolean',
        'has_tracking' => 'boolean',
        'show_on_website' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /** Dispatch methods offered on the public storefront checkout. */
    public function scopeOnWebsite($query)
    {
        return $query->where('is_active', true)->where('show_on_website', true)->orderBy('sort_order');
    }

    public function deliverySlabs()
    {
        return $this->hasMany(DeliveryChargeSlab::class)->orderBy('min_weight');
    }
}
