<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountTransfer extends Model
{
    protected $fillable = [
        'branch_id', 'user_id', 'from_account', 'to_account',
        'amount', 'note', 'transferred_at',
    ];

    protected $casts = [
        'amount'         => 'float',
        'transferred_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
