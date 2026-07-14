@extends('layouts.admin')

@section('title', 'Edit Employee')

@section('content')
<div class="p-3 sm:p-6">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <a href="{{ route('employees.show', $employee) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-cyan-700 mb-2">
                <i class="fas fa-arrow-left text-xs"></i> Back to {{ $employee->user?->name ?? 'Employee' }}
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user-edit text-amber-600"></i> Edit Employee
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Update account, contact, and employment details.</p>
        </div>
    </div>

    <form action="{{ route('employees.update', $employee) }}" method="POST">
        @csrf
        @method('PUT')

        @include('admin.employees.partials.form')

        <div class="mt-5 flex flex-col sm:flex-row gap-3 justify-end">
            <a href="{{ route('employees.index') }}"
               class="px-5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-sm font-semibold text-center">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-white rounded-lg text-sm font-semibold shadow-sm"
                    style="background:linear-gradient(135deg,#0891b2,#0e7490);">
                <i class="fas fa-check"></i> Update Employee
            </button>
        </div>
    </form>
</div>
@endsection
