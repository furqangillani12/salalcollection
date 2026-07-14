<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Payroll;
use Carbon\Carbon;

class PayrollService
{
    public function generateMonthlyPayroll($month, $year)
    {
        $employees = Employee::all();

        foreach ($employees as $employee) {
            $totalDays = Carbon::create($year, $month)->daysInMonth;

            $presentDays = Attendance::where('employee_id', $employee->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('status', 'present')
                ->count();

            $absentDays = Attendance::where('employee_id', $employee->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('status', 'absent')
                ->count();

            $lateDays = Attendance::where('employee_id', $employee->id)
                ->whereMonth('date', $month)
                ->whereYear('date', $year)
                ->where('status', 'late')
                ->count();

            $perDaySalary = $employee->salary / $totalDays;
            $grossSalary = $perDaySalary * $presentDays;

            // Example: Deduct 0.5 day per late
            $deductions = ($lateDays * 0.5 * $perDaySalary);

            $netSalary = $grossSalary - $deductions;

            Payroll::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'month' => $month,
                    'year' => $year,
                ],
                [
                    'present_days' => $presentDays,
                    'absent_days' => $absentDays,
                    'late_days' => $lateDays,
                    'gross_salary' => $grossSalary,
                    'deductions' => $deductions,
                    'net_salary' => $netSalary,
                ]
            );
        }
    }
}
