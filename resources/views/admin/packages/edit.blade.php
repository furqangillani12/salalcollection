@extends('layouts.admin')
@section('content')
<div class="container mx-auto px-4 py-6 max-w-3xl">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Edit Package — {{ $package->name }}</h1>
        <a href="{{ route('admin.packages.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg text-sm">Back</a>
    </div>
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <form action="{{ route('admin.packages.update', $package) }}" method="POST">
            @method('PUT')
            @php $buttonText = 'Update Package'; @endphp
            @include('admin.packages._form')
        </form>
    </div>
</div>
@endsection
