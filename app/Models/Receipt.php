<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{

    protected $fillable = ['order_id', 'receipt_number', 'content'];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
