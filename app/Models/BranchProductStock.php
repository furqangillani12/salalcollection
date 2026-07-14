<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchProductStock extends Model
{
    protected $table = 'branch_product_stock';

    protected $fillable = ['branch_id', 'product_id', 'stock_quantity', 'reorder_level'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
