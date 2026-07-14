@extends('layouts.admin')

@section('title', 'Attendance Records')

@section('content')
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Attendance</h2>
                <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($date)->format('l, d M Y') }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.attendance.bulk-create', ['date' => $date]) }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-users"></i> Bulk Mark
                </a>
                <a href="{{ route('admin.attendance.create') }}"
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-user-plus"></i> Single Entry
                </a>
                <a href="{{ route('admin.attendance.monthly-report') }}"
                   class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 text-sm font-medium flex items-center gap-2">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm flex items-center gap-2">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Summary Cards --}}
        <div class="grid grid-cols-3 sm:grid-cols-6 gap-3">
            <div class="bg-green-50 border border-green-200 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-green-700">{{ $summary['present'] }}</p>
                <p class="text-xs text-green-600 font-medium">Present</p>
            </div>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-yellow-700">{{ $summary['late'] }}</p>
                <p class="text-xs text-yellow-600 font-medium">Late</p>
            </div>
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-purple-700">{{ $summary['half_day'] }}</p>
                <p class="text-xs text-purple-600 font-medium">Half Day</p>
            </div>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-blue-700">{{ $summary['on_leave'] }}</p>
                <p class="text-xs text-blue-600 font-medium">On Leave</p>
            </div>
            <div class="bg-red-50 border border-red-200 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-red-700">{{ $summary['absent'] }}</p>
                <p class="text-xs text-red-600 font-medium">Absent</p>
            </div>
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-center">
                <p class="text-2xl font-bold text-gray-700">{{ $summary['unmarked'] }}</p>
                <p class="text-xs text-gray-500 font-medium">Unmarked</p>
            </div>
        </div>

        {{-- Date Filter --}}
        <form method="GET" class="bg-white rounded-lg shadow-sm border border-gray-100 p-3 flex flex-wrap items-center gap-3">
            <i class="fas fa-calendar-alt text-gray-400"></i>
            <input type="date" name="date"
                   class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200"
                   value="{{ $date }}" max="{{ now()->format('Y-m-d') }}">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 text-sm font-medium">
                Filter
            </button>
            @if($date !== now()->format('Y-m-d'))
                <a href="{{ route('admin.attendance.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Reset to Today</a>
            @endif
        </form>

        {{-- Attendance Table --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase border-b">
                        <tr>
                            <th class="px-4 py-3 text-left">Employee</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left">Sessions</th>
                            <th class="px-4 py-3 text-center">Hours</th>
                            <th class="px-4 py-3 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($attendances as $attendance)
                            @php
                                $hasOpen = $attendance->sessions->whereNull('check_out')->count() > 0;
                                $statusColors = [
                                    'present'  => 'bg-green-100 text-green-700',
                                    'late'     => 'bg-yellow-100 text-yellow-700',
                                    'on_leave' => 'bg-blue-100 text-blue-700',
                                    'half_day' => 'bg-purple-100 text-purple-700',
                                    'absent'   => 'bg-red-100 text-red-700',
                                ];
                            @endphp
                            <tr class="hover:bg-gray-50 transition {{ $hasOpen ? 'bg-green-50/30' : '' }}">
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                            {{ $attendance->status === 'present' || $attendance->status === 'late' ? 'bg-green-200 text-green-700' : 'bg-gray-200 text-gray-600' }}">
                                            {{ strtoupper(substr($attendance->employee->user->name ?? '?', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-800">{{ $attendance->employee->user->name }}</p>
                                            @if($attendance->notes)
                                                <p class="text-xs text-gray-400">{{ $attendance->notes }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="px-2.5 py-1 text-xs font-semibold rounded-full {{ $statusColors[$attendance->status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ ucfirst(str_replace('_', ' ', $attendance->status)) }}
                                    </span>
                                    @if($hasOpen)
                                        <span class="ml-1 inline-flex items-center gap-1 text-xs text-green-600">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> Active
                                        </span>
                                    @endif
                                </td>

                                <td class="px-4 py-3">
                                    @if($attendance->sessions->count())
                                        <div class="space-y-1">
                                            @foreach($attendance->sessions as $s)
                                                <div class="flex items-center gap-2 text-xs">
                                                    <span class="font-mono text-gray-700">{{ \Carbon\Carbon::parse($s->check_in)->format('h:i A') }}</span>
                                                    <span class="text-gray-300">→</span>
                                                    @if($s->check_out)
                                                        <span class="font-mono text-gray-700">{{ \Carbon\Carbon::parse($s->check_out)->format('h:i A') }}</span>
                                                        <span class="text-gray-400 text-[10px]">
                                                            ({{ \Carbon\Carbon::parse($s->check_in)->diff(\Carbon\Carbon::parse($s->check_out))->format('%hh %im') }})
                                                        </span>
                                                    @else
                                                        <span class="text-green-600 font-medium">ongoing...</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-gray-300 text-xs">No sessions</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <span class="font-semibold text-gray-800">{{ $attendance->total_worked_hours }}</span>
                                    <span class="text-xs text-gray-400">hrs</span>
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        @if($hasOpen && $attendance->status !== 'on_leave')
                                            <form method="POST" action="{{ route('admin.attendance.checkout', $attendance) }}" class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="bg-orange-100 hover:bg-orange-200 text-orange-700 text-xs px-3 py-1.5 rounded-lg font-medium transition"
                                                        title="Mark Check Out">
                                                    <i class="fas fa-sign-out-alt"></i> Out
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.attendance.destroy', $attendance) }}" class="inline"
                                              onsubmit="return confirm('Delete this attendance record?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-gray-300 hover:text-red-500 text-xs p-1.5 rounded transition" title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-12 text-center">
                                    <div class="text-gray-400">
                                        <i class="fas fa-clipboard-list text-3xl mb-2"></i>
                                        <p class="font-medium">No attendance records for this date</p>
                                        <a href="{{ route('admin.attendance.bulk-create', ['date' => $date]) }}"
                                           class="text-blue-600 hover:underline text-sm mt-2 inline-block">
                                            Mark attendance now →
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($attendances->hasPages())
                <div class="px-4 py-3 border-t">
                    {{ $attendances->appends(['date' => $date])->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
