@extends('layouts.admin')

@section('title', 'Daily Attendance Report')

@section('content')
    <div class="space-y-5">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <a href="{{ route('admin.attendance.index') }}" class="text-sm text-blue-600 hover:underline">← Back</a>
                <h2 class="text-xl font-bold text-gray-800 mt-1">Daily Report</h2>
                <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</p>
            </div>
            <form method="GET" class="flex items-center gap-2">
                <input type="date" name="date"
                       class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                       value="{{ $date }}" max="{{ now()->format('Y-m-d') }}">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-blue-700">
                    Generate
                </button>
            </form>
        </div>

        {{-- Summary Cards --}}
        @php
            $present = $attendances->get('present', collect())->count();
            $late = $attendances->get('late', collect())->count();
            $onLeave = $attendances->get('on_leave', collect())->count();
            $halfDay = $attendances->get('half_day', collect())->count();
            $marked = $attendances->flatten()->count();
            $unmarked = $allEmployees - $marked;
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-green-700">{{ $present }}</p>
                <p class="text-xs text-green-600 font-medium mt-1">Present</p>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-yellow-700">{{ $late }}</p>
                <p class="text-xs text-yellow-600 font-medium mt-1">Late</p>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-purple-700">{{ $halfDay }}</p>
                <p class="text-xs text-purple-600 font-medium mt-1">Half Day</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-blue-700">{{ $onLeave }}</p>
                <p class="text-xs text-blue-600 font-medium mt-1">On Leave</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-red-700">{{ $unmarked }}</p>
                <p class="text-xs text-red-600 font-medium mt-1">Absent/Unmarked</p>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 text-center">
                <p class="text-3xl font-bold text-gray-700">{{ $allEmployees }}</p>
                <p class="text-xs text-gray-500 font-medium mt-1">Total Staff</p>
            </div>
        </div>

        {{-- Attendance Rate Bar --}}
        @php $rate = $allEmployees > 0 ? round(($present + $late + $halfDay) / $allEmployees * 100) : 0; @endphp
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm font-semibold text-gray-700">Attendance Rate</span>
                <span class="text-sm font-bold {{ $rate >= 80 ? 'text-green-600' : ($rate >= 50 ? 'text-yellow-600' : 'text-red-600') }}">{{ $rate }}%</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-3">
                <div class="h-3 rounded-full transition-all {{ $rate >= 80 ? 'bg-green-500' : ($rate >= 50 ? 'bg-yellow-500' : 'bg-red-500') }}"
                     style="width: {{ $rate }}%"></div>
            </div>
        </div>

        {{-- Status Groups --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            @foreach(['present' => ['Present', 'green'], 'late' => ['Late', 'yellow'], 'on_leave' => ['On Leave', 'blue'], 'half_day' => ['Half Day', 'purple']] as $status => $info)
                @if($attendances->has($status) && $attendances[$status]->count())
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="px-5 py-3 border-b bg-{{ $info[1] }}-50">
                            <h3 class="font-semibold text-{{ $info[1] }}-800 text-sm flex items-center gap-2">
                                {{ $info[0] }} <span class="bg-{{ $info[1] }}-200 text-{{ $info[1] }}-800 px-2 py-0.5 rounded-full text-xs">{{ $attendances[$status]->count() }}</span>
                            </h3>
                        </div>
                        <ul class="divide-y divide-gray-50">
                            @foreach($attendances[$status] as $attendance)
                                <li class="px-5 py-3 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full bg-{{ $info[1] }}-100 text-{{ $info[1] }}-700 flex items-center justify-center text-xs font-bold">
                                            {{ strtoupper(substr($attendance->employee->user->name ?? '?', 0, 1)) }}
                                        </div>
                                        <span class="text-sm font-medium text-gray-800">{{ $attendance->employee->user->name }}</span>
                                    </div>
                                    <div class="text-right text-xs text-gray-500">
                                        @if($attendance->sessions->count())
                                            @foreach($attendance->sessions as $s)
                                                <div class="font-mono">
                                                    {{ \Carbon\Carbon::parse($s->check_in)->format('h:i A') }}
                                                    @if($s->check_out) — {{ \Carbon\Carbon::parse($s->check_out)->format('h:i A') }} @endif
                                                </div>
                                            @endforeach
                                        @else
                                            <span class="text-gray-300">—</span>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endforeach
        </div>

    </div>
@endsection
