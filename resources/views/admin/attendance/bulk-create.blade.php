@extends('layouts.admin')

@section('title', 'Bulk Attendance Marking')

@section('content')
    <div class="space-y-5">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <a href="{{ route('admin.attendance.index') }}" class="text-sm text-blue-600 hover:underline">← Back to Attendance</a>
                <h2 class="text-xl font-bold text-gray-800 mt-1">Bulk Attendance</h2>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
            </div>
        @endif

        {{-- Already Marked (if any) --}}
        @if($markedEmployees->count())
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-green-800 mb-2">
                    <i class="fas fa-check-circle"></i> Already Marked ({{ $markedEmployees->count() }})
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($markedEmployees as $emp)
                        @php $att = $emp->attendances->first(); @endphp
                        <span class="inline-flex items-center gap-1.5 bg-white border border-green-200 rounded-full px-3 py-1 text-xs">
                            <span class="font-medium text-gray-700">{{ $emp->user->name }}</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold
                                {{ $att && $att->status === 'present' ? 'bg-green-100 text-green-700' :
                                   ($att && $att->status === 'late' ? 'bg-yellow-100 text-yellow-700' :
                                   ($att && $att->status === 'on_leave' ? 'bg-blue-100 text-blue-700' : 'bg-gray-100 text-gray-700')) }}">
                                {{ $att ? ucfirst(str_replace('_', ' ', $att->status)) : '' }}
                            </span>
                        </span>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Quick Check-In Section --}}
        @if($employees->count())
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
                    <i class="fas fa-bolt text-yellow-500"></i> Quick Check-In (One Click)
                </h3>
                <div class="flex flex-wrap gap-2">
                    @foreach($employees as $employee)
                        <form method="POST" action="{{ route('admin.attendance.quick-checkin') }}" class="inline">
                            @csrf
                            <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                            <button type="submit"
                                    class="bg-green-50 hover:bg-green-100 border border-green-200 text-green-700 px-4 py-2 rounded-lg text-sm font-medium transition flex items-center gap-2">
                                <i class="fas fa-sign-in-alt"></i> {{ $employee->user->name }}
                            </button>
                        </form>
                    @endforeach
                </div>
                <p class="text-xs text-gray-400 mt-2">Click to check in now ({{ now()->format('h:i A') }}). Auto-detects late arrival.</p>
            </div>
        @endif

        {{-- Bulk Form --}}
        @if($employees->count())
            <form method="POST" action="{{ route('admin.attendance.bulk-store') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="date" value="{{ $date }}">

                <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 py-4 bg-gray-50 border-b flex flex-wrap items-center justify-between gap-3">
                        <h3 class="font-semibold text-gray-700">
                            Unmarked Employees ({{ $employees->count() }})
                        </h3>
                        <div class="flex items-center gap-3">
                            <label class="text-xs text-gray-500">Date:</label>
                            <span class="text-sm font-semibold text-gray-800">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
                        </div>
                    </div>

                    <div class="divide-y divide-gray-50">
                        @foreach($employees as $i => $employee)
                            <div class="px-5 py-4 hover:bg-gray-50/50 transition">
                                <input type="hidden" name="attendances[{{ $i }}][employee_id]" value="{{ $employee->id }}">

                                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                    {{-- Employee Name --}}
                                    <div class="flex items-center gap-3 sm:w-48">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold">
                                            {{ strtoupper(substr($employee->user->name, 0, 1)) }}
                                        </div>
                                        <span class="font-medium text-gray-800 text-sm">{{ $employee->user->name }}</span>
                                    </div>

                                    {{-- Status --}}
                                    <select name="attendances[{{ $i }}][status]"
                                            class="border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 status-select"
                                            data-index="{{ $i }}">
                                        <option value="present">Present</option>
                                        <option value="late">Late</option>
                                        <option value="half_day">Half Day</option>
                                        <option value="on_leave">On Leave</option>
                                        <option value="absent">Absent</option>
                                    </select>

                                    {{-- Session Times --}}
                                    <div class="sessions-wrapper flex flex-wrap items-center gap-2" data-index="{{ $i }}">
                                        <div class="flex items-center gap-1 session-row">
                                            <input type="time" name="attendances[{{ $i }}][sessions][0][check_in]"
                                                   class="border border-gray-200 rounded-lg px-2 py-2 text-sm w-28 checkin-input"
                                                   value="09:00">
                                            <span class="text-gray-300">→</span>
                                            <input type="time" name="attendances[{{ $i }}][sessions][0][check_out]"
                                                   class="border border-gray-200 rounded-lg px-2 py-2 text-sm w-28">
                                        </div>
                                    </div>

                                    {{-- Notes --}}
                                    <input type="text" name="attendances[{{ $i }}][notes]" placeholder="Notes..."
                                           class="border border-gray-200 rounded-lg px-3 py-2 text-sm flex-1 min-w-0 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="px-5 py-4 bg-gray-50 border-t">
                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold text-sm transition flex items-center gap-2">
                            <i class="fas fa-save"></i> Save All Attendance
                        </button>
                    </div>
                </div>
            </form>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 p-12 text-center">
                <div class="text-gray-400">
                    <i class="fas fa-check-double text-4xl mb-3 text-green-400"></i>
                    <p class="font-medium text-gray-600">All employees are marked for today!</p>
                    <a href="{{ route('admin.attendance.index') }}" class="text-blue-600 hover:underline text-sm mt-2 inline-block">
                        View attendance →
                    </a>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Auto-adjust check-in time based on status
            document.querySelectorAll('.status-select').forEach(select => {
                select.addEventListener('change', function () {
                    const wrapper = this.closest('.flex').querySelector('.sessions-wrapper');
                    const checkIn = wrapper?.querySelector('.checkin-input');
                    if (!checkIn) return;

                    switch (this.value) {
                        case 'late':     checkIn.value = '10:00'; break;
                        case 'half_day': checkIn.value = '13:00'; break;
                        case 'absent':
                        case 'on_leave': checkIn.value = ''; break;
                        default:         checkIn.value = '09:00';
                    }
                });
            });
        });
    </script>
    @endpush
@endsection
