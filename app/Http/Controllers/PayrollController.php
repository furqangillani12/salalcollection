<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Payroll;
use App\Traits\BranchScoped;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PayrollController extends Controller
{
    use BranchScoped;

    public function index(Request $request)
    {
        $month = (int) $request->input('month', now()->month);
        $year  = (int) $request->input('year', now()->year);

        $payrolls = $this->scopeBranch(Payroll::with('employee.user'))
            ->where('month', $month)
            ->where('year', $year)
            ->orderBy('net_salary', 'desc')
            ->get();

        $months = collect(range(1, 12))->map(fn($m) => [
            'value' => $m,
            'label' => Carbon::create(null, $m)->format('F'),
        ]);

        $years       = collect(range(now()->year - 2, now()->year + 1));
        $workingDays = $this->getWorkingDays($month, $year);

        return view('admin.payroll.index', compact('payrolls', 'month', 'year', 'months', 'years', 'workingDays'));
    }

    private function getWorkingDays($month, $year)
    {
        $start  = Carbon::create($year, $month, 1)->startOfMonth();
        $end    = Carbon::create($year, $month, 1)->endOfMonth();
        $days   = 0;
        $cursor = $start->copy();

        while ($cursor <= $end) {
            if (!$cursor->isWeekend()) {
                $days++;
            }
            $cursor->addDay();
        }

        return $days;
    }

    private function calculatePayroll(Employee $employee, $month, $year)
    {
        $attendances = $employee->attendances()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->with('sessions')
            ->get();

        $totalWorkedMinutes = $attendances->sum(function ($attendance) {
            return $attendance->sessions->sum(function ($session) {
                if ($session->check_in && $session->check_out) {
                    return Carbon::parse($session->check_in)->diffInMinutes(Carbon::parse($session->check_out));
                }
                return 0;
            });
        });

        $totalWorkedHours     = round($totalWorkedMinutes / 60, 2);
        $workingDays          = $this->getWorkingDays($month, $year);
        $expectedMonthlyHours = max($workingDays * 8, 1);
        $hourlyRate           = $employee->salary / $expectedMonthlyHours;
        $grossSalary          = $employee->salary;
        $calculatedSalary     = $hourlyRate * $totalWorkedHours;

        $presentDays = $attendances->where('status', 'present')->count();
        $lateDays    = $attendances->where('status', 'late')->count();
        $halfDays    = $attendances->where('status', 'half_day')->count();
        $leaveDays   = $attendances->where('status', 'on_leave')->count();
        $absentDays  = $attendances->where('status', 'absent')->count();

        $deductions = max(0, $grossSalary - $calculatedSalary);
        $netSalary  = min($calculatedSalary, $grossSalary);

        return [
            'employee_id'  => $employee->id,
            'month'        => $month,
            'year'         => $year,
            'present_days' => $presentDays,
            'absent_days'  => $absentDays,
            'late_days'    => $lateDays,
            'total_hours'  => $totalWorkedHours,
            'hourly_rate'  => round($hourlyRate, 2),
            'gross_salary' => round($grossSalary, 2),
            'deductions'   => round($deductions, 2),
            'net_salary'   => round($netSalary, 2),
            'status'       => 'unpaid',
        ];
    }

    public function generate(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year'  => 'required|integer|min:2020|max:2030',
        ]);

        $month    = $request->input('month');
        $year     = $request->input('year');
        $branchId = $this->branchId();

        $employees = $this->scopeBranch(Employee::with(['user', 'attendances.sessions']))->get();
        $count     = 0;

        foreach ($employees as $employee) {
            $data = $this->calculatePayroll($employee, $month, $year);

            Payroll::updateOrCreate(
                [
                    'employee_id' => $employee->id,
                    'month'       => $month,
                    'year'        => $year,
                ],
                [
                    'branch_id'    => $branchId !== 'all' ? $branchId : null,
                    'present_days' => $data['present_days'],
                    'absent_days'  => $data['absent_days'],
                    'late_days'    => $data['late_days'],
                    'gross_salary' => $data['gross_salary'],
                    'deductions'   => $data['deductions'],
                    'net_salary'   => $data['net_salary'],
                    'total_hours'  => $data['total_hours'],
                    'hourly_rate'  => $data['hourly_rate'],
                    'status'       => 'unpaid',
                ]
            );
            $count++;
        }

        $monthName = Carbon::create(null, $month)->format('F');

        return redirect()
            ->route('admin.payroll.index', ['month' => $month, 'year' => $year])
            ->with('success', "Payroll generated for {$count} employees — {$monthName} {$year}");
    }

    public function payslip(Payroll $payroll)
    {
        $payroll->load('employee.user');
        $workingDays = $this->getWorkingDays($payroll->month, $payroll->year);

        return view('admin.payroll.payslip', compact('payroll', 'workingDays'));
    }

    public function markPaid(Payroll $payroll)
    {
        $payroll->update(['status' => 'paid']);

        return redirect()
            ->route('admin.payroll.index', ['month' => $payroll->month, 'year' => $payroll->year])
            ->with('success', $payroll->employee->user->name . ' marked as paid');
    }

    public function markAllPaid(Request $request)
    {
        $request->validate([
            'month' => 'required|integer',
            'year'  => 'required|integer',
        ]);

        $count = $this->scopeBranch(Payroll::where('month', $request->month)
            ->where('year', $request->year)
            ->where('status', 'unpaid'))
            ->update(['status' => 'paid']);

        return redirect()
            ->route('admin.payroll.index', ['month' => $request->month, 'year' => $request->year])
            ->with('success', "{$count} payroll(s) marked as paid");
    }
}
