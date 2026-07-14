<?php

// app/Models/Shift.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shift extends Model
{
    protected $fillable = [
        'employee_id', 'date', 'start_time', 'end_time', 'notes'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
