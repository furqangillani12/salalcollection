<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id', 'branch_id', 'month', 'year',
        'present_days', 'absent_days', 'late_days',
        'total_hours', 'hourly_rate',
        'gross_salary', 'deductions', 'net_salary', 'status'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
