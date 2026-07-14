@extends('layouts.admin')

@section('title', 'Payroll Management')

@section('content')
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Payroll</h2>
                <p class="text-sm text-gray-500">
                    {{ \Carbon\Carbon::create(null, (int)$month)->format('F') }} {{ $year }}
                    — {{ $workingDays }} working days ({{ $workingDays * 8 }} hrs expected)
                </p>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Controls: Month/Year Selector + Generate --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex flex-col sm:flex-row gap-4">
                {{-- Month/Year Filter --}}
                <form method="GET" action="{{ route('admin.payroll.index') }}" class="flex flex-wrap items-end gap-3 flex-1">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Month</label>
                        <select name="month" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                            @foreach($months as $m)
                                <option value="{{ $m['value'] }}" {{ $m['value'] == $month ? 'selected' : '' }}>{{ $m['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Year</label>
                        <select name="year" class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200">
                            @foreach($years as $y)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-filter"></i> View
                    </button>
                </form>

                {{-- Generate Button --}}
                <form method="POST" action="{{ route('admin.payroll.generate') }}" class="flex items-end"
                      onsubmit="return confirm('Generate/Regenerate payroll for {{ \Carbon\Carbon::create(null, (int)$month)->format('F') }} {{ $year }}? This will recalculate all salaries.')">
                    @csrf
                    <input type="hidden" name="month" value="{{ $month }}">
                    <input type="hidden" name="year" value="{{ $year }}">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                        <i class="fas fa-calculator"></i> Generate Payroll
                    </button>
                </form>
            </div>
        </div>

        @if($payrolls->count())
            {{-- Summary Cards --}}
            @php
                $totalGross      = $payrolls->sum('gross_salary');
                $totalDeductions = $payrolls->sum('deductions');
                $totalNet        = $payrolls->sum('net_salary');
                $totalHours      = $payrolls->sum('total_hours');
                $paidCount       = $payrolls->where('status', 'paid')->count();
                $unpaidCount     = $payrolls->where('status', 'unpaid')->count();
            @endphp

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-xl font-bold text-gray-800">{{ $payrolls->count() }}</p>
                    <p class="text-xs text-gray-500">Employees</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-xl font-bold text-blue-600">{{ number_format($totalHours, 1) }}</p>
                    <p class="text-xs text-gray-500">Total Hours</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-xl font-bold text-gray-800">Rs. {{ number_format($totalGross, 0) }}</p>
                    <p class="text-xs text-gray-500">Gross Salary</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-xl font-bold text-red-600">Rs. {{ number_format($totalDeductions, 0) }}</p>
                    <p class="text-xs text-gray-500">Deductions</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-xl font-bold text-green-600">Rs. {{ number_format($totalNet, 0) }}</p>
                    <p class="text-xs text-gray-500">Net Payable</p>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 text-center">
                    <p class="text-xl font-bold {{ $unpaidCount > 0 ? 'text-yellow-600' : 'text-green-600' }}">
                        {{ $paidCount }}/{{ $payrolls->count() }}
                    </p>
                    <p class="text-xs text-gray-500">Paid</p>
                </div>
            </div>

            {{-- Employee Payroll Cards --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach($payrolls as $payroll)
                    @php
                        $pct = ($payroll->gross_salary > 0) ? round(($payroll->net_salary / $payroll->gross_salary) * 100) : 0;
                        $isPaid = $payroll->status === 'paid';
                    @endphp
                    <div class="bg-white rounded-xl shadow-sm border {{ $isPaid ? 'border-green-200' : 'border-gray-100' }} p-5 relative">
                        {{-- Paid badge --}}
                        @if($isPaid)
                            <div class="absolute top-3 right-3">
                                <span class="bg-green-100 text-green-700 text-xs font-bold px-2.5 py-1 rounded-full">PAID</span>
                            </div>
                        @else
                            <div class="absolute top-3 right-3">
                                <span class="bg-yellow-100 text-yellow-700 text-xs font-bold px-2.5 py-1 rounded-full">UNPAID</span>
                            </div>
                        @endif

                        {{-- Employee Info --}}
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-sm font-bold">
                                {{ strtoupper(substr($payroll->employee->user->name ?? '?', 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-semibold text-gray-800">{{ $payroll->employee->user->name }}</p>
                                <p class="text-xs text-gray-400">Base Salary: Rs. {{ number_format($payroll->gross_salary, 0) }}</p>
                            </div>
                        </div>

                        {{-- Attendance Stats --}}
                        <div class="grid grid-cols-4 gap-2 mb-4">
                            <div class="bg-green-50 rounded-lg p-2 text-center">
                                <p class="text-lg font-bold text-green-700">{{ $payroll->present_days }}</p>
                                <p class="text-[10px] text-green-600">Present</p>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-2 text-center">
                                <p class="text-lg font-bold text-yellow-700">{{ $payroll->late_days }}</p>
                                <p class="text-[10px] text-yellow-600">Late</p>
                            </div>
                            <div class="bg-red-50 rounded-lg p-2 text-center">
                                <p class="text-lg font-bold text-red-700">{{ $payroll->absent_days }}</p>
                                <p class="text-[10px] text-red-600">Absent</p>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-2 text-center">
                                <p class="text-lg font-bold text-blue-700">{{ $payroll->total_hours ?? 0 }}</p>
                                <p class="text-[10px] text-blue-600">Hours</p>
                            </div>
                        </div>

                        {{-- Salary Bar --}}
                        <div class="mb-4">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="text-gray-500">Salary Earned</span>
                                <span class="font-semibold {{ $pct >= 90 ? 'text-green-600' : ($pct >= 70 ? 'text-yellow-600' : 'text-red-600') }}">{{ $pct }}%</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2">
                                <div class="h-2 rounded-full {{ $pct >= 90 ? 'bg-green-500' : ($pct >= 70 ? 'bg-yellow-500' : 'bg-red-500') }}"
                                     style="width: {{ $pct }}%"></div>
                            </div>
                        </div>

                        {{-- Salary Breakdown --}}
                        <div class="space-y-1.5 text-sm border-t pt-3">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Hourly Rate</span>
                                <span class="text-gray-700">Rs. {{ number_format($payroll->hourly_rate ?? 0, 2) }}/hr</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Gross Salary</span>
                                <span class="text-gray-700">Rs. {{ number_format($payroll->gross_salary, 0) }}</span>
                            </div>
                            @if($payroll->deductions > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Deductions</span>
                                    <span class="text-red-600 font-medium">- Rs. {{ number_format($payroll->deductions, 0) }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between pt-2 border-t font-bold">
                                <span class="text-gray-800">Net Salary</span>
                                <span class="text-green-600 text-lg">Rs. {{ number_format($payroll->net_salary, 0) }}</span>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-2 mt-4 pt-3 border-t">
                            @if(!$isPaid)
                                <form method="POST" action="{{ route('admin.payroll.markPaid', $payroll) }}" class="inline"
                                      onsubmit="return confirm('Mark {{ $payroll->employee->user->name }} as paid?')">
                                    @csrf
                                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition">
                                        <i class="fas fa-check"></i> Mark Paid
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.payroll.payslip', $payroll) }}"
                               class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs px-3 py-1.5 rounded-lg font-medium transition">
                                <i class="fas fa-file-alt"></i> Payslip
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Mark All Paid --}}
            @if($unpaidCount > 0)
                <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-yellow-800">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>{{ $unpaidCount }}</strong> payroll(s) still unpaid — Total: <strong>Rs. {{ number_format($payrolls->where('status', 'unpaid')->sum('net_salary'), 0) }}</strong>
                    </p>
                    <form method="POST" action="{{ route('admin.payroll.markAllPaid') }}"
                          onsubmit="return confirm('Mark all {{ $unpaidCount }} payrolls as paid?')">
                        @csrf
                        <input type="hidden" name="month" value="{{ $month }}">
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg font-medium transition">
                            <i class="fas fa-check-double"></i> Mark All Paid
                        </button>
                    </form>
                </div>
            @endif

        @else
            {{-- Empty State --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="text-gray-400">
                    <i class="fas fa-money-check-alt text-4xl mb-3"></i>
                    <p class="font-medium text-gray-600 mb-1">No payroll records for this month</p>
                    <p class="text-sm text-gray-400">Click "Generate Payroll" to calculate salaries from attendance data.</p>
                </div>
            </div>
        @endif

    </div>
@endsection
