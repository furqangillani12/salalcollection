@extends('layouts.admin')
@section('title', 'Edit Brand')
@section('content')
<div class="p-3 sm:p-6">
    <a href="{{ route('admin.brands.index') }}" class="text-sm text-gray-600 hover:text-cyan-700 inline-flex items-center gap-1.5 mb-3"><i class="fas fa-arrow-left text-xs"></i> Back</a>
    <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-5"><i class="fas fa-pen text-amber-600"></i> Edit Brand: {{ $brand->name }}</h1>

    @if ($errors->any())
        <div class="mb-4 p-3 bg-red-50 text-red-800 rounded-lg border border-red-200 text-sm"><ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <form method="POST" action="{{ route('admin.brands.update', $brand) }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
        @method('PUT')
        @include('admin.brands._form')
    </form>
</div>
@endsection
