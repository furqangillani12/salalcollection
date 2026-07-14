<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EcommerceSyncLog extends Model
{
    protected $fillable = [
        'sync_type', 'external_id', 'local_id', 'status', 'response'
    ];
}
