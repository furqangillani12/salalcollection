<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceSession;
use App\Models\Employee;
use App\Traits\BranchScoped;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    use BranchScoped;

    public function index(Request $request)
    {
        $date = $request->date ?? Carbon::today()->format('Y-m-d');

        $attendances = $this->scopeBranch(Attendance::with(['employee.user', 'sessions']))
            ->whereDate('date', $date)
            ->orderBy('date', 'desc')
            ->paginate(20);

        $allAttendances = $this->scopeBranch(Attendance::whereDate('date', $date))->get();
        $totalEmployees = $this->scopeBranch(Employee::query())->count();
        $summary = [
            'present'  => $allAttendances->where('status', 'present')->count(),
            'late'     => $allAttendances->where('status', 'late')->count(),
            'on_leave' => $allAttendances->where('status', 'on_leave')->count(),
            'half_day' => $allAttendances->where('status', 'half_day')->count(),
            'absent'   => $allAttendances->where('status', 'absent')->count(),
            'unmarked' => $totalEmployees - $allAttendances->count(),
        ];

        return view('admin.attendance.index', compact('attendances', 'date', 'summary', 'totalEmployees'));
    }

    public function create()
    {
        $employees = $this->scopeBranch(Employee::with('user'))->get();
        return view('admin.attendance.create', compact('employees'));
    }

    public function bulkCreate(Request $request)
    {
        $date = $request->date ?? today()->format('Y-m-d');

        $employees = $this->scopeBranch(Employee::with('user'))
            ->whereDoesntHave('attendances', function ($query) use ($date) {
                $query->whereDate('date', $date);
            })
            ->get();

        $markedEmployees = $this->scopeBranch(Employee::with(['user', 'attendances' => function ($q) use ($date) {
            $q->whereDate('date', $date)->with('sessions');
        }]))
            ->whereHas('attendances', function ($query) use ($date) {
                $query->whereDate('date', $date);
            })
            ->get();

        return view('admin.attendance.bulk-create', compact('employees', 'markedEmployees', 'date'));
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'date'                          => 'required|date',
            'attendances'                   => 'required|array',
            'attendances.*.employee_id'     => 'required|exists:employees,id',
            'attendances.*.status'          => 'required|in:present,absent,late,on_leave,half_day,break',
            'attendances.*.notes'           => 'nullable|string',
        ]);

        $date     = $request->date;
        $branchId = $this->branchId();

        foreach ($request->attendances as $att) {
            $attendance = Attendance::firstOrCreate(
                [
                    'employee_id' => $att['employee_id'],
                    'date'        => $date,
                ],
                [
                    'status'    => $att['status'],
                    'branch_id' => $branchId !== 'all' ? $branchId : null,
                    'notes'     => $att['notes'] ?? null,
                ]
            );

            $attendance->update([
                'status' => $att['status'],
                'notes'  => $att['notes'] ?? $attendance->notes,
            ]);

            if (!empty($att['sessions']) && is_array($att['sessions'])) {
                foreach ($att['sessions'] as $s) {
                    if (empty($s['check_in'])) continue;

                    $ci = Carbon::parse($s['check_in']);
                    $co = !empty($s['check_out']) ? Carbon::parse($s['check_out']) : null;

                    if ($co && $co->lessThanOrEqualTo($ci)) continue;

                    $attendance->sessions()->create([
                        'check_in'  => $ci->format('H:i'),
                        'check_out' => $co ? $co->format('H:i') : null,
                    ]);
                }
            }
        }

        return redirect()
            ->route('admin.attendance.index', ['date' => $date])
            ->with('success', 'Bulk attendance recorded successfully');
    }

    public function store(Request $request)
    {
        $basic = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'date'        => 'required|date',
            'status'      => 'required|in:present,absent,late,on_leave,half_day',
            'notes'       => 'nullable|string',
        ]);

        $branchId = $this->branchId();

        $attendance = Attendance::firstOrCreate(
            [
                'employee_id' => $basic['employee_id'],
                'date'        => $basic['date'],
            ],
            [
                'status'    => $basic['status'],
                'branch_id' => $branchId !== 'all' ? $branchId : null,
                'notes'     => $basic['notes'] ?? null,
            ]
        );

        $attendance->update([
            'status' => $basic['status'],
            'notes'  => $basic['notes'] ?? $attendance->notes,
        ]);

        $sessions = $request->input('sessions', []);

        if (!empty($sessions) && is_array($sessions)) {
            foreach ($sessions as $s) {
                if (empty($s['check_in'])) continue;

                $checkIn  = Carbon::parse($s['check_in'])->format('H:i');
                $checkOut = null;

                if (!empty($s['check_out'])) {
                    $ci = Carbon::parse($s['check_in']);
                    $co = Carbon::parse($s['check_out']);
                    if ($co->lessThanOrEqualTo($ci)) continue;
                    $checkOut = $co->format('H:i');
                }

                $attendance->sessions()->create([
                    'check_in'  => $checkIn,
                    'check_out' => $checkOut,
                ]);
            }
        }

        return redirect()
            ->route('admin.attendance.index', ['date' => $basic['date']])
            ->with('success', 'Attendance recorded successfully');
    }

    public function checkOut(Attendance $attendance)
    {
        $openSession = $attendance->sessions()->whereNull('check_out')->orderBy('id', 'desc')->first();

        if (!$openSession) {
            return back()->with('error', 'No open session found to check out.');
        }

        $openSession->update([
            'check_out' => now()->format('H:i'),
        ]);

        return back()->with('success', 'Check-out recorded at ' . now()->format('h:i A'));
    }

    public function quickCheckIn(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
        ]);

        $date     = today()->format('Y-m-d');
        $now      = now()->format('H:i');
        $branchId = $this->branchId();

        $attendance = Attendance::firstOrCreate(
            [
                'employee_id' => $request->employee_id,
                'date'        => $date,
            ],
            [
                'status'    => $now > '09:15' ? 'late' : 'present',
                'branch_id' => $branchId !== 'all' ? $branchId : null,
            ]
        );

        $openSession = $attendance->sessions()->whereNull('check_out')->first();
        if ($openSession) {
            return back()->with('error', 'Already checked in with an open session.');
        }

        $attendance->sessions()->create([
            'check_in' => $now,
        ]);

        $employeeName = Employee::with('user')->find($request->employee_id)->user->name ?? '';

        return back()->with('success', "{$employeeName} checked in at " . now()->format('h:i A'));
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->sessions()->delete();
        $attendance->delete();

        return back()->with('success', 'Attendance record deleted.');
    }

    public function dailyReport(Request $request)
    {
        $date = $request->date ?? Carbon::today()->format('Y-m-d');

        $attendances = $this->scopeBranch(Attendance::with('employee.user', 'sessions'))
            ->whereDate('date', $date)
            ->get()
            ->groupBy('status');

        $allEmployees = $this->scopeBranch(Employee::query())->count();

        return view('admin.attendance.report', compact('attendances', 'date', 'allEmployees'));
    }

    public function monthlyReport(Request $request)
    {
        $month = $request->month ?? now()->format('Y-m');
        $start = Carbon::parse($month)->startOfMonth();
        $end   = Carbon::parse($month)->endOfMonth();

        $employees = $this->scopeBranch(Employee::with(['user', 'attendances' => function ($query) use ($start, $end) {
            $query->whereBetween('date', [$start, $end])->with('sessions');
        }]))->get();

        $workingDays = $this->getWorkingDays($start, $end);

        foreach ($employees as $employee) {
            $totalMinutes = 0;
            foreach ($employee->attendances as $attendance) {
                $totalMinutes += $attendance->total_worked_minutes;
            }
            $totalHours       = round($totalMinutes / 60, 2);
            $hoursPerMonth    = max($workingDays * 8, 1);
            $hourlyRate       = $employee->salary ? ($employee->salary / $hoursPerMonth) : 0;
            $calculatedSalary = round($totalHours * $hourlyRate, 2);

            $employee->total_minutes     = $totalMinutes;
            $employee->total_hours       = $totalHours;
            $employee->hourly_rate       = round($hourlyRate, 2);
            $employee->calculated_salary = $calculatedSalary;
        }

        return view('admin.attendance.monthly-report', compact('employees', 'month', 'workingDays'));
    }

    public function yearlyReport(Request $request)
    {
        $year = $request->year ?? now()->format('Y');

        $report = [];
        for ($month = 1; $month <= 12; $month++) {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end   = Carbon::create($year, $month, 1)->endOfMonth();

            $report[$month] = [
                'name'         => $start->format('F'),
                'present'      => $this->scopeBranch(Attendance::whereBetween('date', [$start, $end])->where('status', 'present'))->count(),
                'absent'       => $this->scopeBranch(Attendance::whereBetween('date', [$start, $end])->where('status', 'absent'))->count(),
                'late'         => $this->scopeBranch(Attendance::whereBetween('date', [$start, $end])->where('status', 'late'))->count(),
                'on_leave'     => $this->scopeBranch(Attendance::whereBetween('date', [$start, $end])->where('status', 'on_leave'))->count(),
                'working_days' => $this->getWorkingDays($start, $end),
            ];
        }

        return view('admin.attendance.yearly-report', compact('report', 'year'));
    }

    private function getWorkingDays($start, $end)
    {
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
}
