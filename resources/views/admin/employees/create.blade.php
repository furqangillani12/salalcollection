@extends('layouts.admin')

@section('title', 'Add Employee')

@section('content')
<div class="p-3 sm:p-6">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
        <div>
            <a href="{{ route('employees.index') }}" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-cyan-700 mb-2">
                <i class="fas fa-arrow-left text-xs"></i> Back to Employees
            </a>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-user-plus text-emerald-600"></i> Add Employee
            </h1>
            <p class="text-xs sm:text-sm text-gray-500 mt-1">Create a user account with role and employment details.</p>
        </div>
    </div>

    <form action="{{ route('employees.store') }}" method="POST">
        @csrf

        @include('admin.employees.partials.form', ['employee' => null])

        <div class="mt-5 flex flex-col sm:flex-row gap-3 justify-end">
            <a href="{{ route('employees.index') }}"
               class="px-5 py-2.5 bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 rounded-lg text-sm font-semibold text-center">
                Cancel
            </a>
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 text-white rounded-lg text-sm font-semibold shadow-sm"
                    style="background:linear-gradient(135deg,#059669,#047857);">
                <i class="fas fa-check"></i> Create Employee
            </button>
        </div>
    </form>
</div>
@endsection
