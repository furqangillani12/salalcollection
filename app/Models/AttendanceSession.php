<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceSession extends Model
{
    protected $fillable = [
        'attendance_id',
        'check_in',
        'check_out',
    ];

    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}
