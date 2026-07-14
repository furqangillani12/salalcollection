<?php

// app/Models/LoginHistory.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginHistory extends Model
{
    protected $fillable = [
        'user_id', 'ip_address', 'user_agent', 'login_at', 'logout_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
