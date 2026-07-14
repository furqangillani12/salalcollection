@extends('layouts.admin')

@section('content')
    <div class="p-6 bg-white rounded-lg shadow-md">
        <h1 class="text-2xl font-semibold mb-4 text-gray-800">Edit Customer</h1>

        @if($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded border border-red-200">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if(session('warning'))
            <div class="mb-4 p-3 bg-yellow-100 text-yellow-800 rounded border border-yellow-200">
                {{ session('warning') }}
            </div>
        @endif

        <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
            @method('PUT')
            @include('admin.customers._form', ['buttonText' => 'Update Customer'])
        </form>
    </div>
@endsection
