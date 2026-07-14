@extends('layouts.admin')

@section('title', 'Employees')

@section('content')
<div class="p-3 sm:p-6">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-id-badge text-cyan-600"></i> Employees
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Manage staff accounts, roles, and personal details.</p>
        </div>
        <a href="{{ route('employees.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-white rounded-lg text-sm font-semibold shadow-sm hover:shadow"
           style="background:linear-gradient(135deg,#0891b2,#0e7490);">
            <i class="fas fa-plus"></i> Add Employee
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-emerald-50 text-emerald-800 rounded-lg border border-emerald-200 text-sm">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-50 text-red-800 rounded-lg border border-red-200 text-sm">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
        </div>
    @endif

    {{-- ── Stats row ── --}}
    @php
        $totalCount = $employees->total();
        $totalSalary = $employees->getCollection()->sum('salary');
        $rolesCount = $roles->count();
    @endphp
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mb-5">
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] uppercase tracking-wide text-cyan-700 font-semibold">Total Employees</div>
                    <div class="text-2xl font-extrabold text-gray-800 mt-1">{{ $totalCount }}</div>
                </div>
                <span class="w-10 h-10 rounded-full bg-cyan-100 text-cyan-600 flex items-center justify-center">
                    <i class="fas fa-users"></i>
                </span>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] uppercase tracking-wide text-emerald-700 font-semibold">Salary (this page)</div>
                    <div class="text-2xl font-extrabold text-gray-800 mt-1">Rs. {{ number_format($totalSalary, 0) }}</div>
                </div>
                <span class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-money-bill-wave"></i>
                </span>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 shadow-sm col-span-2 sm:col-span-1">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-[11px] uppercase tracking-wide text-indigo-700 font-semibold">Roles</div>
                    <div class="text-2xl font-extrabold text-gray-800 mt-1">{{ $rolesCount }}</div>
                </div>
                <span class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <i class="fas fa-user-shield"></i>
                </span>
            </div>
        </div>
    </div>

    {{-- ── Search + filter ── --}}
    <form method="GET" action="{{ route('employees.index') }}" class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-5">
        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
            <div class="sm:col-span-7" style="position:relative;">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name, email, phone, address..."
                       class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                <i class="fas fa-search text-gray-400"
                   style="position:absolute;left:14px;top:50%;transform:translateY(-50%);pointer-events:none;"></i>
            </div>
            <div class="sm:col-span-3">
                <select name="role" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                    <option value="">All Roles</option>
                    @foreach ($roles as $r)
                        <option value="{{ $r->name }}" @selected(request('role') === $r->name)>{{ ucfirst(str_replace('_', ' ', $r->name)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 flex gap-2">
                <button type="submit" class="flex-1 px-3 py-2 bg-cyan-600 hover:bg-cyan-700 text-white text-sm font-semibold rounded-lg">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                @if (request()->filled('search') || request()->filled('role'))
                    <a href="{{ route('employees.index') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg" title="Clear">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>

    {{-- ── Table ── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr class="text-left text-[11px] uppercase tracking-wide text-gray-600">
                        <th class="px-4 py-3">Employee</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3 text-right">Salary</th>
                        <th class="px-4 py-3">Joined</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php
                        $colors = ['#0891b2','#7c3aed','#db2777','#059669','#d97706','#dc2626','#2563eb','#6d28d9'];
                        $roleColors = [
                            'super_admin' => ['bg' => '#fef2f2', 'text' => '#b91c1c', 'border' => '#fecaca'],
                            'admin'       => ['bg' => '#eff6ff', 'text' => '#1d4ed8', 'border' => '#bfdbfe'],
                            'manager'     => ['bg' => '#f5f3ff', 'text' => '#6d28d9', 'border' => '#ddd6fe'],
                            'cashier'     => ['bg' => '#ecfdf5', 'text' => '#047857', 'border' => '#a7f3d0'],
                            'employee'    => ['bg' => '#f1f5f9', 'text' => '#475569', 'border' => '#e2e8f0'],
                        ];
                    @endphp
                    @forelse($employees as $i => $employee)
                        @php
                            $name = $employee->user?->name ?? '—';
                            $initials = collect(preg_split('/\s+/', trim($name)))->take(2)->map(fn ($w) => mb_substr($w, 0, 1))->implode('');
                            $color = $colors[$employee->id % count($colors)];
                            $roleName = $employee->user?->roles?->first()?->name;
                            $roleStyle = $roleColors[$roleName] ?? ['bg' => '#f3f4f6', 'text' => '#6b7280', 'border' => '#e5e7eb'];
                        @endphp
                        <tr class="hover:bg-gray-50">
                            {{-- Employee --}}
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full text-white font-bold flex items-center justify-center text-sm shadow-sm flex-shrink-0"
                                         style="background:{{ $color }};">
                                        {{ strtoupper($initials ?: '?') }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-gray-800 truncate">{{ $name }}</div>
                                        <div class="text-xs text-gray-500 truncate">{{ $employee->user?->email ?? '—' }}</div>
                                    </div>
                                </div>
                            </td>

                            {{-- Role --}}
                            <td class="px-4 py-3">
                                @if ($roleName)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold border"
                                          style="background:{{ $roleStyle['bg'] }};color:{{ $roleStyle['text'] }};border-color:{{ $roleStyle['border'] }};">
                                        <i class="fas fa-user-shield mr-1 text-[10px]"></i>
                                        {{ ucfirst(str_replace('_', ' ', $roleName)) }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Contact --}}
                            <td class="px-4 py-3">
                                @if ($employee->phone)
                                    <a href="tel:{{ $employee->phone }}" class="text-gray-700 hover:text-cyan-700 text-xs flex items-center gap-1">
                                        <i class="fas fa-phone text-cyan-500 text-[10px]"></i> {{ $employee->phone }}
                                    </a>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                                @if ($employee->address)
                                    <div class="text-[11px] text-gray-500 mt-0.5 flex items-center gap-1 truncate max-w-[220px]" title="{{ $employee->address }}">
                                        <i class="fas fa-map-marker-alt text-gray-400 text-[10px]"></i> {{ $employee->address }}
                                    </div>
                                @endif
                            </td>

                            {{-- Salary --}}
                            <td class="px-4 py-3 text-right whitespace-nowrap">
                                @if ($employee->salary)
                                    <span class="font-bold text-emerald-700">Rs. {{ number_format($employee->salary, 0) }}</span>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>

                            {{-- Joined --}}
                            <td class="px-4 py-3 text-xs text-gray-600 whitespace-nowrap">
                                @if ($employee->joining_date)
                                    {{ \Carbon\Carbon::parse($employee->joining_date)->format('d M Y') }}
                                    <div class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($employee->joining_date)->diffForHumans() }}</div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('employees.show', $employee) }}"
                                       class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-md text-xs font-medium border border-blue-200" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('employees.edit', $employee) }}"
                                       class="px-2.5 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 rounded-md text-xs font-medium border border-amber-200" title="Edit">
                                        <i class="fas fa-pen"></i>
                                    </a>
                                    <form action="{{ route('employees.destroy', $employee) }}" method="POST" class="inline-block"
                                          onsubmit="return confirm('Delete employee {{ addslashes($name) }}? This will also remove their user account.');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-md text-xs font-medium border border-rose-200" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-16 text-center text-gray-400">
                                <i class="fas fa-user-slash text-4xl mb-2 block"></i>
                                @if (request()->filled('search') || request()->filled('role'))
                                    No employees match your filters.
                                    <a href="{{ route('employees.index') }}" class="block mt-2 text-cyan-600 hover:underline text-sm font-medium">Clear filters</a>
                                @else
                                    No employees yet.
                                    <a href="{{ route('employees.create') }}" class="block mt-2 text-cyan-600 hover:underline text-sm font-medium">Add your first employee</a>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($employees->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 bg-gray-50">
                {{ $employees->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
