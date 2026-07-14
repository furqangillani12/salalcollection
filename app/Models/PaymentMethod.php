<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name', 'label', 'sort_order', 'is_active',
        'show_on_website', 'is_cod', 'account_title', 'account_number', 'bank_name', 'instructions',
    ];

    protected $casts = [
        'is_active'       => 'boolean',
        'show_on_website' => 'boolean',
        'is_cod'          => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /** Payment methods offered on the public storefront checkout. */
    public function scopeOnWebsite($query)
    {
        return $query->where('is_active', true)->where('show_on_website', true)->orderBy('sort_order');
    }
}
