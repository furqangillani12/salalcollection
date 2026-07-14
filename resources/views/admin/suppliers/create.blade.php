@extends('layouts.admin')

@section('title', 'Add New Supplier')

@section('content')
    <div class="p-6 bg-white rounded-lg shadow-md">
        <div class="flex flex-wrap justify-between items-center gap-3 mb-6">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800">Add New Supplier</h1>
                <p class="text-sm text-gray-600 mt-1">Create a new supplier record</p>
            </div>
            <a href="{{ route('suppliers.index') }}"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 flex items-center">
                <svg class="h-4 w-4 mr-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd"
                        d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                        clip-rule="evenodd" />
                </svg>
                Back
            </a>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded border border-red-200">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('suppliers.store') }}" method="POST">
            @include('admin.suppliers._form', ['buttonText' => 'Save Supplier'])
        </form>
    </div>
@endsection
