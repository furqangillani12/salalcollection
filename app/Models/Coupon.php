<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'type', 'value', 'min_order_amount', 'max_discount',
        'usage_limit', 'used_count', 'starts_on', 'expires_on', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'starts_on'  => 'date',
        'expires_on' => 'date',
    ];

    public function isUsable(float $orderAmount = 0): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_on && $this->starts_on->isFuture()) return false;
        if ($this->expires_on && $this->expires_on->isPast()) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        if ($this->min_order_amount && $orderAmount < $this->min_order_amount) return false;
        return true;
    }

    public function discountFor(float $orderAmount): float
    {
        if (!$this->isUsable($orderAmount)) return 0;
        $d = $this->type === 'percent'
            ? round($orderAmount * ((float) $this->value / 100), 2)
            : (float) $this->value;
        if ($this->max_discount) $d = min($d, (float) $this->max_discount);
        return min($d, $orderAmount);
    }
}
