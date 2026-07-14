@php
    $isEdit = isset($employee) && $employee !== null;
    $inputClass = "w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500";
@endphp

@if ($errors->any())
    <div class="mb-4 p-3 bg-red-50 text-red-800 rounded-lg border border-red-200 text-sm">
        <ul class="list-disc list-inside space-y-0.5">
            @foreach ($errors->all() as $err)<li>{{ $err }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="space-y-5">

    {{-- ── Account credentials ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50" style="border-radius:0.75rem 0.75rem 0 0;">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide flex items-center gap-2">
                <i class="fas fa-user-circle text-cyan-600"></i> Account Credentials
            </h2>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Full Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required value="{{ old('name', $employee?->user->name ?? '') }}" class="{{ $inputClass }}" placeholder="e.g. Ahmad Khan">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Email <span class="text-rose-500">*</span></label>
                <input type="email" name="email" required value="{{ old('email', $employee?->user->email ?? '') }}" class="{{ $inputClass }}" placeholder="email@example.com">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">
                    Password
                    @if ($isEdit) <span class="text-gray-400 font-normal text-[11px]">(leave blank to keep current)</span>
                    @else <span class="text-rose-500">*</span>
                    @endif
                </label>
                <input type="password" name="password" class="{{ $inputClass }}" placeholder="{{ $isEdit ? 'New password (optional)' : 'Min 8 characters' }}" {{ $isEdit ? '' : 'required' }} minlength="8">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Role <span class="text-rose-500">*</span></label>
                <select name="role" required class="{{ $inputClass }}">
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" @selected(old('role', $employee?->user->roles->first()->name ?? '') === $role->name)>
                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ── Contact info ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50" style="border-radius:0.75rem 0.75rem 0 0;">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide flex items-center gap-2">
                <i class="fas fa-address-book text-purple-600"></i> Contact Info
            </h2>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Phone</label>
                <input type="text" name="phone" value="{{ old('phone', $employee?->phone ?? '') }}" class="{{ $inputClass }}" placeholder="+92 300 1234567">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Address</label>
                <input type="text" name="address" value="{{ old('address', $employee?->address ?? '') }}" class="{{ $inputClass }}" placeholder="Street, City">
            </div>
        </div>
    </div>

    {{-- ── Employment ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="px-5 py-3 border-b border-gray-100 bg-gray-50" style="border-radius:0.75rem 0.75rem 0 0;">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide flex items-center gap-2">
                <i class="fas fa-briefcase text-emerald-600"></i> Employment
            </h2>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Monthly Salary (Rs.)</label>
                <input type="number" name="salary" min="0" step="1" value="{{ old('salary', $employee?->salary ?? '') }}" class="{{ $inputClass }}" placeholder="50000">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1.5">Joining Date</label>
                <input type="date" name="joining_date" value="{{ old('joining_date', $employee?->joining_date ?? '') }}" class="{{ $inputClass }}">
            </div>
        </div>
    </div>
</div>
