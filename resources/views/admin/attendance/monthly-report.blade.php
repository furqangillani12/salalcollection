@extends('layouts.admin')

@section('title', 'Monthly Attendance Report')

@section('content')
    <div class="space-y-5">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <a href="{{ route('admin.attendance.index') }}" class="text-sm text-blue-600 hover:underline">← Back</a>
                <h2 class="text-xl font-bold text-gray-800 mt-1">Monthly Report</h2>
                <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($month)->format('F Y') }} — {{ $workingDays }} working days</p>
            </div>
            <div class="flex items-center gap-2">
                <form method="GET" class="flex items-center gap-2">
                    <input type="month" name="month"
                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                           value="{{ $month }}" max="{{ now()->format('Y-m') }}">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">Generate</button>
                </form>
                <a href="{{ route('admin.attendance.yearly-report') }}"
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 text-sm font-medium">
                    Yearly
                </a>
            </div>
        </div>

        {{-- Employee Cards --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($employees as $employee)
                @php
                    $present  = $employee->attendances->where('status', 'present')->count();
                    $late     = $employee->attendances->where('status', 'late')->count();
                    $onLeave  = $employee->attendances->where('status', 'on_leave')->count();
                    $halfDay  = $employee->attendances->where('status', 'half_day')->count();
                    $absent   = max(0, $workingDays - ($present + $late + $onLeave + $halfDay));
                    $pct      = $workingDays ? round((($present + $late + $halfDay) / $workingDays) * 100) : 0;
                    $barColor = $pct >= 90 ? 'bg-green-500' : ($pct >= 70 ? 'bg-yellow-500' : 'bg-red-500');
                    $pctColor = $pct >= 90 ? 'text-green-600' : ($pct >= 70 ? 'text-yellow-600' : 'text-red-600');
                @endphp
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">
                                {{ strtoupper(substr($employee->user->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $employee->user->name }}</p>
                                <p class="text-xs text-gray-400">Salary: Rs. {{ number_format($employee->salary ?? 0, 0) }}</p>
                            </div>
                        </div>
                        <span class="text-lg font-bold {{ $pctColor }}">{{ $pct }}%</span>
                    </div>

                    {{-- Attendance bar --}}
                    <div class="w-full bg-gray-100 rounded-full h-2.5 mb-4">
                        <div class="h-2.5 rounded-full {{ $barColor }} transition-all" style="width: {{ $pct }}%"></div>
                    </div>

                    {{-- Stats Grid --}}
                    <div class="grid grid-cols-5 gap-2 mb-4">
                        <div class="text-center">
                            <p class="text-lg font-bold text-green-600">{{ $present }}</p>
                            <p class="text-[10px] text-gray-500">Present</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-yellow-600">{{ $late }}</p>
                            <p class="text-[10px] text-gray-500">Late</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-purple-600">{{ $halfDay }}</p>
                            <p class="text-[10px] text-gray-500">Half Day</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-blue-600">{{ $onLeave }}</p>
                            <p class="text-[10px] text-gray-500">Leave</p>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-bold text-red-600">{{ $absent }}</p>
                            <p class="text-[10px] text-gray-500">Absent</p>
                        </div>
                    </div>

                    {{-- Hours & Salary --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100 text-sm">
                        <div>
                            <span class="text-gray-500">Hours Worked:</span>
                            <span class="font-semibold text-gray-800 ml-1">{{ $employee->total_hours ?? 0 }} hrs</span>
                        </div>
                        <div>
                            <span class="text-gray-500">Calculated:</span>
                            <span class="font-bold text-green-600 ml-1">Rs. {{ number_format($employee->calculated_salary ?? 0, 0) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Summary Table --}}
        @if($employees->count())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-5 py-4 border-b bg-gray-50">
                <h3 class="font-semibold text-gray-700">Summary Table</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-600 uppercase border-b">
                        <tr>
                            <th class="px-4 py-3 text-left">Employee</th>
                            <th class="px-4 py-3 text-center">P</th>
                            <th class="px-4 py-3 text-center">L</th>
                            <th class="px-4 py-3 text-center">LV</th>
                            <th class="px-4 py-3 text-center">A</th>
                            <th class="px-4 py-3 text-center">%</th>
                            <th class="px-4 py-3 text-center">Hours</th>
                            <th class="px-4 py-3 text-right">Salary (PKR)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @php $totalSalary = 0; $totalHrs = 0; @endphp
                        @foreach($employees as $employee)
                            @php
                                $present  = $employee->attendances->where('status', 'present')->count();
                                $late     = $employee->attendances->where('status', 'late')->count();
                                $onLeave  = $employee->attendances->where('status', 'on_leave')->count();
                                $absent   = max(0, $workingDays - ($present + $late + $onLeave));
                                $pct      = $workingDays ? round((($present + $late) / $workingDays) * 100) : 0;
                                $totalSalary += $employee->calculated_salary ?? 0;
                                $totalHrs += $employee->total_hours ?? 0;
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $employee->user->name }}</td>
                                <td class="px-4 py-3 text-center text-green-600 font-semibold">{{ $present }}</td>
                                <td class="px-4 py-3 text-center text-yellow-600">{{ $late }}</td>
                                <td class="px-4 py-3 text-center text-blue-600">{{ $onLeave }}</td>
                                <td class="px-4 py-3 text-center text-red-600">{{ $absent }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="{{ $pct >= 90 ? 'text-green-600' : ($pct >= 70 ? 'text-yellow-600' : 'text-red-600') }} font-semibold">
                                        {{ $pct }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center font-medium">{{ $employee->total_hours ?? 0 }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ number_format($employee->calculated_salary ?? 0, 0) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-gray-50 border-t-2 font-bold text-sm">
                        <tr>
                            <td class="px-4 py-3 text-gray-700" colspan="6">Total</td>
                            <td class="px-4 py-3 text-center">{{ $totalHrs }}</td>
                            <td class="px-4 py-3 text-right text-green-700">Rs. {{ number_format($totalSalary, 0) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        @endif

    </div>
@endsection
