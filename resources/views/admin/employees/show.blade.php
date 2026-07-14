@extends('layouts.admin')

@section('title', 'Employee Details')

@section('content')
@php
    $name      = $employee->user?->name ?? '—';
    $email     = $employee->user?->email ?? '—';
    $initials  = collect(preg_split('/\s+/', trim($name)))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
    $colors    = ['#0891b2','#7c3aed','#db2777','#059669','#d97706','#dc2626','#2563eb','#6d28d9'];
    $color     = $colors[$employee->id % count($colors)];
    $roleName  = $employee->user?->roles?->first()?->name;
    $roleColors = [
        'super_admin' => ['bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],
        'admin'       => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
        'manager'     => ['bg' => '#f5f3ff', 'text' => '#6d28d9', 'border' => '#ddd6fe'],
        'cashier'     => ['bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0'],
        'employee'    => ['bg' => '#f1f5f9', 'text' => '#475569', 'border' => '#e2e8f0'],
    ];
    $roleStyle = $roleColors[$roleName] ?? ['bg' => '#f3f4f6', 'text' => '#6b7280', 'border' => '#e5e7eb'];
    $tenure    = $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->diffForHumans(null, true) : null;
@endphp

<div class="p-3 sm:p-6">

    {{-- ── Back link ── --}}
    <div class="mb-4">
        <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-cyan-700">
            <i class="fas fa-arrow-left text-xs"></i> Back to Employees
        </a>
    </div>

    {{-- ── Hero card ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5">
        <div class="p-5 sm:p-6 text-white" style="background:linear-gradient(135deg,{{ $color }},#1e293b);">
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div class="w-20 h-20 rounded-full bg-white/20 backdrop-blur flex items-center justify-center text-3xl font-extrabold border-4 border-white/30 flex-shrink-0">
                    {{ strtoupper($initials ?: '?') }}
                </div>
                <div class="flex-1 min-w-0">
                    <h1 class="text-2xl font-extrabold truncate">{{ $name }}</h1>
                    <div class="text-sm opacity-90 mt-1 truncate">
                        <i class="fas fa-envelope mr-1"></i> {{ $email }}
                    </div>
                    @if ($roleName)
                        <span class="inline-flex items-center mt-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/15 backdrop-blur border border-white/30">
                            <i class="fas fa-user-shield mr-1.5 text-[10px]"></i>
                            {{ ucfirst(str_replace('_', ' ', $roleName)) }}
                        </span>
                    @endif
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <a href="{{ route('employees.edit', $employee) }}"
                       class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2 bg-white text-gray-800 rounded-lg text-sm font-semibold shadow-sm hover:bg-gray-50">
                        <i class="fas fa-pen"></i> Edit
                    </a>
                    <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="flex-1 sm:flex-none"
                          onsubmit="return confirm('Delete employee {{ addslashes($name) }}? This will also remove their user account.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-sm font-semibold shadow-sm">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Quick stats strip --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-gray-100 border-t border-gray-100">
            <div class="p-4 text-center">
                <div class="text-[10px] uppercase tracking-wide text-gray-500 font-semibold">Salary</div>
                <div class="text-lg font-extrabold text-emerald-700 mt-0.5">
                    {{ $employee->salary ? 'Rs. ' . number_format($employee->salary, 0) : '—' }}
                </div>
            </div>
            <div class="p-4 text-center">
                <div class="text-[10px] uppercase tracking-wide text-gray-500 font-semibold">Joined</div>
                <div class="text-lg font-extrabold text-gray-800 mt-0.5">
                    {{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : '—' }}
                </div>
            </div>
            <div class="p-4 text-center border-t sm:border-t-0 border-gray-100">
                <div class="text-[10px] uppercase tracking-wide text-gray-500 font-semibold">Tenure</div>
                <div class="text-lg font-extrabold text-indigo-700 mt-0.5">
                    {{ $tenure ?? '—' }}
                </div>
            </div>
            <div class="p-4 text-center border-t sm:border-t-0 border-gray-100">
                <div class="text-[10px] uppercase tracking-wide text-gray-500 font-semibold">Branch</div>
                <div class="text-lg font-extrabold text-gray-800 mt-0.5 truncate">
                    {{ $employee->branch?->name ?? 'All' }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── Detail cards ── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Account --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50" style="border-radius:0.75rem 0.75rem 0 0;">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide flex items-center gap-2">
                    <i class="fas fa-user-circle text-cyan-600"></i> Account
                </h2>
            </div>
            <div class="p-5 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Name</span>
                    <span class="font-semibold text-gray-800">{{ $name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Email</span>
                    <a href="mailto:{{ $email }}" class="font-semibold text-cyan-700 hover:underline">{{ $email }}</a>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Role</span>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border"
                          style="background:{{ $roleStyle['bg'] }};color:{{ $roleStyle['text'] }};border-color:{{ $roleStyle['border'] }};">
                        {{ $roleName ? ucfirst(str_replace('_', ' ', $roleName)) : '—' }}
                    </span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">User ID</span>
                    <span class="font-mono text-xs text-gray-600">#{{ $employee->user_id }}</span>
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50" style="border-radius:0.75rem 0.75rem 0 0;">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide flex items-center gap-2">
                    <i class="fas fa-address-book text-purple-600"></i> Contact
                </h2>
            </div>
            <div class="p-5 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Phone</span>
                    @if ($employee->phone)
                        <a href="tel:{{ $employee->phone }}" class="font-semibold text-cyan-700 hover:underline">{{ $employee->phone }}</a>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </div>
                <div>
                    <div class="text-gray-500 mb-1">Address</div>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 text-gray-700 text-sm leading-relaxed">
                        {{ $employee->address ?: '— Not set —' }}
                    </div>
                </div>
            </div>
        </div>

        {{-- Employment --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 lg:col-span-2">
            <div class="px-5 py-3 border-b border-gray-100 bg-gray-50" style="border-radius:0.75rem 0.75rem 0 0;">
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide flex items-center gap-2">
                    <i class="fas fa-briefcase text-emerald-600"></i> Employment
                </h2>
            </div>
            <div class="p-5 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                <div>
                    <div class="text-[11px] uppercase tracking-wide text-gray-500 font-semibold">Salary</div>
                    <div class="text-xl font-extrabold text-emerald-700 mt-1">
                        {{ $employee->salary ? 'Rs. ' . number_format($employee->salary, 0) : '—' }}
                    </div>
                </div>
                <div>
                    <div class="text-[11px] uppercase tracking-wide text-gray-500 font-semibold">Joining Date</div>
                    <div class="text-xl font-extrabold text-gray-800 mt-1">
                        {{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') : '—' }}
                    </div>
                </div>
                <div>
                    <div class="text-[11px] uppercase tracking-wide text-gray-500 font-semibold">Branch</div>
                    <div class="text-xl font-extrabold text-gray-800 mt-1 truncate">
                        {{ $employee->branch?->name ?? 'All Branches' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
