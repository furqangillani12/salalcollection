@extends('layouts.admin')

@section('title', 'Payslip - ' . ($payroll->employee->user->name ?? 'Employee'))

@section('content')
    @php
        $monthName = \Carbon\Carbon::create(null, (int)$payroll->month)->format('F');
        $isPaid = $payroll->status === 'paid';
        $expectedHours = $workingDays * 8;
        $hoursPct = $expectedHours > 0 ? min(100, round(($payroll->total_hours / $expectedHours) * 100)) : 0;
        $salaryPct = ($payroll->gross_salary > 0) ? round(($payroll->net_salary / $payroll->gross_salary) * 100) : 0;
    @endphp

    <div class="max-w-3xl mx-auto space-y-5">

        {{-- Back Link --}}
        <a href="{{ route('admin.payroll.index', ['month' => $payroll->month, 'year' => $payroll->year]) }}"
           class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> Back to Payroll
        </a>

        {{-- Payslip Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" id="payslip-content">

            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-5 text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold">Payslip</h2>
                        <p class="text-blue-200 text-sm mt-0.5">{{ $monthName }} {{ $payroll->year }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $isPaid ? 'bg-green-400/20 text-green-100 border border-green-300/30' : 'bg-yellow-400/20 text-yellow-100 border border-yellow-300/30' }}">
                        {{ strtoupper($payroll->status) }}
                    </span>
                </div>
            </div>

            {{-- Employee Info --}}
            <div class="px-6 py-5 border-b border-gray-100">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xl font-bold flex-shrink-0">
                        {{ strtoupper(substr($payroll->employee->user->name ?? '?', 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <h3 class="text-lg font-bold text-gray-800">{{ $payroll->employee->user->name }}</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-1 mt-1 text-sm text-gray-500">
                            @if($payroll->employee->phone)
                                <p><i class="fas fa-phone text-xs mr-1"></i> {{ $payroll->employee->phone }}</p>
                            @endif
                            @if($payroll->employee->joining_date)
                                <p><i class="fas fa-calendar text-xs mr-1"></i> Joined: {{ \Carbon\Carbon::parse($payroll->employee->joining_date)->format('d M Y') }}</p>
                            @endif
                            @if($payroll->employee->address)
                                <p class="sm:col-span-2"><i class="fas fa-map-marker-alt text-xs mr-1"></i> {{ $payroll->employee->address }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Attendance Stats --}}
            <div class="px-6 py-5 border-b border-gray-100">
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Attendance Summary</h4>
                <div class="grid grid-cols-4 gap-3">
                    <div class="bg-green-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-green-700">{{ $payroll->present_days }}</p>
                        <p class="text-[10px] text-green-600 font-medium mt-0.5">Present</p>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-yellow-700">{{ $payroll->late_days }}</p>
                        <p class="text-[10px] text-yellow-600 font-medium mt-0.5">Late</p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-red-700">{{ $payroll->absent_days }}</p>
                        <p class="text-[10px] text-red-600 font-medium mt-0.5">Absent</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-blue-700">{{ number_format($payroll->total_hours ?? 0, 1) }}</p>
                        <p class="text-[10px] text-blue-600 font-medium mt-0.5">Hours</p>
                    </div>
                </div>

                {{-- Hours Progress --}}
                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-gray-500">Hours Worked: {{ number_format($payroll->total_hours ?? 0, 1) }} / {{ $expectedHours }} hrs expected</span>
                        <span class="font-semibold {{ $hoursPct >= 90 ? 'text-green-600' : ($hoursPct >= 70 ? 'text-yellow-600' : 'text-red-600') }}">{{ $hoursPct }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $hoursPct >= 90 ? 'bg-green-500' : ($hoursPct >= 70 ? 'bg-yellow-500' : 'bg-red-500') }}"
                             style="width: {{ $hoursPct }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Salary Breakdown --}}
            <div class="px-6 py-5 border-b border-gray-100">
                <h4 class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Salary Breakdown</h4>
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Base / Gross Salary</span>
                        <span class="font-medium text-gray-800">Rs. {{ number_format($payroll->gross_salary, 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Working Days</span>
                        <span class="font-medium text-gray-800">{{ $workingDays }} days ({{ $expectedHours }} hrs)</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Hourly Rate</span>
                        <span class="font-medium text-gray-800">Rs. {{ number_format($payroll->hourly_rate ?? 0, 2) }} / hr</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Hours Worked</span>
                        <span class="font-medium text-gray-800">{{ number_format($payroll->total_hours ?? 0, 1) }} hrs</span>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Calculated (Hours x Rate)</span>
                        <span class="font-medium text-gray-800">Rs. {{ number_format(($payroll->hourly_rate ?? 0) * ($payroll->total_hours ?? 0), 0) }}</span>
                    </div>

                    @if($payroll->deductions > 0)
                        <div class="border-t border-dashed pt-2.5">
                            <div class="flex items-center justify-between text-sm">
                                <span class="text-red-600">Deductions (Absent/Short Hours)</span>
                                <span class="font-medium text-red-600">- Rs. {{ number_format($payroll->deductions, 0) }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Net Salary --}}
                    <div class="border-t-2 border-gray-200 pt-3 mt-1">
                        <div class="flex items-center justify-between">
                            <span class="text-base font-bold text-gray-800">Net Salary</span>
                            <span class="text-xl font-bold text-green-600">Rs. {{ number_format($payroll->net_salary, 0) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Salary % Bar --}}
                <div class="mt-4">
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="text-gray-500">Salary Earned</span>
                        <span class="font-semibold {{ $salaryPct >= 90 ? 'text-green-600' : ($salaryPct >= 70 ? 'text-yellow-600' : 'text-red-600') }}">{{ $salaryPct }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full {{ $salaryPct >= 90 ? 'bg-green-500' : ($salaryPct >= 70 ? 'bg-yellow-500' : 'bg-red-500') }}"
                             style="width: {{ $salaryPct }}%"></div>
                    </div>
                </div>
            </div>

            {{-- Footer Note --}}
            <div class="px-6 py-4 bg-gray-50 text-xs text-gray-400 text-center">
                Generated on {{ now()->format('d M Y, h:i A') }} — {{ $monthName }} {{ $payroll->year }} Payroll
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-between">
            <div>
                @if(!$isPaid)
                    <form method="POST" action="{{ route('admin.payroll.markPaid', $payroll) }}" class="inline"
                          onsubmit="return confirm('Mark as paid?')">
                        @csrf
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-lg font-medium transition">
                            <i class="fas fa-check"></i> Mark as Paid
                        </button>
                    </form>
                @endif
            </div>
            <button onclick="window.print()"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-lg font-medium transition">
                <i class="fas fa-print"></i> Print Payslip
            </button>
        </div>

    </div>

    {{-- Print Styles --}}
    <style>
        @media print {
            body * { visibility: hidden; }
            #payslip-content, #payslip-content * { visibility: visible; }
            #payslip-content { position: absolute; left: 0; top: 0; width: 100%; box-shadow: none !important; border: none !important; }
            nav, .no-print, button, form, a { display: none !important; }
        }
    </style>
@endsection
