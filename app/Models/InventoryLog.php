<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class InventoryLog extends Model
{
    protected $fillable = [
        'product_id', 'branch_id', 'action', 'quantity_change', 'notes', 'user_id'
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // Relationship with Product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Filter scope for inventory logs
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter(Builder $query, array $filters)
    {
        return $query->when(isset($filters['product_id']) ? $filters['product_id'] : null, function (Builder $query, $productId) {
            $query->where('product_id', $productId);
        })
            ->when(isset($filters['action']) ? $filters['action'] : null, function (Builder $query, $action) {
                $query->where('action', $action);
            });
    }
}
