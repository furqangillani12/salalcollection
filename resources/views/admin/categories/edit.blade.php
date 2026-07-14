@extends('layouts.admin')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Edit Category</h1>
            <a href="{{ route('categories.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                Back to Categories
            </a>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden max-w-2xl">
            <div class="p-6">
                <form action="{{ route('categories.update', $category) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('admin.categories._form')

                    <div class="mt-6 flex justify-end gap-2">
                        <a href="{{ route('categories.index') }}" class="px-4 py-2 rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50">Cancel</a>
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg">
                            Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
