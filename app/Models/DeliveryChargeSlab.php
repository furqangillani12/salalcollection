<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryChargeSlab extends Model
{
    protected $fillable = ['dispatch_method_id', 'min_weight', 'max_weight', 'charge', 'is_active'];

    protected $casts = [
        'min_weight' => 'decimal:3',
        'max_weight' => 'decimal:3',
        'charge'     => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    public function dispatchMethod()
    {
        return $this->belongsTo(DispatchMethod::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Find the delivery charge for a given dispatch method and weight (kg).
     */
    public static function getChargeForWeight(int $dispatchMethodId, float $weight): float
    {
        $slab = static::active()
            ->where('dispatch_method_id', $dispatchMethodId)
            ->where('min_weight', '<=', $weight)
            ->where('max_weight', '>=', $weight)
            ->first();

        return $slab ? (float) $slab->charge : 0;
    }
}
