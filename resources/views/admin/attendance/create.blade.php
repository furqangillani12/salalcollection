@extends('layouts.admin')

@section('title', 'Mark Attendance')

@section('content')
    <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-semibold mb-6 text-gray-800">📝 Mark Attendance</h2>

        @if(session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-700 border border-red-300 rounded">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.attendance.store') }}" class="space-y-8">
            @csrf

            <!-- Daily Attendance Info -->
            <div class="border-b pb-6">
                <h3 class="text-lg font-semibold text-gray-700 mb-4">📌 Daily Attendance</h3>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label for="employee_id" class="block text-sm font-medium text-gray-700 mb-1">Employee</label>
                        <select name="employee_id" id="employee_id" required
                                class="w-full border-gray-300 rounded px-3 py-2 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->user->name }} ({{ $employee->phone }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" name="date" id="date" required
                               value="{{ old('date', now()->format('Y-m-d')) }}"
                               class="w-full border-gray-300 rounded px-3 py-2 text-sm shadow-sm focus:ring-blue-500 focus:border-blue-500">
                    </div>
                </div>

                <div class="grid md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" required
                                class="w-full border-gray-300 rounded px-3 py-2 text-sm">
                            <option value="present" {{ old('status') == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="late" {{ old('status') == 'late' ? 'selected' : '' }}>Late</option>
                            <option value="on_leave" {{ old('status') == 'on_leave' ? 'selected' : '' }}>On Leave</option>
                            <option value="half_day" {{ old('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                            <option value="absent" {{ old('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                        </select>
                    </div>

                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes (optional)</label>
                        <input type="text" name="notes" id="notes" value="{{ old('notes') }}"
                               class="w-full border-gray-300 rounded px-3 py-2 text-sm">
                    </div>
                </div>
            </div>

            <!-- Work Sessions Section -->
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-4">🕒 Work Sessions</h3>
                <p class="text-sm text-gray-500 mb-3">Add one or more work sessions. Breaks are the gaps between sessions.</p>

                <div id="sessions-list" class="space-y-2">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 session-row">
                        <div>
                            <label class="block text-xs text-gray-600">Check In</label>
                            <input type="time" name="sessions[0][check_in]" required
                                   class="w-full border-gray-300 rounded px-3 py-2 text-sm"
                                   value="{{ old('sessions.0.check_in', '09:00') }}">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600">Check Out</label>
                            <input type="time" name="sessions[0][check_out]"
                                   class="w-full border-gray-300 rounded px-3 py-2 text-sm"
                                   value="{{ old('sessions.0.check_out') }}">
                        </div>
                    </div>
                </div>

                <div class="mt-3 flex flex-wrap gap-2">
                    <button type="button" id="add-session" class="px-3 py-1 bg-green-600 text-white rounded">➕ Add Another Session</button>
                    <button type="button" id="remove-session" class="px-3 py-1 bg-red-500 text-white rounded">➖ Remove Last</button>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="text-right">
                <button type="submit"
                        class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 text-sm font-semibold shadow">
                    💾 Save Attendance
                </button>
            </div>
        </form>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('click', function (e) {
            if (e.target && e.target.id === 'add-session') {
                let wrapper = document.getElementById('sessions-list');
                let idx = wrapper.querySelectorAll('.session-row').length;
                let row = document.createElement('div');
                row.className = 'grid grid-cols-1 sm:grid-cols-2 gap-4 session-row mt-2';
                row.innerHTML = `
                <div>
                    <label class="block text-xs text-gray-600">Check In</label>
                    <input type="time" name="sessions[${idx}][check_in]" required class="w-full border-gray-300 rounded px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs text-gray-600">Check Out</label>
                    <input type="time" name="sessions[${idx}][check_out]" class="w-full border-gray-300 rounded px-3 py-2 text-sm">
                </div>
            `;
                wrapper.appendChild(row);
            }

            if (e.target && e.target.id === 'remove-session') {
                let wrapper = document.getElementById('sessions-list');
                let rows = wrapper.querySelectorAll('.session-row');
                if (rows.length > 1) {
                    wrapper.removeChild(rows[rows.length - 1]);
                }
            }
        });
    </script>
@endsection
