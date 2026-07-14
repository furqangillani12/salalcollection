<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'user_id', 'branch_id', 'phone', 'address', 'salary', 'joining_date'
    ];

    // Relationship with User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
    // app/Models/Employee.php
    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }
    // app/Models/Employee.php
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    protected static function booted()
    {
        static::deleting(function ($employee) {
            $employee->shifts()->delete();
            $employee->attendances()->delete();

            // Also delete user if exists
            if ($employee->user) {
                $employee->user->delete();
            }
        });
    }
}
