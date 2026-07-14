@extends('layouts.admin')

@section('content')
    <div class="p-6 bg-white rounded-lg shadow-md">
        <h1 class="text-2xl font-semibold mb-4 text-gray-800">Create Customer</h1>
        <form action="{{ route('admin.customers.store') }}" method="POST">
            @include('admin.customers._form', ['buttonText' => 'Create Customer'])
        </form>
    </div>
@endsection
