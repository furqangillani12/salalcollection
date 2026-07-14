@extends('layouts.admin')

@section('title', 'Edit Permission')

@section('content')
    <div class="max-w-xl mx-auto space-y-5">

        {{-- Back --}}
        <a href="{{ route('permissions.index') }}" class="text-sm text-blue-600 hover:underline inline-flex items-center gap-1">
            <i class="fas fa-arrow-left text-xs"></i> Back to Permissions
        </a>

        {{-- Card --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                <h2 class="text-lg font-bold text-gray-800">Edit Permission</h2>
                <p class="text-xs text-gray-500 mt-0.5">Update permission name</p>
            </div>

            @if($errors->any())
                <div class="mx-6 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('permissions.update', $permission) }}" method="POST" class="p-6 space-y-5">
                @csrf @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Permission Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $permission->name) }}" required
                           class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-400">
                </div>

                <div class="flex items-center gap-3 pt-3 border-t">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg text-sm font-medium transition">
                        <i class="fas fa-save mr-1"></i> Update Permission
                    </button>
                    <a href="{{ route('permissions.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
